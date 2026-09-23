import '../constants/app_constants.dart';

class ApiEndpoints {
  static String get baseUrl => AppConstants.apiBaseUrl;

  // Auth Endpoints
  static String get sendOtp => '$baseUrl/auth/send-otp';
  static String get verifyOtp => '$baseUrl/auth/verify-otp';
  static String get me => '$baseUrl/auth/me';
  static String get logout => '$baseUrl/auth/logout';

  // Loan Products
  static String get loanTypes => '$baseUrl/loan-types';

  // Applications
  static String get activeApplication => '$baseUrl/applications/active';
  static String get applyLoan => '$baseUrl/applications';

  // Pendency
  static String resolvePendency(int id) => '$baseUrl/pendencies/$id/resolve';
}
