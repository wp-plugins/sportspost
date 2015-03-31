<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since   1.0.0
 * @package    SportsPost
 * @subpackage SportsPost/includes
 */
class SportsPost {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since 1.0.0
	 * @access   protected
	 * @var      SportsPost_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'sportspost';
		$this->version = '2.0.0';
		$this->load_dependencies();
		$this->loader = new SportsPost_Loader();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - SportsPost_Loader. Orchestrates the hooks of the plugin.
	 * - SportsPost_i18n. Defines internationalization functionality.
	 * - SportsPost_Admin. Defines all hooks for the dashboard.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since 1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sportspost-loader.php';
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sportspost-i18n.php';
		/**
		 * The class responsible for defining the widgets.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sportspost-content-widget.php';
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sportspost-public.php';
		/**
		 * The class responsible for Photo integration integration.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sportspost-photos-module.php';
		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sportspost-admin.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the SportsPost_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since 1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new SportsPost_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since 1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		global $pagenow;
		$plugin_admin = new SportsPost_Admin( $this->get_plugin_name(), $this->get_version() );
		$module_photo = new SportsPost_Photos_Module( $this->get_plugin_name(), $this->get_version(), $plugin_admin->get_settings() );
		if ( in_array( $pagenow, array( 'post.php', 'post-new.php', 'widgets.php', 'customize.php', 'admin-ajax.php' ) ) ) {
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'load_admin_pointers' );
			$this->loader->add_filter( 'mce_buttons', $plugin_admin, 'register_tinymce_button' );
			$this->loader->add_filter( 'mce_external_plugins', $plugin_admin, 'add_tinymce_plugin' );
			$this->loader->add_action( 'admin_print_footer_scripts', $plugin_admin, 'add_quicktags_button' );
			$this->loader->add_action( 'wp_tiny_mce_init', $plugin_admin, 'wp_tiny_mce_init' );
			$this->loader->add_action( 'after_wp_tiny_mce', $plugin_admin, 'player_link_dialog' );
			$this->loader->add_action( 'wp_ajax_sportspost-player-link-ajax', $plugin_admin, 'player_link_ajax' );
			$this->loader->add_action( 'siteorigin_panels_widgets', $plugin_admin, 'siteorigin_panels_widgets' );
		}
		if ( 'options-general.php' == $pagenow && isset( $_GET['page'] ) && 'sportspost_settings_page' == $_GET['page'] ) {
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_settings_scripts' );
		}
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_init' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );
		// Photos integration
		$this->loader->add_action( 'admin_enqueue_scripts', $module_photo, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $module_photo, 'enqueue_styles' );
		$this->loader->add_action( 'print_media_templates', $module_photo, 'print_media_templates', 99);
		$this->loader->add_filter( 'media_view_strings', $module_photo, 'custom_media_string', 10, 2);
		$this->loader->add_action( 'wp_ajax_sportspost_connect', $module_photo, 'connect_source' );
		$this->loader->add_action( 'wp_ajax_sportspost_disconnect', $module_photo, 'disconnect_source' );
		$this->loader->add_action( 'wp_ajax_sportspost_check', $module_photo, 'connect_check' );
		$this->loader->add_action( 'wp_ajax_sportspost_load_images', $module_photo, 'load_images' );
		$this->loader->add_action( 'wp_ajax_sportspost_param_choices', $module_photo, 'param_choices' );
		$this->loader->add_action( 'wp_ajax_sportspost_pre_insert', $module_photo, 'pre_insert' );
		$this->loader->add_action( 'wp_ajax_sportspost_user_preferences_save', $module_photo, 'user_preferences_save' );
		$this->loader->add_action( 'wp_ajax_sportspost_user_preferences_get', $module_photo, 'user_preferences_get' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since 1.2.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new SportsPost_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 20 );
		$this->loader->add_action( 'init', $plugin_public, 'init_widget', 1 );
		$this->loader->add_filter( 'template_include', $plugin_public, 'full_sport_template_include' );
		$this->loader->add_filter( 'the_posts', $plugin_public, 'full_sport_content_virtual_page' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  1.0.0
	 * @return    SportsPost_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
