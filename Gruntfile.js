/* jshint node:true */
/* global require, grunt */
module.exports = function ( grunt ) {
	'use strict'

	var loader = require( 'load-project-config' )
	var config = require( 'grunt-plugin-fleet' )
	config     = config()
	loader( grunt, config ).init()
}
