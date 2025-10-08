<?php
class ControllerExtensionPaymentMulenPay extends Controller {
    public function index() {
        $this->load->language('extension/payment/mulen_pay');

        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['text_loading'] = $this->language->get('text_loading');

        $data['continue'] = $this->url->link('checkout/success');

        return $this->load->view('extension/payment/mulen_pay', $data);
    }

    public function confirm() {
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $this->load->model('extension/payment/mulen_pay');

        $payment_data = $this->model_extension_payment_mulen_pay->getPaymentData($order_info);

        $json['redirect'] = $payment_data['paymentUrl'];

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function callback() {
        $this->load->model('checkout/order');

        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['uuid']) && isset($input['payment_status'])) {
            $order_id = (int)$input['uuid'];
            $order_info = $this->model_checkout_order->getOrder($order_id);

            if ($order_info) {
                if ($input['payment_status'] == 'success') {
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_mulen_pay_order_status_id'));
                } else {
                    $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['success' => true]));
    }
}
