<?php
/**
 * Register settings
 *
 * @package     EDD\ConditionalGateways\Admin\Settings\Register
 * @since       1.0.1
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add settings
 *
 * @since       1.0.1
 * @param       array $settings The current plugin settings
 * @return      array The modified plugin settings
 */
function edd_conditional_gateways_add_settings( $settings ) {
	$new_settings = array(
		array(
			'id'    => 'edd_conditional_gateways_settings',
			'name'  => '<strong>' . __( 'Conditional Gateways Settings', 'edd-conditional-gateways' ) . '</strong>',
			'desc'  => '',
			'type'  => 'header'
		),
		array(
			'id'    => 'edd_conditional_gateways_checkout_error',
			'name'  => __( 'Checkout Error Message', 'edd-conditional-gateways' ),
			'desc'  => __( 'The error message to display if no gateways are available due to the cart contents.', 'edd-conditional-gateways' ),
			'type'  => 'text',
			'std'   => __( 'Your cart contents have resulted in no gateways being available. Please remove an item from your cart and try again.', 'edd-conditional-gateways' )
		)
	);

	return array_merge( $settings, $new_settings );
}

add_filter( 'edd_settings_extensions', 'edd_conditional_gateways_add_settings' );