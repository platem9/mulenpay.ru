<?php
class MulenPayClient
{
    /** @var string */
    private $baseUrl;
    /** @var string */
    private $apiKey;
    /** @var int */
    private $shopId;
    /** @var string */
    private $secret;

    public function __construct($baseUrl, $apiKey, $shopId, $secret)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->shopId = (int)$shopId;
        $this->secret = $secret;
    }

    /**
     * Create payment and return array with keys: success, paymentUrl, id
     * @param array $payload
     * @return array [success => bool, paymentUrl => string|null, id => int|null, error => string|null]
     */
    public function createPayment(array $payload)
    {
        $url = $this->baseUrl.'/v2/payments';
        $payload['shopId'] = $this->shopId;
        // Sign: concatenation of currency, amount, shopId and secret, sha1
        $currency = isset($payload['currency']) ? $payload['currency'] : '';
        $amount = isset($payload['amount']) ? $payload['amount'] : '';
        $payload['sign'] = sha1($currency.$amount.$this->shopId.$this->secret);
        return $this->postJson($url, $payload);
    }

    private function postJson($url, array $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer '.$this->apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            return ['success' => false, 'paymentUrl' => null, 'id' => null, 'error' => 'cURL error: '.$error];
        }

        $json = json_decode($body, true);
        if ($status >= 200 && $status < 300 && is_array($json)) {
            // Some endpoints return success/paymentUrl/id directly per spec
            return [
                'success' => isset($json['success']) ? (bool)$json['success'] : true,
                'paymentUrl' => isset($json['paymentUrl']) ? $json['paymentUrl'] : null,
                'id' => isset($json['id']) ? $json['id'] : null,
                'raw' => $json,
            ];
        }

        return ['success' => false, 'paymentUrl' => null, 'id' => null, 'error' => 'HTTP '.$status.': '.$body];
    }
}
