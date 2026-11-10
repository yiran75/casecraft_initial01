# CaseCraft – Investigation Case Management Web Application

> **Academic Scope Notice:** CaseCraft is an academic prototype using fictional sample data only. It is not an officially approved police or law-enforcement system. Real-world deployment would require formal legal authorization, security auditing, privacy safeguards, and strict chain-of-custody protocols.

---

## 1. Project Overview & Features
CaseCraft is a centralized web application designed for authorized investigation personnel to organize investigation case files and related records. It replaces scattered paper notes and disconnected files with structured case records.

### Core Features:
- **Case CRUD & Lifecycle:** Create, view, edit, and track status (`Open`, `Under Investigation`, `Pending Review`, `Closed`, `Archived`).
- **Investigator Assignment Matrix:** Assign lead investigators and specialists to cases.
- **Evidence Management & Secure Attachments:** Catalog physical and digital evidence with strict file-type allowlists (PDF, JPG, PNG) and non-public storage with authenticated downloads.
- **Witness Statements & Tactical Notes:** Record chronological interview narratives and internal notes tied to case dossiers.
- **Audit Activity Timeline:** Immutable logging of status changes, uploads, and edits.
- **Live AJAX Status Console:** Change case statuses asynchronously via Fetch API with instant feedback.
- **Dashboard & Chart.js Visualizations:** Workload KPIs, case status doughnut chart, and crime category bar charts.
- **Dossiers & Exports:** Formal printable case reports and CSV case registry export.
- **Role-Based Access Control (RBAC):** Server-side enforcement for `admin`, `investigator`, and `reviewer` roles.
- **Security Protections:** `password_hash()` BCRYPT, CSRF tokens on all state-changing requests, login throttling against brute-force attacks, generic login errors, and output sanitization against XSS.

---

## 2. Default Demo User Accounts
All default seeded accounts use the password: `password`

| Role | Email Address | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@casecraft.local` | `password` | Full system access, User Management, Global Audit Logs |
| **Lead Investigator** | `investigator@casecraft.local` | `password` | Case creation, updates, evidence, witness statements, notes |
| **Forensic Analyst** | `dchen@casecraft.local` | `password` | Case creation, evidence uploads, technical notes |
| **Legal Reviewer** | `reviewer@casecraft.local` | `password` | Read-only inspection of assigned cases and dossiers |

---

## 3. Quick Setup & Run Instructions

### Prerequisites:
- **XAMPP for Windows** (PHP 8.2+ and MySQL / MariaDB) OR standalone PHP 8.0+ and MySQL.
- Any modern web browser (Chrome, Edge, Firefox).

---

### Step A: Database Setup (phpMyAdmin)
1. Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. Visit `http://localhost/phpmyadmin` in your browser.
3. Click **New** in the left sidebar and name the database: `casecraft_db`
4. Click the **SQL** tab and execute the SQL file located at `database/schema.sql` and `database/seed.sql`.

---

### Step B: Running the Web Application

#### Option 1: Using PHP's Built-in Development Server (Recommended & Instant!)
1. Open your terminal or VS Code terminal in the project directory:
   ```powershell
   cd C:\Users\USER\.gemini\antigravity\scratch\casecraft
   ```
2. Start the PHP server pointing to the `public/` web root:
   ```powershell
   & "C:\xampp\php\php.exe" -S localhost:8000 -t public
   ```
3. Open your browser and navigate to:  
   👉 **`http://localhost:8000`**

#### Option 2: Running directly inside XAMPP Apache
1. Copy or symlink the `casecraft` project folder to `C:\xampp\htdocs\casecraft`.
2. Open your browser and navigate to:  
   👉 **`http://localhost/casecraft/public/`**

---

## 4. Architecture & Directory Structure
```text
casecraft/
├── app/
│   ├── Controllers/     # Request handlers (Auth, Case, Dashboard, Evidence, etc.)
│   ├── Models/          # PDO Models (CaseModel, User, Evidence, Notes, etc.)
│   ├── Services/        # AuthService, FileUploadService, ActivityLogger
│   ├── Middleware/      # AuthMiddleware, RoleMiddleware, CsrfMiddleware
│   └── Helpers/         # View, Session, Csrf, Sanitizer
├── config/              # app.php, database.php
├── database/            # schema.sql, seed.sql, erd.dbml, migrate.php
├── public/              # Web Root (index.php, .htaccess, assets)
├── resources/views/     # Blade-style UI Templates & Layouts
├── routes/              # web.php
├── storage/             # Secure uploads/ (non-public) & logs/
└── docs/                # Academic documentation, AI_LOG, Test checklists
```

---

## 5. Security Summary
- **SQL Injection:** 100% prepared PDO statements with bound parameters across all database queries.
- **XSS Prevention:** Automatic HTML entity escaping via `e()` and `Sanitizer::e()` on all rendered variables.
- **CSRF Protection:** Cryptographically secure 256-bit session tokens validated for all forms and AJAX endpoints.
- **Authentication:** `password_hash()` (BCRYPT) and `password_verify()`. Session ID regeneration upon login.
- **Brute Force Lockout:** Automatically locks login attempts after 5 consecutive failed tries for 15 minutes.
- **File Upload Security:** Rigid extension and MIME type allowlists (PDF, JPG, PNG only). Non-executable storage outside public document root. Authenticated streaming downloads.
