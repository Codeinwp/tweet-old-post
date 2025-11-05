/* jshint ignore:start */
( function ( $ ) {

	var $action_button = $( '#rop_conection_check' );
	var $server_response = $( '#server_responded' );
	var $wordpress_response = $( '#website_responded' );

	if ( $action_button.length ) {
		$( '#rop-debug-table' ).on(
			'click tap',
			'#rop_conection_check',
			function () {
				$server_response.html( 'N/A' ).css( 'color', 'black' );
				$wordpress_response.html( 'N/A' ).css( 'color', 'black' );
				var initial_label = $action_button.val();
				$action_button.attr( 'disabled', 'disabled' ).val( 'Checking connection...' );
				$.ajax(
					{
						type: "GET",
						url: rop_debug.remote_url,
						data: { 'secret_temp_key': rop_debug.nonce, 'respond_to': rop_debug.local_url },
						dataType: 'json', // xml, html, script, json, jsonp, text
						success: function ( data ) {
							if ( typeof data !== 'undefined' ) {
								if ( true === data.success ) {
									$server_response.html( '&#10004; ' + data.message ).css( 'color', 'darkgreen' );
								} else {
									$server_response.html( '&#10006; ' + 'Could not reach ROP Cron System' ).css( 'color', 'darkgreen' );
								}

								if ( true === data.remote_success ) {
									$wordpress_response.html( '&#10004; ' + data.remote_message ).css( 'color', 'darkgreen' );
								} else {
									$wordpress_response.html( '&#10006; ' + data.remote_message ).css( 'color', 'darkred' );
								}
							}
						},
						error: function ( jqXHR, textStatus, errorThrown ) {
							console.log( jqXHR );
							console.log( textStatus );
							console.log( errorThrown );
							$server_response.html( '&#10006; ' + 'Could not reach ROP Cron System' ).css( 'color', 'darkred' );
							$wordpress_response.html( '&#10006; ' + 'Could not reach ROP Cron System' ).css( 'color', 'darkred' );
						},
						// called when the request finishes (after success and error callbacks are executed)
						complete: function () {
							$action_button.removeAttr( 'disabled' ).val( initial_label );
						}
					}
				);

			}
		);
	}

	/**
	 * Clear local data button.
	 *
	 * @type {*|jQuery|HTMLElement}
	 */
	var $clear_local_cron_data = $( '#rop_clear_local' );

	/**
	 * Clear local data ajax response container.
	 *
	 * @type {*|jQuery|HTMLElement}
	 */
	var $clear_local_cron_data_response = $( '#ajax_rop_clear_local' );

	/**
	 * Clear the local data ajax process.
	 */
	if ( $clear_local_cron_data.length ) {
		$( '#rop-debug-table' ).on(
			'click tap',
			'#rop_clear_local',
			function () {

				var clear_data_account_confirm = confirm( "This will delete your Cron server authentication key\n Click ok to continue." );
				if ( clear_data_account_confirm ) {
					var initial_label = $clear_local_cron_data.val();
					$clear_local_cron_data.attr( 'disabled', 'disabled' ).val( 'Removing data...' );
					$.ajax(
						{
							type: "GET",
							url: ajaxurl,
							data: { 'action': 'reset_local_auth_key', 'nonce': rop_debug.nonce },
							dataType: 'json', // xml, html, script, json, jsonp, text
							success: function ( data ) {
								if ( true === data.success ) {
									$clear_local_cron_data_response.html( '<span style="color:darkgreen">&#10004; ' + data.message + '</span>' );
								} else {
									$clear_local_cron_data_response.html( '<span style="color:darkred">&#10006; ' + data.message + '</span>' );
								}
							},
							error: function ( jqXHR, textStatus, errorThrown ) {
								console.log( jqXHR );
								console.log( textStatus );
								console.log( errorThrown );
								$clear_local_cron_data_response.html( '<span style="color:darkred">&#10006; ' + rop_debug.action_fail + '</span>' );
							},
							// called when the request finishes (after success and error callbacks are executed)
							complete: function ( jqXHR, textStatus ) {
								$clear_local_cron_data.removeAttr( 'disabled' ).val( initial_label );
							}
						}
					);
				}
				//--

			}
		);
	}

	/**
	 * Delete remote account data button.
	 *
	 * @type {*|jQuery|HTMLElement}
	 */
	var $delete_account_data = $( '#rop_remove_account' );

	/**
	 *
	 * @type {*|jQuery|HTMLElement}
	 */
	var $delete_account_data_response = $( '#ajax_rop_remove_account' );

	/**
	 *
	 */
	if ( $delete_account_data.length ) {
		$( '#rop-debug-table' ).on(
			'click tap',
			'#rop_remove_account',
			function () {

				var delete_account_confirm = confirm( "This will delete your remote cron account\n Click ok to continue." );

				if ( delete_account_confirm ) {
					var initial_label = $delete_account_data.val();
					$delete_account_data.attr( 'disabled', 'disabled' ).val( 'Removing data...' );
					$.ajax(
						{
							type: "GET",
							url: ajaxurl,
							data: { 'action': 'remove_remote_account', 'nonce': rop_debug.nonce },
							dataType: 'json', // xml, html, script, json, jsonp, text
							success: function ( data ) {
								if ( true === data.success ) {
									$delete_account_data_response.html( '<span style="color:darkgreen">&#10004; ' + data.message + '</span>' );
								} else {
									$delete_account_data_response.html( '<span style="color:darkred">&#10006; ' + data.message + '</span>' );
								}
							},
							error: function ( jqXHR, textStatus, errorThrown ) {
								console.log( jqXHR );
								console.log( textStatus );
								console.log( errorThrown );
								$delete_account_data_response.html( '<span style="color:darkred">&#10006; ' + rop_debug.action_fail + '</span>' );
							},
							// called when the request finishes (after success and error callbacks are executed)
							complete: function () {
								$delete_account_data.removeAttr( 'disabled' ).val( initial_label );
							}
						}
					);
				}
				// ---

			}
		);
	}

} )( jQuery );

/* jshint ignore:end */