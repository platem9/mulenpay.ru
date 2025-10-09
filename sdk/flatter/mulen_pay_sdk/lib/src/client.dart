import 'dart:convert';
import 'package:crypto/crypto.dart';
import 'package:http/http.dart' as http;

import 'exceptions.dart';
import 'models/balance.dart';
import 'models/payment.dart';
import 'models/receipt.dart';
import 'models/subscribe.dart';

class MulenPay {
  final String apiKey;
  final String secretKey;
  final String _baseUrl = 'https://mulenpay.ru/api/v2';
  final http.Client _client;

  MulenPay({required this.apiKey, required this.secretKey, http.Client? client}) : _client = client ?? http.Client();

  String generateSignature(String currency, String amount, int shopId) {
    final data = '$currency$amount$shopId$secretKey';
    return sha1.convert(utf8.encode(data)).toString();
  }

  Future<http.Response> _post(String endpoint, Map<String, dynamic> body) async {
    final url = Uri.parse('$_baseUrl/$endpoint');
    final headers = {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $apiKey',
    };
    final response = await _client.post(url, headers: headers, body: json.encode(body));
    if (response.statusCode >= 400) {
      throw ApiException(response.statusCode, response.body);
    }
    return response;
  }

  Future<http.Response> _get(String endpoint) async {
    final url = Uri.parse('$_baseUrl/$endpoint');
    final headers = {
      'Authorization': 'Bearer $apiKey',
    };
    final response = await _client.get(url, headers: headers);
    if (response.statusCode >= 400) {
      throw ApiException(response.statusCode, response.body);
    }
    return response;
  }
  
    Future<http.Response> _put(String endpoint) async {
    final url = Uri.parse('$_baseUrl/$endpoint');
    final headers = {
      'Authorization': 'Bearer $apiKey',
    };
    final response = await _client.put(url, headers: headers);
    if (response.statusCode >= 400) {
      throw ApiException(response.statusCode, response.body);
    }
    return response;
  }
  
  Future<http.Response> _delete(String endpoint) async {
    final url = Uri.parse('$_baseUrl/$endpoint');
    final headers = {
      'Authorization': 'Bearer $apiKey',
    };
    final response = await _client.delete(url, headers: headers);
    if (response.statusCode >= 400) {
      throw ApiException(response.statusCode, response.body);
    }
    return response;
  }

  Future<Map<String, dynamic>> createPayment(PaymentRequest paymentRequest) async {
    final response = await _post('payments', paymentRequest.toJson());
    return json.decode(response.body);
  }

  Future<List<Payment>> getPayments() async {
    final response = await _get('payments');
    final data = json.decode(response.body);
    return (data['items'] as List).map((item) => Payment.fromJson(item)).toList();
  }

  Future<Payment> getPayment(int id) async {
    final response = await _get('payments/$id');
    final data = json.decode(response.body);
    return Payment.fromJson(data['payment']);
  }

  Future<void> holdPayment(int id) async {
    await _put('payments/$id/hold');
  }

  Future<void> cancelHoldPayment(int id) async {
    await _delete('payments/$id/hold');
  }

  Future<void> refundPayment(int id) async {
    await _put('payments/$id/refund');
  }

  Future<List<Receipt>> getReceipt(int id) async {
    final response = await _get('payments/$id/receipt');
    final data = json.decode(response.body);
    return (data['items'] as List).map((item) => Receipt.fromJson(item)).toList();
  }
  
  Future<List<Subscribe>> getSubscribes() async {
    final response = await _get('subscribes');
    final data = json.decode(response.body);
    return (data['items'] as List).map((item) => Subscribe.fromJson(item)).toList();
  }
  
  Future<void> cancelSubscribe(int id) async {
    await _delete('subscribes/$id');
  }
  
  Future<List<Balance>> getBalances(int shopId) async {
    final response = await _get('shops/$shopId/balances');
    final data = json.decode(response.body);
    return (data['data']['balances'] as List).map((item) => Balance.fromJson(item)).toList();
  }
}
