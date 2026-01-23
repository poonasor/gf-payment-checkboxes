<?php

/**
 * Checkbox Product Field Class
 *
 * Defines the custom Checkbox Product field type for Gravity Forms
 *
 * @package GF_Checkbox_Products
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Ensure Gravity Forms is loaded
if (!class_exists('GF_Field')) {
    return;
}

/**
 * Custom Checkbox Product field class
 *
 * Extends GF_Field to create a checkbox-based product selector
 */
class CHECPRFO_Field_Checkbox_Product extends GF_Field
{

    /**
     * Field type identifier
     *
     * @var string
     */
    public $type = 'checkbox_product';

    /**
     * Get field title for form editor
     *
     * @return string
     */
    public function get_form_editor_field_title()
    {
        return esc_attr__('Checkbox Products', 'checkbox-products-for-gravity-forms');
    }

    /**
     * Get field button configuration for form editor
     *
     * @return array
     */
    public function get_form_editor_button()
    {
        return [
            'group' => 'pricing_fields',
            'text'  => $this->get_form_editor_field_title(),
        ];
    }

    /**
     * Get field icon for form editor button
     *
     * @return string
     */
    public function get_form_editor_field_icon()
    {
        return 'gform-icon gform-icon--check-box';
    }

    /**
     * Define which settings appear in the form editor
     *
     * @return array
     */
    public function get_form_editor_field_settings()
    {
        return [
            'label_setting',
            'description_setting',
            'rules_setting',
            'conditional_logic_field_setting',
            'label_placement_setting',
            'admin_label_setting',
            'css_class_setting',
            'checkbox_product_choices_setting',
        ];
    }

    /**
     * Mark this field as a product field for Gravity Forms pricing system
     *
     * @return bool
     */
    public function is_product_field()
    {
        return true;
    }

    /**
     * Define entry inputs (sub-inputs) so Gravity Forms persists checkbox values
     * as 1.1, 1.2, etc. and other GF subsystems (order, merge tags, etc.) can
     * properly access each selection.
     *
     * @return array|null
     */
    public function get_entry_inputs()
    {
        if (!is_array($this->choices) || empty($this->choices)) {
            return null;
        }

        $inputs = [];
        foreach ($this->choices as $index => $choice) {
            $inputs[] = [
                'id'    => (string) $this->id . '.' . ($index + 1),
                'label' => rgar($choice, 'text', ''),
            ];
        }

        return $inputs;
    }

    /**
     * Render field input HTML for frontend
     *
     * @param array      $form  The form object
     * @param string     $value The field value (empty string on initial render)
     * @param array|null $entry The entry object when available
     * @return string Field HTML markup
     */
    public function get_field_input($form, $value = '', $entry = null)
    {
        $form_id = absint($form['id']);
        $field_id = absint($this->id);
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor = $this->is_form_editor();

        // Return placeholder for form editor
        if ($is_form_editor) {
            return '<div class="ginput_container">Checkbox product choices will appear here</div>';
        }

        // Parse existing value
        $selected_values = [];
        if (!empty($value)) {
            $selected_values = is_array($value) ? $value : explode(',', $value);
        }

        // Start building HTML - using GF native structure
        $html = '<div class="ginput_container ginput_container_checkbox">';
        $html .= sprintf(
            '<div class="gfield_checkbox" id="input_%d_%d">',
            $form_id,
            $field_id
        );

        // Render each choice
        if (is_array($this->choices) && !empty($this->choices)) {
            foreach ($this->choices as $index => $choice) {
                $html .= $this->render_choice($choice, $index, $form_id, $field_id, $selected_values, $form);
            }
        } else {
            // No choices defined
            $html .= '<div class="gchoice">';
            $html .= esc_html__('No product choices configured.', 'checkbox-products-for-gravity-forms');
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a single checkbox choice
     *
     * @param array  $choice          The choice configuration
     * @param int    $index           Choice index
     * @param int    $form_id         Form ID
     * @param int    $field_id        Field ID
     * @param array  $selected_values Currently selected values
     * @param array  $form            The form object
     * @return string Choice HTML markup
     */
    private function render_choice($choice, $index, $form_id, $field_id, $selected_values, $form)
    {
        $choice_id = $field_id . '_' . ($index + 1);
        $input_id = sprintf('choice_%d_%s', $form_id, $choice_id);
        $label_id = sprintf('label_%d_%s', $form_id, $choice_id);

        // Get price and format it
        $price = isset($choice['price']) ? GFCommon::to_number($choice['price']) : 0;
        $currency = $this->get_currency($form);
        $price_display = GFCommon::to_money($price, $currency);

        // Check if this choice is selected
        $choice_value = rgar($choice, 'value', '');
        $is_selected = in_array($choice_value, $selected_values, true);
        $checked = $is_selected ? 'checked="checked"' : '';

        // Build choice HTML - matching GF native structure exactly
        $html = sprintf('<div class="gchoice gchoice_%s">', esc_attr($choice_id));

        // Input with both classes for compatibility
        $html .= sprintf(
            '<input class="gfield-choice-input gfield-checkbox-product-choice" name="input_%d.%d" type="checkbox" value="%s" data-price="%s" id="%s" %s />',
            $field_id,
            ($index + 1),
            esc_attr($choice_value),
            esc_attr($price),
            esc_attr($input_id),
            $checked
        );

        // Label with GF classes
        $html .= sprintf(
            '<label for="%s" id="%s" class="gform-field-label gform-field-label--type-inline">%s <span class="ginput_product_price">(%s)</span></label>',
            esc_attr($input_id),
            esc_attr($label_id),
            esc_html(rgar($choice, 'text', '')),
            esc_html($price_display)
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * Get form currency
     *
     * @param array $form The form object
     * @return string Currency code
     */
    private function get_currency($form)
    {
        return rgar($form, 'currency', GFCommon::get_currency());
    }

    /**
     * Get submitted field value
     *
     * @param array $field_values        Field values from $_POST
     * @param bool  $get_from_post_global Whether to get value from $_POST
     * @return array|string Submitted value
     */
    public function get_value_submission($field_values, $get_from_post_global = true)
    {
        $field_id = $this->id;
        $input_name = 'input_' . $field_id;

        if (isset($field_values[$input_name])) {
            return $field_values[$input_name];
        }

        if ($get_from_post_global) {
            $values = [];

            $sources = [];
            if (is_array($field_values)) {
                $sources[] = $field_values;
            }
            if (isset($_POST) && is_array($_POST)) {
                $sources[] = $_POST;
            }

            $pattern = '/^input_' . preg_quote((string) $field_id, '/') . '[\._](\d+)$/';

            foreach ($sources as $source) {
                foreach ($source as $key => $value) {
                    if (!is_string($key)) {
                        continue;
                    }

                    if (!preg_match($pattern, $key)) {
                        continue;
                    }

                    if ($value === '' || $value === null) {
                        continue;
                    }

                    $values[] = $value;
                }
            }

            return $values;
        }

        return [];
    }

    /**
     * Format value for saving to entry
     *
     * @param string $value         The field value
     * @param array  $form          The form object
     * @param string $input_name    The input name
     * @param int    $entry_id      The entry ID
     * @param array  $entry         The entry object
     * @return string Formatted value for storage
     */
    public function get_value_save_entry($value, $form, $input_name, $entry_id, $entry)
    {
        if (is_array($value)) {
            // Filter out empty values and join with commas
            $value = array_filter($value);
            return implode(',', $value);
        }

        return $value;
    }

    /**
     * Format value for entry detail page
     *
     * @param string $value    The field value
     * @param string $currency Currency code
     * @param bool   $use_text Whether to use choice text
     * @param string $format   Output format (html, text)
     * @param string $media    Output media (screen, email, etc)
     * @return string Formatted value
     */
    public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen')
    {
        if (empty($value)) {
            return '';
        }

        // Parse selected values
        $selected = is_array($value) ? $value : explode(',', $value);
        $output = [];

        // Get currency if not provided
        if (empty($currency)) {
            $currency = GFCommon::get_currency();
        }

        // Build output for each selected choice
        if (is_array($this->choices)) {
            foreach ($this->choices as $choice) {
                if (in_array(rgar($choice, 'value'), $selected, true)) {
                    $price = GFCommon::to_money(rgar($choice, 'price', 0), $currency);
                    $text = esc_html(rgar($choice, 'text', ''));

                    if ($format === 'html') {
                        $output[] = sprintf(
                            '%s <span class="entry-price">(%s)</span>',
                            $text,
                            $price
                        );
                    } else {
                        $output[] = sprintf('%s (%s)', $text, $price);
                    }
                }
            }
        }

        if (empty($output)) {
            return '';
        }

        // Format output
        if ($format === 'html') {
            return '<ul class="bulleted"><li>' . implode('</li><li>', $output) . '</li></ul>';
        }

        return implode(', ', $output);
    }

    /**
     * Format value for entry list table
     *
     * @param string $value   The field value
     * @param array  $entry   The entry object
     * @param string $field_id The field ID
     * @param array  $columns  Column configuration
     * @param array  $form     The form object
     * @return string Formatted value
     */
    public function get_value_entry_list($value, $entry, $field_id, $columns, $form)
    {
        if (empty($value)) {
            return '';
        }

        $selected = is_array($value) ? $value : explode(',', $value);
        $count = count($selected);
        $total = 0;

        // Calculate total price
        if (is_array($this->choices)) {
            foreach ($this->choices as $choice) {
                if (in_array(rgar($choice, 'value'), $selected, true)) {
                    $total += floatval(rgar($choice, 'price', 0));
                }
            }
        }

        $currency = rgar($entry, 'currency', GFCommon::get_currency());

        return sprintf(
            /* translators: 1: number of items, 2: total price */
            esc_html__('%1$d item(s) - %2$s', 'checkbox-products-for-gravity-forms'),
            $count,
            GFCommon::to_money($total, $currency)
        );
    }

    /**
     * Format value for merge tags (notifications, confirmations)
     *
     * @param string $value      The field value
     * @param string $input_id   The input ID
     * @param array  $entry      The entry object
     * @param array  $form       The form object
     * @param string $modifier   Merge tag modifier
     * @param string $raw_value  Raw field value
     * @param bool   $url_encode Whether to URL encode
     * @param bool   $esc_html   Whether to escape HTML
     * @param string $format     Output format
     * @param bool   $nl2br      Whether to convert newlines to <br>
     * @return string Formatted value
     */
    public function get_value_merge_tag($value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br)
    {
        $currency = rgar($entry, 'currency', GFCommon::get_currency());
        $detail = $this->get_value_entry_detail($value, $currency, false, $format);

        if ($esc_html) {
            $detail = esc_html($detail);
        }

        if ($url_encode) {
            $detail = urlencode($detail);
        }

        return $detail;
    }

    /**
     * Validate field value
     *
     * @param string $value The submitted value
     * @param array  $form  The form object
     * @return void
     */
    public function validate($value, $form)
    {
        // If field is required, ensure at least one checkbox is selected
        if ($this->isRequired) {
            $selected = is_array($value) ? $value : [];

            if (empty($selected) || (count($selected) === 1 && empty($selected[0]))) {
                $this->failed_validation = true;
                $this->validation_message = empty($this->errorMessage)
                    ? esc_html__('This field is required. Please select at least one option.', 'checkbox-products-for-gravity-forms')
                    : $this->errorMessage;
            }
        }
    }

    /**
     * Get product field values for pricing calculations
     *
     * @param array $entry The entry object
     * @return array Array of product info
     */
    public function get_product_field_values($entry)
    {
        $products = [];
        $field_id = $this->id;
        $selected = rgar($entry, $field_id);

        if (empty($selected)) {
            return $products;
        }

        $selected_values = is_array($selected) ? $selected : explode(',', $selected);

        // Build product info for each selected choice
        if (is_array($this->choices)) {
            foreach ($this->choices as $index => $choice) {
                if (in_array(rgar($choice, 'value'), $selected_values, true)) {
                    $products[] = [
                        'name'     => rgar($choice, 'text', ''),
                        'price'    => GFCommon::to_number(rgar($choice, 'price', 0)),
                        'quantity' => 1,
                    ];
                }
            }
        }

        return $products;
    }
}
