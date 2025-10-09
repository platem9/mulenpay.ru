# Mulen Pay Python SDK

Python SDK для работы с API [Mulen Pay](https://mulenpay.ru/).

## Установка

```bash
pip install mulen-pay-sdk
```

## Использование

```python
from mulen_pay_sdk import MulenPayClient

client = MulenPayClient(api_key="YOUR_API_KEY")

# Создание платежа
payment_data = {
    "currency": "rub",
    "amount": 1000.50,
    "shopId": 5,
    "description": "Покупка булочек",
    "uuid": "invoice_123",
    "items": [
        {
            "description": "Булочка",
            "quantity": 1,
            "price": 1000.50,
            "vat_code": 0,
            "payment_subject": 1,
            "payment_mode": 4
        }
    ]
}
new_payment = client.create_payment(**payment_data)
print(new_payment)

# Получение списка платежей
payments = client.get_payments()
print(payments)

# Получение данных по платежу
payment_info = client.get_payment(payment_id=1)
print(payment_info)
```

## Тестирование

Для запуска тестов выполните:

```bash
pytest
