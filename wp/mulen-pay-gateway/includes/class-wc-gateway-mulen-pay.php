<?php
/**
 * WC_Gateway_Mulen_Pay Class.
 *
 * @class    WC_Gateway_Mulen_Pay
 * @extends  WC_Payment_Gateway
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_Mulen_Pay extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'mulen_pay';
		$this->icon               = apply_filters( 'woocommerce_mulen_pay_icon', '' );
		$this->has_fields         = false;
		$this->method_title       = __( 'Mulen Pay', 'mulen-pay-gateway' );
		$this->method_description = __( 'Allows payments with Mulen Pay.', 'mulen-pay-gateway' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->shop_id      = $this->get_option( 'shop_id' );
		$this->secret_key   = $this->get_option( 'secret_key' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_mulen_pay_webhook', array( $this, 'webhook_handler' ) );
	}

	/**
	 * Initialize Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'mulen-pay-gateway' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Mulen Pay', 'mulen-pay-gateway' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'       => __( 'Title', 'mulen-pay-gateway' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'mulen-pay-gateway' ),
				'default'     => __( 'Mulen Pay', 'mulen-pay-gateway' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'mulen-pay-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'mulen-pay-gateway' ),
				'default'     => '',
			),
			'shop_id' => array(
				'title'       => __( 'Shop ID', 'mulen-pay-gateway' ),
				'type'        => 'text',
				'description' => __( 'Enter your Mulen Pay Shop ID.', 'mulen-pay-gateway' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'secret_key' => array(
				'title'       => __( 'Secret Key', 'mulen-pay-gateway' ),
				'type'        => 'password',
				'description' => __( 'Enter your Mulen Pay Secret Key.', 'mulen-pay-gateway' ),
				'default'     => '',
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$currency = $order->get_currency();
		$amount = $order->get_total();
		$shopId = $this->shop_id;
		$uuid = 'order_' . $order_id;
		$description = 'Order ' . $order_id;

		$items = array();
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			$items[] = array(
				'description' => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'price' => $product->get_price(),
				'vat_code' => 0,
				'payment_subject' => 1,
				'payment_mode' => 4,
			);
		}

		$sign = sha1( $currency . $amount . $shopId . $this->secret_key );

		$body = array(
			'currency' => $currency,
			'amount' => $amount,
			'shopId' => $shopId,
			'uuid' => $uuid,
			'description' => $description,
			'items' => $items,
			'sign' => $sign,
		);

		$response = wp_remote_post( 'https://mulenpay.ru/api/v2/payments', array(
			'method'    => 'POST',
			'headers'   => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->secret_key,
			),
			'body'      => json_encode( $body ),
			'timeout'   => 60,
			'sslverify' => false,
		) );

		if ( is_wp_error( $response ) ) {
			wc_get_logger()->error( 'Mulen Pay API connection error: ' . $response->get_error_message() );
			wc_add_notice( __( 'Payment error. Please try again later.', 'mulen-pay-gateway' ), 'error' );
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 201 ) {
			wc_get_logger()->error( 'Mulen Pay API error: ' . $response_body );
			wc_add_notice( __( 'Payment error. Please try again later.', 'mulen-pay-gateway' ), 'error' );
			return;
		}

		$body = json_decode( $response_body, true );

		if ( $body['success'] ) {
			return array(
				'result'   => 'success',
				'redirect' => $body['paymentUrl'],
			);
		} else {
			wc_get_logger()->error( 'Mulen Pay API error: ' . $body['error'] );
			wc_add_notice( __( 'Payment error. Please try again later.', 'mulen-pay-gateway' ), 'error' );
			return;
		}
	}

	/**
	 * Handle webhook notifications.
	 */
	public function webhook_handler() {
		$raw_post = file_get_contents( 'php://input' );
		$data = json_decode( $raw_post, true );
		$signature = $_SERVER['HTTP_X_SIGNATURE'];

		$expected_signature = sha1( $raw_post . $this->secret_key );

		if ( ! hash_equals( $expected_signature, $signature ) ) {
			status_header( 401 );
			exit;
		}

		$order_id = intval( str_replace( 'order_', '', $data['uuid'] ) );
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			status_header( 404 );
			exit;
		}

		if ( $data['payment_status'] === 'success' ) {
			$order->payment_complete();
		} else {
			$order->update_status( 'cancelled' );
		}

		status_header( 200 );
		exit;
	}
}
