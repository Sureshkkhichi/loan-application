# Loan Application - Product Requirements & Technical Architecture (PRD)

## 📌 1. Project Overview & Business Scope
- **Project Name**: Loan Application & Lead Collection System (Brand: **LoanDesk**)
- **Core Scope**: Customer Lead Capture (Flutter Mobile App) + Sales Completion & Manager Review (Web Portal) + Banker External Handoff & Pendency Notification Cycle.
- **Out of Scope (By Design)**: Direct Bank LOS APIs, automatic banking disbursements, or internal bank loan sanctioning. Bank processing completely system ke bahar (externally) handle hoti hai.

---

## 🎨 2. Design System & Theme Guidelines (Modern Trust Fintech)
> **Core Principle**: Ye koi flashy gamified app ya PhonePe/GPay clone nahi hai. Customer ko feel hona chahiye: *"Ye ek professional financial service hai aur meri application safely handle ho rahi hai."*

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

## 🏛️ 3. Architectural Blueprint & Repository Structure
- **Monorepo Layout**:
  - `mobile/`: Flutter Mobile App (Customer-facing, State Management: BLoC / Cubit)
  - `backend/`: Laravel 12 Web Portal & REST API (Sales/Manager Dashboard, MySQL Database, Sanctum Auth)
- **Role-Based Access Control (RBAC)**:
  1. **Customer**: Mobile + OTP Login (Single active application per mobile number).
  2. **Sales Executive**: Web portal login (`sales@loandesk.com` / `password123`). Leads receive karna, customer ko call karke detailed form fill karna, documents upload karna, "Submit for Review" karna.
  3. **Manager / Admin**: Applications review karna (`admin@loandesk.com` / `password123`), Reject (with mandatory reason) karna, "Ready for Bank" mark karke external banker ko share karna, Banker ki Pendency system me add karna.

---

## 📱 4. Flutter Customer Mobile App Architecture (`mobile/`)
- **State Management**: BLoC / Cubit (`AuthCubit`, `LoanCubit`)
- **Entry Routing**: State-driven `AppEntryGate` (Zero navigation clutter)
  - Agar user logged-in nahi hai -> `LoginScreen` (+91 Mobile number input) -> `OtpScreen` (6-digit PIN box, 30s resend timer, dev bypass `123456`).
  - Agar logged-in hai aur koi active application nahi hai -> `ApplyLoanScreen` (Modular fixed form: Name, Type, Amount, City, Pincode, Referral code).
  - Agar active application in progress hai -> `StatusDashboardScreen` (Live timeline, status chip, pull-to-refresh).
  - Agar banker ne pendency raise ki -> In-place `PendencyActionCard` alert banner -> Tap opens `ResolvePendencySheet` (File upload max 5MB + explanation remarks note).
  - Agar application reject ho gayi -> Rejection Banner with credit officer reason + *"Start Fresh Application"* action.
- **Automated Verification**: `flutter test` smoke test suite passed with `0 errors`.

---

## 🌐 5. API Endpoints Specification (Tested & Live)
| Endpoint | Method | Auth | Payload / Params | Response | Usage / Screen |
|---|---|---|---|---|---|
| `/api/v1/auth/send-otp` | `POST` | Public | `{ "phone": "9876543210" }` | `{ "success": true, "data": { "resend_cooldown_seconds": 30 } }` | Customer Login Screen |
| `/api/v1/auth/verify-otp` | `POST` | Public | `{ "phone": "9876543210", "otp": "123456" }` | `{ "token": "...", "user": { ... } }` | Customer OTP Verification |
| `/api/v1/loan-types` | `GET` | Public | None | `[{ "id": 1, "name": "Personal Loan", "code": "personal" }]` | Apply Loan Screen Dropdown |
| `/api/v1/applications` | `POST` | Sanctum | `{ "applicant_name": "...", "loan_type_id": 1, "requested_amount": 500000, "city": "Jaipur", "pincode": "302001", "referral_code": "..." }` | `{ "success": true, "data": { "application_number": "LD-2026-00001" } }` | Apply Loan Screen Submit |
| `/api/v1/applications/active` | `GET` | Sanctum | None | Active LoanApplication object with `loanType`, `activePendency`, `activities` | Customer Status Dashboard |
| `/api/v1/pendencies/{id}/resolve` | `POST` | Sanctum | Multipart: `response_text`, `file` (PDF/Image max 5MB) | `{ "success": true, "message": "..." }` | Pendency Bottom Sheet Modal |

---

## 💻 6. Web Portal Operations Routes
- `GET /login` & `POST /login`: Staff Authentication
- `GET /dashboard`: Overview metrics counters & filtered leads table
- `GET /applications/{id}`: Detailed Lead Review sheet, caller form, and action bar
- `POST /applications/{id}/assign`: Assign lead to sales executive
- `POST /applications/{id}/update-details`: Sales updates caller form data & discussion notes
- `POST /applications/{id}/upload-doc`: Sales uploads customer documents
- `POST /applications/{id}/submit-review`: Submit lead to manager (`SUBMITTED_FOR_REVIEW`)
- `POST /applications/{id}/ready-for-bank`: Manager marks `READY_FOR_BANK` for external banker
- `POST /applications/{id}/add-pendency`: Manager injects banker pendency (`PENDENCY_RAISED`)
- `POST /applications/{id}/reject`: Manager rejects with mandatory reason (`REJECTED`)
- `POST /applications/{id}/complete`: Manager marks loan as disbursed/completed (`COMPLETED`)

---

## 🗄️ 7. MySQL Database Details
- **Database Name**: `loan_application`
- **Host**: `127.0.0.1:3306`
- **Username**: `root`
- **Password**: `password`
- **Tables**: `users`, `otp_verifications`, `loan_types`, `loan_applications`, `application_documents`, `pendencies`, `application_activity_logs`, `personal_access_tokens`.
- **Raw SQL Reference**: `backend/database/schema_raw.sql`
