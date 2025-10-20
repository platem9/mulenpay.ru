import { Router } from 'express';
import Joi from 'joi';
import MulenPayClient from '../clients/mulenpay.js';

const router = Router();

const itemSchema = Joi.object({
  description: Joi.string().required(),
  quantity: Joi.number().positive().required(),
  price: Joi.number().positive().required(),
  vat_code: Joi.number().valid(0,1,2,3,4,5,6,7).required(),
  payment_subject: Joi.number().valid(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,19,20,21,22,23,24,25,26).required(),
  payment_mode: Joi.number().valid(1,2,3,4,5,6,7).required(),
  product_code: Joi.string().optional().allow(null,''),
  country_of_origin_code: Joi.string().optional().allow(null,''),
  customs_declaration_number: Joi.string().optional().allow(null,''),
  excise: Joi.string().optional().allow(null,''),
  measurement_unit: Joi.number().optional()
});

const createSchema = Joi.object({
  order_id: Joi.alternatives(Joi.string(), Joi.number()).required(),
  amount: Joi.number().positive().required(),
  currency: Joi.string().valid('rub').default('rub'),
  description: Joi.string().default('Оплата заказа'),
  website_url: Joi.string().uri().optional(),
  language: Joi.string().valid('ru','en').default('ru'),
  items: Joi.array().items(itemSchema).optional()
});

router.post('/create', async (req, res) => {
  try {
    const cfgErrors = [];
    if (!process.env.MULENPAY_API_KEY) cfgErrors.push('MULENPAY_API_KEY');
    if (!process.env.MULENPAY_SHOP_ID) cfgErrors.push('MULENPAY_SHOP_ID');
    if (!process.env.MULENPAY_SECRET) cfgErrors.push('MULENPAY_SECRET');
    if (cfgErrors.length) {
      return res.status(500).json({ error: 'MulenPay config missing', missing: cfgErrors });
    }

    const { value, error } = createSchema.validate(req.body, { abortEarly: false });
    if (error) {
      return res.status(400).json({ error: 'validation_error', details: error.details.map(d => d.message) });
    }
    const { order_id, amount, currency, description, website_url, language } = value;

    const items = (value.items && value.items.length)
      ? value.items
      : [{
          description: description || `Заказ #${order_id}`,
          quantity: 1,
          price: Number(amount),
          vat_code: 0,
          payment_subject: 1,
          payment_mode: 4
        }];

    const mp = new MulenPayClient({});
    const data = await mp.createPayment({
      currency,
      amount: Number(amount).toFixed(2),
      uuid: String(order_id),
      description: description || `Оплата заказа #${order_id}`,
      website_url,
      language,
      items
    });

    return res.status(201).json({ success: true, paymentUrl: data.paymentUrl, id: data.id });
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error('Create payment error:', e);
    return res.status(500).json({ error: 'server_error', message: e.message });
  }
});

router.get('/pay', async (req, res) => {
  try {
    const { order_id, amount, currency = 'rub', description, website_url, language = 'ru' } = req.query;
    if (!order_id || !amount) {
      return res.status(400).send('Missing required query params: order_id, amount');
    }

    const mp = new MulenPayClient({});
    const data = await mp.createPayment({
      currency,
      amount: Number(amount).toFixed(2),
      uuid: String(order_id),
      description: description || `Оплата заказа #${order_id}`,
      website_url,
      language,
      items: [{
        description: description || `Заказ #${order_id}`,
        quantity: 1,
        price: Number(amount),
        vat_code: 0,
        payment_subject: 1,
        payment_mode: 4
      }]
    });

    return res.redirect(302, data.paymentUrl);
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error('Pay redirect error:', e);
    return res.status(500).send('Server error');
  }
});

export default router;
