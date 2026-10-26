<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login();

$page_title = "Attendance Tracker | Student Hub";
$active_page = "attendance";

$user_id = $_SESSION["user_id"];

// Fetch attendance records from database
$attendance_records = [];
$res = mysqli_query($conn, "SELECT subject, total_lectures, attended_lectures, 
                            ROUND((attended_lectures / total_lectures * 100), 1) as percentage 
                            FROM attendance WHERE student_id = 1 ORDER BY id ASC");
if ($res && mysqli_num_rows($res) > 0) {
    while ($r = mysqli_fetch_assoc($res)) {
        $attendance_records[] = $r;
    }
}

// Calculate overall aggregate
$total_all = 0;
$attended_all = 0;
foreach ($attendance_records as $rec) {
    $total_all += $rec['total_lectures'];
    $attended_all += $rec['attended_lectures'];
}
$aggregate_pct = $total_all > 0 ? round(($attended_all / $total_all) * 100, 1) : 85.0;

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1000px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;border-bottom:1px solid #E1EDF2;padding-bottom:15px;margin-bottom:20px;">
            <div>
                <h2 style="color:#245B73;margin:0 0 5px;">Subject-wise Attendance Tracker</h2>
                <p style="margin:0;color:#666;font-size:14px;">Academic Semester: 3rd Sem (2026-27 ODD) &bull; Minimum Required: 75%</p>
            </div>
            <div style="background:#EBF5F9;border:1px solid #76B6C9;padding:10px 18px;border-radius:8px;text-align:right;">
                <span style="font-size:12px;color:#555;font-weight:bold;text-transform:uppercase;">Overall Aggregate:</span>
                <span style="font-size:22px;font-weight:bold;color:<?php echo ($aggregate_pct >= 75) ? '#257E66' : '#C05A6F'; ?>;margin-left:8px;">
                    <?php echo $aggregate_pct; ?>%
                </span>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#245B73;color:white;">
                        <th style="padding:12px;text-align:left;">Course / Subject</th>
                        <th style="padding:12px;text-align:center;">Total Classes</th>
                        <th style="padding:12px;text-align:center;">Attended</th>
                        <th style="padding:12px;text-align:left;width:220px;">Attendance Progress</th>
                        <th style="padding:12px;text-align:center;">Percentage</th>
                        <th style="padding:12px;text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $rec): ?>
                        <?php 
                        $pct = $rec['percentage'];
                        $is_eligible = $pct >= 75.0;
                        ?>
                        <tr style="border-bottom:1px solid #E1EDF2;">
                            <td style="padding:12px;font-weight:600;color:#245B73;">
                                <?php echo htmlspecialchars($rec['subject']); ?>
                            </td>
                            <td style="padding:12px;text-align:center;"><?php echo $rec['total_lectures']; ?></td>
                            <td style="padding:12px;text-align:center;"><?php echo $rec['attended_lectures']; ?></td>
                            <td style="padding:12px;">
                                <div style="background:#E1EDF2;height:12px;border-radius:6px;overflow:hidden;">
                                    <div style="background:<?php echo $is_eligible ? '#257E66' : '#C05A6F'; ?>;height:100%;width:<?php echo min(100, $pct); ?>%;"></div>
                                </div>
                            </td>
                            <td style="padding:12px;text-align:center;font-weight:bold;color:<?php echo $is_eligible ? '#257E66' : '#C05A6F'; ?>;">
                                <?php echo $pct; ?>%
                            </td>
                            <td style="padding:12px;text-align:center;">
                                <?php if ($is_eligible): ?>
                                    <span style="background:#DEF7EC;color:#03543F;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:bold;">Eligible</span>
                                <?php else: ?>
                                    <span style="background:#FDE8E8;color:#9B1C1C;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:bold;">Shortage</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;background:#F4F9FB;padding:15px;border-radius:6px;border:1px solid #D5E5ED;font-size:13px;color:#555;">
            <strong>University Policy Note:</strong> Students are strictly required to maintain a minimum of 75% attendance in each course to be eligible for end-semester examinations as per CHARUSAT academic regulations.
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
