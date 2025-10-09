<?php

namespace Mulen\MulenPay\Tests\Service;

use GuzzleHttp\Exception\GuzzleException;
use Mulen\MulenPay\Service\ShopService;
use Mulen\MulenPay\Tests\TestCase;

class ShopServiceTest extends TestCase
{
    /**
     * @throws GuzzleException
     */
    public function testGetBalances()
    {
        $expected = ['balance' => 1000];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new ShopService($client);
        $response = $service->getBalances(1);
        $this->assertEquals($expected, $response);
    }
}
