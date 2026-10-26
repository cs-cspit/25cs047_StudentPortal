<?php
session_start();
require_once __DIR__ . '/config/db.php';

$page_title = "Login | Student Hub";
$active_page = "login";

// If already logged in, redirect to dashboard
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_GET["error"] ?? "";
$message = $_GET["message"] ?? "";

// Handle Login Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identity = trim($_POST["identity"] ?? "");
    $password = $_POST["password"] ?? "";
    $remember = isset($_POST["remember"]);

    if (empty($identity) || empty($password)) {
        $error = "Please enter your email or username and password.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, username, name, email, password, role, status FROM users WHERE email = ? OR username = ?");
        mysqli_stmt_bind_param($stmt, "ss", $identity, $identity);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($res)) {
            if ($user["status"] === "inactive") {
                $error = "This account is inactive. Please contact administration.";
            } elseif (password_verify($password, $user["password"])) {
                session_regenerate_id(true);

                $_SESSION["user_id"]       = $user["id"];
                $_SESSION["username"]      = $user["username"];
                $_SESSION["name"]          = $user["name"];
                $_SESSION["email"]         = $user["email"];
                $_SESSION["role"]          = $user["role"];
                $_SESSION["last_activity"] = time();

                // Remember-me token (Practical 10 Advanced Extension)
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $token_hash = hash('sha256', $token);
                    $rem_stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = ? WHERE id = ?");
                    if ($rem_stmt) {
                        mysqli_stmt_bind_param($rem_stmt, "si", $token_hash, $user["id"]);
                        mysqli_stmt_execute($rem_stmt);
                        mysqli_stmt_close($rem_stmt);
                    }
                    setcookie("studenthub_remember", $token, time() + (86400 * 30), "/", "", false, true);
                }

                // Audit log
                log_audit_trail($conn, $user["id"], "User Login", "Logged into StudentHub Portal via login.php");

                // Update last_login
                @mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE id = " . (int)$user["id"]);

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid password. Please check your credentials.";
            }
        } else {
            $error = "No user found with that email or username.";
        }
        mysqli_stmt_close($stmt);
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:500px;margin:35px auto;padding:0 15px;flex:1;">
    <section style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.12);font-family:'Segoe UI',sans-serif;">
        <h2 style="color:#245B73;text-align:center;margin-top:0;">Student &amp; Staff Login</h2>
        <p style="text-align:center;color:#666;font-size:14px;margin-bottom:20px;">
            Access your academic dashboard, results, attendance and events.
        </p>

        <?php if ($error): ?>
            <div style="background:#FDE8E8;color:#9B1C1C;padding:12px;border-radius:6px;font-size:13px;margin-bottom:15px;font-weight:bold;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div style="background:#DEF7EC;color:#03543F;padding:12px;border-radius:6px;font-size:13px;margin-bottom:15px;font-weight:bold;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div style="margin-bottom:15px;">
                <label for="identity" style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:14px;">
                    Email Address or Username *
                </label>
                <input type="text" id="identity" name="identity" placeholder="e.g. admin@studenthub.com or dhruvi" required autofocus style="width:100%;padding:10px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:15px;">
                <label for="password" style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:14px;">
                    Password *
                </label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required style="width:100%;padding:10px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:20px;display:flex;align-items:center;gap:6px;font-size:13px;color:#333;">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember" style="margin:0;font-weight:normal;cursor:pointer;">Remember me on this browser (30 days)</label>
            </div>

            <button type="submit" style="width:100%;padding:12px;background:linear-gradient(135deg,#245B73,#4389A8);color:white;border:none;border-radius:6px;font-weight:bold;font-size:16px;cursor:pointer;">
                Sign In to Student Hub
            </button>
        </form>

        <div style="background:#F4F9FB;border:1px solid #D5E5ED;border-radius:6px;padding:12px;margin-top:20px;font-size:12px;line-height:1.6;">
            <strong>Quick Demo Credentials (Click to fill):</strong><br>
            &bull; <a href="javascript:void(0)" onclick="fillCreds('admin@studenthub.com', 'Admin@123')" style="color:#245B73;font-weight:bold;">Admin Account</a>: <code>admin@studenthub.com</code> / <code>Admin@123</code><br>
            &bull; <a href="javascript:void(0)" onclick="fillCreds('dhruvimokani08@gmail.com', 'Student@123')" style="color:#245B73;font-weight:bold;">Student (Dhruvi)</a>: <code>dhruvimokani08@gmail.com</code> / <code>Student@123</code><br>
            &bull; <a href="javascript:void(0)" onclick="fillCreds('student01@gmail.com', '123456')" style="color:#245B73;font-weight:bold;">Student (Kalp)</a>: <code>student01@gmail.com</code> / <code>123456</code>
        </div>

        <div style="text-align:center;margin-top:18px;font-size:13px;">
            Don't have an account? <a href="registration.php" style="color:#245B73;font-weight:bold;">Register Now &rarr;</a>
        </div>
    </section>
</main>

<script>
function fillCreds(ident, pass) {
    document.getElementById('identity').value = ident;
    document.getElementById('password').value = pass;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
