<?php

namespace Mulen\MulenPay\Tests\Service;

use GuzzleHttp\Exception\GuzzleException;
use Mulen\MulenPay\Service\PaymentService;
use Mulen\MulenPay\Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    /**
     * @throws GuzzleException
     */
    public function testCreate()
    {
        $expected = ['id' => 1, 'status' => 'created'];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new PaymentService($client);
        $response = $service->create(['amount' => 100]);
        $this->assertEquals($expected, $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testList()
    {
        $expected = [['id' => 1], ['id' => 2]];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new PaymentService($client);
        $response = $service->list();
        $this->assertEquals($expected, $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testRetrieve()
    {
        $expected = ['id' => 1, 'status' => 'paid'];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new PaymentService($client);
        $response = $service->retrieve(1);
        $this->assertEquals($expected, $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testHold()
    {
        $expected = ['success' => true];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new PaymentService($client);
        $response = $service->hold(1);
        $this->assertEquals($expected, $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testCancel()
    {
        $expected = ['success' => true];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new PaymentService($client);
        $response = $service->cancel(1);
        $this->assertEquals($expected, $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testRefund()
    {
        $expected = ['success' => true];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new PaymentService($client);
        $response = $service->refund(1);
        $this->assertEquals($expected, $response);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetReceipt()
    {
        $expected = ['id' => 1, 'status' => 'created'];
        $client = $this->getMockedClient([$this->createMockResponse($expected)]);
        $service = new PaymentService($client);
        $response = $service->getReceipt(1);
        $this->assertEquals($expected, $response);
    }
}
