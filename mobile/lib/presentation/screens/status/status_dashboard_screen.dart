import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/loan_application_model.dart';
import '../../../logic/application/loan_cubit.dart';
import '../../../logic/auth/auth_cubit.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/pendency_action_card.dart';
import '../../widgets/resolve_pendency_sheet.dart';
import '../../widgets/timeline_widget.dart';

class StatusDashboardScreen extends StatelessWidget {
  final LoanApplicationModel application;

  const StatusDashboardScreen({
    super.key,
    required this.application,
  });

  String _formatCurrency(double amount) {
    final format = NumberFormat.currency(locale: 'en_IN', symbol: '₹', decimalDigits: 0);
    return format.format(amount);
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'NEW':
      case 'IN_PROGRESS':
        return AppColors.info;
      case 'SUBMITTED_FOR_REVIEW':
      case 'READY_FOR_BANK':
        return AppColors.primary;
      case 'PENDENCY_RAISED':
        return AppColors.warning;
      case 'PENDENCY_RESOLVED':
        return const Color(0xFF0D9488);
      case 'COMPLETED':
        return AppColors.success;
      case 'REJECTED':
        return AppColors.error;
      default:
        return AppColors.textSecondary;
    }
  }

  String _getStatusTitle(String status) {
    switch (status) {
      case 'NEW':
        return 'Application Queued';
      case 'IN_PROGRESS':
        return 'Executive Contacting You';
      case 'SUBMITTED_FOR_REVIEW':
        return 'Internal Quality Review';
      case 'READY_FOR_BANK':
        return 'With Partner Banker';
      case 'PENDENCY_RAISED':
        return 'Action Required';
      case 'PENDENCY_RESOLVED':
        return 'Response Under Verification';
      case 'COMPLETED':
        return 'Loan Disbursed';
      case 'REJECTED':
        return 'Application Declined';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('My Loan Application'),
        backgroundColor: AppColors.primary,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, size: 22),
            tooltip: 'Check Latest Status',
            onPressed: () {
              context.read<LoanCubit>().loadLoanData(silent: true);
            },
          ),
          IconButton(
            icon: const Icon(Icons.logout_rounded, size: 20),
            tooltip: 'Sign Out',
            onPressed: () {
              context.read<AuthCubit>().logout();
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: () async {
          await context.read<LoanCubit>().loadLoanData(silent: true);
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 560),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [

                // Application Summary Card
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.border),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.02),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withOpacity(0.08),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              application.applicationNumber,
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w700,
                                color: AppColors.primary,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: _getStatusColor(application.status).withOpacity(0.12),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: _getStatusColor(application.status).withOpacity(0.3),
                              ),
                            ),
                            child: Text(
                              _getStatusTitle(application.status),
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: _getStatusColor(application.status),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 18),
                      Text(
                        _formatCurrency(application.requestedAmount),
                        style: const TextStyle(
                          fontSize: 30,
                          fontWeight: FontWeight.w800,
                          color: AppColors.textPrimary,
                          letterSpacing: -0.8,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${application.loanType?.name ?? "Standard Loan"} • ${application.city}',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w500,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Rejection State Card
                if (application.isRejected) ...[
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFFECACA), width: 1.5),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: const [
                            Icon(Icons.cancel_rounded, color: AppColors.error, size: 24),
                            SizedBox(width: 10),
                            Text(
                              'Application Declined',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                color: AppColors.error,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        const Text(
                          'Reason recorded by credit officer:',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF991B1B),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          application.rejectionReason ?? 'Criteria not met at this time.',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF7F1D1D),
                            height: 1.4,
                          ),
                        ),
                        const SizedBox(height: 20),
                        CustomButton(
                          text: 'Start Fresh Application',
                          backgroundColor: AppColors.primary,
                          onPressed: () {
                            context.read<LoanCubit>().startFreshApplication();
                          },
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                ],

                // Banker Pendency Action Card (In-place Highlight)
                if (application.isPendencyRaised && application.activePendency != null) ...[
                  PendencyActionCard(
                    pendency: application.activePendency!,
                    onResolveTap: () {
                      ResolvePendencySheet.show(context, application.activePendency!);
                    },
                  ),
                  const SizedBox(height: 20),
                ],

                // Completion Congratulatory Card
                if (application.isCompleted) ...[
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFBBF7D0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: const [
                            Icon(Icons.verified_rounded, color: AppColors.success, size: 26),
                            SizedBox(width: 10),
                            Text(
                              'Congratulations! Loan Disbursed',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                color: AppColors.success,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Your loan has been successfully sanctioned and disbursed by the partner bank.',
                          style: TextStyle(fontSize: 13, color: Color(0xFF166534)),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                ],

                // Vertical Milestones Progression Timeline
                if (!application.isRejected)
                  TimelineWidget(currentStatus: application.status),

                const SizedBox(height: 24),

                // Support & Help Card
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Row(
                    children: const [
                      Icon(Icons.support_agent_rounded, size: 24, color: AppColors.primary),
                      SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Need help with your application?',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            Text(
                              'Your assigned executive will contact you shortly.',
                              style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
