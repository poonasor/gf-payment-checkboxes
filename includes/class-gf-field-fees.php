<?php

/**
 * Fees Field Class
 *
 * Defines a field type that allows adding multiple fees with labels and prices
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
 * Fees field
 */
class CHECPRFO_Field_Fees extends GF_Field
{
    /**
     * Field type identifier
     *
     * @var string
     */
    public $type = 'fees';

    /**
     * Get field title for form editor
     *
     * @return string
     */
    public function get_form_editor_field_title()
    {
        return esc_attr__('Fees', 'checkbox-products-for-gravity-forms');
    }

    public function get_form_editor_inline_script_on_page_render()
    {
        $title = esc_js($this->get_form_editor_field_title());

        return "function SetDefaultValues_{$this->type}(field){if(typeof field!=='undefined'&&field){field.label='{$title}';}else if(typeof window.field!=='undefined'){window.field.label='{$title}';}}";
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

    public function is_product_field()
    {
        return true;
    }

    /**
     * Get field icon for form editor button
     *
     * @return string
     */
    public function get_form_editor_field_icon()
    {
        return 'gform-icon gform-icon--total';
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
            'fees_setting',
            'rules_setting',
            'conditional_logic_field_setting',
            'admin_label_setting',
            'css_class_setting',
        ];
    }

    /**
     * Render field input HTML for frontend
     *
     * @param array      $form  The form object
     * @param string     $value The field value
     * @param array|null $entry The entry object
     * @return string
     */
    public function get_field_input($form, $value = '', $entry = null)
    {
        $form_id = absint($form['id']);
        $field_id = absint($this->id);

        if ($this->is_form_editor()) {
            return '<div class="ginput_container">Fees will appear here</div>';
        }

        $input_id = sprintf('input_%d_%d', $form_id, $field_id);
        $name = 'input_' . $field_id;

        $fees = isset($this->fees) && is_array($this->fees) ? $this->fees : [];
        $currency = rgar($form, 'currency', GFCommon::get_currency());

        $html = '<div class="ginput_container ginput_container_fees">';

        if (!empty($fees)) {
            $html .= '<ul class="gfield_fees_list">';

            foreach ($fees as $index => $fee) {
                $label = isset($fee['label']) ? esc_html($fee['label']) : '';
                $price = isset($fee['price']) ? GFCommon::to_number($fee['price']) : 0;
                $price_display = GFCommon::to_money($price, $currency);

                $html .= sprintf(
                    '<li class="gfield_fee_item" data-price="%s"><span class="gfield_fee_label">%s:</span> <span class="gfield_fee_price">%s</span></li>',
                    esc_attr($price),
                    $label,
                    $price_display
                );
            }

            $html .= '</ul>';
        }

        $html .= sprintf(
            '<input name="%s" id="%s" type="hidden" value="%s" class="fees-field-value" data-fees="%s" />',
            esc_attr($name),
            esc_attr($input_id),
            esc_attr($this->get_fees_total()),
            esc_attr(json_encode($fees))
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * Get total of all fees
     *
     * @return float
     */
    private function get_fees_total()
    {
        $total = 0;
        $fees = isset($this->fees) && is_array($this->fees) ? $this->fees : [];

        foreach ($fees as $fee) {
            if (isset($fee['price'])) {
                $total += GFCommon::to_number($fee['price']);
            }
        }

        return $total;
    }

    /**
     * Return field value from submission
     *
     * @param array $field_values Field values
     * @param bool  $get_from_post_global Whether to use $_POST
     * @return string
     */
    public function get_value_submission($field_values, $get_from_post_global = true)
    {
        $field_id = $this->id;
        $input_name = 'input_' . $field_id;

        if (isset($field_values[$input_name])) {
            return $field_values[$input_name];
        }

        if ($get_from_post_global) {
            return $this->get_input_value_submission($input_name, rgar($this, 'inputName'), false);
        }

        return $this->get_fees_total();
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
        $fees = isset($this->fees) && is_array($this->fees) ? $this->fees : [];

        if (empty($fees)) {
            return '';
        }

        if (empty($currency)) {
            $currency = GFCommon::get_currency();
        }

        $output = [];

        foreach ($fees as $fee) {
            $label = isset($fee['label']) ? esc_html($fee['label']) : '';
            $price = isset($fee['price']) ? GFCommon::to_number($fee['price']) : 0;
            $price_display = GFCommon::to_money($price, $currency);

            if ($format === 'html') {
                $output[] = sprintf(
                    '%s: <span class="entry-price">%s</span>',
                    $label,
                    $price_display
                );
            } else {
                $output[] = sprintf('%s: %s', $label, $price_display);
            }
        }

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
        $total = $this->get_fees_total();
        $currency = rgar($entry, 'currency', GFCommon::get_currency());

        return GFCommon::to_money($total, $currency);
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
}
