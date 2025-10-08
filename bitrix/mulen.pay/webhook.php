<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem;

Loader::includeModule('sale');

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

$inputStream = file_get_contents('php://input');
$data = json_decode($inputStream, true);

if ($data && isset($data['uuid'])) {
    $order = Order::load($data['uuid']);
    if ($order) {
        $paymentCollection = $order->getPaymentCollection();
        foreach ($paymentCollection as $payment) {
            if (!$payment->isPaid()) {
                $paySystemService = PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
                if ($paySystemService) {
                    $result = $paySystemService->processRequest($payment, $request);
                    if ($result->isSuccess()) {
                        $payment->setPaid(true);
                        $order->save();
                    }
                }
            }
        }
    }
}

