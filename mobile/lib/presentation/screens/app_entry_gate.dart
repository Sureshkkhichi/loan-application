import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../core/constants/app_colors.dart';
import '../../core/constants/app_constants.dart';
import '../../logic/application/loan_cubit.dart';
import '../../logic/application/loan_state.dart';
import '../../logic/auth/auth_cubit.dart';
import '../../logic/auth/auth_state.dart';
import '../widgets/custom_button.dart';
import 'apply/apply_loan_screen.dart';
import 'auth/login_screen.dart';
import 'status/status_dashboard_screen.dart';

class AppEntryGate extends StatefulWidget {
  const AppEntryGate({super.key});

  @override
  State<AppEntryGate> createState() => _AppEntryGateState();
}

class _AppEntryGateState extends State<AppEntryGate> {
  @override
  void initState() {
    super.initState();
    context.read<AuthCubit>().checkAuthStatus();
  }

  @override
  Widget build(BuildContext context) {
    return BlocConsumer<AuthCubit, AuthState>(
      listener: (context, authState) {
        if (authState is AuthAuthenticated) {
          context.read<LoanCubit>().loadLoanData();
        }
      },
      builder: (context, authState) {
        if (authState is AuthInitial || authState is AuthLoading) {
          return const _SplashScreen();
        }

        if (authState is AuthUnauthenticated || authState is AuthError) {
          return const LoginScreen();
        }

        if (authState is AuthAuthenticated) {
          return BlocBuilder<LoanCubit, LoanState>(
            builder: (context, loanState) {
              if (loanState is LoanInitial || loanState is LoanLoading) {
                return const Scaffold(
                  backgroundColor: AppColors.background,
                  body: Center(
                    child: CircularProgressIndicator(color: AppColors.primary),
                  ),
                );
              }

              if (loanState is LoanError) {
                return Scaffold(
                  backgroundColor: AppColors.background,
                  body: Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24.0),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.cloud_off_rounded, size: 48, color: AppColors.error),
                          const SizedBox(height: 16),
                          Text(
                            loanState.message,
                            style: const TextStyle(fontSize: 14, color: AppColors.textPrimary),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 24),
                          SizedBox(
                            width: 180,
                            child: CustomButton(
                              text: 'Retry',
                              onPressed: () {
                                context.read<LoanCubit>().loadLoanData();
                              },
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }

              if (loanState is LoanDataLoaded) {
                // If customer has an active application, route directly to status dashboard
                if (loanState.hasActiveApplication) {
                  return StatusDashboardScreen(
                    application: loanState.activeApplication!,
                  );
                }

                // If customer's last application was rejected, show status screen so they can see reason + re-apply
                if (loanState.hasRejectedApplication) {
                  return StatusDashboardScreen(
                    application: loanState.activeApplication!,
                  );
                }

                // Otherwise, show high-conversion clean apply screen
                return ApplyLoanScreen(
                  loanTypes: loanState.loanTypes,
                );
              }

              return const _SplashScreen();
            },
          );
        }

        return const LoginScreen();
      },
    );
  }
}

class _SplashScreen extends StatelessWidget {
  const _SplashScreen();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.primary,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.15),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: const Center(
                child: Text(
                  'LD',
                  style: TextStyle(
                    color: AppColors.primary,
                    fontSize: 32,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 20),
            const Text(
              AppConstants.appName,
              style: TextStyle(
                color: Colors.white,
                fontSize: 26,
                fontWeight: FontWeight.w800,
                letterSpacing: -0.5,
              ),
            ),
            const SizedBox(height: 6),
            const Text(
              AppConstants.appTagline,
              style: TextStyle(
                color: Colors.white70,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 36),
            const SizedBox(
              width: 24,
              height: 24,
              child: CircularProgressIndicator(
                strokeWidth: 2.5,
                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
