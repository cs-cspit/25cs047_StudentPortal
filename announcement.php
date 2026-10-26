<?php
require_once __DIR__ . '/includes/auth_check.php';

$page_title = "Announcements | Student Hub";
$active_page = "announcement";

$category = $_GET['cat'] ?? 'All';

$sql = "SELECT * FROM announcements WHERE is_active = 1";
if ($category !== 'All') {
    $sql .= " AND category = '" . mysqli_real_escape_string($conn, $category) . "'";
}
$sql .= " ORDER BY publish_date DESC, id DESC";

$res = mysqli_query($conn, $sql);
$announcements = [];
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $announcements[] = $r;
    }
}

$categories = ['All', 'Academic', 'Event', 'General'];

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1050px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">
    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        <h2 style="color:#245B73;margin-top:0 0 5px;">Campus Circulars &amp; Official Notices</h2>
        <p style="color:#666;font-size:14px;border-bottom:1px solid #E1EDF2;padding-bottom:12px;margin-bottom:20px;">
            Stay updated with examination schedules, holidays, placement drives, and campus workshops.
        </p>

        <!-- Category Filter -->
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:25px;">
            <?php foreach ($categories as $c): ?>
                <a href="announcement.php?cat=<?php echo urlencode($c); ?>"
                   style="padding:8px 16px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;<?php echo ($category === $c) ? 'background:#245B73;color:white;' : 'background:#F0F7FA;color:#245B73;border:1px solid #C7DCE5;'; ?>">
                    <?php echo $c; ?> Notices
                </a>
            <?php endforeach; ?>
        </div>

        <div style="display:flex;flex-direction:column;gap:15px;">
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $an): ?>
                    <div style="border:1px solid #D5E5ED;border-left:5px solid #245B73;border-radius:8px;padding:20px;background:#FAFCFD;">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:8px;">
                            <span style="background:#E1F0F5;color:#1F546B;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:bold;">
                                <?php echo htmlspecialchars($an['category']); ?>
                            </span>
                            <span style="color:#888;font-size:12px;">
                                &#128197; Published on <?php echo htmlspecialchars($an['publish_date']); ?>
                            </span>
                        </div>
                        <h3 style="color:#245B73;margin:0 0 10px;font-size:18px;">
                            <?php echo htmlspecialchars($an['title']); ?>
                        </h3>
                        <p style="color:#4B5563;font-size:14px;line-height:1.6;margin:0;">
                            <?php echo nl2br(htmlspecialchars($an['content'])); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#888;text-align:center;padding:30px;">No announcements found in this category.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
