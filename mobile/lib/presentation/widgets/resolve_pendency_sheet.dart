import 'dart:io';
import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../core/constants/app_colors.dart';
import '../../data/models/pendency_model.dart';
import '../../logic/application/loan_cubit.dart';
import '../../logic/application/loan_state.dart';
import 'custom_button.dart';
import 'custom_text_field.dart';

class ResolvePendencySheet extends StatefulWidget {
  final PendencyModel pendency;

  const ResolvePendencySheet({super.key, required this.pendency});

  static Future<void> show(BuildContext context, PendencyModel pendency) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => BlocProvider.value(
        value: context.read<LoanCubit>(),
        child: ResolvePendencySheet(pendency: pendency),
      ),
    );
  }

  @override
  State<ResolvePendencySheet> createState() => _ResolvePendencySheetState();
}

class _ResolvePendencySheetState extends State<ResolvePendencySheet> {
  final TextEditingController _noteController = TextEditingController();
  PlatformFile? _pickedFile;
  int _pickedFileSize = 0;

  @override
  void dispose() {
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _pickFile() async {
    try {
      final file = await FilePicker.pickFile(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
      );

      if (file != null) {
        final fileSize = (await file.length()) ?? 0;
        // Check 5MB limit
        if (fileSize > 5 * 1024 * 1024) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('File exceeds maximum size limit of 5MB.')),
            );
          }
          return;
        }

        setState(() {
          _pickedFile = file;
          _pickedFileSize = fileSize;
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Could not pick file: $e')),
        );
      }
    }
  }

  Future<void> _submitResolution() async {
    final note = _noteController.text.trim();
    if (note.isEmpty && _pickedFile == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please upload a document or enter clarification notes.')),
      );
      return;
    }

    File? fileOnDisk;
    List<int>? fileBytes;
    if (_pickedFile != null) {
      if (!kIsWeb && _pickedFile!.path != null) {
        fileOnDisk = File(_pickedFile!.path!);
      } else {
        fileBytes = await _pickedFile!.readAsBytes();
      }
    }

    if (!mounted) return;

    final success = await context.read<LoanCubit>().resolvePendency(
      pendencyId: widget.pendency.id,
      responseText: note.isNotEmpty ? note : null,
      file: fileOnDisk,
      fileBytes: fileBytes,
      fileName: _pickedFile?.name,
    );

    if (success && mounted) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          backgroundColor: AppColors.success,
          content: Text('Pendency response submitted to manager successfully!'),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<LoanCubit, LoanState>(
      builder: (context, state) {
        final isSubmitting = state is PendencyResolving;

        return Container(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom + 24,
            top: 20,
            left: 20,
            right: 20,
          ),
          decoration: const BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: AppColors.border,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Resolve Banker Pendency',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: AppColors.textSecondary),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.background,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.pendency.title,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        widget.pendency.description,
                        style: const TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
                const Text(
                  'Upload Requested Document (PDF, JPG, PNG - Max 5MB)',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 8),
                InkWell(
                  onTap: _pickFile,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
                    decoration: BoxDecoration(
                      color: AppColors.background,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: _pickedFile != null ? AppColors.accent : AppColors.border,
                        width: _pickedFile != null ? 1.5 : 1,
                      ),
                    ),
                    child: Column(
                      children: [
                        Icon(
                          _pickedFile != null ? Icons.check_circle_rounded : Icons.cloud_upload_outlined,
                          size: 32,
                          color: _pickedFile != null ? AppColors.accent : AppColors.primary,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _pickedFile != null
                              ? _pickedFile!.name
                              : 'Tap to select document from phone',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: _pickedFile != null ? AppColors.textPrimary : AppColors.primary,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        if (_pickedFile != null)
                          Text(
                            'Size: ${(_pickedFileSize / 1024).toStringAsFixed(1)} KB',
                            style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                          ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                CustomTextField(
                  label: 'Clarification Note or Message (Optional)',
                  hintText: 'e.g. Attached bank statement for Oct 2025 to Mar 2026',
                  controller: _noteController,
                  maxLines: 3,
                ),
                const SizedBox(height: 24),
                CustomButton(
                  text: 'Submit Response to Manager',
                  isLoading: isSubmitting,
                  onPressed: _submitResolution,
                  backgroundColor: AppColors.accent,
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
