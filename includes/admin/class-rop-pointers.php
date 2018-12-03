<?php
/**
 * The plugin pointers class.
 *
 * This is used to help users get familiar with the plugin's options
 *
 * @since      8.1.4
 * @package    Rop
 * @subpackage Rop/includes
 * @author     ThemeIsle <friends@revive.social>
 */
class Rop_Pointers {

	/**
	 * Pointer support script and CSS.
	 *
	 * @since   8.1.4
	 * @access  public
	 */
	public function rop_setup_pointer_support() {
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
	}

	/**
	 * Fix dismiss button orientation.
	 *
	 * @since   8.1.4
	 * @access  public
	 */
	public function rop_pointer_button_css() {
		$side = is_rtl() ? 'right' : 'left';

		echo "
    <style>
    html{
      scroll-behavior: smooth;
    }
    .rop-pointer-buttons .close {
      float: $side;
      margin: 4px 20px;
    }
    </style>
    ";
	}

	/**
	 * Tutorial pointers for plugin dashboard.
	 *
	 * @since   8.1.4
	 * @access  public
	 */
	public function create_rop_menu_pointer() {

		if ( get_option( 'rop_menu_pointer_queued' ) || ! empty( get_option( 'rop_data' ) ) ) {
			return;
		}

		$pointers = array(
			'pointers' => array(
				'settings'          => array(
					'target'       => '#toplevel_page_TweetOldPost',
					'next'         => '',
					'next_trigger' => array(),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Get Started', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Click here to get started with Revive Old Posts (ROP).', 'tweet-old-post' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'left',
						),
					),
				),
			),
		);

		update_option( 'rop_menu_pointer_queued', 1 );
		return $pointers;
	}

	/**
	 * Tutorial pointers for plugin dashboard.
	 *
	 * @since   8.1.4
	 * @access  public
	 */
	public function create_rop_dashboard_pointers() {

		if ( get_option( 'rop_dashboard_pointers_queued' ) || ! empty( get_option( 'rop_data' ) ) ) {
			return;
		}

		$pointers = array(
			'pointers' => array(
				'accounts'          => array(
					'target'       => '#accounts',
					'next'         => 'add-account',
					'next_trigger' => array(
						'target' => '#accounts',
						'event'  => 'click',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Accounts Area', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Your social media accounts will show here once connected.', 'tweet-old-post' ) . '</p>',
						'position' => array(
							'edge'  => 'top',
							'align' => 'left',
						),
					),
				),
				'add-account'        => array(
					'target'       => '#rop-add-account-btn',
					'next'         => 'general',
					'next_trigger' => array(
						'target' => '#rop-add-account-btn',
						'event'  => 'click',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Adding Accounts', 'tweet-old-post' ) . '</h3>' .
						'<p>' . sprintf( esc_html__( 'You can add your social media accounts by clicking this button. %1$sLet\'s do this later%2$s.', 'tweet-old-post' ), '<strong>', '</strong>' ) . '</p>',
						'position' => array(
							'edge'  => 'bottom',
							'align' => 'left',
						),
					),
				),
				'general'        => array(
					'target'       => '#generalsettings',
					'next'         => 'min-interval',
					'next_trigger' => array(
						'target' => '#generalsettings',
						'event'  => 'click',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'General Settings', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'This is the main configuration page of the plugin, we\'ll go through a few of the settings, click it now.', 'tweet-old-post' ) . '</p>',
						'position' => array(
							'edge'  => 'top',
							'align' => 'left',
						),
					),
				),
				'min-interval'        => array(
					'target'       => '#default_interval',
					'next'         => 'min-post-age',
					'next_trigger' => array(
						'target' => '#default_interval',
						'event'  => 'input click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Time Between Shares', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Here you can set how many hours you\'d like between shares.', 'tweet-old-post' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'min-post-age'        => array(
					'target'       => '#min_post_age',
					'next'         => 'max-post-age',
					'next_trigger' => array(
						'target' => '#min_post_age',
						'event'  => 'input click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Minimum Post Age', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Here you can set how old posts should be before they are eligible to be shared.', 'tweet-old-post' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'max-post-age'        => array(
					'target'       => '#max_post_age',
					'next'         => 'share-more-than-once',
					'next_trigger' => array(
						'target' => '#max_post_age',
						'event'  => 'input click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Maximum Post Age', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Here you can set the maximum age of posts that are eligible to be shared.', 'tweet-old-post' ) . '</p>' .
						'<p>' . esc_html__( 'E.g. setting this option to 15 would mean that posts older than 15 days will not be shared.', 'tweet-old-post' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'share-more-than-once'        => array(
					'target'       => '#share_more_than_once',
					'next'         => 'post-types',
					'next_trigger' => array(
						'target' => '#share_more_than_once',
						'event'  => 'input click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Autopilot', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Checking this option ensures that your posts share perpetually.', 'tweet-old-post' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'post-types'        => array(
					'target'       => '#rop_post_types',
					'next'         => 'taxonomies',
					'next_trigger' => array(
						'target' => '#rop_post_types',
						'event'  => 'input click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Post Types', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Rop works with any post type, from products to posts, to custom post types.', 'tweet-old-post' ) . '</p>' .
						'<p>' . esc_html__( 'You can share media straight from your media library!', 'tweet-old-post' ) . '</p>' .
						'<p>' . sprintf( __( '%1$s%2$sLearn more about this feature%3$s%4$s.', 'tweet-old-post' ), '<strong>', '<a href="https://docs.revive.social/article/968-share-different-post-types-w-revive-old-posts" target="_blank">', '</a>', '</strong>' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'taxonomies'        => array(
					'target'       => '#rop_taxonomies',
					'next'         => 'instant-share',
					'next_trigger' => array(
						'target' => '#rop_taxonomies',
						'event'  => 'input click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Taxonomy Filtering', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Here you can set which WordPress taxonomies you\'d like to include/exclude from sharing.', 'tweet-old-post' ) . '</p>' .
						'<p>' . sprintf( __( '%1$sNote:%2$s', 'tweet-old-post' ), '<strong>', '</strong>' ) . '</p>' .
						'<p>' . sprintf( __( 'Selecting options here and %1$schecking%2$s the Exclude box will %1$sprevent%2$s posts in those taxonomies from sharing.', 'tweet-old-post' ), '<strong>', '</strong>' ) . '</p>' .
						'<p>' . sprintf( __( 'Selecting options here and leaving the Exclude box %1$sunchecked%2$s will %1$sonly share%2$s posts in those taxonomies.', 'tweet-old-post' ), '<strong>', '</strong>' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'instant-share'        => array(
					'target'       => '#rop_instant_share',
					'next'         => 'custom-share',
					'next_trigger' => array(
						'target' => '#rop_instant_share',
						'event'  => 'input click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Share on Publish', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'ROP not only works on autopilot, it can also be used to push new posts to your social networks immediately.', 'tweet-old-post' ) . '</p>' .
						'<p>' . sprintf( __( '%1$s%2$sLearn more about this feature%3$s%4$s.', 'tweet-old-post' ), '<strong>', '<a href="https://docs.revive.social/article/933-how-to-share-posts-immediately-with-revive-old-posts" target="_blank">', '</a>', '</strong>' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'custom-share'        => array(
					'target'       => '#rop_custom_share_msg',
					'next'         => 'post-format',
					'next_trigger' => array(
						'target' => '#rop_custom_share_msg',
						'event'  => 'input click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Share Content Variations', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'You can add multiple custom messages to individual posts as share variations! ROP will randomly select one to share.', 'tweet-old-post' ) . '</p>' .
						'<p>' . sprintf( __( '%1$s%2$sLearn more about this feature%3$s%4$s.', 'tweet-old-post' ), '<strong>', '<a href="https://docs.revive.social/article/971-how-to-add-variations-to-revive-old-posts-shares" target="_blank">', '</a>', '</strong>' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'post-format'        => array(
					'target'       => '#postformat',
					'next'         => 'custom-schedule',
					'next_trigger' => array(
						'target' => '#postformat',
						'event'  => 'input click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Post Format', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Once you\'ve connected an account(s) you\'ll be able to configure the settings for the account(s) here.', 'tweet-old-post' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'custom-schedule'        => array(
					'target'       => '#customschedule',
					'next'         => 'sharing-queue',
					'next_trigger' => array(
						'target' => '#customschedule',
						'event'  => 'click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Custom Schedule', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Custom scheduling allows you to refine the post times and days of your posts.', 'tweet-old-post' ) . '</p>',
						'<p>' . sprintf( __( '%1$s%2$sLearn more about this feature%3$s%4$s.', 'tweet-old-post' ), '<strong>', '<a href="https://docs.revive.social/article/972-revive-old-posts-custom-schedule-feature" target="_blank">', '</a>', '</strong>' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'sharing-queue'        => array(
					'target'       => '#sharingqueue',
					'next'         => 'log',
					'next_trigger' => array(
						'target' => '#sharingqueue',
						'event'  => 'click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Sharing Queue', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'You\'ll be able to have look at the posts scheduled to go out by ROP. You can even skip or block them from sharing in the future.', 'tweet-old-post' ) . '</p>' .
						'<p>' . sprintf( __( '%1$s%2$sLearn more about this feature%3$s%4$s.', 'tweet-old-post' ), '<strong>', '<a href="https://docs.revive.social/article/973-working-with-revive-old-posts-sharing-queue" target="_blank">', '</a>', '</strong>' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'log'        => array(
					'target'       => '#logs',
					'next'         => 'start-stop',
					'next_trigger' => array(
						'target' => '#logs',
						'event'  => 'click change',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Share Log', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'You can track the success and failings of your shares here.', 'tweet-old-post' ) . '</p>' .
						'<p>' . sprintf( __( 'The resolution to most of these possible errors can be found %1$s%2$sHere%3$s%4$s.', 'tweet-old-post' ), '<strong>', '<a href="https://docs.revive.social/" target="_blank">', '</a>', '</strong>' ) . '</p>',
						'position' => array(
							'edge'  => 'left',
							'align' => 'right',
						),
					),
				),
				'start-stop'        => array(
					'target'       => '#rop_start_stop_btn',
					'next'         => '',
					'next_trigger' => array(
						'target' => '',
						'event'  => '',
					),
					'options'      => array(
						'content'  => '<h3>' . esc_html__( 'Start & Forget', 'tweet-old-post' ) . '</h3>' .
						'<p>' . esc_html__( 'Once you\'ve connected your accounts and setup their Post Format settings, use this button to start the plugin.', 'tweet-old-post' ) . '</p>',
						'position' => array(
							'edge'  => 'right',
							'align' => 'left',
						),
					),
				),
			),
		);

		update_option( 'rop_dashboard_pointers_queued', 1 );
		return $pointers;
	}

	/**
	 * Enqueues the pointer's scripts.
	 *
	 * @since   8.1.4
	 * @access  public
	 */
	public function rop_enqueue_pointers() {

		if ( ! $screen = get_current_screen() ) {
			return;
		}

		$general_settings = new Rop_Global_Settings;

		switch ( $screen->id ) {
			case 'plugins':
				$pointers = $this->create_rop_menu_pointer();
				break;
			case 'toplevel_page_TweetOldPost':
				$pointers = $this->create_rop_dashboard_pointers();
				break;
			default:
				return;
		}

		$pointers = wp_json_encode( $pointers );

		?>
	  <script type="text/javascript">
	  jQuery( function( $ ) {

		var rop_pointer = <?php echo $pointers; ?>;
	  var rop_license = <?php echo $general_settings->license_type(); ?>;

	  setTimeout( init_rop_pointer, 800 );

	  function init_rop_pointer() {
		$.each( rop_pointer.pointers, function( i ) {
		  show_rop_pointer( i );
		  return false;
		});
	  }

	  function show_rop_pointer( id ) {
		var pointer = rop_pointer.pointers[ id ];
		var options = $.extend( pointer.options, {
		  pointerClass: 'wp-pointer rop-pointer',
		  close: function() {
			if ( pointer.next ) {
			  // Minimum sharing schedule option not present in Business and Marketer plans
			  if ( pointer.next == 'min-interval' && rop_license > 1 ){
				pointer = rop_pointer.pointers[ 'min-interval' ];
			  }
			  show_rop_pointer( pointer.next );
			}
		  },
		  buttons: function( event, t ) {

			if ( pointer.next !== 'min-interval' ) {

			  var close   = " <?php echo esc_js( __( 'Dismiss', 'tweet-old-post' ) ); ?>",
			  next    = "<?php echo esc_js( __( 'Next', 'tweet-old-post' ) ); ?>",

			  button  = $( '<a class=\"close\" href=\"#\">' + close + '</a>' ),
			  button2 = $( '<a class=\"button button-primary next\" href=\"#\">' + next + '</a>' ),
			  wrapper = $( '<div class=\"rop-pointer-buttons\" />' );

			  button.bind( 'click.pointer', function(e) {
				e.preventDefault();
				t.element.pointer('destroy');
			  });

			  button2.bind( 'click.pointer', function(e) {
				e.preventDefault();
				t.element.pointer('close');

				switch( pointer.next ){
				  case 'activate-rop':
				  window.scrollBy(0, 400);
				  break;
				  case 'rop-menu':
				  window.scrollBy(0, 400);
				  break;
				  case 'post-types':
				  window.scrollBy(0, 350);
				  break;
				  case 'custom-share':
				  window.scrollBy(0, 190);
				  break;
				  case 'post-format':
				  window.scrollBy(0, -590);
				  break;
				}
			  });

			  wrapper.append( button );
			  wrapper.append( button2 );

			  return wrapper;
			}
		  },
		} );

		var this_pointer = $( pointer.target ).pointer( options );
		this_pointer.pointer( 'open' );

		if ( pointer.next_trigger ) {
		  $( pointer.next_trigger.target ).on( pointer.next_trigger.event, function() {
			setTimeout( function() { this_pointer.pointer( 'close' ); }, 400 );
		  });
		}
	  }
	});
	</script>
	<?php

	}

}
