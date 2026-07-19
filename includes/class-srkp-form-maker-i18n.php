<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Define the internationalization functionality
 *
 * @link       https://github.com/keshavsharma262001-ops/srkp-form-maker
 * @since      1.0.0
 *
 * @package    Srkp_Form_Maker
 * @subpackage Srkp_Form_Maker/includes
 */

class Srkp_Form_Maker_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'srkp-form-maker',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

}
