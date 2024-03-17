<?php
/**
 * Plugin Name: Shetab Card Field For WooCommerce
 * Description: This plugin adds a field for receiving Shetab card number in orders form in WooCommerce.
 * Plugin URI: https://dedidata.com
 * Author: DediData
 * Author URI: https://dedidata.com
 * Version: 2.1.3
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 7.0
 * License: GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woo-iran-shetab-card-field
 * 
 * @package Shetab_Card_Field
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( '\DediData\Plugin_Autoloader' ) ) {
	require 'includes/DediData/class-plugin-autoloader.php';
}
// Set name spaces we use in this plugin
new \DediData\Plugin_Autoloader( array( 'DediData', 'ShetabCardField' ) );
/**
 * The function SHETAB_CARD_FIELD returns an instance of the Shetab_Card_Field class.
 *
 * @return object an instance of the \ShetabCardField\SHETAB_CARD class.
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
function SHETAB_CARD_FIELD() { // phpcs:ignore Squiz.Functions.GlobalFunction.Found, WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return \ShetabCardField\Shetab_Card_Field::get_instance( __FILE__ );
}
SHETAB_CARD_FIELD();
