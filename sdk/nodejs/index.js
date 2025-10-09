import axios from 'axios';
import crypto from 'crypto';

class MulenPaySDK {
  constructor(apiKey, shopId, secretKey) {
    if (!apiKey) {
      throw new Error('API key is required.');
    }
    if (!shopId) {
      throw new Error('Shop ID is required.');
    }
    if (!secretKey) {
      throw new Error('Secret key is required.');
    }

    this.apiKey = apiKey;
    this.shopId = shopId;
    this.secretKey = secretKey;
    this.baseURL = 'https://mulenpay.ru/api';
    
    this.api = axios.create({
      baseURL: this.baseURL,
      headers: {
        'Authorization': `Bearer ${this.apiKey}`,
        'Content-Type': 'application/json'
      }
    });
  }

  generateSignature(data) {
    const { currency, amount, shopId, uuid } = data;
    const signString = `${currency}${amount}${shopId}${uuid}${this.secretKey}`;
    return crypto.createHash('sha1').update(signString).digest('hex');
  }

  async createPayment(paymentData) {
    const dataWithShopId = { ...paymentData, shopId: this.shopId };
    const signature = this.generateSignature(dataWithShopId);
    const requestData = { ...dataWithShopId, sign: signature };

    try {
      const response = await this.api.post('/v2/payments', requestData);
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }

  async getPayments() {
    try {
      const response = await this.api.get('/v2/payments');
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }

  async getPayment(id) {
    try {
      const response = await this.api.get(`/v2/payments/${id}`);
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }

  async confirmHold(id) {
    try {
      const response = await this.api.put(`/v2/payments/${id}/hold`);
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }

  async cancelHold(id) {
    try {
      const response = await this.api.delete(`/v2/payments/${id}/hold`);
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }

  async refundPayment(id) {
    try {
      const response = await this.api.put(`/v2/payments/${id}/refund`);
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }

  async getReceipt(id) {
    try {
      const response = await this.api.get(`/v2/payments/${id}/receipt`);
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }

  async getSubscriptions() {
    try {
      const response = await this.api.get('/v2/subscribes');
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }

  async cancelSubscription(id) {
    try {
      const response = await this.api.delete(`/v2/subscribes/${id}`);
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }

  async getShopBalances(shopId) {
    try {
      const response = await this.api.get(`/v2/shops/${shopId}/balances`);
      return response.data;
    } catch (error) {
      throw error.response ? error.response.data : new Error(error.message);
    }
  }
}

export default MulenPaySDK;
