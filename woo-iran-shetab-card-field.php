<?php
/*
Plugin Name: افزودن فیلد دریافت شماره کارت شتاب شتاب برای ووکامرس
Plugin URI: https://parsmizban.com/shetab-card-number
Description: این پلاگین روش حمل و نقل پست سفارشی و پیشتاز ایران را محاسبه و به سیستم اضافه می کند.
Version: 1.0
Author: فرهاد سخایی
Author URI: https://parsmizban.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	/**
	* Add the field to the checkout
	*/
	add_action( 'woocommerce_after_order_notes', 'shetab_card_number_field' );
	 
	function shetab_card_number_field( $checkout ) {
	 
		echo '<div id="shetab_card_number_field"><h2>' . __('شماره کارت شتاب شما') . '</h2>';
	 
		woocommerce_form_field( 'shetab_card_number', array(
			'type'          => 'text',
			'class'         => array('shetab-card-number-class form-row-wide'),
			'label'         => __('لطفا شماره کارت شتاب خود را وارد نمائید تا در صورت عدم توانایی در ارسال محصول ، مبلغ پرداختی شما به کارت شما عودت داده شود'),
			'placeholder'   => __('شماره کارت'),
			'required'   => TRUE,
			'clear'   => TRUE,
			), $checkout->get_value( 'shetab_card_number' ));
		echo '<br />';
		woocommerce_form_field( 'shetab_card_number_person', array(
			'type'          => 'text',
			'class'         => array('shetab-card-number-person-class form-row-wide'),
			'label'         => __('نام صاحب حساب کارت شتاب'),
			'placeholder'   => __('نام صاحب حساب'),
			'required'   => TRUE,
			'clear'   => TRUE,
			), $checkout->get_value( 'shetab_card_number_person' ));
		echo '</div>';
	}

	/**
	* Process the checkout
	*/
	add_action('woocommerce_checkout_process', 'shetab_card_field_process');
	 
	function shetab_card_field_process() {
		// Check if set, if its not set add an error.
		if ( ! $_POST['shetab_card_number'] or strlen($_POST['shetab_card_number']) < 16 or ! is_numeric($_POST['shetab_card_number']) )
			wc_add_notice( __( 'لطفا شماره کارت شتابی خود را در 16 رقم بدون فاصله و خط تیره و فقط عددی وارد نمائید' ), 'error' );
		if ( ! $_POST['shetab_card_number_person'] or strlen($_POST['shetab_card_number_person']) < 3 )
			wc_add_notice( __( 'لطفا نام صاحب حساب را وارد نمائید تا مبلغ عودتی به اشتباه به حساب فرد دیگری واریز نشود!' ), 'error' );
	}

	/**
	* Update the order meta with field value
	*/
	add_action( 'woocommerce_checkout_update_order_meta', 'shetab_card_field_update_order_meta' );
	 
	function shetab_card_field_update_order_meta( $order_id ) {
		if ( ! empty( $_POST['shetab_card_number'] ) ) {
			update_post_meta( $order_id, 'شماره کارت شتاب', sanitize_text_field( $_POST['shetab_card_number'] ) );
		}
		if ( ! empty( $_POST['shetab_card_number_person'] ) ) {
			update_post_meta( $order_id, 'نام دارنده حساب', sanitize_text_field( $_POST['shetab_card_number_person'] ) );
		}
	}

	/**
	 * Display field value on the order edit page
	 */
	add_action( 'woocommerce_admin_order_data_after_billing_address', 'shetab_card_field_display_admin_order_meta', 10, 1 );

	function shetab_card_field_display_admin_order_meta($order){
		echo '<p><strong>'.__('شماره کارت شتاب').':</strong> ' . get_post_meta( $order->id, 'شماره کارت شتاب', true ) . '</p>';
		echo '<p><strong>'.__('نام دارنده حساب').':</strong> ' . get_post_meta( $order->id, 'نام دارنده حساب', true ) . '</p>';
	}
	
	/**
	* Add the field to order emails
	**/
	add_filter('woocommerce_email_order_meta_keys', 'shetab_card_field_order_meta_keys');
	function shetab_card_field_order_meta_keys( $keys ) {
		$keys['شماره کارت شتاب'] = 'شماره کارت شتاب';
		$keys['نام دارنده حساب'] = 'نام دارنده حساب';
		return $keys;
	}
}
