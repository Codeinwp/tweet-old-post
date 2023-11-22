const webpack = require( 'webpack' )
const TerserPlugin = require( "terser-webpack-plugin" );
const path    = require( 'path' )
const VueLoaderPlugin = require('vue-loader').VueLoaderPlugin;

// Main Settings config
module.exports = (env, argv) => (
	{
		mode: argv.mode,
		devtool: argv.mode === 'production' ? false : 'inline-source-map',
		entry: {
			dashboard: './vue/src/rop_main.js' ,
			exclude: './vue/src/rop_exclude_posts.js' ,
			publish_now: './vue/src/rop_publish_now.js' ,
		},
		output: {
			path: path.resolve( __dirname, './assets/js/build' ),
			filename: '[name].js',
			publicPath: '/'
		},
		module: {
			rules: [
				{ test: /\.(jpe?g|png|gif|svg)$/i, use: [ 'file-loader?name=assets/img/[name].[ext]' ] },
				{
					test: /\.js$/,
					exclude: /(node_modules|bower_components)/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: [
								[
									"@babel/preset-env",
									{
									  useBuiltIns: "usage",
									  corejs: "3.22",
									  targets: [
										"last 2 versions",
										"> 1%",
										"not ie <= 8"
									  ]
									},
								],
							]
						}
					}
				},
				{
					test: /\.vue$/,
					use: 'vue-loader'
				},
				{
					test: /\.s?css$/,
					use: ["style-loader", "css-loader", "sass-loader"],
				},
			]
		},
		resolve: {
			alias: {
				'vue$': 'vue/dist/vue.esm.js'
			}
		},
		plugins: [
			new VueLoaderPlugin(),
		],
		optimization: {
			minimizer: [new TerserPlugin()],
		},
	}
)
