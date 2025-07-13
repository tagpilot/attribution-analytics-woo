/**
 * External dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SourcesReport from './components/SourcesReport';

// Register the page for WooCommerce Admin routing
addFilter('woocommerce_admin_reports_list', 'analytics/sources', (pages) => {
    pages.push({
        report: 'sources',
        component: SourcesReport,
        navArgs: {
            id: 'woocommerce-analytics-sources',
        },
        breadcrumbs: [
            __('Analytics', 'wc-custom-analytics'),
            __('Custom Reports', 'wc-custom-analytics')
        ],
        capability: "view_woocommerce_reports"
    });

    return pages;
});