<?php
session_start();
require_once __DIR__ . '/config/db.php';

$page_title = "Student Registration | Student Hub";
$active_page = "register";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. CSRF Token Check
    $token = $_POST["csrf_token"] ?? "";
    if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = "Security token expired. Please try again.";
    }

    // 2. Collect inputs
    $name             = trim($_POST["name"] ?? "");
    $username         = trim($_POST["username"] ?? "");
    $email            = trim($_POST["email"] ?? "");
    $mobile           = trim($_POST["mobile"] ?? "");
    $password         = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirmPassword"] ?? "";
    $course           = trim($_POST["course"] ?? "");
    $year             = trim($_POST["year"] ?? "");
    $gender           = trim($_POST["gender"] ?? "");
    $terms            = isset($_POST["terms"]);

    // If username is empty, generate from email local part
    if (empty($username) && !empty($email)) {
        $username = explode('@', $email)[0];
        $username = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $username));
        if (strlen($username) < 3) {
            $username = 'student_' . bin2hex(random_bytes(2));
        }
    }

    // 3. Server-side Validations (Practical 5 & 9)
    if (empty($name) || !preg_match("/^[A-Za-z ]{3,50}$/", $name)) {
        $errors[] = "Full name must be 3-50 characters containing letters only.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($mobile) || !preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
        $errors[] = "Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.";
    }

    if (empty($password) || strlen($password) < 8 || !preg_match("/[a-z]/", $password) || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[^A-Za-z0-9]/", $password)) {
        $errors[] = "Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($course)) {
        $errors[] = "Please select your academic course.";
    }

    if (empty($year)) {
        $errors[] = "Please select your study year.";
    }

    if (empty($gender)) {
        $errors[] = "Please select your gender.";
    }

    if (!$terms) {
        $errors[] = "You must agree to the Terms and Conditions.";
    }

    // 4. Duplicate Check & Username Guarantee (Practical 9)
    if (empty($errors)) {
        // Check if an account with this email already exists in users
        $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $errors[] = "An account with the email '$email' already exists. Please log in instead.";
        }
        mysqli_stmt_close($check_stmt);

        // Ensure username is completely unique
        if (empty($errors)) {
            $base_username = $username;
            $counter = 1;
            while (true) {
                $u_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
                mysqli_stmt_bind_param($u_stmt, "s", $username);
                mysqli_stmt_execute($u_stmt);
                mysqli_stmt_store_result($u_stmt);
                $exists = mysqli_stmt_num_rows($u_stmt) > 0;
                mysqli_stmt_close($u_stmt);
                if (!$exists) {
                    break;
                }
                $username = $base_username . rand(10, 999);
                $counter++;
                if ($counter > 25) {
                    $username = $base_username . time();
                    break;
                }
            }
        }
    }

    // 5. Insert into Database if valid
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = "student";
        $status = "active";

        $ins_user = mysqli_prepare($conn, "INSERT INTO users (username, name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins_user, "ssssss", $username, $name, $email, $password_hash, $role, $status);

        if (mysqli_stmt_execute($ins_user)) {
            $user_id = mysqli_insert_id($conn);
            mysqli_stmt_close($ins_user);

            // Sync into students table (with ON DUPLICATE KEY UPDATE so pre-seeded students link correctly)
            $ins_student = mysqli_prepare($conn, "INSERT INTO students (user_id, name, email, mobile, course, year, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active') ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), name = VALUES(name), mobile = VALUES(mobile), course = VALUES(course), year = VALUES(year), gender = VALUES(gender), status = 'active'");
            if ($ins_student) {
                mysqli_stmt_bind_param($ins_student, "issssss", $user_id, $name, $email, $mobile, $course, $year, $gender);
                mysqli_stmt_execute($ins_student);
                mysqli_stmt_close($ins_student);
            }

            // Also backup to students.json (Practical 7/9 sync)
            $json_file = __DIR__ . '/students.json';
            $curr_json = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];
            if (!is_array($curr_json)) $curr_json = [];
            $curr_json[] = [
                'id' => $user_id,
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'course' => $course,
                'year' => $year,
                'gender' => $gender
            ];
            @file_put_contents($json_file, json_encode($curr_json, JSON_PRETTY_PRINT));

            // Log Audit event
            log_audit_trail($conn, $user_id, "User Registered", "Account created via registration.php for $name ($email, username: $username)");

            // Reset token & Redirect to login
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $msg = "Registration successful for $name! Your username is '$username'. Please log in with your credentials.";
            header("Location: login.php?message=" . urlencode($msg));
            exit;
        } else {
            $errors[] = "Registration failed due to a database error: " . mysqli_error($conn);
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:680px;margin:30px auto;padding:0 15px;flex:1;">
    <section style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.12);font-family:'Segoe UI',sans-serif;">
        <h2 style="color:#245B73;text-align:center;margin-top:0;">Student Account Registration</h2>
        <p style="text-align:center;color:#666;font-size:14px;margin-bottom:20px;">
            Create your unified student portal profile with real-time validation and secure password hashing.
        </p>

        <?php if (!empty($errors)): ?>
            <div style="background:#FDE8E8;color:#9B1C1C;padding:12px 18px;border-radius:6px;font-size:13px;margin-bottom:20px;">
                <strong>Please correct the following:</strong>
                <ul style="margin:6px 0 0;padding-left:20px;">
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="registrationForm" method="POST" action="registration.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group" style="margin-bottom:15px;">
                <label for="name" style="display:block;font-weight:600;color:#245B73;margin-bottom:5px;">Full Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="e.g. Dhruvi Mokani" required style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                <span class="error" id="nameError" style="color:#C05A6F;font-size:12px;display:block;margin-top:4px;"></span>
            </div>

            <div style="display:flex;gap:15px;flex-wrap:wrap;">
                <div class="form-group" style="flex:1;min-width:240px;margin-bottom:15px;">
                    <label for="email" style="display:block;font-weight:600;color:#245B73;margin-bottom:5px;">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="student@charusat.edu.in" required style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                    <span class="error" id="emailError" style="color:#C05A6F;font-size:12px;display:block;margin-top:4px;"></span>
                </div>

                <div class="form-group" style="flex:1;min-width:240px;margin-bottom:15px;">
                    <label for="mobile" style="display:block;font-weight:600;color:#245B73;margin-bottom:5px;">Mobile Number *</label>
                    <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>" placeholder="10-digit number" maxlength="10" required style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                    <span class="error" id="mobileError" style="color:#C05A6F;font-size:12px;display:block;margin-top:4px;"></span>
                </div>
            </div>

            <div style="display:flex;gap:15px;flex-wrap:wrap;">
                <div class="form-group" style="flex:1;min-width:240px;margin-bottom:15px;">
                    <label for="password" style="display:block;font-weight:600;color:#245B73;margin-bottom:5px;">Password *</label>
                    <input type="password" id="password" name="password" placeholder="Min. 8 chars with mix" required style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                    <!-- Password Strength Meter (Practical 5) -->
                    <div style="width:100%;height:6px;background:#E1EDF2;border-radius:3px;margin-top:6px;overflow:hidden;">
                        <div id="strengthBar" style="height:100%;width:0%;transition:width 0.3s, background 0.3s;background:#76B6C9;"></div>
                    </div>
                    <small id="strengthText" style="display:block;font-size:11px;color:#666;margin-top:3px;">Password strength: Not entered</small>
                    <span class="error" id="passwordError" style="color:#C05A6F;font-size:12px;display:block;margin-top:4px;"></span>
                </div>

                <div class="form-group" style="flex:1;min-width:240px;margin-bottom:15px;">
                    <label for="confirmPassword" style="display:block;font-weight:600;color:#245B73;margin-bottom:5px;">Confirm Password *</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                    <span class="error" id="confirmPasswordError" style="color:#C05A6F;font-size:12px;display:block;margin-top:4px;"></span>
                </div>
            </div>

            <div style="display:flex;gap:15px;flex-wrap:wrap;">
                <div class="form-group" style="flex:1;min-width:240px;margin-bottom:15px;">
                    <label for="course" style="display:block;font-weight:600;color:#245B73;margin-bottom:5px;">Academic Course *</label>
                    <select id="course" name="course" required style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                        <option value="">-- Select Course --</option>
                        <option value="B.Tech CSE" <?php echo (($_POST['course'] ?? '') === 'B.Tech CSE' || empty($_POST['course'])) ? 'selected' : ''; ?>>B.Tech Computer Science &amp; Engg</option>
                        <option value="B.Tech IT" <?php echo (($_POST['course'] ?? '') === 'B.Tech IT') ? 'selected' : ''; ?>>B.Tech Information Technology</option>
                        <option value="B.Tech CE" <?php echo (($_POST['course'] ?? '') === 'B.Tech CE') ? 'selected' : ''; ?>>B.Tech Computer Engineering</option>
                        <option value="BCA" <?php echo (($_POST['course'] ?? '') === 'BCA') ? 'selected' : ''; ?>>BCA</option>
                        <option value="MCA" <?php echo (($_POST['course'] ?? '') === 'MCA') ? 'selected' : ''; ?>>MCA</option>
                    </select>
                    <span class="error" id="courseError" style="color:#C05A6F;font-size:12px;display:block;margin-top:4px;"></span>
                </div>

                <div class="form-group" style="flex:1;min-width:240px;margin-bottom:15px;">
                    <label for="year" style="display:block;font-weight:600;color:#245B73;margin-bottom:5px;">Study Year *</label>
                    <select id="year" name="year" required style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                        <option value="">-- Select Year --</option>
                        <option value="First Year" <?php echo (($_POST['year'] ?? '') === 'First Year') ? 'selected' : ''; ?>>First Year</option>
                        <option value="Second Year" <?php echo (($_POST['year'] ?? '') === 'Second Year') ? 'selected' : ''; ?>>Second Year</option>
                        <option value="Third Year" <?php echo (($_POST['year'] ?? '') === 'Third Year' || empty($_POST['year'])) ? 'selected' : ''; ?>>Third Year</option>
                        <option value="Fourth Year" <?php echo (($_POST['year'] ?? '') === 'Fourth Year') ? 'selected' : ''; ?>>Fourth Year</option>
                    </select>
                    <span class="error" id="yearError" style="color:#C05A6F;font-size:12px;display:block;margin-top:4px;"></span>
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;color:#245B73;margin-bottom:5px;">Gender *</label>
                <div style="display:flex;gap:20px;">
                    <label style="font-weight:normal;cursor:pointer;"><input type="radio" name="gender" value="Female" <?php echo (($_POST['gender'] ?? 'Female') === 'Female') ? 'checked' : ''; ?>> Female</label>
                    <label style="font-weight:normal;cursor:pointer;"><input type="radio" name="gender" value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'checked' : ''; ?>> Male</label>
                    <label style="font-weight:normal;cursor:pointer;"><input type="radio" name="gender" value="Other" <?php echo (($_POST['gender'] ?? '') === 'Other') ? 'checked' : ''; ?>> Other</label>
                </div>
                <span class="error" id="genderError" style="color:#C05A6F;font-size:12px;display:block;margin-top:4px;"></span>
            </div>

            <div style="margin-bottom:20px;background:#F4F9FB;padding:12px;border-radius:6px;border:1px solid #D5E5ED;">
                <label style="font-weight:normal;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" id="terms" name="terms" value="1" required checked>
                    I agree to the Charotar University &amp; Student Hub Academic Terms of Service.
                </label>
                <span class="error" id="termsError" style="color:#C05A6F;font-size:12px;display:block;margin-top:4px;"></span>
            </div>

            <button type="submit" id="submitBtn" style="width:100%;padding:12px;background:linear-gradient(135deg,#245B73,#4389A8);color:white;border:none;border-radius:6px;font-size:16px;font-weight:bold;cursor:pointer;box-shadow:0 2px 6px rgba(36,91,115,0.2);">
                Complete Registration
            </button>
            <div id="successMessage" class="success" style="color:#0E9F6E;font-size:14px;font-weight:600;margin-top:12px;text-align:center;"></div>
        </form>

        <div style="text-align:center;margin-top:18px;font-size:13px;">
            Already have an account? <a href="login.php" style="color:#245B73;font-weight:bold;">Sign In Here &rarr;</a>
        </div>
    </section>
</main>

<!-- Frontend Validation Script (Practical 5) -->
<script src="js/prac5.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
