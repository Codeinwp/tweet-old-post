<?php
class Rop_Post_Format_Helper {

	private $post_format = false;

	public function set_post_format( $account_id ) {
		$parts = explode( '_', $account_id );
		$service = $parts[0];
		$post_format_model = new Rop_Post_Format_Model( $service );
		$this->post_format = $post_format_model->get_post_format( $account_id );
	}

	public function get_custom_field_value( $post_id, $field_key ) {
		return get_post_custom_values( $field_key, $post_id );
	}

	public function build_content( WP_Post $post ) {
		$ch = new Rop_Content_Helper();
		$max_length = $this->post_format['maximum_length'];
		if ( $this->post_format ) {
			switch ( $this->post_format['post_content'] ) {
				case 'post_title':
					$content = $post->post_title;
					break;
				case 'post_content':
					$content = $post->post_content;
					break;
				case 'post_title_content':
					$content = $post->post_title . ' ' . $post->post_content;
					break;
				case 'custom_field':
					$content = $this->get_custom_field_value( $post->ID, $this->post_format['custom_meta_field'] );
					break;
				default :
					$content = 'We found nothing here!!!';
					break;
			}

			$custm_length = 0;
			if ( strlen( $this->post_format['custom_text'] ) != 0 ) {
				$custm_length = strlen( $this->post_format['custom_text'] ) + 1; // one char added for the space.
				switch ( $this->post_format['custom_text_pos'] ) {
					case 'beginning':
						$content = $this->post_format['custom_text'] . ' ' . $content;
						break;
					default :
						$content = $content . ' ' . $this->post_format['custom_text'];
						break;
				}
			}

            switch ( $this->post_format['hashtags'] ) {
                case 'no-hashtags':
                    $hastags = '';
                    break;
                case 'common-hashtags':
                    $hastags = $this->post_format['hashtags_common'];
                    break;
                case 'categories-hashtags':
                    if ( $post->post_type == "post" ) {
                        $post_categories = get_the_category( $post->ID );
                        foreach ( $post_categories as $category ) {
                            $thisHashtag = $category->slug;
                            if ( $this->tweetContentHashtag( $tweetContent, $thisHashtag ) !== false ) { // if the hashtag exists in $tweetContent
                                $tweetContent = $this->tweetContentHashtag($tweetContent, $thisHashtag); // simply add a # there
                                $maximum_hashtag_length--; // subtract 1 for the # we added to $tweetContent
                            }
                            elseif ( strlen( $thisHashtag . $newHashtags ) <= $maximum_hashtag_length || $maximum_hashtag_length == 0 ) {
                                $newHashtags = $newHashtags . " #" . preg_replace( '/-/', '', strtolower( $thisHashtag ) );
                            }
                        }
                    } else {
                        if ( CWP_TOP_PRO ) {
                            global $CWP_TOP_Core_PRO;
                            $newHashtags = $CWP_TOP_Core_PRO->topProGetCustomCategories( $postQuery, $maximum_hashtag_length );
                        }
                    }
                    break;
                case 'tags-hashtags':
                    $hastags = $post->post_title;
                    break;
                case 'custom-hashtags':
                    $hastags = $post->post_title;
                    break;
                default :
                    $hastags = '';
                    break;
            }

			print_r( $this->post_format );
		}

		return 'N/A';
	}


}
