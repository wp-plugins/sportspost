<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since   1.2.0
 *
 * @package    SportsPost
 * @subpackage SportsPost/public
 */
class SportsPost_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.2.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.2.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.2.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.2.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sportspost-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the widget.
	 *
	 * @since 1.2.0
	 */
	public function init_widget() {
		if ( !is_blog_installed() ) {
			return;
		}
		register_widget( 'SportsPost_Content_Widget' );
	}


	/**
	 * Check if the current URL is a full Sport Content.
	 *
	 * @since 1.2.0
	 */
	public function is_full_sport_content_page() {
		return isset( $_GET['sportspost_document'] ) && $_GET['sportspost_document'] == 'true';
	}
	
	/**
	 * Create a virtual page to display full Sport Content.
	 */
	public function full_sport_content_virtual_page( $posts ) {
		$option = get_option( 'sportspost_settings' );
		global $wp, $wp_query;
		if ( $this->is_full_sport_content_page() ) {
			//create a fake post intance
			$post = new stdClass;
			// fill properties of $post with everything a page in the database would have
			$post->ID = -1; // use an illegal value for page ID
			$post->post_author = 1; // post author id
			$post->post_date = current_time( 'mysql' ); // date of post
			$post->post_date_gmt = current_time( 'mysql', 1 );
			$post->post_content =  $this->render_content_url( $option['content_api_url'] . $_GET['url'] );
			$post->post_title = '';
			$post->post_excerpt = '';
			$post->post_status = 'publish';
			$post->comment_status = 'closed'; // mark as closed for comments, since page doesn't exist
			$post->ping_status = 'closed'; // mark as closed for pings, since page doesn't exist
			$post->post_password = ''; // no password
			$post->post_name = '';
			$post->to_ping = '';
			$post->pinged = '';
			$post->modified = $post->post_date;
			$post->modified_gmt = $post->post_date_gmt;
			$post->post_content_filtered = '';
			$post->post_parent = 0;
			$post->guid = get_home_url( '/' );
			$post->menu_order = 0;
			$post->post_type = 'page';
			$post->post_mime_type = '';
			$post->comment_count = 0;
			
			// set filter results
			$posts = array( $post );
			
			// reset wp_query properties to simulate a found page
			$wp_query->is_page = TRUE;
			$wp_query->is_singular = TRUE;
			$wp_query->is_home = FALSE;
			$wp_query->is_archive = FALSE;
			$wp_query->is_category = FALSE;
			unset($wp_query->query['error']);
			$wp_query->query_vars['error'] = '';
			$wp_query->is_404 = FALSE;
		}
		return $posts;
	}
		

	/**
	 * Apply template for full Sport Content.
	 *
	 * @since 1.2.0
	 * @param      string    $template    The current template.
	 */
	public function full_sport_template_include( $template ) {
		if ( $this->is_full_sport_content_page() ) {
			$template = locate_template( array( 'page.php' ) );
		}
		return $template;
	}

	/**
	 * Render a Sport Content URL.
	 */
	public function render_content_url( $url ) {
		if ( empty( $url ) ) {
			return '';
		}
		$option = get_option( 'sportspost_settings' );
		$username = isset( $option['content_api_username'] )? $option['content_api_username'] : '';
		$password = isset( $option['content_api_password'] )? $option['content_api_password'] : '';
		$context  = stream_context_create( array( 
			'http' => array(
				'header'  => "Authorization: Basic " . base64_encode( "$username:$password" ),
			)
		));
		$text = '';
		set_error_handler(
			create_function(
				'$severity, $message, $file, $line',
				'throw new ErrorException( $message, $severity, $severity, $file, $line );'
			)
		);
		try {
			$text = file_get_contents( $url, false, $context );
		}
		catch ( Exception $e ) {
		}
		restore_error_handler();
		// Remap URLs
		if ( ! empty( $text ) ) {
			// $text = preg_replace( '/<a(.*)href=([\'"])\/(.*)([\'"])/Ui', '<a$1href=$2/?sportspost_document=true&url=/$3$4', $text );
			$text = preg_replace_callback(
				'/<a(.*)href=([\'"])\/(.*)([\'"])/Ui', 
				function ( $matches ) {
					return '<a' . $matches[1] . 'href=' . $matches[2] . '/?sportspost_document=true&url=' . urlencode( '/' . html_entity_decode( $matches[3] ) ) . $matches[4];
				},
				$text
			);
		}
		return $text;
	}
	
}
