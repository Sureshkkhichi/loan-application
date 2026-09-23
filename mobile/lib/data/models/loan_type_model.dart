class LoanTypeModel {
  final int id;
  final String name;
  final String code;
  final String? description;
  final double minAmount;
  final double maxAmount;

  LoanTypeModel({
    required this.id,
    required this.name,
    required this.code,
    this.description,
    required this.minAmount,
    required this.maxAmount,
  });

  factory LoanTypeModel.fromJson(Map<String, dynamic> json) {
    return LoanTypeModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? '',
      code: json['code'] ?? '',
      description: json['description'],
      minAmount: double.tryParse(json['min_amount'].toString()) ?? 10000.0,
      maxAmount: double.tryParse(json['max_amount'].toString()) ?? 5000000.0,
    );
  }
}
