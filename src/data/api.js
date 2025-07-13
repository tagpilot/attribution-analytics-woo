/**
 * External dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Get analytics data from the server
 *
 * @param {Object} params Parameters for the request
 * @param {string} params.startDate Start date in YYYY-MM-DD format
 * @param {string} params.endDate End date in YYYY-MM-DD format
 * @return {Promise} Promise resolving to analytics data
 */
export const getAnalyticsData = async (params = {}) => {
    const { startDate, endDate } = params;
    
    // Check if we have the WordPress AJAX setup
    if (window.sourceAnalyticsWoo && window.sourceAnalyticsWoo.ajaxUrl) {
        // Use WordPress AJAX for older setups
        const formData = new FormData();
        formData.append('action', 'source_analytics_woo_data');
        formData.append('nonce', window.sourceAnalyticsWoo.nonce);
        formData.append('start_date', startDate || '');
        formData.append('end_date', endDate || '');
        
        const response = await fetch(window.sourceAnalyticsWoo.ajaxUrl, {
            method: 'POST',
            body: formData,
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.data || 'Unknown error occurred');
        }
        
        return result.data;
    }
};