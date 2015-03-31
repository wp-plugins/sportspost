<?php

/**
 * SportsPost Module for Photo integration.
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin
 * @since      2.0.0
 */
class SportsPost_Photos_Module {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The settings of this plugin.
	 *
	 * @since 2.0.0
	 * @access   private
	 * @var      array    $settings    The settings of this plugin.
	 */
	private $settings;

	/**
	 * The callback url for sources.
	 *
	 * @since 2.0.0
	 * @access   private
	 * @var      array    $callback    The callback url.
	 */
	private $callback;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $settings ) {
		// Initialize plugin meta data
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = $settings;
		$this->callback = get_admin_url() .'options-general.php?page=sportspost_settings_page';
		// Include sources
		$this->include_sources();
		$this->get_sources();
	}
	
	/**
	 * Helper function to include source class files.
	 *
	 * @since 2.0.0
	 */
	public function include_sources() {
		include( 'class-sportspost-photos-source.php' );
		$source_dir = glob( dirname( __FILE__ ) . '/sources/*' );
		if ( $source_dir ) {
			foreach( $source_dir as $dir ) include_once( $dir );
		}
	}
	
	/**
	 * Helper function to instantiate a source class.
	 *
	 * @since 2.0.0
	 */
	public function get_source_instance( $source ) {
		$var = 'SportsPost_Photos_Source_' . $source;
		$obj = new $var();
		return $obj;
	}
	
	/**
	 * Helper function to convert from camel case function names (JS) to underscores (PHP).
	 *
	 * @since 2.0.0
	 * @var      string    $input      The input string to be converted.
	 */
	public function decamelize( $input ) {
		preg_match_all( '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches );
		$ret = $matches[0];
		foreach ( $ret as &$match ) {
			$match = $match == strtoupper( $match ) ? strtolower( $match ) : lcfirst( $match );
		}
  		return implode( '_', $ret );
	}
	
	/**
	 * Helper function to call a method on a source class.
	 *
	 * @since 2.0.0
	 */
	public function call_source_method( $source, $method, $args ) {
		$obj = $this->get_source_instance( $source );
		return call_user_func_array( array($obj, $this->decamelize( $method ) ), $args );
	}

	/**
	 * Helper function to get sources.
	 *
	 * @since 2.0.0
	 */
	public function get_sources() {
		$callback = $this->callback;
		$load_sources = apply_filters( 'sportspost_sources', array() );
		$sources = array();	
		if ($load_sources) {
			foreach( $load_sources as $source => $source_details ) {
				$source_data['url'] = '#';
				$obj = $this->get_source_instance( $source );
				$source_data['name'] = $obj->get_name();
				$source_data['settings'] = $obj->get_settings();
				$sources[$source] = $source_data;				
			}
		}
		ksort( $sources );
		return $sources;
	}

	/**
	 * Ajax action called to disconnect a source.
	 *
	 * @since 2.0.0
	 */
	public function disconnect_source() {
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'sportspost_nonce' ))
			return 0;
		if ( !isset($_POST['source']))
			return 0; 
		$source = $_POST['source'];
		$response = $this->call_source_method( $source, 'disconnect', array() );
		echo json_encode($response);
		die;
	}

	/**
	 * Ajax action called to check connection.
	 *
	 * @since 2.0.0
	 */
	public function connect_check() {
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'sportspost_nonce' )) {
			return 0;
		}
		if ( !isset($_POST['source'])){
			return 0;
		}
		$source = $_POST['source'];
		$response = $this->call_source_method( $source, 'connect_check', array() );
		echo json_encode($response);
		die;
	}
	
	/**
	 * Ajax action called to perform connection.
	 *
	 * @since 2.0.0
	 */
	public function connect_source() {
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'sportspost_nonce' )) {
			return 0;
		}
		if ( !isset($_POST['source'])){
			return 0;
		}
		$source = $_POST['source'];
		$response = $this->call_source_method( $source, 'connect', array() );
		echo json_encode( $response );
		die;
	}

	
	/**
	 * Ajax action called to get param choices.
	 *
	 * @since 2.0.0
	 */
	public function param_choices() {
		if ( !isset($_POST['method'])  || !isset($_POST['source']))
			return 0;   
		$response['error'] = false;
		$response['message'] = '';
		$response['choices'] = array();
		$choices = array();
		
		$image_source =  $_POST['source'];
		$obj = $this->get_source_instance( $image_source );
		$method = $_POST['method'];
		$return = $obj->get_param_choices($method);
		
		if ($return) {
			$choices = $return;
		} else {
			$response['error'] = true;
			$response['message'] = 'Failed to get choices for '. $method;
		}
		$response['choices'] = $choices;
		echo json_encode($response);
		die;
	}

	/**
	 * Ajax action called to get images.
	 *
	 * @since 2.0.0
	 */
	public function load_images(){
		if ( !isset($_POST['param'])  || !isset($_POST['method'])  || !isset($_POST['source']))
			return 0;   
		$response['error'] = false;
		$response['message'] = '';
		$response['images'] = array();
		$images = array();
		$image_source =  $_POST['source'];
		$obj = $this->get_source_instance( $image_source );
		$method = $_POST['method'];
		$count = 50;
		$params = array();
		if ( isset($_POST['param'] ) && $_POST['param'] != '') $params[] = $_POST['param'];
		if ( $count != '' ) $params['count'] = $count;
		if ( isset( $_POST['page'] ) ) $params['page'] = $_POST['page'];
		if ( isset( $_POST['altpage'] ) ) $params['altpage'] = $_POST['altpage'];
		if ( isset( $_POST['paramlabel'] ) ) $params['paramlabel'] = $_POST['paramlabel'];
		if ( isset( $_POST['photoset'] ) ) $params['photoset'] = $_POST['photoset'];
		$return =  $this->call_source_method( $image_source, $method, $params );
		if ($return['images']) {
			foreach( $return['images'] as $image) $images[] = $image;
			if(isset($return['pagin'])) $response['pagin'] = 'end';
			if(isset($return['altpage'])) $response['altpage'] = $return['altpage'];
		} else {
			$response['error'] = true;
			$response['message'] = 'No images available from '. $obj->get_name() . ((isset($_POST['param']) && $_POST['param'] != '') ? ' for '. $_POST['param'] : '') ;
		}
		if(isset($return['title'])) $response['title'] = $return['title'];
		$response['images'] = $images;
		
		echo json_encode($response);
		die;
	}

	/**
	 * Custom strings used for the media dialog.
	 *
	 * @since 2.0.0
	 */
	public function custom_media_string( $strings, $post ){
		$hier = $post && is_post_type_hierarchical( $post->post_type );
		$strings['sportspost'] = $this->get_sources(true);
		$strings['sportspostInsertButton'] = $hier ? __( 'Insert into page', 'sportspost' ) : __( 'Insert into post', 'sportspost' );
		$strings['sportspostImportButton'] = __( 'Import', 'sportspost' );
		$strings['sportspost_menu'] = apply_filters( 'sportspost_default_menu','default');
		$strings['sportspost_menu_prefix'] = apply_filters( 'sportspost_menu_prefix', __('Insert from ', 'sportspost' ) );
		$strings['sportspost_defaults'] = apply_filters( 'sportspost_default_settings', array() );
		$strings['sportspost_default_filters'] = apply_filters( 'sportspost_default_filters', array() );
		return $strings;
	}
	
	/**
	 * Ajax action called prior to insert the media into the editor.
	 *
	 * @since 2.0.0
	 */
	public function pre_insert() {
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'sportspost_nonce' )) return 0;
		$response['error'] = false;
		$response['message'] = 'success';
		$response['imgsrc'] = $_POST['imgsrc'];
		$response['fields'] = $_POST;
		echo json_encode( apply_filters('sportspost_pre_insert', $response ) );
		die;
	}
	
	/**
	 * Ajax action for saving user preferences.
	 *
	 * @since 2.0.0
	 */
	function user_preferences_save() {
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'sportspost_nonce' )) return 0;
		if ( !isset($_POST['source'])){
			return 0;
		}
		$source = $_POST['source'];
		$response = $this->call_source_method( $source, 'user_preferences_save', array() );
		echo json_encode( $response );
		die;
	}

	/**
	 * Ajax action for saving user preferences.
	 *
	 * @since 2.0.0
	 */
	public function user_preferences_get() {
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'sportspost_nonce' )) return 0;
		if ( !isset($_POST['source'])){
			return 0;
		}
		$source = $_POST['source'];
		$response = $this->call_source_method( $source, 'user_preferences_get', array() );
		echo json_encode( $response );
		die;
	}

	/**
	 * Print the templates used for the media dialog.
	 *
	 * @since 2.0.0
	 */
	public function print_media_templates() {
		include( 'partials/sportspost-photos-image-single.php' );
		include( 'partials/sportspost-photos-image-settings.php' );
		include( 'partials/sportspost-photos-user-settings.php' );
		include( 'partials/sportspost-photos-user-connect.php' );
	}
	
	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name . '-photos', plugin_dir_url( __FILE__ ) . 'css/sportspost-photos.css', array( 'wp-jquery-ui-dialog' ), $this->version, 'all' );
		wp_enqueue_media();
	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '-photos', plugin_dir_url( __FILE__ ) . '../admin/js/sportspost-photos.js', array( 'jquery', 'media-views', 'jquery-ui-dialog' ), $this->version, false );
		wp_localize_script( $this->plugin_name . '-photos', 'sportspost_nonce', array(  'nonce' => wp_create_nonce('sportspost_nonce') ));
	}

}