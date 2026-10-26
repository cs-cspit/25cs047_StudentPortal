<?php
session_start();
require_once __DIR__ . '/config/db.php';

$page_title = "Student Feedback System | Student Hub";
$active_page = "feedback";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$msg = "";
$err = "";

// CSV file path (Practical 7 File I/O requirement)
$csv_file = __DIR__ . '/data.csv';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. CSRF Verification
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $err = "Security token validation failed. Please refresh and try again.";
    } else {
        $name     = trim($_POST["name"] ?? ($_SESSION["name"] ?? ""));
        $email    = trim($_POST["email"] ?? ($_SESSION["email"] ?? ""));
        $category = trim($_POST["category"] ?? "Academic");
        $rating   = (int)($_POST["rating"] ?? 5);
        $comments = trim($_POST["comments"] ?? "");

        if (empty($name) || empty($email) || empty($comments)) {
            $err = "Name, email and feedback comments are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = "Please enter a valid email address.";
        } else {
            // 2. MySQL Database Storage
            $stmt = mysqli_prepare($conn, "INSERT INTO feedback (name, email, category, rating, comments) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssis", $name, $email, $category, $rating, $comments);
                if (mysqli_stmt_execute($stmt)) {
                    $msg = "Thank you! Your feedback has been recorded in database and backed up to CSV.";
                    
                    // 3. File I/O: Dual persistence to data.csv (Practical 7 requirement)
                    $fp = @fopen($csv_file, 'a');
                    if ($fp) {
                        fputcsv($fp, [date('Y-m-d H:i:s'), $name, $email, $category, $rating, $comments]);
                        fclose($fp);
                    }

                    if (isset($_SESSION['user_id'])) {
                        log_audit_trail($conn, (int)$_SESSION['user_id'], "Submit Feedback", "Feedback for $category submitted");
                    }
                } else {
                    $err = "Database error: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fetch recent feedback for live table preview (Practical 7 requirement)
$recent_feedbacks = [];
$fb_q = mysqli_query($conn, "SELECT name, category, rating, comments, created_at FROM feedback ORDER BY id DESC LIMIT 5");
if ($fb_q) {
    while ($fbr = mysqli_fetch_assoc($fb_q)) {
        $recent_feedbacks[] = $fbr;
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:850px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        <h2 style="color:#245B73;text-align:center;margin-top:0;">Student Feedback &amp; Evaluation System</h2>
        <p style="text-align:center;color:#666;font-size:14px;margin-bottom:20px;">
            Your feedback helps us continuously improve academic delivery, laboratory infrastructure, and student support services.
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

        <form method="POST" action="feedback.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Full Name *</label>
                    <input type="text" name="name" required value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" placeholder="Enter your full name" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Email Address *</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" placeholder="Enter your email" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Feedback Category *</label>
                    <select name="category" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                        <option value="Academic Delivery">Academic Curriculum &amp; Delivery</option>
                        <option value="Laboratory">Laboratory &amp; Practical Infrastructure</option>
                        <option value="Campus Facilities">Campus Facilities &amp; Library</option>
                        <option value="Examination">Examination &amp; Assessment</option>
                        <option value="General Support">General Student Support</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Experience Rating *</label>
                    <select name="rating" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                        <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; (5 - Excellent)</option>
                        <option value="4">&#9733;&#9733;&#9733;&#9733;&#9734; (4 - Very Good)</option>
                        <option value="3">&#9733;&#9733;&#9733;&#9734;&#9734; (3 - Good)</option>
                        <option value="2">&#9733;&#9733;&#9734;&#9734;&#9734; (2 - Fair)</option>
                        <option value="1">&#9733;&#9734;&#9734;&#9734;&#9734; (1 - Needs Improvement)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Comments &amp; Constructive Suggestions *</label>
                <textarea name="comments" rows="4" required placeholder="Write your detailed feedback here..." style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;"></textarea>
            </div>

            <button type="submit" style="width:100%;padding:12px;background:linear-gradient(135deg,#245B73,#4389A8);color:white;border:none;border-radius:6px;font-weight:bold;font-size:15px;cursor:pointer;">
                Submit Academic Feedback
            </button>
        </form>

        <!-- Live Preview of Recent Submissions -->
        <div style="margin-top:35px;border-top:2px solid #E1EDF2;padding-top:20px;">
            <h3 style="color:#245B73;margin-top:0;font-size:16px;">Recent Academic Feedback &amp; Suggestions</h3>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;text-align:left;">
                    <thead>
                        <tr style="background:#245B73;color:white;">
                            <th style="padding:8px 12px;">Date</th>
                            <th style="padding:8px 12px;">Student</th>
                            <th style="padding:8px 12px;">Category</th>
                            <th style="padding:8px 12px;">Rating</th>
                            <th style="padding:8px 12px;">Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_feedbacks)): ?>
                            <?php foreach ($recent_feedbacks as $rf): ?>
                                <tr style="border-bottom:1px solid #E1EDF2;">
                                    <td style="padding:8px 12px;color:#555;white-space:nowrap;"><?php echo htmlspecialchars(substr($rf['created_at'], 0, 10)); ?></td>
                                    <td style="padding:8px 12px;font-weight:600;"><?php echo htmlspecialchars($rf['name']); ?></td>
                                    <td style="padding:8px 12px;"><span style="background:#E1F0F5;color:#1B546B;padding:2px 6px;border-radius:4px;font-size:11px;"><?php echo htmlspecialchars($rf['category']); ?></span></td>
                                    <td style="padding:8px 12px;color:#F39C12;font-weight:bold;"><?php echo str_repeat('&#9733;', (int)$rf['rating']); ?></td>
                                    <td style="padding:8px 12px;color:#444;"><?php echo htmlspecialchars($rf['comments']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;padding:15px;color:#888;">No feedback records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
