class PaymentRequest {
  final String currency;
  final String amount;
  final String uuid;
  final int shopId;
  final String description;
  final String? websiteUrl;
  final String? subscribe;
  final int? holdTime;
  final String? language;
  final List<Item> items;
  final String sign;

  PaymentRequest({
    required this.currency,
    required this.amount,
    required this.uuid,
    required this.shopId,
    required this.description,
    this.websiteUrl,
    this.subscribe,
    this.holdTime,
    this.language,
    required this.items,
    required this.sign,
  });

  Map<String, dynamic> toJson() {
    return {
      'currency': currency,
      'amount': amount,
      'uuid': uuid,
      'shopId': shopId,
      'description': description,
      'website_url': websiteUrl,
      'subscribe': subscribe,
      'holdTime': holdTime,
      'language': language,
      'items': items.map((item) => item.toJson()).toList(),
      'sign': sign,
    };
  }
}

class Item {
  final String description;
  final double quantity;
  final double price;
  final int vatCode;
  final int paymentSubject;
  final int paymentMode;
  final String? productCode;
  final String? countryOfOriginCode;
  final String? customsDeclarationNumber;
  final String? excise;
  final int? measurementUnit;

  Item({
    required this.description,
    required this.quantity,
    required this.price,
    required this.vatCode,
    required this.paymentSubject,
    required this.paymentMode,
    this.productCode,
    this.countryOfOriginCode,
    this.customsDeclarationNumber,
    this.excise,
    this.measurementUnit,
  });

  Map<String, dynamic> toJson() {
    return {
      'description': description,
      'quantity': quantity,
      'price': price,
      'vat_code': vatCode,
      'payment_subject': paymentSubject,
      'payment_mode': paymentMode,
      'product_code': productCode,
      'country_of_origin_code': countryOfOriginCode,
      'customs_declaration_number': customsDeclarationNumber,
      'excise': excise,
      'measurement_unit': measurementUnit,
    };
  }
}

class Payment {
  final int id;
  final String uuid;
  final String amount;
  final String currency;
  final String description;
  final int status;

  Payment({
    required this.id,
    required this.uuid,
    required this.amount,
    required this.currency,
    required this.description,
    required this.status,
  });

  factory Payment.fromJson(Map<String, dynamic> json) {
    return Payment(
      id: json['id'],
      uuid: json['uuid'],
      amount: json['amount'],
      currency: json['currency'],
      description: json['description'],
      status: json['status'],
    );
  }
}
