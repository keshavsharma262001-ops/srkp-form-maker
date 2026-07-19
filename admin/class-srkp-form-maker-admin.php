<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/keshavsharma262001-ops/srkp-form-maker
 * @since      1.0.0
 *
 * @package    Srkp_Form_Maker
 * @subpackage Srkp_Form_Maker/admin
 */

class Srkp_Form_Maker_Admin {

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
	 * Register the stylesheets / scripts for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		global $post;
		if ( $hook === 'post.php' || $hook === 'post-new.php' ) {
			if ( isset( $post->post_type ) && $post->post_type === 'srkp_form' ) {
				wp_enqueue_script( 'jquery-ui-sortable' );
			}
		}
	}

	/**
	 * Register Custom Post Types for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function register_submission_cpts() {
		// Submissions CPT
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

		// Forms Builder CPT
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
	 * Set columns for the Submissions CPT.
	 *
	 * @since    1.0.0
	 */
	public function set_submission_columns( $columns ) {
		unset( $columns['date'] );
		$columns['form_type']       = 'Form Type';
		$columns['submitter_name']  = 'Name';
		$columns['submitter_email'] = 'Email';
		$columns['submitter_phone'] = 'Phone';
		$columns['date']            = 'Date';
		return $columns;
	}

	/**
	 * Render custom column contents for Submissions CPT.
	 *
	 * @since    1.0.0
	 */
	public function custom_submission_column( $column, $post_id ) {
		switch ( $column ) {
			case 'form_type' :
				$type = get_post_meta( $post_id, '_srkp_submission_type', true );
				echo esc_html( ucfirst( str_replace( '_', ' ', $type ) ) );
				break;
			case 'submitter_name' :
				echo esc_html( get_post_meta( $post_id, '_srkp_submission_name', true ) );
				break;
			case 'submitter_email' :
				$email = get_post_meta( $post_id, '_srkp_submission_email', true );
				if ( ! empty( $email ) ) {
					echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
				} else {
					echo '—';
				}
				break;
			case 'submitter_phone' :
				echo esc_html( get_post_meta( $post_id, '_srkp_submission_phone', true ) );
				break;
		}
	}

	/**
	 * Set columns for Forms CPT.
	 *
	 * @since    1.0.0
	 */
	public function set_forms_columns( $columns ) {
		$columns['form_shortcode'] = 'Shortcode';
		return $columns;
	}

	/**
	 * Render custom column contents for Forms CPT.
	 *
	 * @since    1.0.0
	 */
	public function custom_forms_column( $column, $post_id ) {
		if ( $column === 'form_shortcode' ) {
			$post = get_post( $post_id );
			$slug = $post->post_name;
			if ( empty( $slug ) ) {
				$slug = sanitize_title( $post->post_title );
			}
			$shortcode = '[srkp_form slug="' . esc_attr( $slug ) . '"]';
			echo '<code style="background:#f5f5f5; border:1px solid #ccc; padding:4px 8px; font-family:monospace; font-size:11px; font-weight:bold; cursor:pointer; border-radius:3px;" onclick="var temp = document.createElement(\'input\'); temp.value=this.innerText; document.body.appendChild(temp); temp.select(); document.execCommand(\'copy\'); document.body.removeChild(temp); alert(\'Shortcode copied to clipboard!\');" title="Click to copy">' . esc_html( $shortcode ) . '</code>';
		}
	}

	/**
	 * Add metaboxes for Submissions and Forms.
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {
		// Submission Details Metabox
		add_meta_box(
			'srkp_submission_details',
			'Submission Details',
			array( $this, 'render_submission_details' ),
			'srkp_submission',
			'normal',
			'high'
		);

		// Forms fields builder
		add_meta_box(
			'srkp_form_fields_manager',
			'Manage Form Fields',
			array( $this, 'render_form_fields_metabox' ),
			'srkp_form',
			'normal',
			'high'
		);

		// Forms shortcode sidebar
		add_meta_box(
			'srkp_form_shortcode_sidebar',
			'Form Shortcode',
			array( $this, 'render_form_shortcode_sidebar' ),
			'srkp_form',
			'side',
			'default'
		);
	}

	/**
	 * Render Submission Details.
	 *
	 * @since    1.0.0
	 */
	public function render_submission_details( $post ) {
		$type    = get_post_meta( $post->ID, '_srkp_submission_type', true );
		$name    = get_post_meta( $post->ID, '_srkp_submission_name', true );
		$email   = get_post_meta( $post->ID, '_srkp_submission_email', true );
		$phone   = get_post_meta( $post->ID, '_srkp_submission_phone', true );
		$details = get_post_meta( $post->ID, '_srkp_submission_details', true );
		?>
		<table class="form-table">
			<tr>
				<th style="width:200px;">Form Type</th>
				<td><strong><?php echo esc_html( ucfirst( str_replace( '_', ' ', $type ) ) ); ?></strong></td>
			</tr>
			<tr>
				<th>Full Name</th>
				<td><?php echo esc_html( $name ); ?></td>
			</tr>
			<tr>
				<th>Email</th>
				<td><?php echo esc_html( $email ); ?></td>
			</tr>
			<tr>
				<th>Phone</th>
				<td><?php echo esc_html( $phone ); ?></td>
			</tr>
			<?php if ( is_array( $details ) ) : ?>
				<?php foreach ( $details as $key => $val ) : ?>
					<tr>
						<th><?php echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) ); ?></th>
						<td><?php echo esc_html( $val ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Render Form Shortcode sidebar.
	 *
	 * @since    1.0.0
	 */
	public function render_form_shortcode_sidebar( $post ) {
		$slug = $post->post_name;
		if ( empty( $slug ) ) {
			$slug = sanitize_title( $post->post_title );
		}
		?>
		<p style="margin-bottom:10px;">Copy this shortcode and paste it on any page, post, or widget area to display this form:</p>
		<input type="text" value='[srkp_form slug="<?php echo esc_attr( $slug ); ?>"]' readonly onclick="this.select();" style="width:100%; font-family:monospace; padding:6px; background:#f5f5f5; border:1px solid #ccc; font-size:11px; font-weight:bold; cursor:pointer;" title="Click to select all">
		<p style="font-size:11px; color:#666; margin-top:5px; font-style:italic;">Tip: Click the box to select all text, then copy (Ctrl+C / Cmd+C).</p>
		<?php
	}

	/**
	 * Render Form Fields Manager Metabox.
	 *
	 * @since    1.0.0
	 */
	public function render_form_fields_metabox( $post ) {
		wp_nonce_field( 'srkp_form_fields_save', 'srkp_form_fields_nonce' );
		
		$fields = get_post_meta( $post->ID, '_srkp_form_fields', true );
		if ( ! is_array( $fields ) ) {
			$fields = json_decode( $fields, true );
		}
		if ( ! is_array( $fields ) ) {
			$fields = array();
		}

		$notification_emails = get_post_meta( $post->ID, '_srkp_notification_emails', true );
		if ( empty( $notification_emails ) ) {
			$notification_emails = get_option( 'admin_email' );
		}
		
		$types = array(
			'text'     => 'Text Input',
			'email'    => 'Email Address',
			'tel'      => 'Phone/Tel',
			'date'     => 'Date Picker',
			'number'   => 'Number Input',
			'select'   => 'Dropdown Select',
			'textarea' => 'Multi-line Textarea'
		);
		
		$widths = array(
			'full' => 'Full Width (1 Column)',
			'half' => 'Half Width (2 Columns)'
		);
		?>
		<div style="margin-bottom:20px; background:#f9f9f9; padding:15px; border:1px solid #e5e5e5; border-radius:4px;">
			<label for="notification_emails" style="font-weight:bold; display:block; margin-bottom:5px;">Notification Emails (comma-separated):</label>
			<input type="text" id="notification_emails" name="notification_emails" value="<?php echo esc_attr( $notification_emails ); ?>" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" placeholder="e.g. admin@resort.com, manager@resort.com">
			<p style="font-size:12px; color:#666; margin:5px 0 0 0;">Submissions will be emailed to these addresses. Leave empty to use site admin email.</p>
		</div>

		<div id="srkp-fields-container" style="margin-bottom:15px;">
			<p style="color:#666; font-style:italic; margin-bottom:12px;">Drag and drop rows using the menu icon (<span class="dashicons dashicons-menu" style="vertical-align:middle; font-size:16px;"></span>) to reorder the fields as they will appear on the frontend form.</p>
			
			<table class="wp-list-table widefat fixed striped" style="width:100%;">
				<thead>
					<tr>
						<th style="width:5%; text-align:center;"></th>
						<th style="width:25%;">Field Label</th>
						<th style="width:20%;">Field Key (unique ID)</th>
						<th style="width:18%;">Input Type</th>
						<th style="width:18%;">Width</th>
						<th style="width:6%; text-align:center;">Req?</th>
						<th style="width:8%;">Actions</th>
					</tr>
				</thead>
				<tbody id="srkp-fields-rows">
					<?php if ( ! empty( $fields ) ) : ?>
						<?php foreach ( $fields as $index => $field ) : ?>
							<tr class="field-row" data-index="<?php echo esc_attr( $index ); ?>">
								<td class="drag-handle" style="text-align:center; cursor:move; vertical-align:middle;">
									<span class="dashicons dashicons-menu" style="color:#aaa;"></span>
								</td>
								<td>
									<input type="text" name="form_fields[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $field['label'] ); ?>" style="width:100%;" required>
									<div class="options-container" style="margin-top:5px; <?php echo $field['type'] === 'select' ? '' : 'display:none;'; ?>">
										<label style="font-size:11px; color:#555; display:block;">Select Options (comma-separated value:label):</label>
										<input type="text" name="form_fields[<?php echo esc_attr( $index ); ?>][options]" value="<?php echo esc_attr( isset( $field['options'] ) ? $field['options'] : '' ); ?>" style="width:100%;" placeholder="e.g. standard:Standard, premium:Premium">
									</div>
								</td>
								<td>
									<input type="text" name="form_fields[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $field['id'] ); ?>" style="width:100%; font-family:monospace;" required>
								</td>
								<td>
									<select name="form_fields[<?php echo esc_attr( $index ); ?>][type]" class="field-type-select" style="width:100%;">
										<?php foreach ( $types as $val => $lbl ) : ?>
											<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $field['type'], $val ); ?>><?php echo esc_html( $lbl ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td>
									<select name="form_fields[<?php echo esc_attr( $index ); ?>][width]" style="width:100%;">
										<?php foreach ( $widths as $val => $lbl ) : ?>
											<option value="<?php echo esc_attr( $val ); ?>" <?php selected( isset( $field['width'] ) ? $field['width'] : 'full', $val ); ?>><?php echo esc_html( $lbl ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td style="text-align:center; vertical-align:middle;">
									<input type="checkbox" name="form_fields[<?php echo esc_attr( $index ); ?>][required]" value="yes" <?php checked( ! empty( $field['required'] ) ); ?>>
								</td>
								<td style="vertical-align:middle;">
									<button type="button" class="button button-link-delete delete-field-row">Delete</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			
			<p style="margin-top:15px;">
				<button type="button" id="add-field-row-btn" class="button button-primary">+ Add New Field</button>
			</p>
		</div>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
			var rowIndex = $('#srkp-fields-rows .field-row').length;
			
			// Initialize sortable behavior
			if ($.fn.sortable) {
				$("#srkp-fields-rows").sortable({
					handle: ".drag-handle",
					placeholder: "ui-state-highlight",
					update: function(event, ui) {
						reindexRows();
					}
				});
			}
			
			$('#add-field-row-btn').on('click', function(e) {
				e.preventDefault();
				var rowHtml = '<tr class="field-row" data-index="' + rowIndex + '">' +
					'<td class="drag-handle" style="text-align:center; cursor:move; vertical-align:middle;">' +
						'<span class="dashicons dashicons-menu" style="color:#aaa;"></span>' +
					'</td>' +
					'<td>' +
						'<input type="text" name="form_fields[' + rowIndex + '][label]" placeholder="e.g. Full Name" style="width:100%;" required>' +
						'<div class="options-container" style="margin-top:5px; display:none;">' +
							'<label style="font-size:11px; color:#555; display:block;">Select Options (comma-separated value:label):</label>' +
							'<input type="text" name="form_fields[' + rowIndex + '][options]" style="width:100%;" placeholder="val1:Label 1, val2:Label 2">' +
						'</div>' +
					'</td>' +
					'<td>' +
						'<input type="text" name="form_fields[' + rowIndex + '][id]" placeholder="e.g. b_name" style="width:100%; font-family:monospace;" required>' +
					'</td>' +
					'<td>' +
						'<select name="form_fields[' + rowIndex + '][type]" class="field-type-select" style="width:100%;">' +
							'<option value="text">Text Input</option>' +
							'<option value="email">Email Address</option>' +
							'<option value="tel">Phone/Tel</option>' +
							'<option value="date">Date Picker</option>' +
							'<option value="number">Number Input</option>' +
							'<option value="select">Dropdown Select</option>' +
							'<option value="textarea">Multi-line Textarea</option>' +
						'</select>' +
					'</td>' +
					'<td>' +
						'<select name="form_fields[' + rowIndex + '][width]" style="width:100%;">' +
							'<option value="full">Full Width (1 Column)</option>' +
							'<option value="half">Half Width (2 Columns)</option>' +
						'</select>' +
					'</td>' +
					'<td style="text-align:center; vertical-align:middle;">' +
						'<input type="checkbox" name="form_fields[' + rowIndex + '][required]" value="yes" checked>' +
					'</td>' +
					'<td style="vertical-align:middle;">' +
						'<button type="button" class="button button-link-delete delete-field-row">Delete</button>' +
					'</td>' +
				'</tr>';
				
				$('#srkp-fields-rows').append(rowHtml);
				rowIndex++;
				reindexRows();
			});
			
			$(document).on('change', '.field-type-select', function() {
				var row = $(this).closest('.field-row');
				if ($(this).val() === 'select') {
					row.find('.options-container').show();
				} else {
					row.find('.options-container').hide();
				}
			});
			
			$(document).on('click', '.delete-field-row', function(e) {
				e.preventDefault();
				if (confirm('Are you sure you want to delete this field?')) {
					$(this).closest('.field-row').remove();
					reindexRows();
				}
			});

			function reindexRows() {
				$('#srkp-fields-rows .field-row').each(function(newIndex) {
					$(this).attr('data-index', newIndex);
					$(this).find('input, select').each(function() {
						var name = $(this).attr('name');
						if (name) {
							var newName = name.replace(/form_fields\[\d+\]/, 'form_fields[' + newIndex + ']');
							$(this).attr('name', newName);
						}
					});
				});
				rowIndex = $('#srkp-fields-rows .field-row').length;
			}
		});
		</script>
		<?php
	}

	/**
	 * Save the custom form fields metadata.
	 *
	 * @since    1.0.0
	 */
	public function save_form_fields( $post_id ) {
		if ( ! isset( $_POST['srkp_form_fields_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['srkp_form_fields_nonce'], 'srkp_form_fields_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['notification_emails'] ) ) {
			update_post_meta( $post_id, '_srkp_notification_emails', sanitize_text_field( $_POST['notification_emails'] ) );
		}
		
		if ( isset( $_POST['form_fields'] ) && is_array( $_POST['form_fields'] ) ) {
			$saved_fields = array();
			foreach ( $_POST['form_fields'] as $field ) {
				$saved_fields[] = array(
					'label'    => sanitize_text_field( $field['label'] ),
					'id'       => sanitize_title_with_dashes( $field['id'] ),
					'type'     => sanitize_text_field( $field['type'] ),
					'width'    => sanitize_text_field( $field['width'] ),
					'required' => isset( $field['required'] ) ? 'yes' : '',
					'options'  => isset( $field['options'] ) ? sanitize_text_field( $field['options'] ) : ''
				);
			}
			update_post_meta( $post_id, '_srkp_form_fields', $saved_fields );
		} else {
			update_post_meta( $post_id, '_srkp_form_fields', array() );
		}
	}

}
