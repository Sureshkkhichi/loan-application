class ActivityModel {
  final int id;
  final String action;
  final String? remarks;
  final DateTime createdAt;

  ActivityModel({
    required this.id,
    required this.action,
    this.remarks,
    required this.createdAt,
  });

  factory ActivityModel.fromJson(Map<String, dynamic> json) {
    return ActivityModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      action: json['action'] ?? '',
      remarks: json['remarks'],
      createdAt: json['created_at'] != null ? (DateTime.tryParse(json['created_at']) ?? DateTime.now()) : DateTime.now(),
    );
  }
}
