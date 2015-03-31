<?php

add_filter( 'sportspost_sources', 'add_iconsportswire_source', 20 );
function add_iconsportswire_source( $sources ) {
	$sources['Icon_Sportswire'] = array(
		'source' => 'Icon_Sportswire',
		'core' => true
	);
	return $sources;
}

/**
 * Icon Sportswire image source
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin
 * @since   2.0.0
 *
**/
class SportsPost_Photos_Source_Icon_Sportswire extends SportsPost_Photos_Source {

	public $host = 'http://iconsportswire.com/api/v1/';
	public $format = 'json';
	protected $name = 'Icon Sportswire';
 	private $default_count = 40;
 	private $settings = array();
 	
	/**
	 * Class constructor.
	 *
	 * @since 2.0.0
	 */
 	function __construct() {
		$this->settings = array(
			'getLeagueImages' => array(
				'name' => __( 'Browse Photos', 'sportspost' ),
				'param' => true,
				'param_type' => 'select',
				'param_dynamic' => true,
				'param_desc' => __( 'Search by league', 'sportspost' )
			),
			'getSearchImages' => array(
				'name' => __( 'Search Photos', 'sportspost' ),
				'param' => true,
				'param_type' => 'text',
				'param_desc' => __( 'Search by keyword', 'sportspost' )
			),
		);		
 		parent::__construct(
			$this->host,
			$this->format,
			$this->settings,
			$this->default_count
		);
		add_filter( 'sportspost_pre_insert', array( $this, 'import' ) );
		add_filter( 'sportspost_default_filters', array( $this, 'sportspost_default_filters' ) );
	}
	
	/**
	 * Returns URL format.
	 *
	 * @since 2.0.0
	 */
	public function get_format( $url ) {
		return "{$this->host}{$url}";
	}
	
	/**
	 * Helper function to generate caption excerpts.
	 *
	 * @since 2.0.0
	 */
	public function get_caption_excerpt( $caption ) {
		$separators = array( ':', '-' );
		foreach ( $separators as $sep ) {
			$pos = strpos( $caption, $sep );
			if ( $pos !== false && $pos < 20 ) {
				$caption = trim( substr( strstr( $caption, $sep ), 1 ) );
			}
		}
		return wp_trim_words( $caption, 12, '...' );
	}
	
	/**
	 * Ajax callback to search for photos by keywords.
	 *
	 * @since 2.0.0
	 */
	public function get_search_images( $keyword, $count = null, $page = 1) {
		$count = isset($count) ? $count : $this->default_count;
		$params = ($count == $this->default_count) ? array() : array( 'keyword' => $keyword, 'pg' => $page );
		$result = $this->get('get_search_results.php', $params );
		$response = array();
		$new_images = array();
		if ($result && isset( $result->photos )) {
			if ( ! isset( $result->total_results ) ) {
				$result->total_results = isset( $_REQUEST['altpage'] )? sanitize_text_field( $_REQUEST['altpage'] ) : 40;
			}
			if ( $page * $this->default_count >= $result->total_results ) {
				$response['pagin'] = false;
			}
			foreach( $result->photos as $image ) {
				if ( isset( $image->photographer ) ) {
					$image->caption .= ' ' . $image->photographer;
				}
				$new_images[] = array( 	'id' => $image->photo_id,
										'full' => $image->watermark,
										'thumbnail' => $image->thumbnail,
										'title' => isset( $image->photo_set_name ) ? $this->filter_text( $image->photo_set_name ) : '',
										'caption' => isset($image->caption) ? $this->filter_text($image->caption) : '',
										'short_caption' => isset($image->caption) ? $this->get_caption_excerpt( $this->filter_text($image->caption) ): '',
									);
			}
		}
		$response['images'] = $new_images;
		$response['altpage'] = isset( $images->total_results )? $images->total_results : 0 ;
		$response['title'] = sprintf( __( 'Search results for %s', 'sportspost'), $keyword );
		return $response;
	}
	
	/**
	 * Ajax callback to browse photos and photo sets.
	 *
	 * @since 2.0.0
	 */
	public function get_league_images( $league_link, $count = null, $page = 1, $altpage = '', $paramlabel = '', $photoset = '' ) {
		if ( strstr( $league_link, '/' ) && $league_link != '/' ) {
			$parts = array_filter( explode( '/', $league_link ) );
			if ( count( $parts ) > 1 ) {
				$set_id = end( $parts );
				return $this->get_photo_set_photos( $set_id, $count, $page, $altpage, $paramlabel, $photoset );
			}
			else {
				$league_link = reset( $parts );
			}
		}
		return $this->get_photo_sets( $league_link, $count, $page, $altpage, $paramlabel, $photoset );
	}
	
	/**
	 * Ajax callback to get photos of a photo set.
	 *
	 * @since 2.0.0
	 */
	public function get_photo_set_photos( $photo_set_id, $count = null, $page = 1, $altpage = '', $paramlabel = '', $photoset = '' ) {
		$count = isset($count) ? $count : $this->default_count;
		$params = ($photo_set_id == $this->default_count) ? array() : array( 'photo_set_id' => $photo_set_id );
		$result = $this->get( 'get_photo_set_photos.php', $params );
		$response = array();
		$new_images = array();
		if ( $result && isset( $result->photos ) ) {
			foreach( $result->photos as $image ) {
				if ( isset( $image->photographer ) ) {
					$image->caption .= ' ' . $image->photographer;
				}
				$new_images[] = array( 	'id' => $image->photo_id,
										'full' => $image->watermark,
										'thumbnail' => $image->thumbnail,
										'title' => isset( $image->photo_set_name ) ? $this->filter_text( $image->photo_set_name ) : '',
										'caption' => isset( $image->caption) ? $this->filter_text( $image->caption ) : '',
										'short_caption' => isset( $image->caption ) ? $this->get_caption_excerpt( $this->filter_text( $image->caption ) ): '',
									);
			}
		}
		$response['images'] = $new_images;
		$response['title'] = stripslashes( $paramlabel . ' &raquo; ' . $photoset );
		return $response;
	}

	/**
	 * Ajax callback to get list of photo sets for a league.
	 *
	 * @since 2.0.0
	 */
	public function get_photo_sets( $league_link, $count = null, $page = 1, $altpage = '', $paramlabel = '', $photoset = '' ) {
		$count = isset( $count ) ? $count : $this->default_count;
		$params = ( $count == $this->default_count )? array() : array( 'league_link' => $league_link, 'pg' => $page );
		$result = $this->get( 'get_photo_sets.php', $params );
		$response = array();
		$new_images = array();
		if ( $result && isset( $result->photo_sets ) ) {
			if ( $page * $this->default_count >= $result->total_results ) {
				$response['pagin'] = false;
			}
			foreach( $result->photo_sets as $image_set) {
				$new_images[] = array( 	'id' => $image_set->photo_id,
										'full' => $image_set->watermark,
										'thumbnail' => $image_set->thumbnail,
										'title' => (isset($image_set->photo_set_name) ? $this->filter_text($image_set->photo_set_name) : ''),
										'caption' => (isset($image_set->photo_set_name) ? $this->filter_text($image_set->photo_set_name) : ''),
										'short_caption' => (isset($image_set->photo_set_name) ? $this->get_caption_excerpt( $this->filter_text($image_set->photo_set_name) ): ''),
										'photo_set_id' => $image_set->photo_set_id,
										'photo_set_name' => $image_set->photo_set_name,
										'num_photos' => $image_set->num_photos,
										'league_link' => $league_link
									);
			}
		}
		$response['images'] = $new_images;
		$response['title'] = sprintf( __( 'Photo sets for %s', 'sportspost'), $paramlabel );
		return $response;
	}
	
	/**
	 * Ajax callback to check account connection.
	 *
	 * @since 2.0.0
	 */
	public function connect_check() {
		$settings = get_user_option( 'sportspost_settings' );
		$response['error'] = true;
		$response['message'] = '';  
		if ( $settings ) {
			if ( isset ( $settings['iconsportswire_username']) && isset( $settings['iconsportswire_password'] ) ) {
				$params = array( 
					'user' => $settings['iconsportswire_username'],
					'pwd' => $settings['iconsportswire_password'],
				);
				$user_info = $this->get( 'get_user_info.php', $params );
				if ( isset( $user_info->id ) ) {
					$response['error'] = false;
					$response['message'] = 'success';  
					$response['status'] = $this->get_status_string( $user_info );
					if ( isset( $settings['iconsportswire_username'] ) ) unset( $settings['iconsportswire_username'] ); // Excluded for security reasons
					if ( isset( $settings['iconsportswire_password'] ) ) unset( $settings['iconsportswire_password'] ); // Excluded for security reasons
					$response['settings'] = $settings;
				}
			}
		}
		return $response;
	}
	
	/**
	 * Ajax callback to perform account connection.
	 *
	 * @since 2.0.0
	 */
	public function connect() {
 		$username = $_POST['username'];
		$password = $_POST['password'];
		$params = array(
			'user' => $username,
			'pwd' => $password,
		);
		$user_info = $this->get( 'get_user_info.php', $params );
		$response = array();
		if ( isset( $user_info->id ) ) {
			$settings = get_user_option( 'sportspost_settings' );
			$settings['iconsportswire_username'] = $username;
			$settings['iconsportswire_password'] = $password;
			$settings['iconsportswire_id'] = $user_info->id;
			$settings['iconsportswire_account_type'] = $user_info->account_type;
			foreach( $user_info->clients as $client ) {
				$settings['iconsportswire_default_client'] = $client->client_id;
				break;
			}
			update_user_option( get_current_user_id(), 'sportspost_settings', $settings);
			if ( isset( $settings['iconsportswire_username'] ) ) unset( $settings['iconsportswire_username'] ); // Excluded for security reasons
			if ( isset( $settings['iconsportswire_password'] ) ) unset( $settings['iconsportswire_password'] ); // Excluded for security reasons
			$response['error'] = false;
			$response['message'] = 'success';
			$response['status'] = $this->get_status_string( $user_info );
			$response['settings'] = $settings;
		}
		else {
			$response['error'] = false;
			$response['message'] = $user_info->error;
		}
		return $response;
	}

	/**
	 * Helper function to get account clients.
	 *
	 * @since 2.0.0
	 */
	public function get_clients() {
		$clients = array();
		$settings = get_user_option( 'sportspost_settings' );
		if ( $settings ) {
			if ( isset ( $settings['iconsportswire_username']) && isset( $settings['iconsportswire_password'] ) ) {
				$params = array( 
					'user' => $settings['iconsportswire_username'],
					'pwd' => $settings['iconsportswire_password'],
				);
				$user_info = $this->get( 'get_user_info.php', $params );
				if ( isset( $user_info->clients ) ) {
					$clients = $user_info->clients;
				}
			}
		}
		return $clients;
	}


	/**
	 * Ajax callback to save user preferences.
	 *
	 * @since 2.0.0
	 */
	public function user_preferences_save() {
		$settings = get_user_option( 'sportspost_settings' );
		if ( isset( $_REQUEST['default_client'] ) ) {
			$settings['iconsportswire_default_client'] = $_REQUEST['default_client'];
		}
		if ( isset( $_REQUEST['default_league'] ) ) {
			$settings['iconsportswire_default_league'] = $_REQUEST['default_league'];
		}
		if ( isset( $_REQUEST['default_panel'] ) ) {
			$settings['iconsportswire_default_panel'] = $_REQUEST['default_panel'];
		}
		if ( isset( $_REQUEST['quick_download'] ) ) {
			$settings['iconsportswire_quick_download'] = $_REQUEST['quick_download'];
		}
		$result = update_user_option( get_current_user_id(), 'sportspost_settings', $settings);
		$response = $this->connect_check(); // needed for the possible status updates
		$response['error'] = ! $result;
		$response['message'] = $result? 'success' : __( 'Error saving user settings', 'sportspost' );
		return $response;
	}

	/**
	 * Ajax callback to get user preferences.
	 *
	 * @since 2.0.0
	 */
	public function user_preferences_get() {
		$settings = get_user_option( 'sportspost_settings' );
		if ( isset( $settings['iconsportswire_username'] ) ) unset( $settings['iconsportswire_username'] ); // Excluded for security reasons
		if ( isset( $settings['iconsportswire_password'] ) ) unset( $settings['iconsportswire_password'] ); // Excluded for security reasons
		$response['error'] = false;
		$response['message'] = 'success';
		$response['settings'] = get_user_option( 'sportspost_settings' );
		$response['leagues'] = $this->get_param_choices();
		$response['clients'] = $this->get_clients();
		return $response;
	}

	/**
	 * Ajax callback to disconnect user.
	 *
	 * @since 2.0.0
	 */
	public function disconnect() {
		$settings = get_user_option( 'sportspost_settings' );
		unset( $settings['iconsportswire_username'] );
		unset( $settings['iconsportswire_password'] );
		update_user_option( get_current_user_id(), 'sportspost_settings', $settings);
		$response['error'] = false;
		$response['message'] = 'success';
		return $response;
	}

	/**
	 * Helper function to get user status string.
	 *
	 * @since 2.0.0
	 */
	public function get_status_string( $user_info ) {
		$status = '';
		if ( isset( $user_info->id ) ) {
			$status = $user_info->first_name . ' ' . $user_info->last_name;
			// Subscription accounts (possible multiple clients)
			if ( $user_info->account_type == 1 ) {
				$settings = get_user_option( 'sportspost_settings' );
				$default_client_id = isset( $settings['iconsportswire_default_client'] )? $settings['iconsportswire_default_client'] : '';
				if ( ! empty( $default_client_id ) ) {
					foreach( $user_info->clients as $client) {
						if ( $client->client_id == $default_client_id ) {
							if ( count( (array) $user_info->clients ) > 1 ) {
								$status .=  ' (' . $client->client_name . ')';
							}
							if ( isset( $client->remaining_monthly_downloads ) ) {
								$status .= '<br/>' . $client->remaining_monthly_downloads . ' downloads remaining this month';
							}
							else if ( isset( $client->remaining_downloads ) ) {
								$status .= '<br/>' . $client->remaining_downloads . ' downloads remaining overall';
							}
						}
					}
				}
			}
			// Ecommerce accounts (single clients only)
			if ( $user_info->account_type == 2 ) {
				foreach( $user_info->clients as $client) {
					$status .= '<br/>' . $client->credits_remaining . ' Credits (' . ( floor( $client->credits_remaining / 24 ) ) . ' Photos)';
				}
			}
		}
		return $status;
	}

	/**
	 * Ajax callback to get list of leagues.
	 *
	 * @since 2.0.0
	 */
	public function get_param_choices($type = '') {
		$response = array();
		$response[''] = __( 'Select a league', 'sportspost' );
		$leagues = $this->get('get_league_types.php' );
		if ( $leagues ) {
			foreach( $leagues as $league ) {
				$response[ $league->league_link ] = $league->name;
			}
		}
		return $response;
	}
	
	/**
	 * Helper function to download image.
	 *
	 * @since 2.0.0
	 */
	public function download_image( $id ) {
		$settings = get_user_option( 'sportspost_settings' );
		$params = array(
			'user' => $settings['iconsportswire_username'],
			'pwd' => $settings['iconsportswire_password'],
		);
		$client_id = isset( $settings['iconsportswire_default_client'] )? $settings['iconsportswire_default_client'] : '';
		// Check if current default client is one of the client associated with the current user
		$check_client = false;
		$user_info = $this->get( 'get_user_info.php', $params );
		if ( ! isset( $user_info->id ) ) {
			$response = array();
			$response['error'] = true;
			$response['message'] = isset( $user_info->error )? $result->error : __( 'Error getting current user info.', 'sportspost' );
			echo json_encode( $response );
			die;
		}
		foreach( $user_info->clients as $client) {
			if ( $client_id == $client->client_id ) {
				$check_client = true;
				break;
			};
		}
		if ( ! $check_client ) {
			$response = array();
			$response['error'] = true;
			$response['message'] = __( 'The client set in your preferences is not related to the current account. Please check your preferences.', 'sportspost' );
			echo json_encode( $response );
			die;
		}
		// Download image
		$params = array(
			'photo_id' => $id,
			'user' => $settings['iconsportswire_username'],
			'pwd' => $settings['iconsportswire_password'],
			'client_id' => $client_id, 
			'res' => 'web'
		);
		$result = $this->get( 'download_photo.php', $params );
		if ( $result && isset ( $result->download_url ) ) {
			$download_url = stripslashes( $result->download_url );
			return $download_url;
		} else {
			$response = array();
			$response['error'] = true;
			$response['message'] = isset( $result->error )? $result->error : __( 'Error downloading image.', 'sportspost' );
			echo json_encode( $response );
			die;
		}
	}
	
	/**
	 * Ajax callback to insert image in WordPress Media Library.
	 *
	 * @since 2.0.0
	 */
	public function import( $response ) {
		if ( isset( $_REQUEST['id'] ) ) {
			$id = sanitize_key( $_REQUEST['id'] );
			$parent_post_id = sanitize_key( $_REQUEST['postid'] );
			$title = sanitize_text_field( $_REQUEST['title'] );
			$text = sanitize_text_field( $_REQUEST['data-caption'] );
			$link_to = sanitize_text_field( $_REQUEST['setting-link-to'] );
			$link_to_custom = sanitize_text_field( $_REQUEST['setting-link-to-custom'] );
			$file = $this->download_image( $id );
			//$file = $_REQUEST['data-full']; // For testing purposes (import watermarked version)
			$filename = basename($file);
			$upload_file = wp_upload_bits( $filename, null, file_get_contents(  $file ) );
			if ( ! $upload_file['error'] ) {
				$wp_filetype = wp_check_filetype($filename, null );
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_parent' => $parent_post_id,
					'post_title' => $title,
					'post_content' => $text,
					'post_excerpt' => $text,
					'post_status' => 'inherit'
				);
				$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );
				if (!is_wp_error($attachment_id)) {
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
					wp_update_attachment_metadata( $attachment_id,  $attachment_data );
					update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
				}
				$src = wp_get_attachment_image_src( $attachment_id, 'full' );
				switch ($link_to ) {
					case 'post':
						$response['linkto'] = get_attachment_link( $attachment_id );
						break;
					case 'file':
						$response['linkto'] = wp_get_attachment_url( $attachment_id );
						break;
					case 'none':
						$response['linkto'] = '';
						break;
					case 'custom':
						$response['linkto'] = $link_to_custom;
						break;
				}
				$response['imgsrc'] = $src[0];
			}
		}
		$status_response = $this->connect_check(); // needed for the possible status updates
		$response = array_merge( $status_response, $response );
		return $response;
	}

	/**
	 * Sets default values for photos methods and filters.
	 *
	 * @since 2.0.0
	 */
	public function sportspost_default_filters( $filters ) {
		if ( is_admin() ) require_once( ABSPATH . 'wp-includes/pluggable.php' );
		$user_settings = get_user_option( 'sportspost_settings' );
		if ( isset( $user_settings['iconsportswire_default_panel'] ) ) {
			$filters['method'] = $user_settings['iconsportswire_default_panel'] == 'browse' ? 'getLeagueImages' : 'getSearchImages';
		}
		if ( isset( $user_settings['iconsportswire_default_league'] ) ) {
			$filters['paramselect'] = $user_settings['iconsportswire_default_league'];
		}
		return $filters;
	}
	
}
