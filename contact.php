<?php
session_start();
require_once __DIR__ . '/config/db.php';

$page_title = "Contact Us | Student Hub";
$active_page = "contact";

$msg = "";
$err = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name    = trim($_POST["name"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $mobile  = trim($_POST["mobile"] ?? "");
    $pincode = trim($_POST["pincode"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $message = trim($_POST["message"] ?? "");

    // Validation (Practical 7)
    if (empty($name) || empty($email) || empty($message)) {
        $err = "Name, email and message are mandatory fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email address format.";
    } elseif (!empty($mobile) && !preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
        $err = "Mobile number must be a 10-digit number.";
    } else {
        // Save to Database
        $stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (name, email, mobile, pincode, address, message) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $mobile, $pincode, $address, $message);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Also save to CSV file (File I/O backup)
        $csv_file = __DIR__ . '/data.csv';
        if (($handle = @fopen($csv_file, 'a')) !== false) {
            fputcsv($handle, [date('Y-m-d H:i:s'), $name, $email, $message, $mobile, $pincode, $address, 'Inquiry']);
            fclose($handle);
        }

        $msg = "Thank you, " . htmlspecialchars($name) . "! Your message has been transmitted to the CSPIT administrative desk.";
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1100px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        
        <h2 style="color:#245B73;margin-top:0 0 5px;">Contact Student Hub &amp; CSPIT</h2>
        <p style="color:#666;font-size:14px;border-bottom:1px solid #E1EDF2;padding-bottom:12px;margin-bottom:25px;">
            Have inquiries regarding admissions, examinations, laboratory files, or technical portal support? Reach out below.
        </p>

        <?php if ($msg): ?>
            <div style="background:#DEF7EC;color:#03543F;padding:12px;border-radius:6px;font-weight:bold;margin-bottom:20px;">
                &#10004; <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php if ($err): ?>
            <div style="background:#FDE8E8;color:#9B1C1C;padding:12px;border-radius:6px;font-weight:bold;margin-bottom:20px;">
                &#9888; <?php echo htmlspecialchars($err); ?>
            </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:30px;">
            
            <!-- Contact Form -->
            <form method="POST" action="contact.php">
                <div style="margin-bottom:15px;">
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Full Name *</label>
                    <input type="text" name="name" required placeholder="Enter full name" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                    <div>
                        <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Email Address *</label>
                        <input type="email" name="email" required placeholder="Enter email" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Mobile Number</label>
                        <input type="text" name="mobile" maxlength="10" placeholder="10-digit number" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                    <div>
                        <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Pincode</label>
                        <input type="text" name="pincode" maxlength="6" placeholder="e.g. 387002" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Address / City</label>
                        <input type="text" name="address" placeholder="City or Hostel" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-weight:bold;color:#245B73;margin-bottom:5px;font-size:13px;">Your Message or Inquiry *</label>
                    <textarea name="message" rows="4" required placeholder="Write your message here..." style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;font-family:inherit;"></textarea>
                </div>

                <button type="submit" style="width:100%;padding:12px;background:linear-gradient(135deg,#245B73,#4389A8);color:white;border:none;border-radius:6px;font-size:15px;font-weight:bold;cursor:pointer;">
                    Send Message (Validated with CSV &amp; MySQL Storage)
                </button>
            </form>

            <!-- Campus Information & Map Placeholder -->
            <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:25px;">
                <h3 style="color:#245B73;margin-top:0;font-size:18px;">Campus Address</h3>
                <p style="color:#555;font-size:14px;line-height:1.6;">
                    <strong>Chandubhai S Patel Institute of Technology (CSPIT)</strong><br>
                    Charotar University of Science and Technology (CHARUSAT)<br>
                    CHARUSAT Campus, Changa, Dist. Anand, Gujarat, India - 388421
                </p>

                <h4 style="color:#245B73;margin-top:20px;font-size:15px;">Contact Channels</h4>
                <p style="color:#555;font-size:14px;line-height:1.8;">
                    &#128222; <strong>Phone:</strong> +91-2697-265112 / 265113<br>
                    &#128231; <strong>Email:</strong> support@studenthub.com<br>
                    &#128187; <strong>Developer:</strong> Dhruvi Mokani (25CS047)<br>
                    &#127979; <strong>Working Hours:</strong> Monday - Saturday (9:00 AM - 4:30 PM)
                </p>

                <div style="margin-top:20px;background:#EBF5F9;padding:15px;border-radius:6px;border:1px solid #76B6C9;font-size:13px;color:#245B73;">
                    <strong>Student Helpdesk:</strong> For examination grievances, hall ticket inquiries, or attendance corrections, please visit the academic section in person or submit this contact form.
                </div>
            </div>

        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
