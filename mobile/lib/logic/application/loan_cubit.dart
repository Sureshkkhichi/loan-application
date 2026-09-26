import 'dart:io';

import 'package:flutter_bloc/flutter_bloc.dart';

import '../../data/models/loan_application_model.dart';
import '../../data/models/loan_type_model.dart';
import '../../data/repositories/loan_repository.dart';
import 'loan_state.dart';

class LoanCubit extends Cubit<LoanState> {
  final LoanRepository _loanRepository;
  List<LoanTypeModel> _cachedLoanTypes = [];
  LoanApplicationModel? _cachedApplication;

  LoanCubit({required this._loanRepository}) : super(LoanInitial());

  /// Load initial loan types and active application
  Future<void> loadLoanData({bool silent = false}) async {
    if (!silent) emit(LoanLoading());
    try {
      final types = await _loanRepository.fetchLoanTypes();
      _cachedLoanTypes = types;

      final activeApp = await _loanRepository.fetchActiveApplication();
      _cachedApplication = activeApp;

      emit(
        LoanDataLoaded(
          loanTypes: _cachedLoanTypes,
          activeApplication: _cachedApplication,
        ),
      );
    } catch (e) {
      emit(LoanError(message: 'Failed to load application data: $e'));
    }
  }

  /// Submit new loan application
  Future<bool> submitApplication({
    required String applicantName,
    required int loanTypeId,
    required double requestedAmount,
    required String city,
    String? pincode,
    String? referralCode,
    String? campaignSource,
  }) async {
    emit(LoanSubmitting());
    try {
      final response = await _loanRepository.applyLoan(
        applicantName: applicantName,
        loanTypeId: loanTypeId,
        requestedAmount: requestedAmount,
        city: city,
        pincode: pincode,
        referralCode: referralCode,
        campaignSource: campaignSource,
      );

      if (response.success && response.data != null) {
        final newApp = LoanApplicationModel.fromJson(
          response.data as Map<String, dynamic>,
        );
        _cachedApplication = newApp;

        emit(LoanSubmitSuccess(application: newApp, message: response.message));

        // Transition back to loaded state with active app
        emit(
          LoanDataLoaded(
            loanTypes: _cachedLoanTypes,
            activeApplication: newApp,
          ),
        );
        return true;
      } else {
        emit(LoanError(message: response.message));
        emit(
          LoanDataLoaded(
            loanTypes: _cachedLoanTypes,
            activeApplication: _cachedApplication,
          ),
        );
        return false;
      }
    } catch (e) {
      emit(LoanError(message: 'Failed to submit application: $e'));
      emit(
        LoanDataLoaded(
          loanTypes: _cachedLoanTypes,
          activeApplication: _cachedApplication,
        ),
      );
      return false;
    }
  }

  /// Resolve banker pendency via document upload or explanation note
  Future<bool> resolvePendency({
    required int pendencyId,
    String? responseText,
    File? file,
    List<int>? fileBytes,
    String? fileName,
  }) async {
    emit(PendencyResolving());
    try {
      final response = await _loanRepository.resolvePendency(
        pendencyId: pendencyId,
        responseText: responseText,
        file: file,
        fileBytes: fileBytes,
        fileName: fileName,
      );

      if (response.success) {
        emit(PendencyResolveSuccess(message: response.message));
        // Refresh active application
        await loadLoanData(silent: true);
        return true;
      } else {
        emit(LoanError(message: response.message));
        emit(
          LoanDataLoaded(
            loanTypes: _cachedLoanTypes,
            activeApplication: _cachedApplication,
          ),
        );
        return false;
      }
    } catch (e) {
      emit(LoanError(message: 'Failed to resolve pendency: $e'));
      emit(
        LoanDataLoaded(
          loanTypes: _cachedLoanTypes,
          activeApplication: _cachedApplication,
        ),
      );
      return false;
    }
  }

  /// Start fresh application after rejection or completion
  void startFreshApplication() {
    _cachedApplication = null;
    emit(LoanDataLoaded(loanTypes: _cachedLoanTypes, activeApplication: null));
  }
}
