<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/keshavsharma262001-ops/srkp-form-maker
 * @since      1.0.0
 *
 * @package    Srkp_Form_Maker
 * @subpackage Srkp_Form_Maker/public
 */

class Srkp_Form_Maker_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets / scripts for the public side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script( 'srkp-form-maker-helper', '' );
		wp_enqueue_script( 'srkp-form-maker-helper' );
		wp_localize_script( 'srkp-form-maker-helper', 'srkp_form_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'srkp_form_nonce' )
		) );
		
		// Add inline script for dynamic shortcode submissions
		$inline_js = "
		function handleSrkpDynamicFormSubmit(event, formSlug, formId) {
			event.preventDefault();
			var form = document.getElementById(formId);
			if (!form) return;
			
			var button = form.querySelector('button[type=\"submit\"]');
			if (button) {
				button.disabled = true;
				button.innerHTML = 'Submitting...';
			}
			
			var formData = new FormData(form);
			var submitData = {
				action: 'srkp_submit_form',
				nonce: srkp_form_ajax.nonce,
				form_type: formSlug.replace('-form', '')
			};
			
			for (var pair of formData.entries()) {
				submitData[pair[0]] = pair[1];
			}
			
			jQuery.post(srkp_form_ajax.ajax_url, submitData, function(res) {
				if (res.success) {
					form.innerHTML = '<div style=\"padding: 20px; background: rgba(76, 175, 80, 0.1); border: 1px solid #4CAF50; color: #4CAF50; border-radius: 4px; text-align: center;\"><h4>Thank You!</h4><p>Your submission was captured successfully.</p></div>';
				} else {
					alert(res.data.message || 'Submission failed.');
					if (button) {
						button.disabled = false;
						button.innerHTML = 'Submit';
					}
				}
			}).fail(function() {
				alert('Server error.');
				if (button) {
					button.disabled = false;
					button.innerHTML = 'Submit';
				}
			});
		}
		";
		wp_add_inline_script( 'srkp-form-maker-helper', $inline_js );
	}

	/**
	 * AJAX Submission Handler.
	 *
	 * @since    1.0.0
	 */
	public function ajax_submit_form() {
		check_ajax_referer( 'srkp_form_nonce', 'nonce' );

		$form_type = isset( $_POST['form_type'] ) ? sanitize_text_field( $_POST['form_type'] ) : '';
		if ( empty( $form_type ) ) {
			wp_send_json_error( array( 'message' => 'Required fields missing.' ) );
		}

		// Load fields to map dynamically
		$form_post = get_page_by_path( $form_type . '-form', OBJECT, 'srkp_form' );
		if ( ! $form_post ) {
			$posts = get_posts( array(
				'post_type'      => 'srkp_form',
				'name'           => $form_type . '-form',
				'posts_per_page' => 1
			) );
			if ( ! empty( $posts ) ) {
				$form_post = $posts[0];
			}
		}

		$name = 'Anonymous Submitter';
		$email = '';
		$phone = '';
		$extra_details = array();

		if ( $form_post ) {
			$fields = get_post_meta( $form_post->ID, '_srkp_form_fields', true );
			if ( is_array( $fields ) ) {
				foreach ( $fields as $field ) {
					$field_id = $field['id'];
					if ( isset( $_POST[$field_id] ) ) {
						$val = sanitize_text_field( wp_unslash( $_POST[$field_id] ) );
						
						// Identify primary fields based on id patterns or labels
						if ( strpos( $field_id, 'name' ) !== false ) {
							$name = $val;
						} elseif ( strpos( $field_id, 'email' ) !== false ) {
							$email = sanitize_email( $val );
						} elseif ( strpos( $field_id, 'phone' ) !== false || strpos( $field_id, 'tel' ) !== false ) {
							$phone = $val;
						}
						
						$extra_details[$field['label']] = $val;
					}
				}
			}
		}

		// In case no field match primary name/email/phone, fall back to POST primary arguments
		if ( $name === 'Anonymous Submitter' && isset( $_POST['name'] ) ) {
			$name = sanitize_text_field( $_POST['name'] );
		}
		if ( empty( $email ) && isset( $_POST['email'] ) ) {
			$email = sanitize_email( $_POST['email'] );
		}
		if ( empty( $phone ) && isset( $_POST['phone'] ) ) {
			$phone = sanitize_text_field( $_POST['phone'] );
		}

		// Add reference details for booking
		if ( $form_type === 'booking' && isset( $_POST['reference'] ) ) {
			$extra_details['Reference Code'] = sanitize_text_field( $_POST['reference'] );
		}

		// Create custom post of type 'srkp_submission'
		$post_title = $name . ' — ' . ucfirst( $form_type ) . ' Submission';
		$post_data = array(
			'post_title'   => $post_title,
			'post_status'  => 'publish',
			'post_type'    => 'srkp_submission',
			'post_content' => '',
		);

		add_filter( 'map_meta_cap', array( $this, 'allow_submission_creation' ), 10, 4 );
		$post_id = wp_insert_post( $post_data );
		remove_filter( 'map_meta_cap', array( $this, 'allow_submission_creation' ), 10 );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			wp_send_json_error( array( 'message' => 'Could not save submission.' ) );
		}

		// Save meta fields
		update_post_meta( $post_id, '_srkp_submission_type', $form_type );
		update_post_meta( $post_id, '_srkp_submission_name', $name );
		update_post_meta( $post_id, '_srkp_submission_email', $email );
		update_post_meta( $post_id, '_srkp_submission_phone', $phone );
		update_post_meta( $post_id, '_srkp_submission_details', $extra_details );

		// Send email notification to site admin (or multiple custom addresses)
		$to = '';
		if ( $form_post ) {
			$to = get_post_meta( $form_post->ID, '_srkp_notification_emails', true );
		}
		if ( empty( $to ) ) {
			$to = get_option( 'admin_email' );
		}
		$subject = 'New ' . ucfirst( $form_type ) . ' Submission from ' . $name;
		
		// Create HTML email body
		$message_body = '<html><body>';
		$message_body .= '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e1e1e1; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">';
		$message_body .= '<div style="background: #1a1615; color: #FAF9F6; padding: 25px; text-align: center; border-bottom: 2px solid #BC9A5F;">';
		$message_body .= '<h2 style="margin: 0; font-family: Georgia, serif; letter-spacing: 1px; color: #FAF9F6;">SRKP Resort</h2>';
		$message_body .= '<p style="margin: 5px 0 0 0; font-size: 13px; color: #BC9A5F; text-transform: uppercase; letter-spacing: 1px;">New Form Submission</p>';
		$message_body .= '</div>';
		$message_body .= '<div style="padding: 30px; background: #fff; color: #333; line-height: 1.6;">';
		$message_body .= '<p style="margin-top: 0; font-size: 16px;">Hello Admin,</p>';
		$message_body .= '<p>You have received a new form submission from your website. Below are the details:</p>';
		
		$message_body .= '<table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px;">';
		$message_body .= '<tr style="background: #f9f9f9;"><td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 180px;">Submission Type</td><td style="padding: 10px; border-bottom: 1px solid #eee;">' . esc_html( ucfirst( $form_type ) ) . '</td></tr>';
		$message_body .= '<tr><td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Full Name</td><td style="padding: 10px; border-bottom: 1px solid #eee;">' . esc_html( $name ) . '</td></tr>';
		$message_body .= '<tr style="background: #f9f9f9;"><td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Email Address</td><td style="padding: 10px; border-bottom: 1px solid #eee;">' . esc_html( $email ) . '</td></tr>';
		$message_body .= '<tr><td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Phone Number</td><td style="padding: 10px; border-bottom: 1px solid #eee;">' . esc_html( $phone ) . '</td></tr>';
		
		$bg = true;
		foreach ( $extra_details as $label => $val ) {
			$bg_style = $bg ? 'background: #f9f9f9;' : '';
			$message_body .= '<tr style="' . $bg_style . '"><td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">' . esc_html( $label ) . '</td><td style="padding: 10px; border-bottom: 1px solid #eee;">' . esc_html( $val ) . '</td></tr>';
			$bg = !$bg;
		}
		
		$message_body .= '</table>';
		$message_body .= '<p style="margin-top: 30px; font-size: 13px; color: #777;">You can view and manage this submission directly from your WordPress dashboard under <strong>Submissions</strong>.</p>';
		$message_body .= '</div>';
		$message_body .= '<div style="background: #f5f5f5; color: #888; padding: 15px; text-align: center; font-size: 12px; border-top: 1px solid #e1e1e1;">';
		$message_body .= '&copy; ' . date( 'Y' ) . ' SRKP Resort. All Rights Reserved.';
		$message_body .= '</div>';
		$message_body .= '</div>';
		$message_body .= '</body></html>';
		
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		wp_mail( $to, $subject, $message_body, $headers );

		wp_send_json_success( array( 'message' => 'Submission saved successfully!' ) );
	}

	/**
	 * Filter to temporarily allow public creation of submissions.
	 *
	 * @since    1.0.0
	 */
	public function allow_submission_creation( $caps, $cap, $user_id, $args ) {
		if ( $cap === 'edit_post' || $cap === 'edit_others_posts' ) {
			return array( 'read' );
		}
		return $caps;
	}

	/**
	 * Register shortcodes.
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'srkp_form', array( $this, 'form_shortcode' ) );
	}

	/**
	 * Shortcode callback to render the forms.
	 *
	 * @since    1.0.0
	 */
	public function form_shortcode( $atts ) {
		$a = shortcode_atts( array(
			'slug'        => '',
			'button_text' => 'Submit'
		), $atts );

		if ( empty( $a['slug'] ) ) {
			return '<p style="color:red;">Please specify a form slug (e.g. <code>[srkp_form slug="contact-form"]</code>).</p>';
		}

		ob_start();
		$form_id = 'dynamicForm_' . sanitize_title( $a['slug'] );
		$onsubmit = 'handleSrkpDynamicFormSubmit(event, "' . esc_js( $a['slug'] ) . '", "' . esc_js( $form_id ) . '")';
		
		$this->render_dynamic_form( $a['slug'], $form_id, $onsubmit, $a['button_text'] );
		
		return ob_get_clean();
	}

	/**
	 * Dynamic Form HTML Builder.
	 *
	 * @since    1.0.0
	 */
	private function render_dynamic_form( $form_slug, $form_id_attr, $onsubmit_attr, $submit_button_text = 'Submit' ) {
		$form_post = get_page_by_path( $form_slug, OBJECT, 'srkp_form' );
		if ( ! $form_post ) {
			$posts = get_posts( array(
				'post_type'      => 'srkp_form',
				'name'           => $form_slug,
				'posts_per_page' => 1
			) );
			if ( ! empty( $posts ) ) {
				$form_post = $posts[0];
			}
		}

		if ( ! $form_post ) {
			echo "<p style='color:red;'>Form '" . esc_html( $form_slug ) . "' not found. Please verify it is created in the Forms Builder admin screen.</p>";
			return;
		}

		$fields = get_post_meta( $form_post->ID, '_srkp_form_fields', true );
		if ( ! is_array( $fields ) ) {
			$fields = json_decode( $fields, true );
		}
		if ( ! is_array( $fields ) ) {
			$fields = array();
		}

		echo '<form id="' . esc_attr( $form_id_attr ) . '" class="booking-form mt-20" onsubmit="' . esc_attr( $onsubmit_attr ) . '">';
		
		$in_row = false;
		foreach ( $fields as $index => $field ) {
			$field_id      = esc_attr( $field['id'] );
			$field_label   = esc_html( $field['label'] );
			$field_type    = esc_attr( $field['type'] );
			$field_req     = ! empty( $field['required'] ) ? 'required' : '';
			$field_options = isset( $field['options'] ) ? $field['options'] : '';
			$field_width   = isset( $field['width'] ) ? $field['width'] : 'full';

			if ( $field_width === 'half' ) {
				if ( ! $in_row ) {
					echo '<div class="form-row">';
					$in_row = true;
				}
			} else {
				if ( $in_row ) {
					echo '</div>';
					$in_row = false;
				}
			}

			echo '<div class="form-group">';
			echo '<label for="' . $field_id . '">' . $field_label . '</label>';

			if ( $field_type === 'select' ) {
				echo '<select id="' . $field_id . '" name="' . $field_id . '" ' . $field_req . '>';
				$options_arr = explode( ',', $field_options );
				foreach ( $options_arr as $opt ) {
					$opt = trim( $opt );
					if ( strpos( $opt, ':' ) !== false ) {
						list( $val, $lbl ) = explode( ':', $opt, 2 );
					} else {
						$val = $opt;
						$lbl = $opt;
					}
					echo '<option value="' . esc_attr( trim( $val ) ) . '">' . esc_html( trim( $lbl ) ) . '</option>';
				}
				echo '</select>';
			} elseif ( $field_type === 'textarea' ) {
				echo '<textarea id="' . $field_id . '" name="' . $field_id . '" rows="3" placeholder="Enter ' . strtolower( $field_label ) . '..." ' . $field_req . '></textarea>';
			} else {
				echo '<input type="' . $field_type . '" id="' . $field_id . '" name="' . $field_id . '" placeholder="Enter ' . strtolower( $field_label ) . '" ' . $field_req . '>';
			}
			echo '</div>';

			if ( $field_width === 'half' ) {
				$next_field = isset( $fields[$index + 1] ) ? $fields[$index + 1] : null;
				if ( ! $next_field || ( isset( $next_field['width'] ) && $next_field['width'] !== 'half' ) ) {
					echo '</div>';
					$in_row = false;
				}
			}
		}

		if ( $in_row ) {
			echo '</div>';
		}

		echo '<button type="submit" class="btn btn-primary w-full btn-lg mt-20">' . esc_html( $submit_button_text ) . '</button>';
		echo '</form>';
	}

}
