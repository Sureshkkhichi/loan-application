import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import '../../core/constants/app_constants.dart';
import '../../core/network/api_client.dart';
import '../../core/network/api_endpoints.dart';
import '../models/user_model.dart';

class AuthRepository {
  final ApiClient _apiClient;

  AuthRepository({ApiClient? apiClient})
    : _apiClient = apiClient ?? ApiClient();

  /// Send OTP to mobile number
  Future<ApiResponse> sendOtp(String phone) async {
    return await _apiClient.post(ApiEndpoints.sendOtp, {'phone': phone});
  }

  /// Verify 6-digit OTP and store Sanctum token
  Future<ApiResponse> verifyOtp({
    required String phone,
    required String otp,
    String? fcmToken,
  }) async {
    final response = await _apiClient.post(ApiEndpoints.verifyOtp, {
      'phone': phone,
      'otp': otp,
      'fcm_token': ?fcmToken,
    });

    if (response.success && response.data != null) {
      final token = response.data['token'];
      final userData = response.data['user'];

      final prefs = await SharedPreferences.getInstance();
      if (token != null) {
        await prefs.setString(AppConstants.tokenKey, token);
      }
      if (userData != null) {
        await prefs.setString(AppConstants.userKey, jsonEncode(userData));
      }
    }

    return response;
  }

  /// Check if user has an existing session token
  Future<UserModel?> getSavedUser() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(AppConstants.tokenKey);
    final rawUser = prefs.getString(AppConstants.userKey);

    if (token != null && token.isNotEmpty && rawUser != null) {
      try {
        return UserModel.fromJson(jsonDecode(rawUser));
      } catch (_) {}
    }
    return null;
  }

  /// Logout and clear storage
  Future<void> logout() async {
    try {
      await _apiClient.post(ApiEndpoints.logout, {});
    } catch (_) {}

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(AppConstants.tokenKey);
    await prefs.remove(AppConstants.userKey);
  }
}
