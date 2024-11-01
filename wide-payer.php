<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://homescript1.gitlab.io/wide-payer/
 * @since             1.0.0
 * @package           Wide_Payer
 *
 * @wordpress-plugin
 * Plugin Name:       Wide Payer
 * Plugin URI:        https://homescript1.gitlab.io/wide-payer/
 * Description:       Payer vos commandes en ligne en utilisant WooCommerce Wide Payer MTN Momo.
 * Version:           1.0.4
 * Author:            HomeScript
 * Author URI:        https://github.com/homescript1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wide-payer
 * Domain Path:       /languages
 * Contributors :     homescript1
 * WC requires at least: 3.0.0
 * WC tested up to: 3.4.2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) && ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WIDE_PAYER_VERSION', '1.0.4' );
define( 'WIDE_PAYER_DIR', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wide-payer-activator.php
 */
function activate_wide_payer() {
	require_once WIDE_PAYER_DIR . 'includes/class-wide-payer-activator.php';
	Wide_Payer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wide-payer-deactivator.php
 */
function deactivate_wide_payer() {
	require_once WIDE_PAYER_DIR . 'includes/class-wide-payer-deactivator.php';
	Wide_Payer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wide_payer' );
register_deactivation_hook( __FILE__, 'deactivate_wide_payer' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WIDE_PAYER_DIR . 'includes/class-wide-payer.php';



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wide_payer() {

	$plugin = new Wide_Payer();
	$plugin->run();

}
run_wide_payer();
