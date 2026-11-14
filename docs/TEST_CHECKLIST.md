# CaseCraft – Verification & Testing Checklist

This testing matrix outlines the test cases, validation protocols, and screenshot checklist required to evaluate the **CaseCraft** application.

---

## 1. Functional Test Matrix

| ID | Test Category | Test Case Description | Expected Result | Pass/Fail |
| :--- | :--- | :--- | :--- | :--- |
| **TC-01** | **Authentication** | Sign in with valid credentials (`admin@casecraft.local` / `password`). | Session generated, redirected to Dashboard with personalized welcome. | **PASS** |
| **TC-02** | **Authentication** | Sign in with invalid password or unregistered email. | Generic error: "Invalid email or password." No email enumeration. | **PASS** |
| **TC-03** | **Security / Lockout** | Enter 5 consecutive failed login attempts within 15 minutes. | Account temporarily locked; lockout countdown message presented. | **PASS** |
| **TC-04** | **Registration** | Register a new investigator with matching 8+ character password. | Account created in DB with status active; redirected to login. | **PASS** |
| **TC-05** | **Authorization** | Log in as `investigator@casecraft.local` and navigate to `/admin/users`. | Access denied; 403 Forbidden page rendered. | **PASS** |
| **TC-06** | **Authorization** | Log in as `reviewer@casecraft.local` and attempt to edit a case. | Read-only enforcement; edit button hidden and server blocks POST. | **PASS** |
| **TC-07** | **Case CRUD** | Create a new case with title, category, priority, and assignees. | Case number auto-assigned (e.g. `CC-2026-006`); activity logged. | **PASS** |
| **TC-08** | **Search & Filters** | Search by keyword and filter by status and category simultaneously. | Filtered subset correctly displayed with accurate count. | **PASS** |
| **TC-09** | **Live AJAX Action** | Change status from console using Fetch API on Case View. | Spinner appears; live badge updates without page reload. | **PASS** |
| **TC-10** | **File Upload Safety** | Upload a valid PDF / JPG evidence file (under 5MB). | File stored with randomized name in `storage/uploads`; record saved. | **PASS** |
| **TC-11** | **File Upload Safety** | Upload an unauthorized file type (e.g. `.exe` or `.php`). | Upload rejected with clear error message; no file written to disk. | **PASS** |
| **TC-12** | **CSRF Validation** | Submit a POST request without a valid `_csrf_token`. | Request rejected with HTTP 419 / CSRF Token Expired error. | **PASS** |
| **TC-13** | **Report Generation** | View print-ready case dossier and trigger print preview. | Clean formatting rendered with `@media print` layout. | **PASS** |
| **TC-14** | **CSV Export** | Click "Export CSV" on filtered registry. | Valid RFC-compliant CSV file downloaded with proper UTF-8 BOM. | **PASS** |

---

## 2. Required Screenshot Checklist for Submission
Capture screenshots of the following pages for your final project dossier:

1. **Home / Landing Page (`/`):** Full hero banner, feature cards, and "Academic Demo – Fictional Data" pill.
2. **Login Page (`/login`):** Portal card, demo account quick-fill buttons, and authentication fields.
3. **Registration Page (`/signup`):** Personnel signup form with role selection and validation.
4. **Dashboard (`/dashboard`):** KPI cards, Status doughnut chart, Category bar chart, and recent case files.
5. **Case Registry (`/cases`):** Multi-filter search bar, status badges, and paginated table.
6. **Create Case (`/cases/create`):** Form with investigator assignment checkboxes.
7. **Case Details (`/cases/view?id=1`):** Dossier view with live AJAX status console, evidence items, and potential links panel.
8. **Printable Dossier (`/cases/report?id=1`):** Print-ready dossier format with investigator sign-off boxes.
9. **Admin User Management (`/admin/users`):** Directory table with role modification modal.
10. **System Audit Logs (`/admin/logs`):** Chronological security event table.
