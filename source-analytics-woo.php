<?php
/**
 * Plugin Name: Source Analytics Woo
 * Plugin URI:  https://wordpress.org/plugins/source-analytics-woo
 * Description: Learn which traffic sources and marketing channels are generating the most revenue
 * Version: 1.0.0
 * Author: Tag Pilot
 * Author URI: https://tagpilot.io
 * Text Domain: source-analytics-woo
 * Requires at least: 5.6
 * Tested up to: 6.8
 * WC requires at least: 5.7.0
 * WC tested up to: 9.9
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SOURCE_ANALYTICS_WOO_VERSION', '1.0.0');
define('SOURCE_ANALYTICS_WOO_PLUGIN_FILE', __FILE__);
define('SOURCE_ANALYTICS_WOO_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SOURCE_ANALYTICS_WOO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SOURCE_ANALYTICS_WOO_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class Source_Analaytics_Woo {
    
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
            <p><?php esc_html_e('WooCommerce Custom Analytics requires WooCommerce to be installed and active.', 'source-analytics-woo'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_source_analytics_woo_data', array($this, 'get_revenue_analytics'));

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
        $script_asset_path = SOURCE_ANALYTICS_WOO_PLUGIN_PATH . 'build/index.asset.php';
        $script_info = file_exists($script_asset_path) 
            ? require($script_asset_path)
            : array('dependencies' => array(), 'version' => SOURCE_ANALYTICS_WOO_VERSION);
        
        $script_url = SOURCE_ANALYTICS_WOO_PLUGIN_URL . 'build/index.js';

        $dependencies = array(
            'wc-components'
        );
        
        wp_register_script(
            'source-analytics-woo-script',
            $script_url,
            array_merge($script_info['dependencies'], $dependencies),
            $script_info['version'],
            true
        );
        
        wp_enqueue_script('source-analytics-woo-script');
        
        // Enqueue styles
        $style_path = SOURCE_ANALYTICS_WOO_PLUGIN_URL . 'build/index.css';
        if (file_exists(SOURCE_ANALYTICS_WOO_PLUGIN_PATH . 'build/index.css')) {
            wp_enqueue_style(
                'source-analytics-woo-style',
                $style_path,
                array(),
                $script_info['version']
            );
        }
        
        // Localize script with data
        wp_localize_script('source-analytics-woo-script', 'sourceAnalyticsWoo', array(
            'ajaxUrl'    => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('source_analytics_woo_nonce'),
            'restUrl'    => rest_url('wc/v3/'),
            'restNonce'  => wp_create_nonce('wp_rest'),
            'currency'   => get_woocommerce_currency(),
            'dateFormat' => wc_date_format(),
            'strings'    => array(
                'loading'      => __('Loading...', 'source-analytics-woo'),
                'error'        => __('Error loading data', 'source-analytics-woo'),
                'noData'       => __('No data available', 'source-analytics-woo'),
                'totalSales'   => __('Total Sales', 'source-analytics-woo'),
                'totalOrders'  => __('Total Orders', 'source-analytics-woo'),
                'avgOrderValue' => __('Average Order Value', 'source-analytics-woo'),
            ),
        ));
    }


    public function get_revenue_analytics() {
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'source_analytics_woo_nonce')) {
            wp_die('Security check failed');
        }

        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Insufficient permissions');
        }

        $end_date = isset($_POST['end_date']) ? gmdate('Y-m-d H:i:s', strtotime(sanitize_text_field(wp_unslash($_POST['end_date'])))) : null;
        $start_date = isset($_POST['start_date']) ? gmdate('Y-m-d H:i:s', strtotime(sanitize_text_field(wp_unslash($_POST['start_date'])))) : null;

        // Get orders with attribution data
        $orders_data = $this->get_orders_with_attribution($start_date, $end_date);

        if (empty($orders_data)) {
            wp_send_json_error('No orders found for the selected period');
        }

        // Process data for analytics
        $analytics_data = $this->process_orders_data($orders_data);

        wp_send_json_success($analytics_data);
    }

    private function get_orders_with_attribution($start_date, $end_date) {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT
                p.ID as order_id,
                p.post_date,
                om_total.meta_value as order_total,
                om_source_type.meta_value as source_type,
                om_source.meta_value as source,
                om_medium.meta_value as medium,
                om_campaign.meta_value as campaign,
                om_device.meta_value as device_type,
                om_referrer.meta_value as referrer
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} om_total ON p.ID = om_total.post_id AND om_total.meta_key = '_order_total'
            LEFT JOIN {$wpdb->postmeta} om_source_type ON p.ID = om_source_type.post_id AND om_source_type.meta_key = '_wc_order_attribution_source_type'
            LEFT JOIN {$wpdb->postmeta} om_source ON p.ID = om_source.post_id AND om_source.meta_key = '_wc_order_attribution_utm_source'
            LEFT JOIN {$wpdb->postmeta} om_medium ON p.ID = om_medium.post_id AND om_medium.meta_key = '_wc_order_attribution_utm_medium'
            LEFT JOIN {$wpdb->postmeta} om_campaign ON p.ID = om_campaign.post_id AND om_campaign.meta_key = '_wc_order_attribution_utm_campaign'
            LEFT JOIN {$wpdb->postmeta} om_device ON p.ID = om_device.post_id AND om_device.meta_key = '_wc_order_attribution_device_type'
            LEFT JOIN {$wpdb->postmeta} om_referrer ON p.ID = om_referrer.post_id AND om_referrer.meta_key = '_wc_order_attribution_referrer'
            WHERE p.post_type = 'shop_order'
            AND p.post_date >= %s
            AND p.post_date <= %s
            AND p.post_status = 'wc-completed'
            AND om_total.meta_value IS NOT NULL
            ORDER BY p.post_date DESC
        ", $start_date, $end_date));

        return $results;
    }

    private function process_orders_data($orders_data) {
        $revenue_by_source = array();
        $orders_by_source = array();
        $daily_revenue = array();

        foreach ($orders_data as $order) {
            $order_total = round(floatval($order->order_total));
            // Clean up source data
            $source = (empty($order->source) ? "unknown" : $order->source)
                . ' / '
                . (empty($order->medium) ? 'unknown' : $order->medium);

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
            $date = gmdate('Y-m-d', strtotime($order->post_date));
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

        // Get top 10 sources by revenue
        $top_10_sources = array_slice($revenue_by_source, 0, 10, true);
        $top_10_source_keys = array_keys($top_10_sources);

        // Create simple source summary array (top 10 only)
        $source_summary = array();
        foreach ($top_10_sources as $source => $revenue) {
            $source_summary[] = array(
                'source' => $source,
                'orders' => intval($orders_by_source[$source]),
                'revenue' => floatval($revenue)
            );
        }

        return array(
            'revenue_trends' => $this->format_revenue_trends($daily_revenue, $top_10_source_keys),
            'source_summary' => $source_summary
        );
    }

    private function format_revenue_trends($daily_revenue, $top_10_source_keys) {
        // Sort dates
        $dates = array_keys($daily_revenue);
        sort($dates);

        // Format data according to the required structure (top 10 sources only)
        $formatted_data = array();

        foreach ($dates as $date) {
            $date_entry = array(
                'date' => gmdate('Y-m-d\TH:i:s', strtotime($date))
            );

            // Add each top 10 source as a property with label and value
            foreach ($top_10_source_keys as $source) {
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

Source_Analaytics_Woo::get_instance();
