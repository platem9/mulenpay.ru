# Mulen Pay Golang SDK

[![Go Reference](https://pkg.go.dev/badge/github.com/platem9/mulenpay.ru.git/sdk/golang.svg)](https://pkg.go.dev/github.com/platem9/mulenpay.ru.git/sdk/golang)

SDK для работы с API Mulen Pay.

## Installation

```bash
go get github.com/platem9/mulenpay.ru.git/sdk/golang
```

## Usage

```go
package main

import (
	"fmt"
	"github.com/platem9/mulenpay.ru.git/sdk/golang"
)

func main() {
	client := mulen.NewClient("YOUR_API_KEY", 123)

	req := &mulen.PaymentRequest{
		Currency:    "rub",
		Amount:      "100.50",
		UUID:        "ORDER-123",
		Description: "Test payment",
		Items: []mulen.Item{
			{
				Description:    "Test item",
				Quantity:       1,
				Price:          100.50,
				VatCode:        1,
				PaymentSubject: 1,
				PaymentMode:    1,
			},
		},
	}

	resp, err := client.CreatePayment(req)
	if err != nil {
		panic(err)
	}

	fmt.Println("Payment URL:", resp.PaymentURL)
	fmt.Println("Payment ID:", resp.ID)
}
```

## API

### `NewClient(apiKey string, shopID int) *Client`

Создает новый клиент API.

### `CreatePayment(req *PaymentRequest) (*PaymentResponse, error)`

Создает новый платеж.

### `GetPayments() ([]Payment, error)`

Получает список платежей.

### `GetPayment(id int) (*Payment, error)`

Получает информацию о платеже по его ID.

### `HoldPayment(id int) error`

Холдирует платеж.

### `CancelHold(id int) error`

Отменяет холдирование платежа.

### `RefundPayment(id int) error`

Возвращает платеж.

### `GetReceipt(id int) ([]Receipt, error)`

Получает чек для платежа.

### `GetSubscribes() ([]Subscribe, error)`

Получает список подписок.

### `CancelSubscribe(id int) error`

Отменяет подписку.

### `GetShopBalances() ([]Balance, error)`

Получает баланс магазина.

## Callbacks

Для обработки колбэков можно использовать функцию `HandleCallback`:

```go
func callbackHandler(w http.ResponseWriter, r *http.Request) {
	cb, err := mulen.HandleCallback(r)
	if err != nil {
		http.Error(w, "Invalid callback", http.StatusBadRequest)
		return
	}

	// Process the callback data
	fmt.Println("Received callback for payment:", cb.ID)

	w.WriteHeader(http.StatusOK)
}
