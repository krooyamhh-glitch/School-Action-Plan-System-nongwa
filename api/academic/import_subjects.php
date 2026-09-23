<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// ต้องเป็น Admin หรือ งานวิชาการ
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$subjects = $data['subjects'] ?? [];
$school_id = $_SESSION['school_id'];

if (empty($subjects)) {
    echo json_encode(['error' => 'ไม่พบข้อมูลรายวิชาที่จะนำเข้า']);
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($subjects as $s) {
        $code = trim($s['code'] ?? '');
        $name = trim($s['name'] ?? '');
        $level = trim($s['level'] ?? '');
        $hours = intval($s['hours'] ?? 40);
        $credits = floatval($s['credits'] ?? 1.0);
        $learning_area = trim($s['learning_area'] ?? '');

        if (empty($code) || empty($name) || empty($level)) continue;

        // ตรวจสอบรายวิชาเดิม (จากรหัสวิชา และ ระดับชั้น ในโรงเรียนเดียวกัน)
        $stmt = $pdo->prepare('SELECT id FROM subjects WHERE school_id = ? AND code = ? AND level = ?');
        $stmt->execute([$school_id, $code, $level]);
        $existing = $stmt->fetch();

        if ($existing) {
            // อัปเดต
            $stmt = $pdo->prepare('UPDATE subjects SET name = ?, hours = ?, credits = ?, learning_area = ? WHERE id = ?');
            $stmt->execute([$name, $hours, $credits, $learning_area, $existing['id']]);
        } else {
            // เพิ่มใหม่
            $stmt = $pdo->prepare('INSERT INTO subjects (code, name, level, hours, credits, learning_area, school_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$code, $name, $level, $hours, $credits, $learning_area, $school_id]);
        }
    }

    $pdo->commit();
    echo json_encode(['message' => 'นำเข้าข้อมูลรายวิชาสำเร็จแล้ว']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถนำเข้าข้อมูลรายวิชาได้: ' . $e->getMessage()]);
}
?>
