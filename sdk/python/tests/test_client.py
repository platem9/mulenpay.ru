import unittest
from unittest.mock import patch, MagicMock

from mulen_pay_sdk import MulenPayClient


class TestMulenPayClient(unittest.TestCase):

    def setUp(self):
        self.api_key = "test_api_key"
        self.client = MulenPayClient(api_key=self.api_key)

    @patch('requests.Session.request')
    def test_create_payment_success(self, mock_request):
        mock_response = MagicMock()
        mock_response.status_code = 201
        mock_response.json.return_value = {"success": True, "id": 1}
        mock_request.return_value = mock_response

        payment_data = {
            "currency": "rub",
            "amount": 1000.50,
            "shop_id": 5,
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
        response = self.client.create_payment(**payment_data)

        self.assertEqual(response, {"success": True, "id": 1})

    @patch('requests.Session.request')
    def test_get_payments_success(self, mock_request):
        mock_response = MagicMock()
        mock_response.status_code = 200
        mock_response.json.return_value = {"items": []}
        mock_request.return_value = mock_response

        response = self.client.get_payments()

        self.assertEqual(response, {"items": []})
