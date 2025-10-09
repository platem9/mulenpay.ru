class Subscribe {
  final int id;
  final String description;
  final int status;
  final String currency;
  final String amount;
  final String startDate;
  final String nextPayDate;
  final String interval;

  Subscribe({
    required this.id,
    required this.description,
    required this.status,
    required this.currency,
    required this.amount,
    required this.startDate,
    required this.nextPayDate,
    required this.interval,
  });

  factory Subscribe.fromJson(Map<String, dynamic> json) {
    return Subscribe(
      id: json['id'],
      description: json['description'],
      status: json['status'],
      currency: json['currency'],
      amount: json['amount'],
      startDate: json['start_date'],
      nextPayDate: json['next_pay_date'],
      interval: json['interval'],
    );
  }
}
