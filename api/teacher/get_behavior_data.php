<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$classroom_id = $_GET['classroom_id'] ?? '';
$academic_year = $_GET['academic_year'] ?? '2567';
$check_date = $_GET['check_date'] ?? date('Y-m-d');

if (empty($classroom_id)) {
    echo json_encode(['error' => 'กรุณาระบุห้องเรียน']);
    exit;
}

try {
    // ดึงรายชื่อนักเรียนในห้อง (ปีปัจจุบัน)
    $stmt = $pdo->prepare("
        SELECT s.id, s.student_code,
               IFNULL(sp.prefix, s.prefix) AS prefix, 
               IFNULL(sp.name, s.name) AS name, 
               IFNULL(sp.last_name, s.last_name) AS last_name 
        FROM students s
        LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id
        WHERE s.classroom_id = ? AND s.academic_year = ? 
        AND (s.status = 'studying' OR s.status IS NULL OR s.status = '')
        ORDER BY s.student_code ASC
    ");
    $stmt->execute([$classroom_id, $academic_year]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลบันทึกพฤติกรรม (กรองตามปีการศึกษาด้วย)
    $stmt = $pdo->prepare("
        SELECT r.* 
        FROM student_behavior_records r
        JOIN students s ON r.student_id = s.id
        WHERE s.classroom_id = ? AND r.check_date = ? AND r.academic_year = ?
    ");
    $stmt->execute([$classroom_id, $check_date, $academic_year]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'students' => $students,
        'records' => $records
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
