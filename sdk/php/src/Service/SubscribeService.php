<?php

namespace Mulen\MulenPay\Service;

use GuzzleHttp\Exception\GuzzleException;

class SubscribeService extends AbstractService
{
    /**
     * @return array
     * @throws GuzzleException
     */
    public function list(): array
    {
        return $this->request('get', '/v2/subscribes');
    }

    /**
     * @param int $id
     * @return array
     * @throws GuzzleException
     */
    public function cancel(int $id): array
    {
        return $this->request('delete', "/v2/subscribes/{$id}");
    }
}
