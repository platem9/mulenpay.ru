# Mulen Pay SDK for Kotlin

Это официальный SDK для работы с API Mulen Pay на языке Kotlin. SDK предоставляет удобный способ взаимодействия со всеми основными функциями API, включая создание платежей, управление подписками и получение информации о балансе.

## Установка

Для установки SDK вам необходимо добавить зависимость в ваш `build.gradle.kts` файл.

```kotlin
dependencies {
    implementation("com.mulen:sdk:1.0.0")
}
```

## Использование

### Инициализация клиента

Для начала работы с SDK необходимо инициализировать `ApiClient`, передав ему ваш API и секретный ключ.

```kotlin
import com.mulen.sdk.ApiClient

val apiClient = ApiClient(apiKey = "YOUR_API_KEY", secretKey = "YOUR_SECRET_KEY")
```

### Создание платежа

```kotlin
import com.mulen.sdk.models.PaymentRequest
import com.mulen.sdk.models.PaymentRequestItem

val paymentRequest = PaymentRequest(
    currency = "rub",
    amount = "1000.50",
    uuid = "invoice_123",
    shopId = 5,
    description = "Покупка булочек",
    items = listOf(
        PaymentRequestItem(
            description = "Булочка",
            quantity = 1.0,
            price = 1000.50,
            vatCode = 0,
            paymentSubject = 1,
            paymentMode = 1
        )
    ),
    sign = "" // Подпись генерируется автоматически
)

val paymentResponse = apiClient.createPayment(paymentRequest)
if (paymentResponse.success) {
    println("Ссылка на оплату: ${paymentResponse.paymentUrl}")
} else {
    println("Ошибка: ${paymentResponse.error}")
}
```

### Получение списка платежей

```kotlin
val payments = apiClient.getPayments()
payments.items.forEach { payment ->
    println("Платеж ID: ${payment.id}, Статус: ${payment.status}")
}
```

### Получение информации о платеже

```kotlin
val paymentInfo = apiClient.getPayment(123) // ID платежа
if (paymentInfo.success) {
    println("Найден платеж: ${paymentInfo.payment}")
}
```

## Сборка и тестирование

Для сборки проекта и запуска тестов выполните следующие команды:

```bash
./gradlew build
./gradlew test
```

## Лицензия

MIT
