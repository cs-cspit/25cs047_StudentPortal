<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login();

$page_title = "Assignments | Student Hub";
$active_page = "assignment";

$msg = "";
// Handle submission simulation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_assignment"])) {
    $as_id = (int)($_POST["assignment_id"] ?? 0);
    if ($as_id > 0) {
        $up = mysqli_query($conn, "UPDATE assignments SET status = 'Submitted', score = 95 WHERE id = $as_id");
        $msg = "Assignment #$as_id submitted successfully! Timestamp recorded.";
    }
}

// Fetch assignments
$assignments = [];
$res = mysqli_query($conn, "SELECT * FROM assignments ORDER BY due_date ASC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $assignments[] = $r;
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1050px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        <h2 style="color:#245B73;margin-top:0 0 5px;">Continuous Evaluation &amp; Assignments</h2>
        <p style="color:#666;font-size:14px;border-bottom:1px solid #E1EDF2;padding-bottom:12px;margin-bottom:20px;">
            Submit your laboratory tasks, project milestones, and assignment deliverables.
        </p>

        <?php if ($msg): ?>
            <div style="background:#DEF7EC;color:#03543F;padding:12px;border-radius:6px;font-weight:bold;margin-bottom:20px;">
                &#10004; <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#245B73;color:white;">
                        <th style="padding:12px;text-align:left;">Subject</th>
                        <th style="padding:12px;text-align:left;">Assignment Title</th>
                        <th style="padding:12px;text-align:center;">Due Date</th>
                        <th style="padding:12px;text-align:center;">Max Marks</th>
                        <th style="padding:12px;text-align:center;">Score</th>
                        <th style="padding:12px;text-align:center;">Status</th>
                        <th style="padding:12px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $a): ?>
                        <tr style="border-bottom:1px solid #E1EDF2;">
                            <td style="padding:12px;font-weight:bold;color:#245B73;">
                                <?php echo htmlspecialchars($a['subject']); ?>
                            </td>
                            <td style="padding:12px;font-weight:600;color:#333;">
                                <?php echo htmlspecialchars($a['title']); ?>
                            </td>
                            <td style="padding:12px;text-align:center;color:#666;">
                                &#128197; <?php echo htmlspecialchars($a['due_date']); ?>
                            </td>
                            <td style="padding:12px;text-align:center;"><?php echo $a['max_marks']; ?></td>
                            <td style="padding:12px;text-align:center;font-weight:bold;color:#257E66;">
                                <?php echo ($a['score'] !== null) ? $a['score'] : '--'; ?>
                            </td>
                            <td style="padding:12px;text-align:center;">
                                <?php if ($a['status'] === 'Submitted'): ?>
                                    <span style="background:#DEF7EC;color:#03543F;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:bold;">Submitted</span>
                                <?php elseif ($a['status'] === 'Evaluated'): ?>
                                    <span style="background:#E1F0F5;color:#1F546B;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:bold;">Evaluated</span>
                                <?php else: ?>
                                    <span style="background:#FEF08A;color:#854D0E;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:bold;">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px;text-align:center;">
                                <?php if ($a['status'] === 'Pending'): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
                                        <button type="submit" name="submit_assignment" style="background:#257E66;color:white;border:none;padding:5px 12px;border-radius:4px;font-size:12px;font-weight:bold;cursor:pointer;">
                                            Upload / Submit
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#257E66;font-size:12px;font-weight:bold;">&check; Complete</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
