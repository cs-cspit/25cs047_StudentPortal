<?php
/**
 * StudentHub RESTful JSON API
 * ITUE203 - Practical 13 Implementation
 * Developer: Dhruvi Mokani (25CS047)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/db.php';

$action = $_GET['action'] ?? '';

if ($action === 'search' || $action === 'get_all') {
    $q = trim($_GET['q'] ?? '');
    $course = trim($_GET['course'] ?? '');

    $sql = "SELECT id, name, email, phone, course, status FROM students WHERE is_deleted = 0";
    $params = [];
    $types = "";

    if (!empty($q)) {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $like = "%$q%";
        $params[] = $like; $params[] = $like; $params[] = $like;
        $types .= "sss";
    }

    if (!empty($course) && $course !== 'All') {
        $sql .= " AND course = ?";
        $params[] = $course;
        $types .= "s";
    }

    $sql .= " ORDER BY id DESC LIMIT 50";

    $stmt = mysqli_prepare($conn, $sql);
    if ($types) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $students = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $students[] = $row;
    }
    mysqli_stmt_close($stmt);

    echo json_encode(['status' => 'success', 'count' => count($students), 'students' => $students]);
    exit;
}

if ($action === 'get_student') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, phone, course, status FROM students WHERE id = ? AND is_deleted = 0");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($student) {
        echo json_encode(['status' => 'success', 'student' => $student]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    }
    exit;
}

// Default response
echo json_encode([
    'status' => 'ok',
    'app' => 'StudentHub Unified API',
    'version' => '1.0',
    'endpoints' => [
        'GET ?action=search&q={query}&course={course}',
        'GET ?action=get_all',
        'GET ?action=get_student&id={id}'
    ]
]);
exit;
