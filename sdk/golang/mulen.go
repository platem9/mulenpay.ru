package mulen

import (
	"bytes"
	"crypto/sha1"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strconv"
)

const (
	apiURL = "https://mulenpay.ru/api"
)

// Client is the API client
type Client struct {
	apiKey     string
	shopID     int
	apiURL     string
	httpClient *http.Client
}

// NewClient creates a new API client
func NewClient(apiKey string, shopID int) *Client {
	return &Client{
		apiKey:     apiKey,
		shopID:     shopID,
		apiURL:     apiURL,
		httpClient: &http.Client{},
	}
}

// newTestClient creates a new API client for testing
func newTestClient(apiKey string, shopID int, apiURL string) *Client {
	return &Client{
		apiKey:     apiKey,
		shopID:     shopID,
		apiURL:     apiURL,
		httpClient: &http.Client{},
	}
}

// PaymentRequest represents the request to create a payment
type PaymentRequest struct {
	Currency      string  `json:"currency"`
	Amount        string  `json:"amount"`
	UUID          string  `json:"uuid"`
	ShopID        int     `json:"shopId"`
	Description   string  `json:"description"`
	WebsiteURL    string  `json:"website_url,omitempty"`
	Subscribe     string  `json:"subscribe,omitempty"`
	HoldTime      int     `json:"holdTime,omitempty"`
	Language      string  `json:"language,omitempty"`
	Items         []Item  `json:"items"`
	Sign          string  `json:"sign"`
}

// Item represents an item in a payment request
type Item struct {
	Description              string  `json:"description"`
	Quantity                 float64 `json:"quantity"`
	Price                    float64 `json:"price"`
	VatCode                  int     `json:"vat_code"`
	PaymentSubject           int     `json:"payment_subject"`
	PaymentMode              int     `json:"payment_mode"`
	ProductCode              string  `json:"product_code,omitempty"`
	CountryOfOriginCode      string  `json:"country_of_origin_code,omitempty"`
	CustomsDeclarationNumber string  `json:"customs_declaration_number,omitempty"`
	Excise                   string  `json:"excise,omitempty"`
	MeasurementUnit          int     `json:"measurement_unit,omitempty"`
}

// Payment represents a payment
type Payment struct {
	ID          int     `json:"id"`
	UUID        string  `json:"uuid"`
	Amount      string  `json:"amount"`
	Currency    string  `json:"currency"`
	Description string  `json:"description"`
	Status      int     `json:"status"`
}

// PaymentResponse represents the response for a create payment request
type PaymentResponse struct {
	Success    bool   `json:"success"`
	PaymentURL string `json:"paymentUrl"`
	ID         int    `json:"id"`
}

// GetPaymentResponse represents the response for a get payment request
type GetPaymentResponse struct {
	Success bool     `json:"success"`
	Payment *Payment `json:"payment"`
}

// ErrorResponse represents an error response from the API
type ErrorResponse struct {
	Error  string `json:"error"`
	Status int    `json:"status"`
}

// CreatePayment creates a new payment
func (c *Client) CreatePayment(req *PaymentRequest) (*PaymentResponse, error) {
	req.ShopID = c.shopID
	req.Sign = c.generateSign(req.Currency, req.Amount, c.shopID)

	body, err := json.Marshal(req)
	if err != nil {
		return nil, err
	}

	httpReq, err := http.NewRequest("POST", fmt.Sprintf("%s/v2/payments", c.apiURL), bytes.NewBuffer(body))
	if err != nil {
		return nil, err
	}

	httpReq.Header.Set("Content-Type", "application/json")
	httpReq.Header.Set("Authorization", "Bearer "+c.apiKey)

	resp, err := c.httpClient.Do(httpReq)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusCreated {
		var errResp ErrorResponse
		if err := json.NewDecoder(resp.Body).Decode(&errResp); err != nil {
			return nil, fmt.Errorf("unexpected status code: %d", resp.StatusCode)
		}
		return nil, fmt.Errorf("error from API: %s (status: %d)", errResp.Error, resp.StatusCode)
	}

	var result PaymentResponse
	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return nil, err
	}

	return &result, nil
}

func (c *Client) generateSign(currency string, amount string, shopId int) string {
	hash := sha1.New()
	io.WriteString(hash, currency)
	io.WriteString(hash, amount)
	io.WriteString(hash, strconv.Itoa(shopId))
	io.WriteString(hash, c.apiKey)
	return fmt.Sprintf("%x", hash.Sum(nil))
}

// GetPayments retrieves a list of payments
func (c *Client) GetPayments() ([]Payment, error) {
	httpReq, err := http.NewRequest("GET", fmt.Sprintf("%s/v2/payments", c.apiURL), nil)
	if err != nil {
		return nil, err
	}

	httpReq.Header.Set("Authorization", "Bearer "+c.apiKey)

	resp, err := c.httpClient.Do(httpReq)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		var errResp ErrorResponse
		if err := json.NewDecoder(resp.Body).Decode(&errResp); err != nil {
			return nil, fmt.Errorf("unexpected status code: %d", resp.StatusCode)
		}
		return nil, fmt.Errorf("error from API: %s (status: %d)", errResp.Error, resp.StatusCode)
	}

	var result struct {
		Items []Payment `json:"items"`
	}

	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return nil, err
	}

	return result.Items, nil
}

// GetPayment retrieves a payment by its ID
func (c *Client) GetPayment(id int) (*Payment, error) {
	httpReq, err := http.NewRequest("GET", fmt.Sprintf("%s/v2/payments/%d", c.apiURL, id), nil)
	if err != nil {
		return nil, err
	}

	httpReq.Header.Set("Authorization", "Bearer "+c.apiKey)

	resp, err := c.httpClient.Do(httpReq)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		var errResp ErrorResponse
		if err := json.NewDecoder(resp.Body).Decode(&errResp); err != nil {
			return nil, fmt.Errorf("unexpected status code: %d", resp.StatusCode)
		}
		return nil, fmt.Errorf("error from API: %s (status: %d)", errResp.Error, resp.StatusCode)

	}

	var result GetPaymentResponse
	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return nil, err
	}

	return result.Payment, nil
}

// HoldPayment holds a payment
func (c *Client) HoldPayment(id int) error {
	return c.performPaymentAction(id, "hold", "PUT")
}

// CancelHold cancels a held payment
func (c *Client) CancelHold(id int) error {
	return c.performPaymentAction(id, "hold", "DELETE")
}

// RefundPayment refunds a payment
func (c *Client) RefundPayment(id int) error {
	return c.performPaymentAction(id, "refund", "PUT")
}

func (c *Client) performPaymentAction(id int, action, method string) error {
	url := fmt.Sprintf("%s/v2/payments/%d/%s", c.apiURL, id, action)
	httpReq, err := http.NewRequest(method, url, nil)
	if err != nil {
		return err
	}

	httpReq.Header.Set("Authorization", "Bearer "+c.apiKey)

	resp, err := c.httpClient.Do(httpReq)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		var errResp ErrorResponse
		if err := json.NewDecoder(resp.Body).Decode(&errResp); err != nil {
			return fmt.Errorf("unexpected status code: %d", resp.StatusCode)
		}
		return fmt.Errorf("error from API: %s (status: %d)", errResp.Error, resp.StatusCode)
	}

	return nil
}

// Receipt represents a receipt
type Receipt struct {
	ID                      int      `json:"id"`
	DaemonCode              string   `json:"daemon_code"`
	DeviceCode              string   `json:"device_code"`
	Warnings                []string `json:"warnings"`
	Error                   []string `json:"error"`
	EcrRegistrationNumber   string   `json:"ecr_registration_number"`
	FiscalDocumentAttribute int      `json:"fiscal_document_attribute"`
	FiscalDocumentNumber    int      `json:"fiscal_document_number"`
	FiscalReceiptNumber     int      `json:"fiscal_receipt_number"`
	FnNumber                string   `json:"fn_number"`
	FnsSite                 string   `json:"fns_site"`
	ReceiptDatetime         string   `json:"receipt_datetime"`
	ShiftNumber             int      `json:"shift_number"`
	Total                   float64  `json:"total"`
	OfdInn                  string   `json:"ofd_inn"`
	OfdReceiptURL           string   `json:"ofd_receipt_url"`
	Status                  string   `json:"status"`
	UUID                    string   `json:"uuid"`
	CreatedAt               string   `json:"created_at"`
	UpdatedAt               string   `json:"updated_at"`
}

// GetReceipt retrieves a receipt for a payment
func (c *Client) GetReceipt(id int) ([]Receipt, error) {
	httpReq, err := http.NewRequest("GET", fmt.Sprintf("%s/v2/payments/%d/receipt", c.apiURL, id), nil)
	if err != nil {
		return nil, err
	}

	httpReq.Header.Set("Authorization", "Bearer "+c.apiKey)

	resp, err := c.httpClient.Do(httpReq)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		var errResp ErrorResponse
		if err := json.NewDecoder(resp.Body).Decode(&errResp); err != nil {
			return nil, fmt.Errorf("unexpected status code: %d", resp.StatusCode)
		}
		return nil, fmt.Errorf("error from API: %s (status: %d)", errResp.Error, resp.StatusCode)
	}

	var result struct {
		Status string    `json:"status"`
		Items  []Receipt `json:"items"`
	}

	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return nil, err
	}

	return result.Items, nil
}

// Subscribe represents a subscription
type Subscribe struct {
	ID          int    `json:"id"`
	Description string `json:"description"`
	Status      int    `json:"status"`
	Currency    string `json:"currency"`
	Amount      string `json:"amount"`
	StartDate   string `json:"start_date"`
	NextPayDate string `json:"next_pay_date"`
	Interval    string `json:"interval"`
}

// GetSubscribes retrieves a list of subscriptions
func (c *Client) GetSubscribes() ([]Subscribe, error) {
	httpReq, err := http.NewRequest("GET", fmt.Sprintf("%s/v2/subscribes", c.apiURL), nil)
	if err != nil {
		return nil, err
	}

	httpReq.Header.Set("Authorization", "Bearer "+c.apiKey)

	resp, err := c.httpClient.Do(httpReq)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		var errResp ErrorResponse
		if err := json.NewDecoder(resp.Body).Decode(&errResp); err != nil {
			return nil, fmt.Errorf("unexpected status code: %d", resp.StatusCode)
		}
		return nil, fmt.Errorf("error from API: %s (status: %d)", errResp.Error, resp.StatusCode)
	}

	var result struct {
		Items []Subscribe `json:"items"`
	}

	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return nil, err
	}

	return result.Items, nil
}

// CancelSubscribe cancels a subscription
func (c *Client) CancelSubscribe(id int) error {
	httpReq, err := http.NewRequest("DELETE", fmt.Sprintf("%s/v2/subscribes/%d", c.apiURL, id), nil)
	if err != nil {
		return err
	}

	httpReq.Header.Set("Authorization", "Bearer "+c.apiKey)

	resp, err := c.httpClient.Do(httpReq)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		var errResp ErrorResponse
		if err := json.NewDecoder(resp.Body).Decode(&errResp); err != nil {
			return fmt.Errorf("unexpected status code: %d", resp.StatusCode)
		}
		return fmt.Errorf("error from API: %s (status: %d)", errResp.Error, resp.StatusCode)
	}

	return nil
}

// Balance represents a shop balance
type Balance struct {
	ShopID    int     `json:"shop_id"`
	Currency  string  `json:"currency"`
	Balance   float64 `json:"balance"`
	Hold      float64 `json:"hold"`
	Available float64 `json:"available"`
}

// GetShopBalances retrieves the balances for a shop
func (c *Client) GetShopBalances() ([]Balance, error) {
	httpReq, err := http.NewRequest("GET", fmt.Sprintf("%s/v2/shops/%d/balances", c.apiURL, c.shopID), nil)
	if err != nil {
		return nil, err
	}

	httpReq.Header.Set("Authorization", "Bearer "+c.apiKey)

	resp, err := c.httpClient.Do(httpReq)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		var errResp ErrorResponse
		if err := json.NewDecoder(resp.Body).Decode(&errResp); err != nil {
			return nil, fmt.Errorf("unexpected status code: %d", resp.StatusCode)
		}
		return nil, fmt.Errorf("error from API: %s (status: %d)", errResp.Error, resp.StatusCode)
	}

	var result struct {
		Success bool `json:"success"`
		Data    struct {
			Balances []Balance `json:"balances"`
		} `json:"data"`
	}

	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return nil, err
	}

	return result.Data.Balances, nil
}

// Callback represents the data sent to the callback URL
type Callback struct {
	ID            int     `json:"id"`
	Amount        float64 `json:"amount"`
	Currency      string  `json:"currency"`
	UUID          string  `json:"uuid"`
	PaymentStatus string  `json:"payment_status"`
}

// HandleCallback parses and validates a callback request.
// It should be used in an http.HandlerFunc.
func HandleCallback(r *http.Request) (*Callback, error) {
	if r.Method != "POST" {
		return nil, fmt.Errorf("invalid method: %s", r.Method)
	}

	var cb Callback
	if err := json.NewDecoder(r.Body).Decode(&cb); err != nil {
		return nil, err
	}

	return &cb, nil
}
