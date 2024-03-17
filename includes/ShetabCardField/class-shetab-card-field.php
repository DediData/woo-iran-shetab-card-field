<?php
/**
 * Shetab Card Field Main Class
 * 
 * @package Shetab_Card_Field
 */

declare(strict_types=1);

namespace ShetabCardField;

use WP_Error;

/**
 * Class Shetab_Card_Field
 */
final class Shetab_Card_Field extends \DediData\Singleton {
	
	/**
	 * Plugin URL
	 * 
	 * @var string $plugin_url
	 */
	private $plugin_url;

	/**
	 * Plugin Folder
	 * 
	 * @var string $plugin_folder
	 */
	private $plugin_folder;

	/**
	 * Plugin Name
	 * 
	 * @var string $plugin_name
	 */
	private $plugin_name;

	/**
	 * Plugin Version
	 * 
	 * @var string $plugin_version
	 */
	private $plugin_version;
	
	/**
	 * Plugin Slug
	 * 
	 * @var string $plugin_slug
	 */
	private $plugin_slug;

	/**
	 * Plugin File
	 * 
	 * @var string $plugin_file
	 */
	private $plugin_file;

	/**
	 * Constructor
	 * 
	 * @param mixed $plugin_file Plugin File Name.
	 * @see https://developer.wordpress.org/reference/functions/register_activation_hook
	 * @see https://developer.wordpress.org/reference/functions/register_deactivation_hook
	 * @see https://developer.wordpress.org/reference/functions/register_uninstall_hook
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	protected function __construct( $plugin_file = null ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once \ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return;
		}
		$this->plugin_file = $plugin_file;
		$this->set_plugin_info();
		register_activation_hook( $plugin_file, array( $this, 'activate' ) );
		register_deactivation_hook( $plugin_file, array( $this, 'deactivate' ) );
		register_uninstall_hook( $plugin_file, self::class . '::uninstall' );
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ), 11 );
			$this->admin();
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ), 11 );
			$this->run();
		}
		add_action( 'woocommerce_after_order_notes', array( $this, 'field' ), 10, 1 );
		add_action( 'woocommerce_checkout_process', array( $this, 'process' ), 10, 1 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'Update_order_meta' ), 10, 1 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_admin_order_meta' ), 10, 1 );
		add_filter( 'woocommerce_email_order_meta_keys', array( $this, 'order_meta_keys' ) );
	}

	/**
	 * The function is used to load frontend scripts and styles in a WordPress plugin, with support for
	 * RTL (right-to-left) languages.
	 * 
	 * @return void
	 */
	public function load_frontend_scripts() {
		/*
		if ( is_rtl() ) {
			wp_register_style( $this->plugin_slug . '-rtl', $this->plugin_url . '/assets/public/css/style.rtl.css', array(), $this->plugin_version );
			wp_enqueue_style( $this->plugin_slug . '-rtl' );
		} else {
			wp_register_style( $this->plugin_slug, $this->plugin_url . '/assets/public/css/style.css', array(), $this->plugin_version );
			wp_enqueue_style( $this->plugin_slug );
		}

		wp_register_script( $this->plugin_slug, $this->plugin_url . '/assets/public/js/script.js', array(), $this->plugin_version, true );
		wp_enqueue_script( $this->plugin_slug );
		*/    
	}

	/**
	 * Styles for Admin
	 * 
	 * @return void
	 */
	public function load_admin_scripts() {
		/*
		if ( is_rtl() ) {
			wp_register_style( $this->plugin_slug . '-rtl', $this->plugin_url . '/assets/admin/css/style.rtl.css', array(), $this->plugin_version );
			wp_enqueue_style( $this->plugin_slug . '-rtl' );
		} else {
			wp_register_style( $this->plugin_slug, $this->plugin_url . '/assets/admin/css/style.css', array(), $this->plugin_version );
			wp_enqueue_style( $this->plugin_slug );
		}

		wp_register_script( $this->plugin_slug, $this->plugin_url . '/assets/admin/js/script.js', array(), $this->plugin_version, true );
		wp_enqueue_script( $this->plugin_slug );
		*/
	}

	/**
	 * Activate the plugin
	 * 
	 * @return void
	 * @see https://developer.wordpress.org/reference/functions/add_option
	 */
	public function activate() {
		// add_option( $this->plugin_slug );
	}

	/**
	 * Run when plugins deactivated
	 * 
	 * @return void
	 */
	public function deactivate() {
		// Clear any temporary data stored by plugin.
		// Flush Cache/Temp.
		// Flush Permalinks.
	}

	/**
	 * The function "Field" in PHP displays two input fields for entering Shetab card number and card
	 * owner's name in a checkout form.
	 * 
	 * @param object $checkout The  parameter is an instance of the WooCommerce checkout class. It is used
	 *                         to retrieve the values of the form fields and set their default values if they exist.
	 * @return mixed
	 */
	public function field( $checkout ) {
		echo '<div id="shetab_card_number_field"><h2>' . esc_html__( 'Your Shetab card number', 'woo-iran-shetab-card-field' ) . '</h2>';
		woocommerce_form_field(
			'shetab_card_number',
			array(
				'type'        => 'text',
				'class'       => array( 'shetab-card-field-class form-row-wide' ),
				'label'       => esc_html__( 'Please enter your Shetab card number so that in case of inability to deliver the product, the paid amount will be refunded to your card.', 'woo-iran-shetab-card-field' ),
				'placeholder' => esc_html__( 'Card Number', 'woo-iran-shetab-card-field' ),
				'required'    => true,
				'clear'       => true,
			),
			$checkout->get_value( 'shetab_card_number' )
		);
		echo '<br />';
		woocommerce_form_field(
			'shetab_card_number_person',
			array(
				'type'        => 'text',
				'class'       => array( 'shetab-card-field-person-class form-row-wide' ),
				'label'       => esc_html__( 'Name of the Shetab card account holder', 'woo-iran-shetab-card-field' ),
				'placeholder' => esc_html__( 'Account Holder Name', 'woo-iran-shetab-card-field' ),
				'required'    => true,
				'clear'       => true,
			),
			$checkout->get_value( 'shetab_card_number_person' )
		);
		echo '</div>';
		wp_nonce_field( 'shetab_card_info', 'shetab_card_info_nonce' );
	}

	/**
	 * The function checks if the Shetab card number and the name of the account holder are provided
	 * correctly and displays an error message if they are not.
	 * 
	 * @return void|\WP_Error
	 */
	public function process() {
		$nonce = filter_input( \INPUT_POST, 'shetab_card_info_nonce', \FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! wp_verify_nonce( $nonce, 'shetab_card_info' ) ) {
			return new WP_Error( 'Nonce verification failed' );
		}

		$card_number = filter_input( \INPUT_POST, 'shetab_card_number', \FILTER_SANITIZE_NUMBER_INT );
		$card_number = absint( $card_number );
		$card_number = strval( $card_number );
		if ( '' === $card_number || strlen( $card_number ) < 16 ) {
			wc_add_notice( esc_html__( 'Please enter your Shetab card number in 16 digits without spaces, hyphens, and only numerals', 'woo-iran-shetab-card-field' ), 'error' );
		}
		
		$card_name = filter_input( \INPUT_POST, 'shetab_card_number_person', \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		// phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
		if ( '' === $card_name || strlen( $card_name ) < 3 ) {
			wc_add_notice( esc_html__( "Please enter the account holder's name to ensure that the refund amount is not mistakenly deposited into another person's account!", 'woo-iran-shetab-card-field' ), 'error' );
		}
	}

	/**
	 * Update the order meta with field value
	 * 
	 * @param integer $order_id Order ID.
	 * @return void|\WP_Error
	 */
	public function update_order_meta( int $order_id ) {
		$nonce = filter_input( \INPUT_POST, 'shetab_card_info_nonce', \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( ! wp_verify_nonce( $nonce, 'shetab_card_info' ) ) {
			return new WP_Error( 'Nonce verification failed' ); 
		}
		$shetab_card_number = filter_input( \INPUT_POST, 'shetab_card_number', \FILTER_SANITIZE_NUMBER_INT );
		if ( '' !== $shetab_card_number ) {
			update_post_meta( $order_id, esc_html__( 'Shetab Card Number', 'woo-iran-shetab-card-field' ), sanitize_text_field( $shetab_card_number ) );
		}
		$shetab_card_name = filter_input( \INPUT_POST, 'shetab_card_number_person', \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		// phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
		if ( '' !== $shetab_card_name ) {
			update_post_meta( $order_id, esc_html__( 'Account Holder Name', 'woo-iran-shetab-card-field' ), sanitize_text_field( $shetab_card_name ) );
		}
	}

	/**
	 * Display field value on the order edit page
	 * 
	 * @param object $order Order Object.
	 * @return mixed
	 */
	public function display_admin_order_meta( $order ) {
		echo '<p><strong>' . esc_html__( 'Shetab Card Number', 'woo-iran-shetab-card-field' ) . ':</strong> '
			. esc_html( get_post_meta( $order->get_id(), esc_html__( 'Shetab Card Number', 'woo-iran-shetab-card-field' ), true ) . '</p>' );
		echo '<p><strong>' . esc_html__( 'Account Holder Name', 'woo-iran-shetab-card-field' ) . ':</strong> '
			. esc_html( get_post_meta( $order->get_id(), esc_html__( 'Account Holder Name', 'woo-iran-shetab-card-field' ), true ) . '</p>' );
	}
		
	/**
	 * Add the field to order emails
	 * 
	 * @param array<string> $keys Order Meta Keys.
	 * @return array<string>
	 **/
	public function order_meta_keys( $keys ) {
		$keys[ esc_html__( 'Shetab Card Number', 'woo-iran-shetab-card-field' ) ]  = esc_html__( 'Shetab Card Number', 'woo-iran-shetab-card-field' );
		$keys[ esc_html__( 'Account Holder Name', 'woo-iran-shetab-card-field' ) ] = esc_html__( 'Account Holder Name', 'woo-iran-shetab-card-field' );
		return $keys;
	}

	/**
	 * Uninstall plugin
	 * 
	 * @return void
	 * @see https://developer.wordpress.org/reference/functions/delete_option
	 */
	public static function uninstall() {
		// delete_option( 'woo-iran-shetab-card-field' );
		// Remove Tables from wpdb
		// global $wpdb;
		// $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}woo-iran-shetab-card-field");
		// Clear any cached data that has been removed.
		wp_cache_flush();
	}

	/**
	 * Set Plugin Info
	 * 
	 * @return void
	 */
	private function set_plugin_info() {
		$this->plugin_slug = basename( $this->plugin_file, '.php' );
		$this->plugin_url  = plugins_url( '', $this->plugin_file );

		if ( ! function_exists( 'get_plugins' ) ) {
			include_once \ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->plugin_folder  = plugin_dir_path( $this->plugin_file );
		$plugin_info          = get_plugins( '/' . plugin_basename( $this->plugin_folder ) );
		$plugin_file_name     = basename( $this->plugin_file );
		$this->plugin_version = $plugin_info[ $plugin_file_name ]['Version'];
		$this->plugin_name    = $plugin_info[ $plugin_file_name ]['Name'];
	}

	/**
	 * The function "run" is a placeholder function in PHP with no code inside.
	 * 
	 * @return void
	 */
	private function run() {
		// nothing for now
	}

	/**
	 * The admin function includes the options.php file and registers the admin menu.
	 * 
	 * @return void
	 */
	private function admin() {
		// add_action( 'admin_menu', 'ShetabCardField\Admin_Menus::register_admin_menu' );
	}
}
