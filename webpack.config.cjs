const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: () => ( {
		...( typeof defaultConfig.entry === 'function' ? defaultConfig.entry() : defaultConfig.entry ),
		'blocks/search-filter/view': path.resolve( process.cwd(), 'src/blocks/search-filter', 'view.js' ),
		'js/button-icon': path.resolve( process.cwd(), 'src/plugins/button-icon', 'index.js' ),
		'css/button-icon': path.resolve( process.cwd(), 'src/plugins/button-icon', 'style.scss' ),
		'js/back-to-top': path.resolve( process.cwd(), 'src/plugins/back-to-top', 'index.js' ),
		'js/back-to-top-view': path.resolve( process.cwd(), 'src/plugins/back-to-top', 'view.js' ),
		'css/back-to-top': path.resolve( process.cwd(), 'src/plugins/back-to-top', 'style.scss' ),
		'js/style-switcher': path.resolve( process.cwd(), 'src/js', 'style-switcher.js' ),
		'js/linkable-blocks-editor': path.resolve( process.cwd(), 'src/js', 'linkable-blocks-editor.js' ),
		'js/linkable-blocks-frontend': path.resolve( process.cwd(), 'src/js', 'linkable-blocks-frontend.js' ),
		'css/linkable-blocks': path.resolve( process.cwd(), 'src/css', 'linkable-blocks.css' ),
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