import { Router } from 'express';
import Joi from 'joi';
import InSalesClient from '../clients/insales.js';

const router = Router();

const callbackSchema = Joi.object({
  id: Joi.number().integer().required(),
  amount: Joi.number().positive().required(),
  currency: Joi.string().required(),
  uuid: Joi.alternatives(Joi.string(), Joi.number()).required(),
  payment_status: Joi.string().valid('success', 'cancel').required()
});

router.post('/mulenpay', async (req, res) => {
  const { value, error } = callbackSchema.validate(req.body, { abortEarly: false });
  if (error) {
    return res.status(400).json({ error: 'validation_error', details: error.details.map(d => d.message) });
  }

  const { id, amount, uuid, payment_status } = value;

  try {
    if (payment_status !== 'success') {
      // We acknowledge non-success callbacks without changing order state
      return res.json({ ok: true, message: 'Callback received, no action for status', status: payment_status });
    }

    const orderId = String(uuid);
    const ins = new InSalesClient({});
    const result = await ins.createTransaction(orderId, {
      amount,
      external_id: `mulenpay_${id}`,
      gateway: 'MulenPay'
    });

    return res.json({ ok: true, orderId, result });
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error('Webhook processing error:', e);
    return res.status(500).json({ error: 'server_error', message: e.message });
  }
});

export default router;
