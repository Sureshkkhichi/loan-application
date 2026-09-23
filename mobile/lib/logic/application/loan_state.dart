import '../../data/models/loan_application_model.dart';
import '../../data/models/loan_type_model.dart';

abstract class LoanState {}

class LoanInitial extends LoanState {}

class LoanLoading extends LoanState {}

class LoanDataLoaded extends LoanState {
  final List<LoanTypeModel> loanTypes;
  final LoanApplicationModel? activeApplication;

  LoanDataLoaded({
    required this.loanTypes,
    this.activeApplication,
  });

  bool get hasActiveApplication =>
      activeApplication != null && activeApplication!.isActiveAndPending;

  bool get hasRejectedApplication =>
      activeApplication != null && activeApplication!.isRejected;
}

class LoanSubmitting extends LoanState {}

class LoanSubmitSuccess extends LoanState {
  final LoanApplicationModel application;
  final String message;

  LoanSubmitSuccess({
    required this.application,
    required this.message,
  });
}

class PendencyResolving extends LoanState {}

class PendencyResolveSuccess extends LoanState {
  final String message;

  PendencyResolveSuccess({required this.message});
}

class LoanError extends LoanState {
  final String message;

  LoanError({required this.message});
}
