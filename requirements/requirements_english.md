# Loan Application - Product Requirements Document & Technical Architecture

## 1. Project Overview & Scope
- **Project Name**: Loan Application & Lead Collection Management System (Brand: **LoanDesk**)
- **Core Scope**: Customer Lead Capture (Flutter Mobile App) + Sales Completion & Manager Quality Review (Web Portal) + External Banker Handoff & Customer Pendency Notification Cycle.
- **Out of Scope (By Design)**: Direct Banking LOS APIs, automatic disbursement webhooks, and automated underwriting sanction engines. External banking processing is conducted outside the software platform.

---

## 2. Design System & Brand Identity (Modern Trust Fintech)
### Color Palette:
| Token Name | Hex Code | Purpose / Usage |
|---|---|---|
| **Primary** | `#123B6D` | Deep Navy (App bar, Primary buttons, Brand identity) |
| **Primary Dark** | `#0B294D` | Dark Blue (Pressed states, Headers) |
| **Accent / Action** | `#16A085` | Restrained Teal (Action buttons, positive highlights) |
| **Background** | `#F6F8FB` | Cool Light Grey (Scaffold background) |
| **Surface** | `#FFFFFF` | Clean White (Cards, Bottom sheets, Dialogs) |
| **Text Primary** | `#172033` | Dark Charcoal (Headings, Main form labels) |
| **Text Secondary** | `#667085` | Muted Grey (Subtitles, Timestamps, Helper text) |
| **Border / Divider** | `#E4E8EF` | Subtle borders for inputs and cards |
| **Success** | `#168A5B` | Approved / Verified / Resolved states |
| **Warning** | `#D99000` | Pendency / Action Required alerts |
| **Error / Destructive** | `#D64545` | Rejection banners, Field validation errors |

---

## 3. Architecture & Monorepo Structure
- **Monorepo Layout**:
  - `mobile/`: Flutter Mobile App (Customer-facing, State Management: BLoC / Cubit)
  - `backend/`: Laravel 12 Web Portal & REST API (Sales/Manager Dashboard, MySQL Database, Sanctum Auth)
- **Role-Based Access Control (RBAC)**:
  1. **Customer**: Mobile + OTP Login (Single active application per mobile number).
  2. **Sales Executive**: Web portal login (`sales@loandesk.com` / `password123`). Receives leads, contacts applicant via phone, gathers required documentation, populates client-defined fields, and submits for manager review.
  3. **Manager / Admin**: Global overview (`admin@loandesk.com` / `password123`). Reviews submitted applications, rejects with mandatory reason, marks "Ready for Bank" for external handoff, and registers banker pendencies requiring customer resolution.

---

## 4. Flutter Customer Mobile App Architecture (`mobile/`)
- **State Management**: BLoC / Cubit (`AuthCubit`, `LoanCubit`)
- **State-Driven Routing**: `AppEntryGate` (Zero navigation clutter)
  - Unauthenticated -> `LoginScreen` (+91 Mobile input) -> `OtpScreen` (6-digit PIN box, 30s resend timer, dev bypass `123456`).
  - Authenticated & No active loan -> `ApplyLoanScreen` (Modular form: Name, Type, Amount, City, Pincode, Referral code).
  - Authenticated & Active loan in progress -> `StatusDashboardScreen` (Live timeline, status badge, pull-to-refresh).
  - Banker pendency raised -> In-place `PendencyActionCard` alert banner -> Tap launches `ResolvePendencySheet` (File upload max 5MB + explanation notes).
  - Application declined -> Rejection Banner with credit officer reason + *"Start Fresh Application"* action.
- **Automated Verification**: `flutter test` smoke test suite verified with `0 errors`.

---

## 5. API Endpoints Specification (Tested & Live)
| Endpoint | Method | Auth | Payload / Params | Response | Usage / Screen |
|---|---|---|---|---|---|
| `/api/v1/auth/send-otp` | `POST` | Public | `{ "phone": "9876543210" }` | `{ "success": true, "data": { "resend_cooldown_seconds": 30 } }` | Customer Login Screen |
| `/api/v1/auth/verify-otp` | `POST` | Public | `{ "phone": "9876543210", "otp": "123456" }` | `{ "token": "...", "user": { ... } }` | Customer OTP Verification |
| `/api/v1/loan-types` | `GET` | Public | None | `[{ "id": 1, "name": "Personal Loan", "code": "personal" }]` | Apply Loan Screen Dropdown |
| `/api/v1/applications` | `POST` | Sanctum | `{ "applicant_name": "...", "loan_type_id": 1, "requested_amount": 500000, "city": "Jaipur", "pincode": "302001", "referral_code": "..." }` | `{ "success": true, "data": { "application_number": "LD-2026-00001" } }` | Apply Loan Screen Submit |
| `/api/v1/applications/active` | `GET` | Sanctum | None | Active LoanApplication object with `loanType`, `activePendency`, `activities` | Customer Status Dashboard |
| `/api/v1/pendencies/{id}/resolve` | `POST` | Sanctum | Multipart: `response_text`, `file` (PDF/Image max 5MB) | `{ "success": true, "message": "..." }` | Pendency Bottom Sheet Modal |

---

## 6. Web Portal Operations Architecture (100% Livewire Reactive Components)
- **Framework**: Laravel 12 + **Livewire 3/4** (Zero-reload reactive SPA experience across the entire web application).
- **100% Views Converted to Livewire**: All legacy Blade views have been completely removed and refactored into pure reactive Livewire components.
- **Interactive Livewire Components Suite**:
  1. `App\Livewire\Home` (`resources/views/livewire/home.blade.php`):
     - Public landing page with Modern Trust Fintech design.
     - Live interactive EMI Calculator widget (Loan amount, interest rate, tenure sliders with real-time monthly EMI, total interest, and total payable calculation).
     - Active loan products showcase.
  2. `App\Livewire\Components\Navbar` (`resources/views/livewire/components/navbar.blade.php`):
     - Livewire reactive navigation bar with active user role badge.
     - Real-time notification counters (`New Leads` & `For Review` alerts).
     - Reactive sign-out action (`wire:click="logout"`).
  3. `App\Livewire\Auth\Login` (`resources/views/livewire/auth/login.blade.php`):
     - Staff authentication with live validation and loading indicators.
  4. `App\Livewire\Dashboard` (`resources/views/livewire/dashboard.blade.php`):
     - Real-time debounced lead search (`wire:model.live.debounce.300ms="search"`).
     - Reactive metric pipeline cards (`All`, `New`, `Calling`, `Review`, `With Bank`, `Pendency`, `Disbursed`, `Rejected`).
     - Sales Rep "My Leads" toggle (`wire:click="toggleMyLeads"`).
     - Live pagination (`WithPagination`) and instant status badges.
  5. `App\Livewire\Applications\ApplicationDetail` (`resources/views/livewire/applications/application-detail.blade.php`):
     - Sales Lead Assignment (`wire:click="assignSales"`).
     - Detailed Caller Form auto-save with live field validation (`wire:click="saveDetails"`).
     - Live document upload (`WithFileUploads`) with upload progress indicator.
     - Sales Review Submission (`wire:click="submitReview"`).
     - Manager "Ready for Bank" external handoff (`wire:click="readyForBank"`).
     - Interactive Banker Pendency Modal (`$showPendencyModal` + `wire:click="savePendency"`).
     - Interactive Application Rejection Modal with mandatory reason (`$showRejectModal` + `wire:click="confirmReject"`).
     - Loan Disbursement Completion (`wire:click="markCompleted"`).
     - Live Activity Audit Trail & Pendency resolution tracker.
- **Form POST Endpoints**: Preserved for automated feature testing & backward compatibility (`applications.assign`, `applications.update-details`, etc.).

---

## 7. MySQL Database Details
- **Database Name**: `loan_application`
- **Host**: `127.0.0.1:3306`
- **Username**: `root`
- **Password**: `password`
- **Tables**: `users`, `otp_verifications`, `loan_types`, `loan_applications`, `application_documents`, `pendencies`, `application_activity_logs`, `personal_access_tokens`.
- **Raw SQL Reference**: `backend/database/schema_raw.sql`
