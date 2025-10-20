import express from 'express';
import morgan from 'morgan';
import dotenv from 'dotenv';
import paymentsRouter from './routes/payments.js';
import webhooksRouter from './routes/webhooks.js';

dotenv.config();

const app = express();
app.use(express.json({ limit: '1mb' }));
app.use(morgan(process.env.LOG_FORMAT || 'dev'));

app.get('/health', (req, res) => {
  res.json({ status: 'ok', time: new Date().toISOString() });
});

app.use('/payments', paymentsRouter);
app.use('/webhooks', webhooksRouter);

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  // eslint-disable-next-line no-console
  console.log(`MulenPay InSales integration listening on port ${PORT}`);
});
