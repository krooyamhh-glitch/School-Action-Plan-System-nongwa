<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$level = $data['level'] ?? '';
$generation = $data['generation'] ?? '';
$school_id = $_SESSION['school_id'];

if (empty($level) || empty($generation)) {
    echo json_encode(['error' => 'กรุณาระบุระดับชั้นและรุ่นที่จบการศึกษา']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE students SET status = "graduated", generation = ? WHERE school_id = ? AND level = ? AND status = "studying"');
    $stmt->execute([$generation, $school_id, $level]);
    $count = $stmt->rowCount();
    echo json_encode(['message' => "บันทึกการจบการศึกษาสำเร็จ จำนวน $count คน"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถบันทึกการจบการศึกษาได้: ' . $e->getMessage()]);
}
?>
