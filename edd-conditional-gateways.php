<?php
/**
 * Plugin Name:     Easy Digital Downloads - Conditional Gateways
 * Plugin URI:      https://easydigitaldownloads.com/extensions/conditional-gateways/
 * Description:     Allows you to configure supported gateways on a per-download basis
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     edd-conditional-gateways
 *
 * @package         EDD\ConditionalGateways
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


if( ! class_exists( 'EDD_Conditional_Gateways' ) ) {


    /**
     * Main EDD_Conditional_Gateways class
     *
     * @since       1.0.0
     */
    class EDD_Conditional_Gateways {


        /**
         * @var         EDD_Conditional_Gateways $instance The one true EDD_Conditional_Gateways
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true EDD_Conditional_Gateways
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new EDD_Conditional_Gateways();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_CONDITIONAL_GATEWAYS_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_CONDITIONAL_GATEWAYS_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_CONDITIONAL_GATEWAYS_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            require_once EDD_CONDITIONAL_GATEWAYS_DIR . 'includes/functions.php';

            if( is_admin() ) {
                require_once EDD_CONDITIONAL_GATEWAYS_DIR . 'includes/admin/meta-boxes.php';
            }
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            // Add extension settings
            add_filter( 'edd_settings_extensions', array( $this, 'add_settings' ) );

            // Filter gateways on checkout
            add_filter( 'edd_payment_gateways', array( $this, 'filter_gateways' ) );

            // Display error if no gateways are allowed
            add_action( 'init', array( $this, 'gateway_error' ) );

            // Handle licensing
            if( class_exists( 'EDD_License' ) ) {
                $license = new EDD_License( __FILE__, 'Conditional Gateways', EDD_CONDITIONAL_GATEWAYS_VER, 'Daniel J Griffiths' );
            }
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'edd_conditional_gateways_language_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-conditional-gateways', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-conditional-gateways/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-conditional-gateways/ folder
                load_textdomain( 'edd-conditional-gateways', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-conditional-gateways/ folder
                load_textdomain( 'edd-conditional-gateways', $mofile_local );
            } else {
                // Load the traditional language files
                load_plugin_textdomain( 'edd-conditional-gateways', false, $lang_dir );
            }
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The current plugin settings
         * @return      array The modified plugin settings
         */
        public function add_settings( $settings ) {
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


        /**
         * Filter payment gateways on checkout
         *
         * @access      public
         * @since       1.0.0
         * @param       array $gateways The available gateways
         * @return      array $gateways The allowed gateways
         */
        public function filter_gateways( $gateways ) {
            $allowed = $gateways;
            
            if( edd_is_checkout() ) {
                $cart_contents = edd_get_cart_contents();

                foreach( $gateways as $key => $gateway ) {
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

                $gateways = $allowed;
            }

            if( empty( $gateways ) ) {
                $message = edd_get_option( 'edd_conditional_gateways_checkout_error', __( 'Your cart contents have resulted in no gateways being available. Please remove an item from your cart and try again.', 'edd-conditional-gateways' ) );

                edd_set_error( 'no-allowed-gateways', $message );
            }

            return $gateways;
        }


        /**
         * Display an EDD checkout error if no gateways are available
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function gateway_error() {
        }
    }
}


/**
 * The main function responsible for returning the one true
 * EDD_Conditional_Gateways instance to functions everywhere
 *
 * @since       1.0.0
 * @return      EDD_Conditional_Gateways The one true EDD_Conditional_Gateways
 */
function edd_conditional_gateways() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'S214_EDD_Activation' ) ) {
            require_once 'includes/class.s214-edd-activation.php';
        }

        $activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();

        return EDD_Conditional_Gateways::instance();
    } else {
        return EDD_Conditional_Gateways::instance();
    }
}
add_action( 'plugins_loaded', 'edd_conditional_gateways' );
