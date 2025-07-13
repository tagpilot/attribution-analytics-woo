/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Table, TableCard } from '@woocommerce/components';
// import { formatCurrency } from '@woocommerce/currency';

/**
 * Internal dependencies
 */

function formatCurrency(val, currency) {
    return Math.round(val) + ' ' + currency;
}

const SourcesTable = ({ data, loading }) => {
    const currency = (window.wcCustomAnalytics && window.wcCustomAnalytics.currency) || 'USD';
    
    const headers = [
        {
            key: 'sources',
            label: __('Source', 'wc-custom-analytics'),
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
    
    if (loading) {
        return (
            <TableCard
                title={__('Sources', 'wc-custom-analytics')}
                headers={headers}
                isLoading={true}
                rowsPerPage={10}
                totalRows={0}
                rows={[]}
            />
        );
    }
    
    if (!data || data.length === 0) {
        return (
            <TableCard
                title={__('Sources', 'wc-custom-analytics')}
                headers={headers}
                isLoading={false}
                rowsPerPage={10}
                totalRows={0}
                rows={[]}
                emptyMessage={__('No data found for the selected period.', 'wc-custom-analytics')}
            />
        );
    }
    
    const rows = data.map((source, index) => [
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
            value: parseInt(source.ourders) || 0,
        },
        {
            display: formatCurrency(source.revenue || 0, currency),
            value: parseFloat(source.revenue) || 0,
        },
    ]);
    
    return (
        <TableCard
            title={__('Sources', 'wc-custom-analytics')}
            headers={headers}
            isLoading={false}
            rowsPerPage={100}
            totalRows={rows.length}
            rows={rows}
            downloadable
            onQueryChange={() => {}}
            query={{}}
            summary={[
                {
                    label: __('Total Sources', 'wc-custom-analytics'),
                    value: rows.length,
                },
            ]}
        />
    );
};

export default SourcesTable;