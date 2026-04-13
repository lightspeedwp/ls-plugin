const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: () => ( {
		...( typeof defaultConfig.entry === 'function' ? defaultConfig.entry() : defaultConfig.entry ),
		'js/button-icon': path.resolve( process.cwd(), 'src/plugins/button-icon', 'index.js' ),
		'css/button-icon': path.resolve( process.cwd(), 'src/plugins/button-icon', 'style.scss' ),
		'js/style-switcher': path.resolve( process.cwd(), 'src/js', 'style-switcher.js' ),
	} ),
	output: {
		...defaultConfig.output,
		path: path.resolve( process.cwd(), 'build' ),
		filename: '[name].js',
		clean: true,
	},
	plugins: [
		...defaultConfig.plugins,
		new RemoveEmptyScriptsPlugin( {
			stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
		} ),
	],
};