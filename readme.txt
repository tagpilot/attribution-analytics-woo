=== Attribution Analytics for WooCommerce ===
Contributors: tagconcierge
Tags: analytics, woocommerce, sources, marketing
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Track and analyze which traffic sources and marketing channels drive the most revenue in your WooCommerce store

== Description ==

Attribution Analytics for WooCommerce gives you powerful insights into which marketing channels and traffic sources are driving revenue for your online store. By analyzing the native source data that WooCommerce already collects with each order, this plugin provides clear, actionable analytics without requiring any external tracking scripts.

**Key Benefits:**

* **Revenue Attribution** - See exactly how much revenue each traffic source generates
* **Marketing ROI** - Understand which channels provide the best return on investment
* **Data-Driven Decisions** - Make informed choices about where to focus your marketing efforts
* **Privacy-Friendly** - Uses only native WooCommerce data, no external tracking required

**Core Features:**

* **Modern Analytics Dashboard** - React-powered interface with interactive charts and tables
* **Revenue by Source** - Track total revenue, order count, and average order value by traffic source
* **Flexible Date Ranges** - Analyze performance across any time period with the built-in date picker
* **WooCommerce Integration** - Seamlessly integrated into the WooCommerce Analytics menu
* **Real-time Updates** - See your data update instantly as new orders come in
* **HPOS Compatible** - Full support for WooCommerce High-Performance Order Storage
* **REST API** - Access your attribution data programmatically
* **Mobile Responsive** - Works perfectly on all devices with dark mode support
* **Export Capabilities** - Download your attribution data for further analysis

**Perfect for:**

* Store owners wanting to understand their marketing performance
* Marketing agencies tracking campaign effectiveness
* Anyone looking to optimize their marketing spend
* Stores focused on data-driven growth


== Installation ==

**Automatic Installation (Recommended)**

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins → Add New**
3. Search for "Attribution Analytics for WooCommerce"
4. Click **Install Now** and then **Activate**
5. Access your analytics at **WooCommerce → Analytics → Sources**

**Manual Installation**

1. Download the plugin zip file from WordPress.org
2. Log in to your WordPress admin dashboard
3. Navigate to **Plugins → Add New → Upload Plugin**
4. Choose the downloaded zip file and click **Install Now**
5. Activate the plugin through the **Plugins** menu
6. Access your analytics at **WooCommerce → Analytics → Sources**

**FTP Installation**

1. Download and unzip the plugin file
2. Upload the `attribution-analytics-for-woocommerce` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Access your analytics at **WooCommerce → Analytics → Sources**

== Frequently Asked Questions ==

= How does it work? =

This plugin analyzes the native source tracking data that WooCommerce automatically collects with each order. When customers arrive at your store, WooCommerce records information about where they came from (traffic source, utm parameters, etc.). Attribution Analytics aggregates this per-order data to show you comprehensive analytics across all your orders.

= Do I need to add any tracking codes? =

No! The plugin uses WooCommerce's built-in source tracking. No additional tracking codes, pixels, or scripts are required.

= Is this GDPR compliant? =

Yes. The plugin only uses data that WooCommerce already collects as part of normal operations. No additional customer data is collected, and no data is sent to external services.

= Does it work with High-Performance Order Storage (HPOS)? =

Yes, the plugin is fully compatible with WooCommerce's High-Performance Order Storage system.


= What sources can it track? =

The plugin tracks all sources that WooCommerce records, including:
* Direct traffic
* Organic search (Google, Bing, etc.)
* Social media (Facebook, Instagram, Twitter, etc.)
* Email campaigns
* Paid advertising
* Referral sites
* Any custom UTM parameters

= Does it slow down my site? =

No. The analytics calculations happen in the background and don't affect your store's frontend performance.

= Can I see historical data? =

Yes, the plugin analyzes all existing orders in your store, so you can see attribution data from before the plugin was installed.



== Screenshots ==

1. **Sources Analytics Dashboard** - Main analytics view showing revenue by source with interactive charts
2. **Sources Table** - Detailed breakdown of metrics for each traffic source
3. **Date Range Selection** - Flexible date filtering to analyze specific time periods
4. **Mobile View** - Responsive design works perfectly on all devices
5. **Dark Mode** - Built-in dark mode support for comfortable viewing


== Development ==

This plugin uses modern JavaScript development tools. The distributed JavaScript files in the `build` directory are compiled from source files located in the `src` directory.

= Build from Source =

To build the JavaScript files from source:

1. Install Node.js (version 16 or higher) and npm (version 8 or higher)
2. Run `npm install` to install dependencies
3. Run `npm run build` to create production builds with source maps
4. Run `npm run start` for development mode with file watching

The source code is available in the plugin's `src` directory, and the build process uses WordPress Scripts (@wordpress/scripts) for bundling and compilation.

== Changelog ==

= 1.0.0 - 2025-08-05 =
* Initial release
* Core attribution analytics functionality
* Revenue by source tracking
* Interactive charts and tables
* WooCommerce Analytics integration
* HPOS compatibility
* REST API endpoints
* Date range filtering
* Mobile responsive design
* Dark mode support

