<?php
/**
 * REST API Controller for Custom Analytics
 *
 * @package WC_Custom_Analytics
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API Controller class
 */
class WC_Custom_Analytics_REST_Controller extends WP_REST_Controller {
    
    /**
     * Endpoint namespace
     */
    protected $namespace = 'wc-custom-analytics/v1';
    
    /**
     * Route base
     */
    protected $rest_base = 'analytics';
    
    /**
     * Register routes
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_analytics_data'),
                    'permission_callback' => array($this, 'get_items_permissions_check'),
                    'args'                => $this->get_collection_params(),
                ),
                'schema' => array($this, 'get_public_item_schema'),
            )
        );
        
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/summary',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array($this, 'get_summary_data'),
                    'permission_callback' => array($this, 'get_items_permissions_check'),
                    'args'                => $this->get_collection_params(),
                ),
            )
        );
    }
    
    /**
     * Check permissions for reading analytics data
     */
    public function get_items_permissions_check($request) {
        if (!current_user_can('manage_woocommerce')) {
            return new WP_Error(
                'woocommerce_rest_cannot_view',
                __('Sorry, you cannot view analytics data.', 'wc-custom-analytics'),
                array('status' => rest_authorization_required_code())
            );
        }
        
        return true;
    }
    
    /**
     * Get analytics data
     */
    public function get_analytics_data($request) {
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');
        
        // Validate dates
        if ($start_date && !$this->is_valid_date($start_date)) {
            return new WP_Error(
                'invalid_start_date',
                __('Invalid start date format. Use YYYY-MM-DD.', 'wc-custom-analytics'),
                array('status' => 400)
            );
        }
        
        if ($end_date && !$this->is_valid_date($end_date)) {
            return new WP_Error(
                'invalid_end_date',
                __('Invalid end date format. Use YYYY-MM-DD.', 'wc-custom-analytics'),
                array('status' => 400)
            );
        }
        
        try {
            $data = $this->fetch_analytics_data($start_date, $end_date);
            return rest_ensure_response($data);
        } catch (Exception $e) {
            return new WP_Error(
                'analytics_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }
    }
    
    /**
     * Get summary data only
     */
    public function get_summary_data($request) {
        $start_date = $request->get_param('start_date');
        $end_date = $request->get_param('end_date');
        
        try {
            $data = $this->fetch_analytics_data($start_date, $end_date);
            return rest_ensure_response($data['summary']);
        } catch (Exception $e) {
            return new WP_Error(
                'analytics_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }
    }
    
    /**
     * Fetch analytics data from database
     */
    private function fetch_analytics_data($start_date = '', $end_date = '') {
        global $wpdb;
        
        // Set default dates if not provided
        if (empty($start_date)) {
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }
        if (empty($end_date)) {
            $end_date = date('Y-m-d');
        }
        
        // Check if we're using HPOS (High-Performance Order Storage)
        $orders_table = $this->get_orders_table();
        $order_items_table = $this->get_order_items_table();
        
        // Get daily orders data
        $orders_query = $wpdb->prepare("
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as avg_order_value,
                DATE(date_created_gmt) as order_date
            FROM {$orders_table} 
            WHERE status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
            AND date_created_gmt >= %s 
            AND date_created_gmt <= %s
            GROUP BY DATE(date_created_gmt)
            ORDER BY order_date ASC
        ", $start_date . ' 00:00:00', $end_date . ' 23:59:59');
        
        $daily_data = $wpdb->get_results($orders_query, ARRAY_A);
        
        // Calculate totals
        $total_sales = 0;
        $total_orders = 0;
        $chart_data = array();
        
        foreach ($daily_data as $day) {
            $total_sales += floatval($day['total_sales'] ?? 0);
            $total_orders += intval($day['total_orders'] ?? 0);
            
            $chart_data[] = array(
                'date' => $day['order_date'],
                'sales' => floatval($day['total_sales'] ?? 0),
                'orders' => intval($day['total_orders'] ?? 0),
            );
        }
        
        $avg_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;
        
        // Get top products
        $top_products = $this->get_top_products($start_date, $end_date);
        
        return array(
            'summary' => array(
                'total_sales' => $total_sales,
                'total_orders' => $total_orders,
                'avg_order_value' => $avg_order_value,
            ),
            'chart_data' => $chart_data,
            'top_products' => $top_products,
            'date_range' => array(
                'start' => $start_date,
                'end' => $end_date,
            ),
        );
    }
    
    /**
     * Get top products data
     */
    private function get_top_products($start_date, $end_date) {
        global $wpdb;
        
        $orders_table = $this->get_orders_table();
        $order_items_table = $this->get_order_items_table();
        
        $query = $wpdb->prepare("
            SELECT 
                p.post_title as product_name,
                oi.product_id,
                SUM(oi.product_qty) as total_quantity,
                SUM(oi.product_total) as total_revenue
            FROM {$order_items_table} oi
            JOIN {$orders_table} o ON oi.order_id = o.id
            JOIN {$wpdb->posts} p ON oi.product_id = p.ID
            WHERE o.status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
            AND o.date_created_gmt >= %s 
            AND o.date_created_gmt <= %s
            AND oi.order_item_type = 'line_item'
            GROUP BY oi.product_id
            ORDER BY total_revenue DESC
            LIMIT 10
        ", $start_date . ' 00:00:00', $end_date . ' 23:59:59');
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Get orders table name (HPOS compatible)
     */
    private function get_orders_table() {
        global $wpdb;
        
        // Check if HPOS is enabled
        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && 
            \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            return $wpdb->prefix . 'wc_orders';
        }
        
        // Fallback to posts table
        return $wpdb->posts;
    }
    
    /**
     * Get order items table name (HPOS compatible)
     */
    private function get_order_items_table() {
        global $wpdb;
        
        // Check if HPOS is enabled
        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && 
            \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            return $wpdb->prefix . 'wc_order_product_lookup';
        }
        
        // Fallback to order items table
        return $wpdb->prefix . 'woocommerce_order_items';
    }
    
    /**
     * Validate date format
     */
    private function is_valid_date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Get collection parameters
     */
    public function get_collection_params() {
        return array(
            'start_date' => array(
                'description' => __('Start date for analytics data in YYYY-MM-DD format.', 'wc-custom-analytics'),
                'type'        => 'string',
                'format'      => 'date',
            ),
            'end_date' => array(
                'description' => __('End date for analytics data in YYYY-MM-DD format.', 'wc-custom-analytics'),
                'type'        => 'string',
                'format'      => 'date',
            ),
        );
    }
    
    /**
     * Get schema for analytics data
     */
    public function get_public_item_schema() {
        $schema = array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'analytics',
            'type'       => 'object',
            'properties' => array(
                'summary' => array(
                    'description' => __('Summary analytics data.', 'wc-custom-analytics'),
                    'type'        => 'object',
                    'properties'  => array(
                        'total_sales' => array(
                            'description' => __('Total sales amount.', 'wc-custom-analytics'),
                            'type'        => 'number',
                        ),
                        'total_orders' => array(
                            'description' => __('Total number of orders.', 'wc-custom-analytics'),
                            'type'        => 'integer',
                        ),
                        'avg_order_value' => array(
                            'description' => __('Average order value.', 'wc-custom-analytics'),
                            'type'        => 'number',
                        ),
                    ),
                ),
                'chart_data' => array(
                    'description' => __('Daily chart data.', 'wc-custom-analytics'),
                    'type'        => 'array',
                    'items'       => array(
                        'type'       => 'object',
                        'properties' => array(
                            'date' => array(
                                'description' => __('Date.', 'wc-custom-analytics'),
                                'type'        => 'string',
                                'format'      => 'date',
                            ),
                            'sales' => array(
                                'description' => __('Sales amount for the date.', 'wc-custom-analytics'),
                                'type'        => 'number',
                            ),
                            'orders' => array(
                                'description' => __('Number of orders for the date.', 'wc-custom-analytics'),
                                'type'        => 'integer',
                            ),
                        ),
                    ),
                ),
                'top_products' => array(
                    'description' => __('Top selling products.', 'wc-custom-analytics'),
                    'type'        => 'array',
                    'items'       => array(
                        'type'       => 'object',
                        'properties' => array(
                            'product_name' => array(
                                'description' => __('Product name.', 'wc-custom-analytics'),
                                'type'        => 'string',
                            ),
                            'total_quantity' => array(
                                'description' => __('Total quantity sold.', 'wc-custom-analytics'),
                                'type'        => 'integer',
                            ),
                            'total_revenue' => array(
                                'description' => __('Total revenue from product.', 'wc-custom-analytics'),
                                'type'        => 'number',
                            ),
                        ),
                    ),
                ),
            ),
        );
        
        return $this->add_additional_fields_schema($schema);
    }
}