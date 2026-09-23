<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$current_role = $_SESSION['role'];
$current_school_id = $_SESSION['school_id'];

// รับ school_id จาก GET หรือใช้จาก Session ถ้าเป็น Admin
$school_id = $_GET['school_id'] ?? '';
if (empty($school_id) && $current_role === 'admin') {
    $school_id = $current_school_id;
}

if (empty($school_id)) {
    echo json_encode(['error' => 'ไม่พบรหัสโรงเรียน']);
    exit;
}

// ตรวจสอบสิทธิ์: Super Admin ดูได้ทุกที่, Admin ดูได้เฉพาะโรงเรียนตัวเอง
if ($current_role !== 'super_admin' && (string)$school_id !== (string)$current_school_id) {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงข้อมูลโรงเรียนนี้']);
    exit;
}

try {
    $query = 'SELECT u.id, u.username, u.name, u.last_name, u.position, u.role, u.is_approved, u.is_academic,
                     (SELECT GROUP_CONCAT(DISTINCT CONCAT(c.level, \'/\', c.room) SEPARATOR \', \') 
                      FROM classrooms c 
                      LEFT JOIN learner_development_assignments lda ON c.id = lda.classroom_id
                      WHERE c.teacher_id_1 = u.id OR c.teacher_id_2 = u.id OR lda.teacher_id = u.id) as homeroom_classrooms
              FROM users u
              WHERE u.school_id = ?';
    
    // สำหรับ Admin โรงเรียน ให้เห็นทุกคนในโรงเรียนตัวเองเพื่อจัดการข้อมูลได้ (ทั้งที่อนุมัติแล้วและยังไม่ได้รับอนุมัติ)
    // แต่ถ้าเป็น Super Admin ให้ดูได้ทุกคนตามรหัสโรงเรียนที่ส่งมา
    
    $query .= ' ORDER BY u.name ASC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$school_id]);
    $teachers = $stmt->fetchAll();
    echo json_encode($teachers);
} catch (PDOException $e) {
    http_response_code(500);
    $error_message = $e->getMessage();
    if (strpos($error_message, 'Unknown column \'is_academic\'') !== false) {
        $error_message .= " (กรุณาแจ้ง Super Admin ให้ทำการปรับปรุงฐานข้อมูล)";
    }
    echo json_encode(['error' => 'ไม่สามารถดึงข้อมูลคุณครูได้: ' . $error_message]);
}
?>
