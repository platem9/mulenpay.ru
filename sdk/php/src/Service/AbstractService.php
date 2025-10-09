<?php

namespace Mulen\MulenPay\Service;

use GuzzleHttp\Exception\GuzzleException;
use Mulen\MulenPay\MulenPayClient;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractService
{
    protected MulenPayClient $client;
    protected string $servicePath;

    public function __construct(MulenPayClient $client)
    {
        $this->client = $client;
    }

    protected function get(string $path, array $params = []): ResponseInterface
    {
        return $this->client->getHttpClient()->get($path, ['query' => $params]);
    }

    protected function post(string $path, array $params = []): ResponseInterface
    {
        return $this->client->getHttpClient()->post($path, ['json' => $params]);
    }

    protected function put(string $path, array $params = []): ResponseInterface
    {
        return $this->client->getHttpClient()->put($path, ['json' => $params]);
    }

    protected function delete(string $path): ResponseInterface
    {
        return $this->client->getHttpClient()->delete($path);
    }

    /**
     * @throws GuzzleException
     */
    protected function request(string $method, string $path = '', array $params = []): array
    {
        $response = $this->{$method}($path, $params);
        return json_decode($response->getBody()->getContents(), true);
    }
}
