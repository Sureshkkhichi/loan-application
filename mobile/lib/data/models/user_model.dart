class UserModel {
  final int id;
  final String name;
  final String phone;
  final String role;

  UserModel({
    required this.id,
    required this.name,
    required this.phone,
    required this.role,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? '',
      phone: json['phone'] ?? '',
      role: json['role'] ?? 'customer',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'phone': phone,
      'role': role,
    };
  }
}
