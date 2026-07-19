<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/keshavsharma262001-ops/srkp-form-maker
 * @since      1.0.0
 *
 * @package    Srkp_Form_Maker
 * @subpackage Srkp_Form_Maker/includes
 */

class Srkp_Form_Maker_Activator {

	/**
	 * Run the activation seeding and custom post types setup.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::register_submission_cpts();
		flush_rewrite_rules();
		self::seed_default_forms();
	}

	/**
	 * Temporary register CPTs so we can seed posts on activation.
	 *
	 * @since    1.0.0
	 */
	private static function register_submission_cpts() {
		$labels_sub = array(
			'name'               => 'Form Submissions',
			'singular_name'      => 'Submission',
			'menu_name'          => 'Submissions',
			'name_admin_bar'     => 'Submission',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Submission',
			'new_item'           => 'New Submission',
			'edit_item'          => 'View Submission',
			'view_item'          => 'View Submission',
			'all_items'          => 'All Submissions',
			'search_items'       => 'Search Submissions',
			'not_found'          => 'No submissions found.',
			'not_found_in_trash' => 'No submissions found in Trash.',
		);

		$args_sub = array(
			'labels'             => $labels_sub,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=srkp_form',
			'query_var'          => true,
			'capability_type'    => 'post',
			'capabilities'       => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'       => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
		);

		register_post_type( 'srkp_submission', $args_sub );

		$labels_form = array(
			'name'               => 'Forms',
			'singular_name'      => 'Form',
			'menu_name'          => 'Forms Builder',
			'name_admin_bar'     => 'Form',
			'add_new'            => 'Add New Form',
			'add_new_item'       => 'Add New Form',
			'new_item'           => 'New Form',
			'edit_item'          => 'Edit Form',
			'all_items'          => 'All Forms',
			'search_items'       => 'Search Forms',
		);

		$args_form = array(
			'labels'             => $labels_form,
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-feedback',
			'supports'           => array( 'title' ),
		);

		register_post_type( 'srkp_form', $args_form );
	}

	/**
	 * Seed the default booking, banquet, and contact forms.
	 *
	 * @since    1.0.0
	 */
	private static function seed_default_forms() {
		$default_forms = array(
			'booking-form' => array(
				'title'  => 'Booking Form',
				'fields' => array(
					array( 'id' => 'b_room', 'label' => 'Select Suite Type', 'type' => 'select', 'options' => 'imperial-pool:Imperial Pool Suite — ₹12,500 / night, mountain-view:Mountain View Executive — ₹8,500 / night, redstone-sig:Redstone Signature Suite — ₹9,500 / night, royal-blue:Royal Blue Room — ₹6,500 / night, blue-marble:Blue Marble Classic — ₹9,000 / night, presidential:Presidential Suite — ₹10,500 / night', 'required' => 'yes', 'width' => 'full' ),
					array( 'id' => 'b_checkin', 'label' => 'Check-In Date', 'type' => 'date', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'b_checkout', 'label' => 'Check-Out Date', 'type' => 'date', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'b_guests', 'label' => 'Guests', 'type' => 'select', 'options' => '1:1 Adult, 2:2 Adults, 3:3 Adults, 4:4 Adults', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'b_children', 'label' => 'Children', 'type' => 'select', 'options' => '0:0 Children, 1:1 Child, 2:2 Children', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'b_name', 'label' => 'Full Name', 'type' => 'text', 'required' => 'yes', 'width' => 'full' ),
					array( 'id' => 'b_email', 'label' => 'Email Address', 'type' => 'email', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'b_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => 'yes', 'width' => 'half' ),
				)
			),
			'banquet-form' => array(
				'title'  => 'Banquet Form',
				'fields' => array(
					array( 'id' => 'ban_type', 'label' => 'Event Type', 'type' => 'select', 'options' => 'wedding:Wedding / Reception, corporate:Corporate Seminar / Meeting, birthday:Birthday / Anniversary Celebration, social:Social Gathering / Dinner Party', 'required' => 'yes', 'width' => 'full' ),
					array( 'id' => 'ban_date', 'label' => 'Proposed Date', 'type' => 'date', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'ban_guests', 'label' => 'Expected Guest Count', 'type' => 'select', 'options' => 'less-100:Less than 100 Guests, 100-250:100 to 250 Guests, 250-500:250 to 500 Guests, 500-plus:More than 500 Guests', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'ban_name', 'label' => 'Organizer\'s Full Name', 'type' => 'text', 'required' => 'yes', 'width' => 'full' ),
					array( 'id' => 'ban_email', 'label' => 'Email Address', 'type' => 'email', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'ban_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'ban_requirements', 'label' => 'Special Requirements / Catering Preferences', 'type' => 'textarea', 'required' => '', 'width' => 'full' ),
				)
			),
			'contact-form' => array(
				'title'  => 'Contact Form',
				'fields' => array(
					array( 'id' => 'c_name', 'label' => 'Full Name', 'type' => 'text', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'c_email', 'label' => 'Email Address', 'type' => 'email', 'required' => 'yes', 'width' => 'half' ),
					array( 'id' => 'c_subject', 'label' => 'Subject', 'type' => 'text', 'required' => 'yes', 'width' => 'full' ),
					array( 'id' => 'c_message', 'label' => 'Your Message', 'type' => 'textarea', 'required' => 'yes', 'width' => 'full' ),
				)
			)
		);

		foreach ( $default_forms as $slug => $form ) {
			// Find post by slug in the CPT
			$posts = get_posts( array(
				'post_type'   => 'srkp_form',
				'name'        => $slug,
				'post_status' => 'any',
				'numberposts' => 1
			) );

			if ( empty( $posts ) ) {
				$post_id = wp_insert_post( array(
					'post_title'  => $form['title'],
					'post_name'   => $slug,
					'post_status' => 'publish',
					'post_type'   => 'srkp_form'
				) );
				if ( $post_id && ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, '_srkp_form_fields', $form['fields'] );
				}
			}
		}
	}
}
