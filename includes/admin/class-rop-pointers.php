<?php

class Rop_Pointers{

  /**
  * Determines if the tutorial has run.
  *
  * @since   8.1.4
  * @access  public
  */
  public function rop_setup_pointer_support(){
    wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
  }

  /**
  * Determines if the tutorial has run.
  *
  * @since   8.1.4
  * @access  public
  */
  public function rop_get_tutorial_status(){
    ( !empty( get_option( 'rop_tutorial_queued' ) ) ? true : false );
  }


  /**
  * Tutorial pointers for personal plan.
  *
  * @since   8.1.4
  * @access  public
  */
  public function create_rop_license_activation_tutorial_first() {

    //Run activation pointer for Pro plugin if exists and activation has not run pointer
    if( ! class_exists( 'Rop_Pro' ) || get_option( 'rop_start_activation' ) ){
      return;
    }

    // if( ! class_exists( 'Rop_Pro' ) ){
    // 	return;
    // }


    $pointers = array(
      'pointers' => array(
        'settings'          => array(
          'target'       => '#menu-settings',
          'next'         => '',
          'next_trigger' => array(
            'target' => '#menu-settings',
            'event'  => 'click',
          ),
          'options'      => array(
            'content'  => '<h3>' . esc_html__( 'Activate Your New Plugin', 'tweet-old-post' ) . '</h3>' .
            '<p>' . esc_html__( 'Click here to start activating Revive Old Posts(ROP).', 'tweet-old-post' ) . '</p>',
            'position' => array(
              'edge'  => 'left',
              'align' => 'right',
            ),
          ),
        ),
      ),
    );

    update_option( 'rop_start_activation', 1 );
    return $pointers;
  }

  /**
  * Tutorial pointers for personal plan.
  *
  * @since   8.1.4
  * @access  public
  */
  public function create_rop_license_activation_tutorial_last() {

    //Only show if plugin was activated
    if ( get_option( 'rop_end_activation' ) ){
      return;
    };

    $pointers = array(
      'pointers' => array(
        'settings'          => array(
          'target'       => '#tweet_old_post_pro_license',
          'next'         => 'rop-menu',
          'next_trigger' => array(
            'target' => '#tweet_old_post_pro_license',
            'event'  => 'click',
          ),
          'options'      => array(
            'content'  => '<h3>' . esc_html__( 'Enter License Key', 'tweet-old-post' ) . '</h3>' .
            '<p>' . __( sprintf('Grab your license key from your purchase history %shere%s. Then activate it.', '<a href="https://revive.social/your-purchases/" target="_blank">', '</a>'), 'tweet-old-post' ) . '</p>',
            'position' => array(
              'edge'  => 'left',
              'align' => 'right',
            ),
          ),
        ),
        'rop-menu'        => array(
          'target'       => '#toplevel_page_TweetOldPost',
          'next'         => '',
          'next_trigger' => array(),
          'options'      => array(
            'content'  => '<h3>' . esc_html__( 'Learn How it Works', 'tweet-old-post' ) . '</h3>' .
            '<p>' . esc_html__( 'Then click here to get started with your new plugin.', 'tweet-old-post' ) . '</p>',
            'position' => array(
              'edge'  => 'left',
              'align' => 'right',
            ),
          ),
        ),
      ),
    );

    update_option( 'rop_end_activation', 1 );
    return $pointers;
  }

  /**
  * Tutorial pointers for personal plan.
  *
  * @since   8.1.4
  * @access  public
  */
  public function create_rop_personal_plan_tutorial() {

    // if( get_option( 'rop_tutorial_queued' ) ){
    // 	return;
    // }

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
            '<p>' . esc_html__( 'You can add your social media accounts by clicking this button. Let\'s do this later.', 'tweet-old-post' ) . '</p>',
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
            '<p>'. esc_html__( 'E.g. setting this option to 15 would mean that posts older than 15 days will not be shared.', 'tweet-old-post' ) .'</p>',
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
            'content'  => '<h3>' . esc_html__( 'Post types', 'tweet-old-post' ) . '</h3>' .
            '<p>' . esc_html__( 'Rop works with any post type, from products to posts, to custom post types.', 'tweet-old-post' ) . '</p>',
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
            'content'  => '<h3>' . esc_html__( 'Taxonomy filtering', 'tweet-old-post' ) . '</h3>' .
            '<p>' . esc_html__( 'Here you can set which WordPress taxonomies you\'d like to include/exclude from sharing.', 'tweet-old-post' ) . '</p>' .
            '<p>' . __( sprintf( '%sNote:%s', '<strong>', '</strong>' ), 'tweet-old-post' ) . '</p>' .
            '<p>' . __( sprintf( 'Selecting options here and %1$schecking%2$s the Exclude box will %1$sprevent%2$s posts in those taxonomies from sharing.', '<strong>', '</strong>' ), 'tweet-old-post' ) . '</p>' .
            '<p>' . __( sprintf( 'Selecting options here and leaving the Exclude box %1$sunchecked%2$s will %1$sonly share%2$s posts in those taxonomies.', '<strong>', '</strong>' ), 'tweet-old-post' ) . '</p>',
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
            '<p>' . esc_html__( 'ROP not only works on autopilot, it can also be used to push new posts to your social networks immediately!', 'tweet-old-post' ) . '</p>',
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
            'content'  => '<h3>' . esc_html__( 'Custom Messages', 'tweet-old-post' ) . '</h3>' .
            '<p>' . esc_html__( 'You can add multiple custom messages to individual posts! ROP will randomly select one to share.', 'tweet-old-post' ) . '</p>',
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
            '<p>' . __( sprintf( '%sLearn More Here%s', '<a href="#" target="_blank">', '</a>' ), 'tweet-old-post' ) . '</p>',
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
            '<p>' . esc_html__( 'You\'ll be able to have look at the posts scheduled to go out by ROP. You can even schedule or ban them from sharing in the future!', 'tweet-old-post' ) . '</p>' .
            '<p>' . __( sprintf( '%s%sLearn More Here%s%s', '<strong>', '<a href="#" target="_blank">', '</a>', '</strong>'), 'tweet-old-post' ) . '</p>',
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
            'target' => '',
            'event'  => '',
          ),
          'options'      => array(
            'content'  => '<h3>' . esc_html__( 'Share Log', 'tweet-old-post' ) . '</h3>' .
            '<p>' . esc_html__( 'You can track the success and failings of your shares here.', 'tweet-old-post' ) . '</p>' .
            '<p>' . __( sprintf( 'The resolution to most of these possible errors can be found %s%sHere%s%s', '<strong>', '<a href="https://docs.revive.social/" target="_blank">', '</a>', '</strong>' ), 'tweet-old-post' ) . '</p>',
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

    update_option( 'rop_tutorial_queued', 1 );
    return $pointers;
  }

  /**
  * Enqueus the pointer's scripts.
  *
  * @since   8.1.4
  * @access  public
  */
  public function rop_enqueue_pointers() {

    if ( ! $screen = get_current_screen() ) {
      return;
    }

    switch ( $screen->id ) {
      case 'plugins':
      $pointers = $this->create_rop_license_activation_tutorial_first();
      break;
      case 'options-general':
      $pointers = $this->create_rop_license_activation_tutorial_last();
      break;
      case 'toplevel_page_TweetOldPost':
      $pointers = $this->create_rop_personal_plan_tutorial();
      break;
      default:
      return;
    }

    $pointers = wp_json_encode( $pointers );

    ?>
    <script type="text/javascript">
    jQuery( function( $ ) {
      var rop_pointer = <?php echo $pointers ?>;

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
              show_rop_pointer( pointer.next );
            }
          },
          buttons: function( event, t ) {
            if (pointer.next !== 'min-interval') {

            var close   = " <?php echo esc_js( __( 'Dismiss', 'tweet-old-post' ) ) ?>",
            next    = "<?php echo esc_js( __( 'Next', 'tweet-old-post' ) ) ?>",

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
                window.scrollBy(0, 400);
                break;
                case 'custom-share':
                window.scrollBy(0, 100);
                break;
                case 'post-format':
                window.scrollBy(0, -550);
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
