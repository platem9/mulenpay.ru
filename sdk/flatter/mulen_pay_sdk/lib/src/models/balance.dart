class Balance {
  final int shopId;
  final String currency;
  final double balance;
  final double hold;
  final double available;

  Balance({
    required this.shopId,
    required this.currency,
    required this.balance,
    required this.hold,
    required this.available,
  });

  factory Balance.fromJson(Map<String, dynamic> json) {
    return Balance(
      shopId: json['shop_id'],
      currency: json['currency'],
      balance: json['balance'],
      hold: json['hold'],
      available: json['available'],
    );
  }
}
