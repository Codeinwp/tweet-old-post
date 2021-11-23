<?php
/**
 * The model for managing the posts queued for sharing.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin/models
 */

/**
 * Class Rop_Queue_Model
 */
class Rop_Queue_Model extends Rop_Model_Abstract {


	/**
	 * Holds the logger
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     Rop_Logger $logger The logger handler.
	 */
	protected $logger;
	/**
	 * Holds the queue namespace.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $queue The queue option key.
	 */
	private $queue_namespace = 'queue';
	/**
	 * Holds the active queue.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $queue The active queue array.
	 */
	private $queue;
	/**
	 * An instance of the Posts Selector.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     Rop_Posts_Selector_Model An instance of the Posts Selector.
	 */
	private $selector;

	/**
	 * An instance of the Scheduler Model
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     Rop_Scheduler_Model An instance of the Scheduler Model.
	 */
	private $scheduler;

	/**
	 * Rop_Queue_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct( 'rop_queue' );

		$this->selector  = new Rop_Posts_Selector_Model();
		$this->logger    = new Rop_Logger();
		$this->scheduler = new Rop_Scheduler_Model();
		$this->queue     = $this->get( $this->queue_namespace );
		if ( ! is_array( $this->queue ) ) {
			$this->queue = array();
		}
	}

	/**
	 * Update a queue object with custom data, passed by the user.
	 *
	 * @param string $account_id The account ID.
	 * @param int    $post_id The post ID referenced.
	 * @param array  $custom_data The custom data.
	 *
	 * @return bool
	 * @since   8.0.0
	 * @access  public
	 */
	public function update_queue_object( $account_id, $post_id, $custom_data ) {
		$key             = '_rop_edit_' . md5( $account_id );
		$custom_data_old = get_post_meta( $post_id, $key, true );
		if ( ! is_array( $custom_data_old ) ) {
			$custom_data_old = array();
		}
		$custom_data = wp_parse_args( $custom_data, $custom_data_old );
		update_post_meta( $post_id, $key, $custom_data );

		return true;
	}

	/**
	 * Utility method to remove from queue.
	 *
	 * @param int    $timestamp The timestamp which we should clear.
	 * @param string $account_id The account ID.
	 * @param bool   $refresh Whether to refresh the rop_data property in parent abstract class with new rop_data option value.
	 *
	 * @return mixed
	 * @since   8.0.0
	 * @access  public
	 */
	public function remove_from_queue( $timestamp, $account_id, $refresh = false ) {
		$index = $this->scheduler->remove_timestamp( $timestamp, $account_id );
		if ( isset( $this->queue[ $account_id ], $this->queue[ $account_id ][ $index ] ) ) {
			$posts = $this->queue[ $account_id ][ $index ];
		} else {
			return false;
		}

		if ( empty( $posts ) ) {
			return false;
		}

		unset( $this->queue[ $account_id ][ $index ] );
		$this->update_queue( $this->queue );
		foreach ( $posts as $post ) {
			$this->selector->update_buffer( $account_id, $post, $refresh );
		}

		return true;
	}

	/**
	 * Update queue object.
	 *
	 * @param array $queue New queue to update.
	 */
	public function update_queue( $queue ) {
		$this->set( $this->queue_namespace, $queue );
		$this->queue = $queue;
	}

	/**
	 * Removes an account from queue
	 *
	 * @param string $account_id The account ID.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function remove_account_from_queue( $account_id ) {
		unset( $this->queue[ $account_id ] );
		$this->set( $this->queue_namespace, $this->queue );
	}

	/**
	 * Mark a post_id as blocked for the account.
	 *
	 * @param int    $post_id The post id.
	 * @param string $account_id The account ID.
	 *
	 * @return bool Ban status.
	 * @since   8.0.0
	 * @access  public
	 */
	public function ban_post( $post_id, $account_id ) {
		$queue = $this->get_queue();
		if ( empty( $queue ) ) {
			return false;
		}

		if ( ! isset( $queue[ $account_id ] ) ) {
			return false;
		}
		$this->selector->mark_as_blocked( $account_id, $post_id );
		foreach ( $queue[ $account_id ] as $key => $posts ) {
			$queue[ $account_id ][ $key ] = array_diff( $posts, array( $post_id ) );
			$queue[ $account_id ][ $key ] = array_values( $queue[ $account_id ][ $key ] );
		}
		$this->update_queue( $queue );

		return true;

	}

	/**
	 * Method to retrieve the queue based on the number of timeline events.
	 * It creates the queue order, refill it in case that we don't have enought elements.
	 *
	 * @return array
	 * @since   8.0.0
	 * @access  public
	 */
	public function get_queue() {
		$settings    = new Rop_Settings_Model();
		$no_of_posts = $settings->get_number_of_posts();

		$upcoming_events = $this->scheduler->get_all_upcoming_events();

		$current_queue   = $this->queue;
		if ( empty( $upcoming_events ) ) {
			return array();
		}
		$normalized_queue = array();
		$event_queue = array();

		foreach ( $upcoming_events as $account_id => $events ) {

			$account_queue                   = isset( $current_queue[ $account_id ] ) ? $current_queue[ $account_id ] : array();
			// Normalizes the array keys so next available posts can go to the bottom of the Sharing Queue (stack)
			$account_queue = array_values( $account_queue );

			$normalized_queue[ $account_id ] = array();

			$post_pool = '';
			$post_pool = $this->selector->select( $account_id );

			if ( empty( $post_pool ) ) {
				$this->logger->alert_error( 'No posts are available to share for your account. Try activating the Share more than once option or changing the minimum and maximum post age setting to widen the pool of available posts.' );
				continue;
			}

			foreach ( $events as $index => $event ) {
				$event_queue = isset( $account_queue[ $index ] ) ? $account_queue[ $index ] : array();
				/**
				 * Bail if we have queue already filled for this account.
				 */
				if ( count( $event_queue ) === $no_of_posts ) {
					$normalized_queue[ $account_id ][ $index ] = $event_queue;
					continue;
				}
				/**
				 * If we have more posts in queue than necessary, slice them.
				 * This might happen when user changes the no of posts shared.
				 */
				if ( count( $event_queue ) > $no_of_posts ) {
					$normalized_queue[ $account_id ][ $index ] = array_slice( $event_queue, 0, $no_of_posts );
					continue;
				}

				if ( empty( $post_pool ) ) {
					continue;
				}

				$items_needed = $no_of_posts - count( $event_queue );
				$i            = 0;

				while ( $i < $items_needed ) {
					if ( empty( $post_pool ) ) {
						break;
					}
					$rand_key      = rand( 0, count( $post_pool ) - 1 );
					// Grab a random post id from the pool to add to the queue.
					$post_id       = $post_pool[ $rand_key ];

					$event_queue[] = $post_id;
					unset( $post_pool[ $rand_key ] );

					$post_pool = array_values( $post_pool );
					$i++;

				}

				$current_normalized_queue = $normalized_queue[ $account_id ];
				// Get the last 2 items in the queue.
				$last_two = array_slice( $current_normalized_queue, apply_filters( 'rop_allowed_consecutive_posts', -2 ) );

				$is_consecutive = array_search( $event_queue, $last_two );

				// If the new entry is is not the same as any of the last two items, then add it to the queue.
				if ( $is_consecutive === false ) {
					$normalized_queue[ $account_id ][ $index ] = $event_queue;
				}

				// Below causes more issues with post stacking. Solution
				// Is to regen account queue keys
				// $new_queue = array_merge( $account_queue, array($event_queue) );
				// $normalized_queue[ $account_id ] = $new_queue;
				// $account_queue  = $new_queue;

			}
		}

		$this->set( $this->queue_namespace, $normalized_queue );
		$this->queue = $normalized_queue;

		return $normalized_queue;
	}

	/**
	 * Method to retrieve the queue formatted for display.
	 *
	 * @return array
	 * @since   8.0.0
	 * @access  public
	 */
	public function get_ordered_queue() {
		$cron_helper = new Rop_Cron_Helper();
		/**
		 * Bail if the sharing is not started.
		 */
		if ( $cron_helper->get_status() === false ) {
			return array();
		}
		$queue   = $this->build_queue();
		$ordered = array();

		foreach ( $queue as $account_id => $data ) {
			foreach ( $data as $index => $events_posts ) {
				if ( ! isset( $events_posts['posts'] ) ) {
					continue;
				}
				foreach ( $events_posts['posts'] as $post_id ) {
					/**
					 * Prevent showing due posts in the frontend queue.
					 * It will cover delays between posts are sent to the networks and removal from the queue.
					 */
					if ( Rop_Scheduler_Model::get_current_time() > $events_posts['time'] ) {
						continue;
					}

					/*
					* Prevents queue from showing posts that do not exist
					* on the website. This can occur when a post is deleted
					* and queue hasn't yet refreshed.
					*/
					if ( empty( get_post_status( $post_id ) ) ) {
						continue;
					}

					$ordered[] = array(
						'time'      => $events_posts['time'],
						'post_data' => array(
							'time'       => $events_posts['time'],
							'account_id' => $account_id,
							'date'       => Rop_Scheduler_Model::get_date( $events_posts['time'] ),
							'post_id'    => $post_id,
							'content'    => $this->prepare_post_object( $post_id, $account_id ),
						),
						'post_id'   => $post_id,
					);
				}
			}
		}
		usort(
			$ordered,
			function ( $a, $b ) {
				return ( ( $a['time'] ) - ( $b['time'] ) );
			}
		);

		return $ordered;
	}

	/**
	 * Method to build the queue for posts to be published on update/create.
	 *
	 * @access  public
	 *
	 * @param int   $post_id the Post ID.
	 * @param array $accounts_data The accounts data, may either be the accounts the user has selected to share the post to (by clicking the instant sharing checkbox on post edit screen, would also contain the custom share message if any was entered), or an array of active accounts to share to by the share_scheduled_future_post() method.
	 * @param bool  $is_future_post Whether method was called by share_scheduled_future_post() method.
	 * @param bool  $true_instant_share Whether the share immediately option is checked.
	 *
	 * @return array
	 */
	public function build_queue_publish_now( $post_id = '', $accounts_data = array(), $is_future_post = false, $true_instant_share = false ) {

		if ( $is_future_post ) {
			$accounts         = $accounts_data;
			$normalized_queue = array();

			$index = 0;

			foreach ( $accounts as $account_id ) {
				$normalized_queue[ $account_id ][ $index ] = array(
					'post' => array( $post_id ),
				);
				$index ++;
			}

			return $normalized_queue;
		} elseif ( $true_instant_share ) {
			$accounts         = $accounts_data;
			$normalized_queue = array();

			$index = 0;

			foreach ( $accounts as $account_id => $custom_instant_share_message ) {
				$normalized_queue[ $account_id ][ $index ] = array(
					'post'                         => array( $post_id ),
					'custom_instant_share_message' => $custom_instant_share_message,
				);
				$index ++;
			}

			return $normalized_queue;

		} else {

			$selector = new Rop_Posts_Selector_Model();
			$posts    = $selector->get_publish_now_posts();

			$normalized_queue = array();
			if ( ! $posts ) {
				return $normalized_queue;
			}

			$index = 0;
			foreach ( $posts as $post_id ) {
				$accounts = get_post_meta( $post_id, 'rop_publish_now_accounts', true );
				if ( ! $accounts ) {
					continue;
				}

				// delete the meta so that when the post loads again after publishing, the checkboxes are cleared.
				delete_post_meta( $post_id, 'rop_publish_now_accounts' );

				foreach ( $accounts as $account_id => $custom_instant_share_message ) {
					$normalized_queue[ $account_id ][ $index ] = array(
						'post'                         => array( $post_id ),
						'custom_instant_share_message' => $custom_instant_share_message,
					);
				}
				$index ++;
			}

			return $normalized_queue;

		}

	}

	/**
	 * Method to build the queue according to the timeline.
	 *
	 * @return array
	 * @since   8.0.0
	 * @access  public
	 */
	public function build_queue() {
		$queue            = $this->get_queue();

		$upcoming_events  = $this->scheduler->get_all_upcoming_events();
		$normalized_queue = array();
		foreach ( $upcoming_events as $account_id => $events ) {
			$account_queue                   = $queue[ $account_id ];
			$normalized_queue[ $account_id ] = array();
			foreach ( $events as $index => $event ) {
				if ( empty( $account_queue[ $index ] ) ) {
					continue;
				}
				$normalized_queue[ $account_id ][ $index ] = array(
					'time'  => $event,
					'posts' => $account_queue[ $index ],
				);
			}
		}

		return $normalized_queue;
	}

	/**
	 * Rebuilds the post object using the updated post format
	 * and preserving the old user settings. Or creates a new one.
	 *
	 * @param integer $post_id A WordPress Post Object.
	 * @param string  $account_id The account ID.
	 *
	 * @return array
	 * @since   8.0.0
	 * @access  private
	 */
	public function prepare_post_object( $post_id, $account_id ) {
		$post_format_helper = new Rop_Post_Format_Helper();
		// $post_format_helper->set_post_format( $account_id );
		$filtered_post = $post_format_helper->get_formated_object( $post_id, $account_id );

		return $filtered_post;
	}

	/**
	 * Method to clear the queue
	 *
	 * @return void
	 * @since   8.0.0
	 * @access  public
	 */
	public function clear_queue( $account_id = false ) {
		if ( empty( $account_id ) ) {
			$this->queue = array();
		} else {
			$this->queue[ $account_id ] = array();
		}
		$this->set( 'queue', $this->queue );
	}

	/**
	 * Method to skip post for the given account.
	 *
	 * @param int    $post_id The post uid.
	 * @param string $account_id The account ID.
	 *
	 * @return bool
	 * @since   8.0.0
	 * @access  public
	 */
	public function skip_post( $post_id, $account_id ) {
		$queue = $this->get_queue();
		if ( empty( $queue ) ) {
			return false;
		}

		if ( ! isset( $queue[ $account_id ] ) ) {
			return false;
		}
		$this->selector->update_buffer( $account_id, $post_id );
		foreach ( $queue[ $account_id ] as $key => $posts ) {
			$queue[ $account_id ][ $key ] = array_diff( $posts, array( $post_id ) );
			$queue[ $account_id ][ $key ] = array_values( $queue[ $account_id ][ $key ] );
		}
		$this->update_queue( $queue );

		return true;
	}


}
