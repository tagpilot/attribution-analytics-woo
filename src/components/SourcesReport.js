/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { DateRangeFilterPicker, Chart, Section, H } from '@woocommerce/components';
import { getCurrentDates, getDateParamsFromQuery, isoDateFormat } from '@woocommerce/date';
import { getQuery, getNewPath, updateQueryString } from '@woocommerce/navigation';

/**
 * Internal dependencies
 */
import SourcesTable from './SourcesTable';
import { getAnalyticsData } from '../data/api';

class SourcesReport extends Component {
    constructor(props) {
        super(props);
        
        this.state = {
            data: null,
            loading: true,
            error: null,
        };
        
        this.onDateChange = this.onDateChange.bind(this);

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
            const data = await getAnalyticsData({
                startDate: dates.primary.after,
                endDate: dates.primary.before,
            });
            
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
    
    render() {
        const { data, loading, error } = this.state;
        const query = getQuery();
        const dates = getCurrentDates(query);
        const dateParams = getDateParamsFromQuery(query);

        dateParams.primaryDate = dates.primary;
        dateParams.secondaryDate = dates.secondary;
        const chartData = data ? data.revenue_trends : [];

        return (
            <Fragment>
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
                            />
                        </div>
                    </Section>
                </Fragment>
                <Chart data={ chartData } title="Revenue by Sources" layout="item-comparison" />
                <SourcesTable
                    data={data?.source_summary}
                    loading={loading}
                />
            </Fragment>
        );
    }
}

export default SourcesReport;