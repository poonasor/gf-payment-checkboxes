/**
 * Admin JavaScript for Checkbox Products field
 *
 * Handles the field settings UI in the Gravity Forms form editor
 *
 * @package GF_Checkbox_Products
 */

(function ($) {
  "use strict";

  // Define which settings to show for this field type
  window.fieldSettings = window.fieldSettings || {};
  window.fieldSettings.checkbox_product =
    ".label_setting, " +
    ".description_setting, " +
    ".checkbox_product_choices_setting, " +
    ".rules_setting, " +
    ".conditional_logic_field_setting, " +
    ".label_placement_setting, " +
    ".admin_label_setting, " +
    ".css_class_setting";

  window.fieldSettings.deposit_total =
    ".label_setting, " +
    ".description_setting, " +
    ".deposit_total_percent_setting, " +
    ".rules_setting, " +
    ".conditional_logic_field_setting, " +
    ".admin_label_setting, " +
    ".css_class_setting";

  // Bind to field settings load event
  $(document).on("gform_load_field_settings", function (event, field, form) {
    if (field.type === "checkbox_product") {
      loadProductChoices(field);
    }

    if (field.type === "deposit_total") {
      $("#field_deposit_total_percent").val(field.depositPercent || "");

      if (!field.label || field.label === "Untitled") {
        window.SetFieldProperty("label", "Deposit Total");
        $("#field_label")
          .val("Deposit Total")
          .trigger("input")
          .trigger("change");
      }
    }
  });

  $(document).on("gform_field_added", function (event, form, field) {
    if (field && field.type === "deposit_total") {
      if (!field.label || field.label === "Untitled") {
        field.label = "Deposit Total";
      }

      if (typeof window.SetFieldProperty === "function") {
        window.SetFieldProperty("label", field.label);
      }
    }
  });

  /**
   * Load existing product choices into the settings UI
   *
   * @param {Object} field The field object
   */
  function loadProductChoices(field) {
    var container = $("#checkbox_product_choices_container");
    container.empty();

    if (field.choices && field.choices.length > 0) {
      // Load existing choices
      $.each(field.choices, function (index, choice) {
        addChoiceRow(
          choice.text || "",
          choice.value || "",
          choice.price || "",
          index,
        );
      });
    } else {
      // Add one default empty row
      addChoiceRow("", "", "", 0);
    }
  }

  /**
   * Add a new choice (global function for onclick handler)
   */
  window.gfCheckboxProductAddChoice = function () {
    addChoiceRow();
  };

  /**
   * Add a new choice row to the settings UI
   *
   * @param {string} label Choice label
   * @param {string} value Choice value
   * @param {string} price Choice price
   * @param {number} index Choice index
   */
  function addChoiceRow(label, value, price, index) {
    label = label || "";
    value = value || "";
    price = price || "";

    var container = $("#checkbox_product_choices_container");
    var newIndex =
      typeof index !== "undefined"
        ? index
        : container.find(".gf-checkbox-product-choice-row").length;

    // If no value provided, generate one from label
    if (!value && label) {
      value = sanitizeValue(label);
    }

    var i18n = window.gfCheckboxProductsAdmin
      ? window.gfCheckboxProductsAdmin.i18n
      : {};

    var row = $("<div>", {
      class: "gf-checkbox-product-choice-row",
      "data-index": newIndex,
    });

    // Label input
    var labelInput = $("<input>", {
      type: "text",
      class: "gf-choice-label",
      placeholder: i18n.labelPlaceholder || "Product Name",
      value: escapeHtml(label),
    });

    // Value input
    var valueInput = $("<input>", {
      type: "text",
      class: "gf-choice-value",
      placeholder: i18n.valuePlaceholder || "value",
      value: escapeHtml(value),
    });

    // Price input
    var priceInput = $("<input>", {
      type: "text",
      class: "gf-choice-price",
      placeholder: i18n.pricePlaceholder || "0.00",
      value: price,
    });

    // Delete button
    var deleteBtn = $("<button>", {
      type: "button",
      class: "button gf-delete-choice",
      html: '<span class="dashicons dashicons-trash"></span>',
    });

    // Assemble row
    row.append(
      $('<div class="gf-choice-column gf-choice-column-label">').append(
        $("<label>").text("Label:"),
        labelInput,
      ),
      $('<div class="gf-choice-column gf-choice-column-value">').append(
        $("<label>").text("Value:"),
        valueInput,
      ),
      $('<div class="gf-choice-column gf-choice-column-price">').append(
        $("<label>").text("Price:"),
        priceInput,
      ),
      $('<div class="gf-choice-column gf-choice-column-actions">').append(
        deleteBtn,
      ),
    );

    container.append(row);

    // Bind events
    row.find("input").on("input change", function () {
      // Auto-generate value from label if value is empty
      if ($(this).hasClass("gf-choice-label")) {
        var valueField = row.find(".gf-choice-value");
        if (!valueField.val()) {
          valueField.val(sanitizeValue($(this).val()));
        }
      }
      saveProductChoices();
    });

    row.find(".gf-delete-choice").on("click", function (e) {
      e.preventDefault();
      deleteChoiceRow(row);
    });
  }

  /**
   * Delete a choice row
   *
   * @param {jQuery} row The row element to delete
   */
  function deleteChoiceRow(row) {
    var container = $("#checkbox_product_choices_container");
    var i18n = window.gfCheckboxProductsAdmin
      ? window.gfCheckboxProductsAdmin.i18n
      : {};

    // Don't allow deleting the last row
    if (container.find(".gf-checkbox-product-choice-row").length <= 1) {
      alert(i18n.confirmDelete || "You must have at least one choice.");
      return;
    }

    row.fadeOut(200, function () {
      row.remove();
      saveProductChoices();
    });
  }

  /**
   * Save product choices to the field object
   */
  function saveProductChoices() {
    var choices = [];

    $(
      "#checkbox_product_choices_container .gf-checkbox-product-choice-row",
    ).each(function () {
      var label = $(this).find(".gf-choice-label").val();
      var value = $(this).find(".gf-choice-value").val();
      var price = $(this).find(".gf-choice-price").val();

      // Only save if label has content
      if (label && label.trim()) {
        // Use label as value if value is empty
        if (!value || !value.trim()) {
          value = sanitizeValue(label);
        }

        choices.push({
          text: label.trim(),
          value: value.trim(),
          price: parseFloat(price) || 0,
        });
      }
    });

    // Update the field property
    window.SetFieldProperty("choices", choices);
  }

  /**
   * Sanitize a string for use as a value
   *
   * @param {string} text The text to sanitize
   * @return {string} Sanitized value
   */
  function sanitizeValue(text) {
    if (!text) return "";

    return text
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, "") // Remove special characters
      .replace(/\s+/g, "_") // Replace spaces with underscores
      .replace(/-+/g, "_") // Replace hyphens with underscores
      .replace(/_+/g, "_") // Remove duplicate underscores
      .replace(/^_+|_+$/g, ""); // Trim underscores from start/end
  }

  /**
   * Escape HTML entities
   *
   * @param {string} text The text to escape
   * @return {string} Escaped text
   */
  function escapeHtml(text) {
    if (!text) return "";

    var div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
})(jQuery);
