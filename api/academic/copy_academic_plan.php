<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$from_year = $data['from_year'] ?? '';
$to_year = $data['to_year'] ?? '';
$school_id = $_SESSION['school_id'];

if (empty($from_year) || empty($to_year)) {
    echo json_encode(['error' => 'ข้อมูลปีการศึกษาไม่ครบถ้วน']);
    exit;
}

try {
    // 1. ตรวจสอบว่าปีปลายทางมีข้อมูลมอบหมายงานสอนหรือยัง
    $check = $pdo->prepare("SELECT COUNT(*) FROM teacher_assignments WHERE academic_year = ? AND subject_id IN (SELECT id FROM subjects WHERE school_id = ?)");
    $check->execute([$to_year, $school_id]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['error' => 'มีข้อมูลแผนการจัดตารางสอนในปีการศึกษาปลายทางแล้ว กรุณาลบข้อมูลเดิมก่อนหากต้องการคัดลอกใหม่']);
        exit;
    }

    // 2. ดึงข้อมูลมอบหมายงานสอนจากปีการศึกษาเดิม
    $stmt = $pdo->prepare("
        SELECT ta.* FROM teacher_assignments ta
        JOIN subjects s ON ta.subject_id = s.id
        WHERE s.school_id = ? AND ta.academic_year = ?
    ");
    $stmt->execute([$school_id, $from_year]);
    $old_assignments = $stmt->fetchAll();

    if (count($old_assignments) === 0) {
        echo json_encode(['error' => 'ไม่พบข้อมูลแผนการจัดตารางสอนในปีการศึกษาต้นทาง']);
        exit;
    }

    $pdo->beginTransaction();
    $copied_count = 0;

    foreach ($old_assignments as $ta) {
        // คัดลอกไปยังปีใหม่
        $ins = $pdo->prepare("
            INSERT INTO teacher_assignments (teacher_id, subject_id, classroom_id, academic_year, semester)
            VALUES (?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $ta['teacher_id'],
            $ta['subject_id'],
            $ta['classroom_id'],
            $to_year,
            $ta['semester']
        ]);
        $copied_count++;
    }

    $pdo->commit();

    echo json_encode([
        'message' => "ดำเนินการคัดลอกแผนการจัดตารางสอนเรียบร้อยแล้ว $copied_count รายการ"
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
