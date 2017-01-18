<?php
/**
 * Helper functions
 *
 * @package     EDD\ConditionalGateways\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Check if a download is gateway limited
 *
 * @since       1.0.0
 * @param       int $download_id The ID of a given download
 * @return      bool $limited True if download is limited, false otherwise
 */
function edd_conditional_gateways_is_limited( $download_id ) {
	$limited  = false;
	$gateways = get_post_meta( $download_id, '_edd_conditional_gateways', true );

	if( is_array( $gateways ) ) {
		$limited = true;
	}

	return $limited;
}


/**
 * Check if a gateway is allowed for a given download
 *
 * @since       1.0.0
 * @param       int $download_id The ID of a given download
 * @param       string $gateway The gateway to check
 * @return      bool $allowed True if allowed, false otherwise
 */
function edd_conditional_gateways_is_allowed( $download_id, $gateway ) {
	$allowed  = true;
	$gateways = get_post_meta( $download_id, '_edd_conditional_gateways', true );

	if( is_array( $gateways ) && ! array_key_exists( $gateway, $gateways ) ) {
		$allowed = false;
	}

	return $allowed;
}


/**
 * Filter payment gateways on checkout
 *
 * @access      public
 * @since       1.0.0
 * @param       array $gateways The available gateways
 * @return      array $gateways The allowed gateways
 */
function edd_conditional_gateways_filter_gateways( $gateways ) {
	if ( ! is_admin() ) {
		$cart_contents = edd_get_cart_contents();

		// Support wallet!
		if( class_exists( 'EDD_Wallet' ) && is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$value   = edd_wallet()->wallet->balance( $user_id );
			$total   = edd_get_cart_total();
			$fee     = EDD()->fees->get_fee( 'edd-wallet-deposit' );

			if( (float) $value >= (float) $total && ! $fee ) {
				$gateways['wallet'] = array(
					'admin_label'    => 'Wallet',
					'checkout_label' => edd_get_option( 'edd_wallet_gateway_label', __( 'My Wallet', 'edd-wallet' ) )
				);
			}
		}

		$allowed = $gateways;

		foreach( $gateways as $key => $gateway ) {
			if( is_array( $cart_contents ) ) {
				foreach( $cart_contents as $item ) {
					if( array_key_exists( $key, $allowed ) ) {
						if( edd_conditional_gateways_is_limited( $item['id'] ) ) {
							if( ! edd_conditional_gateways_is_allowed( $item['id'], $key ) ) {
								unset( $allowed[$key] );
							}
						}
					}
				}
			}
		}

		$gateways = $allowed;

		if( empty( $gateways ) ) {
			$message = edd_get_option( 'edd_conditional_gateways_checkout_error', __( 'Your cart contents have resulted in no gateways being available. Please remove an item from your cart and try again.', 'edd-conditional-gateways' ) );

			edd_set_error( 'no-allowed-gateways', $message );
		}
	}

	return $gateways;
}
add_filter( 'edd_enabled_payment_gateways', 'edd_conditional_gateways_filter_gateways' );
