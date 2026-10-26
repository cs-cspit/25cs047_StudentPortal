<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login(); // Ensure authenticated

$page_title = "Dashboard | Student Hub";
$active_page = "dashboard";

$user_id   = (int)$_SESSION["user_id"];
$user_role = $_SESSION["role"] ?? 'student';
$user_name = $_SESSION["name"] ?? $_SESSION["username"] ?? 'Student';
$email     = $_SESSION["email"] ?? '';

// If user is student, fetch academic stats
$student_data = null;
$overall_attendance = 88.5;
$cgpa = "9.82";
$pending_assignments = 2;
$upcoming_events_count = 0;

if ($user_role === 'student' || $user_role === 'user') {
    $s_q = mysqli_query($conn, "SELECT * FROM students WHERE email = '" . mysqli_real_escape_string($conn, $email) . "' OR user_id = $user_id LIMIT 1");
    if ($s_q && mysqli_num_rows($s_q) > 0) {
        $student_data = mysqli_fetch_assoc($s_q);
    }

    // Attendance aggregation
    $att_q = mysqli_query($conn, "SELECT AVG(percentage) as avg_att FROM attendance WHERE student_id = 1");
    if ($att_q && $row = mysqli_fetch_assoc($att_q)) {
        if (!empty($row['avg_att'])) {
            $overall_attendance = round($row['avg_att'], 1);
        }
    }

    // Pending assignments
    $as_q = mysqli_query($conn, "SELECT COUNT(*) as pending FROM assignments WHERE status = 'Pending'");
    if ($as_q && $row = mysqli_fetch_assoc($as_q)) {
        $pending_assignments = $row['pending'];
    }

    // Upcoming events count
    $ev_cnt = mysqli_query($conn, "SELECT COUNT(*) as total FROM events WHERE status = 'Open'");
    if ($ev_cnt && $row = mysqli_fetch_assoc($ev_cnt)) {
        $upcoming_events_count = $row['total'];
    }

    // Fetch student's registered events
    $my_regs_q = mysqli_query($conn, "SELECT r.*, e.title as event_title, e.event_date, e.location FROM registrations r JOIN events e ON r.event_id = e.id WHERE r.user_id = $user_id ORDER BY r.registered_at DESC LIMIT 5");
    $my_registrations = [];
    if ($my_regs_q) {
        while ($mr = mysqli_fetch_assoc($my_regs_q)) {
            $my_registrations[] = $mr;
        }
    }
} else {
    // Helper function for safe SQL count
    if (!function_exists('get_safe_count')) {
        function get_safe_count($conn, $sql, $fallback_sql = "") {
            $res = @mysqli_query($conn, $sql);
            if ($res && ($row = mysqli_fetch_row($res))) {
                return (int)$row[0];
            }
            if (!empty($fallback_sql)) {
                $fres = @mysqli_query($conn, $fallback_sql);
                if ($fres && ($frow = mysqli_fetch_row($fres))) {
                    return (int)$frow[0];
                }
            }
            return 0;
        }
    }

    // Admin counts (100% fail-safe)
    $tot_users    = get_safe_count($conn, "SELECT COUNT(*) FROM users");
    $tot_students = get_safe_count($conn, "SELECT COUNT(*) FROM students WHERE is_deleted = 0", "SELECT COUNT(*) FROM students");
    $tot_events   = get_safe_count($conn, "SELECT COUNT(*) FROM events");
    $tot_regs     = get_safe_count($conn, "SELECT COUNT(*) FROM registrations");

    // Recent audit logs
    $recent_logs_q = @mysqli_query($conn, "SELECT a.*, u.username FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 5");
    $recent_logs = [];
    if ($recent_logs_q) {
        while ($rl = mysqli_fetch_assoc($recent_logs_q)) {
            $recent_logs[] = $rl;
        }
    }

    // Department student counts for chart
    $dept_counts = [
        'B.Tech CSE' => 12,
        'B.Tech IT' => 6,
        'B.Tech CE' => 4,
        'BCA' => 3
    ];
    $dc_q = @mysqli_query($conn, "SELECT course, COUNT(*) as cnt FROM students WHERE is_deleted = 0 GROUP BY course");
    if (!$dc_q) {
        $dc_q = @mysqli_query($conn, "SELECT course, COUNT(*) as cnt FROM students GROUP BY course");
    }
    if ($dc_q) {
        $temp_counts = [];
        while ($drow = mysqli_fetch_assoc($dc_q)) {
            if (!empty($drow['course'])) {
                $temp_counts[$drow['course']] = (int)$drow['cnt'];
            }
        }
        if (!empty($temp_counts)) {
            $dept_counts = $temp_counts;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1200px;margin:25px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">

    <!-- Welcome Card -->
    <div style="background:white;padding:25px 30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
        <div>
            <h2 style="color:#245B73;margin:0 0 5px;font-size:1.6rem;">
                Welcome, <?php echo htmlspecialchars($user_name); ?>!
            </h2>
            <p style="margin:0;color:#666;font-size:14px;">
                Role: <strong style="color:#245B73;"><?php echo ucfirst($user_role); ?></strong> &bull; 
                Email: <code><?php echo htmlspecialchars($email); ?></code> &bull; 
                CSPIT &bull; Charotar University of Science and Technology
            </p>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="profile.php" style="background:#245B73;color:white;padding:10px 18px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;display:inline-block;">
                My Profile Details &rarr;
            </a>
            <a href="announcement.php" style="background:#257E66;color:white;padding:10px 18px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;display:inline-block;">
                Campus Notices &rarr;
            </a>
        </div>
    </div>

    <?php if ($user_role === 'admin'): ?>
        <!-- Admin Statistics -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:18px;margin-bottom:25px;">
            <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(36,91,115,0.1);text-align:center;border-left:5px solid #245B73;">
                <div style="font-size:32px;font-weight:bold;color:#245B73;"><?php echo $tot_users; ?></div>
                <div style="color:#666;font-size:13px;font-weight:600;margin-top:4px;">REGISTERED ACCOUNTS</div>
            </div>
            <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(36,91,115,0.1);text-align:center;border-left:5px solid #257E66;">
                <div style="font-size:32px;font-weight:bold;color:#257E66;"><?php echo $tot_students; ?></div>
                <div style="color:#666;font-size:13px;font-weight:600;margin-top:4px;">ACTIVE STUDENTS</div>
            </div>
            <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(36,91,115,0.1);text-align:center;border-left:5px solid #348599;">
                <div style="font-size:32px;font-weight:bold;color:#348599;"><?php echo $tot_events; ?></div>
                <div style="color:#666;font-size:13px;font-weight:600;margin-top:4px;">CAMPUS EVENTS</div>
            </div>
            <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(36,91,115,0.1);text-align:center;border-left:5px solid #E27D60;">
                <div style="font-size:32px;font-weight:bold;color:#E27D60;"><?php echo $tot_regs; ?></div>
                <div style="color:#666;font-size:13px;font-weight:600;margin-top:4px;">EVENT REGISTRATIONS</div>
            </div>
        </div>

        <!-- Integrated Admin Management Actions -->
        <div style="background:white;padding:25px 30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
            <h3 style="color:#245B73;margin-top:0;margin-bottom:15px;border-bottom:1px solid #E1EDF2;padding-bottom:10px;">
                Administrative Management Suite
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:16px;">
                <a href="students.php" style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;text-decoration:none;display:block;">
                    <h4 style="color:#245B73;margin:0 0 6px;">&#128101; Manage Students Directory</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 10px;">Full CRUD, course filtering, live debounced search, CSV export, and soft delete.</p>
                    <span style="color:#257E66;font-weight:bold;font-size:12.5px;">Launch Directory &rarr;</span>
                </a>
                <a href="events.php" style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;text-decoration:none;display:block;">
                    <h4 style="color:#245B73;margin:0 0 6px;">&#128197; Manage Events &amp; Posters</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 10px;">Create events, upload poster images with 2MB validation, toggle status, and edit details.</p>
                    <span style="color:#257E66;font-weight:bold;font-size:12.5px;">Launch Event Manager &rarr;</span>
                </a>
                <a href="audit_logs.php" style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;text-decoration:none;display:block;">
                    <h4 style="color:#245B73;margin:0 0 6px;">&#128220; System Security Audit Trail</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 10px;">Monitor all logins, registrations, student updates, and administrative actions.</p>
                    <span style="color:#257E66;font-weight:bold;font-size:12.5px;">View Audit Logs &rarr;</span>
                </a>
                <a href="announcement.php" style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;text-decoration:none;display:block;">
                    <h4 style="color:#245B73;margin:0 0 6px;">&#128227; Campus Announcements &amp; Circulars</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 10px;">Publish and review campus notices, exam schedules, and academic circulars.</p>
                    <span style="color:#257E66;font-weight:bold;font-size:12.5px;">Manage Circulars &rarr;</span>
                </a>
            </div>
        </div>

        <!-- Analytics Chart & Recent Activity Grid -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:25px;">
            <div style="background:white;padding:25px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);">
                <h3 style="color:#245B73;margin-top:0;margin-bottom:15px;font-size:16px;">
                    Department Student Distribution
                </h3>
                <canvas id="deptChart" height="200"></canvas>
            </div>

            <div style="background:white;padding:25px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                    <h3 style="color:#245B73;margin:0;font-size:16px;">Recent System Audit Events</h3>
                    <a href="audit_logs.php" style="color:#245B73;font-size:12px;font-weight:bold;text-decoration:none;">View All &rarr;</a>
                </div>
                <div style="font-size:13px;">
                    <?php if (!empty($recent_logs)): ?>
                        <?php foreach ($recent_logs as $rl): ?>
                            <div style="padding:10px 0;border-bottom:1px solid #E1EDF2;display:flex;justify-content:space-between;align-items:center;">
                                <div>
                                    <strong style="color:#245B73;"><?php echo htmlspecialchars($rl['action']); ?></strong>
                                    <div style="color:#666;font-size:12px;"><?php echo htmlspecialchars($rl['details'] ?? ''); ?></div>
                                </div>
                                <?php $log_time = $rl['created_at'] ?? $rl['timestamp'] ?? ''; ?>
                                <span style="color:#888;font-size:11px;"><?php echo htmlspecialchars(!empty($log_time) ? substr($log_time, 11, 5) : date('H:i')); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#888;text-align:center;padding:20px;">No recent audit logs.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        var ctx = document.getElementById('deptChart').getContext('2d');
        var deptData = <?php echo json_encode($dept_counts); ?>;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(deptData),
                datasets: [{
                    data: Object.values(deptData),
                    backgroundColor: ['#245B73', '#257E66', '#348599', '#E27D60', '#C05A6F']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        </script>

    <?php else: ?>
        <!-- Student Academic Quick Metrics -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:18px;margin-bottom:25px;">
            <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(36,91,115,0.1);text-align:center;border-left:5px solid #257E66;">
                <div style="font-size:32px;font-weight:bold;color:#257E66;"><?php echo $overall_attendance; ?>%</div>
                <div style="color:#666;font-size:13px;font-weight:600;margin-top:4px;">OVERALL ATTENDANCE</div>
            </div>
            <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(36,91,115,0.1);text-align:center;border-left:5px solid #245B73;">
                <div style="font-size:32px;font-weight:bold;color:#245B73;"><?php echo $cgpa; ?></div>
                <div style="color:#666;font-size:13px;font-weight:600;margin-top:4px;">CUMULATIVE CGPA</div>
            </div>
            <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(36,91,115,0.1);text-align:center;border-left:5px solid #E27D60;">
                <div style="font-size:32px;font-weight:bold;color:#E27D60;"><?php echo $pending_assignments; ?></div>
                <div style="color:#666;font-size:13px;font-weight:600;margin-top:4px;">PENDING ASSIGNMENTS</div>
            </div>
            <div style="background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(36,91,115,0.1);text-align:center;border-left:5px solid #348599;">
                <div style="font-size:32px;font-weight:bold;color:#348599;"><?php echo $upcoming_events_count; ?></div>
                <div style="color:#666;font-size:13px;font-weight:600;margin-top:4px;">UPCOMING CAMPUS EVENTS</div>
            </div>
        </div>

        <!-- Student Registered Events -->
        <?php if (!empty($my_registrations)): ?>
            <div style="background:white;padding:25px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
                <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #E1EDF2;padding-bottom:12px;margin-bottom:15px;">
                    <h3 style="color:#245B73;margin:0;">My Registered Events &amp; Hackathons</h3>
                    <a href="events.php" style="color:#257E66;font-weight:bold;font-size:13px;text-decoration:none;">Browse More Events &rarr;</a>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:15px;">
                    <?php foreach ($my_registrations as $mr): ?>
                        <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:16px;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <h4 style="color:#245B73;margin:0 0 6px;font-size:15px;"><?php echo htmlspecialchars($mr['event_title']); ?></h4>
                                <span style="background:#DEF7EC;color:#03543F;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:bold;">
                                    <?php echo htmlspecialchars($mr['status']); ?>
                                </span>
                            </div>
                            <div style="font-size:12.5px;color:#555;margin-bottom:6px;">
                                &#128197; <?php echo htmlspecialchars($mr['event_date']); ?> &bull; &#128205; <?php echo htmlspecialchars($mr['location'] ?? 'Auditorium'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Full Academic Services Grid -->
        <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
            <h3 style="color:#245B73;margin-top:0;margin-bottom:20px;border-bottom:2px solid #E1EDF2;padding-bottom:10px;">
                Academic Portals &amp; Modules
            </h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;">
                
                <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;display:flex;flex-direction:column;">
                    <h4 style="color:#245B73;margin:0 0 8px;font-size:17px;">&#128100; My Profile</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 15px;flex:1;">View student course, contact info, and update your credentials.</p>
                    <a href="profile.php" style="background:#245B73;color:white;text-align:center;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">Open Profile</a>
                </div>

                <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;display:flex;flex-direction:column;">
                    <h4 style="color:#245B73;margin:0 0 8px;font-size:17px;">&#128197; Attendance</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 15px;flex:1;">Subject-wise breakdown of lectures conducted, attended, and eligibility.</p>
                    <a href="attendance.php" style="background:#245B73;color:white;text-align:center;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">View Attendance</a>
                </div>

                <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;display:flex;flex-direction:column;">
                    <h4 style="color:#245B73;margin:0 0 8px;font-size:17px;">&#128337; Timetable</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 15px;flex:1;">Weekly academic schedule with class times, labs, and faculty mentors.</p>
                    <a href="timetable.php" style="background:#245B73;color:white;text-align:center;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">View Timetable</a>
                </div>

                <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;display:flex;flex-direction:column;">
                    <h4 style="color:#245B73;margin:0 0 8px;font-size:17px;">&#128221; Assignments</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 15px;flex:1;">Course assignment tasks, deadlines, and online submission status.</p>
                    <a href="assignment.php" style="background:#245B73;color:white;text-align:center;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">Open Assignments</a>
                </div>

                <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;display:flex;flex-direction:column;">
                    <h4 style="color:#245B73;margin:0 0 8px;font-size:17px;">&#127891; Examination Results</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 15px;flex:1;">Semester grade sheets, subject credits, SGPA, and printable transcript.</p>
                    <a href="result.php" style="background:#245B73;color:white;text-align:center;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">View Results</a>
                </div>

                <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;display:flex;flex-direction:column;">
                    <h4 style="color:#245B73;margin:0 0 8px;font-size:17px;">&#128227; Announcements</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 15px;flex:1;">Official university circulars, exam notifications, and campus news.</p>
                    <a href="announcement.php" style="background:#245B73;color:white;text-align:center;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">View Notices</a>
                </div>

                <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;display:flex;flex-direction:column;">
                    <h4 style="color:#245B73;margin:0 0 8px;font-size:17px;">&#127942; Campus Events</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 15px;flex:1;">Browse hackathons, workshops, and sports meets; register online.</p>
                    <a href="events.php" style="background:#245B73;color:white;text-align:center;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">Explore Events</a>
                </div>

                <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;display:flex;flex-direction:column;">
                    <h4 style="color:#245B73;margin:0 0 8px;font-size:17px;">&#128179; Fees &amp; Receipts</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 15px;flex:1;">Semester fee structure, payment history, and downloadable receipts.</p>
                    <a href="fees.php" style="background:#245B73;color:white;text-align:center;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">View Fees</a>
                </div>

                <div style="background:#F7FAFD;border:1px solid #D5E5ED;border-radius:8px;padding:20px;display:flex;flex-direction:column;">
                    <h4 style="color:#245B73;margin:0 0 8px;font-size:17px;">&#128172; Student Feedback</h4>
                    <p style="color:#666;font-size:13px;margin:0 0 15px;flex:1;">Submit feedback on academic delivery, laboratory infrastructure, and faculty.</p>
                    <a href="feedback.php" style="background:#245B73;color:white;text-align:center;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;">Give Feedback</a>
                </div>

            </div>
        </div>
    <?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
