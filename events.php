<?php
session_start();
require_once __DIR__ . '/config/db.php';

$page_title = "Campus Events & Management | Student Hub";
$active_page = "events";

$is_logged_in = isset($_SESSION["user_id"]);
$user_id      = $is_logged_in ? (int)$_SESSION["user_id"] : 0;
$is_admin     = (($_SESSION["role"] ?? '') === 'admin');

$msg = "";
$err = "";

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}

// 1. Handle Event Registration (Student Action)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register_event"])) {
    if (!$is_logged_in) {
        header("Location: login.php?error=" . urlencode("Please log in to register for campus events."));
        exit;
    }

    $event_id = (int)($_POST["event_id"] ?? 0);
    if ($event_id > 0) {
        $chk = mysqli_prepare($conn, "SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
        mysqli_stmt_bind_param($chk, "ii", $user_id, $event_id);
        mysqli_stmt_execute($chk);
        mysqli_stmt_store_result($chk);

        if (mysqli_stmt_num_rows($chk) > 0) {
            $err = "You have already registered for this event!";
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO registrations (user_id, event_id, status) VALUES (?, ?, 'Confirmed')");
            mysqli_stmt_bind_param($ins, "ii", $user_id, $event_id);
            if (mysqli_stmt_execute($ins)) {
                $msg = "Congratulations! You have successfully registered for the event.";
                log_audit_trail($conn, $user_id, "Event Registration", "Registered for event ID #$event_id");
            } else {
                $err = "Registration failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($chk);
    }
}

// 2. Handle Add New Event with Poster Upload (Admin Action - Practical 12)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_event"])) {
    if (!$is_admin) {
        $err = "Unauthorized action. Admin privileges required.";
    } else {
        $title       = trim($_POST["title"] ?? "");
        $event_date  = trim($_POST["event_date"] ?? "");
        $location    = trim($_POST["location"] ?? "");
        $category    = trim($_POST["category"] ?? "Technical");
        $status      = trim($_POST["status"] ?? "Open");
        $description = trim($_POST["description"] ?? "");
        $poster_name = null;

        if (empty($title) || empty($event_date) || empty($location)) {
            $err = "Title, Event Date, and Venue / Location are required.";
        } else {
            // Poster upload validation
            if (!empty($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
                $f = $_FILES['poster'];
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (!in_array($ext, $allowed, true)) {
                    $err = "Invalid poster image format. Allowed: JPG, PNG, WEBP.";
                } elseif ($f['size'] > 2 * 1024 * 1024) {
                    $err = "Poster image size exceeds 2MB limit.";
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $f['tmp_name']);
                    finfo_close($finfo);

                    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                        $err = "Uploaded file is not a valid genuine image.";
                    } else {
                        $poster_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (!move_uploaded_file($f['tmp_name'], $upload_dir . $poster_name)) {
                            $err = "Failed to save poster image to uploads directory.";
                            $poster_name = null;
                        }
                    }
                }
            }

            if (empty($err)) {
                $stmt = mysqli_prepare($conn, "INSERT INTO events (title, description, event_date, location, category, status, poster) VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssssss", $title, $description, $event_date, $location, $category, $status, $poster_name);
                if (mysqli_stmt_execute($stmt)) {
                    $new_eid = mysqli_insert_id($conn);
                    $msg = "New event #$new_eid added successfully!";
                    log_audit_trail($conn, $user_id, "Create Event", "Created event '$title' with poster ($poster_name)");
                } else {
                    $err = "Failed to create event: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// 3. Handle Edit Event (Admin Action)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_event"])) {
    if (!$is_admin) {
        $err = "Unauthorized action. Admin privileges required.";
    } else {
        $eid         = (int)($_POST["event_id"] ?? 0);
        $title       = trim($_POST["title"] ?? "");
        $event_date  = trim($_POST["event_date"] ?? "");
        $location    = trim($_POST["location"] ?? "");
        $category    = trim($_POST["category"] ?? "Technical");
        $status      = trim($_POST["status"] ?? "Open");
        $description = trim($_POST["description"] ?? "");

        if ($eid > 0 && !empty($title) && !empty($event_date)) {
            // Check for optional poster replacement
            $poster_update_sql = "";
            $new_poster = null;
            if (!empty($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
                $f = $_FILES['poster'];
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) && $f['size'] <= 2 * 1024 * 1024) {
                    $new_poster = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (move_uploaded_file($f['tmp_name'], $upload_dir . $new_poster)) {
                        $poster_update_sql = ", poster = '$new_poster'";
                    }
                }
            }

            $stmt = mysqli_prepare($conn, "UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, category = ?, status = ? $poster_update_sql WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssssssi", $title, $description, $event_date, $location, $category, $status, $eid);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Event #$eid updated successfully!";
                log_audit_trail($conn, $user_id, "Edit Event", "Updated event #$eid ($title)");
            } else {
                $err = "Failed to update event: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// 4. Handle Delete Event (Admin Action - cleans up poster file)
if (isset($_GET["delete"])) {
    if (!$is_admin) {
        $err = "Unauthorized action.";
    } else {
        $del_eid = (int)$_GET["delete"];
        if ($del_eid > 0) {
            // Find poster to unlink
            $p_q = mysqli_query($conn, "SELECT poster FROM events WHERE id = $del_eid");
            if ($p_q && $p_row = mysqli_fetch_assoc($p_q)) {
                if (!empty($p_row['poster']) && is_file($upload_dir . $p_row['poster'])) {
                    @unlink($upload_dir . $p_row['poster']);
                }
            }
            $stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $del_eid);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Event #$del_eid and associated poster deleted successfully.";
                log_audit_trail($conn, $user_id, "Delete Event", "Deleted event #$del_eid");
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// 5. Handle Toggle Status (Admin Action)
if (isset($_GET["toggle_status"])) {
    if (!$is_admin) {
        $err = "Unauthorized action.";
    } else {
        $t_eid = (int)$_GET["toggle_status"];
        if ($t_eid > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE events SET status = IF(status = 'Open', 'Closed', 'Open') WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $t_eid);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Event #$t_eid status toggled successfully.";
                log_audit_trail($conn, $user_id, "Toggle Status", "Toggled status for event #$t_eid");
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fetch events from database with search, category, and sort filters
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['cat'] ?? 'All');
$sort     = trim($_GET['sort'] ?? 'dateAsc');

$sql = "SELECT * FROM events WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR location LIKE ? OR description LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}
if (!empty($category) && $category !== 'All') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}

if ($sort === 'dateDesc') {
    $sql .= " ORDER BY event_date DESC";
} elseif ($sort === 'name') {
    $sql .= " ORDER BY title ASC";
} else {
    $sql .= " ORDER BY event_date ASC";
}

$stmt = mysqli_prepare($conn, $sql);
if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$events_res = mysqli_stmt_get_result($stmt);
$events = [];
while ($e = mysqli_fetch_assoc($events_res)) {
    $events[] = $e;
}

// Fetch list of event IDs registered by current user
$user_registered_ids = [];
if ($is_logged_in) {
    $u_regs = mysqli_query($conn, "SELECT event_id FROM registrations WHERE user_id = $user_id");
    if ($u_regs) {
        while ($ur = mysqli_fetch_assoc($u_regs)) {
            $user_registered_ids[] = (int)$ur['event_id'];
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main style="max-width:1200px;margin:30px auto;padding:0 15px;flex:1;font-family:'Segoe UI',sans-serif;">

    <div style="background:white;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(36,91,115,0.1);margin-bottom:25px;">
        
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;border-bottom:1px solid #E1EDF2;padding-bottom:15px;margin-bottom:20px;">
            <div>
                <h2 style="color:#245B73;margin:0 0 5px;">Campus Events &amp; Hackathons</h2>
                <p style="margin:0;color:#666;font-size:14px;">Technical summits, code marathons, sports meets, and cultural festivals.</p>
            </div>

            <?php if ($is_admin): ?>
                <button type="button" onclick="openAddEventModal()" style="background:#245B73;color:white;border:none;padding:10px 18px;border-radius:6px;font-weight:bold;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                    + Create New Event &amp; Upload Poster
                </button>
            <?php endif; ?>
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

        <!-- Search, Filter and Sort Controls -->
        <form method="GET" action="events.php" style="display:flex;gap:12px;flex-wrap:wrap;background:#F4F9FB;padding:15px;border-radius:8px;border:1px solid #D5E5ED;margin-bottom:25px;align-items:center;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search events by title, venue, or details..." style="flex:2;min-width:220px;padding:10px 12px;border:1px solid #B9D2DF;border-radius:6px;font-size:14px;">

            <select name="cat" style="flex:1;min-width:160px;padding:10px;border:1px solid #B9D2DF;border-radius:6px;font-size:14px;">
                <option value="All" <?php if ($category === 'All') echo 'selected'; ?>>All Categories</option>
                <option value="Technical" <?php if ($category === 'Technical') echo 'selected'; ?>>Technical</option>
                <option value="Workshop" <?php if ($category === 'Workshop') echo 'selected'; ?>>Workshop</option>
                <option value="Sports" <?php if ($category === 'Sports') echo 'selected'; ?>>Sports</option>
                <option value="Cultural" <?php if ($category === 'Cultural') echo 'selected'; ?>>Cultural</option>
                <option value="Conference" <?php if ($category === 'Conference') echo 'selected'; ?>>Conference</option>
            </select>

            <select name="sort" style="flex:1;min-width:160px;padding:10px;border:1px solid #B9D2DF;border-radius:6px;font-size:14px;">
                <option value="dateAsc" <?php if ($sort === 'dateAsc') echo 'selected'; ?>>Date: Earliest First</option>
                <option value="dateDesc" <?php if ($sort === 'dateDesc') echo 'selected'; ?>>Date: Latest First</option>
                <option value="name" <?php if ($sort === 'name') echo 'selected'; ?>>Name: A to Z</option>
            </select>

            <button type="submit" style="background:#245B73;color:white;border:none;padding:10px 20px;border-radius:6px;font-weight:bold;cursor:pointer;">
                Filter
            </button>
            <a href="events.php" style="color:#666;font-size:13px;text-decoration:none;">Reset</a>
        </form>

        <!-- Events Grid -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:22px;">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $ev): ?>
                    <?php 
                    $is_registered = in_array((int)$ev['id'], $user_registered_ids, true);
                    $is_open = ($ev['status'] === 'Open');
                    
                    // Determine poster image
                    $poster_url = "Photo/student3.png";
                    if (!empty($ev['poster'])) {
                        if (file_exists(__DIR__ . '/uploads/' . $ev['poster'])) {
                            $poster_url = 'uploads/' . $ev['poster'];
                        } elseif (file_exists(__DIR__ . '/Photo/' . $ev['poster'])) {
                            $poster_url = 'Photo/' . $ev['poster'];
                        }
                    }
                    ?>
                    <div style="border:1px solid #D5E5ED;border-radius:10px;overflow:hidden;background:#FAFCFD;display:flex;flex-direction:column;box-shadow:0 2px 8px rgba(0,0,0,0.06);transition:transform 0.2s;">
                        <div style="height:170px;background:#1B4557;overflow:hidden;position:relative;">
                            <img src="<?php echo htmlspecialchars($poster_url); ?>" alt="<?php echo htmlspecialchars($ev['title']); ?>" style="width:100%;height:100%;object-fit:cover;">
                            <span style="position:absolute;top:12px;right:12px;background:<?php echo $is_open ? '#257E66' : '#C05A6F'; ?>;color:white;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:bold;">
                                <?php echo htmlspecialchars($ev['status']); ?>
                            </span>
                        </div>

                        <div style="padding:20px;flex:1;display:flex;flex-direction:column;">
                            <span style="background:#E1F0F5;color:#1F546B;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:bold;align-self:flex-start;">
                                <?php echo htmlspecialchars($ev['category']); ?>
                            </span>

                            <h3 style="color:#245B73;margin:10px 0 6px;font-size:18px;">
                                <?php echo htmlspecialchars($ev['title']); ?>
                            </h3>

                            <div style="font-size:13px;color:#555;margin-bottom:10px;line-height:1.5;">
                                <div>&#128197; <strong>Date:</strong> <?php echo htmlspecialchars($ev['event_date']); ?></div>
                                <div>&#128205; <strong>Venue:</strong> <?php echo htmlspecialchars($ev['location'] ?? $ev['venue'] ?? 'Campus Auditorium'); ?></div>
                            </div>

                            <p style="font-size:13px;color:#666;line-height:1.5;margin:0 0 15px;flex:1;">
                                <?php echo htmlspecialchars($ev['description'] ?? 'Campus event organized by Faculty of Technology & Engineering.'); ?>
                            </p>

                            <!-- Actions Row -->
                            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <button type="button" onclick="openEventModal(<?php echo htmlspecialchars(json_encode($ev)); ?>)" style="background:#F0F7FA;border:1px solid #76B6C9;color:#245B73;padding:7px 12px;border-radius:6px;font-weight:bold;font-size:12px;cursor:pointer;">
                                    Details
                                </button>

                                <?php if ($is_open): ?>
                                    <?php if ($is_registered): ?>
                                        <button disabled style="background:#DEF7EC;color:#03543F;border:1px solid #31C48D;padding:7px 12px;border-radius:6px;font-weight:bold;font-size:12px;cursor:default;">
                                            &check; Registered
                                        </button>
                                    <?php else: ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="event_id" value="<?php echo $ev['id']; ?>">
                                            <button type="submit" name="register_event" style="background:#257E66;color:white;border:none;padding:7px 14px;border-radius:6px;font-weight:bold;font-size:12px;cursor:pointer;">
                                                Register Now
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button disabled style="background:#E5E7EB;color:#6B7280;border:none;padding:7px 12px;border-radius:6px;font-size:12px;cursor:not-allowed;">
                                        Closed
                                    </button>
                                <?php endif; ?>

                                <?php if ($is_admin): ?>
                                    <!-- In-page Admin Controls -->
                                    <button type="button" onclick="openEditEventModal(<?php echo htmlspecialchars(json_encode($ev)); ?>)" style="background:#348599;color:white;border:none;padding:7px 10px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer;">
                                        Edit
                                    </button>
                                    <a href="events.php?toggle_status=<?php echo $ev['id']; ?>" style="background:#F39C12;color:white;text-decoration:none;padding:7px 10px;border-radius:6px;font-size:12px;font-weight:bold;">
                                        Toggle
                                    </a>
                                    <a href="events.php?delete=<?php echo $ev['id']; ?>" onclick="return confirm('Delete event #<?php echo $ev['id']; ?> and unlink its poster?')" style="background:#C05A6F;color:white;text-decoration:none;padding:7px 10px;border-radius:6px;font-size:12px;font-weight:bold;">
                                        Delete
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column:1/-1;text-align:center;padding:40px;color:#888;">
                    No events found matching your search and category filter.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Event Detail Modal -->
<div id="eventDetailModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:10000;align-items:center;justify-content:center;padding:15px;box-sizing:border-box;">
    <div style="background:white;width:100%;max-width:550px;border-radius:10px;padding:30px;box-shadow:0 10px 25px rgba(0,0,0,0.3);position:relative;">
        <button onclick="closeEventModal()" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#666;">&times;</button>
        <h3 id="modalTitle" style="color:#245B73;margin-top:0;"></h3>
        <p style="font-size:13px;color:#555;"><strong>Date:</strong> <span id="modalDate"></span></p>
        <p style="font-size:13px;color:#555;"><strong>Venue:</strong> <span id="modalVenue"></span></p>
        <p style="font-size:13px;color:#555;"><strong>Category:</strong> <span id="modalCat"></span></p>
        <p id="modalDesc" style="font-size:14px;color:#444;line-height:1.6;margin:15px 0 20px;"></p>
        
        <div style="text-align:right;">
            <button onclick="closeEventModal()" style="background:#245B73;color:white;border:none;padding:8px 18px;border-radius:6px;font-weight:bold;cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<!-- Admin: Add Event Modal with Poster Upload -->
<?php if ($is_admin): ?>
<div id="addEventModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:10000;align-items:center;justify-content:center;padding:15px;box-sizing:border-box;">
    <div style="background:white;width:100%;max-width:540px;border-radius:10px;padding:25px 30px;box-shadow:0 10px 25px rgba(0,0,0,0.3);position:relative;max-height:90vh;overflow-y:auto;">
        <button onclick="closeAddEventModal()" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#666;">&times;</button>
        <h3 style="color:#245B73;margin-top:0;">Create Campus Event &amp; Upload Poster</h3>
        
        <form method="POST" action="events.php" enctype="multipart/form-data">
            <input type="hidden" name="add_event" value="1">

            <div style="margin-bottom:12px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Event Title *</label>
                <input type="text" name="title" required placeholder="e.g. HackCHARUSAT 2026" style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Event Date *</label>
                    <input type="date" name="event_date" required style="width:100%;padding:9px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Category *</label>
                    <select name="category" style="width:100%;padding:9px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                        <option value="Technical">Technical</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Sports">Sports</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Conference">Conference</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Venue / Location *</label>
                <input type="text" name="location" required placeholder="e.g. CSPIT Auditorium &amp; Labs" style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Description</label>
                <textarea name="description" rows="3" placeholder="Details about agenda, eligibility, prizes, and schedule..." style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;"></textarea>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Poster Image (JPG, PNG, WEBP &le; 2MB)</label>
                <input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp" style="width:100%;padding:6px;border:1px solid #B9D2DF;border-radius:6px;font-size:13px;">
                <small style="color:#666;font-size:11px;">Validated with server-side MIME verification and unique secure hashing.</small>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Registration Status</label>
                <select name="status" style="width:100%;padding:9px;border:1px solid #B9D2DF;border-radius:6px;">
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>

            <div style="text-align:right;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeAddEventModal()" style="background:#E5E7EB;color:#374151;border:none;padding:9px 16px;border-radius:6px;cursor:pointer;">Cancel</button>
                <button type="submit" style="background:#245B73;color:white;border:none;padding:9px 20px;border-radius:6px;font-weight:bold;cursor:pointer;">Save Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Admin: Edit Event Modal -->
<div id="editEventModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:10000;align-items:center;justify-content:center;padding:15px;box-sizing:border-box;">
    <div style="background:white;width:100%;max-width:540px;border-radius:10px;padding:25px 30px;box-shadow:0 10px 25px rgba(0,0,0,0.3);position:relative;max-height:90vh;overflow-y:auto;">
        <button onclick="closeEditEventModal()" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#666;">&times;</button>
        <h3 style="color:#245B73;margin-top:0;">Edit Campus Event</h3>
        
        <form method="POST" action="events.php" enctype="multipart/form-data">
            <input type="hidden" name="edit_event" value="1">
            <input type="hidden" name="event_id" id="edit_eid">

            <div style="margin-bottom:12px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Event Title *</label>
                <input type="text" name="title" id="edit_etitle" required style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Event Date *</label>
                    <input type="date" name="event_date" id="edit_edate" required style="width:100%;padding:9px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Category *</label>
                    <select name="category" id="edit_ecat" style="width:100%;padding:9px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
                        <option value="Technical">Technical</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Sports">Sports</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Conference">Conference</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Venue / Location *</label>
                <input type="text" name="location" id="edit_eloc" required style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Description</label>
                <textarea name="description" id="edit_edesc" rows="3" style="width:100%;padding:9px 12px;border:1px solid #B9D2DF;border-radius:6px;box-sizing:border-box;"></textarea>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Replace Poster Image (Optional, &le; 2MB)</label>
                <input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp" style="width:100%;padding:6px;border:1px solid #B9D2DF;border-radius:6px;font-size:13px;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:bold;color:#245B73;font-size:13px;margin-bottom:4px;">Registration Status</label>
                <select name="status" id="edit_estatus" style="width:100%;padding:9px;border:1px solid #B9D2DF;border-radius:6px;">
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>

            <div style="text-align:right;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeEditEventModal()" style="background:#E5E7EB;color:#374151;border:none;padding:9px 16px;border-radius:6px;cursor:pointer;">Cancel</button>
                <button type="submit" style="background:#245B73;color:white;border:none;padding:9px 20px;border-radius:6px;font-weight:bold;cursor:pointer;">Update Event</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openEventModal(eventObj) {
    document.getElementById('modalTitle').textContent = eventObj.title;
    document.getElementById('modalDate').textContent = eventObj.event_date;
    document.getElementById('modalVenue').textContent = eventObj.location || eventObj.venue;
    document.getElementById('modalCat').textContent = eventObj.category;
    document.getElementById('modalDesc').textContent = eventObj.description || 'Join us for this exciting campus event!';
    document.getElementById('eventDetailModal').style.display = 'flex';
}
function closeEventModal() {
    document.getElementById('eventDetailModal').style.display = 'none';
}

function openAddEventModal() {
    document.getElementById('addEventModal').style.display = 'flex';
}
function closeAddEventModal() {
    document.getElementById('addEventModal').style.display = 'none';
}

function openEditEventModal(ev) {
    document.getElementById('edit_eid').value = ev.id;
    document.getElementById('edit_etitle').value = ev.title;
    document.getElementById('edit_edate').value = ev.event_date;
    document.getElementById('edit_ecat').value = ev.category;
    document.getElementById('edit_eloc').value = ev.location || ev.venue || '';
    document.getElementById('edit_edesc').value = ev.description || '';
    document.getElementById('edit_estatus').value = ev.status || 'Open';
    document.getElementById('editEventModal').style.display = 'flex';
}
function closeEditEventModal() {
    document.getElementById('editEventModal').style.display = 'none';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
