import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../core/constants/app_colors.dart';
import '../../../data/models/loan_type_model.dart';
import '../../../logic/application/loan_cubit.dart';
import '../../../logic/application/loan_state.dart';
import '../../../logic/auth/auth_cubit.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';

class ApplyLoanScreen extends StatefulWidget {
  final List<LoanTypeModel> loanTypes;

  const ApplyLoanScreen({super.key, required this.loanTypes});

  @override
  State<ApplyLoanScreen> createState() => _ApplyLoanScreenState();
}

class _ApplyLoanScreenState extends State<ApplyLoanScreen> {
  final _formKey = GlobalKey<FormState>();

  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _amountController = TextEditingController();
  final TextEditingController _cityController = TextEditingController();
  final TextEditingController _pincodeController = TextEditingController();
  final TextEditingController _referralController = TextEditingController();

  LoanTypeModel? _selectedLoanType;

  @override
  void initState() {
    super.initState();
    if (widget.loanTypes.isNotEmpty) {
      _selectedLoanType = widget.loanTypes.first;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _amountController.dispose();
    _cityController.dispose();
    _pincodeController.dispose();
    _referralController.dispose();
    super.dispose();
  }

  void _onSubmit() {
    if (_formKey.currentState?.validate() ?? false) {
      if (_selectedLoanType == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please select a loan type')),
        );
        return;
      }

      final amount =
          double.tryParse(_amountController.text.replaceAll(',', '').trim()) ??
          0;

      context.read<LoanCubit>().submitApplication(
        applicantName: _nameController.text.trim(),
        loanTypeId: _selectedLoanType!.id,
        requestedAmount: amount,
        city: _cityController.text.trim(),
        pincode: _pincodeController.text.trim(),
        referralCode: _referralController.text.trim().isNotEmpty
            ? _referralController.text.trim()
            : null,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Apply for a Loan'),
        backgroundColor: AppColors.primary,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.logout_rounded, size: 20),
            tooltip: 'Sign Out',
            onPressed: () {
              context.read<AuthCubit>().logout();
            },
          ),
        ],
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 520),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header Subtitle
                    const Text(
                      'Tell us about your requirement',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textPrimary,
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Submit basic details and our financial executive will assist you with banker processing.',
                      style: TextStyle(
                        fontSize: 13,
                        color: AppColors.textSecondary,
                        height: 1.4,
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Main Form Card
                    Container(
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.border),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.02),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Full Name Field
                          CustomTextField(
                            label: 'Full Name (as per PAN)',
                            hintText: 'e.g. Karan Sharma',
                            controller: _nameController,
                            keyboardType: TextInputType.name,
                            validator: (val) {
                              if (val == null || val.trim().isEmpty) {
                                return 'Please enter your full name';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 20),

                          // Loan Type Dropdown
                          const Text(
                            'Loan Type',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: AppColors.textPrimary,
                            ),
                          ),
                          const SizedBox(height: 6),
                          DropdownButtonFormField<LoanTypeModel>(
                            initialValue: _selectedLoanType,
                            isExpanded: true,
                            decoration: const InputDecoration(
                              contentPadding: EdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 14,
                              ),
                            ),
                            items: widget.loanTypes.map((type) {
                              return DropdownMenuItem<LoanTypeModel>(
                                value: type,
                                child: Text(
                                  type.name,
                                  style: const TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w500,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              setState(() {
                                _selectedLoanType = val;
                              });
                            },
                          ),
                          const SizedBox(height: 20),

                          // Loan Amount Field
                          CustomTextField(
                            label: 'Required Loan Amount',
                            hintText: 'e.g. 5,00,000',
                            controller: _amountController,
                            keyboardType: TextInputType.number,
                            prefix: const Padding(
                              padding: EdgeInsets.only(left: 16, right: 8),
                              child: Text(
                                '₹',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.primary,
                                ),
                              ),
                            ),
                            inputFormatters: [
                              FilteringTextInputFormatter.digitsOnly,
                            ],
                            validator: (val) {
                              if (val == null || val.trim().isEmpty) {
                                return 'Please enter requested loan amount';
                              }
                              final amt = double.tryParse(
                                val.replaceAll(',', ''),
                              );
                              if (amt == null || amt < 10000) {
                                return 'Minimum loan amount is ₹10,000';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 20),

                          // City & Pincode Row
                          Row(
                            children: [
                              Expanded(
                                flex: 2,
                                child: CustomTextField(
                                  label: 'City / Location',
                                  hintText: 'e.g. Mumbai',
                                  controller: _cityController,
                                  validator: (val) {
                                    if (val == null || val.trim().isEmpty) {
                                      return 'Please enter city';
                                    }
                                    return null;
                                  },
                                ),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                flex: 1,
                                child: CustomTextField(
                                  label: 'Pincode',
                                  hintText: '400001',
                                  controller: _pincodeController,
                                  keyboardType: TextInputType.number,
                                  inputFormatters: [
                                    FilteringTextInputFormatter.digitsOnly,
                                    LengthLimitingTextInputFormatter(6),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 20),

                          // Optional Promo / Referral Code (Marketing Attribution)
                          CustomTextField(
                            label: 'Referral / Promotion Code (Optional)',
                            hintText: 'e.g. PROMO2026',
                            controller: _referralController,
                          ),
                          const SizedBox(height: 32),

                          // Submit Action
                          BlocBuilder<LoanCubit, LoanState>(
                            builder: (context, state) {
                              return CustomButton(
                                text: 'Submit Loan Request →',
                                isLoading: state is LoanSubmitting,
                                onPressed: _onSubmit,
                              );
                            },
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Compliance Banner
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withValues(alpha: 0.04),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Row(
                        children: const [
                          Icon(
                            Icons.lock_outline_rounded,
                            size: 18,
                            color: AppColors.primary,
                          ),
                          SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'Your financial details are securely transmitted directly to verified banking officers.',
                              style: TextStyle(
                                fontSize: 11,
                                color: AppColors.textSecondary,
                                height: 1.3,
                              ),
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
        ),
      ),
    );
  }
}
