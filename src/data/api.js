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
    if (window.wcCustomAnalytics && window.wcCustomAnalytics.ajaxUrl) {
        // Use WordPress AJAX for older setups
        const formData = new FormData();
        formData.append('action', 'wc_custom_analytics_data');
        formData.append('nonce', window.wcCustomAnalytics.nonce);
        formData.append('start_date', startDate || '');
        formData.append('end_date', endDate || '');
        
        const response = await fetch(window.wcCustomAnalytics.ajaxUrl, {
            method: 'POST',
            body: formData,
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        // if (!result.success) {
        //     throw new Error(result.data || 'Unknown error occurred');
        // }
        
        return result.data;
    }
    
    // Fallback to REST API
    const queryParams = new URLSearchParams();
    if (startDate) queryParams.append('start_date', startDate);
    if (endDate) queryParams.append('end_date', endDate);
    
    try {
        return await apiFetch({
            path: `/wc-custom-analytics/v1/analytics?${queryParams.toString()}`,
            method: 'GET',
        });
    } catch (error) {
        console.error('API fetch error:', error);
        throw new Error(error.message || 'Failed to fetch analytics data');
    }
};

/**
 * Get WooCommerce orders data using REST API
 *
 * @param {Object} params Parameters for the request
 * @return {Promise} Promise resolving to orders data
 */
export const getOrdersData = async (params = {}) => {
    const queryParams = new URLSearchParams({
        per_page: 100,
        status: 'completed,processing,on-hold',
        ...params,
    });
    
    try {
        return await apiFetch({
            path: `/wc/v3/orders?${queryParams.toString()}`,
            method: 'GET',
        });
    } catch (error) {
        console.error('Orders API error:', error);
        throw new Error(error.message || 'Failed to fetch orders data');
    }
};

/**
 * Get WooCommerce products data using REST API
 *
 * @param {Object} params Parameters for the request
 * @return {Promise} Promise resolving to products data
 */
export const getProductsData = async (params = {}) => {
    const queryParams = new URLSearchParams({
        per_page: 100,
        ...params,
    });
    
    try {
        return await apiFetch({
            path: `/wc/v3/products?${queryParams.toString()}`,
            method: 'GET',
        });
    } catch (error) {
        console.error('Products API error:', error);
        throw new Error(error.message || 'Failed to fetch products data');
    }
};