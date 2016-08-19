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
 * Add settings section
 *
 * @since       1.0.2
 * @param       array $sections The existing extensions sections
 * @return      array The modified extensions settings
 */
function edd_conditional_gateways_add_settings_section( $sections ) {
	$sections['conditional-gateways'] = __( 'Conditional Gateways', 'edd-conditional-gateways' );

	return $sections;
}
add_filter( 'edd_settings_sections_gateways', 'edd_conditional_gateways_add_settings_section' );


/**
 * Add settings
 *
 * @since       1.0.1
 * @param       array $settings The current plugin settings
 * @return      array The modified plugin settings
 */
function edd_conditional_gateways_add_settings( $settings ) {
	$new_settings = array(
		'conditional-gateways' => apply_filters( 'edd_conditional_gateways_settings', array(
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
		) )
	);

	return array_merge( $settings, $new_settings );
}

add_filter( 'edd_settings_gateways', 'edd_conditional_gateways_add_settings' );
