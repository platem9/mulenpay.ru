# Mulen Pay SDK для Dart/Flutter

Неофициальный SDK для работы с API Mulen Pay на языке Dart, подходит для использования во Flutter-проектах.

## Установка

Добавьте зависимость в ваш `pubspec.yaml`:

```yaml
dependencies:
  mulen_pay_sdk: ^0.0.1 # Укажите актуальную версию
```

Или установите через терминал:

```shell
flutter pub add mulen_pay_sdk
```

## Использование

### Инициализация клиента

Для начала работы необходимо инициализировать клиент, передав ему ваш `apiKey` и `secretKey`, полученные в личном кабинете Mulen Pay.

```dart
import 'package:mulen_pay_sdk/mulen_pay_sdk.dart';

final mulenPay = MulenPay(
  apiKey: 'ВАШ_API_KEY',
  secretKey: 'ВАШ_СЕКРЕТНЫЙ_КЛЮЧ',
);
```

### Создание платежа

Для создания нового платежа необходимо сформировать объект `PaymentRequest` и передать его в метод `createPayment`.

```dart
// Формирование списка товаров
final items = [
  Item(
    description: 'Покупка булочек',
    quantity: 2,
    price: 50.25,
    vatCode: 0, // Без НДС
    paymentSubject: 1, // Товар
    paymentMode: 4, // Полный расчет
  ),
];

// Генерация подписи
final amount = (items.fold(0.0, (sum, item) => sum + item.price * item.quantity)).toStringAsFixed(2);
final shopId = 5; // ID вашего магазина
final currency = 'rub';
final signature = mulenPay.generateSignature(currency, amount, shopId);

// Создание запроса
final paymentRequest = PaymentRequest(
  currency: currency,
  amount: amount,
  uuid: 'ORDER-12345', // Уникальный идентификатор заказа в вашей системе
  shopId: shopId,
  description: 'Заказ №12345',
  items: items,
  sign: signature,
  websiteUrl: 'https://example.com',
  language: 'ru',
);

// Отправка запроса
try {
  final response = await mulenPay.createPayment(paymentRequest);
  final paymentUrl = response['paymentUrl'];
  final paymentId = response['id'];
  
  print('Платеж успешно создан! ID: $paymentId');
  print('URL для оплаты: $paymentUrl');
  
  // Здесь вы можете перенаправить пользователя на paymentUrl
  
} on ApiException catch (e) {
  print('Произошла ошибка API: ${e.statusCode} - ${e.message}');
} catch (e) {
  print('Произошла неизвестная ошибка: $e');
}
```

### Получение списка платежей

```dart
try {
  final payments = await mulenPay.getPayments();
  for (final payment in payments) {
    print('Платеж #${payment.id}, статус: ${payment.status}');
  }
} on ApiException catch (e) {
  // Обработка ошибки
}
```

### Получение информации о платеже

```dart
try {
  final paymentId = 123; // ID платежа
  final payment = await mulenPay.getPayment(paymentId);
  print('Информация о платеже #${payment.id}:');
  print('  Сумма: ${payment.amount} ${payment.currency}');
  print('  Статус: ${payment.status}');
} on ApiException catch (e) {
  // Обработка ошибки
}
```

## Доступные методы

-   `Future<Map<String, dynamic>> createPayment(PaymentRequest paymentRequest)` - Создание нового платежа.
-   `Future<List<Payment>> getPayments()` - Получение списка платежей.
-   `Future<Payment> getPayment(int id)` - Получение информации о конкретном платеже.
-   `Future<void> holdPayment(int id)` - Подтверждение списания захолдированных средств.
-   `Future<void> cancelHoldPayment(int id)` - Отмена списания захолдированных средств.
-   `Future<void> refundPayment(int id)` - Возврат платежа.
-   `Future<List<Receipt>> getReceipt(int id)` - Получение информации о чеке для платежа.
-   `Future<List<Subscribe>> getSubscribes()` - Получение списка подписок.
-   `Future<void> cancelSubscribe(int id)` - Отмена подписки.
-   `Future<List<Balance>> getBalances(int shopId)` - Получение балансов магазина.
