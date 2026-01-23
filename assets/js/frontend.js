/**
 * Frontend JavaScript for Checkbox Products field
 *
 * Handles live price calculations and integration with Gravity Forms pricing
 *
 * @package GF_Checkbox_Products
 */

(function ($) {
  "use strict";

  /**
   * Initialize on document ready
   */
  $(document).ready(function () {
    initCheckboxProducts();
  });

  /**
   * Initialize checkbox products for all forms
   */
  function initCheckboxProducts() {
    // Bind to checkbox changes
    $(document).on("change", ".gfield-checkbox-product-choice", function () {
      var $checkbox = $(this);
      var $form = $checkbox.closest("form");

      if ($form.length) {
        var formId = extractFormId($form);
        if (formId) {
          console.log(
            "[GF Checkbox Products] Checkbox changed, recalculating form",
            formId,
          );
          recalculateTotal(formId);
          setTimeout(function () {
            updateDepositTotals(formId);
          }, 150);
        }
      }
    });

    // Monitor address field changes for distance pricing
    $(document).on(
      "change blur",
      ".ginput_container_address input, .ginput_container_address select",
      function () {
        var $input = $(this);
        var $form = $input.closest("form");

        if ($form.length) {
          var formId = extractFormId($form);
          if (formId) {
            setTimeout(function () {
              checkDistancePricingFields(formId);
            }, 500);
          }
        }
      },
    );

    // Initialize on form render (for AJAX forms)
    $(document).on("gform_post_render", function (event, formId) {
      initializeForm(formId);
    });

    // Deposit Due: recalc when the total changes
    $(document).on("gform_total_change", function (event, formId) {
      updateDepositTotals(formId);
    });

    // Deposit Due: recalc when pricing changes
    $(document).on("gform_price_change", function (event, formId) {
      updateDepositTotals(formId);
    });

    // Deposit Due: recalc when percentage input changes
    $(document).on("input change", ".deposit-total-percent-value", function () {
      var $input = $(this);
      var $form = $input.closest("form");
      if ($form.length) {
        var formId = extractFormId($form);
        if (formId) {
          updateDepositTotals(formId);
        }
      }
    });

    // Initialize existing forms on page
    $(".gform_wrapper form").each(function () {
      var formId = extractFormId($(this));
      if (formId) {
        initializeForm(formId);
      }
    });
  }

  /**
   * Initialize a specific form
   *
   * @param {number} formId Form ID
   */
  function initializeForm(formId) {
    // Hook into Gravity Forms price calculation if available
    if (typeof window.gform !== "undefined" && window.gform.addFilter) {
      // Add filter for product total calculation
      window.gform.addFilter(
        "gform_product_total",
        function (total, formIdParam) {
          if (parseInt(formIdParam) === parseInt(formId)) {
            var checkboxTotal = calculateCheckboxProductsTotal(formId);
            console.log(
              "[GF Checkbox Products] Adding checkbox total:",
              checkboxTotal,
              "to existing total:",
              total,
            );
            return total + checkboxTotal;
          }
          return total;
        },
      );

      // Also hook into subtotal if it exists
      window.gform.addFilter(
        "gform_product_subtotal",
        function (subtotal, formIdParam) {
          if (parseInt(formIdParam) === parseInt(formId)) {
            var checkboxTotal = calculateCheckboxProductsTotal(formId);
            return subtotal + checkboxTotal;
          }
          return subtotal;
        },
      );
    }

    // Trigger initial calculation with a slight delay
    setTimeout(function () {
      recalculateTotal(formId);
      updateDepositTotals(formId);
      initDistancePricing(formId);
    }, 100);
  }

  /**
   * Initialize distance pricing for a form
   *
   * @param {number} formId Form ID
   */
  function initDistancePricing(formId) {
    var configKey = "gfCheckboxProducts_" + formId;
    if (
      typeof window[configKey] === "undefined" ||
      !window[configKey].distanceFields
    ) {
      return;
    }

    var distanceFields = window[configKey].distanceFields;
    if (!distanceFields || distanceFields.length === 0) {
      return;
    }

    console.log(
      "[GF Distance Pricing] Initializing distance pricing for form",
      formId,
    );
  }

  /**
   * Check and calculate distance pricing fields
   *
   * @param {number} formId Form ID
   */
  function checkDistancePricingFields(formId) {
    var configKey = "gfCheckboxProducts_" + formId;
    if (
      typeof window[configKey] === "undefined" ||
      !window[configKey].distanceFields
    ) {
      return;
    }

    var distanceFields = window[configKey].distanceFields;
    var apiKey = window[configKey].googleMapsApiKey;

    if (!apiKey) {
      console.error("[GF Distance Pricing] Google Maps API key not configured");
      return;
    }

    $.each(distanceFields, function (index, fieldConfig) {
      calculateDistancePrice(formId, fieldConfig, apiKey);
    });
  }

  /**
   * Calculate distance and price for a distance pricing field
   *
   * @param {number} formId Form ID
   * @param {Object} fieldConfig Field configuration
   * @param {string} apiKey Google Maps API key
   */
  function calculateDistancePrice(formId, fieldConfig, apiKey) {
    var $form = $("#gform_" + formId);
    var addressFieldId = fieldConfig.addressField;

    if (!addressFieldId) {
      return;
    }

    // Get address from the address field
    var address = getAddressFieldValue(formId, addressFieldId);

    if (!address || address.trim() === "") {
      return;
    }

    var $container = $form.find(
      '[data-field-id="' + fieldConfig.fieldId + '"]',
    );
    var $status = $container.find(".distance-pricing-status");
    var $details = $container.find(".distance-pricing-details");
    var $distanceInfo = $container.find(".distance-pricing-distance-info");
    var $costInfo = $container.find(".distance-pricing-cost-info");
    var $priceInput = $container.find(".distance-pricing-value");
    var $distanceInput = $container.find(".distance-pricing-distance");

    $status.text("Calculating distance...");
    $details.hide();

    // Use Google Maps Distance Matrix API
    if (typeof google === "undefined" || !google.maps) {
      $status.text("Google Maps API not loaded. Please refresh the page.");
      return;
    }

    var service = new google.maps.DistanceMatrixService();
    var origin = fieldConfig.startingLocation;
    var destination = address;

    var unitType = fieldConfig.unitType || "miles";
    var unitSystem =
      unitType === "kilometers"
        ? google.maps.UnitSystem.METRIC
        : google.maps.UnitSystem.IMPERIAL;

    service.getDistanceMatrix(
      {
        origins: [origin],
        destinations: [destination],
        travelMode: google.maps.TravelMode.DRIVING,
        unitSystem: unitSystem,
      },
      function (response, status) {
        if (status === google.maps.DistanceMatrixStatus.OK) {
          var results = response.rows[0].elements[0];

          if (results.status === "OK") {
            var distanceValue = results.distance.value; // in meters
            var distanceText = results.distance.text;

            // Convert to miles or kilometers
            var distance =
              unitType === "kilometers"
                ? distanceValue / 1000
                : distanceValue / 1609.34;

            distance = Math.round(distance * 100) / 100;

            $distanceInput.val(distance);

            // Calculate price
            var freeZone = parseFloat(fieldConfig.freeZone) || 0;
            var pricePerUnit = parseFloat(fieldConfig.pricePerUnit) || 0;
            var price = 0;

            if (distance > freeZone) {
              var chargeableDistance = distance - freeZone;
              price = chargeableDistance * pricePerUnit;
              price = Math.round(price * 100) / 100;
            }

            $priceInput.val(price);

            // Update display
            var unitLabel = unitType === "kilometers" ? "km" : "miles";
            $distanceInfo.html(
              "<strong>Distance:</strong> " +
                distance.toFixed(2) +
                " " +
                unitLabel,
            );

            if (price > 0) {
              var formattedPrice = formatCurrency(price, formId);
              $costInfo.html(
                "<strong>Distance Charge:</strong> " +
                  formattedPrice +
                  " (" +
                  chargeableDistance.toFixed(2) +
                  " " +
                  unitLabel +
                  " @ " +
                  formatCurrency(pricePerUnit, formId) +
                  " per " +
                  unitLabel +
                  ")",
              );
              $status.text("Distance calculated successfully");
            } else {
              $costInfo.html(
                "<strong>Distance Charge:</strong> No charge (within free zone of " +
                  freeZone +
                  " " +
                  unitLabel +
                  ")",
              );
              $status.text("Within free delivery zone");
            }

            $details.show();

            // Trigger price recalculation
            recalculateTotal(formId);
            setTimeout(function () {
              updateDepositTotals(formId);
            }, 150);
          } else {
            $status.text(
              "Could not calculate distance. Please check the address.",
            );
            $priceInput.val(0);
          }
        } else {
          $status.text("Error calculating distance: " + status);
          $priceInput.val(0);
        }
      },
    );
  }

  /**
   * Get address field value
   *
   * @param {number} formId Form ID
   * @param {string} fieldId Field ID
   * @return {string} Address string
   */
  function getAddressFieldValue(formId, fieldId) {
    var $form = $("#gform_" + formId);
    var addressParts = [];

    // Standard Gravity Forms address field structure
    var street = $form.find("#input_" + formId + "_" + fieldId + "_1").val();
    var street2 = $form.find("#input_" + formId + "_" + fieldId + "_2").val();
    var city = $form.find("#input_" + formId + "_" + fieldId + "_3").val();
    var state = $form.find("#input_" + formId + "_" + fieldId + "_4").val();
    var zip = $form.find("#input_" + formId + "_" + fieldId + "_5").val();
    var country = $form.find("#input_" + formId + "_" + fieldId + "_6").val();

    if (street) addressParts.push(street);
    if (street2) addressParts.push(street2);
    if (city) addressParts.push(city);
    if (state) addressParts.push(state);
    if (zip) addressParts.push(zip);
    if (country) addressParts.push(country);

    return addressParts.join(", ");
  }

  function parsePercent(value) {
    if (typeof value !== "string") {
      return 0;
    }

    value = $.trim(value);
    if (!value) {
      return 0;
    }

    value = value.replace("%", "");
    var percent = parseFloat(value);

    if (isNaN(percent) || percent < 0) {
      return 0;
    }

    return percent;
  }

  function getFormTotal(formId) {
    var $form = $("#gform_" + formId);
    var raw = "";

    // Visible total output (varies by GF markup/theme)
    var $total = $form.find(".ginput_total");
    if (!$total.length) {
      $total = $form.find('[class*="ginput_total"]');
    }

    if ($total.length) {
      raw = $.trim($total.first().text());
    }

    // Hidden total input (fallback)
    if (!raw) {
      var $hiddenTotal = $form.find(
        'input[id^="gform_total_"], input[name^="gform_total"], input[name="gform_total"]',
      );
      if ($hiddenTotal.length) {
        raw = $.trim($hiddenTotal.first().val());
      }
    }

    if (raw) {
      raw = String(raw).replace(/[^0-9.\-]/g, "");
      var n = parseFloat(raw);
      if (!isNaN(n)) {
        return n;
      }
    }

    // Last resort: at least include checkbox products total
    return calculateCheckboxProductsTotal(formId);
  }

  function updateDepositTotals(formId) {
    var $form = $("#gform_" + formId);
    if (!$form.length) {
      return;
    }

    var total = getFormTotal(formId);
    $form.find(".ginput_container_deposit_total").each(function () {
      var $container = $(this);
      var $percentInput = $container.find(".deposit-total-percent-value");
      var $display = $container.find(".ginput_deposit_total_amount");

      if (!$display.length) {
        return;
      }

      var percentRaw = "";
      if ($percentInput.length) {
        percentRaw = $percentInput.val();
      }

      if (!percentRaw) {
        percentRaw = $container.data("deposit-percent");
      }

      var percent = parsePercent(String(percentRaw || ""));
      var amount = total * (percent / 100);

      var formatted = formatCurrency(amount, formId);
      $display.text(formatted);
    });
  }

  /**
   * Calculate total for all checkbox products in a form
   *
   * @param {number} formId Form ID
   * @return {number} Total price
   */
  function calculateCheckboxProductsTotal(formId) {
    var total = 0;
    var selector =
      "#gform_" + formId + " .gfield-checkbox-product-choice:checked";

    $(selector).each(function () {
      var price = parseFloat($(this).data("price")) || 0;
      total += price;
    });

    // Add fees to the total
    var feesTotal = calculateFeesTotal(formId);
    total += feesTotal;

    return total;
  }

  /**
   * Calculate total for all fees in a form
   *
   * @param {number} formId Form ID
   * @return {number} Total fees
   */
  function calculateFeesTotal(formId) {
    var total = 0;
    var $form = $("#gform_" + formId);

    $form.find(".gfield_fee_item").each(function () {
      var price = parseFloat($(this).data("price")) || 0;
      total += price;
    });

    return total;
  }

  /**
   * Trigger Gravity Forms price recalculation
   *
   * @param {number} formId Form ID
   */
  function recalculateTotal(formId) {
    console.log("[GF Checkbox Products] Recalculating total for form", formId);

    // Method 1: Use GF's native function if available
    if (typeof window.gformCalculateTotalPrice === "function") {
      console.log("[GF Checkbox Products] Using gformCalculateTotalPrice");
      window.gformCalculateTotalPrice(formId);
    }

    // Method 2: Trigger custom event (some themes/plugins listen to this)
    $(document).trigger("gform_price_change", [formId]);

    // Method 3: Also trigger change on any pricing field to force recalc
    $(
      "#gform_" +
        formId +
        " .ginput_quantity, #gform_" +
        formId +
        " .gfield_price",
    )
      .first()
      .trigger("change");
  }

  /**
   * Manually update the total field (fallback method)
   *
   * @param {number} formId Form ID
   */
  function updateTotalField(formId) {
    var $form = $("#gform_" + formId);
    var $totalField = $form.find(".ginput_total");

    if (!$totalField.length) {
      return;
    }

    // Calculate checkbox products total
    var checkboxTotal = calculateCheckboxProductsTotal(formId);

    // Get other product totals (basic implementation)
    var otherTotal = 0;
    $form
      .find(".gfield_price")
      .not(".gfield-checkbox-product-choice")
      .each(function () {
        var val = parseFloat($(this).val()) || 0;
        otherTotal += val;
      });

    var total = checkboxTotal + otherTotal;

    // Format and update total
    var formattedTotal = formatCurrency(total, formId);
    $totalField.text(formattedTotal);
  }

  /**
   * Format a number as currency
   *
   * @param {number} amount Amount to format
   * @param {number} formId Form ID
   * @return {string} Formatted currency
   */
  function formatCurrency(amount, formId) {
    // Try to get currency settings from localized data
    var configKey = "gfCheckboxProducts_" + formId;
    var currency = "USD";

    if (typeof window[configKey] !== "undefined") {
      currency = window[configKey].currency || "USD";
    }

    // Basic currency formatting
    var formatted = amount.toFixed(2);

    // Add currency symbol (basic implementation)
    switch (currency) {
      case "USD":
      case "CAD":
      case "AUD":
        return "$" + formatted;
      case "EUR":
        return "€" + formatted;
      case "GBP":
        return "£" + formatted;
      default:
        return formatted + " " + currency;
    }
  }

  /**
   * Extract form ID from form element
   *
   * @param {jQuery} $form Form element
   * @return {number|null} Form ID or null
   */
  function extractFormId($form) {
    var formId = $form.attr("id");

    if (!formId) {
      return null;
    }

    // Extract number from id like "gform_1"
    var matches = formId.match(/gform_(\d+)/);
    return matches ? parseInt(matches[1]) : null;
  }

  /**
   * Debug logging (only if debug mode enabled)
   *
   * @param {string} message Message to log
   * @param {*} data Optional data to log
   */
  function debug(message, data) {
    // Check if debug enabled for any form
    for (var key in window) {
      if (key.indexOf("gfCheckboxProducts_") === 0) {
        if (window[key].debug) {
          console.log("[GF Checkbox Products] " + message, data || "");
          break;
        }
      }
    }
  }

  /**
   * Public API (optional)
   */
  window.GFCheckboxProducts = {
    calculateTotal: calculateCheckboxProductsTotal,
    recalculate: recalculateTotal,
  };
})(jQuery);
