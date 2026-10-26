<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login();

$page_title = "Semester Results | Student Hub";
$active_page = "result";

$results = [];
$res = mysqli_query($conn, "SELECT * FROM results WHERE student_id = 1 ORDER BY id ASC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $results[] = $r;
    }
}

// Compute SGPA
$total_credits = 0;
$total_earned = 0;
foreach ($results as $r) {
    $total_credits += $r['credits'];
    $total_earned += ($r['credits'] * $r['grade_point']);
}
$sgpa = $total_credits > 0 ? round($total_earned / $total_credits, 2) : 9.60;
$cgpa = 9.82;

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1050px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;" id="printable-result">
        
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:15px;border-bottom:1px solid #E1EDF2;padding-bottom:15px;margin-bottom:20px;">
            <div>
                <h2 style="color:#245B73;margin:0 0 5px;">Semester Performance &amp; Grade Card</h2>
                <p style="margin:0;color:#666;font-size:14px;">
                    Candidate: <strong>Dhruvi Mokani</strong> &bull; ID: <code>25CS047</code> &bull; Semester: 2nd Sem End Examination
                </p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <button onclick="window.print()" style="background:#245B73;color:white;border:none;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:bold;cursor:pointer;">
                    &#128424; Print Grade Sheet
                </button>
            </div>
        </div>

        <!-- Grade Summary Cards -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:15px;margin-bottom:25px;">
            <div style="background:#F4F9FB;border:1px solid #D5E5ED;border-radius:8px;padding:15px;text-align:center;">
                <small style="color:#666;font-weight:bold;text-transform:uppercase;font-size:11px;">Semester SGPA</small>
                <div style="font-size:26px;font-weight:bold;color:#257E66;margin-top:4px;"><?php echo $sgpa; ?> / 10.0</div>
            </div>
            <div style="background:#F4F9FB;border:1px solid #D5E5ED;border-radius:8px;padding:15px;text-align:center;">
                <small style="color:#666;font-weight:bold;text-transform:uppercase;font-size:11px;">Cumulative CGPA</small>
                <div style="font-size:26px;font-weight:bold;color:#245B73;margin-top:4px;"><?php echo $cgpa; ?> / 10.0</div>
            </div>
            <div style="background:#F4F9FB;border:1px solid #D5E5ED;border-radius:8px;padding:15px;text-align:center;">
                <small style="color:#666;font-weight:bold;text-transform:uppercase;font-size:11px;">Credits Earned</small>
                <div style="font-size:26px;font-weight:bold;color:#1A4355;margin-top:4px;"><?php echo $total_credits; ?> Credits</div>
            </div>
            <div style="background:#F4F9FB;border:1px solid #D5E5ED;border-radius:8px;padding:15px;text-align:center;">
                <small style="color:#666;font-weight:bold;text-transform:uppercase;font-size:11px;">Overall Result</small>
                <div style="font-size:26px;font-weight:bold;color:#257E66;margin-top:4px;">FIRST CLASS (DIST)</div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#245B73;color:white;">
                        <th style="padding:12px;text-align:left;">Course Code</th>
                        <th style="padding:12px;text-align:left;">Course Title</th>
                        <th style="padding:12px;text-align:center;">Credits</th>
                        <th style="padding:12px;text-align:center;">Letter Grade</th>
                        <th style="padding:12px;text-align:center;">Grade Point</th>
                        <th style="padding:12px;text-align:center;">Credit Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr style="border-bottom:1px solid #E1EDF2;">
                            <td style="padding:12px;font-weight:bold;color:#245B73;"><?php echo htmlspecialchars($r['subject_code']); ?></td>
                            <td style="padding:12px;font-weight:600;color:#333;"><?php echo htmlspecialchars($r['subject_name']); ?></td>
                            <td style="padding:12px;text-align:center;"><?php echo $r['credits']; ?></td>
                            <td style="padding:12px;text-align:center;">
                                <span style="background:#DEF7EC;color:#03543F;padding:3px 8px;border-radius:4px;font-weight:bold;">
                                    <?php echo htmlspecialchars($r['grade']); ?>
                                </span>
                            </td>
                            <td style="padding:12px;text-align:center;font-weight:bold;"><?php echo $r['grade_point']; ?></td>
                            <td style="padding:12px;text-align:center;font-weight:bold;color:#257E66;">
                                <?php echo ($r['credits'] * $r['grade_point']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:#F4F9FB;font-weight:bold;">
                        <td colspan="2" style="padding:12px;text-align:right;">Total Semester Earned Credits &amp; Points:</td>
                        <td style="padding:12px;text-align:center;"><?php echo $total_credits; ?></td>
                        <td colspan="2" style="padding:12px;text-align:right;">Total Points:</td>
                        <td style="padding:12px;text-align:center;color:#257E66;"><?php echo $total_earned; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
