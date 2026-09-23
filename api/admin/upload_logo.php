<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

if (!isset($_FILES['logo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ไม่พบไฟล์ที่อัปโหลด']);
    exit;
}

$file = $_FILES['logo'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 500 * 1024; // 500KB

if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'อนุญาตเฉพาะไฟล์รูปภาพ (JPG, PNG, GIF, WEBP)']);
    exit;
}

if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['error' => 'ขนาดไฟล์ต้องไม่เกิน 500KB']);
    exit;
}

$upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/logos/';
// Fallback if DOCUMENT_ROOT is not set or weird
if (empty($_SERVER['DOCUMENT_ROOT']) || !is_dir($_SERVER['DOCUMENT_ROOT'])) {
    $upload_dir = __DIR__ . '/../../uploads/logos/';
}

if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'ไม่สามารถสร้างโฟลเดอร์สำหรับเก็บไฟล์ได้', 'path' => $upload_dir]);
        exit;
    }
}

// Try to ensure it's writable
chmod($upload_dir, 0777);

if (!is_writable($upload_dir)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'โฟลเดอร์สำหรับเก็บไฟล์ไม่มีสิทธิ์ในการเขียน (Permission Denied)',
        'path' => $upload_dir,
        'user' => function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'unknown'
    ]);
    exit;
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$school_id = $_SESSION['school_id'] ?? 'default';
$filename = 'logo_' . $school_id . '_' . time() . '.' . $extension;
$target_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    chmod($target_path, 0666);
    // Return the relative path for the frontend
    $relative_path = 'uploads/logos/' . $filename;
    echo json_encode([
        'status' => 'success',
        'url' => $relative_path
    ]);
} else {
    $error = error_get_last();
    http_response_code(500);
    echo json_encode([
        'error' => 'เกิดข้อผิดพลาดในการบันทึกไฟล์',
        'debug' => $error ? $error['message'] : 'Unknown error',
        'path' => $target_path,
        'tmp_name' => $file['tmp_name']
    ]);
}
?>
