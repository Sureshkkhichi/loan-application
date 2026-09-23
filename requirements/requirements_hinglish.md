# Loan Application - Product Requirements & Technical Architecture (PRD)

## 📌 1. Project Overview & Scope
- **Project Name**: Loan Application
- **Platform Scope**: Mobile App (Flutter) / Backend / Admin Panel (as defined)
- **Objective**: End-to-end digital lending lifecycle manage karna - User onboarding, KYC verification, Loan eligibility calculation, Loan application submission, Approval/Disbursement workflow, aur Repayment/EMI tracking.

---

## 🔄 2. Complete Project Flow & SOP (Standard Operating Procedure)
1. **User Onboarding & Authentication**:
   - Mobile OTP-based signup/login.
   - Profile setup (Name, Email, PAN, Aadhaar info, Employment details).
2. **KYC & Document Verification**:
   - Identity & Address verification.
   - Bank statement upload / Financial data collection.
3. **Loan Discovery & Eligibility Assessment**:
   - Loan types (Personal Loan, Business Loan, Instant Cash Loan, etc.).
   - Credit score / Rule-engine based eligibility check.
4. **Loan Application Submission**:
   - Loan amount, tenure, EMI frequency selection.
   - Bank account linking for disbursement & auto-debit (eNACH / Mandate).
5. **Underwriting & Approval**:
   - Automated rule engine + Admin/Credit manager review.
   - Sanction letter generation & e-Sign.
6. **Disbursement**:
   - Direct bank transfer via payment gateway / payout API.
7. **Repayment & EMI Lifecycle**:
   - EMI schedules, Upcoming due alerts, Auto-debit retry mechanism.
   - Manual payment gateway integration (UPI, Netbanking, Cards).
   - Loan closure & NOC certificate generation.

---

## 🧩 3. Module-wise Breakdown & Business Rules
- **Module 1: Authentication & User Profile**
  - Unique mobile number constraint.
  - JWT / Session token authentication with refresh mechanism.
- **Module 2: KYC & Compliance**
  - PAN format validation (`[A-Z]{5}[0-9]{4}[A-Z]{1}`).
  - Aadhaar masking / DigiLocker / Verification API integration.
- **Module 3: Loan Products & Calculator**
  - Configurable interest rates (Flat / Reducing balance), processing fees, GST.
  - EMI calculation logic: \( E = P \times r \times \frac{(1+r)^n}{(1+r)^n - 1} \).
- **Module 4: Loan Application & Underwriting**
  - Application states: `DRAFT`, `SUBMITTED`, `UNDER_REVIEW`, `APPROVED`, `REJECTED`, `DISBURSED`, `CLOSED`, `DEFAULTED`.
- **Module 5: Payments & Ledger**
  - Immutable transaction log for every debit/credit.
  - Penalty calculation for overdue payments.

---

## 🌐 4. API Mapping Architecture
| Endpoint | Method | Purpose | Request Payload | Response | Screen / Module |
|---|---|---|---|---|---|
| `/api/v1/auth/send-otp` | `POST` | Send Mobile OTP | `{ "phone": "string" }` | `{ "status": true, "requestId": "string" }` | Auth Screen |
| `/api/v1/auth/verify-otp` | `POST` | Verify OTP & Login | `{ "phone": "string", "otp": "string" }` | `{ "token": "jwt", "user": {} }` | OTP Screen |
| `/api/v1/loans/products` | `GET` | Fetch loan products | Header Auth | `[{ "id": "1", "name": "Personal Loan", ... }]` | Dashboard |
| `/api/v1/loans/apply` | `POST` | Submit loan application | `{ "productId": "1", "amount": 50000, "tenureMonths": 12 }` | `{ "applicationId": "xyz", "status": "SUBMITTED" }` | Apply Screen |
| `/api/v1/loans/active` | `GET` | Current active loans & EMIs | Header Auth | `[{ "loanId": "xyz", "nextEmiDate": "...", ... }]` | Repayments |

---

## 🗄️ 5. Database Schema & Data Models (Initial Blueprint)
- **`users`**: id, full_name, phone, email, pan_number, kyc_status, created_at, updated_at
- **`loan_products`**: id, name, min_amount, max_amount, min_tenure, max_tenure, interest_rate, processing_fee_pct, is_active
- **`loan_applications`**: id, user_id, product_id, applied_amount, approved_amount, tenure_months, interest_rate, status, sanctioned_at, disbursed_at
- **`repayment_schedules`**: id, application_id, installment_no, due_date, principal_amount, interest_amount, total_emi, status (`PENDING`, `PAID`, `OVERDUE`)
- **`transactions`**: id, application_id, user_id, type (`DISBURSEMENT`, `REPAYMENT`, `PENALTY`), amount, status, gateway_ref_id, created_at

---

## 🔐 6. Security & Compliance
- AES-256 encryption for sensitive PII (PAN, Bank details).
- HTTPS / TLS 1.3 for all in-transit communications.
- Audit logs for admin approvals and financial transactions.
