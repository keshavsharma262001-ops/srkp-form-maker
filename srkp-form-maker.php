<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/keshavsharma262001-ops/srkp-form-maker
 * @since             1.0.0
 * @package           Srkp_Form_Maker
 *
 * @wordpress-plugin
 * Plugin Name:       SRKP Form Maker
 * Plugin URI:        https://github.com/keshavsharma262001-ops/srkp-form-maker
 * Description:       Automatically captures front-end form submissions (Contact, Booking, Banquet) and saves them to a custom post type, with a built-in admin Forms Builder CPT.
 * Version:           1.0.0
 * Author:            SRKP Team
 * Author URI:        https://github.com/keshavsharma262001-ops
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       srkp-form-maker
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'SRKP_FORM_MAKER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 */
function srkp_form_maker_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-srkp-form-maker-activator.php';
	Srkp_Form_Maker_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function srkp_form_maker_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-srkp-form-maker-deactivator.php';
	Srkp_Form_Maker_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'srkp_form_maker_activate' );
register_deactivation_hook( __FILE__, 'srkp_form_maker_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-srkp-form-maker.php';

/**
 * Begins execution of the plugin.
 */
function srkp_form_maker_run() {
	$plugin = new Srkp_Form_Maker();
	$plugin->run();
}
srkp_form_maker_run();
