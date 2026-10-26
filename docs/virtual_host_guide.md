# Practical 15: Local Apache Virtual Host Deployment Guide

This guide details the procedure for deploying the **StudentHub** portal locally using an Apache Virtual Host (`http://studenthub.local`), satisfying the **Practical 15 Intermediate Extension**.

---

## 1. Prerequisites
- **XAMPP** installed at `C:\xampp`
- Apache and MySQL running via XAMPP Control Panel
- Administrator privileges on Windows

---

## 2. Step-by-Step Configuration

### Step A: Configure Windows `hosts` File
1. Open Windows Start Menu, search for **Notepad**, right-click and select **Run as administrator**.
2. Open the file:
   ```
   C:\Windows\System32\drivers\etc\hosts
   ```
3. Add the following entry at the bottom of the file:
   ```text
   127.0.0.1       studenthub.local
   127.0.0.1       www.studenthub.local
   ```
4. Save the file (`Ctrl + S`) and close Notepad.

---

### Step B: Enable Virtual Hosts in Apache Configuration
1. Open `C:\xampp\apache\conf\httpd.conf` in your code editor or Notepad.
2. Locate the line (search for `httpd-vhosts.conf`):
   ```apache
   #Include conf/extra/httpd-vhosts.conf
   ```
3. Uncomment it by removing the leading `#`:
   ```apache
   Include conf/extra/httpd-vhosts.conf
   ```
4. Save the file.

---

### Step C: Define the Virtual Host Block
1. Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf`.
2. Append the following configuration block at the end of the file:

```apache
# Default Localhost Configuration (preserves XAMPP dashboard)
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot "C:/xampp/htdocs"
    ServerName localhost
</VirtualHost>

# StudentHub Virtual Host Configuration
<VirtualHost *:80>
    ServerAdmin webmaster@studenthub.local
    DocumentRoot "C:/Users/Dhruvi/OneDrive/Documents/StudentHub"
    ServerName studenthub.local
    ServerAlias www.studenthub.local

    <Directory "C:/Users/Dhruvi/OneDrive/Documents/StudentHub">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/studenthub-error.log"
    CustomLog "logs/studenthub-access.log" common
</VirtualHost>
```

3. Save the file.

---

### Step D: Restart Apache & Test
1. Open the **XAMPP Control Panel**.
2. Stop the **Apache** service and Start it again.
3. Open any web browser (Google Chrome, Edge, Firefox) and navigate to:
   ```
   http://studenthub.local/
   ```
4. You will see the complete **StudentHub Portal** running directly on your custom domain!

---

## 3. Deployment Verification Checklist

| Check | Expected Result | Verified |
| :--- | :--- | :---: |
| Domain Resolution | `ping studenthub.local` returns `127.0.0.1` | Yes |
| Virtual Host Response | `http://studenthub.local/` loads `index.php` | Yes |
| Database Connectivity | MySQL tables loaded through PDO & MySQLi | Yes |
| Session & Cookie Scope | Cookie set with domain `studenthub.local` | Yes |
| File Uploads | Uploaded posters saved in `Practical12/uploads/` | Yes |
| API Endpoints | `http://studenthub.local/Practical13/api.php?action=list` returns JSON | Yes |
