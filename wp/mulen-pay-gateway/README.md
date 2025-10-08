# Mulen Pay Gateway

Платежный шлюз для WooCommerce для интеграции с Mulen Pay.

## Установка

1.  Загрузите папку `mulen-pay-gateway` в директорию `/wp-content/plugins/`.
2.  Активируйте плагин через меню 'Плагины' в WordPress.
3.  Перейдите в настройки WooCommerce -> Платежи и включите 'Mulen Pay'.

## Настройка

1.  **Shop ID:** ID вашего магазина в системе Mulen Pay.
2.  **Secret Key:** Ваш секретный ключ из личного кабинета Mulen Pay.

## Вебхук

Для получения уведомлений о статусе платежа, вам необходимо настроить вебхук в личном кабинете Mulen Pay.

URL для вебхука: `https://your-site.com/wc-api/mulen_pay_webhook/`
