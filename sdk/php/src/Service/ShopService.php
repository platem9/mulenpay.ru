<?php

namespace Mulen\MulenPay\Service;

use GuzzleHttp\Exception\GuzzleException;

class ShopService extends AbstractService
{
    /**
     * @param int $shopId
     * @return array
     * @throws GuzzleException
     */
    public function getBalances(int $shopId): array
    {
        return $this->request('get', "/v2/shops/{$shopId}/balances");
    }
}
