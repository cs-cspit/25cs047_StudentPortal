<?php
require_once __DIR__ . '/includes/auth_check.php';
require_admin(); // Admin only

$page_title = "System Audit Trail & Security Logs | Student Hub";
$active_page = "audit_logs";

$msg = "";
$err = "";

// Handle Clear Logs
if (isset($_POST["clear_logs"])) {
    if (mysqli_query($conn, "TRUNCATE TABLE audit_logs")) {
        $msg = "All audit logs cleared successfully.";
        log_audit_trail($conn, $_SESSION['user_id'], "Clear Audit Logs", "Truncated audit_logs table");
    } else {
        $err = "Failed to clear logs: " . mysqli_error($conn);
    }
}

// Search and filter
$action_filter = trim($_GET['action_type'] ?? '');
$search = trim($_GET['search'] ?? '');

$sql = "SELECT a.*, u.username, u.name, u.role 
        FROM audit_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE 1=1";
$params = [];
$types = "";

if (!empty($action_filter) && $action_filter !== 'All') {
    $sql .= " AND a.action = ?";
    $params[] = $action_filter;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (a.action LIKE ? OR a.details LIKE ? OR a.ip_address LIKE ? OR u.username LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "ssss";
}

$sql .= " ORDER BY a.id DESC LIMIT 100";

$stmt = mysqli_prepare($conn, $sql);
if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$logs = mysqli_stmt_get_result($stmt);

// Fetch distinct actions for filter dropdown
$actions_res = mysqli_query($conn, "SELECT DISTINCT action FROM audit_logs ORDER BY action ASC");
$actions_list = [];
if ($actions_res) {
    while ($ar = mysqli_fetch_assoc($actions_res)) {
        $actions_list[] = $ar['action'];
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1200px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">

    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;border-bottom:1px solid #E1EDF2;padding-bottom:15px;margin-bottom:20px;">
            <div>
                <h2 style="color:#245B73;margin:0 0 5px;">Security &amp; System Audit Trail</h2>
                <p style="margin:0;color:#666;font-size:14px;">
                    Immutable historical records of user authentication, event registration, and administrative CRUD operations.
                </p>
            </div>

            <form method="POST" onsubmit="return confirm('Are you sure you want to purge all historical audit logs?');" style="margin:0;">
                <button type="submit" name="clear_logs" style="background:#C05A6F;color:white;border:none;padding:9px 16px;border-radius:6px;font-weight:bold;font-size:13px;cursor:pointer;">
                    Purge All Logs
                </button>
            </form>
        </div>

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

        <!-- Search and Action Filter -->
        <form method="GET" action="audit_logs.php" style="display:flex;gap:12px;flex-wrap:wrap;background:#F4F9FB;padding:15px;border-radius:8px;border:1px solid #D5E5ED;margin-bottom:25px;align-items:center;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search logs by keyword, IP address, or username..." style="flex:2;min-width:240px;padding:10px 12px;border:1px solid #B9D2DF;border-radius:6px;font-size:14px;">

            <select name="action_type" style="flex:1;min-width:180px;padding:10px;border:1px solid #B9D2DF;border-radius:6px;font-size:14px;">
                <option value="All">All Actions</option>
                <?php foreach ($actions_list as $act): ?>
                    <option value="<?php echo htmlspecialchars($act); ?>" <?php if ($action_filter === $act) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($act); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" style="background:#245B73;color:white;border:none;padding:10px 20px;border-radius:6px;font-weight:bold;cursor:pointer;">
                Filter Logs
            </button>
            <a href="audit_logs.php" style="color:#666;font-size:13px;text-decoration:none;">Reset</a>
        </form>

        <!-- Audit Log Table -->
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13.5px;text-align:left;">
                <thead>
                    <tr style="background:#245B73;color:white;">
                        <th style="padding:11px;border:1px solid #348599;">#</th>
                        <th style="padding:11px;border:1px solid #348599;">Timestamp</th>
                        <th style="padding:11px;border:1px solid #348599;">User</th>
                        <th style="padding:11px;border:1px solid #348599;">Role</th>
                        <th style="padding:11px;border:1px solid #348599;">Action</th>
                        <th style="padding:11px;border:1px solid #348599;">Details</th>
                        <th style="padding:11px;border:1px solid #348599;">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($logs) > 0): ?>
                        <?php while ($l = mysqli_fetch_assoc($logs)): ?>
                            <tr style="border-bottom:1px solid #E1EDF2;">
                                <td style="padding:10px;font-weight:bold;color:#245B73;"><?php echo $l['id']; ?></td>
                                <td style="padding:10px;color:#555;white-space:nowrap;"><?php echo htmlspecialchars($l['created_at'] ?? $l['timestamp'] ?? ''); ?></td>
                                <td style="padding:10px;font-weight:600;">
                                    <?php echo htmlspecialchars($l['username'] ?? ('User #' . $l['user_id'])); ?>
                                </td>
                                <td style="padding:10px;">
                                    <?php 
                                    $role = $l['role'] ?? 'user';
                                    $bg = ($role === 'admin') ? '#FDE8E8;color:#9B1C1C;' : '#DEF7EC;color:#03543F;';
                                    ?>
                                    <span style="background:<?php echo $bg; ?>;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:bold;">
                                        <?php echo ucfirst($role); ?>
                                    </span>
                                </td>
                                <td style="padding:10px;font-weight:600;color:#245B73;">
                                    <?php echo htmlspecialchars($l['action']); ?>
                                </td>
                                <td style="padding:10px;color:#444;">
                                    <?php echo htmlspecialchars($l['details'] ?? 'N/A'); ?>
                                </td>
                                <td style="padding:10px;font-family:monospace;color:#666;">
                                    <?php echo htmlspecialchars($l['ip_address'] ?? '127.0.0.1'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:30px;color:#888;">
                                No audit log records found matching query.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
