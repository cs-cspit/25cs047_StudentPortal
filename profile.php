<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login();

$page_title = "My Profile | Student Hub";
$active_page = "profile";

$user_id = (int)$_SESSION["user_id"];
$email   = $_SESSION["email"] ?? "";
$msg = "";
$err = "";

// Fetch student & user details
$student = null;
$s_res = mysqli_query($conn, "SELECT s.*, u.username, u.role, u.created_at as reg_date 
                              FROM users u 
                              LEFT JOIN students s ON s.user_id = u.id OR s.email = u.email 
                              WHERE u.id = $user_id LIMIT 1");
if ($s_res && mysqli_num_rows($s_res) > 0) {
    $student = mysqli_fetch_assoc($s_res);
}

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_profile"])) {
    $mobile  = trim($_POST["mobile"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $pincode = trim($_POST["pincode"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if (!empty($mobile) && !preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
        $err = "Mobile number must be a valid 10-digit number.";
    } elseif (!empty($pincode) && !preg_match("/^\d{6}$/", $pincode)) {
        $err = "Pincode must be 6 digits.";
    } else {
        $up = mysqli_prepare($conn, "UPDATE students SET mobile = ?, address = ?, pincode = ?, message = ? WHERE email = ? OR user_id = ?");
        if ($up) {
            mysqli_stmt_bind_param($up, "sssssi", $mobile, $address, $pincode, $message, $email, $user_id);
            mysqli_stmt_execute($up);
            mysqli_stmt_close($up);
            $msg = "Profile details updated successfully!";
            // Refresh student data
            $s_res = mysqli_query($conn, "SELECT s.*, u.username, u.role, u.created_at as reg_date FROM users u LEFT JOIN students s ON s.user_id = u.id OR s.email = u.email WHERE u.id = $user_id LIMIT 1");
            $student = mysqli_fetch_assoc($s_res);
        }
    }
}

// Handle Password Change
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["change_password"])) {
    $current_pass = $_POST["current_password"] ?? "";
    $new_pass     = $_POST["new_password"] ?? "";
    $confirm_pass = $_POST["confirm_password"] ?? "";

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $err = "All password fields are required.";
    } elseif ($new_pass !== $confirm_pass) {
        $err = "New passwords do not match.";
    } elseif (strlen($new_pass) < 6) {
        $err = "New password must be at least 6 characters long.";
    } else {
        $pwd_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id = $user_id"));
        if ($pwd_check && password_verify($current_pass, $pwd_check["password"])) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $up_pwd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($up_pwd, "si", $new_hash, $user_id);
            mysqli_stmt_execute($up_pwd);
            mysqli_stmt_close($up_pwd);
            log_audit_trail($conn, $user_id, "Password Changed", "User updated password in profile.php");
            $msg = "Password updated successfully!";
        } else {
            $err = "Incorrect current password.";
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:900px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        <h2 style="color:#245B73;margin-top:0;">Student Academic Profile</h2>
        <p style="color:#666;font-size:14px;border-bottom:1px solid #E1EDF2;padding-bottom:12px;">
            Personal information, course affiliation, and account security.
        </p>

        <?php if ($msg): ?>
            <div style="background:#DEF7EC;color:#03543F;padding:12px;border-radius:6px;font-weight:bold;margin-bottom:20px;">
                &#10004; <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <?php if ($err): ?>
            <div style="background:#FDE8E8;color:#9B1C1C;padding:12px;border-radius:6px;font-weight:bold;margin-bottom:20px;">
                &#9888; <?php echo htmlspecialchars($err); ?>
            </div>
        <?php endif; ?>

        <!-- Read-only Academic Details -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:15px;background:#F4F9FB;padding:20px;border-radius:8px;border:1px solid #D5E5ED;margin-bottom:25px;">
            <div>
                <small style="color:#666;text-transform:uppercase;font-size:11px;font-weight:bold;">Student Name</small>
                <div style="font-weight:bold;font-size:16px;color:#245B73;margin-top:3px;">
                    <?php echo htmlspecialchars($student['name'] ?? $_SESSION['name'] ?? 'Dhruvi Mokani'); ?>
                </div>
            </div>

            <div>
                <small style="color:#666;text-transform:uppercase;font-size:11px;font-weight:bold;">Email Address</small>
                <div style="font-weight:bold;font-size:15px;color:#333;margin-top:3px;">
                    <?php echo htmlspecialchars($email); ?>
                </div>
            </div>

            <div>
                <small style="color:#666;text-transform:uppercase;font-size:11px;font-weight:bold;">Program &amp; Course</small>
                <div style="font-weight:bold;font-size:15px;color:#257E66;margin-top:3px;">
                    <?php echo htmlspecialchars($student['course'] ?? 'B.Tech CSE'); ?>
                </div>
            </div>

            <div>
                <small style="color:#666;text-transform:uppercase;font-size:11px;font-weight:bold;">Current Study Year</small>
                <div style="font-weight:bold;font-size:15px;color:#333;margin-top:3px;">
                    <?php echo htmlspecialchars($student['year'] ?? 'Third Year'); ?>
                </div>
            </div>

            <div>
                <small style="color:#666;text-transform:uppercase;font-size:11px;font-weight:bold;">Gender</small>
                <div style="font-weight:bold;font-size:15px;color:#333;margin-top:3px;">
                    <?php echo htmlspecialchars($student['gender'] ?? 'Female'); ?>
                </div>
            </div>

            <div>
                <small style="color:#666;text-transform:uppercase;font-size:11px;font-weight:bold;">Account Role</small>
                <div style="font-weight:bold;font-size:15px;color:#333;margin-top:3px;">
                    <?php echo ucfirst($student['role'] ?? 'Student'); ?>
                </div>
            </div>
        </div>

        <!-- Editable Contact Information -->
        <h3 style="color:#245B73;font-size:18px;">Update Contact &amp; Residential Details</h3>
        <form method="POST" action="profile.php" style="margin-bottom:30px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Mobile Number</label>
                    <input type="text" name="mobile" maxlength="10" value="<?php echo htmlspecialchars($student['mobile'] ?? ''); ?>" placeholder="10-digit mobile" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Pincode</label>
                    <input type="text" name="pincode" maxlength="6" value="<?php echo htmlspecialchars($student['pincode'] ?? ''); ?>" placeholder="6-digit pincode" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Residential Address</label>
                <textarea name="address" rows="2" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;font-family:inherit;"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Bio / Remarks</label>
                <input type="text" name="message" value="<?php echo htmlspecialchars($student['message'] ?? ''); ?>" placeholder="Brief bio or academic interests" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <button type="submit" name="update_profile" style="background:#245B73;color:white;padding:10px 20px;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">
                Save Profile Changes
            </button>
        </form>

        <!-- Change Password Section -->
        <h3 style="color:#245B73;font-size:18px;border-top:1px solid #E1EDF2;padding-top:20px;">Change Account Password</h3>
        <form method="POST" action="profile.php">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:15px;margin-bottom:15px;">
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Current Password</label>
                    <input type="password" name="current_password" required placeholder="Enter current password" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">New Password (Min 6 chars)</label>
                    <input type="password" name="new_password" minlength="6" required placeholder="New secure password" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Confirm New Password</label>
                    <input type="password" name="confirm_password" minlength="6" required placeholder="Re-enter new password" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
            </div>

            <button type="submit" name="change_password" style="background:#257E66;color:white;padding:10px 20px;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">
                Update Password
            </button>
        </form>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
