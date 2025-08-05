/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { DateRangeFilterPicker, Chart, Section, H } from '@woocommerce/components';
import { SelectControl } from '@wordpress/components';
import { getCurrentDates, getDateParamsFromQuery, isoDateFormat } from '@woocommerce/date';
import { getQuery, getNewPath, updateQueryString } from '@woocommerce/navigation';

/**
 * Internal dependencies
 */
import SourcesTable from './SourcesTable';
import AttributionEducation from './AttributionEducation';
import { getAnalyticsData } from '../data/api';

class SourcesReport extends Component {
    constructor(props) {
        super(props);
        
        this.state = {
            data: null,
            loading: true,
            error: null,
            breakdownBy: 'source_medium', // Default breakdown
        };
        
        this.onDateChange = this.onDateChange.bind(this);
        this.onBreakdownChange = this.onBreakdownChange.bind(this);

    }
    
    componentDidMount() {
        this.fetchData();
    }
    
    componentDidUpdate(prevProps) {
        const query = getQuery();
        const prevQuery = prevProps.query || {};
        
        if (
            query.period !== prevQuery.period ||
            query.compare !== prevQuery.compare ||
            query.before !== prevQuery.before ||
            query.after !== prevQuery.after
        ) {
            this.fetchData();
        }
    }
    
    async fetchData() {
        this.setState({ loading: true, error: null });
        
        try {
            const query = getQuery();
            const dates = getCurrentDates(query);
            const { breakdownBy } = this.state;
            const data = await getAnalyticsData({
                startDate: dates.primary.after,
                endDate: dates.primary.before,
                breakdownBy: breakdownBy,
            });
            
            console.log('Analytics data received:', data);
            
            this.setState({ 
                data, 
                loading: false 
            });
        } catch (error) {
            console.error('Error fetching analytics data:', error);
            this.setState({ 
                error: error.message, 
                loading: false 
            });
        }
    }
    
    onDateChange(data) {
        updateQueryString( data );
        this.fetchData();
    }
    
    onBreakdownChange(value) {
        this.setState({ breakdownBy: value }, () => {
            this.fetchData();
        });
    }
    
    render() {
        const { data, loading, error, breakdownBy } = this.state;
        const query = getQuery();
        const dates = getCurrentDates(query);
        const dateParams = getDateParamsFromQuery(query);

        dateParams.primaryDate = dates.primary;
        dateParams.secondaryDate = dates.secondary;
        const chartData = data ? data.revenue_trends : [];
        
        const breakdownOptions = [
            { label: __( 'Source / Medium', 'attribution-analytics-for-woocommerce' ), value: 'source_medium' },
            { label: __( 'Source Type', 'attribution-analytics-for-woocommerce' ), value: 'source_type' },
            { label: __( 'Campaign', 'attribution-analytics-for-woocommerce' ), value: 'campaign' },
            { label: __( 'Device Type', 'attribution-analytics-for-woocommerce' ), value: 'device_type' },
            { label: __( 'Referrer', 'attribution-analytics-for-woocommerce' ), value: 'referrer' },
            { label: __( 'Source Only', 'attribution-analytics-for-woocommerce' ), value: 'source' },
            { label: __( 'Medium Only', 'attribution-analytics-for-woocommerce' ), value: 'medium' },
        ];

        return (
            <Fragment>
                <AttributionEducation />
                <Fragment>
                    <H className="screen-reader-text">
                    { __( 'Filters', 'woocommerce' ) }
                    </H>
                    <Section component="div" className="woocommerce-filters">
                        <div className="woocommerce-filters__basic-filters">
                            <DateRangeFilterPicker
                                key="daterange"
                                onRangeSelect={ this.onDateChange }
                                dateQuery={ dateParams }
                                isoDateFormat={ isoDateFormat }
                                isComparisonEnabled={ false }
                            />
                            <div style={{ minWidth: '200px', marginLeft: '16px' }}>
                                <SelectControl
                                    label={ __( 'Breakdown by', 'attribution-analytics-for-woocommerce' ) }
                                    value={ breakdownBy }
                                    options={ breakdownOptions }
                                    onChange={ this.onBreakdownChange }
                                />
                            </div>
                        </div>
                    </Section>
                </Fragment>
                {error ? (
                    <div style={{ 
                        padding: '20px', 
                        backgroundColor: '#f8d7da', 
                        color: '#721c24', 
                        border: '1px solid #f5c6cb',
                        borderRadius: '4px',
                        margin: '20px 0'
                    }}>
                        <strong>{__('Error loading data:', 'attribution-analytics-for-woocommerce')}</strong> {error}
                        <p style={{ marginTop: '10px', marginBottom: 0 }}>
                            {__('Try selecting a different date range or check if you have any completed orders in WooCommerce.', 'attribution-analytics-for-woocommerce')}
                        </p>
                    </div>
                ) : (
                    <Fragment>
                        <Chart data={ chartData } title={`Revenue by ${breakdownOptions.find(opt => opt.value === breakdownBy)?.label || 'Sources'}`} layout="item-comparison" />
                        <SourcesTable
                            data={data?.source_summary}
                            loading={loading}
                        />
                    </Fragment>
                )}
            </Fragment>
        );
    }
}

export default SourcesReport;