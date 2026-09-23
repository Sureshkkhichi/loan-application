import 'package:flutter/material.dart';

/// Modern Trust Fintech Color System
/// Designed for credibility, clarity, and conversion.
class AppColors {
  // Brand Primary & Dark
  static const Color primary = Color(0xFF123B6D);      // Deep Navy
  static const Color primaryDark = Color(0xFF0B294D);  // Dark Blue
  static const Color primaryLight = Color(0xFF1F4E89); // Subtle highlight

  // Restrained Accent (Actions & Success highlights only)
  static const Color accent = Color(0xFF16A085);       // Teal / Emerald

  // Background & Surfaces
  static const Color background = Color(0xFFF6F8FB);   // Cool Light Grey
  static const Color surface = Color(0xFFFFFFFF);      // Clean White
  static const Color surfaceElevated = Color(0xFFFAFBFC);

  // Typography & Text
  static const Color textPrimary = Color(0xFF172033);  // Dark Charcoal
  static const Color textSecondary = Color(0xFF667085);// Muted Slate Grey
  static const Color textTertiary = Color(0xFF98A2B3); // Placeholder / Sub-caption

  // Borders & Dividers
  static const Color border = Color(0xFFE4E8EF);       // Subtle 1px borders
  static const Color divider = Color(0xFFEEF2F6);

  // Lifecycle & Status Indicators
  static const Color success = Color(0xFF168A5B);      // Approved / Resolved
  static const Color warning = Color(0xFFD99000);      // Pendency / Action Required
  static const Color error = Color(0xFFD64545);        // Rejection / Error
  static const Color info = Color(0xFF2E90FA);         // In Progress / Submissions
}
