/**
 * External dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SourcesReport from './components/SourcesReport';
import './style.css';

// Register the page for WooCommerce Admin routing
addFilter('woocommerce_admin_reports_list', 'analytics/attribution', (pages) => {
    pages.push({
        report: 'attribution',
        component: SourcesReport,
        navArgs: {
            id: 'woocommerce-analytics-attribution',
        },
        breadcrumbs: [
            __('Analytics', 'attribution-analytics-for-woocommerce'),
            __('Attribution', 'attribution-analytics-for-woocommerce')
        ],
        capability: "view_woocommerce_reports"
    });

    return pages;
});