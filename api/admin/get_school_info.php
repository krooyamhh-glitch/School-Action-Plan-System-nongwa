<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $schoolId = !empty($_SESSION['school_id']) ? (int)$_SESSION['school_id'] : 1;
    $stmt = $pdo->prepare('SELECT * FROM schools WHERE id = ?');
    $stmt->execute([$schoolId]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$school) {
        $stmt2 = $pdo->query('SELECT * FROM schools LIMIT 1');
        $school = $stmt2->fetch(PDO::FETCH_ASSOC);
    }

    if (!$school) {
        // Auto-seed default school if table is empty
        $pdo->exec("INSERT INTO schools (id, code, smis_code, name, province, district, affiliation, director_name, director_position, plan_officer_name, status)
            VALUES (1, '10310001', '10310001', 'โรงเรียนบ้านหนองบัว', 'ขอนแก่น', 'เมืองขอนแก่น', 'สำนักงานคณะกรรมการการศึกษาขั้นพื้นฐาน', 'นายสมคิด จิตมั่น', 'ผู้อำนวยการโรงเรียน', 'นางสาวพิมพ์ใจ แผนงาน', 'active')
            ON DUPLICATE KEY UPDATE name=VALUES(name)");
        $stmt3 = $pdo->query('SELECT * FROM schools WHERE id = 1');
        $school = $stmt3->fetch(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'status' => 'success',
        'school' => $school
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
