<?php
class Cloudinary_Smartcrops {

	private static $instance;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( is_admin() ) {
			// Add a new field to the edit attachment screen
			add_filter( 'attachment_fields_to_edit', array( $this, 'modify_attachment_fields' ), 51, 2 );
		}
	}

	/**
	 * Modify attachment fields.
	 *
	 * @since 2018.1.0
	 * @filter attachment_fields_to_edit
	 *
	 * @param $form_fields array Existing fields.
	 * @param $attachment object The attachment currently being edited.
	 * @return array Form fields, either unmodified on error or new field added on success.
	 */
	public function modify_attachment_fields( $form_fields, $attachment ) {

		if ( ! wp_attachment_is_image( $attachment->ID ) ) {
			return $form_fields;
		}

		$form_fields['smart_crops'] = array(
			'label' => __( 'Smart Crops', 'smartcrops' ),
			'input' => 'html',
			'html'  => $this->get_attachment_field_html( $attachment ),
		);

		return $form_fields;
	}

	/**
	 * Generate the HTML for the edit attachment field.
	 *
	 * @param $attachment object The attachment currently being edited.
	 * @return string The HTML for the form field.
	 */
	public function get_attachment_field_html( $attachment ) {
		global $_wp_additional_image_sizes;
		$sizes    = $_wp_additional_image_sizes;
		$image    = wp_get_attachment_image_src( $attachment->ID, 'full' );
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( empty( $image ) ) {
			return '<p>' . __( 'No attachment image was found.' ) . '</p>';
		}

		// List image properties.
		list( $src, $width, $height ) = $image;

		// Get the image metadata.
		$metadata = wp_get_attachment_metadata( $attachment->ID );

		$crop_parameters = '';
		if ( isset( $metadata['focal_point_offset_left'] ) && isset( $metadata['focal_point_offset_top'] ) ) {
			$offset_x        = round( $metadata['focal_point_offset_left'] / $width * 100 );
			$offset_y        = round( $metadata['focal_point_offset_top'] / $height * 100 );
			$crop_parameters = sprintf( ',offset-x%d,offset-y%d', $offset_x, $offset_y );
		} elseif ( isset( $metadata['enable_smart_crop'] ) && 1 === $metadata['enable_smart_crop'] ) {
			$crop_parameters = ',smart';
		}
		$smart_crops_state = ( '' !== $crop_parameters ) ? 'has-cropping-parameters' : '';

		// Set the URL structure.
		$src = str_replace( ' ', '%20', $src );

		// Generate the HTML output.
		$html  = '<p class="hide-if-js">' . __( 'You need to enable Javascript to use this functionality.' ) . '</p>';
		$html .= '<input type="button" class="hide-if-no-js button" data-show-thumbnails value="' . __( 'Open Cropping Tools' ) . '" />';
		$html .= '<input type="button" class="hide-if-no-js button hidden" data-use-smart-crop value="' . __( 'Use Smart Crop' ) . '" />';
		$html .= '<input type="button" class="hide-if-no-js button hidden" data-select-focal-point value="' . __( 'Select Focal Point' ) . '" />';
		$html .= '<input type="button" class="hide-if-no-js button hidden" data-reset-smart-crops value="' . __( 'Reset Cropping' ) . '" />';

		$html .= sprintf(
			'</table><ul id="%s" class="smart-crop-thumbs-container hidden %s" data-attachment-id="%d" data-attachment-src="%s">',
			esc_attr( 'wpcom-thumbs-' . $attachment->ID ),
			esc_attr( $smart_crops_state ),
			esc_attr( $attachment->ID ),
			esc_url( $src )
		); // TODO: Fix `</table>`

		foreach ( $sizes as $image_name => $size ) {
			$thumbnail_url = sprintf( '%1$s?width=%2$d&crop=%2$d:%3$d%4$s', $src, $size['width'], $size['height'], $crop_parameters );

			$html .= '<li>';
			$html .= '<strong>' . esc_html( $image_name ) . '</strong><br />';
			$html .= '<img src="' . esc_url( $thumbnail_url ) . '" alt="' . esc_attr( $image_name ) . '" data-cloudinary="' . $metadata['cloudinary_data']['public_id'] . '" />';
			$html .= '</li>';
		}
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Toggle smart crop feature.
	 *
	 * @since 2018.1.0
	 * @action wp_ajax_toggle_smart_crop
	 *
	 * @return void Echo return value.
	 */
	public function toggle_smart_crop() {
		$this->validate();

		// Get the attachment ID and validate the smart crop.
		$id    = $this->get_attachment_id();
		$state = $this->validate_ajax_smart_crop_state();

		// Update attachment metadata.
		$metadata = wp_get_attachment_metadata( $id );
		$metadata['cloudinary_data']['enable_smart_crop'] = $state;

		print( esc_html( 'Smart crop state: ' . $state ) );

		unset( $metadata['cloudinary_data']['focal_point_offset_left'] );
		unset( $metadata['cloudinary_data']['focal_point_offset_top'] );

		$return_value = wp_update_attachment_metadata( $id, $metadata );

		// Send the result of action back to the view.
		wp_die( esc_html( strval( $return_value ) ) );
	}

	/**
	 * Validate smart crop state AJAX parameter.
	 *
	 * Makes sure "smart_crop_state" post parameter is valid and dies if it's not.
	 * Returns the parameter value on success.
	 *
	 * @return null|int Dies on error, returns parameter value on success.
	 */
	public function validate_ajax_smart_crop_state() {
		$state = filter_input( INPUT_POST, 'smart_crop_state', FILTER_VALIDATE_BOOLEAN );

		if ( empty( $state ) ) {
			wp_die( esc_html__( 'Smart crop state parameter is missing.' ) );
		}

		return $state;
	}
}
