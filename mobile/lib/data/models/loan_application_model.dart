import 'activity_model.dart';
import 'loan_type_model.dart';
import 'pendency_model.dart';

class LoanApplicationModel {
  final int id;
  final String applicationNumber;
  final double requestedAmount;
  final String applicantName;
  final String applicantPhone;
  final String city;
  final String? pincode;
  final String? referralCode;
  final String status;
  final String? rejectionReason;
  final LoanTypeModel? loanType;
  final PendencyModel? activePendency;
  final List<ActivityModel> activities;
  final DateTime createdAt;

  LoanApplicationModel({
    required this.id,
    required this.applicationNumber,
    required this.requestedAmount,
    required this.applicantName,
    required this.applicantPhone,
    required this.city,
    this.pincode,
    this.referralCode,
    required this.status,
    this.rejectionReason,
    this.loanType,
    this.activePendency,
    required this.activities,
    required this.createdAt,
  });

  bool get isNew => status == 'NEW';
  bool get isInProgress => status == 'IN_PROGRESS';
  bool get isSubmittedForReview => status == 'SUBMITTED_FOR_REVIEW';
  bool get isReadyForBank => status == 'READY_FOR_BANK';
  bool get isPendencyRaised => status == 'PENDENCY_RAISED';
  bool get isPendencyResolved => status == 'PENDENCY_RESOLVED';
  bool get isCompleted => status == 'COMPLETED';
  bool get isRejected => status == 'REJECTED';

  // Indicates if user has an ongoing application that prevents starting another
  bool get isActiveAndPending => !isRejected && !isCompleted;

  factory LoanApplicationModel.fromJson(Map<String, dynamic> json) {
    var rawActivities = json['activities'] as List? ?? [];
    List<ActivityModel> activitiesList = rawActivities
        .map((a) => ActivityModel.fromJson(a as Map<String, dynamic>))
        .toList();

    return LoanApplicationModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      applicationNumber: json['application_number'] ?? '',
      requestedAmount: double.tryParse(json['requested_amount'].toString()) ?? 0.0,
      applicantName: json['applicant_name'] ?? '',
      applicantPhone: json['applicant_phone'] ?? '',
      city: json['city'] ?? '',
      pincode: json['pincode'],
      referralCode: json['referral_code'],
      status: json['status'] ?? 'NEW',
      rejectionReason: json['rejection_reason'],
      loanType: json['loan_type'] != null
          ? LoanTypeModel.fromJson(json['loan_type'] as Map<String, dynamic>)
          : null,
      activePendency: json['active_pendency'] != null
          ? PendencyModel.fromJson(json['active_pendency'] as Map<String, dynamic>)
          : null,
      activities: activitiesList,
      createdAt: json['created_at'] != null
          ? (DateTime.tryParse(json['created_at']) ?? DateTime.now())
          : DateTime.now(),
    );
  }
}
