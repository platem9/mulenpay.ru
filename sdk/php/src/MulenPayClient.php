<?php

namespace Mulen\MulenPay;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Mulen\MulenPay\Service\PaymentService;
use Mulen\MulenPay\Service\ShopService;
use Mulen\MulenPay\Service\SubscribeService;

class MulenPayClient
{
    private const API_BASE_URL = 'https://mulenpay.ru/api';

    private HttpClient $httpClient;

    public function __construct(string $apiKey, array $options = [])
    {
        $this->httpClient = new HttpClient(array_merge([
            'base_uri' => self::API_BASE_URL,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ], $options));
    }

    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    public function payments(): PaymentService
    {
        return new PaymentService($this);
    }

    public function subscribes(): SubscribeService
    {
        return new SubscribeService($this);
    }

    public function shops(): ShopService
    {
        return new ShopService($this);
    }
}
