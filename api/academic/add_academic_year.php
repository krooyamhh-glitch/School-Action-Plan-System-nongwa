<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$year = $data['year'] ?? '';
$school_id = $_SESSION['school_id'];

if (empty($year) || !preg_match('/^\d{4}$/', $year)) {
    echo json_encode(['error' => 'กรุณาระบุปีการศึกษาให้ถูกต้อง (เช่น 2567)']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO academic_years (school_id, year) VALUES (?, ?)');
    $stmt->execute([$school_id, $year]);
    echo json_encode(['message' => 'เพิ่มปีการศึกษาสำเร็จ']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['error' => 'ปีการศึกษานี้มีอยู่แล้ว']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'ไม่สามารถเพิ่มปีการศึกษาได้: ' . $e->getMessage()]);
    }
}
?>
