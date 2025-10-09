import hashlib
import json
from typing import Dict, Any, List

import requests

from .exceptions import MulenPayException


class MulenPayClient:
    BASE_URL = "https://mulenpay.ru/api"

    def __init__(self, api_key: str):
        self.api_key = api_key
        self.session = requests.Session()
        self.session.headers.update({"Authorization": f"Bearer {self.api_key}"})

    def _request(self, method: str, path: str, **kwargs) -> Dict[str, Any]:
        url = f"{self.BASE_URL}{path}"
        try:
            response = self.session.request(method, url, **kwargs)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            raise MulenPayException(f"API request failed: {e}")

    def create_payment(self, currency: str, amount: float, shop_id: int, description: str, uuid: str,
                       items: List[Dict[str, Any]], **kwargs) -> Dict[str, Any]:
        path = "/v2/payments"
        secret_key = "test"  # Заменить на реальный ключ
        sign = hashlib.sha1(f"{currency}{amount}{shop_id}{secret_key}".encode()).hexdigest()
        data = {
            "currency": currency,
            "amount": amount,
            "shopId": shop_id,
            "description": description,
            "uuid": uuid,
            "items": items,
            "sign": sign,
            **kwargs
        }
        return self._request("post", path, json=data)

    def get_payments(self) -> Dict[str, Any]:
        path = "/v2/payments"
        return self._request("get", path)

    def get_payment(self, payment_id: int) -> Dict[str, Any]:
        path = f"/v2/payments/{payment_id}"
        return self._request("get", path)

    def hold_payment(self, payment_id: int) -> Dict[str, Any]:
        path = f"/v2/payments/{payment_id}/hold"
        return self._request("put", path)

    def cancel_hold_payment(self, payment_id: int) -> Dict[str, Any]:
        path = f"/v2/payments/{payment_id}/hold"
        return self._request("delete", path)

    def refund_payment(self, payment_id: int) -> Dict[str, Any]:
        path = f"/v2/payments/{payment_id}/refund"
        return self._request("put", path)

    def get_receipt(self, payment_id: int) -> Dict[str, Any]:
        path = f"/v2/payments/{payment_id}/receipt"
        return self._request("get", path)

    def get_subscribes(self) -> Dict[str, Any]:
        path = "/v2/subscribes"
        return self._request("get", path)

    def cancel_subscribe(self, subscribe_id: int) -> Dict[str, Any]:
        path = f"/v2/subscribes/{subscribe_id}"
        return self._request("delete", path)

    def get_shop_balances(self, shop_id: int) -> Dict[str, Any]:
        path = f"/v2/shops/{shop_id}/balances"
        return self._request("get", path)

    def send_callback(self, data: Dict[str, Any]) -> Dict[str, Any]:
        path = "/payments/callback"
        return self._request("post", path, json=data)
