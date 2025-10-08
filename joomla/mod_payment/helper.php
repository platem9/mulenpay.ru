<?php
defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;
use Joomla\Registry\Registry;

class ModMulenPaymentHelper
{
    public static function getPaymentLink($params, $order_details)
    {
        $shop_id = $params->get('shop_id');
        $secret_key = $params->get('secret_key');
        $api_key = $params->get('api_key');

        $currency = 'rub';
        $amount = $order_details['amount'];
        $uuid = $order_details['uuid'];
        $description = $order_details['description'];
        $items = $order_details['items'];

        // Correct sign calculation
        $sign = sha1($currency . $amount . $shop_id . $secret_key);

        $data = [
            'currency' => $currency,
            'amount' => $amount,
            'shopId' => (int)$shop_id,
            'description' => $description,
            'sign' => $sign,
            'uuid' => $uuid,
            'items' => $items,
        ];

        try {
            $http = HttpFactory::getHttp();
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ];

            $response = $http->post('https://mulenpay.ru/api/v2/payments', json_encode($data), $headers);

            if ($response->code !== 201) {
                // Log error
                JLog::add('Mulen Pay API error: ' . $response->body, JLog::ERROR, 'mod_mulen_payment');
                return null;
            }

            $result = json_decode($response->body);

            if ($result && $result->success) {
                return $result->paymentUrl;
            }
        } catch (Exception $e) {
            JLog::add('Mulen Pay request failed: ' . $e->getMessage(), JLog::ERROR, 'mod_mulen_payment');
            return null;
        }

        return null;
    }
}
