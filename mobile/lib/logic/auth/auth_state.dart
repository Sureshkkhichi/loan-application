import '../../data/models/user_model.dart';

abstract class AuthState {}

class AuthInitial extends AuthState {}

class AuthLoading extends AuthState {}

class AuthUnauthenticated extends AuthState {}

class AuthOtpSent extends AuthState {
  final String phone;
  final int resendCooldownSeconds;

  AuthOtpSent({
    required this.phone,
    this.resendCooldownSeconds = 30,
  });
}

class AuthAuthenticated extends AuthState {
  final UserModel user;

  AuthAuthenticated({required this.user});
}

class AuthError extends AuthState {
  final String message;

  AuthError({required this.message});
}
