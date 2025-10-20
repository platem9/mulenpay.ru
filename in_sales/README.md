# Интеграция InSales с MulenPay

Этот модуль — минимальный HTTP‑сервис для интеграции магазина InSales с платежным сервисом MulenPay по спецификации OpenAPI (см. `docs/api.yml`).

Функции:
- Создание платежа в MulenPay и получение ссылки для оплаты.
- Эндпоинт редиректа для покупателей (InSales перенаправляет покупателя на наш сервис, а мы — на страницу оплаты MulenPay).
- Обработка callback (webhook) от MulenPay и зачисление оплаты по заказу в InSales.

## Архитектура и поток выполнения
1. Покупатель оформляет заказ в магазине и выбирает способ оплаты «MulenPay».
2. InSales перенаправляет покупателя на URL нашего сервиса `/payments/pay` с параметрами заказа (минимум: `order_id`, `amount`).
3. Сервис вызывает API MulenPay `POST /v2/payments`, получает `paymentUrl` и делает 302-редирект на страницу оплаты.
4. После успешной оплаты MulenPay отправляет callback на URL `/webhooks/mulenpay` с данными платежа.
5. Сервис создаёт транзакцию в InSales для соответствующего заказа (или помечает заказ оплаченным в качестве запасного варианта).

Примечание: поле `uuid` в запросе к MulenPay используется для связи платежа с заказом InSales. Мы передаём туда `order_id` из InSales.

## Требования
- Node.js 18+.
- Доступы к API MulenPay (Bearer API Key, shopId и секрет для подписи) — берутся из личного кабинета MulenPay.
- Доступы к API InSales (домен магазина, API key и пароль приложения).

## Установка
```bash
npm install
cp .env.example .env
# отредактируйте .env
npm start
```
Сервис по умолчанию запускается на `http://localhost:3000`.

## Настройки окружения (.env)
См. `.env.example`.
- PORT — порт HTTP сервера.
- MULENPAY_BASE_URL — базовый URL API MulenPay (по умолчанию `https://mulenpay.ru/api`).
- MULENPAY_API_KEY — Bearer токен MulenPay.
- MULENPAY_SHOP_ID — ID магазина в MulenPay.
- MULENPAY_SECRET — секрет (для подписи платежного запроса `sign`).
- INSALES_DOMAIN — домен магазина InSales, например `yourshop.myinsales.ru`.
- INSALES_API_KEY — ключ API InSales.
- INSALES_PASSWORD — пароль API InSales.

## Эндпоинты

- GET `/health` — проверка работоспособности.

- POST `/payments/create` — создать платёж и получить ссылку (для backend‑интеграций).
  Пример запроса:
  ```json
  {
    "order_id": 12345,
    "amount": 1000.50,
    "currency": "rub",
    "description": "Оплата заказа #12345"
  }
  ```
  Ответ `201`:
  ```json
  { "success": true, "paymentUrl": "https://mulenpay.ru/...", "id": 9876 }
  ```

- GET `/payments/pay` — создание платежа и мгновенный редирект на оплату (для подключения в InSales как URL способа оплаты).
  Параметры query: `order_id` (обяз.), `amount` (обяз.), `currency=rub`, `description`, `website_url`, `language=ru`.
  Пример URL: `https://<host>/payments/pay?order_id={{order.id}}&amount={{order.total_price}}`

- POST `/webhooks/mulenpay` — обработка callback от MulenPay.
  Тело запроса соответствует `docs/api.yml /payments/callback`:
  ```json
  {
    "id": 9876,
    "amount": 1000.50,
    "currency": "RUB",
    "uuid": "12345",
    "payment_status": "success"
  }
  ```
  При `payment_status = success` сервис создаёт транзакцию в InSales для заказа с ID, равным `uuid`, и зачисляет оплату.

## Подпись запроса к MulenPay
Поле `sign` формируется согласно OpenAPI:
```
sha1( currency + amount + shopId + secret )
```
где `secret` — секрет из ЛК MulenPay. В модуле это делает `MulenPayClient.buildSign(...)`.

## Настройка в InSales
1. В админ‑панели InSales создайте способ оплаты «MulenPay» (тип: внешний/ссылка на сторонний сервис).
2. В качестве URL способа оплаты укажите адрес вашего сервера:
   ```
   https://<ваш-домен>/payments/pay?order_id={{order.id}}&amount={{order.total_price}}
   ```
   При необходимости передавайте `description` и `website_url`.
3. Убедитесь, что ваш сервер доступен из интернета.

## Настройка в MulenPay
В личном кабинете MulenPay укажите URL callback вашего сервера:
```
https://<ваш-домен>/webhooks/mulenpay
```
При тестировании можно использовать инструмент из OpenAPI `POST /payments/callback` для принудительной отправки колбэка.

## Безопасность
- Колбэк в спецификации не содержит подписи. Рекомендуется ограничить доступ к `/webhooks/mulenpay` по IP MulenPay или настроить дополнительную проверку на стороне MulenPay, если она доступна.
- Храните ключи и секреты только в переменных окружения.

## Технические детали реализации
- Клиент MulenPay: `src/clients/mulenpay.js` (Bearer авторизация, `createPayment`, `getPayment`, `listPayments`).
- Клиент InSales: `src/clients/insales.js` (создание транзакции `POST /admin/orders/{id}/transactions.json`; при ошибке пытается отметить заказ оплаченным `PUT /admin/orders/{id}.json`).
- Маршруты: `src/routes/payments.js`, `src/routes/webhooks.js`.
- Валидация входящих данных — через Joi.

## Тестирование локально
1. Запустите сервис: `npm start`.
2. Создайте платёж:
   ```bash
   curl -X POST http://localhost:3000/payments/create \
     -H 'Content-Type: application/json' \
     -d '{"order_id":1,"amount":100.50,"description":"Тестовый заказ"}'
   ```
3. Эмулируйте callback:
   ```bash
   curl -X POST http://localhost:3000/webhooks/mulenpay \
     -H 'Content-Type: application/json' \
     -d '{"id":999,"amount":100.50,"currency":"RUB","uuid":"1","payment_status":"success"}'
   ```

## Лицензия
MIT