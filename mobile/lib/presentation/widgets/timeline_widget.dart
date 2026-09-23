import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';

class TimelineWidget extends StatelessWidget {
  final String currentStatus;

  const TimelineWidget({super.key, required this.currentStatus});

  int _getCurrentStepIndex() {
    switch (currentStatus) {
      case 'NEW':
        return 0;
      case 'IN_PROGRESS':
        return 1;
      case 'SUBMITTED_FOR_REVIEW':
      case 'UNDER_REVIEW':
        return 2;
      case 'READY_FOR_BANK':
      case 'PENDENCY_RAISED':
      case 'PENDENCY_RESOLVED':
        return 3;
      case 'COMPLETED':
        return 4;
      case 'REJECTED':
        return -1; // Special handling
      default:
        return 0;
    }
  }

  @override
  Widget build(BuildContext context) {
    final activeStep = _getCurrentStepIndex();

    final steps = [
      {
        'title': 'Application Submitted',
        'subtitle': 'Basic lead received & queued for verification',
      },
      {
        'title': 'Sales Contact & Verification',
        'subtitle': 'Executive calls you to complete documentation',
      },
      {
        'title': 'Manager Review & Audit',
        'subtitle': 'Internal quality check before banker submission',
      },
      {
        'title': 'Banker Processing',
        'subtitle': 'External bank verification & credit check',
      },
      {
        'title': 'Loan Disbursement',
        'subtitle': 'Funds credited directly to your bank account',
      },
    ];

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Application Journey',
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 16),
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: steps.length,
            itemBuilder: (context, index) {
              final isPassed = activeStep > index || activeStep == 4;
              final isCurrent = activeStep == index;
              final isLast = index == steps.length - 1;

              Color stepColor;
              if (isPassed) {
                stepColor = AppColors.success;
              } else if (isCurrent) {
                stepColor = currentStatus == 'PENDENCY_RAISED'
                    ? AppColors.warning
                    : AppColors.primary;
              } else {
                stepColor = AppColors.border;
              }

              return Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Dot and Line
                  Column(
                    children: [
                      Container(
                        width: 22,
                        height: 22,
                        decoration: BoxDecoration(
                          color: isPassed
                              ? AppColors.success
                              : (isCurrent ? stepColor : Colors.white),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: stepColor,
                            width: 2,
                          ),
                        ),
                        child: Center(
                          child: isPassed
                              ? const Icon(Icons.check, size: 13, color: Colors.white)
                              : (isCurrent
                                  ? Container(
                                      width: 8,
                                      height: 8,
                                      decoration: BoxDecoration(
                                        color: currentStatus == 'PENDENCY_RAISED'
                                            ? Colors.white
                                            : AppColors.primary,
                                        shape: BoxShape.circle,
                                      ),
                                    )
                                  : null),
                        ),
                      ),
                      if (!isLast)
                        Container(
                          width: 2,
                          height: 40,
                          color: isPassed ? AppColors.success : AppColors.border,
                        ),
                    ],
                  ),
                  const SizedBox(width: 14),
                  // Texts
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            steps[index]['title']!,
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: isCurrent ? FontWeight.w700 : FontWeight.w600,
                              color: isCurrent
                                  ? AppColors.textPrimary
                                  : (isPassed ? AppColors.textPrimary : AppColors.textTertiary),
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            steps[index]['subtitle']!,
                            style: const TextStyle(
                              fontSize: 12,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              );
            },
          ),
        ],
      ),
    );
  }
}
