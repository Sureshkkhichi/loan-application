class PendencyModel {
  final int id;
  final int applicationId;
  final String title;
  final String description;
  final String status; // 'PENDING' | 'RESOLVED'
  final String? customerResponseText;
  final String? customerResponseFile;
  final DateTime? resolvedAt;
  final DateTime createdAt;

  PendencyModel({
    required this.id,
    required this.applicationId,
    required this.title,
    required this.description,
    required this.status,
    this.customerResponseText,
    this.customerResponseFile,
    this.resolvedAt,
    required this.createdAt,
  });

  bool get isPending => status == 'PENDING';
  bool get isResolved => status == 'RESOLVED';

  factory PendencyModel.fromJson(Map<String, dynamic> json) {
    return PendencyModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      applicationId: json['application_id'] is int ? json['application_id'] : int.tryParse(json['application_id'].toString()) ?? 0,
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      status: json['status'] ?? 'PENDING',
      customerResponseText: json['customer_response_text'],
      customerResponseFile: json['customer_response_file'],
      resolvedAt: json['resolved_at'] != null ? DateTime.tryParse(json['resolved_at']) : null,
      createdAt: json['created_at'] != null ? (DateTime.tryParse(json['created_at']) ?? DateTime.now()) : DateTime.now(),
    );
  }
}
