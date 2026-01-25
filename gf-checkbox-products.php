<?php

/**
 * Plugin Name: Checkbox Products for Gravity Forms
 * Plugin URI: https://github.com/poonasor/gf-payment-checkboxes
 * Description: Adds a checkbox-based product field to Gravity Forms for selecting multiple products with individual prices
 * Version: 1.0.0
 * Author: Ricky Poon
 * Author URI: https://perpetualmedia.ca/
 * Text Domain: checkbox-products-for-gravity-forms
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CHECPRFO_VERSION', '1.0.0');
define('CHECPRFO_PATH', plugin_dir_path(__FILE__));
define('CHECPRFO_URL', plugin_dir_url(__FILE__));
define('CHECPRFO_MIN_GF_VERSION', '2.5');

// Initialize plugin after Gravity Forms loads
add_action('gform_loaded', ['CHECPRFO_Bootstrap', 'load'], 5);

// Ensure form editor assets are enqueued early enough on admin pages.
add_action('admin_enqueue_scripts', ['CHECPRFO_Bootstrap', 'enqueue_admin_assets'], 20);

/**
 * Bootstrap class for the plugin
 *
 * Handles plugin initialization and dependency checks
 */
class CHECPRFO_Bootstrap
{

    /**
     * Load the plugin
     *
     * @return void
     */
    public static function load()
    {
        // Check if Gravity Forms is active and meets minimum version
        if (!self::is_gravityforms_supported()) {
            add_action('admin_notices', [__CLASS__, 'gf_required_notice']);
            return;
        }

        // Load required files
        self::load_files();

        // Register the custom field
        GF_Fields::register(new CHECPRFO_Field_Checkbox_Product());
        GF_Fields::register(new CHECPRFO_Field_Deposit_Total());
        GF_Fields::register(new CHECPRFO_Field_Fees());
        GF_Fields::register(new CHECPRFO_Field_Distance_Pricing());

        // Initialize admin and pricing classes
        new CHECPRFO_Admin();
        new CHECPRFO_Pricing();
        new CHECPRFO_Settings();
    }

    public static function enqueue_admin_assets($hook_suffix = '')
    {
        if (!is_string($hook_suffix) || strpos($hook_suffix, 'gf_edit_forms') === false) {
            return;
        }

        if ((defined('DOING_AJAX') && DOING_AJAX) || (function_exists('wp_doing_ajax') && wp_doing_ajax()) || (isset($_GET) && is_array($_GET) && array_key_exists('gf_ajax_save', $_GET))) {
            return;
        }

        if (!self::is_gravityforms_supported()) {
            return;
        }

        $js_ver = defined('CHECPRFO_VERSION') ? CHECPRFO_VERSION : false;
        $css_ver = defined('CHECPRFO_VERSION') ? CHECPRFO_VERSION : false;

        if (defined('CHECPRFO_PATH')) {
            $js_path = CHECPRFO_PATH . 'assets/js/admin.js';
            if (file_exists($js_path)) {
                $js_ver = filemtime($js_path);
            }

            $css_path = CHECPRFO_PATH . 'assets/css/admin.css';
            if (file_exists($css_path)) {
                $css_ver = filemtime($css_path);
            }
        }

        wp_enqueue_script(
            'gf-checkbox-products-admin',
            CHECPRFO_URL . 'assets/js/admin.js',
            ['jquery', 'gform_form_editor'],
            $js_ver,
            true
        );

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
                'currency' => class_exists('GFCommon') ? GFCommon::get_currency() : '',
            ]
        );

        wp_enqueue_style(
            'gf-checkbox-products-admin',
            CHECPRFO_URL . 'assets/css/admin.css',
            [],
            $css_ver
        );
    }

    /**
     * Check if Gravity Forms is installed and meets minimum version
     *
     * @return bool
     */
    private static function is_gravityforms_supported()
    {
        if (!class_exists('GFForms')) {
            return false;
        }

        return version_compare(GFForms::$version, CHECPRFO_MIN_GF_VERSION, '>=');
    }

    /**
     * Load required plugin files
     *
     * @return void
     */
    private static function load_files()
    {
        require_once CHECPRFO_PATH . 'includes/class-gf-field-checkbox-product.php';
        require_once CHECPRFO_PATH . 'includes/class-gf-field-deposit-total.php';
        require_once CHECPRFO_PATH . 'includes/class-gf-field-fees.php';
        require_once CHECPRFO_PATH . 'includes/class-gf-field-distance-pricing.php';
        require_once CHECPRFO_PATH . 'includes/class-gf-checkbox-products-admin.php';
        require_once CHECPRFO_PATH . 'includes/class-gf-checkbox-products-pricing.php';
        require_once CHECPRFO_PATH . 'includes/class-gf-checkbox-products-settings.php';
    }

    /**
     * Display admin notice if Gravity Forms is not available
     *
     * @return void
     */
    public static function gf_required_notice()
    {
        $message = sprintf(
            /* translators: %s: minimum Gravity Forms version */
            esc_html__('Checkbox Products for Gravity Forms requires Gravity Forms %s or higher to be installed and activated.', 'checkbox-products-for-gravity-forms'),
            CHECPRFO_MIN_GF_VERSION
        );

        echo '<div class="error"><p><strong>' . esc_html__('Checkbox Products for Gravity Forms', 'checkbox-products-for-gravity-forms') . ':</strong> ' . esc_html($message) . '</p></div>';
    }
}
