<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class mulen_pay extends CModule
{
    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');

        $this->MODULE_ID = 'mulen.pay';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('MULEN_PAY_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MULEN_PAY_MODULE_DESC');
        $this->PARTNER_NAME = Loc::getMessage('MULEN_PAY_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('MULEN_PAY_PARTNER_URI');
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        CopyDirFiles(__DIR__ . '/sale_payment/mulenpay', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/sale_payment/mulenpay', true, true);
    }

    public function doUninstall()
    {
        DeleteDirFilesEx('/bitrix/php_interface/include/sale_payment/mulenpay');
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}
