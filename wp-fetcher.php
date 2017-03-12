<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://github.com/diegoacuna/
 * @since             1.0.0
 * @package           Wp_Fetcher
 *
 * @wordpress-plugin
 * Plugin Name:       WPFetcher
 * Plugin URI:        http://github.com/diegoacuna/wp-fetcher
 * Description:       Fetch posts from other Wordpress Blog and allows to display those posts using a shortcode. It uses the standard feed from the fetched wordpress blog (XML format).
 * Version:           1.0.0
 * Author:            Diego Acuña
 * Author URI:        http://github.com/diegoacuna/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-fetcher
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-fetcher-activator.php
 */
function activate_wp_fetcher() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-fetcher-activator.php';
	Wp_Fetcher_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-fetcher-deactivator.php
 */
function deactivate_wp_fetcher() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-fetcher-deactivator.php';
	Wp_Fetcher_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_fetcher' );
register_deactivation_hook( __FILE__, 'deactivate_wp_fetcher' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-fetcher.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_fetcher() {

	$plugin = new Wp_Fetcher();
	$plugin->run();

}
run_wp_fetcher();
