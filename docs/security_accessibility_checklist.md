# Practical 15: Security & Accessibility Audit Checklist

**Course:** Web Development Frameworks (ITUE203)  
**Developer:** Dhruvi Mokani (25CS047)  
**Institute:** CSPIT, CHARUSAT University  

---

## Part 1: OWASP Web Security Audit

| Security Domain | Vulnerability Risk | Implemented Defense Mechanism in StudentHub | Status |
| :--- | :--- | :--- | :---: |
| **SQL Injection (SQLi)** | Attacker manipulates SQL queries through raw input. | 100% prepared statements with parameterized inputs using `PDO::prepare()` and `mysqli_prepare()`. No raw string concatenation in queries. | **PASS** |
| **Password Storage** | Plaintext or weak MD5/SHA1 hashes exposed in data breaches. | High-cost native bcrypt algorithm via `password_hash($pwd, PASSWORD_DEFAULT)` and verified with `password_verify()`. | **PASS** |
| **Cross-Site Request Forgery (CSRF)** | Unauthorized commands transmitted from trusted user. | Cryptographic tokens generated using `bin2hex(random_bytes(32))` stored in `$_SESSION['csrf_token']` and validated using `hash_equals()`. | **PASS** |
| **Cross-Site Scripting (XSS)** | Malicious JavaScript injected via user input rendering. | Output escaping via `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`. Content sanitization before HTML rendering. | **PASS** |
| **Session Fixation & Hijacking** | Stealing session ID before authentication. | Mandatory `session_regenerate_id(true)` upon successful user authentication. | **PASS** |
| **Session Inactivity Exposure** | Unattended terminals left logged in on lab computers. | Automatic 5-minute (300s) session timeout mechanism in `Practical10/timeout.php` and 15-minute timeout on main portal. | **PASS** |
| **Broken Access Control (RBAC)** | Unauthorized users accessing administrative functions. | Role-Based Access Control middleware checking `$_SESSION['role'] === 'admin'` before rendering sensitive views or executing deletions. | **PASS** |
| **Unrestricted File Upload** | Malicious PHP scripts uploaded and executed as images. | 3-tier validation in Practical 12: file extension whitelist (`.jpg, .jpeg, .png, .webp`), MIME content inspection via `finfo_file()`, image geometry validation (`getimagesize()`), file size restriction (&le; 2MB), and random cryptographic renaming. | **PASS** |
| **Traceability & Audit Logging** | Unlogged modifications prevent intrusion detection. | Centralized `audit_logs` table tracking user ID, IP address, exact action, details, and timestamp. | **PASS** |

---

## Part 2: WCAG 2.1 Web Accessibility (a11y) Audit

| Accessibility Guideline | Implementation in StudentHub | Verified |
| :--- | :--- | :---: |
| **Semantic HTML5** | Consistent usage of `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<footer>`, `<figure>`, `<figcaption>`. | **PASS** |
| **Form Labels & Associations** | All `<input>`, `<select>`, `<textarea>` elements paired with explicit `<label for="...">` attributes. | **PASS** |
| **Alternative Text (Alt Text)** | All images, logos, and event posters include descriptive `alt="..."` attributes for screen readers. | **PASS** |
| **Keyboard Navigation** | Accessible tab order across navigation menus, modal popups, buttons, and form controls. | **PASS** |
| **Color Contrast** | High contrast ratio between text (`#283747` / `#FFFFFF`) and background (`#F2F7FB` / `#245B73`) adhering to AA standards. | **PASS** |
| **Dark Mode Preference** | Theme switcher respects user choice and persists across sessions via `localStorage.getItem('studenthub_theme')`. | **PASS** |
| **Screen Reader Landmarks** | ARIA live regions (`role="status"`, `aria-live="polite"`) used for instant AJAX alert announcements. | **PASS** |
