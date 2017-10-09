var webpack = require( 'webpack' )
var path = require( 'path' )

// Naming and path settings
var appName = 'rop'
var entryPoint = './vue/src/rop_main.js'
var exportPath = path.resolve( __dirname, './assets/js/build' )

// Enviroment flag
var plugins = []
var env = process.env.WEBPACK_ENV

// Differ settings based on production flag
if (env === 'production') {
	var UglifyJsPlugin = webpack.optimize.UglifyJsPlugin

	plugins.push( new UglifyJsPlugin( { minimize: true } ) )
	plugins.push(new webpack.DefinePlugin({
		'process.env': {
			NODE_ENV: '"production"'
		}
	}))

	appName = appName + '.min.js'
} else {
	appName = appName + '.js'
}

// Main Settings config
module.exports = {
	entry: entryPoint,
	output: {
		path: exportPath,
		filename: appName,
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
