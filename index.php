<?php
session_start();
require_once __DIR__ . '/config/db.php';

$page_title = "Student Hub | Home & Academic Portal";
$active_page = "home";

// Fetch active announcements for ticker & alerts
$announcements = [];
$ann_res = @mysqli_query($conn, "SELECT title, content, category, publish_date FROM announcements WHERE is_active = 1 ORDER BY publish_date DESC LIMIT 4");
if ($ann_res) {
    while ($a = mysqli_fetch_assoc($ann_res)) {
        $announcements[] = $a;
    }
}

// Fetch upcoming 3 events for home preview
$upcoming_events = [];
$ev_res = @mysqli_query($conn, "SELECT id, title, event_date, location, category, poster FROM events WHERE status = 'Open' ORDER BY event_date ASC LIMIT 3");
if ($ev_res) {
    while ($ev = mysqli_fetch_assoc($ev_res)) {
        $upcoming_events[] = $ev;
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1200px;margin:25px auto;padding:0 15px;flex:1;">

    <!-- Campus Notification Banner -->
    <div id="notification" style="background:#348599;border:1px solid #245E6D;color:white;padding:14px 20px;border-radius:8px;position:relative;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <strong>&#128227; Official Campus Notice:</strong> Welcome to the StudentHub Academic Portal! 
        Fall Semester 2026 academic timetable and event registrations are active. Please monitor your attendance and assignment deadlines.
        <button onclick="document.getElementById('notification').style.display='none';" style="position:absolute;right:15px;top:50%;transform:translateY(-50%);background:none;border:none;color:white;font-size:24px;cursor:pointer;line-height:1;" aria-label="Close Notice">&times;</button>
    </div>

    <!-- Live Notice Ticker -->
    <?php if (!empty($announcements)): ?>
        <div style="background:#EBF5F9;border:1px solid #C7E2ED;border-radius:8px;padding:8px 15px;margin-bottom:25px;display:flex;align-items:center;gap:12px;">
            <span style="background:#245B73;color:white;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:bold;white-space:nowrap;">
                LATEST UPDATES
            </span>
            <marquee scrollamount="6" style="margin:0;font-size:14px;color:#245B73;">
                <?php foreach ($announcements as $an): ?>
                    [<?php echo htmlspecialchars($an['category']); ?>] <?php echo htmlspecialchars($an['title']); ?> (<?php echo htmlspecialchars($an['publish_date']); ?>) &nbsp;&bull;&nbsp; 
                <?php endforeach; ?>
            </marquee>
        </div>
    <?php endif; ?>

    <!-- Welcome & Hero Section -->
    <section style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;text-align:center;">
        <h2 style="color:#245B73;margin-top:0;font-size:1.8rem;">Welcome to Student Hub</h2>
        <p style="color:#555;max-width:750px;margin:10px auto 20px;font-size:15px;">
            A unified academic management and student services portal for Charotar University of Science and Technology (CHARUSAT). Manage course attendance, weekly schedules, internal grades, assignments, fees, and campus event registrations in one synchronized environment.
        </p>

        <!-- Image Slider (Practical 4 requirement) -->
        <div style="max-width:650px;margin:20px auto;display:flex;align-items:center;justify-content:center;gap:15px;">
            <button onclick="prevImage()" style="background:#348599;color:white;border:none;width:44px;height:44px;border-radius:50%;cursor:pointer;font-size:20px;box-shadow:0 2px 6px rgba(0,0,0,0.15);" aria-label="Previous image">&lt;</button>
            <div style="width:100%;height:320px;overflow:hidden;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.15);background:#F0F7FA;">
                <img id="sliderImage" src="Photo/student3.png" alt="Student Academic Activities" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <button onclick="nextImage()" style="background:#348599;color:white;border:none;width:44px;height:44px;border-radius:50%;cursor:pointer;font-size:20px;box-shadow:0 2px 6px rgba(0,0,0,0.15);" aria-label="Next image">&gt;</button>
        </div>

        <div style="margin-top:25px;display:flex;justify-content:center;gap:15px;flex-wrap:wrap;">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" style="background:linear-gradient(135deg,#245B73,#4389A8);color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;">
                    Student / Admin Login &rarr;
                </a>
                <a href="registration.php" style="background:#FFFFFF;color:#245B73;border:2px solid #245B73;padding:10px 24px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;">
                    Create Account
                </a>
            <?php else: ?>
                <a href="dashboard.php" style="background:#257E66;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;">
                    Open My Student Dashboard &rarr;
                </a>
            <?php endif; ?>
            <a href="events.php" style="background:#348599;color:white;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;">
                Explore Campus Events
            </a>
        </div>
    </section>

    <!-- Academic Modules Grid -->
    <section style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        <h3 style="color:#245B73;margin-top:0;margin-bottom:20px;border-bottom:2px solid #E1EDF2;padding-bottom:10px;">
            Academic Services &amp; Modules
        </h3>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;">
            <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;transition:transform 0.2s;">
                <h4 style="color:#245B73;margin-top:0;font-size:17px;">&#128100; Student Profile</h4>
                <p style="font-size:13px;color:#666;">View and manage your personal bio, contact info, and course details.</p>
                <a href="profile.php" style="color:#348599;font-weight:bold;text-decoration:none;font-size:13px;">View Profile &rarr;</a>
            </div>

            <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;">
                <h4 style="color:#245B73;margin-top:0;font-size:17px;">&#128197; Attendance Tracker</h4>
                <p style="font-size:13px;color:#666;">Real-time subject-wise percentage and eligibility indicators.</p>
                <a href="attendance.php" style="color:#348599;font-weight:bold;text-decoration:none;font-size:13px;">Check Attendance &rarr;</a>
            </div>

            <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;">
                <h4 style="color:#245B73;margin-top:0;font-size:17px;">&#128337; Weekly Timetable</h4>
                <p style="font-size:13px;color:#666;">Daily schedules, classroom/lab numbers, and subject faculty.</p>
                <a href="timetable.php" style="color:#348599;font-weight:bold;text-decoration:none;font-size:13px;">View Schedule &rarr;</a>
            </div>

            <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;">
                <h4 style="color:#245B73;margin-top:0;font-size:17px;">&#128221; Assignments</h4>
                <p style="font-size:13px;color:#666;">View submission deadlines, evaluation status, and upload tasks.</p>
                <a href="assignment.php" style="color:#348599;font-weight:bold;text-decoration:none;font-size:13px;">Open Assignments &rarr;</a>
            </div>

            <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;">
                <h4 style="color:#245B73;margin-top:0;font-size:17px;">&#127891; Exam Results</h4>
                <p style="font-size:13px;color:#666;">Semester SGPA/CGPA grade sheets with printable transcripts.</p>
                <a href="result.php" style="color:#348599;font-weight:bold;text-decoration:none;font-size:13px;">View Results &rarr;</a>
            </div>

            <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;">
                <h4 style="color:#245B73;margin-top:0;font-size:17px;">&#128179; Fees &amp; Receipts</h4>
                <p style="font-size:13px;color:#666;">Fee structures, payment confirmations, and transaction records.</p>
                <a href="fees.php" style="color:#348599;font-weight:bold;text-decoration:none;font-size:13px;">View Fees &rarr;</a>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Preview Section -->
    <?php if (!empty($upcoming_events)): ?>
        <section style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:2px solid #E1EDF2;padding-bottom:10px;">
                <h3 style="color:#245B73;margin:0;">Upcoming Campus Events</h3>
                <a href="events.php" style="color:#245B73;font-weight:bold;text-decoration:none;font-size:14px;">View All 15+ Events &rarr;</a>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;">
                <?php foreach ($upcoming_events as $ev): ?>
                    <div style="border:1px solid #D5E5ED;border-radius:8px;overflow:hidden;background:#FAFCFD;">
                        <div style="height:150px;background:#245B73;display:flex;align-items:center;justify-content:center;color:white;overflow:hidden;">
                            <?php 
                            $poster_src = "Photo/student3.png";
                            if (!empty($ev['poster'])) {
                                if (file_exists(__DIR__ . '/uploads/' . $ev['poster'])) {
                                    $poster_src = 'uploads/' . $ev['poster'];
                                } elseif (file_exists(__DIR__ . '/Photo/' . $ev['poster'])) {
                                    $poster_src = 'Photo/' . $ev['poster'];
                                }
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($poster_src); ?>" alt="<?php echo htmlspecialchars($ev['title']); ?>" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                        <div style="padding:15px;">
                            <span style="background:#E1F0F5;color:#1F546B;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;">
                                <?php echo htmlspecialchars($ev['category']); ?>
                            </span>
                            <h4 style="color:#245B73;margin:10px 0 5px;font-size:16px;"><?php echo htmlspecialchars($ev['title']); ?></h4>
                            <p style="font-size:12px;color:#666;margin:0 0 10px;">
                                &#128197; <?php echo htmlspecialchars($ev['event_date']); ?> &bull; &#128205; <?php echo htmlspecialchars($ev['location']); ?>
                            </p>
                            <a href="events.php?event_id=<?php echo $ev['id']; ?>" style="display:inline-block;background:#245B73;color:white;padding:6px 14px;border-radius:4px;text-decoration:none;font-size:12px;font-weight:bold;">
                                Details &amp; Registration
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</main>

<script>
// Image Slider JavaScript (Practical 4 requirement)
var sliderImages = [
    "Photo/student3.png",
    "Photo/student2.png",
    "Photo/student.png"
];
var sliderIndex = 0;

function showSliderImage() {
    var img = document.getElementById("sliderImage");
    if (img) {
        img.src = sliderImages[sliderIndex];
    }
}

function nextImage() {
    sliderIndex = (sliderIndex + 1) % sliderImages.length;
    showSliderImage();
}

function prevImage() {
    sliderIndex = (sliderIndex - 1 + sliderImages.length) % sliderImages.length;
    showSliderImage();
}

// Auto slider rotation every 5 seconds
setInterval(nextImage, 5000);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
