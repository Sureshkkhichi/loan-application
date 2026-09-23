# Loan Application - Product Requirements Document & Technical Architecture

## 1. Project Overview & Scope
- **Project Name**: Loan Application
- **Platform Scope**: Mobile App (Flutter) / Backend / Admin Panel
- **Objective**: Manage the entire digital lending lifecycle: User onboarding, KYC verification, Loan eligibility assessment, Loan application processing, Approval/Disbursement workflows, and EMI Repayment management.

---

## 2. Complete Project Workflow & SOP
1. **User Onboarding & Authentication**:
   - OTP-based mobile authentication.
   - Profile creation (Identity, Contact, Employment information).
2. **KYC & Document Verification**:
   - Government ID verification (PAN, Aadhaar/DigiLocker).
   - Financial document ingestion (Bank statements / Payslips).
3. **Loan Discovery & Eligibility Assessment**:
   - Loan catalog (Personal, Business, Instant Credit).
   - Real-time or batch credit score evaluation and eligibility check.
4. **Loan Application Submission**:
   - Configuration of loan principal, tenure, and repayment schedule.
   - Bank mandate registration (eNACH / UPI Autopay).
5. **Underwriting & Decisioning**:
   - Algorithmic credit decisioning + Credit officer underwriting.
   - Sanction letter generation, digital signing, and agreement execution.
6. **Disbursement**:
   - Automated payout to verified beneficiary account via banking APIs.
7. **Repayment & Servicing**:
   - Automated EMI debits, payment notifications, and reconciliation.
   - Manual payment options (UPI, Net Banking, Debit Card).
   - Full settlement, No-Objection Certificate (NOC) issuance, and loan closure.

---

## 3. Module Breakdown & Business Rules
- **Module 1: Authentication & User Management**
  - Unique mobile number constraint per active identity.
  - JWT token architecture with short-lived access tokens and refresh token rotation.
- **Module 2: KYC & Compliance**
  - PAN validation standard (`[A-Z]{5}[0-9]{4}[A-Z]{1}`).
  - Secure vaulting of sensitive verification tokens.
- **Module 3: Loan Products & Calculator**
  - Reducing balance EMI calculation: \( E = P \times r \times \frac{(1+r)^n}{(1+r)^n - 1} \).
  - Parametric fees: processing fees, GST, broken-period interest.
- **Module 4: Underwriting & Application Lifecycle**
  - State machine: `DRAFT` -> `SUBMITTED` -> `UNDER_REVIEW` -> `APPROVED` -> `DISBURSED` -> `CLOSED` (or `REJECTED` / `DEFAULTED`).
- **Module 5: Payment & General Ledger**
  - Double-entry ledger or immutable transactional ledger for disbursements, collections, and penalties.

---

## 4. API Specification & Mapping
| Endpoint | Method | Description | Request Payload | Response | Client Module |
|---|---|---|---|---|---|
| `/api/v1/auth/send-otp` | `POST` | Dispatch OTP | `{ "phone": "string" }` | `{ "status": true, "requestId": "string" }` | Auth Screen |
| `/api/v1/auth/verify-otp` | `POST` | Verify OTP | `{ "phone": "string", "otp": "string" }` | `{ "token": "jwt", "user": {} }` | OTP Screen |
| `/api/v1/loans/products` | `GET` | Retrieve available products | Bearer Token | `[{ "id": "1", "name": "Personal Loan", ... }]` | Catalog / Home |
| `/api/v1/loans/apply` | `POST` | Submit new application | `{ "productId": "1", "amount": 50000, "tenureMonths": 12 }` | `{ "applicationId": "xyz", "status": "SUBMITTED" }` | Application Form |
| `/api/v1/loans/active` | `GET` | List active loans | Bearer Token | `[{ "loanId": "xyz", "nextEmiDate": "...", ... }]` | Dashboard / Repayment |

---

## 5. Database Schema & Architecture
- **`users`**: id, full_name, phone, email, pan_number, kyc_status, created_at, updated_at
- **`loan_products`**: id, name, min_amount, max_amount, min_tenure, max_tenure, interest_rate, processing_fee_pct, is_active
- **`loan_applications`**: id, user_id, product_id, applied_amount, approved_amount, tenure_months, interest_rate, status, sanctioned_at, disbursed_at
- **`repayment_schedules`**: id, application_id, installment_no, due_date, principal_amount, interest_amount, total_emi, status (`PENDING`, `PAID`, `OVERDUE`)
- **`transactions`**: id, application_id, user_id, type (`DISBURSEMENT`, `REPAYMENT`, `PENALTY`), amount, status, gateway_ref_id, created_at

---

## 6. Security & Infrastructure
- Field-level AES-256 encryption for PII and financial tokens.
- Strict TLS 1.3 encryption in transit.
- Comprehensive audit trails for credit assessments and financial events.
