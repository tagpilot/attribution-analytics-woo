<?php
/**
 * Plugin Name: Source Analytics for WooCommerce
 * Plugin URI: https://example.com/wc-custom-analytics
 * Description: Custom analytics reports for WooCommerce with React components
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wc-custom-analytics
 * Domain Path: /languages
 * Requires at least: 5.6
 * Tested up to: 6.3
 * WC requires at least: 5.7.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_CUSTOM_ANALYTICS_VERSION', '1.0.0');
define('WC_CUSTOM_ANALYTICS_PLUGIN_FILE', __FILE__);
define('WC_CUSTOM_ANALYTICS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WC_CUSTOM_ANALYTICS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WC_CUSTOM_ANALYTICS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WC_Custom_Analytics {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Load text domain
        load_plugin_textdomain('wc-custom-analytics', false, dirname(WC_CUSTOM_ANALYTICS_PLUGIN_BASENAME) . '/languages');
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Check if WooCommerce is active
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Show notice if WooCommerce is not active
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('WooCommerce Custom Analytics requires WooCommerce to be installed and active.', 'wc-custom-analytics'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wc_custom_analytics_data', array($this, 'get_revenue_analytics'));

        add_filter('woocommerce_analytics_report_menu_items', function($links) {

            array_splice($links, -1, 0, [[
                "id" => "sources",
                "title" => "Sources",
                "parent" => "woocommerce-analytics",
                "path" => "/analytics/sources",
            ]]);

            return $links;
        });
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our analytics pages
        if ('woocommerce_page_wc-admin' !== $hook) {
            return;
        }
        
        $script_path = '/build/index.js';
        $script_asset_path = WC_CUSTOM_ANALYTICS_PLUGIN_PATH . 'build/index.asset.php';
        $script_info = file_exists($script_asset_path) 
            ? require($script_asset_path)
            : array('dependencies' => array(), 'version' => WC_CUSTOM_ANALYTICS_VERSION);
        
        $script_url = WC_CUSTOM_ANALYTICS_PLUGIN_URL . 'build/index.js';

        $dependencies = array(
            'wp-element',
            'wp-i18n',
            'wp-hooks',
            'wp-api-fetch',
            'wp-components',
            'wp-date',
            'wp-url',
            'wc-components',
            'wc-currency',
            'wc-date',
            'wc-navigation',
            'wc-number',
            'wc-tracks'
        );
        
        wp_register_script(
            'wc-custom-analytics-script',
            $script_url,
            $dependencies,
            $script_info['version'],
            true
        );
        
        wp_enqueue_script('wc-custom-analytics-script');
        
        // Enqueue styles
        $style_path = WC_CUSTOM_ANALYTICS_PLUGIN_URL . 'build/index.css';
        if (file_exists(WC_CUSTOM_ANALYTICS_PLUGIN_PATH . 'build/index.css')) {
            wp_enqueue_style(
                'wc-custom-analytics-style',
                $style_path,
                array(),
                $script_info['version']
            );
        }
        
        // Localize script with data
        wp_localize_script('wc-custom-analytics-script', 'wcCustomAnalytics', array(
            'ajaxUrl'    => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('wc_custom_analytics_nonce'),
            'restUrl'    => rest_url('wc/v3/'),
            'restNonce'  => wp_create_nonce('wp_rest'),
            'currency'   => get_woocommerce_currency(),
            'dateFormat' => wc_date_format(),
            'strings'    => array(
                'loading'      => __('Loading...', 'wc-custom-analytics'),
                'error'        => __('Error loading data', 'wc-custom-analytics'),
                'noData'       => __('No data available', 'wc-custom-analytics'),
                'totalSales'   => __('Total Sales', 'wc-custom-analytics'),
                'totalOrders'  => __('Total Orders', 'wc-custom-analytics'),
                'avgOrderValue' => __('Average Order Value', 'wc-custom-analytics'),
            ),
        ));
    }


    public function get_revenue_analytics() {
        if (!wp_verify_nonce($_POST['nonce'], 'wc_custom_analytics_nonce')) {
            wp_die('Security check failed');
        }

        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Insufficient permissions');
        }

        $date_range = intval($_POST['date_range']);
        $order_status = sanitize_text_field($_POST['order_status']);
        $order_status = 'all';

        $end_date = sanitize_text_field($_POST['end_date']);
        $start_date = sanitize_text_field($_POST['start_date']);

        // Get orders with attribution data
        $orders_data = $this->get_orders_with_attribution($start_date, $end_date, $order_status);

        if (empty($orders_data)) {
            wp_send_json_error('No orders found for the selected period');
        }

        // Process data for analytics
        $analytics_data = $this->process_orders_data($orders_data);

        wp_send_json_success($analytics_data);
    }

    private function get_orders_with_attribution($start_date, $end_date, $status = 'all') {
        global $wpdb;

        // Build status condition
        $status_condition = '';
        if ($status !== 'all') {
            $status_condition = $wpdb->prepare("AND p.post_status = %s", 'wc-' . $status);
        } else {
            $status_condition = "AND p.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')";
        }

        // Query to get orders with attribution data
        $query = "
            SELECT
                p.ID as order_id,
                p.post_date,
                om_total.meta_value as order_total,
                om_source.meta_value as source,
                om_medium.meta_value as medium,
                om_campaign.meta_value as campaign,
                om_device.meta_value as device_type,
                om_page_views.meta_value as page_views
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} om_total ON p.ID = om_total.post_id AND om_total.meta_key = '_order_total'
            LEFT JOIN {$wpdb->postmeta} om_source ON p.ID = om_source.post_id AND om_source.meta_key = 'wc_order_attribution_source_type'
            LEFT JOIN {$wpdb->postmeta} om_medium ON p.ID = om_medium.post_id AND om_medium.meta_key = 'wc_order_attribution_utm_medium'
            LEFT JOIN {$wpdb->postmeta} om_campaign ON p.ID = om_campaign.post_id AND om_campaign.meta_key = 'wc_order_attribution_utm_campaign'
            LEFT JOIN {$wpdb->postmeta} om_device ON p.ID = om_device.post_id AND om_device.meta_key = 'wc_order_attribution_device_type'
            LEFT JOIN {$wpdb->postmeta} om_page_views ON p.ID = om_page_views.post_id AND om_page_views.meta_key = 'wc_order_attribution_session_pages'
            WHERE p.post_type = 'shop_order'
            AND p.post_date >= %s
            AND p.post_date <= %s
            {$status_condition}
            AND om_total.meta_value IS NOT NULL
            ORDER BY p.post_date DESC
        ";

        $results = $wpdb->get_results($wpdb->prepare($query, $start_date, $end_date));

        return $results;
    }

    private function process_orders_data($orders_data) {
        $revenue_by_source = array();
        $orders_by_source = array();
        $daily_revenue = array();

        foreach ($orders_data as $order) {
            $order_total = floatval($order->order_total);

            // Clean up source data
            $source = $this->clean_attribution_data($order->source, 'Direct');

            // Revenue by source
            if (!isset($revenue_by_source[$source])) {
                $revenue_by_source[$source] = 0;
            }
            $revenue_by_source[$source] += $order_total;

            // Orders by source
            if (!isset($orders_by_source[$source])) {
                $orders_by_source[$source] = 0;
            }
            $orders_by_source[$source]++;

            // Daily revenue for trends
            $date = date('Y-m-d', strtotime($order->post_date));
            if (!isset($daily_revenue[$date])) {
                $daily_revenue[$date] = array();
            }
            if (!isset($daily_revenue[$date][$source])) {
                $daily_revenue[$date][$source] = 0;
            }
            $daily_revenue[$date][$source] += $order_total;
        }

        // Sort arrays by revenue (descending)
        arsort($revenue_by_source);

        // Create simple source summary array
        $source_summary = array();
        foreach ($revenue_by_source as $source => $revenue) {
            $source_summary[] = array(
                'source' => $source,
                'orders' => intval($orders_by_source[$source]),
                'revenue' => floatval($revenue)
            );
        }

        return array(
            'revenue_trends' => $this->format_revenue_trends($daily_revenue),
            'source_summary' => $source_summary
        );
    }

    private function clean_attribution_data($value, $default = 'Unknown') {
        if (empty($value) || $value === 'null' || $value === '(none)') {
            return $default;
        }

        // Clean up common source names
        $value = ucfirst(strtolower($value));

        // Map common variations
        $source_map = array(
            'Google' => 'Google',
            'Facebook' => 'Facebook',
            'Bing' => 'Bing',
            'Yahoo' => 'Yahoo',
            'Direct' => 'Direct',
            'Organic' => 'Organic',
            'Referral' => 'Referral',
            'Email' => 'Email',
            'Social' => 'Social',
            'Cpc' => 'Paid Search',
            'Utm' => 'UTM Campaign'
        );

        return isset($source_map[$value]) ? $source_map[$value] : $value;
    }

    private function format_revenue_trends($daily_revenue) {
        // Sort dates
        $dates = array_keys($daily_revenue);
        sort($dates);

        // Get all unique sources
        $all_sources = array();
        foreach ($daily_revenue as $date => $sources_data) {
            foreach ($sources_data as $source => $revenue) {
                $all_sources[$source] = true;
            }
        }
        $all_sources = array_keys($all_sources);

        // Format data according to the required structure
        $formatted_data = array();

        foreach ($dates as $date) {
            $date_entry = array(
                'date' => date('Y-m-d\TH:i:s', strtotime($date))
            );

            // Add each source as a property with label and value
            foreach ($all_sources as $source) {
                $revenue = isset($daily_revenue[$date][$source]) ? $daily_revenue[$date][$source] : 0;
                $date_entry[$source] = array(
                    'label' => $source,
                    'value' => intval($revenue) // Convert to integer as in your JS example
                );
            }

            $formatted_data[] = $date_entry;
        }

        return $formatted_data;
    }

}

// Initialize plugin
WC_Custom_Analytics::get_instance();

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'wc_custom_analytics_activate');
function wc_custom_analytics_activate() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('WooCommerce Custom Analytics requires WooCommerce to be installed and active.', 'wc-custom-analytics'));
    }
}

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, 'wc_custom_analytics_deactivate');
function wc_custom_analytics_deactivate() {
    // Cleanup tasks if needed
}