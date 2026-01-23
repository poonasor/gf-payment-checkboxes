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
