<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * The file that defines the core plugin class
 *
 * @link       https://github.com/keshavsharma262001-ops/srkp-form-maker
 * @since      1.0.0
 *
 * @package    Srkp_Form_Maker
 * @subpackage Srkp_Form_Maker/includes
 */

class Srkp_Form_Maker {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Srkp_Form_Maker_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SRKP_FORM_MAKER_VERSION' ) ) {
			$this->version = SRKP_FORM_MAKER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'srkp-form-maker';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-srkp-form-maker-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-srkp-form-maker-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-srkp-form-maker-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-srkp-form-maker-public.php';

		$this->loader = new Srkp_Form_Maker_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		// Translation files are loaded automatically by WordPress.org since 4.6.
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Srkp_Form_Maker_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_submission_cpts' );
		$this->loader->add_filter( 'manage_srkp_submission_posts_columns', $plugin_admin, 'set_submission_columns' );
		$this->loader->add_action( 'manage_srkp_submission_posts_custom_column', $plugin_admin, 'custom_submission_column', 10, 2 );
		$this->loader->add_filter( 'manage_srkp_form_posts_columns', $plugin_admin, 'set_forms_columns' );
		$this->loader->add_action( 'manage_srkp_form_posts_custom_column', $plugin_admin, 'custom_forms_column', 10, 2 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_boxes' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_form_fields' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Srkp_Form_Maker_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_srkp_submit_form', $plugin_public, 'ajax_submit_form' );
		$this->loader->add_action( 'wp_ajax_nopriv_srkp_submit_form', $plugin_public, 'ajax_submit_form' );
		
		// Hook the shortcode registration inside init
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
