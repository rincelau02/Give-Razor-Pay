<?php
/**
 * Plugin Name: Give - Razorpay
 * Plugin URI: https://github.com/WordImpress/Give-Razorpay
 * Description: Process online donations via the Razorpay payment gateway.
 * Author: WordImpress
 * Author URI: https://wordimpress.com
 * Version: 1.1.3
 * Text Domain: give-razorpay
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WordImpress/Give-Razorpay
 */


/**
 * Class Give_Razorpay_Gateway
 *
 * @since 1.0
 */
final class Give_Razorpay_Gateway {

	/**
	 * @since  1.0
	 * @access static
	 * @var Give_Razorpay_Gateway $instance
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * Give_Razorpay_Gateway constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance
	 *
	 * @since  1.0
	 * @access static
	 * @return Give_Razorpay_Gateway|static
	 */
	static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Setup constants.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_Razorpay_Gateway
	 */
	public function setup_constants() {

		// Global Params.
		define( 'GIVE_RAZORPAY_VERSION', '1.1.3' );
		define( 'GIVE_RAZORPAY_MIN_GIVE_VER', '2.0' );
		define( 'GIVE_RAZORPAY_FILE', __FILE__ );
		define( 'GIVE_RAZORPAY_BASENAME', plugin_basename( GIVE_RAZORPAY_FILE ) );
		define( 'GIVE_RAZORPAY_URL', plugins_url( '/', GIVE_RAZORPAY_FILE ) );
		define( 'GIVE_RAZORPAY_DIR', plugin_dir_path( GIVE_RAZORPAY_FILE ) );

		add_action( 'init', array( $this, 'load_textdomain' ) );

		return self::$instance;
	}

	/**
	 * Load files.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_Razorpay_Gateway
	 */
	public function load_files() {

		if ( file_exists( GIVE_RAZORPAY_DIR . 'includes/lib/razorpay-php/Razorpay.php' ) ) {
			require_once GIVE_RAZORPAY_DIR . 'includes/lib/razorpay-php/Razorpay.php';
		}

		// Load helper functions.
		require_once GIVE_RAZORPAY_DIR . 'includes/functions.php';

		// Load plugin settings.
		require_once GIVE_RAZORPAY_DIR . 'includes/admin/admin-settings.php';

		// Load frontend actions.
		require_once GIVE_RAZORPAY_DIR . 'includes/actions.php';

		// Process payment
		require_once GIVE_RAZORPAY_DIR . 'includes/process-payment.php';

		if ( is_admin() ) {
			// Load admin actions..
			require_once GIVE_RAZORPAY_DIR . 'includes/admin/actions.php';
		}

		return self::$instance;
	}


	/**
	 * Setup hooks.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_Razorpay_Gateway
	 */
	public function setup_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );

		return self::$instance;
	}


	/**
	 * Load frontend scripts
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_Razorpay_Gateway
	 */
	public function frontend_enqueue() {
		if ( give_razorpay_is_active() ) {
			wp_register_script( 'razorpay-js', 'https://checkout.razorpay.com/v1/checkout.js' );
			wp_enqueue_script( 'razorpay-js' );

			wp_register_script( 'give-razorpay-popup-js', GIVE_RAZORPAY_URL . 'assets/js/give-razorpay-popup.js', array( 'jquery' ), false, GIVE_RAZORPAY_VERSION );
			wp_enqueue_script( 'give-razorpay-popup-js' );

			$merchant = give_razorpay_get_merchant_credentials();
			$data     = array(
				'merchant_key_id' => $merchant['merchant_key_id'],
				'popup'           => array(
					'color' => give_get_option( 'razorpay_popup_theme_color' ),
					'image' => give_get_option( 'razorpay_popup_image' ),
				),
				'setup_order_url' => add_query_arg( array( 'give_action' => 'give_process_razorpay' ), home_url() ),
				'clear_order_url' => add_query_arg( array( 'give_action' => 'give_clear_order' ), home_url() ),
			);

			wp_localize_script( 'give-razorpay-popup-js', 'give_razorpay_vars', $data );
		}
	}


	/**
	 * Check if plugin dependencies satisfied or not
	 *
	 * @since  1.0
	 * @access public
	 * @return bool
	 */
	public function is_plugin_dependency_satisfied() {

		if ( ! defined( 'GIVE_VERSION' ) ) {
			return false;
		}

		return ( - 1 !== version_compare( GIVE_VERSION, GIVE_RAZORPAY_MIN_GIVE_VER ) );
	}


	/**
	 * Load the text domain.
	 *
	 * @access private
	 * @since  1.0
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$give_razorpay_lang_dir = dirname( plugin_basename( GIVE_RAZORPAY_FILE ) ) . '/languages/';
		$give_razorpay_lang_dir = apply_filters( 'give_razorpay_languages_directory', $give_razorpay_lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-razorpay' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-razorpay', $locale );

		// Setup paths to current locale file
		$mofile_local  = $give_razorpay_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-razorpay/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-razorpay folder
			load_textdomain( 'give-razorpay', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-razorpay/languages/ folder
			load_textdomain( 'give-razorpay', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'give-razorpay', false, $give_razorpay_lang_dir );
		}

	}
}

/**
 *  Initiate plugin.
 */
function give_razorpay_plugin_init() {
	// Get instance.
	$give_razorpay = Give_Razorpay_Gateway::get_instance();

	// Load constants.
	$give_razorpay->setup_constants();

	if ( is_admin() ) {
		// Process plugin activation.
		require_once GIVE_RAZORPAY_DIR . 'includes/admin/plugin-activation.php';
	}

	if (
		class_exists( 'Give' )
		&& $give_razorpay->is_plugin_dependency_satisfied()
	) {
		// Load addon files.
		$give_razorpay->load_files()->setup_hooks();

		// Add license.
		if ( class_exists( 'Give_License' ) ) {
			new Give_License( GIVE_RAZORPAY_FILE, 'Razorpay Gateway', GIVE_RAZORPAY_VERSION, 'WordImpress' );
		}
	}
}

add_action( 'plugins_loaded', 'give_razorpay_plugin_init' );
