<?php
session_start();
// Error reporting for debugging - remove in final version if needed
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

header('Content-Type: application/json');

// Check permission
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

// ปิดการใช้งานปุ่มล้างข้อมูลเพื่อความปลอดภัยตามคำขอของผู้ใช้งาน
echo json_encode(['error' => 'ฟังก์ชันนี้ถูกปิดการใช้งานเพื่อความปลอดภัยของข้อมูลนักเรียน หากต้องการเปิดใช้งานใหม่กรุณาติดต่อผู้พัฒนา']);
exit;

if ($_SESSION['role'] !== 'admin' && !($_SESSION['is_academic'] ?? false)) {
    http_response_code(403);
    echo json_encode(['error' => 'คุณไม่มีสิทธิ์ดำเนินการนี้']);
    exit;
}

$school_id = $_SESSION['school_id'] ?? null;
if (!$school_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ไม่พบข้อมูลโรงเรียน']);
    exit;
}

try {
    if (!isset($pdo)) {
        throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้ (PDO Object Missing)");
    }

    $pdo->beginTransaction();

    // ขั้นแรก: ค้นหา ID ของนักเรียนทั้งหมดในโรงเรียนนี้
    // การลบจากตาราง students ที่มี ON DELETE CASCADE จะครอบคลุมตารางส่วนใหญ่
    // แต่เพื่อความมั่นใจและรองรับตารางที่อาจจะไม่มี Foreign Key แบบ CASCADE
    // เราสามารถล้างตารางที่เรารู้จักก่อนได้
    
    // รายชื่อตารางที่มี student_id และเราต้องการล้าง
    $related_tables = [
        'attendance',
        'evaluation_scores',
        'grades',
        'student_behavior_records',
        'student_health_records',
        'learner_development_records',
        'learner_development_results',
        'competency_scores'
    ];

    foreach ($related_tables as $table) {
        $checkTable = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($checkTable) {
            $pdo->prepare("DELETE FROM $table WHERE student_id IN (SELECT id FROM students WHERE school_id = ?)")
                ->execute([$school_id]);
        }
    }

    // สุดท้าย ลบตัวนักเรียน
    $stmt = $pdo->prepare("DELETE FROM students WHERE school_id = ?");
    $stmt->execute([$school_id]);
    
    $pdo->commit();

    echo json_encode(['message' => 'ล้างข้อมูลนักเรียนและข้อมูลที่เกี่ยวข้องทั้งหมดเรียบร้อยแล้ว']);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

