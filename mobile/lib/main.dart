import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'core/constants/app_constants.dart';
import 'core/theme/app_theme.dart';
import 'data/repositories/auth_repository.dart';
import 'data/repositories/loan_repository.dart';
import 'logic/application/loan_cubit.dart';
import 'logic/auth/auth_cubit.dart';
import 'presentation/screens/app_entry_gate.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  // Set status bar styling
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.light,
      statusBarBrightness: Brightness.dark,
    ),
  );

  final authRepository = AuthRepository();
  final loanRepository = LoanRepository();

  runApp(
    LoanDeskApp(
      authRepository: authRepository,
      loanRepository: loanRepository,
    ),
  );
}

class LoanDeskApp extends StatelessWidget {
  final AuthRepository authRepository;
  final LoanRepository loanRepository;

  const LoanDeskApp({
    super.key,
    required this.authRepository,
    required this.loanRepository,
  });

  @override
  Widget build(BuildContext context) {
    return MultiRepositoryProvider(
      providers: [
        RepositoryProvider.value(value: authRepository),
        RepositoryProvider.value(value: loanRepository),
      ],
      child: MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (_) => AuthCubit(authRepository: authRepository),
          ),
          BlocProvider(
            create: (_) => LoanCubit(loanRepository: loanRepository),
          ),
        ],
        child: MaterialApp(
          title: AppConstants.appName,
          debugShowCheckedModeBanner: false,
          theme: AppTheme.lightTheme,
          home: const AppEntryGate(),
        ),
      ),
    );
  }
}
