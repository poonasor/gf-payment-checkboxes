<?php

/**
 * Admin Interface Class
 *
 * Handles admin-side functionality including field settings UI
 *
 * @package GF_Checkbox_Products
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin functionality for Checkbox Product field
 */
class CHECPRFO_Admin
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Add custom settings UI to form editor
        add_action('gform_field_standard_settings', [$this, 'field_settings_ui'], 10, 2);

        // Enqueue admin scripts and styles
        add_action('gform_editor_js', [$this, 'editor_js']);

        // Add field-specific tooltips
        add_filter('gform_tooltips', [$this, 'add_tooltips']);

        // Add custom field icon (optional)
        add_filter('gform_field_type_title', [$this, 'field_type_title'], 10, 2);
    }

    /**
     * Add custom settings panel for product choices
     *
     * @param int $position Position in settings panel
     * @param int $form_id  Form ID
     * @return void
     */
    public function field_settings_ui($position, $form_id)
    {
        // Add settings at position 25 (after standard choices setting)
        if ($position !== 25) {
            return;
        }
?>
        <li class="deposit_total_percent_setting field_setting">
            <label class="section_label" for="field_deposit_total_percent">
                <?php esc_html_e('Deposit Percentage', 'checkbox-products-for-gravity-forms'); ?>
                <?php gform_tooltip('form_field_deposit_total_percent'); ?>
            </label>
            <input type="text" id="field_deposit_total_percent" onkeyup="SetFieldProperty('depositPercent', this.value);" onchange="SetFieldProperty('depositPercent', this.value);" />
        </li>

        <li class="checkbox_product_choices_setting field_setting">
            <label class="section_label" for="checkbox_product_choices_container">
                <?php esc_html_e('Product Choices', 'checkbox-products-for-gravity-forms'); ?>
                <?php gform_tooltip('form_field_checkbox_product_choices'); ?>
            </label>

            <div id="checkbox_product_choices_container" class="gf-checkbox-products-choices">
                <!-- Dynamic choices will be populated via JavaScript -->
            </div>

            <button type="button" class="button gf-add-checkbox-product-choice" style="margin-top: 10px;" onclick="gfCheckboxProductAddChoice(); return false;">
                <?php esc_html_e('Add Product Choice', 'checkbox-products-for-gravity-forms'); ?>
            </button>

            <p class="description" style="margin-top: 10px;">
                <?php esc_html_e('Add product choices with individual prices. Each checkbox can have a different price.', 'checkbox-products-for-gravity-forms'); ?>
            </p>
        </li>

        <li class="fees_setting field_setting">
            <label class="section_label" for="fees_container">
                <?php esc_html_e('Fees', 'checkbox-products-for-gravity-forms'); ?>
                <?php gform_tooltip('form_field_fees'); ?>
            </label>

            <div id="fees_container" class="gf-fees-container">
                <!-- Dynamic fees will be populated via JavaScript -->
            </div>

            <button type="button" class="button gf-add-fee" style="margin-top: 10px;" onclick="gfAddFee(); return false;">
                <?php esc_html_e('Add Fee', 'checkbox-products-for-gravity-forms'); ?>
            </button>

            <p class="description" style="margin-top: 10px;">
                <?php esc_html_e('Add fees with labels and prices (e.g., Travel Fee, Processing Fee). These will be added to the form total.', 'checkbox-products-for-gravity-forms'); ?>
            </p>
        </li>

        <li class="distance_pricing_settings field_setting">
            <label class="section_label">
                <?php esc_html_e('Distance Pricing Configuration', 'checkbox-products-for-gravity-forms'); ?>
                <?php gform_tooltip('form_field_distance_pricing'); ?>
            </label>

            <div style="margin-bottom: 15px;">
                <label for="field_distance_price_per_unit" style="display: block; margin-bottom: 5px;">
                    <?php esc_html_e('Price per Mile/KM', 'checkbox-products-for-gravity-forms'); ?>
                </label>
                <input type="text" id="field_distance_price_per_unit" class="fieldwidth-3" onkeyup="SetFieldProperty('distancePricePerUnit', this.value);" onchange="SetFieldProperty('distancePricePerUnit', this.value);" />
            </div>

            <div style="margin-bottom: 15px;">
                <label for="field_distance_starting_location" style="display: block; margin-bottom: 5px;">
                    <?php esc_html_e('Starting Location (Postal/Zip Code)', 'checkbox-products-for-gravity-forms'); ?>
                </label>
                <input type="text" id="field_distance_starting_location" class="fieldwidth-3" onkeyup="SetFieldProperty('distanceStartingLocation', this.value);" onchange="SetFieldProperty('distanceStartingLocation', this.value);" placeholder="<?php esc_attr_e('e.g., 90210 or M5H 2N2', 'checkbox-products-for-gravity-forms'); ?>" />
            </div>

            <div style="margin-bottom: 15px;">
                <label for="field_distance_free_zone" style="display: block; margin-bottom: 5px;">
                    <?php esc_html_e('Free Zone Distance', 'checkbox-products-for-gravity-forms'); ?>
                </label>
                <input type="text" id="field_distance_free_zone" class="fieldwidth-3" onkeyup="SetFieldProperty('distanceFreeZone', this.value);" onchange="SetFieldProperty('distanceFreeZone', this.value);" placeholder="<?php esc_attr_e('e.g., 10', 'checkbox-products-for-gravity-forms'); ?>" />
                <p class="description"><?php esc_html_e('Distance within this zone is free. Charges apply beyond this distance.', 'checkbox-products-for-gravity-forms'); ?></p>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="field_distance_unit_type" style="display: block; margin-bottom: 5px;">
                    <?php esc_html_e('Distance Unit', 'checkbox-products-for-gravity-forms'); ?>
                </label>
                <select id="field_distance_unit_type" onchange="SetFieldProperty('distanceUnitType', this.value);">
                    <option value="miles"><?php esc_html_e('Miles', 'checkbox-products-for-gravity-forms'); ?></option>
                    <option value="kilometers"><?php esc_html_e('Kilometers', 'checkbox-products-for-gravity-forms'); ?></option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="field_distance_address_field" style="display: block; margin-bottom: 5px;">
                    <?php esc_html_e('Link to Address Field', 'checkbox-products-for-gravity-forms'); ?>
                </label>
                <select id="field_distance_address_field" onchange="SetFieldProperty('distanceAddressField', this.value);">
                    <option value=""><?php esc_html_e('Select an Address Field', 'checkbox-products-for-gravity-forms'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('Select the address field that users will fill in to calculate distance.', 'checkbox-products-for-gravity-forms'); ?></p>
            </div>
        </li>
<?php
    }

    /**
     * Enqueue admin JavaScript and CSS
     *
     * @return void
     */
    public function editor_js()
    {
        // Enqueue admin JavaScript
        wp_enqueue_script(
            'gf-checkbox-products-admin',
            CHECPRFO_URL . 'assets/js/admin.js',
            ['jquery', 'gform_form_editor'],
            CHECPRFO_VERSION,
            true
        );

        // Localize script with translations and settings
        wp_localize_script(
            'gf-checkbox-products-admin',
            'gfCheckboxProductsAdmin',
            [
                'i18n' => [
                    'confirmDelete' => esc_html__('Are you sure you want to delete this choice?', 'checkbox-products-for-gravity-forms'),
                    'labelPlaceholder' => esc_attr__('Product Name', 'checkbox-products-for-gravity-forms'),
                    'pricePlaceholder' => esc_attr__('0.00', 'checkbox-products-for-gravity-forms'),
                    'valuePlaceholder' => esc_attr__('value', 'checkbox-products-for-gravity-forms'),
                ],
                'currency' => GFCommon::get_currency(),
            ]
        );

        // Enqueue admin CSS
        wp_enqueue_style(
            'gf-checkbox-products-admin',
            CHECPRFO_URL . 'assets/css/admin.css',
            [],
            CHECPRFO_VERSION
        );
    }

    /**
     * Add tooltips for field settings
     *
     * @param array $tooltips Existing tooltips
     * @return array Modified tooltips
     */
    public function add_tooltips($tooltips)
    {
        $tooltips['form_field_checkbox_product_choices'] = sprintf(
            '<h6>%s</h6>%s',
            esc_html__('Product Choices', 'checkbox-products-for-gravity-forms'),
            esc_html__('Add the products you want users to select from. Each product can have its own price. The selected products will be added to the form total.', 'checkbox-products-for-gravity-forms')
        );

        $tooltips['form_field_deposit_total_percent'] = sprintf(
            '<h6>%s</h6>%s',
            esc_html__('Deposit Percentage', 'checkbox-products-for-gravity-forms'),
            esc_html__('Enter a percentage (e.g. 10% or 50) to calculate the deposit amount from the form total.', 'checkbox-products-for-gravity-forms')
        );

        $tooltips['form_field_fees'] = sprintf(
            '<h6>%s</h6>%s',
            esc_html__('Fees', 'checkbox-products-for-gravity-forms'),
            esc_html__('Add fees with labels and prices (e.g., Travel Fee, Processing Fee). These fees will be automatically added to the form total.', 'checkbox-products-for-gravity-forms')
        );

        $tooltips['form_field_distance_pricing'] = sprintf(
            '<h6>%s</h6>%s',
            esc_html__('Distance Pricing', 'checkbox-products-for-gravity-forms'),
            esc_html__('Calculate pricing based on distance from a starting location. Set your price per mile/km, starting location, and free zone distance. The field will automatically calculate charges for distances beyond the free zone.', 'checkbox-products-for-gravity-forms')
        );

        return $tooltips;
    }

    /**
     * Customize field type title
     *
     * @param string $title Field title
     * @param string $type  Field type
     * @return string Modified title
     */
    public function field_type_title($title, $type)
    {
        if ($type === 'checkbox_product') {
            return esc_html__('Checkbox Products', 'checkbox-products-for-gravity-forms');
        }

        return $title;
    }
}
