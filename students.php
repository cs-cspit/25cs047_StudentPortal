<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login(); // All logged in users can view; admin has full CRUD

$page_title = "Student Directory & Management | Student Hub";
$active_page = "students";

$is_admin = (($_SESSION['role'] ?? '') === 'admin');
$msg = "";
$err = "";

// CSV Export (Practical 11 extension)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=students_export_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Course', 'Status', 'Created At']);

    $q = mysqli_query($conn, "SELECT id, name, email, phone, course, status, created_at FROM students WHERE is_deleted = 0 ORDER BY id ASC");
    while ($row = mysqli_fetch_assoc($q)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Handle Add Student (Admin only)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_student"])) {
    if (!$is_admin) {
        $err = "Unauthorized action. Admin privilege required.";
    } else {
        $name   = trim($_POST["name"] ?? "");
        $email  = trim($_POST["email"] ?? "");
        $phone  = trim($_POST["phone"] ?? "");
        $course = trim($_POST["course"] ?? "Computer Science");
        $status = trim($_POST["status"] ?? "Active");

        if (empty($name) || empty($email)) {
            $err = "Name and Email are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = "Invalid email format.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, phone, course, status, is_deleted) VALUES (?, ?, ?, ?, ?, 0)");
            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $course, $status);
            if (mysqli_stmt_execute($stmt)) {
                $new_id = mysqli_insert_id($conn);
                $msg = "Student record #$new_id successfully added!";
                log_audit_trail($conn, $_SESSION['user_id'], "Add Student", "Added student $name ($email)");
            } else {
                $err = "Failed to add student: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Handle Edit Student (Admin only)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_student"])) {
    if (!$is_admin) {
        $err = "Unauthorized action. Admin privilege required.";
    } else {
        $id     = (int)($_POST["student_id"] ?? 0);
        $name   = trim($_POST["name"] ?? "");
        $email  = trim($_POST["email"] ?? "");
        $phone  = trim($_POST["phone"] ?? "");
        $course = trim($_POST["course"] ?? "");
        $status = trim($_POST["status"] ?? "Active");

        if ($id > 0 && !empty($name) && !empty($email)) {
            $stmt = mysqli_prepare($conn, "UPDATE students SET name = ?, email = ?, phone = ?, course = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $phone, $course, $status, $id);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Student record #$id successfully updated!";
                log_audit_trail($conn, $_SESSION['user_id'], "Edit Student", "Updated student #$id ($name)");
            } else {
                $err = "Update failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Handle Soft Delete (Admin only - Practical 11)
if (isset($_GET["delete"])) {
    if (!$is_admin) {
        $err = "Unauthorized action.";
    } else {
        $del_id = (int)$_GET["delete"];
        if ($del_id > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE students SET is_deleted = 1, status = 'Inactive' WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $del_id);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Student #$del_id soft-deleted successfully.";
                log_audit_trail($conn, $_SESSION['user_id'], "Delete Student", "Soft deleted student #$del_id");
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch Departments / Courses for filter dropdown
$courses_res = mysqli_query($conn, "SELECT DISTINCT course FROM students WHERE course IS NOT NULL AND course != '' ORDER BY course ASC");
$courses_list = [];
if ($courses_res) {
    while ($c = mysqli_fetch_assoc($courses_res)) {
        $courses_list[] = $c['course'];
    }
}

// Search and filter parameters
$search_query   = trim($_GET['search'] ?? '');
$course_filter  = trim($_GET['course'] ?? '');

$sql = "SELECT * FROM students WHERE is_deleted = 0";
$params = [];
$types = "";

if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like = "%$search_query%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

if (!empty($course_filter) && $course_filter !== 'All') {
    $sql .= " AND course = ?";
    $params[] = $course_filter;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$students = mysqli_stmt_get_result($stmt);

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1200px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">

    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;border-bottom:1px solid #E1EDF2;padding-bottom:15px;margin-bottom:20px;">
            <div>
                <h2 style="color:#245B73;margin:0 0 5px;">Student Directory &amp; Records</h2>
                <p style="margin:0;color:#666;font-size:14px;">
                    Central repository of enrolled students across CSPIT CHARUSAT departments.
                </p>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="students.php?export=csv" style="background:#257E66;color:white;padding:9px 16px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:13px;display:inline-flex;align-items:center;gap:6px;">
                    &#128196; Export to CSV
                </a>
                <?php if ($is_admin): ?>
                    <button type="button" onclick="openAddModal()" style="background:#245B73;color:white;border:none;padding:9px 18px;border-radius:6px;font-weight:bold;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                        + Add New Student
                    </button>
                <?php endif; ?>
            </div>
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

        <!-- Search & Filter Controls with Debounced Live AJAX (Practical 13) -->
        <div style="background:#F4F9FB;padding:16px;border-radius:8px;border:1px solid #D5E5ED;margin-bottom:25px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <div style="flex:2;min-width:240px;position:relative;">
                <input type="text" id="liveSearchInput" placeholder="Live search by name, email, or phone (instant)..." value="<?php echo htmlspecialchars($search_query); ?>" style="width:100%;padding:10px 14px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;font-size:14px;">
            </div>

            <div style="flex:1;min-width:180px;">
                <select id="courseFilterSelect" onchange="applyFilters()" style="width:100%;padding:10px;border:1px solid #B9D2DF;border-radius:6px;font-size:14px;">
                    <option value="All">All Departments / Courses</option>
                    <?php foreach ($courses_list as $c): ?>
                        <option value="<?php echo htmlspecialchars($c); ?>" <?php if ($course_filter === $c) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($c); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" onclick="applyFilters()" style="background:#245B73;color:white;border:none;padding:10px 18px;border-radius:6px;font-weight:bold;cursor:pointer;">
                Search
            </button>
            <a href="students.php" style="color:#666;font-size:13px;text-decoration:none;">Reset</a>
        </div>

        <!-- Student Data Table -->
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;text-align:left;">
                <thead>
                    <tr style="background:#245B73;color:white;">
                        <th style="padding:12px;border:1px solid #348599;">ID</th>
                        <th style="padding:12px;border:1px solid #348599;">Student Name</th>
                        <th style="padding:12px;border:1px solid #348599;">Email Address</th>
                        <th style="padding:12px;border:1px solid #348599;">Phone</th>
                        <th style="padding:12px;border:1px solid #348599;">Course / Department</th>
                        <th style="padding:12px;border:1px solid #348599;">Status</th>
                        <?php if ($is_admin): ?>
                            <th style="padding:12px;border:1px solid #348599;text-align:center;">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="studentTableBody">
                    <?php if (mysqli_num_rows($students) > 0): ?>
                        <?php while ($s = mysqli_fetch_assoc($students)): ?>
                            <tr style="border-bottom:1px solid #E1EDF2;">
                                <td style="padding:11px;font-weight:bold;color:#245B73;"><?php echo $s['id']; ?></td>
                                <td style="padding:11px;font-weight:600;"><?php echo htmlspecialchars($s['name']); ?></td>
                                <td style="padding:11px;color:#555;"><?php echo htmlspecialchars($s['email']); ?></td>
                                <td style="padding:11px;color:#555;"><?php echo htmlspecialchars($s['phone'] ?? 'N/A'); ?></td>
                                <td style="padding:11px;"><span style="background:#E1F0F5;color:#1B546B;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:600;"><?php echo htmlspecialchars($s['course'] ?? 'General'); ?></span></td>
                                <td style="padding:11px;">
                                    <?php if ($s['status'] === 'Active'): ?>
                                        <span style="background:#DEF7EC;color:#03543F;padding:3px 8px;border-radius:12px;font-size:11px;font-weight:bold;">Active</span>
                                    <?php else: ?>
                                        <span style="background:#FDE8E8;color:#9B1C1C;padding:3px 8px;border-radius:12px;font-size:11px;font-weight:bold;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($is_admin): ?>
                                    <td style="padding:11px;text-align:center;white-space:nowrap;">
                                        <button type="button" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($s)); ?>)" style="background:#348599;color:white;border:none;padding:5px 10px;border-radius:4px;font-size:12px;cursor:pointer;font-weight:600;">
                                            Edit
                                        </button>
                                        <a href="students.php?delete=<?php echo $s['id']; ?>" onclick="return confirm('Are you sure you want to soft delete student #<?php echo $s['id']; ?>?')" style="background:#C05A6F;color:white;text-decoration:none;padding:5px 10px;border-radius:4px;font-size:12px;font-weight:600;margin-left:5px;">
                                            Delete
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $is_admin ? 7 : 6; ?>" style="text-align:center;padding:30px;color:#888;">
                                No student records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</main>

<!-- Add Student Modal (Admin only) -->
<?php if ($is_admin): ?>
<div id="addModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;padding:15px;box-sizing:border-box;">
    <div style="background:white;width:100%;max-width:500px;border-radius:10px;padding:25px 30px;box-shadow:0 10px 25px rgba(0,0,0,0.3);position:relative;">
        <button onclick="closeAddModal()" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#666;">&times;</button>
        <h3 style="color:#245B73;margin-top:0;">Add New Student</h3>
        
        <form method="POST" action="students.php">
            <input type="hidden" name="add_student" value="1">

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Full Name *</label>
                <input type="text" name="name" required placeholder="e.g. Dhruvi Mokani" style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Email Address *</label>
                <input type="email" name="email" required placeholder="e.g. 25cs047@charusat.edu.in" style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Phone Number</label>
                <input type="text" name="phone" placeholder="e.g. 9876543210" style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Course / Department</label>
                <input type="text" name="course" value="Computer Science" placeholder="e.g. Information Technology" style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Account Status</label>
                <select name="status" style="width:100%;padding:9px;border:1px solid #B9D2DF;border-radius:6px;">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <div style="text-align:right;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeAddModal()" style="background:#E5E7EB;color:#374151;border:none;padding:9px 16px;border-radius:6px;cursor:pointer;">Cancel</button>
                <button type="submit" style="background:#245B73;color:white;border:none;padding:9px 20px;border-radius:6px;font-weight:bold;cursor:pointer;">Save Student</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Student Modal (Admin only) -->
<div id="editModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;padding:15px;box-sizing:border-box;">
    <div style="background:white;width:100%;max-width:500px;border-radius:10px;padding:25px 30px;box-shadow:0 10px 25px rgba(0,0,0,0.3);position:relative;">
        <button onclick="closeEditModal()" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#666;">&times;</button>
        <h3 style="color:#245B73;margin-top:0;">Edit Student Record</h3>
        
        <form method="POST" action="students.php">
            <input type="hidden" name="edit_student" value="1">
            <input type="hidden" name="student_id" id="edit_id">

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Full Name *</label>
                <input type="text" name="name" id="edit_name" required style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Email Address *</label>
                <input type="email" name="email" id="edit_email" required style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Phone Number</label>
                <input type="text" name="phone" id="edit_phone" style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Course / Department</label>
                <input type="text" name="course" id="edit_course" style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Account Status</label>
                <select name="status" id="edit_status" style="width:100%;padding:9px;border:1px solid #B9D2DF;border-radius:6px;">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <div style="text-align:right;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeEditModal()" style="background:#E5E7EB;color:#374151;border:none;padding:9px 16px;border-radius:6px;cursor:pointer;">Cancel</button>
                <button type="submit" style="background:#245B73;color:white;border:none;padding:9px 20px;border-radius:6px;font-weight:bold;cursor:pointer;">Update Record</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- JavaScript for Modals & Debounced Live AJAX (Practical 13) -->
<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function openEditModal(student) {
    document.getElementById('edit_id').value = student.id;
    document.getElementById('edit_name').value = student.name;
    document.getElementById('edit_email').value = student.email;
    document.getElementById('edit_phone').value = student.phone || '';
    document.getElementById('edit_course').value = student.course || '';
    document.getElementById('edit_status').value = student.status || 'Active';
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function applyFilters() {
    var search = document.getElementById('liveSearchInput').value;
    var course = document.getElementById('courseFilterSelect').value;
    window.location.href = 'students.php?search=' + encodeURIComponent(search) + '&course=' + encodeURIComponent(course);
}

// Practical 13: Debounced Live AJAX Search (350ms)
var debounceTimer = null;
var isUserAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;

document.getElementById('liveSearchInput').addEventListener('input', function(e) {
    clearTimeout(debounceTimer);
    var query = e.target.value.trim();
    var course = document.getElementById('courseFilterSelect').value;

    debounceTimer = setTimeout(function() {
        fetch('api.php?action=search&q=' + encodeURIComponent(query) + '&course=' + encodeURIComponent(course))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var tbody = document.getElementById('studentTableBody');
                if (!data || !data.students || data.students.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="' + (isUserAdmin ? 7 : 6) + '" style="text-align:center;padding:30px;color:#888;">No students matching your search.</td></tr>';
                    return;
                }
                var html = '';
                data.students.forEach(function(s) {
                    var statusBadge = (s.status === 'Active') 
                        ? '<span style="background:#DEF7EC;color:#03543F;padding:3px 8px;border-radius:12px;font-size:11px;font-weight:bold;">Active</span>'
                        : '<span style="background:#FDE8E8;color:#9B1C1C;padding:3px 8px;border-radius:12px;font-size:11px;font-weight:bold;">Inactive</span>';

                    var adminActions = '';
                    if (isUserAdmin) {
                        var studentJson = JSON.stringify(s).replace(/"/g, '&quot;');
                        adminActions = '<td style="padding:11px;text-align:center;white-space:nowrap;">' +
                            '<button type="button" onclick="openEditModal(' + studentJson + ')" style="background:#348599;color:white;border:none;padding:5px 10px;border-radius:4px;font-size:12px;cursor:pointer;font-weight:600;">Edit</button> ' +
                            '<a href="students.php?delete=' + s.id + '" onclick="return confirm(\'Soft delete this student?\')" style="background:#C05A6F;color:white;text-decoration:none;padding:5px 10px;border-radius:4px;font-size:12px;font-weight:600;margin-left:5px;">Delete</a>' +
                            '</td>';
                    }

                    html += '<tr style="border-bottom:1px solid #E1EDF2;">' +
                        '<td style="padding:11px;font-weight:bold;color:#245B73;">' + s.id + '</td>' +
                        '<td style="padding:11px;font-weight:600;">' + escapeHtml(s.name) + '</td>' +
                        '<td style="padding:11px;color:#555;">' + escapeHtml(s.email) + '</td>' +
                        '<td style="padding:11px;color:#555;">' + escapeHtml(s.phone || 'N/A') + '</td>' +
                        '<td style="padding:11px;"><span style="background:#E1F0F5;color:#1B546B;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:600;">' + escapeHtml(s.course || 'General') + '</span></td>' +
                        '<td style="padding:11px;">' + statusBadge + '</td>' +
                        adminActions +
                        '</tr>';
                });
                tbody.innerHTML = html;
            })
            .catch(function(err) {
                console.error("AJAX error:", err);
            });
    }, 350);
});

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
