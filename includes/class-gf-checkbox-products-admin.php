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
class GF_Checkbox_Products_Admin {

    /**
     * Constructor
     */
    public function __construct() {
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
    public function field_settings_ui($position, $form_id) {
        // Add settings at position 25 (after standard choices setting)
        if ($position !== 25) {
            return;
        }
        ?>
        <li class="checkbox_product_choices_setting field_setting">
            <label class="section_label" for="checkbox_product_choices_container">
                <?php esc_html_e('Product Choices', 'gf-checkbox-products'); ?>
                <?php gform_tooltip('form_field_checkbox_product_choices'); ?>
            </label>

            <div id="checkbox_product_choices_container" class="gf-checkbox-products-choices">
                <!-- Dynamic choices will be populated via JavaScript -->
            </div>

            <button type="button" class="button gf-add-checkbox-product-choice" style="margin-top: 10px;" onclick="gfCheckboxProductAddChoice(); return false;">
                <?php esc_html_e('Add Product Choice', 'gf-checkbox-products'); ?>
            </button>

            <p class="description" style="margin-top: 10px;">
                <?php esc_html_e('Add product choices with individual prices. Each checkbox can have a different price.', 'gf-checkbox-products'); ?>
            </p>
        </li>
        <?php
    }

    /**
     * Enqueue admin JavaScript and CSS
     *
     * @return void
     */
    public function editor_js() {
        // Enqueue admin JavaScript
        wp_enqueue_script(
            'gf-checkbox-products-admin',
            GF_CHECKBOX_PRODUCTS_URL . 'assets/js/admin.js',
            ['jquery', 'gform_form_editor'],
            GF_CHECKBOX_PRODUCTS_VERSION,
            true
        );

        // Localize script with translations and settings
        wp_localize_script(
            'gf-checkbox-products-admin',
            'gfCheckboxProductsAdmin',
            [
                'i18n' => [
                    'confirmDelete' => esc_html__('Are you sure you want to delete this choice?', 'gf-checkbox-products'),
                    'labelPlaceholder' => esc_attr__('Product Name', 'gf-checkbox-products'),
                    'pricePlaceholder' => esc_attr__('0.00', 'gf-checkbox-products'),
                    'valuePlaceholder' => esc_attr__('value', 'gf-checkbox-products'),
                ],
                'currency' => GFCommon::get_currency(),
            ]
        );

        // Enqueue admin CSS
        wp_enqueue_style(
            'gf-checkbox-products-admin',
            GF_CHECKBOX_PRODUCTS_URL . 'assets/css/admin.css',
            [],
            GF_CHECKBOX_PRODUCTS_VERSION
        );
    }

    /**
     * Add tooltips for field settings
     *
     * @param array $tooltips Existing tooltips
     * @return array Modified tooltips
     */
    public function add_tooltips($tooltips) {
        $tooltips['form_field_checkbox_product_choices'] = sprintf(
            '<h6>%s</h6>%s',
            esc_html__('Product Choices', 'gf-checkbox-products'),
            esc_html__('Add the products you want users to select from. Each product can have its own price. The selected products will be added to the form total.', 'gf-checkbox-products')
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
    public function field_type_title($title, $type) {
        if ($type === 'checkbox_product') {
            return esc_html__('Checkbox Products', 'gf-checkbox-products');
        }

        return $title;
    }
}
