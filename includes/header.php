<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = $page_title ?? 'Student Hub | Academic Portal';
$active_page = $active_page ?? 'home';

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Student';
$user_role = $_SESSION['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Modern Header & Responsive Nav */
        .portal-header {
            background: linear-gradient(135deg, #245B73, #4389A8, #76B6C9);
            color: white;
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: white;
        }
        .header-brand img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            background: white;
            padding: 4px;
            border-radius: 50%;
            margin: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .header-brand-text h1 {
            font-size: 1.5rem;
            margin: 0;
            line-height: 1.2;
            color: white;
            font-weight: 700;
        }
        .header-brand-text p {
            font-size: 0.82rem;
            margin: 0;
            color: #E2F1F8;
            letter-spacing: 0.3px;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-bar {
            background: #FFFFFF;
            padding: 8px 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(36,91,115,0.08);
            border-bottom: 1px solid #E1EDF2;
        }
        .nav-bar a {
            text-decoration: none;
            color: #245B73;
            font-weight: 600;
            padding: 7px 13px;
            border-radius: 6px;
            font-size: 13.5px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .nav-bar a:hover, .nav-bar a.active {
            background: #245B73;
            color: #FFFFFF;
        }
        .nav-bar a.btn-portal-login {
            background: #257E66;
            color: white;
        }
        .nav-bar a.btn-portal-login:hover {
            background: #1B5E4C;
        }
        .nav-bar a.btn-portal-logout {
            background: #C05A6F;
            color: white;
        }
        .nav-bar a.btn-portal-logout:hover {
            background: #A04054;
        }
        .user-tag {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .user-tag-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ADE80;
            display: inline-block;
        }
        @media (max-width: 900px) {
            .nav-bar {
                display: none;
                flex-direction: column;
                align-items: stretch;
            }
            .nav-bar.show {
                display: flex;
            }
            .nav-bar a {
                text-align: center;
                justify-content: center;
            }
            .menu-toggle-btn {
                display: block !important;
            }
        }
        .menu-toggle-btn {
            display: none;
            background: rgba(255,255,255,0.25);
            color: white;
            border: 1px solid rgba(255,255,255,0.4);
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- Main Portal Header -->
<header class="portal-header">
    <a href="index.php" class="header-brand">
        <img src="Photo/logo.png" alt="Student Hub Logo" onerror="this.src='Photo/studenthub.png';">
        <div class="header-brand-text">
            <h1>Student Hub</h1>
            <p>Unified Academic Management Portal &bull; CSPIT CHARUSAT</p>
        </div>
    </a>

    <div class="header-actions">
        <?php if ($is_logged_in): ?>
            <span class="user-tag">
                <span class="user-tag-dot"></span>
                <?php echo htmlspecialchars($user_name); ?> (<?php echo ucfirst($user_role); ?>)
            </span>
        <?php endif; ?>

        <button id="themeBtn" onclick="toggleTheme()" style="background:rgba(255,255,255,0.25);color:white;border:1px solid rgba(255,255,255,0.4);border-radius:6px;padding:6px 12px;cursor:pointer;font-size:12.5px;font-weight:bold;">
            &#9790; Theme
        </button>

        <button class="menu-toggle-btn" onclick="toggleMenu()" aria-label="Toggle navigation menu">&#9776;</button>
    </div>
</header>

<!-- Dynamic Navigation Bar: Single Unified Portal -->
<nav class="nav-bar" id="navbar">
    <a href="index.php" class="<?php echo ($active_page === 'home') ? 'active' : ''; ?>">Home</a>
    <a href="about.php" class="<?php echo ($active_page === 'about') ? 'active' : ''; ?>">About</a>
    <a href="events.php" class="<?php echo ($active_page === 'events') ? 'active' : ''; ?>">Campus Events</a>

    <?php if (!$is_logged_in): ?>
        <a href="announcement.php" class="<?php echo ($active_page === 'announcement') ? 'active' : ''; ?>">Announcements</a>
        <a href="faq.php" class="<?php echo ($active_page === 'faq') ? 'active' : ''; ?>">FAQ</a>
        <a href="contact.php" class="<?php echo ($active_page === 'contact') ? 'active' : ''; ?>">Contact</a>
        <a href="login.php" class="btn-portal-login <?php echo ($active_page === 'login') ? 'active' : ''; ?>">Student / Staff Login</a>
        <a href="registration.php" class="<?php echo ($active_page === 'register') ? 'active' : ''; ?>">Register</a>
    <?php else: ?>
        <a href="dashboard.php" class="<?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>"><strong>Dashboard</strong></a>

        <?php if ($user_role === 'admin'): ?>
            <!-- Unified Admin Navigation -->
            <a href="students.php" class="<?php echo ($active_page === 'students') ? 'active' : ''; ?>">Manage Students</a>
            <a href="events.php" class="<?php echo ($active_page === 'events') ? 'active' : ''; ?>">Manage Events</a>
            <a href="audit_logs.php" class="<?php echo ($active_page === 'audit_logs') ? 'active' : ''; ?>">Audit Logs</a>
        <?php else: ?>
            <!-- Unified Student Navigation -->
            <a href="profile.php" class="<?php echo ($active_page === 'profile') ? 'active' : ''; ?>">My Profile</a>
            <a href="attendance.php" class="<?php echo ($active_page === 'attendance') ? 'active' : ''; ?>">Attendance</a>
            <a href="timetable.php" class="<?php echo ($active_page === 'timetable') ? 'active' : ''; ?>">Timetable</a>
            <a href="assignment.php" class="<?php echo ($active_page === 'assignment') ? 'active' : ''; ?>">Assignments</a>
            <a href="result.php" class="<?php echo ($active_page === 'result') ? 'active' : ''; ?>">Results</a>
            <a href="fees.php" class="<?php echo ($active_page === 'fees') ? 'active' : ''; ?>">Fees</a>
            <a href="feedback.php" class="<?php echo ($active_page === 'feedback') ? 'active' : ''; ?>">Feedback</a>
        <?php endif; ?>

        <a href="announcement.php" class="<?php echo ($active_page === 'announcement') ? 'active' : ''; ?>">Notices</a>
        <a href="faq.php" class="<?php echo ($active_page === 'faq') ? 'active' : ''; ?>">FAQ</a>
        <a href="contact.php" class="<?php echo ($active_page === 'contact') ? 'active' : ''; ?>">Contact</a>
        <a href="logout.php" class="btn-portal-logout">Logout</a>
    <?php endif; ?>
</nav>
