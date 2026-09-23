<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$records = $data['records'] ?? [];
$check_date = $data['check_date'] ?? date('Y-m-d');
$academic_year = $data['academic_year'] ?? '';
$semester = $data['semester'] ?? '';
$teacher_id = $_SESSION['user_id'];

if (empty($records)) {
    echo json_encode(['error' => 'ไม่มีข้อมูลบันทึก']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO student_behavior_records 
        (student_id, category_id, behavior_text, check_date, academic_year, semester, teacher_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        behavior_text = VALUES(behavior_text),
        teacher_id = VALUES(teacher_id),
        updated_at = CURRENT_TIMESTAMP
    ");

    foreach ($records as $record) {
        $stmt->execute([
            $record['student_id'],
            $record['category_id'],
            $record['behavior_text'],
            $check_date,
            $academic_year,
            $semester,
            $teacher_id
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
