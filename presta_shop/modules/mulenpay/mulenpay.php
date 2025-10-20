<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Mulenpay extends PaymentModule
{
    const CONFIG_API_KEY = 'MULENPAY_API_KEY';
    const CONFIG_SECRET = 'MULENPAY_SECRET';
    const CONFIG_SHOP_ID = 'MULENPAY_SHOP_ID';
    const CONFIG_BASE_URL = 'MULENPAY_BASE_URL';
    const CONFIG_WEBHOOK_TOKEN = 'MULENPAY_WEBHOOK_TOKEN';
    const CONFIG_OS_WAITING = 'MULENPAY_OS_WAITING';

    public function __construct()
    {
        $this->name = 'mulenpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'MulenPay';
        $this->need_instance = 0;
        $this->is_eu_compatible = 1;
        $this->controllers = ['payment', 'callback'];

        parent::__construct();

        $this->displayName = $this->l('MulenPay');
        $this->description = $this->l('Оплата через MulenPay');
        $this->confirmUninstall = $this->l('Удалить модуль MulenPay?');

        if (!Configuration::get(self::CONFIG_BASE_URL)) {
            Configuration::updateValue(self::CONFIG_BASE_URL, 'https://mulenpay.ru/api');
        }
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!$this->registerHook('paymentOptions')) {
            return false;
        }
        if (!$this->registerHook('paymentReturn')) {
            return false;
        }

        // Create awaiting payment order state
        if (!Configuration::get(self::CONFIG_OS_WAITING)) {
            $order_state = new OrderState();
            $order_state->name = [];
            foreach (Language::getLanguages(false) as $lang) {
                $order_state->name[$lang['id_lang']] = 'Ожидание оплаты MulenPay';
            }
            $order_state->send_email = false;
            $order_state->color = '#3498db';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            if ($order_state->add()) {
                Configuration::updateValue(self::CONFIG_OS_WAITING, (int)$order_state->id);
            } else {
                return false;
            }
        }

        // Create mapping table
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mulenpay_payment` (
            `id_mulenpay_payment` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_order` INT UNSIGNED NOT NULL,
            `id_cart` INT UNSIGNED NOT NULL,
            `mulen_payment_id` VARCHAR(64) NULL,
            `status` VARCHAR(32) NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id_mulenpay_payment`),
            KEY (`id_order`),
            KEY (`id_cart`),
            UNIQUE KEY `uniq_order` (`id_order`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        // Generate webhook token
        if (!Configuration::get(self::CONFIG_WEBHOOK_TOKEN)) {
            Configuration::updateValue(self::CONFIG_WEBHOOK_TOKEN, Tools::substr(sha1(uniqid('', true)), 0, 24));
        }

        return true;
    }

    public function uninstall()
    {
        $ok = true;
        $ok = $ok && Configuration::deleteByName(self::CONFIG_API_KEY);
        $ok = $ok && Configuration::deleteByName(self::CONFIG_SECRET);
        $ok = $ok && Configuration::deleteByName(self::CONFIG_SHOP_ID);
        $ok = $ok && Configuration::deleteByName(self::CONFIG_BASE_URL);
        $ok = $ok && Configuration::deleteByName(self::CONFIG_WEBHOOK_TOKEN);
        $ok = $ok && Configuration::deleteByName(self::CONFIG_OS_WAITING);
        // Keep table for history by default; uncomment to drop
        // Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'mulenpay_payment`');
        return parent::uninstall() && $ok;
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitMulenPay')) {
            Configuration::updateValue(self::CONFIG_API_KEY, trim(Tools::getValue(self::CONFIG_API_KEY)));
            Configuration::updateValue(self::CONFIG_SECRET, trim(Tools::getValue(self::CONFIG_SECRET)));
            Configuration::updateValue(self::CONFIG_SHOP_ID, (int)Tools::getValue(self::CONFIG_SHOP_ID));
            Configuration::updateValue(self::CONFIG_BASE_URL, trim(Tools::getValue(self::CONFIG_BASE_URL)) ?: 'https://mulenpay.ru/api');
            Configuration::updateValue(self::CONFIG_WEBHOOK_TOKEN, trim(Tools::getValue(self::CONFIG_WEBHOOK_TOKEN)));
            $output .= $this->displayConfirmation($this->l('Настройки сохранены'));
        }

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Настройки MulenPay'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('API Key (Bearer)'),
                        'name' => self::CONFIG_API_KEY,
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Secret для подписи'),
                        'name' => self::CONFIG_SECRET,
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Shop ID'),
                        'name' => self::CONFIG_SHOP_ID,
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('API Base URL'),
                        'name' => self::CONFIG_BASE_URL,
                        'desc' => $this->l('Например, https://mulenpay.ru/api'),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Webhook token'),
                        'name' => self::CONFIG_WEBHOOK_TOKEN,
                        'desc' => $this->l('Добавьте этот токен в URL колбэка на стороне MulenPay для проверки запроса.'),
                        'required' => false,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Сохранить'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMulenPay';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value = [
            self::CONFIG_API_KEY => Tools::safeOutput(Configuration::get(self::CONFIG_API_KEY)),
            self::CONFIG_SECRET => Tools::safeOutput(Configuration::get(self::CONFIG_SECRET)),
            self::CONFIG_SHOP_ID => (int)Configuration::get(self::CONFIG_SHOP_ID),
            self::CONFIG_BASE_URL => Tools::safeOutput(Configuration::get(self::CONFIG_BASE_URL)),
            self::CONFIG_WEBHOOK_TOKEN => Tools::safeOutput(Configuration::get(self::CONFIG_WEBHOOK_TOKEN)),
        ];

        $output .= $helper->generateForm([$fields_form]);
        $output .= $this->renderWebhookInfo();
        return $output;
    }

    protected function renderWebhookInfo()
    {
        $link = $this->context->link->getModuleLink($this->name, 'callback', ['token' => Configuration::get(self::CONFIG_WEBHOOK_TOKEN)], true);
        $html = '<div class="panel"><div class="panel-heading">'.$this->l('URL колбэка').'</div>';
        $html .= '<div class="alert alert-info">'.$this->l('Укажите этот URL в настройках магазина MulenPay для получения уведомлений об оплате:').'<br><b>'.Tools::safeOutput($link).'</b></div>';
        $html .= '</div>';
        return $html;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return [];
        }

        if (!$this->checkCurrency($params['cart'])) {
            return [];
        }

        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption->setCallToActionText($this->l('Оплата через MulenPay'))
            ->setAction($this->context->link->getModuleLink($this->name, 'payment', [], true));
        $logoPath = _PS_MODULE_DIR_.$this->name.'/logo.png';
        if (file_exists($logoPath)) {
            $newOption->setLogo(Media::getMediaPath($logoPath));
        }

        return [$newOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return '';
        }
        return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency((int)$cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }
}
