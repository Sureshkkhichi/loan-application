import 'package:flutter_bloc/flutter_bloc.dart';
import '../../data/models/user_model.dart';
import '../../data/repositories/auth_repository.dart';
import 'auth_state.dart';

class AuthCubit extends Cubit<AuthState> {
  final AuthRepository _authRepository;

  AuthCubit({required AuthRepository authRepository})
      : _authRepository = authRepository,
        super(AuthInitial());

  /// Check if user has active session
  Future<void> checkAuthStatus() async {
    try {
      final user = await _authRepository.getSavedUser();
      if (user != null) {
        emit(AuthAuthenticated(user: user));
      } else {
        emit(AuthUnauthenticated());
      }
    } catch (e) {
      emit(AuthUnauthenticated());
    }
  }

  /// Send OTP to mobile
  Future<void> sendOtp(String phone) async {
    emit(AuthLoading());
    try {
      final response = await _authRepository.sendOtp(phone);
      if (response.success) {
        final cooldown = response.data?['resend_cooldown_seconds'] ?? 30;
        emit(AuthOtpSent(phone: phone, resendCooldownSeconds: cooldown));
      } else {
        emit(AuthError(message: response.message));
      }
    } catch (e) {
      emit(AuthError(message: 'Failed to send OTP: $e'));
    }
  }

  /// Verify 6-digit OTP
  Future<void> verifyOtp(String phone, String otp) async {
    emit(AuthLoading());
    try {
      final response = await _authRepository.verifyOtp(phone: phone, otp: otp);
      if (response.success && response.data != null) {
        final user = UserModel.fromJson(response.data['user']);
        emit(AuthAuthenticated(user: user));
      } else {
        emit(AuthError(message: response.message));
      }
    } catch (e) {
      emit(AuthError(message: 'Verification failed: $e'));
    }
  }

  /// Logout
  Future<void> logout() async {
    await _authRepository.logout();
    emit(AuthUnauthenticated());
  }
}
