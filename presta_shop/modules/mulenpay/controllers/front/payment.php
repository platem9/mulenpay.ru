<?php
class MulenpayPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_header = false;
    public $display_footer = false;

    public function postProcess()
    {
        if (!$this->module->active) {
            die($this->l('Модуль отключен'));
        }

        $cart = $this->context->cart;
        if (!$cart->id || !$cart->id_customer || !$cart->id_address_delivery || !$cart->id_address_invoice) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $waiting_os = (int)Configuration::get(Mulenpay::CONFIG_OS_WAITING);
        $currency = $this->context->currency;
        $amount = (float)$cart->getOrderTotal(true, Cart::BOTH);

        // Create order in awaiting state
        $this->module->validateOrder(
            (int)$cart->id,
            $waiting_os,
            $amount,
            $this->module->displayName,
            null,
            [],
            (int)$currency->id,
            false,
            $customer->secure_key
        );

        $order = new Order($this->module->currentOrder);
        if (!Validate::isLoadedObject($order)) {
            die($this->l('Не удалось создать заказ'));
        }

        // Build items from cart
        $products = $cart->getProducts(true);
        $items = [];
        foreach ($products as $p) {
            $vat_code = $this->mapVatCode(isset($p['rate']) ? (float)$p['rate'] : 0.0);
            $items[] = [
                'description' => Tools::substr($p['name'], 0, 255),
                'price' => (float)Tools::ps_round($p['price_wt'], 2),
                'quantity' => (float)$p['cart_quantity'],
                'vat_code' => $vat_code,
                'payment_subject' => 1, // Товар
                'payment_mode' => 4, // Полный расчет
            ];
        }

        $payload = [
            'currency' => 'rub',
            'amount' => number_format($amount, 2, '.', ''),
            'uuid' => $order->reference,
            'description' => 'Заказ '.$order->reference,
            'website_url' => Tools::getShopDomainSsl(true, true),
            'language' => $this->context->language->iso_code ?: 'ru',
            'items' => $items,
        ];

        // Create payment in MulenPay
        require_once _PS_MODULE_DIR_.'mulenpay/classes/MulenPayClient.php';
        $client = new MulenPayClient(
            Configuration::get(Mulenpay::CONFIG_BASE_URL),
            Configuration::get(Mulenpay::CONFIG_API_KEY),
            (int)Configuration::get(Mulenpay::CONFIG_SHOP_ID),
            Configuration::get(Mulenpay::CONFIG_SECRET)
        );
        $resp = $client->createPayment($payload);

        // Save mapping row
        Db::getInstance()->insert('mulenpay_payment', [
            'id_order' => (int)$order->id,
            'id_cart' => (int)$cart->id,
            'mulen_payment_id' => isset($resp['id']) ? pSQL($resp['id']) : null,
            'status' => 'created',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!empty($resp['success']) && !empty($resp['paymentUrl'])) {
            Tools::redirect($resp['paymentUrl']);
        }

        // On error: show message
        $msg = $this->l('Ошибка при создании платежа в MulenPay: ').(isset($resp['error']) ? $resp['error'] : '');
        die($msg);
    }

    private function mapVatCode($rate)
    {
        $rate = (float)$rate;
        if ($rate >= 19.5 && $rate <= 20.5) {
            return 6; // 20%
        }
        if ($rate >= 9.5 && $rate <= 10.5) {
            return 2; // 10%
        }
        if ($rate >= -0.1 && $rate <= 0.1) {
            return 0; // Без НДС
        }
        return 0; // default
    }
}
