<?php
class MulenpayCallbackModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_header = false;
    public $display_footer = false;

    public function initContent()
    {
        parent::initContent();
        header('Content-Type: application/json');

        $expectedToken = Configuration::get(Mulenpay::CONFIG_WEBHOOK_TOKEN);
        $token = Tools::getValue('token');
        if ($expectedToken && $token !== $expectedToken) {
            http_response_code(403);
            die(json_encode(['success' => false, 'message' => 'Forbidden']));
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST; // fallback
        }

        $uuid = isset($data['uuid']) ? pSQL($data['uuid']) : null;
        $status = isset($data['payment_status']) ? pSQL($data['payment_status']) : null;
        $paymentId = isset($data['id']) ? pSQL($data['id']) : null;
        $amount = isset($data['amount']) ? (float)$data['amount'] : null;

        if (!$uuid || !$status) {
            http_response_code(400);
            die(json_encode(['success' => false, 'message' => 'Bad request']));
        }

        $orders = Order::getByReference($uuid);
        $order = $orders ? $orders->getFirst() : null;
        if (!$order || !Validate::isLoadedObject($order)) {
            http_response_code(404);
            die(json_encode(['success' => false, 'message' => 'Order not found']));
        }

        // Update mapping table
        Db::getInstance()->update('mulenpay_payment', [
            'mulen_payment_id' => $paymentId,
            'status' => pSQL($status),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id_order='.(int)$order->id);

        if ($status === 'success') {
            // Set order status to payment accepted
            $history = new OrderHistory();
            $history->id_order = (int)$order->id;
            $history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), $order);
            $history->addWithemail();

            // Create order payment record
            if ($amount) {
                $payment = new OrderPayment();
                $payment->order_reference = $order->reference;
                $payment->id_currency = (int)$order->id_currency;
                $payment->amount = (float)$amount;
                $payment->payment_method = $this->module->displayName;
                $payment->transaction_id = $paymentId;
                $payment->conversion_rate = 1;
                $payment->add();
            }
        } elseif ($status === 'cancel') {
            $history = new OrderHistory();
            $history->id_order = (int)$order->id;
            $history->changeIdOrderState((int)Configuration::get('PS_OS_CANCELED'), $order);
            $history->addWithemail();
        }

        echo json_encode(['success' => true]);
        exit;
    }
}
