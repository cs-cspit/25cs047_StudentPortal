<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login();

$page_title = "Fees & Payments | Student Hub";
$active_page = "fees";

$fees = [];
$res = mysqli_query($conn, "SELECT * FROM fees WHERE student_id = 1 ORDER BY id ASC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $fees[] = $r;
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1050px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        <h2 style="color:#245B73;margin-top:0 0 5px;">Tuition Fees &amp; Transaction History</h2>
        <p style="color:#666;font-size:14px;border-bottom:1px solid #E1EDF2;padding-bottom:12px;margin-bottom:20px;">
            Semester fee breakdown, payment confirmation status, and e-receipts.
        </p>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#245B73;color:white;">
                        <th style="padding:12px;text-align:left;">Semester</th>
                        <th style="padding:12px;text-align:right;">Tuition Fee Amount</th>
                        <th style="padding:12px;text-align:center;">Due Date</th>
                        <th style="padding:12px;text-align:center;">Status</th>
                        <th style="padding:12px;text-align:left;">Transaction Reference ID</th>
                        <th style="padding:12px;text-align:center;">Date Paid</th>
                        <th style="padding:12px;text-align:center;">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fees as $f): ?>
                        <tr style="border-bottom:1px solid #E1EDF2;">
                            <td style="padding:12px;font-weight:bold;color:#245B73;"><?php echo htmlspecialchars($f['semester']); ?></td>
                            <td style="padding:12px;text-align:right;font-weight:bold;color:#333;">
                                &#8377; <?php echo number_format($f['amount'], 2); ?>
                            </td>
                            <td style="padding:12px;text-align:center;color:#666;"><?php echo htmlspecialchars($f['due_date']); ?></td>
                            <td style="padding:12px;text-align:center;">
                                <?php if ($f['status'] === 'Paid'): ?>
                                    <span style="background:#DEF7EC;color:#03543F;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:bold;">Paid</span>
                                <?php else: ?>
                                    <span style="background:#FDE8E8;color:#9B1C1C;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:bold;">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px;font-family:monospace;font-size:13px;color:#555;">
                                <?php echo htmlspecialchars($f['transaction_id'] ?? 'N/A'); ?>
                            </td>
                            <td style="padding:12px;text-align:center;color:#666;"><?php echo htmlspecialchars($f['paid_at'] ?? '--'); ?></td>
                            <td style="padding:12px;text-align:center;">
                                <button onclick="alert('Receipt for <?php echo addslashes($f['semester']); ?> (Ref: <?php echo addslashes($f['transaction_id']); ?>) downloaded successfully!');" style="background:#245B73;color:white;border:none;padding:5px 12px;border-radius:4px;font-size:12px;font-weight:bold;cursor:pointer;">
                                    &#128196; Receipt
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
