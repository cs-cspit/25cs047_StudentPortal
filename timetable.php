<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login();

$page_title = "Weekly Timetable | Student Hub";
$active_page = "timetable";

$selected_day = $_GET["day"] ?? "All";

$sql = "SELECT * FROM timetable";
if ($selected_day !== "All") {
    $sql .= " WHERE day = '" . mysqli_real_escape_string($conn, $selected_day) . "'";
}
$sql .= " ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), id ASC";

$res = mysqli_query($conn, $sql);
$slots = [];
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $slots[] = $r;
    }
}

$days = ['All', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1050px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        <h2 style="color:#245B73;margin-top:0 0 5px;">Class &amp; Lab Weekly Timetable</h2>
        <p style="color:#666;font-size:14px;border-bottom:1px solid #E1EDF2;padding-bottom:12px;margin-bottom:20px;">
            Department of Computer Science &amp; Engineering &bull; 3rd Semester &bull; Room &amp; Lab Allocations
        </p>

        <!-- Day Filter Tabs -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
            <?php foreach ($days as $d): ?>
                <a href="timetable.php?day=<?php echo urlencode($d); ?>" 
                   style="padding:8px 16px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;<?php echo ($selected_day === $d) ? 'background:#245B73;color:white;' : 'background:#F0F7FA;color:#245B73;border:1px solid #C7DCE5;'; ?>">
                    <?php echo $d; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#245B73;color:white;">
                        <th style="padding:12px;text-align:left;">Day</th>
                        <th style="padding:12px;text-align:left;">Time Slot</th>
                        <th style="padding:12px;text-align:left;">Subject &amp; Framework</th>
                        <th style="padding:12px;text-align:center;">Room / Lab</th>
                        <th style="padding:12px;text-align:left;">Faculty In-charge</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($slots)): ?>
                        <?php foreach ($slots as $s): ?>
                            <tr style="border-bottom:1px solid #E1EDF2;">
                                <td style="padding:12px;font-weight:bold;color:#245B73;">
                                    <?php echo htmlspecialchars($s['day']); ?>
                                </td>
                                <td style="padding:12px;color:#555;font-weight:600;">
                                    &#128337; <?php echo htmlspecialchars($s['time_slot']); ?>
                                </td>
                                <td style="padding:12px;font-weight:600;color:#1A4355;">
                                    <?php echo htmlspecialchars($s['subject']); ?>
                                </td>
                                <td style="padding:12px;text-align:center;">
                                    <span style="background:#E1F0F5;color:#1F546B;padding:3px 8px;border-radius:4px;font-weight:bold;font-size:12px;">
                                        <?php echo htmlspecialchars($s['room']); ?>
                                    </span>
                                </td>
                                <td style="padding:12px;color:#444;">
                                    <?php echo htmlspecialchars($s['faculty']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:25px;color:#888;">No lectures scheduled for this day.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
