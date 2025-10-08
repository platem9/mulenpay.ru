<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem\Service;
use Bitrix\Sale\PaySystem\ServiceResult;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\IRefund;
use Bitrix\Sale\PaySystem\IHold;

Loc::loadMessages(__FILE__);

class MulenPayHandler extends ServiceHandler implements IRefund, IHold
{
    public function initiatePay(Payment $payment, Request $request = null): ServiceResult
    {
        $shopId = $this->getBusinessValue($payment, 'MULEN_PAY_SHOP_ID');
        $secretKey = $this->getBusinessValue($payment, 'MULEN_PAY_SECRET_KEY');
        $currency = $this->getBusinessValue($payment, 'MULEN_PAY_CURRENCY');
        $amount = $payment->getSum();
        $uuid = $payment->getId();
        $description = 'Оплата заказа №' . $payment->getOrderId();

        $sign = sha1($currency . $amount . $shopId . $secretKey);

        $items = [];
        foreach ($payment->getShipmentCollection() as $shipment) {
            foreach ($shipment->getShipmentItemCollection() as $shipmentItem) {
                $basketItem = $shipmentItem->getBasketItem();
                $items[] = [
                    'description' => $basketItem->getField('NAME'),
                    'quantity' => $basketItem->getQuantity(),
                    'price' => $basketItem->getPrice(),
                    'vat_code' => 0,
                    'payment_subject' => 1,
                    'payment_mode' => 4,
                ];
            }
        }

        $data = [
            'currency' => $currency,
            'amount' => $amount,
            'uuid' => $uuid,
            'shopId' => $shopId,
            'description' => $description,
            'items' => $items,
            'sign' => $sign,
        ];

        $url = 'https://mulenpay.ru/api/v2/payments';

        $result = new ServiceResult();

        try {
            $response = $this->sendRequest($url, $data);
            $result->setPsData($response);

            if (isset($response['paymentUrl'])) {
                $this->setExtraParams(['paymentUrl' => $response['paymentUrl']]);
                return $this->showTemplate($payment, 'template');
            } else {
                $result->addError(new \Bitrix\Main\Error(Loc::getMessage('MULEN_PAY_ERROR_CREATE_PAYMENT')));
            }
        } catch (\Exception $e) {
            $result->addError(new \Bitrix\Main\Error($e->getMessage()));
        }

        return $result;
    }

    public function getCurrencyList()
    {
        return ['rub'];
    }

    public function getPublicUserFields(Payment $payment): array
    {
        return [];
    }

    public function processRequest(Payment $payment, Request $request)
    {
        $result = new ServiceResult();

        $inputStream = self::readFromStream();
        $data = json_decode($inputStream, true);

        if ($data === null) {
            $result->addError(new \Bitrix\Main\Error('Invalid JSON'));
            return $result;
        }

        if ($data['payment_status'] === 'success') {
            $result->setOperationType(ServiceResult::MONEY_COMING);
            $result->setPsData($data);
            $result->setTransactionDate(new DateTime());
            $result->setTransactionId($data['id']);
            $result->setShouldPay(true);
        } else {
            $result->addError(new \Bitrix\Main\Error('Payment canceled'));
        }

        return $result;
    }

    public function getPaymentIdFromRequest(Request $request)
    {
        $inputStream = self::readFromStream();
        $data = json_decode($inputStream, true);

        return $data['uuid'] ?? null;
    }

    private static function readFromStream()
    {
        return file_get_contents('php://input');
    }

    private function sendRequest($url, $data)
    {
        $httpClient = new \Bitrix\Main\Web\HttpClient();
        $httpClient->setHeader('Content-Type', 'application/json', true);
        $httpClient->setHeader('Authorization', 'Bearer ' . $this->getBusinessValue(null, 'MULEN_PAY_SECRET_KEY'));

        $response = $httpClient->post($url, json_encode($data));

        if (!$response) {
            throw new \Exception(Loc::getMessage('MULEN_PAY_ERROR_CONNECT'));
        }

        $response = json_decode($response, true);

        if (isset($response['error'])) {
            throw new \Exception($response['error']);
        }

        return $response;
    }

    public function hold(Payment $payment, $holdTime)
    {
        // TODO: Implement hold() method.
    }

    public function cancel(Payment $payment)
    {
        // TODO: Implement cancel() method.
    }

    public function confirm(Payment $payment)
    {
        // TODO: Implement confirm() method.
    }

    public function refund(Payment $payment, $refundableSum)
    {
        // TODO: Implement refund() method.
    }
}
