<?php
/**
 * Plugin Name:     Easy Digital Downloads - Conditional Gateways
 * Plugin URI:      https://easydigitaldownloads.com/downloads/conditional-gateways/
 * Description:     Allows you to configure supported gateways on a per-download basis
 * Version:         1.0.3
 * Author:          Sandhills Development, LLC
 * Author URI:      https://sandhillsdev.com
 * Text Domain:     edd-conditional-gateways
 *
 * @package         EDD\ConditionalGateways
 * @author          Sandhills Development, LLC
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
			define( 'EDD_CONDITIONAL_GATEWAYS_VER', '1.0.3' );

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
				require_once EDD_CONDITIONAL_GATEWAYS_DIR . 'includes/admin/settings/register.php';
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
			// Handle licensing.
			if ( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Conditional Gateways', EDD_CONDITIONAL_GATEWAYS_VER, 'Sandhills Development, LLC', null, null, 497453 );
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
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-conditional-gateways/' . $mofile;

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
			require_once 'includes/libraries/class.s214-edd-activation.php';
		}

		$activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		return EDD_Conditional_Gateways::instance();
	} else {
		return EDD_Conditional_Gateways::instance();
	}
}
add_action( 'plugins_loaded', 'edd_conditional_gateways' );
