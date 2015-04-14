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
    $limited    = false;
    $gateways   = get_post_meta( $download_id, '_edd_conditional_gateways', true );

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
    $allowed    = true;
    $gateways   = get_post_meta( $download_id, '_edd_conditional_gateways', true );

    if( is_array( $gateways ) && ! array_key_exists( $gateway, $gateways ) ) {
        $allowed = false;
    }

    return $allowed;
}
