/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Button, Card, CardBody, CardHeader } from '@wordpress/components';
import { Icon, chevronDown, chevronUp, info } from '@wordpress/icons';

const AttributionEducation = () => {
    const [isExpanded, setIsExpanded] = useState(false);

    return (
        <Card className="attribution-education" style={{ marginBottom: '20px' }}>
            <CardHeader>
                <Button
                    className="attribution-education__toggle"
                    onClick={() => setIsExpanded(!isExpanded)}
                    variant="link"
                    style={{
                        width: '100%',
                        justifyContent: 'space-between',
                        textAlign: 'left',
                        padding: '8px 0',
                        height: 'auto',
                        textDecoration: 'none'
                    }}
                >
                    <span style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                        <Icon icon={info} size={20} />
                        <strong>{__('Understanding Attribution Analytics', 'attribution-analytics-for-woocommerce')}</strong>
                    </span>
                    <Icon icon={isExpanded ? chevronUp : chevronDown} size={24} />
                </Button>
            </CardHeader>
            {isExpanded && (
                <CardBody>
                    <div className="attribution-education__content" style={{ lineHeight: '1.6' }}>
                        <h3>{__('What is Attribution?', 'attribution-analytics-for-woocommerce')}</h3>
                        <p>
                            {__('Attribution is the process of identifying which marketing channels and touchpoints contribute to conversions and sales. It helps you understand which of your marketing efforts are driving revenue, allowing you to make data-driven decisions about where to invest your marketing budget.', 'attribution-analytics-for-woocommerce')}
                        </p>

                        <h3>{__('Common Attribution Models', 'attribution-analytics-for-woocommerce')}</h3>
                        <ul>
                            <li>
                                <strong>{__('Last-Click Attribution', 'attribution-analytics-for-woocommerce')}</strong>: 
                                {__(' Credits 100% of the conversion to the last touchpoint before purchase. This is what WooCommerce tracks natively and what this plugin reports.', 'attribution-analytics-for-woocommerce')}
                            </li>
                            <li>
                                <strong>{__('First-Click Attribution', 'attribution-analytics-for-woocommerce')}</strong>: 
                                {__(' Credits 100% of the conversion to the first touchpoint in the customer journey.', 'attribution-analytics-for-woocommerce')}
                            </li>
                            <li>
                                <strong>{__('Linear Attribution', 'attribution-analytics-for-woocommerce')}</strong>: 
                                {__(' Distributes credit equally across all touchpoints in the customer journey.', 'attribution-analytics-for-woocommerce')}
                            </li>
                            <li>
                                <strong>{__('Time-Decay Attribution', 'attribution-analytics-for-woocommerce')}</strong>: 
                                {__(' Gives more credit to touchpoints closer to the conversion.', 'attribution-analytics-for-woocommerce')}
                            </li>
                        </ul>

                        <h3>{__('What This Plugin Tracks', 'attribution-analytics-for-woocommerce')}</h3>
                        <p>
                            {__('This plugin uses ', 'attribution-analytics-for-woocommerce')}
                            <strong>{__('last-click attribution', 'attribution-analytics-for-woocommerce')}</strong>
                            {__(', which is the model natively supported by WooCommerce. When a customer makes a purchase, WooCommerce records the traffic source from their final session before completing the order.', 'attribution-analytics-for-woocommerce')}
                        </p>

                        <h3>{__('Why ROAS Differs Across Platforms', 'attribution-analytics-for-woocommerce')}</h3>
                        <p>
                            {__('You may notice that the Return on Ad Spend (ROAS) reported here differs from what you see in platforms like Google Ads, Facebook Ads, or Google Analytics. This is normal and happens because:', 'attribution-analytics-for-woocommerce')}
                        </p>
                        <ul>
                            <li>
                                <strong>{__('Different Attribution Models', 'attribution-analytics-for-woocommerce')}</strong>: 
                                {__(' Each platform uses its own attribution model. Google Ads might use data-driven attribution, while Facebook uses a 1-day view/7-day click model.', 'attribution-analytics-for-woocommerce')}
                            </li>
                            <li>
                                <strong>{__('View-Through Conversions', 'attribution-analytics-for-woocommerce')}</strong>: 
                                {__(' Ad platforms often count conversions when someone sees (but doesn\'t click) an ad, then purchases later through another channel.', 'attribution-analytics-for-woocommerce')}
                            </li>
                            <li>
                                <strong>{__('Cross-Device Tracking', 'attribution-analytics-for-woocommerce')}</strong>: 
                                {__(' Platforms like Google and Facebook can track users across devices, while WooCommerce only sees the final device used for purchase.', 'attribution-analytics-for-woocommerce')}
                            </li>
                            <li>
                                <strong>{__('Attribution Windows', 'attribution-analytics-for-woocommerce')}</strong>: 
                                {__(' Different platforms use different time windows for attribution (e.g., 30 days, 7 days, 1 day).', 'attribution-analytics-for-woocommerce')}
                            </li>
                        </ul>

                        <div style={{ 
                            backgroundColor: '#f0f0f1', 
                            padding: '15px', 
                            borderRadius: '4px',
                            marginTop: '20px' 
                        }}>
                            <p style={{ margin: 0 }}>
                                <strong>{__('💡 Pro Tip', 'attribution-analytics-for-woocommerce')}</strong>: 
                                {__(' Use this plugin\'s data as your "source of truth" for actual revenue since it reflects real completed orders in WooCommerce. Use platform-specific metrics to optimize within each platform, but rely on WooCommerce data for overall business decisions.', 'attribution-analytics-for-woocommerce')}
                            </p>
                        </div>
                    </div>
                </CardBody>
            )}
        </Card>
    );
};

export default AttributionEducation;