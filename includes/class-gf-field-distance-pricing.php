<?php

/**
 * Distance Pricing Field Class
 *
 * Defines a field type that calculates distance from a starting location
 * and charges based on mileage beyond a free zone
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
 * Distance Pricing field
 */
class CHECPRFO_Field_Distance_Pricing extends GF_Field
{
    /**
     * Field type identifier
     *
     * @var string
     */
    public $type = 'distance_pricing';

    /**
     * Get field title for form editor
     *
     * @return string
     */
    public function get_form_editor_field_title()
    {
        return esc_attr__('Distance Pricing', 'checkbox-products-for-gravity-forms');
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
        return 'gform-icon gform-icon--shipping';
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
            'distance_pricing_settings',
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
            return '<div class="ginput_container">Distance pricing will be calculated based on address field</div>';
        }

        $input_id = sprintf('input_%d_%d', $form_id, $field_id);
        $name = 'input_' . $field_id;

        // Get field settings
        $price_per_unit = isset($this->distancePricePerUnit) ? floatval($this->distancePricePerUnit) : 0;
        $starting_location = isset($this->distanceStartingLocation) ? $this->distanceStartingLocation : '';
        $free_zone = isset($this->distanceFreeZone) ? floatval($this->distanceFreeZone) : 0;
        $address_field_id = isset($this->distanceAddressField) ? $this->distanceAddressField : '';
        $unit_type = isset($this->distanceUnitType) ? $this->distanceUnitType : 'miles';

        $currency = rgar($form, 'currency', GFCommon::get_currency());

        $html = sprintf(
            '<div class="ginput_container ginput_container_distance_pricing" data-form-id="%d" data-field-id="%d" data-price-per-unit="%s" data-starting-location="%s" data-free-zone="%s" data-address-field="%s" data-unit-type="%s">',
            $form_id,
            $field_id,
            esc_attr($price_per_unit),
            esc_attr($starting_location),
            esc_attr($free_zone),
            esc_attr($address_field_id),
            esc_attr($unit_type)
        );

        // Hidden input to store calculated price
        $html .= sprintf(
            '<input name="%s" id="%s" type="hidden" value="%s" class="distance-pricing-value" />',
            esc_attr($name),
            esc_attr($input_id),
            esc_attr($value)
        );

        // Hidden input to store calculated distance
        $html .= sprintf(
            '<input name="%s_distance" id="%s_distance" type="hidden" value="" class="distance-pricing-distance" />',
            esc_attr($name),
            esc_attr($input_id)
        );

        // Display area for distance and pricing information
        $html .= '<div class="distance-pricing-display">';
        $html .= '<div class="distance-pricing-status">' . esc_html__('Enter your address to calculate distance pricing', 'checkbox-products-for-gravity-forms') . '</div>';
        $html .= '<div class="distance-pricing-details" style="display:none;">';
        $html .= '<div class="distance-pricing-distance-info"></div>';
        $html .= '<div class="distance-pricing-cost-info"></div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
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

        return '';
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
            return esc_html__('No distance charge', 'checkbox-products-for-gravity-forms');
        }

        if (empty($currency)) {
            $currency = GFCommon::get_currency();
        }

        $price = GFCommon::to_money($value, $currency);

        return sprintf(
            esc_html__('Distance Charge: %s', 'checkbox-products-for-gravity-forms'),
            $price
        );
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
            return esc_html__('No charge', 'checkbox-products-for-gravity-forms');
        }

        $currency = rgar($entry, 'currency', GFCommon::get_currency());
        return GFCommon::to_money($value, $currency);
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
     * Calculate distance pricing based on stored distance
     *
     * @param float  $distance Distance in selected units
     * @param array  $form     Form object
     * @param array  $entry    Entry object
     * @return float Calculated price
     */
    public function calculate_distance_price($distance, $form, $entry)
    {
        $price_per_unit = isset($this->distancePricePerUnit) ? floatval($this->distancePricePerUnit) : 0;
        $free_zone = isset($this->distanceFreeZone) ? floatval($this->distanceFreeZone) : 0;

        if ($distance <= $free_zone) {
            return 0;
        }

        $chargeable_distance = $distance - $free_zone;
        return $chargeable_distance * $price_per_unit;
    }
}
