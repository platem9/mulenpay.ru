package mulen

import (
	"bytes"
	"fmt"
	"net/http"
	"net/http/httptest"
	"strings"
	"testing"
)

func TestCreatePayment(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/payments" {
			t.Errorf("Expected to request ‘/v2/payments’, got ‘%s’", r.URL.Path)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusCreated)
		fmt.Fprintln(w, `{"success":true,"paymentUrl":"https://mulenpay.ru/","id":1}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	req := &PaymentRequest{
		Currency:    "rub",
		Amount:      "1000.50",
		UUID:        "invoice_123",
		Description: "Покупка булочек",
		Items: []Item{
			{
				Description:    "Булочка",
				Quantity:       1,
				Price:          1000.50,
				VatCode:        1,
				PaymentSubject: 1,
				PaymentMode:    1,
			},
		},
	}

	resp, err := client.CreatePayment(req)
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}

	if resp.PaymentURL != "https://mulenpay.ru/" {
		t.Errorf("Expected paymentURL to be ‘https://mulenpay.ru/’, got ‘%s’", resp.PaymentURL)
	}

	if resp.ID != 1 {
		t.Errorf("Expected id to be 1, got %d", resp.ID)
	}
}

func TestGetPayments(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/payments" {
			t.Errorf("Expected to request ‘/v2/payments’, got ‘%s’", r.URL.Path)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintln(w, `{"items":[{"id":1,"uuid":"invoice_123","amount":"1000.50","currency":"rub","description":"Покупка булочек","status":3}]}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	payments, err := client.GetPayments()
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}

	if len(payments) != 1 {
		t.Errorf("Expected 1 payment, got %d", len(payments))
	}

	if payments[0].ID != 1 {
		t.Errorf("Expected payment ID to be 1, got %d", payments[0].ID)
	}
}

func TestGetPayment(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/payments/1" {
			t.Errorf("Expected to request ‘/v2/payments/1’, got ‘%s’", r.URL.Path)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintln(w, `{"success":true,"payment":{"id":1,"uuid":"invoice_123","amount":"1000.50","currency":"rub","description":"Покупка булочек","status":3}}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	payment, err := client.GetPayment(1)
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}

	if payment.ID != 1 {
		t.Errorf("Expected payment ID to be 1, got %d", payment.ID)
	}
}

func TestHoldPayment(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/payments/1/hold" {
			t.Errorf("Expected to request ‘/v2/payments/1/hold’, got ‘%s’", r.URL.Path)
		}
		if r.Method != "PUT" {
			t.Errorf("Expected method to be ‘PUT’, got ‘%s’", r.Method)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintln(w, `{"success":true}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	err := client.HoldPayment(1)
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}
}

func TestCancelHold(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/payments/1/hold" {
			t.Errorf("Expected to request ‘/v2/payments/1/hold’, got ‘%s’", r.URL.Path)
		}
		if r.Method != "DELETE" {
			t.Errorf("Expected method to be ‘DELETE’, got ‘%s’", r.Method)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintln(w, `{"success":true}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	err := client.CancelHold(1)
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}
}

func TestRefundPayment(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/payments/1/refund" {
			t.Errorf("Expected to request ‘/v2/payments/1/refund’, got ‘%s’", r.URL.Path)
		}
		if r.Method != "PUT" {
			t.Errorf("Expected method to be ‘PUT’, got ‘%s’", r.Method)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintln(w, `{"success":true}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	err := client.RefundPayment(1)
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}
}

func TestGetReceipt(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/payments/1/receipt" {
			t.Errorf("Expected to request ‘/v2/payments/1/receipt’, got ‘%s’", r.URL.Path)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintln(w, `{"status":"success","items":[{"id":1,"uuid":"invoice_123","total":1000.50}]}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	receipts, err := client.GetReceipt(1)
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}

	if len(receipts) != 1 {
		t.Errorf("Expected 1 receipt, got %d", len(receipts))
	}

	if receipts[0].ID != 1 {
		t.Errorf("Expected receipt ID to be 1, got %d", receipts[0].ID)
	}
}

func TestGetSubscribes(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/subscribes" {
			t.Errorf("Expected to request ‘/v2/subscribes’, got ‘%s’", r.URL.Path)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintln(w, `{"items":[{"id":1,"description":"Подписка","status":1}]}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	subscribes, err := client.GetSubscribes()
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}

	if len(subscribes) != 1 {
		t.Errorf("Expected 1 subscribe, got %d", len(subscribes))
	}

	if subscribes[0].ID != 1 {
		t.Errorf("Expected subscribe ID to be 1, got %d", subscribes[0].ID)
	}
}

func TestCancelSubscribe(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/subscribes/1" {
			t.Errorf("Expected to request ‘/v2/subscribes/1’, got ‘%s’", r.URL.Path)
		}
		if r.Method != "DELETE" {
			t.Errorf("Expected method to be ‘DELETE’, got ‘%s’", r.Method)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintln(w, `{"success":true}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	err := client.CancelSubscribe(1)
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}
}

func TestGetShopBalances(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v2/shops/1/balances" {
			t.Errorf("Expected to request ‘/v2/shops/1/balances’, got ‘%s’", r.URL.Path)
		}
		if r.Header.Get("Authorization") != "Bearer test_api_key" {
			t.Errorf("Expected Authorization header to be ‘Bearer test_api_key’, got ‘%s’", r.Header.Get("Authorization"))
		}
		w.WriteHeader(http.StatusOK)
		fmt.Fprintln(w, `{"success":true,"data":{"balances":[{"shop_id":1,"currency":"rub","balance":1000.50,"hold":200,"available":800.50}]}}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	balances, err := client.GetShopBalances()
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}

	if len(balances) != 1 {
		t.Errorf("Expected 1 balance, got %d", len(balances))
	}

	if balances[0].ShopID != 1 {
		t.Errorf("Expected shop ID to be 1, got %d", balances[0].ShopID)
	}
}

func TestHandleCallback(t *testing.T) {
	body := `{"id":12345,"amount":100.50,"currency":"RUB","uuid":"external-uuid-123","payment_status":"success"}`
	req := httptest.NewRequest("POST", "/callback", bytes.NewBufferString(body))

	cb, err := HandleCallback(req)
	if err != nil {
		t.Errorf("Unexpected error: %v", err)
	}

	if cb.ID != 12345 {
		t.Errorf("Expected ID to be 12345, got %d", cb.ID)
	}
	if cb.Amount != 100.50 {
		t.Errorf("Expected amount to be 100.50, got %f", cb.Amount)
	}
	if cb.Currency != "RUB" {
		t.Errorf("Expected currency to be ‘RUB’, got ‘%s’", cb.Currency)
	}
	if cb.UUID != "external-uuid-123" {
		t.Errorf("Expected UUID to be ‘external-uuid-123’, got ‘%s’", cb.UUID)
	}
	if cb.PaymentStatus != "success" {
		t.Errorf("Expected payment status to be ‘success’, got ‘%s’", cb.PaymentStatus)
	}
}

func TestCreatePayment_Error(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusUnauthorized)
		fmt.Fprintln(w, `{"error":"unauthorized","status":401}`)
	}))
	defer server.Close()

	client := newTestClient("test_api_key", 1, server.URL)

	req := &PaymentRequest{
		Currency:    "rub",
		Amount:      "1000.50",
		UUID:        "invoice_123",
		Description: "Покупка булочек",
	}

	_, err := client.CreatePayment(req)
	if err == nil {
		t.Error("Expected error, got nil")
	}

	expectedError := "error from API: unauthorized (status: 401)"
	if !strings.Contains(err.Error(), expectedError) {
		t.Errorf("Expected error to contain ‘%s’, got ‘%s’", expectedError, err.Error())
	}
}
