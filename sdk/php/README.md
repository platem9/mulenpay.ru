# Mulen Pay PHP SDK

[![Последняя версия на Packagist](https://img.shields.io/packagist/v/mulen/mulen-pay-sdk.svg?style=flat-square)](https://packagist.org/packages/mulen/mulen-pay-sdk)
[![Тесты](https://github.com/mulen/mulen-pay-sdk/actions/workflows/run-tests.yml/badge.svg)](https://github.com/mulen/mulen-pay-sdk/actions/workflows/run-tests.yml)
[![Всего загрузок](https://img.shields.io/packagist/dt/mulen/mulen-pay-sdk.svg?style=flat-square)](https://packagist.org/packages/mulen/mulen-pay-sdk)

Это официальный PHP SDK для [Mulen Pay API](https://mulenpay.ru/).

## Установка

Вы можете установить пакет через Composer:

```bash
composer require mulen/mulen-pay-sdk
```

## Использование

### Инициализация клиента

```php
use Mulen\MulenPay\MulenPayClient;

$client = new MulenPayClient('ВАШ_API_КЛЮЧ');
```

### Платежи

#### Создать платеж

```php
$payment = $client->payments()->create([
    'amount' => 100.50,
    'currency' => 'RUB',
    'description' => 'Тестовый платеж',
    // ... другие параметры платежа
]);
```

#### Получить список платежей

```php
$payments = $client->payments()->list();
```

#### Получить информацию о платеже

```php
$payment = $client->payments()->retrieve(123);
```

### Подписки

#### Получить список подписок

```php
$subscriptions = $client->subscribes()->list();
```

#### Отменить подписку

```php
$client->subscribes()->cancel(456);
```

### Магазины

#### Получить баланс магазина

```php
$balances = $client->shops()->getBalances(789);
```

## Тестирование

```bash
composer test
```

## Лицензия

Лицензия MIT (MIT). Пожалуйста, смотрите [Файл лицензии](LICENSE.md) для получения дополнительной информации.
