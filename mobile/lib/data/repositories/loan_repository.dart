import 'dart:io';

import '../../core/network/api_client.dart';
import '../../core/network/api_endpoints.dart';
import '../models/loan_application_model.dart';
import '../models/loan_type_model.dart';

class LoanRepository {
  final ApiClient _apiClient;

  LoanRepository({ApiClient? apiClient})
    : _apiClient = apiClient ?? ApiClient();

  /// Fetch active loan types catalog
  Future<List<LoanTypeModel>> fetchLoanTypes() async {
    final response = await _apiClient.get(ApiEndpoints.loanTypes);
    if (response.success && response.data is List) {
      return (response.data as List)
          .map((item) => LoanTypeModel.fromJson(item as Map<String, dynamic>))
          .toList();
    }
    return [];
  }

  /// Fetch customer's active loan application
  Future<LoanApplicationModel?> fetchActiveApplication() async {
    final response = await _apiClient.get(ApiEndpoints.activeApplication);
    if (response.success && response.data != null) {
      return LoanApplicationModel.fromJson(
        response.data as Map<String, dynamic>,
      );
    }
    return null;
  }

  /// Submit new loan application
  Future<ApiResponse> applyLoan({
    required String applicantName,
    required int loanTypeId,
    required double requestedAmount,
    required String city,
    String? pincode,
    String? referralCode,
    String? campaignSource,
  }) async {
    return await _apiClient.post(ApiEndpoints.applyLoan, {
      'applicant_name': applicantName,
      'loan_type_id': loanTypeId,
      'requested_amount': requestedAmount,
      'city': city,
      if (pincode != null && pincode.isNotEmpty) 'pincode': pincode,
      if (referralCode != null && referralCode.isNotEmpty)
        'referral_code': referralCode,
      'campaign_source': ?campaignSource,
    });
  }

  /// Resolve banker pendency via document upload or explanation text
  Future<ApiResponse> resolvePendency({
    required int pendencyId,
    String? responseText,
    File? file,
    List<int>? fileBytes,
    String? fileName,
  }) async {
    final fields = <String, String>{};
    if (responseText != null && responseText.isNotEmpty) {
      fields['response_text'] = responseText;
    }

    return await _apiClient.postMultipart(
      url: ApiEndpoints.resolvePendency(pendencyId),
      fields: fields,
      fileField: (file != null || fileBytes != null) ? 'file' : null,
      file: file,
      fileBytes: fileBytes,
      fileName: fileName,
    );
  }
}
