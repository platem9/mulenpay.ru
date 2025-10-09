import 'dart:convert';

import 'package:mulen_pay_sdk/src/client.dart';
import 'package:mulen_pay_sdk/src/models/payment.dart';
import 'package:test/test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

void main() {
  group('MulenPay', () {
    late MulenPay mulenPay;
    late MockClient client;

    setUp(() {
      client = MockClient((request) async {
        if (request.url.path.endsWith('/payments')) {
          final body = json.decode(request.body);
          if (body['sign'] == 'a2e1a3e6f9b8a2b2c9d6b9a5c8e3a2d2f3a6b5c3') {
            return http.Response(json.encode({'success': true, 'paymentUrl': 'https://example.com', 'id': 1}), 201);
          }
        }
        return http.Response('Not Found', 404);
      });
      mulenPay = MulenPay(apiKey: 'test_api_key', secretKey: 'test_secret_key', client: client);
    });

    test('generateSignature', () {
      final signature = mulenPay.generateSignature('rub', '1000.50', 5);
      expect(signature, 'a2e1a3e6f9b8a2b2c9d6b9a5c8e3a2d2f3a6b5c3');
    });

    test('createPayment', () async {
      final paymentRequest = PaymentRequest(
        currency: 'rub',
        amount: '1000.50',
        uuid: 'test_uuid',
        shopId: 5,
        description: 'Test payment',
        items: [
          Item(
            description: 'Test item',
            quantity: 1,
            price: 1000.50,
            vatCode: 0,
            paymentSubject: 1,
            paymentMode: 1,
          ),
        ],
        sign: mulenPay.generateSignature('rub', '1000.50', 5),
      );

      final response = await mulenPay.createPayment(paymentRequest);
      expect(response['success'], true);
      expect(response['paymentUrl'], 'https://example.com');
      expect(response['id'], 1);
    });
  });
}
