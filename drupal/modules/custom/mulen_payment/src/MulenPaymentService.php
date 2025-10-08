
<?php

namespace Drupal\mulen_payment;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;

class MulenPaymentService {

  protected $config;
  protected $httpClient;

  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client) {
    $this->config = $config_factory->get('mulen_payment.settings');
    $this->httpClient = $http_client;
  }

  public function createPayment(array $order_data) {
    $shop_id = $this->config->get('shop_id');
    $secret_key = $this->config->get('secret_key');

    $currency = 'rub'; // Or get from order data
    $amount = $order_data['amount'];
    $uuid = $order_data['uuid'];

    $sign = sha1($currency . $amount . $shop_id . $secret_key);

    $payment_request = [
      'currency' => $currency,
      'amount' => $amount,
      'shopId' => (int)$shop_id,
      'description' => $order_data['description'],
      'sign' => $sign,
      'uuid' => $uuid,
      'items' => $order_data['items'],
      // Add other optional parameters from api.yml as needed
    ];

    try {
      $response = $this->httpClient->post('https://mulenpay.ru/api/v2/payments', [
        'json' => $payment_request,
        'headers' => [
          'Authorization' => 'Bearer ' . $secret_key, // The API spec says secret key, but it might be a different API key.
          'Content-Type' => 'application/json',
        ],
      ]);

      return json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (\Exception $e) {
      \Drupal::logger('mulen_payment')->error($e->getMessage());
      return NULL;
    }
  }
}
