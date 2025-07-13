const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        index: path.resolve(__dirname, 'src', 'index.js'),
    },
    output: {
        ...defaultConfig.output,
        path: path.resolve(__dirname, 'build'),
    },
    externals: {
        '@woocommerce/components': ['wc', 'components'],
        '@woocommerce/currency': ['wc', 'currency'],
        '@woocommerce/date': ['wc', 'date'],
        '@woocommerce/navigation': ['wc', 'navigation'],
        '@woocommerce/number': ['wc', 'number'],
        '@woocommerce/tracks': ['wc', 'tracks'],
        '@wordpress/api-fetch': ['wp', 'apiFetch'],
        '@wordpress/components': ['wp', 'components'],
        '@wordpress/date': ['wp', 'date'],
        '@wordpress/element': ['wp', 'element'],
        '@wordpress/hooks': ['wp', 'hooks'],
        '@wordpress/i18n': ['wp', 'i18n'],
        '@wordpress/url': ['wp', 'url'],
        'react': 'React',
        'react-dom': 'ReactDOM',
    },
};