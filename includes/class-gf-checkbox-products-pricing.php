<?php

/**
 * Pricing Integration Class
 *
 * Handles integration with Gravity Forms pricing system
 *
 * @package GF_Checkbox_Products
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pricing functionality for Checkbox Product field
 */
class CHECPRFO_Pricing
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Hook into Gravity Forms pricing system
        add_filter('gform_product_info', [$this, 'add_checkbox_products_to_order'], 10, 3);

        // Enqueue frontend calculation scripts
        add_action('gform_enqueue_scripts', [$this, 'enqueue_frontend_scripts'], 10, 2);

        // Register our field for price calculations
        add_filter('gform_pricing_fields', [$this, 'register_pricing_field']);

        // Add support for conditional logic on pricing
        add_filter('gform_pre_render', [$this, 'pre_render_support']);

        // Stripe: add Deposit Due fields to the payment amount dropdown
        add_filter('gform_stripe_feed_settings_fields', [$this, 'stripe_feed_settings_fields'], 10, 2);

        add_filter('gform_addon_feed_settings_fields', [$this, 'addon_feed_settings_fields'], 10, 2);

        // Payment processing: override payment amount when a Deposit Due field is selected
        add_filter('gform_submission_data_pre_process_payment', [$this, 'submission_data_pre_process_payment'], 10, 4);
    }

    /**
     * Add Deposit Due fields to the Stripe feed payment amount dropdown.
     *
     * @param array $fields Feed settings fields.
     * @param array $form   Form object.
     * @return array
     */
    public function stripe_feed_settings_fields($fields, $form)
    {
        $deposit_fields = $this->get_deposit_total_fields($form);

        if (empty($deposit_fields)) {
            return $fields;
        }

        foreach ($fields as &$section) {
            if (!isset($section['fields']) || !is_array($section['fields'])) {
                continue;
            }

            foreach ($section['fields'] as &$field) {
                $name = rgar($field, 'name');
                if (!in_array($name, ['paymentAmount', 'paymentAmountField'], true)) {
                    continue;
                }

                $choices = rgar($field, 'choices');
                if (!is_array($choices)) {
                    $choices = [];
                }

                foreach ($deposit_fields as $deposit_field) {
                    $choices[] = [
                        'label' => $deposit_field->label,
                        'value' => (string) $deposit_field->id,
                    ];
                }

                $field['choices'] = $choices;
            }
        }

        return $fields;
    }

    public function addon_feed_settings_fields($fields, $addon)
    {
        if (!is_object($addon) || !method_exists($addon, 'get_slug')) {
            return $fields;
        }

        if ($addon->get_slug() !== 'gravityformsstripe') {
            return $fields;
        }

        $form_id = absint(rgget('id'));
        if (!$form_id) {
            return $fields;
        }

        $form = GFAPI::get_form($form_id);
        if (!$form || is_wp_error($form)) {
            return $fields;
        }

        return $this->stripe_feed_settings_fields($fields, $form);
    }

    /**
     * Override the payment_amount for payment add-ons when a Deposit Due field is selected.
     *
     * @param array $submission_data Submission data.
     * @param array $feed            Feed object.
     * @param array $form            Form object.
     * @param array $entry           Entry object.
     * @return array
     */
    public function submission_data_pre_process_payment($submission_data, $feed, $form, $entry)
    {
        $payment_amount_setting = rgars($feed, 'meta/paymentAmount');
        if (empty($payment_amount_setting)) {
            return $submission_data;
        }

        $deposit_fields = $this->get_deposit_total_fields($form);
        if (empty($deposit_fields)) {
            return $submission_data;
        }

        $deposit_field_ids = array_map(static function ($field) {
            return (string) $field->id;
        }, $deposit_fields);

        if (!in_array((string) $payment_amount_setting, $deposit_field_ids, true)) {
            return $submission_data;
        }

        $deposit_field = null;
        foreach ($deposit_fields as $field) {
            if ((string) $field->id === (string) $payment_amount_setting) {
                $deposit_field = $field;
                break;
            }
        }

        if (!$deposit_field) {
            return $submission_data;
        }

        $percent_raw = isset($deposit_field->depositPercent) ? $deposit_field->depositPercent : '';
        $percent = $this->parse_percentage($percent_raw);
        if ($percent <= 0) {
            return $submission_data;
        }

        $order = GFCommon::get_product_info($form, $entry);
        $total = rgar($order, 'total');
        $total = GFCommon::to_number($total);

        $deposit_amount = round($total * ($percent / 100), 2);
        $submission_data['payment_amount'] = $deposit_amount;

        return $submission_data;
    }

    private function get_deposit_total_fields($form)
    {
        $fields = [];

        if (!is_array(rgar($form, 'fields'))) {
            return $fields;
        }

        foreach ($form['fields'] as $field) {
            if (is_object($field) && $field->type === 'deposit_total') {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    private function parse_percentage($value)
    {
        if (is_array($value)) {
            return 0;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = str_replace('%', '', $value);
        $value = GFCommon::to_number($value);

        return floatval($value);
    }

    /**
     * Register checkbox_product as a pricing field type
     *
     * @param array $pricing_fields Existing pricing field types
     * @return array Modified pricing field types
     */
    public function register_pricing_field($pricing_fields)
    {
        if (!in_array('checkbox_product', $pricing_fields, true)) {
            $pricing_fields[] = 'checkbox_product';
        }

        if (!in_array('deposit_total', $pricing_fields, true)) {
            $pricing_fields[] = 'deposit_total';
        }

        if (!in_array('fees', $pricing_fields, true)) {
            $pricing_fields[] = 'fees';
        }

        return $pricing_fields;
    }

    /**
     * Add selected checkbox products to the order
     *
     * This is the main hook that integrates with GF's pricing system
     * and payment gateways (Stripe, PayPal, etc.)
     *
     * @param array $product_info Product information
     * @param array $form         Form object
     * @param array $entry        Entry object
     * @return array Modified product information
     */
    public function add_checkbox_products_to_order($product_info, $form, $entry)
    {
        if (!is_array($form['fields'])) {
            return $product_info;
        }

        foreach ($form['fields'] as $field) {
            if (!is_object($field)) {
                continue;
            }

            // Process checkbox product fields
            if ($field->type === 'checkbox_product') {
                $field_id = $field->id;
                $selected_values = rgar($entry, $field_id);

                // Skip if no selections
                if (empty($selected_values)) {
                    continue;
                }

                // Parse selected values
                $selected = is_array($selected_values)
                    ? $selected_values
                    : explode(',', $selected_values);

                // Remove empty values
                $selected = array_filter($selected);

                if (empty($selected)) {
                    continue;
                }

                // Add each selected item as a product
                $this->add_selected_products($product_info, $field, $selected, $entry);
            }

            // Process fees fields
            if ($field->type === 'fees') {
                $this->add_fees_to_order($product_info, $field, $entry);
            }
        }

        return $product_info;
    }

    /**
     * Add selected products to product info array
     *
     * @param array  $product_info Product information (passed by reference)
     * @param object $field        Field object
     * @param array  $selected     Selected values
     * @param array  $entry        Entry object
     * @return void
     */
    private function add_selected_products(&$product_info, $field, $selected, $entry)
    {
        if (!is_array($field->choices)) {
            return;
        }

        $field_id = $field->id;

        foreach ($field->choices as $index => $choice) {
            $choice_value = rgar($choice, 'value', '');

            if (!in_array($choice_value, $selected, true)) {
                continue;
            }

            // Get price and ensure it's a number
            $price = GFCommon::to_number(rgar($choice, 'price', 0));

            // Create unique product key
            $product_key = 'checkbox_' . $field_id . '_' . $index;

            // Add to products array
            $product_info['products'][$product_key] = [
                'name'       => rgar($choice, 'text', ''),
                'price'      => $price,
                'quantity'   => 1,
                'options'    => [],
                'is_shipping' => false,
                'field_id'   => $field_id,
            ];
        }
    }

    /**
     * Add fees to product info array
     *
     * @param array  $product_info Product information (passed by reference)
     * @param object $field        Field object
     * @param array  $entry        Entry object
     * @return void
     */
    private function add_fees_to_order(&$product_info, $field, $entry)
    {
        if (!isset($field->fees) || !is_array($field->fees)) {
            return;
        }

        $field_id = $field->id;

        foreach ($field->fees as $index => $fee) {
            $label = isset($fee['label']) ? $fee['label'] : '';
            $price = isset($fee['price']) ? GFCommon::to_number($fee['price']) : 0;

            if (empty($label) || $price <= 0) {
                continue;
            }

            $product_key = 'fee_' . $field_id . '_' . $index;

            $product_info['products'][$product_key] = [
                'name'       => $label,
                'price'      => $price,
                'quantity'   => 1,
                'options'    => [],
                'is_shipping' => false,
                'field_id'   => $field_id,
            ];
        }
    }

    /**
     * Enqueue frontend scripts for price calculation
     *
     * @param array $form    Form object
     * @param bool  $is_ajax Whether form is AJAX-enabled
     * @return void
     */
    public function enqueue_frontend_scripts($form, $is_ajax)
    {
        // Check if form has our field type
        if (!$this->form_has_supported_pricing_field($form)) {
            return;
        }

        // Enqueue frontend JavaScript
        wp_enqueue_script(
            'gf-checkbox-products-frontend',
            CHECPRFO_URL . 'assets/js/frontend.js',
            ['jquery', 'gform_gravityforms'],
            CHECPRFO_VERSION,
            true
        );

        // Localize script with form-specific data
        $this->localize_frontend_script($form);

        // Enqueue frontend CSS
        wp_enqueue_style(
            'gf-checkbox-products-frontend',
            CHECPRFO_URL . 'assets/css/frontend.css',
            [],
            CHECPRFO_VERSION
        );
    }

    /**
     * Check if form has checkbox product field
     *
     * @param array $form Form object
     * @return bool
     */
    private function form_has_checkbox_product_field($form)
    {
        if (!is_array($form['fields'])) {
            return false;
        }

        foreach ($form['fields'] as $field) {
            if (!is_object($field)) {
                continue;
            }

            if ($field->type === 'checkbox_product' || $field->type === 'deposit_total' || $field->type === 'fees') {
                return true;
            }
        }

        return false;
    }

    private function form_has_supported_pricing_field($form)
    {
        return $this->form_has_checkbox_product_field($form);
    }

    /**
     * Localize frontend script with form data
     *
     * @param array $form Form object
     * @return void
     */
    private function localize_frontend_script($form)
    {
        $form_id = absint($form['id']);
        $currency = rgar($form, 'currency', GFCommon::get_currency());

        wp_localize_script(
            'gf-checkbox-products-frontend',
            'gfCheckboxProducts_' . $form_id,
            [
                'formId'   => $form_id,
                'currency' => $currency,
                'debug'    => defined('WP_DEBUG') && WP_DEBUG,
            ]
        );
    }

    /**
     * Pre-render support for conditional logic
     *
     * @param array $form Form object
     * @return array Modified form object
     */
    public function pre_render_support($form)
    {
        // Add any pre-render modifications if needed
        // This can be used for conditional logic support
        return $form;
    }

    /**
     * Calculate total for checkbox products (helper method)
     *
     * @param array  $form  Form object
     * @param array  $entry Entry object
     * @param string $field_id Field ID (optional, calculates all if not provided)
     * @return float Total price
     */
    public static function calculate_total($form, $entry, $field_id = null)
    {
        $total = 0;

        if (!is_array($form['fields'])) {
            return $total;
        }

        foreach ($form['fields'] as $field) {
            if (!is_object($field) || $field->type !== 'checkbox_product') {
                continue;
            }

            // If specific field ID provided, only calculate for that field
            if ($field_id !== null && $field->id != $field_id) {
                continue;
            }

            $selected_values = rgar($entry, $field->id);

            if (empty($selected_values)) {
                continue;
            }

            $selected = is_array($selected_values)
                ? $selected_values
                : explode(',', $selected_values);

            if (is_array($field->choices)) {
                foreach ($field->choices as $choice) {
                    if (in_array(rgar($choice, 'value'), $selected, true)) {
                        $total += floatval(rgar($choice, 'price', 0));
                    }
                }
            }
        }

        return $total;
    }

    /**
     * Get product details for entry
     *
     * @param array $form  Form object
     * @param array $entry Entry object
     * @return array Product details
     */
    public static function get_entry_products($form, $entry)
    {
        $products = [];

        if (!is_array($form['fields'])) {
            return $products;
        }

        foreach ($form['fields'] as $field) {
            if (!is_object($field) || $field->type !== 'checkbox_product') {
                continue;
            }

            $selected_values = rgar($entry, $field->id);

            if (empty($selected_values)) {
                continue;
            }

            $selected = is_array($selected_values)
                ? $selected_values
                : explode(',', $selected_values);

            if (is_array($field->choices)) {
                foreach ($field->choices as $choice) {
                    if (in_array(rgar($choice, 'value'), $selected, true)) {
                        $products[] = [
                            'name'     => rgar($choice, 'text', ''),
                            'price'    => floatval(rgar($choice, 'price', 0)),
                            'quantity' => 1,
                            'field_id' => $field->id,
                        ];
                    }
                }
            }
        }

        return $products;
    }
}
