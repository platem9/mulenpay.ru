import MulenPaySDK from '../index.js';
import axios from 'axios';

jest.mock('axios');

describe('MulenPaySDK', () => {
  let sdk;
  const apiKey = 'test-api-key';
  const shopId = 123;
  const secretKey = 'test-secret-key';

  beforeEach(() => {
    sdk = new MulenPaySDK(apiKey, shopId, secretKey);
  });

  it('should create a payment successfully', async () => {
    const paymentData = {
      currency: 'rub',
      amount: '100.50',
      uuid: 'test-uuid',
      description: 'Test payment',
      items: [
        {
          description: 'Product 1',
          price: 50,
          quantity: 1,
          vat_code: 0,
          payment_subject: 1,
          payment_mode: 1
        }
      ]
    };

    const mockResponse = {
      success: true,
      paymentUrl: 'https://mulenpay.ru/payment-url',
      id: 1
    };

    const mockPost = jest.fn().mockResolvedValue({ data: mockResponse });
    axios.create.mockReturnValue({ post: mockPost });

    sdk = new MulenPaySDK(apiKey, shopId, secretKey); 

    const result = await sdk.createPayment(paymentData);

    expect(result).toEqual(mockResponse);
    expect(mockPost).toHaveBeenCalledWith('/v2/payments', expect.any(Object));
  });
});
