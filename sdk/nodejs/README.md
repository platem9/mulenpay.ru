# Mulen Pay SDK

SDK для Node.js для API Mulen Pay, разработанный для упрощения управления платежами и подписками.

## Установка

Для начала вам понадобятся установленные Node.js и npm. Затем вы можете добавить SDK в свой проект с помощью следующей команды:

```bash
npm install mulen-pay-sdk
```

*Примечание: Этот пакет еще не опубликован в npm. Пока что вы можете установить его напрямую из исходного кода.*

## Начало работы

Сначала вам нужно импортировать SDK и инициализировать его с вашим API-ключом, ID магазина и секретным ключом. Вы можете найти эти учетные данные в панели управления вашего аккаунта Mulen Pay.

```javascript
import MulenPaySDK from 'mulen-pay-sdk';

const apiKey = 'ВАШ_API_КЛЮЧ';
const shopId = 'ВАШ_ID_МАГАЗИНА';
const secretKey = 'ВАШ_СЕКРЕТНЫЙ_КЛЮЧ';

const sdk = new MulenPaySDK(apiKey, shopId, secretKey);
```

## Создание платежа

Вот краткий пример того, как создать новый платеж:

```javascript
const paymentData = {
  currency: 'rub',
  amount: '1250.50',
  uuid: 'order-12345',
  description: 'Заказ №12345',
  items: [
    {
      description: 'Футболка',
      price: 1000,
      quantity: 1,
      vat_code: 0,
      payment_subject: 1,
      payment_mode: 4,
    },
    {
      description: 'Кружка',
      price: 250.50,
      quantity: 1,
      vat_code: 0,
      payment_subject: 1,
      payment_mode: 4,
    },
  ],
};

sdk.createPayment(paymentData)
  .then(response => {
    console.log('Платеж успешно создан:', response);
    // Перенаправьте пользователя на response.paymentUrl
  })
  .catch(error => {
    console.error('Ошибка при создании платежа:', error);
  });
```

## Доступные методы

SDK предоставляет полный набор методов для взаимодействия с API Mulen Pay:

-   `createPayment(paymentData)`: Создает новый платеж.
-   `getPayments()`: Получает список всех платежей.
-   `getPayment(id)`: Запрашивает детали конкретного платежа.
-   `confirmHold(id)`: Подтверждает удержанный платеж.
-   `cancelHold(id)`: Отменяет удержанный платеж.
-   `refundPayment(id)`: Возвращает обработанный платеж.
-   `getReceipt(id)`: Получает квитанцию для платежа.
-   `getSubscriptions()`: Запрашивает список всех подписок.
-   `cancelSubscription(id)`: Отменяет активную подписку.
-   `getShopBalances(shopId)`: Получает балансы для указанного магазина.

Каждый метод возвращает Promise, который разрешается с ответом API или отклоняется с ошибкой. Для получения более подробной информации о необходимых параметрах и структурах ответов, пожалуйста, обратитесь к официальной [документации API Mulen Pay](https://mulenpay.ru/api/docs).
