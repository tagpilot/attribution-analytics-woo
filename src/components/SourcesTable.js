/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Table, TableCard } from '@woocommerce/components';
import { useState, useEffect } from '@wordpress/element';
// import { formatCurrency } from '@woocommerce/currency';

/**
 * Internal dependencies
 */

function formatCurrency(val, currency) {
    return Math.round(val) + ' ' + currency;
}

const SourcesTable = ({ data, loading }) => {
    const currency = (window.wcCustomAnalytics && window.wcCustomAnalytics.currency) || 'USD';
    const [currentPage, setCurrentPage] = useState(1);
    const [rowsPerPage, setRowsPerPage] = useState(10);
    
    // Reset page when data changes - MUST be before any returns
    useEffect(() => {
        setCurrentPage(1);
    }, [data]);
    
    const headers = [
        {
            key: 'sources',
            label: __('Dimension', 'attribution-analytics-for-woocommerce'),
            required: true,
            isLeftAligned: true,
        },
        {
            key: 'orders',
            label: __('Orders', 'wc-custom-analytics'),
            required: true,
            isNumeric: true,
        },
        {
            key: 'revenue',
            label: __('Revenue', 'wc-custom-analytics'),
            required: true,
            isNumeric: true,
        },
    ];
    
    const onQueryChange = (type, query) => {
        if (type === 'paged') {
            setCurrentPage(query.paged || 1);
        }
        if (type === 'per_page') {
            setRowsPerPage(query.per_page || 10);
            setCurrentPage(1); // Reset to first page when changing rows per page
        }
    };
    
    if (loading) {
        return (
            <TableCard
                title={__('Attribution', 'attribution-analytics-for-woocommerce')}
                headers={headers}
                isLoading={true}
                rowsPerPage={rowsPerPage}
                totalRows={0}
                rows={[]}
                query={{
                    paged: currentPage,
                    per_page: rowsPerPage,
                }}
                onQueryChange={onQueryChange}
            />
        );
    }
    
    if (!data || data.length === 0) {
        return (
            <TableCard
                title={__('Attribution', 'attribution-analytics-for-woocommerce')}
                headers={headers}
                isLoading={false}
                rowsPerPage={rowsPerPage}
                totalRows={0}
                rows={[]}
                emptyMessage={__('No data found for the selected period.', 'wc-custom-analytics')}
                query={{
                    paged: currentPage,
                    per_page: rowsPerPage,
                }}
                onQueryChange={onQueryChange}
            />
        );
    }
    
    // Calculate pagination
    const totalRows = data.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = Math.min(startIndex + rowsPerPage, totalRows);
    const paginatedData = data.slice(startIndex, endIndex);
    
    const rows = paginatedData.map((source, index) => [
        {
            display: (
                <div className="woocommerce-table__product">
                    <strong>{source.source}</strong>
                </div>
            ),
            value: source.source,
        },
        {
            display: source.orders,
            value: parseInt(source.orders) || 0,
        },
        {
            display: formatCurrency(source.revenue || 0, currency),
            value: parseFloat(source.revenue) || 0,
        },
    ]);
    
    // Calculate total revenue and orders across all data
    const totalRevenue = data.reduce((sum, source) => sum + (parseFloat(source.revenue) || 0), 0);
    const totalOrders = data.reduce((sum, source) => sum + (parseInt(source.orders) || 0), 0);
    
    return (
        <TableCard
            title={__('Sources', 'wc-custom-analytics')}
            headers={headers}
            isLoading={false}
            rowsPerPage={rowsPerPage}
            totalRows={totalRows}
            rows={rows}
            downloadable
            onQueryChange={onQueryChange}
            query={{
                paged: currentPage,
                per_page: rowsPerPage,
            }}
            summary={[
                {
                    label: __('Total Items', 'attribution-analytics-for-woocommerce'),
                    value: totalRows,
                },
                {
                    label: __('Total Orders', 'wc-custom-analytics'),
                    value: totalOrders,
                },
                {
                    label: __('Total Revenue', 'wc-custom-analytics'),
                    value: formatCurrency(totalRevenue, currency),
                },
            ]}
        />
    );
};

export default SourcesTable;