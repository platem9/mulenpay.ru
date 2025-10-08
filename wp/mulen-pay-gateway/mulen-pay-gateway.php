<?php
/**
 * Plugin Name: Mulen Pay Gateway
 * Plugin URI: https://mulenpay.ru/
 * Description: A payment gateway for WooCommerce that integrates with Mulen Pay.
 * Version: 1.0.0
 * Author: Mulen
 * Author URI: https://mulen.ru/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 3.0.0
 * WC tested up to: 8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'init_mulen_pay_gateway' );

function init_mulen_pay_gateway() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-gateway-mulen-pay.php';

    add_filter( 'woocommerce_payment_gateways', 'add_mulen_pay_gateway' );
}

function add_mulen_pay_gateway( $gateways ) {
    $gateways[] = 'WC_Gateway_Mulen_Pay';
    return $gateways;
}
