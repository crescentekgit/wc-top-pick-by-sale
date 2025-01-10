<?php
/**
 * Plugin Name: WC Top Pick By Sale
 * Description: Assign the top-selling products within a specified time frame to a chosen category. 
 * Author: Crescentek
 * Version: 1.0.0
 * Requires at least: 4.4
 * Tested up to: 6.7.1
 * WC requires at least: 3.0
 * WC tested up to: 9.5.2
 * Author URI: https://www.crescentek.com/
 * Text Domain: wc-top-pick-by-sale
 * Domain Path: /languages/
 * License: GPLv3 or later
 */

if ( ! class_exists( 'WC_Top_Pick_By_Sale_Dependencies' ) )
	require_once 'classes/class-wc-top-pick-by-sale-dependencies.php';

require_once 'includes/wc-top-pick-by-sale-core-functions.php';
require_once 'includes/wc-top-pick-by-sale-setting-functions.php';
require_once 'config.php';
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! WC_Top_Pick_By_Sale_Dependencies::woocommerce_plugin_active_check() ) {
  add_action( 'admin_notices', 'woocommerce_inactive_notice' );
}

/**
 * Declare support for 'High-Performance order storage (COT)' in WooCommerce
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
  add_action(
    'before_woocommerce_init',
    function () {
      if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( __FILE__ ), true );
      }
    }
  );
}

if ( ! class_exists( 'WC_Top_Pick_By_Sale' ) && WC_Top_Pick_By_Sale_Dependencies::woocommerce_plugin_active_check() ) {
    require_once('classes/class-wc-top-pick-by-sale.php');
    global $WC_Top_Pick_By_Sale;
    $WC_Top_Pick_By_Sale = new WC_Top_Pick_By_Sale( __FILE__ );
    $GLOBALS['WC_Top_Pick_By_Sale'] = $WC_Top_Pick_By_Sale;
    // Activation Hooks
    register_activation_hook( __FILE__, [ 'WC_Top_Pick_By_Sale', 'activate_wctpbs' ] );
    // Deactivation Hooks
    register_deactivation_hook( __FILE__, [ 'WC_Top_Pick_By_Sale', 'deactivate_wctpbs' ] );
}
