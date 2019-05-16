const webpack = require( 'webpack' )
const path    = require( 'path' )

// Naming and path settings
let appName      = '';
const entryPoint = {
	dashboard: './vue/src/rop_main.js' ,
	exclude: './vue/src/rop_exclude_posts.js' ,
	publish_now: './vue/src/rop_publish_now.js' ,
	publish_now_guten: './gutenberg/src/rop_publish_now.js' ,
};
const exportPath = path.resolve( __dirname, './assets/js/build' )

// Enviroment flag
const plugins = []
const env     = process.env.WEBPACK_ENV

// Differ settings based on production flag
if ( env === 'production' ) {
	const UglifyJsPlugin = webpack.optimize.UglifyJsPlugin

	plugins.push( new UglifyJsPlugin( { minimize: true } ) )
	plugins.push(
		new webpack.DefinePlugin(
			{
				'process.env': {
					NODE_ENV: '"production"'
				}
			}
		)
	)

	appName = '.min.js'
} else {
	appName = '.js'
}

// Main Settings config
module.exports = {
	entry: entryPoint,
	output: {
		path: exportPath,
		filename: '[name]' + appName,
		publicPath: '/'
	},
	module: {
		loaders: [
			{ test: /\.(jpe?g|png|gif|svg)$/i, use: [ 'file-loader?name=assets/img/[name].[ext]' ] },
			{
				test: /\.js$/,
				exclude: /(node_modules|bower_components)/,
				loader: 'babel-loader',
				query: {
					presets: ['es2015']
				}
			},
			{
				enforce: 'pre',
				test: /\.vue$/,
				loader: 'eslint-loader',
				exclude: /node_modules/
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader'
			}
		]
	},
	resolve: {
		alias: {
			'vue$': 'vue/dist/vue.esm.js'
		}
	},
	plugins
}
