<?php

namespace Mulen\MulenPay\Service;

use GuzzleHttp\Exception\GuzzleException;

class PaymentService extends AbstractService
{
    /**
     * @param array $data
     * @return array
     * @throws GuzzleException
     */
    public function create(array $data): array
    {
        return $this->request('post', '/v2/payments', $data);
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function list(): array
    {
        return $this->request('get', '/v2/payments');
    }

    /**
     * @param int $id
     * @return array
     * @throws GuzzleException
     */
    public function retrieve(int $id): array
    {
        return $this->request('get', "/v2/payments/{$id}");
    }

    /**
     * @param int $id
     * @return array
     * @throws GuzzleException
     */
    public function hold(int $id): array
    {
        return $this->request('put', "/v2/payments/{$id}/hold");
    }

    /**
     * @param int $id
     * @return array
     * @throws GuzzleException
     */
    public function cancel(int $id): array
    {
        return $this->request('delete', "/v2/payments/{$id}/hold");
    }

    /**
     * @param int $id
     * @return array
     * @throws GuzzleException
     */
    public function refund(int $id): array
    {
        return $this->request('put', "/v2/payments/{$id}/refund");
    }

    /**
     * @param int $id
     * @return array
     * @throws GuzzleException
     */
    public function getReceipt(int $id): array
    {
        return $this->request('get', "/v2/payments/{$id}/receipt");
    }
}
