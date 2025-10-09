<?php

namespace Mulen\MulenPay\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mulen\MulenPay\MulenPayClient;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getMockedClient(array $responses): MulenPayClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client = $this->getMockBuilder(MulenPayClient::class)
            ->setConstructorArgs(['test_api_key'])
            ->onlyMethods(['getHttpClient'])
            ->getMock();

        $client->method('getHttpClient')->willReturn($httpClient);

        return $client;
    }

    protected function createMockResponse(array $data, int $status = 200): Response
    {
        return new Response($status, [], json_encode($data));
    }
}
