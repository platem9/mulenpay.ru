<?php

namespace Mulen\MulenPay\Tests\Service;

use GuzzleHttp\Exception\GuzzleException;
use Mulen\MulenPay\Service\SubscribeService;
use Mulen\MulenPay\Tests\TestCase;

class SubscribeServiceTest extends TestCase
{
    /**
     * @throws GuzzleException
     */
    public function testList()
    {
        $expected = [['id' => 1], ['id' => 2]];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new SubscribeService($client);
        $response = $service->list();
        $this->assertEquals($expected, $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testCancel()
    {
        $expected = ['success' => true];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new SubscribeService($client);
        $response = $service->cancel(1);
        $this->assertEquals($expected, $response);
    }
}
