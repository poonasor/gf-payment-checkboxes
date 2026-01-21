<?php

/**
 * Deposit Total Field Class
 *
 * Defines a field type that accepts a percentage and displays a calculated deposit amount.
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
 * Deposit Total field
 */
class GF_Field_Deposit_Total extends GF_Field
{
    /**
     * Field type identifier
     *
     * @var string
     */
    public $type = 'deposit_total';

    /**
     * Get field title for form editor
     *
     * @return string
     */
    public function get_form_editor_field_title()
    {
        return esc_attr__('Deposit Total', 'gf-payment-checkboxes');
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
            'deposit_total_percent_setting',
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
            return '<div class="ginput_container">Deposit total percentage will appear here</div>';
        }

        $input_id = sprintf('input_%d_%d', $form_id, $field_id);
        $name = 'input_' . $field_id;

        $percent = isset($this->depositPercent) ? $this->depositPercent : '';
        if (!is_string($percent)) {
            $percent = '';
        }

        $html = sprintf(
            '<div class="ginput_container ginput_container_deposit_total" data-deposit-percent="%s">',
            esc_attr($percent)
        );
        $html .= sprintf(
            '<input name="%s" id="%s" type="hidden" value="%s" class="deposit-total-percent-value" />',
            esc_attr($name),
            esc_attr($input_id),
            esc_attr($percent)
        );

        $html .= sprintf(
            '<div class="ginput_deposit_total_amount" data-form-id="%d" data-field-id="%d"></div>',
            $form_id,
            $field_id
        );

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
}
