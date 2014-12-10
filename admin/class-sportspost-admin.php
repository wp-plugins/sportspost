<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin
 */
class SportsPost_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The options of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    The options of this plugin.
	 */
	private $options;

	/**
	 * The admin pointers of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    The admin pointers of this plugin.
	 */
	private $pointers;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		// Initialize plugin meta data
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		// Initialize plugin options
		$this->options = array(
			'default_sports_league' => 'l.mlb.com',
			'affiliate_reference_id' => '',
			'target_blank' => 0,
			'force_wizard' => 0,
		);
		$saved_options = get_option( 'sportspost_settings' );
		if ( is_array( $saved_options ) ) {
			$this->options = array_merge(
				$this->options,
				$saved_options
			);
		}
	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sportspost-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sportspost-player-link.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'sportsPostPlayerLinkL10n', array(
				'title' => __( 'Insert/edit Player Link', 'sportspost' ),
				'update' => __('Update'),
				'save' => __( 'Add Player Link', 'sportspost' ),
				'noTitle' => __( '(no title)' ),
				'noMatchesFound' => __( 'No matches found.' ),
				'next' => __( 'Next', 'sportspost' ),
				'close' => __( 'Close', 'sportspost' ),
				'affiliateReferenceID' => $this->options['affiliate_reference_id'],
				'api_endpoint' => SPORTSPOST_API_ENDPOINT,
				'iconURL' => plugins_url( 'admin/img/icon-player.png', dirname( __FILE__ ) ),
		) );
	}

	/**
	 * Add the settings page in the Settings menu.
	 *
	 * @since    1.0.0
	 */
	public function add_settings_page() {
		add_options_page( 'SportsPost Settings', 'SportsPost', 'manage_options', 'sportspost_settings_page', array( $this, 'display_settings_page' ) );
	}

	/**
	 * Display the settings page in the menu API.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		include( 'partials/sportspost-admin-settings.php' );
	}

	/**
	 * Initialize the settings API.
	 *
	 * @since    1.0.0
	 */
	public function settings_init() {
		add_settings_section(
			'sportspost_settings_section', // Section ID
			'', // Section Title
			'', // Section Callback
			'sportspost_settings_page' // Page
		);
		
		add_settings_field(
			'default_sports_league', // Field ID
			__( 'Default Sports League', 'sportspost' ), // Field Title
			array( $this, 'setting_default_sports_league_callback_function' ), // Field Callback
			'sportspost_settings_page', // Page
			'sportspost_settings_section' // Section
		);
		
		add_settings_field(
			'affiliate_reference_id', // Field ID
			__( 'Affiliate Reference ID', 'sportspost' ), // Field Title
			array( $this, 'setting_affiliate_reference_id_callback_function' ), // Field Callback
			'sportspost_settings_page', // Page
			'sportspost_settings_section' // Section
		);
		
		add_settings_field(
			'target_blank', // Field ID
			__( 'Default link target', 'sportspost' ), // Field Title
			array( $this, 'setting_target_blank_callback_function' ), // Field Callback
			'sportspost_settings_page', // Page
			'sportspost_settings_section' // Section
		);
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			add_settings_field(
				'force_wizard', // Field ID
				__( 'Force wizard', 'sportspost' ), // Field Title
				array( $this, 'setting_force_wizard_callback_function' ), // Field Callback
				'sportspost_settings_page', // Page
				'sportspost_settings_section' // Section
			);
		}

		register_setting(
			'sportspost_settings_group', // Option group
			'sportspost_settings' // Option name
		);
	}
 
	/**
	 * Helper function to display Sports League dropdown.
	 *
	 * @since    1.0.0
	 */
	public function display_league_dropdown( $name, $id, $selected ) {
		echo '<select name="' . $name . '" id="' . $id . '">';
		echo '	<option value="mlb" ' . selected( $selected, 'mlb' ) . '>MLB</option>';
		echo '	<option value="nhl" ' . selected( $selected, 'nhl' ) . '>NHL</option>';
		echo '	<option value="nfl" ' . selected( $selected, 'nfl' ) . '>NFL</option>';
		echo '	<option value="nba" ' . selected( $selected, 'nba' ) . '>NBA</option>';
		echo '</select>';
	}

	/**
	 * Callback function for Default Sports League setting.
	 *
	 * @since    1.0.0
	 */
	public function setting_default_sports_league_callback_function() {
		$this->display_league_dropdown( 'sportspost_settings[default_sports_league]', 'default_sports_league', $this->options['default_sports_league'] );
	}

	/**
	 * Callback function for Affiliate Reference ID setting.
	 *
	 * @since    1.0.0
	 */
	public function setting_affiliate_reference_id_callback_function() {
		echo '<input name="sportspost_settings[affiliate_reference_id]" id="affiliate_reference_id" type="text" value="' . esc_attr( $this->options['affiliate_reference_id'] ) . '" class="code" />';
	}

	/**
	 * Callback function for Default link target.
	 *
	 * @since    1.0.0
	 */
	public function setting_target_blank_callback_function() {
		echo '<input name="sportspost_settings[target_blank]" id="target_blank" type="checkbox" value="1" ' . checked( $this->options['target_blank'], 1, false ) . ' class="code" /> ' . __( 'Set link behavior to "open in new window/tab"', 'sportspost' );
	}

	/**
	 * Callback function for Force wizard option.
	 *
	 * @since    1.0.0
	 */
	public function setting_force_wizard_callback_function() {
		echo '<input name="sportspost_settings[force_wizard]" id="force_wizard" type="checkbox" value="1" ' . checked( $this->options['force_wizard'], 1, false ) . ' class="code" /> ' . __( 'Force the "Getting started" wizard to be always executed', 'sportspost' );
	}

	/**
	 * Add TinyMCE toolbar button.
	 *
	 * @since    1.0.0
	 * @var      array    $buttons       Array of TinyMCE buttons.
	 */
	public function register_tinymce_button( $buttons ) {
		array_splice( $buttons, array_search( 'link', $buttons ), 0, array( 'sportspost-playerlink' ) );
		return $buttons;
	}

	/**
	 * Add TinyMCE external plugin.
	 *
	 * @since    1.0.0
	 * @var      array    $plugins       Array of TinyMCE plugins.
	 */
	public function add_tinymce_plugin( $plugins ) {
		$plugins['sportspost'] = plugin_dir_url( __FILE__ ) . 'js/sportspost-tinymce-plugin.js';
		return $plugins;
	}

	/**
	 * Setup editor instance for event handling.
	 *
	 * @since 1.0.0
	 */
	public function wp_tiny_mce_init() {
		if ( ! empty( $this->pointers ) ) {
			echo "\t\t" . '<script type="text/javascript" src="' . plugin_dir_url( __FILE__ ) . 'js/sportspost-tinymce-setup.js' . '"></script>' . "\n";
		}
	}

	/**
	 * Add Quicktags button.
	 *
	 * @since    1.0.0
	 */
	public function add_quicktags_button() {
?>
	<script type="text/javascript">
	QTags.addButton( 'playerlink', 'player', function(){ window.sportsPostPlayerLink.open() }, '', '', 'Insert Player Link', 29 );
    </script>
<?php
	}

	/**
	 * Dialog for palyer links.
	 *
	 * @since    1.0.0
	 */
	public function player_link_dialog() {
		$search_panel_visible = '1' == get_user_setting( 'wplink', '0' ) ? ' search-panel-visible' : '';
		include( 'partials/sportspost-admin-dialog.php' );
	}

	/**
	 * Ajax handler for player linking.
	 *
	 * @since    1.0.0
	 */
	public function player_link_ajax() {
		check_ajax_referer( 'sportpost-player-linking', '_ajax_sportspost_player_link_nonce' );
		
		// Version A: Queries with multiple keys handled directly by sportscodes.org server
		if ( ! empty( $_POST['league'] ) && ! empty( $_POST['search'] ) ) {
			$url = SPORTSPOST_API_ENDPOINT . '?';
			$url .= 'type=players&';
			$url .= 'league=' . 'l.' . wp_unslash( $_POST['league'] ) . '.com' . '&';
			$url .= 'name-fragment=' . urlencode( wp_unslash( $_POST['search'] ) ) . '&';
			$url .= 'input-publisher=sportsforecaster.com&';
			$url .= 'output-publishers=sportsforecaster.com&';
			$url .= 'format=sportsjson';
			echo file_get_contents( $url );
			wp_die();
		}
		else {
			wp_die( 0 );
		}
	}

	/**
	 * Load admin pointer(s)
	 *
	 * @since    1.0.0
	 */
	public function load_admin_pointers() {
		$this->pointers = $this->filter_dismissed_admin_pointers( $this->register_admin_pointer() );
		if ( ! empty( $this->pointers ) ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( $this->plugin_name . '-admin-pointers', plugin_dir_url( __FILE__ ) . 'js/sportspost-admin-pointers.js', array( 'jquery', 'wp-pointer' ), $this->version, false );
			wp_localize_script( $this->plugin_name . '-admin-pointers', 'sportspost_pointers', $this->pointers );
		}
	}

	/**
	 * Register admin pointer(s)
	 *
	 * @since    1.0.0
	 * @var      mixed[] $pointers
	 */
	public function register_admin_pointer() {
		$button_target = '.mce-btn[aria-label="' . __( 'Insert/edit Player Link', 'sportspost' ) . '"]'; // dashicon version: '.mce-i-playerlink:first',
		$pointers = array(
			'sportspost-settings' => array(
				'target' => $button_target,
				'options' => array(
					'content' => sprintf( '<h3>%s</h3> <p>%s</p>',
						__( 'Welcome to SportsPost', 'sportspost' ),
						sprintf( 
							__( 'Thanks for installing the plugin. Please insert your affiliate ID in the <a href="%s">plugin settings</a>.', 'sportspost' ),
							admin_url( 'options-general.php?page=sportspost_settings_page' )
						)
					),
					'position' => array( 'edge' => 'left', 'align' => 'middle' )
				)
			),
			'sportspost-playerlink-button' => array(
				'target' => $button_target . ' i',
				'options' => array(
					'content' => sprintf( '<h3>%s</h3> <p>%s</p>',
						__( 'Insert/edit Player Link', 'sportspost' ),
						__( 'Use this button to insert and edit links to Sports Forecaster player pages.', 'sportspost' )
					),
					'position' => array( 'edge' => 'left', 'align' => 'middle' )
				)
			),
			'sportspost-player-league' => array(
				'target' => '#player-league',
				'options' => array(
					'content' => sprintf( '<h3>%s</h3> <p>%s</p>',
						__( 'Select a Sport League', 'sportspost' ),
						__( 'This dropdown allows you to select the sport league to search for. You may set your default choice in SportsPost settings page.', 'sportspost' )
					),
					'position' => array( 'edge' => 'left', 'align' => 'middle' )
				)
			),
			'sportspost-player-search' => array(
				'target' => '#player-search-field',
				'options' => array(
					'content' => sprintf( '<h3>%s</h3> <p>%s</p>',
						__( 'Search for a player', 'sportspost' ),
						__( 'Type at least 3 characters to perform a search based on player names.', 'sportspost' )
					),
					'position' => array( 'edge' => 'left', 'align' => 'middle' )
				)
			),
			'sportspost-player-search-results' => array(
				'target' => '#player-search-results',
				'options' => array(
					'content' => sprintf( '<h3>%s</h3> <p>%s</p>',
						__( 'Select a player', 'sportspost' ),
						__( 'Click on a search result to insert a link to that player.', 'sportspost' )
					),
					'position' => array( 'edge' => 'left', 'align' => 'middle' )
				)
			),
			'sportspost-player-link-preview' => array(
				'target' => '#player-link-preview',
				'options' => array(
					'content' => sprintf( '<h3>%s</h3> <p>%s</p>',
						__( 'Preview the link', 'sportspost' ),
						__( 'You can preview the destination link being inserted (it opens in a new window/tab).', 'sportspost' )
					),
					'position' => array( 'edge' => 'left', 'align' => 'middle' )
				)
			),
			'sportspost-player-link-submit' => array(
				'target' => '#sportspost-player-link-submit',
				'options' => array(
					'content' => sprintf( '<h3>%s</h3> <p>%s</p>',
						__( 'The end', 'sportspost' ),
						__( 'At the end just click the button to close the window and insert the link.', 'sportspost' )
					),
					'position' => array( 'edge' => 'left', 'align' => 'middle' )
				)
			),
		);
		return $pointers;
	}

	/**
	 * Remove dismissed admin pointer(s)
	 *
	 * @since    1.0.0
	 * @var		 mixed[]  $pointers
	 */
	public function filter_dismissed_admin_pointers( $pointers ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && $this->options['force_wizard'] ) return $pointers;
		$valid_pointers = array();
		if ( is_array( $pointers ) ) {
			$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
			foreach ( $pointers as $pointer_id => $pointer ) {
				if ( ! in_array( $pointer_id, $dismissed ) ) {
					$valid_pointers[ $pointer_id ] = $pointer;
				}
			}
		}
		return $valid_pointers;
	}

}
