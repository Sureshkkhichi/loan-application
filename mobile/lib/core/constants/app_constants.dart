import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;

class AppConstants {
  // Brand Configuration
  static const String appName = 'LoanDesk';
  static const String appTagline = 'Fast • Transparent • Trusted Lending';

  // API Base Configuration
  // Uses 10.0.2.2 on Android emulator, 127.0.0.1 on iOS simulator / desktop / web
  static String get apiBaseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api/v1';
    }
    try {
      if (Platform.isAndroid) {
        return 'http://10.0.2.2:8000/api/v1';
      }
    } catch (_) {}
    return 'http://127.0.0.1:8000/api/v1';
  }

  // Storage Keys
  static const String tokenKey = 'loandesk_auth_token';
  static const String userKey = 'loandesk_user_profile';

  // Durations & Limits
  static const int otpResendCooldownSeconds = 30;
  static const int maxUploadSizeMb = 5;
}
