# Practical 15: Viva Voce Examination Questions & Answers

**Subject:** Web Development Frameworks (ITUE203)  
**Institute:** CSPIT - CHARUSAT University  
**Candidate:** Dhruvi Mokani (25CS047)  

---

### Q1. What is the difference between `mysqli` and `PDO` in PHP?
- **mysqli (MySQL Improved):** Specific to MySQL databases only. Supports both procedural and object-oriented syntax. High performance for pure MySQL applications.
- **PDO (PHP Data Objects):** Database-agnostic abstraction layer supporting 12+ database drivers (MySQL, PostgreSQL, SQLite, Oracle, etc.). Only object-oriented. Allows easy switching of underlying databases without rewriting queries. Both support prepared statements.

---

### Q2. Why should we use Prepared Statements instead of direct query strings?
- Prepared statements separate the **SQL query template** from the **user-supplied data**.
- The database pre-compiles the query structure first. When parameters are bound, the database treats them purely as literal values, never as executable SQL code.
- This **100% eliminates SQL Injection (SQLi)** vulnerabilities.

---

### Q3. How does `password_hash()` work and why is MD5/SHA1 obsolete?
- MD5 and SHA1 are fast hashing algorithms designed for file checksums, making them vulnerable to brute-force and rainbow table attacks.
- `password_hash()` utilizes **bcrypt** (or Argon2), an intentionally slow, adaptive cryptographic algorithm.
- It automatically generates a secure, random cryptographic **salt** for every hash and embeds work factor/cost, making rainbow table attacks impossible.
- Password verification is done via `password_verify($password, $hash)`.

---

### Q4. Why is `session_regenerate_id(true)` called after login?
- To protect against **Session Fixation Attacks**.
- In a fixation attack, an adversary tricks a user into logging in with a known pre-set session ID. Once logged in, the adversary uses that same ID to access the victim's account.
- Calling `session_regenerate_id(true)` generates a completely new, unpredictable session ID upon authentication and deletes the old unauthenticated session file.

---

### Q5. How is session timeout implemented?
- When a user performs any action, `$_SESSION['last_activity'] = time()` is updated.
- On each incoming request, the script checks `if (time() - $_SESSION['last_activity'] > $timeout)`.
- If the difference exceeds the limit (e.g. 300 seconds = 5 minutes), the session is destroyed using `session_unset()` and `session_destroy()`, and the user is redirected to `login.php`.

---

### Q6. What is a CSRF token and how does it prevent Cross-Site Request Forgery?
- CSRF occurs when a malicious site tricks a user's browser into sending an unauthorized POST request to an authenticated web app.
- A **CSRF token** is a random, secret value generated on the server (`bin2hex(random_bytes(32))`) and stored in the user's session.
- Forms include this token in a hidden input field. Upon submission, the server compares the submitted token with the session token using `hash_equals()`. Malicious third-party sites cannot read or forge this token due to the Same-Origin Policy.

---

### Q7. How does AJAX Fetch API CRUD work without page reloading?
- The browser uses the JavaScript `fetch(url, options)` API to send HTTP GET/POST requests asynchronously in the background.
- The PHP backend (`api.php`) processes the request and responds with a JSON payload (`header('Content-Type: application/json')`).
- The frontend JavaScript parses the JSON response using `await response.json()` and dynamically updates the DOM table using methods like `element.replaceChildren()` or `appendChild()`, eliminating full-page refreshes.

---

### Q8. What is search debouncing in Practical 13?
- Without debouncing, an API request is fired on every single keystroke, flooding the server.
- Debouncing uses `clearTimeout(searchTimer)` and `setTimeout(() => loadStudents(query), 350)`.
- The API call is only triggered after the user pauses typing for 350 milliseconds, reducing server load by over 80%.

---

### Q9. What security measures are taken during event poster file upload?
1. **Extension Whitelist:** Only `.jpg`, `.jpeg`, `.png`, and `.webp` allowed.
2. **File Size Enforcement:** Checked against `MAX_POSTER_MB` (2MB).
3. **MIME Inspection:** Checked via `finfo_file(FILEINFO_MIME_TYPE, $tmp_name)` to prevent renamed executable scripts (`.php` renamed to `.png`).
4. **Image Geometry Check:** `getimagesize()` verifies genuine image data.
5. **Cryptographic Renaming:** `time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext` prevents file path traversal and overwriting existing files.
6. **Orphan Cleanup:** `unlink()` deletes previous or rejected posters from disk.

---

### Q10. What is Soft Delete vs Permanent Delete?
- **Permanent Delete (`DELETE FROM students WHERE id = ?`):** Physically purges the row from storage. Irreversible; historical relationships and audit integrity are lost.
- **Soft Delete (`UPDATE students SET status = 'archived' WHERE id = ?`):** Hides the record from normal active views while preserving historical registrations, audit logs, and allowing administrators to restore the record at any time.
