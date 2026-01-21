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

    // Initialize on form render (for AJAX forms)
    $(document).on("gform_post_render", function (event, formId) {
      initializeForm(formId);
    });

    // Deposit total: recalc when the total changes
    $(document).on("gform_total_change", function (event, formId) {
      updateDepositTotals(formId);
    });

    // Deposit total: recalc when pricing changes
    $(document).on("gform_price_change", function (event, formId) {
      updateDepositTotals(formId);
    });

    // Deposit total: recalc when percentage input changes
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
    }, 100);
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
