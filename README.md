# WooCommerce Custom Analytics Plugin

A comprehensive WooCommerce plugin that adds custom analytics reports with React-powered components, integrating seamlessly with WooCommerce Admin.

## Features

- **React-powered Analytics Dashboard**: Modern, interactive analytics interface
- **Custom Reports**: Sales summary, chart visualization, and top products table
- **WooCommerce Admin Integration**: Seamlessly integrates with WooCommerce's admin interface
- **Date Range Filtering**: Built-in date range picker for flexible reporting
- **REST API Support**: RESTful endpoints for data retrieval
- **HPOS Compatible**: Supports WooCommerce High-Performance Order Storage
- **Responsive Design**: Mobile-friendly interface with dark mode support

## Requirements

- WordPress 5.6 or higher
- WooCommerce 5.7.0 or higher
- Node.js 16+ (for development)
- PHP 7.4 or higher

## Installation

### Method 1: Manual Installation

1. Download or clone this repository
2. Upload the plugin folder to `/wp-content/plugins/`
3. Install Node.js dependencies: `npm install`
4. Build the React components: `npm run build`
5. Activate the plugin through the WordPress admin

### Method 2: Development Setup

1. Clone the repository:
   ```bash
   git clone [repository-url] wp-content/plugins/wc-custom-analytics
   cd wp-content/plugins/wc-custom-analytics
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start development mode:
   ```bash
   npm run start
   ```

4. Activate the plugin in WordPress admin

## Plugin Structure

```
wc-custom-analytics/
├── wc-custom-analytics.php          # Main plugin file
├── includes/
│   └── class-rest-api.php           # REST API controller
├── src/
│   ├── index.js                     # Main React entry point
│   ├── style.scss                   # Styles
│   ├── components/
│   │   ├── CustomAnalyticsPage.js   # Main analytics page component
│   │   ├── AnalyticsSummary.js      # Summary cards component
│   │   ├── SalesChart.js            # Chart visualization component
│   │   └── TopProductsTable.js      # Top products table component
│   └── data/
│       └── api.js                   # API utility functions
├── build/                           # Built assets (generated)
├── package.json                     # Node.js dependencies
├── webpack.config.js                # Webpack configuration
└── README.md                        # This file
```

## Usage

### Accessing Analytics

1. **Via WooCommerce Admin**: Navigate to `WooCommerce > Analytics > Custom Reports`
2. **Direct URL**: Visit `/wp-admin/admin.php?page=wc-custom-analytics`

### Features Overview

#### Analytics Summary
- **Total Sales**: Sum of all completed orders in the selected period
- **Total Orders**: Count of completed, processing, and on-hold orders
- **Average Order Value**: Calculated average across all orders

#### Sales Chart
- Interactive line chart showing daily sales and order trends
- Date range filtering with preset options (Last 7 days, Last 30 days, etc.)
- Responsive design with mobile-friendly interface

#### Top Products Table
- Lists top 10 products by revenue in the selected period
- Shows product name, items sold, and total revenue
- Downloadable data export capability

## API Endpoints

The plugin provides REST API endpoints for programmatic access:

### Get Analytics Data
```
GET /wp-json/wc-custom-analytics/v1/analytics
```

**Parameters:**
- `start_date` (optional): Start date in YYYY-MM-DD format
- `end_date` (optional): End date in YYYY-MM-DD format

**Response:**
```json
{
  "summary": {
    "total_sales": 12500.00,
    "total_orders": 85,
    "avg_order_value": 147.06
  },
  "chart_data": [
    {
      "date": "2024-01-01",
      "sales": 1250.00,
      "orders": 8
    }
  ],
  "top_products": [
    {
      "product_name": "Premium Widget",
      "total_quantity": 45,
      "total_revenue": 2250.00
    }
  ]
}
```

### Get Summary Only
```
GET /wp-json/wc-custom-analytics/v1/analytics/summary
```

## Development

### Building the Plugin

For production:
```bash
npm run build
```

For development (with file watching):
```bash
npm run start
```

### Code Structure

#### PHP Components

- **Main Plugin Class**: Handles plugin initialization, menu registration, and admin integration
- **REST API Controller**: Provides RESTful endpoints with proper authentication
- **HPOS Compatibility**: Supports both traditional and High-Performance Order Storage

#### React Components

- **CustomAnalyticsPage**: Main container component with date filtering
- **AnalyticsSummary**: Displays key metrics using WooCommerce's SummaryList
- **SalesChart**: Interactive chart using WooCommerce's Chart component
- **TopProductsTable**: Data table using WooCommerce's TableCard component

### Key Dependencies

#### WordPress/WooCommerce
- `@wordpress/element`: React wrapper for WordPress
- `@wordpress/i18n`: Internationalization
- `@wordpress/hooks`: WordPress filter system
- `@woocommerce/components`: WooCommerce UI components
- `@woocommerce/currency`: Currency formatting utilities
- `@woocommerce/date`: Date handling utilities

#### Build Tools
- `@wordpress/scripts`: WordPress build toolchain
- Webpack configuration for asset bundling
- SCSS compilation for styling

## Customization

### Adding New Metrics

1. **PHP Side**: Extend the analytics data query in `fetch_analytics_data()` method
2. **React Side**: Add new summary cards or components in `AnalyticsSummary.js`

### Styling

Modify `src/style.scss` for custom styling. The plugin includes:
- Responsive grid layouts
- Dark mode support
- Loading state animations
- WooCommerce design system compliance

### Extending API

Add new endpoints by extending the `WC_Custom_Analytics_REST_Controller` class:

```php
register_rest_route(
    $this->namespace,
    '/custom-endpoint',
    array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => array($this, 'custom_callback'),
        'permission_callback' => array($this, 'get_items_permissions_check'),
    )
);
```

## Hooks and Filters

### Available Filters

- `wc_custom_analytics_data`: Filter analytics data before sending to frontend
- `wc_custom_analytics_query_args`: Modify database query arguments
- `wc_custom_analytics_chart_config`: Customize chart configuration

### Available Actions

- `wc_custom_analytics_before_data_fetch`: Runs before data fetching
- `wc_custom_analytics_after_data_fetch`: Runs after data fetching

## Troubleshooting

### Common Issues

1. **React components not loading**:
   - Ensure `npm run build` has been executed
   - Check browser console for JavaScript errors
   - Verify WooCommerce Admin is active

2. **Permission errors**:
   - Ensure user has `manage_woocommerce` capability
   - Check REST API authentication

3. **Data not displaying**:
   - Verify WooCommerce orders exist in the selected date range
   - Check database table structure (HPOS vs traditional)

### Debug Mode

Enable debug mode by adding to `wp-config.php`:
```php
define('SCRIPT_DEBUG', true);
```

This loads unminified assets for easier debugging.

## Performance Considerations

- Database queries are optimized with proper indexing
- Results are cacheable via WordPress transients
- Pagination support for large datasets
- Efficient React rendering with proper key props

## Security

- All REST endpoints require `manage_woocommerce` capability
- Nonce verification for AJAX requests
- SQL injection protection via `$wpdb->prepare()`
- XSS prevention through proper data sanitization

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Make changes and test thoroughly
4. Submit a pull request with detailed description

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and feature requests, please visit [plugin support page] or create an issue in the repository.

## Changelog

### 1.0.0
- Initial release
- React-powered analytics dashboard
- WooCommerce Admin integration
- REST API endpoints
- HPOS compatibility
- Responsive design with dark mode support