<?php
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$school_code = $data['school_code'] ?? '';
$username = $data['username'] ?? ''; // เลขบัตรประชาชน
$password = $data['password'] ?? '';
$name = $data['name'] ?? '';
$last_name = $data['last_name'] ?? '';
$position = $data['position'] ?? '';

if (empty($school_code) || empty($username) || empty($password) || empty($name) || empty($last_name) || empty($position)) {
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

try {
    // 1. ตรวจสอบรหัสโรงเรียน 8 หลัก
    $stmt = $pdo->prepare('SELECT id FROM schools WHERE code = ?');
    $stmt->execute([$school_code]);
    $school = $stmt->fetch();

    if (!$school) {
        http_response_code(400);
        echo json_encode(['error' => 'ไม่พบรหัสโรงเรียน 8 หลักนี้ในระบบ กรุณาติดต่อ Super Admin']);
        exit;
    }

    // 2. ตรวจสอบว่ามีผู้ใช้นี้อยู่แล้วหรือไม่
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'เลขบัตรประชาชนนี้มีการสมัครใช้งานแล้ว']);
        exit;
    }

    // 3. บันทึกข้อมูลการสมัคร (รอการอนุมัติ)
    $stmt = $pdo->prepare('
        INSERT INTO users (username, password, name, last_name, school_id, position, is_approved, role) 
        VALUES (?, ?, ?, ?, ?, ?, FALSE, "teacher")
    ');
    $stmt->execute([$username, $password, $name, $last_name, $school['id'], $position]);

    echo json_encode(['message' => 'สมัครสมาชิกสำเร็จ กรุณารอการอนุมัติจาก Admin โรงเรียน หรือ Super Admin']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
