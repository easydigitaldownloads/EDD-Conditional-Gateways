<?php
/**
 * Meta boxes
 *
 * @package     EDD\ConditionalGateways\MetaBoxes
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register new meta box
 *
 * @since       1.0.0
 * @return      void
 */
function edd_conditional_gateways_add_meta_box() {
	add_meta_box(
		'conditional-gateways',
		__( 'Conditional Gateways', 'edd-conditional-gateways' ),
		'edd_conditional_gateways_render_meta_box',
		'download',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'edd_conditional_gateways_add_meta_box' );


/**
 * Render the new meta box
 *
 * @since       1.0.0
 * @global      object $post The WordPress object for a given post
 * @return      void
 */
function edd_conditional_gateways_render_meta_box() {
	global $post;

	$allowed_gateways = get_post_meta( $post->ID, '_edd_conditional_gateways', true );
	$all_gateways     = edd_get_enabled_payment_gateways();

	$html = '<p><strong>' . __( 'Allowed Gateways:', 'edd-conditional-gateways' ) . '</strong></p>';

	foreach( $all_gateways as $key => $gateway ) {
		$html .= '<p>';
		$html .= '<input type="checkbox" name="_edd_conditional_gateways[' . $key . ']" id="_edd_conditional_gateways[' . $key . ']" value="1"' . ( is_array( $allowed_gateways ) && array_key_exists( $key, $allowed_gateways ) ? ' checked' : '' ) . ' /> ';
		$html .= '<label for="_edd_conditional_gateways[' . $key . ']">' . $gateway['admin_label'] . '</label>';
		$html .= '</p>';
	}

	$html .= '<p class="description">' . __( 'Uncheck all gateways to allow all.', 'edd-conditional-gateways' ) . '</p>';

	echo $html;

	// Allow extension of the meta box
	do_action( 'edd_conditional_gateways_meta_box_fields', $post->ID );

	wp_nonce_field( basename( __FILE__ ), 'edd_conditional_gateways_nonce' );
}


/**
 * Save post meta when the save_post action is called
 *
 * @since       1.0.0
 * @param       int $post_id The ID of the post we are saving
 * @global      object $post The WordPress object for this post
 * @return      void
 */
function edd_conditional_gateways_meta_box_save( $post_id ) {
	global $post;

	// Bail if nonce can't be validated
	if( ! isset( $_POST['edd_conditional_gateways_nonce'] ) || ! wp_verify_nonce( $_POST['edd_conditional_gateways_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// Bail if this is an autosave or bulk edit
	if( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $post_id;
	}

	// Bail if this is a revision
	if( isset( $post->post_type ) && $post->post_type == 'revision' ) {
		return $post_id;
	}

	// Bail if the current user shouldn't be here
	if( ! current_user_can( 'edit_product', $post_id ) ) {
		return $post_id;
	}

	// The default fields that get saved
	$fields = apply_filters( 'edd_conditional_gateways_meta_box_fields_save', array(
		'_edd_conditional_gateways'
	) );

	foreach( $fields as $field ) {
		if( isset( $_POST[$field] ) ) {
			$new = map_deep( wp_unslash( $_POST[$field] ), 'sanitize_text_field' );

			$new = apply_filters( 'edd_conditional_gateways_meta_box_save_' . $field, $new );

			update_post_meta( $post_id, $field, $new );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}
}
add_action( 'save_post', 'edd_conditional_gateways_meta_box_save' );
