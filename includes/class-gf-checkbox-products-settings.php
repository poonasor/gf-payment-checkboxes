<?php

/**
 * Settings Page Class
 *
 * Handles plugin settings including Google Maps API key configuration
 *
 * @package GF_Checkbox_Products
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings functionality for Checkbox Products plugin
 */
class CHECPRFO_Settings
{
    private static $instance;

    /**
     * Constructor
     */
    public function __construct()
    {
        self::$instance = $this;

        if (class_exists('GFForms') && is_callable(['GFForms', 'add_settings_page'])) {
            GFForms::add_settings_page(
                esc_html__('Checkbox Products', 'checkbox-products-for-gravity-forms'),
                ['CHECPRFO_Settings', 'settings_page'],
                'checkbox_products'
            );
        } else {
            add_filter('gform_settings_menu', [$this, 'add_settings_menu'], 5);
        }

        // Register settings
        add_action('admin_init', [$this, 'register_settings']);

        add_action('gform_settings_checkbox_products', ['CHECPRFO_Settings', 'settings_page'], 10, 1);
    }

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add settings menu item to Gravity Forms settings
     *
     * @param array $menu_items Existing menu items
     * @return array Modified menu items
     */
    public function add_settings_menu($menu_items)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $keys = array_keys($menu_items);
            $keys_preview = array_slice($keys, 0, 20);
            error_log('CHECPRFO_Settings::add_settings_menu keys: ' . wp_json_encode($keys_preview));
        }

        $menu_item = [
            'name'  => 'checkbox_products',
            'label' => esc_html__('Checkbox Products', 'checkbox-products-for-gravity-forms'),
            'callback' => ['CHECPRFO_Settings', 'settings_page'],
        ];

        if (isset($menu_items['checkbox_products'])) {
            return $menu_items;
        }

        $keys = array_keys($menu_items);
        $is_numeric_array = $keys === array_keys($keys);

        if ($is_numeric_array) {
            array_splice($menu_items, 1, 0, [$menu_item]);
            return $menu_items;
        }

        $new_menu_items = [];
        $i = 0;
        foreach ($menu_items as $key => $item) {
            if ($i === 1) {
                $new_menu_items['checkbox_products'] = $menu_item;
            }
            $new_menu_items[$key] = $item;
            $i++;
        }

        if (!isset($new_menu_items['checkbox_products'])) {
            $new_menu_items['checkbox_products'] = $menu_item;
        }

        return $new_menu_items;
    }

    /**
     * Static callback for settings page rendering
     */
    public static function settings_page($subview = '')
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CHECPRFO_Settings::settings_page fired');
        }

        self::instance()->render_settings_page();
    }

    /**
     * Register plugin settings
     */
    public function register_settings()
    {
        register_setting(
            'checprfo_settings_group',
            'checprfo_google_maps_api_key',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );
    }

    /**
     * Render settings page content
     */
    public function render_settings_page($subview = '')
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CHECPRFO_Settings::render_settings_page fired');
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'checkbox-products-for-gravity-forms'));
        }

        // Handle form submission
        if (isset($_POST['checprfo_save_settings']) && check_admin_referer('checprfo_settings_nonce', 'checprfo_settings_nonce_field')) {
            $api_key = isset($_POST['checprfo_google_maps_api_key']) ? sanitize_text_field($_POST['checprfo_google_maps_api_key']) : '';
            update_option('checprfo_google_maps_api_key', $api_key);

            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'checkbox-products-for-gravity-forms') . '</p></div>';
        }

        $api_key = get_option('checprfo_google_maps_api_key', '');
?>
        <h3 style="margin-top: 0;"><?php esc_html_e('Google Maps API Configuration', 'checkbox-products-for-gravity-forms'); ?></h3>

        <form method="post" action="">
            <?php wp_nonce_field('checprfo_settings_nonce', 'checprfo_settings_nonce_field'); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="checprfo_google_maps_api_key">
                                <?php esc_html_e('Google Maps API Key', 'checkbox-products-for-gravity-forms'); ?>
                            </label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="checprfo_google_maps_api_key"
                                name="checprfo_google_maps_api_key"
                                value="<?php echo esc_attr($api_key); ?>"
                                class="large-text"
                                placeholder="<?php esc_attr_e('Enter your Google Maps API key', 'checkbox-products-for-gravity-forms'); ?>" />
                            <p class="description">
                                <?php esc_html_e('Required for Distance Pricing field. Enter your Google Maps API key with the following APIs enabled:', 'checkbox-products-for-gravity-forms'); ?>
                            </p>
                            <ul style="list-style: disc; margin-left: 20px; margin-top: 10px;">
                                <li><strong><?php esc_html_e('Distance Matrix API', 'checkbox-products-for-gravity-forms'); ?></strong> - <?php esc_html_e('Required for calculating distances', 'checkbox-products-for-gravity-forms'); ?></li>
                                <li><strong><?php esc_html_e('Geocoding API', 'checkbox-products-for-gravity-forms'); ?></strong> - <?php esc_html_e('Required for converting addresses to coordinates', 'checkbox-products-for-gravity-forms'); ?></li>
                                <li><strong><?php esc_html_e('Places API', 'checkbox-products-for-gravity-forms'); ?></strong> - <?php esc_html_e('Optional, for address autocomplete', 'checkbox-products-for-gravity-forms'); ?></li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 20px; padding: 20px; background: #f9f9f9; border-left: 4px solid #0073aa;">
                <h3><?php esc_html_e('How to Get Your Google Maps API Key', 'checkbox-products-for-gravity-forms'); ?></h3>
                <ol style="margin-left: 20px;">
                    <li><?php esc_html_e('Go to the Google Cloud Console:', 'checkbox-products-for-gravity-forms'); ?> <a href="https://console.cloud.google.com/" target="_blank">https://console.cloud.google.com/</a></li>
                    <li><?php esc_html_e('Create a new project or select an existing one', 'checkbox-products-for-gravity-forms'); ?></li>
                    <li><?php esc_html_e('Navigate to "APIs & Services" > "Library"', 'checkbox-products-for-gravity-forms'); ?></li>
                    <li><?php esc_html_e('Enable the following APIs:', 'checkbox-products-for-gravity-forms'); ?>
                        <ul style="list-style: circle; margin-left: 20px; margin-top: 5px;">
                            <li><?php esc_html_e('Distance Matrix API', 'checkbox-products-for-gravity-forms'); ?></li>
                            <li><?php esc_html_e('Geocoding API', 'checkbox-products-for-gravity-forms'); ?></li>
                            <li><?php esc_html_e('Places API (optional)', 'checkbox-products-for-gravity-forms'); ?></li>
                        </ul>
                    </li>
                    <li><?php esc_html_e('Navigate to "APIs & Services" > "Credentials"', 'checkbox-products-for-gravity-forms'); ?></li>
                    <li><?php esc_html_e('Click "Create Credentials" > "API Key"', 'checkbox-products-for-gravity-forms'); ?></li>
                    <li><?php esc_html_e('Copy the API key and paste it above', 'checkbox-products-for-gravity-forms'); ?></li>
                    <li><?php esc_html_e('(Recommended) Restrict your API key to your domain for security', 'checkbox-products-for-gravity-forms'); ?></li>
                </ol>

                <p style="margin-top: 15px;">
                    <strong><?php esc_html_e('Important:', 'checkbox-products-for-gravity-forms'); ?></strong>
                    <?php esc_html_e('Google Maps API usage may incur charges. Please review Google\'s pricing at:', 'checkbox-products-for-gravity-forms'); ?>
                    <a href="https://cloud.google.com/maps-platform/pricing" target="_blank">https://cloud.google.com/maps-platform/pricing</a>
                </p>

                <p style="margin-top: 15px;">
                    <strong><?php esc_html_e('Documentation:', 'checkbox-products-for-gravity-forms'); ?></strong>
                    <?php esc_html_e('For detailed setup instructions, see the DISTANCE-PRICING-SETUP.md file in the plugin directory.', 'checkbox-products-for-gravity-forms'); ?>
                </p>
            </div>

            <p class="submit">
                <button type="submit" name="checprfo_save_settings" class="primary button large">
                    <?php echo esc_html__('Save Settings  →', 'checkbox-products-for-gravity-forms'); ?>
                </button>
            </p>
        </form>
<?php
    }

    /**
     * Get the Google Maps API key
     *
     * @return string API key or empty string
     */
    public static function get_google_maps_api_key()
    {
        return get_option('checprfo_google_maps_api_key', '');
    }

    /**
     * Check if Google Maps API key is configured
     *
     * @return bool
     */
    public static function has_google_maps_api_key()
    {
        $api_key = self::get_google_maps_api_key();
        return !empty($api_key);
    }
}
