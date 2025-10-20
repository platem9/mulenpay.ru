import axios from 'axios';
import crypto from 'crypto';

function sha1Hex(str) {
  return crypto.createHash('sha1').update(str).digest('hex');
}

export default class MulenPayClient {
  constructor({ apiKey, baseURL, shopId, secret }) {
    this.apiKey = apiKey || process.env.MULENPAY_API_KEY;
    this.baseURL = baseURL || process.env.MULENPAY_BASE_URL || 'https://mulenpay.ru/api';
    this.shopId = Number(shopId || process.env.MULENPAY_SHOP_ID);
    this.secret = secret || process.env.MULENPAY_SECRET;
    this.http = axios.create({
      baseURL: this.baseURL,
      headers: {
        Authorization: `Bearer ${this.apiKey}`,
        'Content-Type': 'application/json'
      },
      timeout: 10000
    });
  }

  buildSign({ currency, amount, shopId }) {
    const sId = shopId ?? this.shopId;
    if (!currency || !amount || !sId || !this.secret) {
      throw new Error('Missing params for sign (need currency, amount, shopId, secret)');
    }
    // OpenAPI: sign = sha1(concat(currency, amount, shopId, secret))
    return sha1Hex(`${currency}${amount}${sId}${this.secret}`);
  }

  async createPayment({
    currency = 'rub',
    amount,
    uuid,
    description,
    website_url,
    subscribe = null,
    holdTime = null,
    language = 'ru',
    items = []
  }) {
    const payload = {
      currency,
      amount: String(amount),
      uuid,
      shopId: this.shopId,
      description,
      website_url,
      subscribe,
      holdTime,
      language,
      items
    };
    payload.sign = this.buildSign({ currency: payload.currency, amount: payload.amount, shopId: payload.shopId });

    const { data, status } = await this.http.post('/v2/payments', payload);
    if (status !== 201) {
      throw new Error(`Unexpected response from MulenPay: ${status}`);
    }
    return data; // { success, paymentUrl, id }
  }

  async getPayment(id) {
    const { data } = await this.http.get(`/v2/payments/${id}`);
    return data; // { success, payment }
  }

  async listPayments(params = {}) {
    const { data } = await this.http.get('/v2/payments', { params });
    return data; // { items: Payment[] }
  }
}
