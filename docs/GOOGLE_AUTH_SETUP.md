# CASECRAFT - Google Identity & OAuth 2.0 Setup Guide
**CID High-Security Investigation Management System**

CaseCraft includes a production-grade, enterprise-ready Google Authentication gateway supporting:
1. **Google OAuth 2.0 Authorization Code Flow** (Server-to-Server encrypted token exchange).
2. **Google Identity Services (GIS) / One-Tap & Popup Sign-In** (Cryptographic JWT verification via Google's tokeninfo API).
3. **Automated Officer Provisioning & Data Isolation** (Zero-Knowledge: Each officer only accesses their own cases).

---

## 🚀 Quick Setup (2 Minutes)

### Step 1: Open Google Cloud Console
1. Navigate to: [https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)
2. Create or select a Google Cloud Project (e.g., `casecraft-cid-intel`).
3. Click **Configure Consent Screen** (if not done yet):
   - User Type: **External** (or Internal if using Google Workspace).
   - App Name: `CaseCraft CID Portal`.
   - User support email: Select your email.
   - Developer contact email: Enter your email.
   - Scopes: `.../auth/userinfo.email`, `.../auth/userinfo.profile`, `openid`.
   - Save and continue.

### Step 2: Create OAuth 2.0 Client Credentials
1. Go back to **Credentials** &rarr; Click **+ CREATE CREDENTIALS** &rarr; Select **OAuth client ID**.
2. **Application type:** Select **Web application**.
3. **Name:** `CaseCraft Web Client`.
4. **Authorized JavaScript origins:**
   ```
   http://localhost:8000
   ```
5. **Authorized redirect URIs:**
   ```
   http://localhost:8000/auth/google/callback
   ```
6. Click **CREATE**.

---

## 🔑 Activating Google Credentials in CaseCraft

You have **two simple options**:

### Option A: Via Login UI (Easiest - 10 Seconds)
1. Open CaseCraft in browser: `http://localhost:8000/login`
2. Click **Sign in with Google Account** &rarr; Select the **Google Cloud API Config** tab.
3. Paste your **Client ID** and **Client Secret**.
4. Click **Save & Activate Google Cloud Credentials**.
5. Done! The official Google OAuth 2.0 button will immediately be live and active.

### Option B: Download JSON File
1. In Google Cloud Console, click the Download icon next to your OAuth client to get the JSON file.
2. Rename or save the file as:
   `config/credentials.json` (or `config/google_client_secret.json`).
3. CaseCraft will automatically detect and load your Client ID and Client Secret!

---

## 🛡️ Security Features
- **Token Verification:** Every Google token is cryptographically verified against `https://oauth2.googleapis.com/tokeninfo`.
- **Audience (aud) Check:** Prevents cross-app token reuse and token spoofing.
- **CSRF Protection:** State parameter validation (`state=...`) prevents OAuth cross-site request forgery.
- **Zero-Knowledge Officer Privacy:** Officer A can never view Officer B's classified case files. Any attempt to browse unauthorized case IDs returns `403 Forbidden`.
