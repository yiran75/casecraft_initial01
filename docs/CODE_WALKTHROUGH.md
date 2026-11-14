# CaseCraft – Code Walkthrough & Architectural Analysis

This walkthrough details the core structural files, critical architectural design decisions, and real-world debugging encountered during development.

---

## 1. Analysis of Three Key Source Files

### File 1: `app/Services/AuthService.php`
- **Purpose:** Serves as the security and authentication engine for CaseCraft.
- **Key Implementation Highlights:**
  - **Password Security:** Uses `password_verify()` against standard BCRYPT hashes (`$2y$10$...`). Plaintext passwords are never persisted.
  - **Brute-Force Login Throttling:** Implements rate-limiting tracking consecutive failed login attempts by composite hash of user email and remote IP. After 5 failed attempts, the account is temporarily locked for 15 minutes (900 seconds).
  - **Session Fixation Defense:** Calls `session_regenerate_id(true)` immediately upon credential verification to invalidate pre-authentication session tokens.
  - **Anti-Enumeration Generic Errors:** Returns identical error strings ("Invalid email or password.") whether the email exists or not, preventing reconnaissance probing.

### File 2: `app/Models/CaseModel.php`
- **Purpose:** The central data layer for case records, dynamic multi-filtering, pagination, and statistical aggregations.
- **Key Implementation Highlights:**
  - **Parameterized PDO Bindings:** Dynamically composes SQL queries based on active filters (status, category, priority, date range, and investigator assignments) using named parameters to ensure 100% SQL injection immunity.
  - **Chart Aggregations:** Groups and tallies records by lifecycle status and crime category to feed Chart.js doughnut and bar charts.
  - **Transparent "Possible Links" Heuristic:** Executes transparent proximity and category matching queries to suggest related cases for human investigator review, explicitly labeling the rationale (e.g. "Shared Category: Cybercrime" or "Opened within 30 days").

### File 3: `app/Services/FileUploadService.php`
- **Purpose:** Governs evidence file integrity and storage security.
- **Key Implementation Highlights:**
  - **Strict Allowlist:** Permitted types strictly limited to safe demonstrator formats (`application/pdf`, `image/jpeg`, `image/png`).
  - **Real MIME Inspection:** Utilizes PHP's `finfo(FILEINFO_MIME_TYPE)` on the server side; does not trust client-supplied MIME headers or file extensions.
  - **Random Filename Obfuscation:** Generates a 32-character hexadecimal filename using `bin2hex(random_bytes(16))` to prevent path traversal and overwriting.
  - **Protected Delivery:** Files reside in `storage/uploads` outside the public web root and are streamed only via authenticated endpoints that verify authorization before piping bytes via `readfile()`.

---

## 2. Two Key Architectural Design Decisions

### Decision 1: Lightweight Frameworkless MVC vs. Heavy Framework (e.g. Laravel)
- **Rationale:** An academic submission must be transparent, easily portable across basic local environments (such as XAMPP, WAMP, or PHP CLI), and run without requiring external package managers (`composer`), Node build tools, or complex web server rewrite rules.
- **Impact:** By implementing a custom Front Controller (`public/index.php`), lightweight Router (`routes/web.php`), and standard PDO models, CaseCraft achieves full enterprise-grade architecture with zero external runtime dependencies.

### Decision 2: Isolated Storage with Authenticated Stream Routing
- **Rationale:** Allowing direct browser access to uploaded evidence files (e.g., placing them inside `public/uploads/`) poses severe security hazards: users could execute malicious scripts if validation failed, and unauthorized users could guess filenames to inspect confidential case documents.
- **Impact:** Evidence attachments are saved in `storage/uploads/` (outside the public directory) and served exclusively through `/evidence/download?id=...`, which verifies active session authentication and investigator permissions before streaming.

---

## 3. Real Bug Encountered & Resolution

### Encountered Issue:
During initial database setup, importing the demo seed data generated a fatal MySQL error:
`#1146 - Table 'casecraft_db.users' doesn't exist`

### Cause Analysis:
The database seeding script (`seed.sql`) contained `INSERT INTO users ...` statements that assumed the schema definitions had already been processed. When executing the seed script prior to or in isolation from `schema.sql`, MySQL failed due to missing table structures.

### Solution & Fix:
1. Updated the setup procedure and documentation to combine table creation (`schema.sql`) and sample data seeding (`seed.sql`) into a single sequential operation with `SET FOREIGN_KEY_CHECKS = 0;` and `SET FOREIGN_KEY_CHECKS = 1;` wrappers.
2. Verified that all foreign key constraints properly cascade on child tables (`evidence`, `witness_statements`, `investigation_notes`, `case_assignments`, `case_activity_logs`) without dependency deadlocks.
