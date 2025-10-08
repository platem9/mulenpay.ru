<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = [
    'NAME' => Loc::getMessage('MULEN_PAY_TITLE'),
    'SORT' => 500,
    'CODES' => [
        'MULEN_PAY_SHOP_ID' => [
            'NAME' => Loc::getMessage('MULEN_PAY_SHOP_ID'),
            'SORT' => 100,
            'GROUP' => 'CONNECT_SETTINGS_MULEN',
        ],
        'MULEN_PAY_SECRET_KEY' => [
            'NAME' => Loc::getMessage('MULEN_PAY_SECRET_KEY'),
            'SORT' => 200,
            'GROUP' => 'CONNECT_SETTINGS_MULEN',
        ],
        'MULEN_PAY_CURRENCY' => [
            'NAME' => Loc::getMessage('MULEN_PAY_CURRENCY'),
            'SORT' => 300,
            'GROUP' => 'CONNECT_SETTINGS_MULEN',
            'INPUT' => [
                'TYPE' => 'ENUM',
                'OPTIONS' => [
                    'rub' => 'RUB',
                ]
            ],
            'DEFAULT' => [
                'PROVIDER_KEY' => 'INPUT',
                'PROVIDER_VALUE' => 'rub',
            ]
        ],
    ]
];
