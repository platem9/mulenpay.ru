import axios from 'axios';

export default class InSalesClient {
  constructor({ domain, apiKey, password }) {
    this.domain = domain || process.env.INSALES_DOMAIN; // e.g., yourshop.myinsales.ru
    this.apiKey = apiKey || process.env.INSALES_API_KEY;
    this.password = password || process.env.INSALES_PASSWORD;

    if (!this.domain || !this.apiKey || !this.password) {
      // Allow instantiation; we'll throw on use for clearer error
    }

    const baseURL = `https://${this.domain}`;
    this.http = axios.create({
      baseURL,
      auth: {
        username: this.apiKey,
        password: this.password
      },
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      },
      timeout: 10000
    });
  }

  async createTransaction(orderId, { amount, external_id, gateway = 'MulenPay' }) {
    if (!this.domain || !this.apiKey || !this.password) {
      throw new Error('InSales credentials are not configured');
    }
    const payload = {
      transaction: {
        kind: 'sale',
        status: 'success',
        amount: Number(amount),
        gateway,
        external_id
      }
    };
    try {
      const { data } = await this.http.post(`/admin/orders/${orderId}/transactions.json`, payload);
      return { ok: true, data };
    } catch (err) {
      // Fallback: try to mark order as paid
      try {
        const { data } = await this.http.put(`/admin/orders/${orderId}.json`, { order: { paid: true } });
        return { ok: true, data, fallback: true };
      } catch (e2) {
        const details = err.response?.data || err.message;
        throw new Error(`InSales transaction failed: ${JSON.stringify(details)}`);
      }
    }
  }
}
