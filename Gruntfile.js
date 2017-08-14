/* jshint node:true */
/* global require */
module.exports = function (grunt) {
    'use strict';

    var loader = require( 'load-project-config' ),
        config = require( 'grunt-plugin-fleet' );
    config = config();
    config.files.php.push( '!inc/PhpFormBuilder.php' );
    config.files.php.push( '!mailin.php' );
    loader( grunt, config ).init();
};