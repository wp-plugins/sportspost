<?php

/**
 * SportsPost - Enriched Sports Posts
 *
 * Enhance your sports posts with rich, detailed metadata about games, teams, and players.
 *
 * @link              http://sportscodes.org
 * @since             1.0.0
 * @package           SportsPost
 *
 * @wordpress-plugin
 * Plugin Name:       SportsPost - Enriched Sports Posts
 * Plugin URI:        https://wordpress.org/plugins/sportspost/
 * Description:       Enhance your sports posts with rich, detailed metadata about games, teams, and players.
 * Version:           1.0.0
 * Author:            XML Team Solutions
 * Author URI:        http://xmlteam.com/
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       sportspost
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 + The SportsCodes API endpoint
 */
// define( 'SPORTSPOST_API_ENDPOINT', 'http://dev.sportscodes.org/getCodes.php' );
define( 'SPORTSPOST_API_ENDPOINT', 'http://api.sportscodes.org/v1/getCodes.php' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sportspost.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sportspost() {

	$plugin = new SportsPost();
	$plugin->run();

}
run_sportspost();
