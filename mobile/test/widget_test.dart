import 'package:flutter_test/flutter_test.dart';
import 'package:loan_desk/data/repositories/auth_repository.dart';
import 'package:loan_desk/data/repositories/loan_repository.dart';
import 'package:loan_desk/main.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  testWidgets('LoanDeskApp launches and displays login or splash', (WidgetTester tester) async {
    final authRepository = AuthRepository();
    final loanRepository = LoanRepository();

    await tester.pumpWidget(
      LoanDeskApp(
        authRepository: authRepository,
        loanRepository: loanRepository,
      ),
    );

    // Initial pump
    await tester.pump();

    // Verify Brand or Login UI elements render
    expect(find.textContaining('LoanDesk'), findsWidgets);
  });
}
