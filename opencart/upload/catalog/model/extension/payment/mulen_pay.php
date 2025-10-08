<?php
class ModelExtensionPaymentMulenPay extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/mulen_pay');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_mulen_pay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('payment_mulen_pay_total') > 0 && $this->config->get('payment_mulen_pay_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_mulen_pay_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'mulen_pay',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_mulen_pay_sort_order')
            );
        }

        return $method_data;
    }

    public function getPaymentData($order_info) {
        $this->load->model('checkout/order');

        $products = $this->cart->getProducts();

        $items = array();
        foreach ($products as $product) {
            $items[] = array(
                'description' => $product['name'],
                'quantity'    => $product['quantity'],
                'price'       => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'], false),
                'vat_code' => 0, // Assuming no VAT
                'payment_subject' => 1, // Assuming товар
                'payment_mode' => 4, // Assuming полный расчет
            );
        }

        $data = array(
            'currency'    => $order_info['currency_code'],
            'amount'      => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false),
            'uuid'        => $order_info['order_id'],
            'shopId'      => $this->config->get('payment_mulen_pay_shop_id'),
            'description' => 'Order ' . $order_info['order_id'],
            'items'       => $items,
        );

        $data['sign'] = sha1($data['currency'] . $data['amount'] . $data['shopId'] . $this->config->get('payment_mulen_pay_secret_key'));

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://mulenpay.ru/api/v2/payments');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->config->get('payment_mulen_pay_secret_key')
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }
}
