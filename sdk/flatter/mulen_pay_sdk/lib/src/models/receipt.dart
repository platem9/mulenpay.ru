class Receipt {
  final int id;
  final String? daemonCode;
  final String? deviceCode;
  final List<String> warnings;
  final List<String> error;
  final String? ecrRegistrationNumber;
  final int? fiscalDocumentAttribute;
  final int? fiscalDocumentNumber;
  final int? fiscalReceiptNumber;
  final String? fnNumber;
  final String? fnsSite;
  final String? receiptDatetime;
  final int? shiftNumber;
  final double total;
  final String? ofdInn;
  final String? ofdReceiptUrl;
  final String status;
  final String uuid;
  final String createdAt;
  final String updatedAt;

  Receipt({
    required this.id,
    this.daemonCode,
    this.deviceCode,
    required this.warnings,
    required this.error,
    this.ecrRegistrationNumber,
    this.fiscalDocumentAttribute,
    this.fiscalDocumentNumber,
    this.fiscalReceiptNumber,
    this.fnNumber,
    this.fnsSite,
    this.receiptDatetime,
    this.shiftNumber,
    required this.total,
    this.ofdInn,
    this.ofdReceiptUrl,
    required this.status,
    required this.uuid,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Receipt.fromJson(Map<String, dynamic> json) {
    return Receipt(
      id: json['id'],
      daemonCode: json['daemon_code'],
      deviceCode: json['device_code'],
      warnings: List<String>.from(json['warnings']),
      error: List<String>.from(json['error']),
      ecrRegistrationNumber: json['ecr_registration_number'],
      fiscalDocumentAttribute: json['fiscal_document_attribute'],
      fiscalDocumentNumber: json['fiscal_document_number'],
      fiscalReceiptNumber: json['fiscal_receipt_number'],
      fnNumber: json['fn_number'],
      fnsSite: json['fns_site'],
      receiptDatetime: json['receipt_datetime'],
      shiftNumber: json['shift_number'],
      total: json['total'],
      ofdInn: json['ofd_inn'],
      ofdReceiptUrl: json['ofd_receipt_url'],
      status: json['status'],
      uuid: json['uuid'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
    );
  }
}
