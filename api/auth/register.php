<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$smis_code = trim($data['smis_code'] ?? '');
$id_card = trim($data['id_card'] ?? '');
$name = trim($data['name'] ?? '');
$position = trim($data['position'] ?? 'ครู');
$department = trim($data['department'] ?? 'academic');
$phone = trim($data['phone'] ?? '');
$email = trim($data['email'] ?? '');

if (empty($smis_code) || empty($id_card) || empty($name)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน (รหัส SMIS, เลขบัตรประชาชน, ชื่อ-นามสกุล)'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($id_card) !== 13 || !ctype_digit($id_card)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'หมายเลขประจำตัวประชาชนต้องเป็นตัวเลข 13 หลัก'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$pdo) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . ($db_error ?: 'กรุณาตรวจสอบการตั้งค่าฐานข้อมูล')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 1. ตรวจสอบโรงเรียนจาก SMIS
    $stmt = $pdo->prepare("SELECT id, name FROM schools WHERE smis_code = ? OR code = ? LIMIT 1");
    $stmt->execute([$smis_code, $smis_code]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$school) {
        echo json_encode([
            'status' => 'error',
            'message' => "ไม่พบรหัส SMIS ($smis_code) ในฐานข้อมูล กรุณาให้ผู้ดูแลระบบสูงสุดสร้างสถานศึกษาก่อน"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. ตรวจสอบว่าเคยสมัครหรือยัง
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id_card = ? OR username = ? LIMIT 1");
    $checkStmt->execute([$id_card, $id_card]);
    if ($checkStmt->fetch()) {
        echo json_encode([
            'status' => 'error',
            'message' => 'หมายเลขประจำตัวประชาชนนี้มีอยู่ในระบบแล้ว สามารถใช้เข้าสู่ระบบได้ทันที'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. กำหนด Role ตามตำแหน่ง
    $role = 'teacher';
    if (strpos($position, 'ผู้อำนวยการ') !== false && strpos($position, 'รอง') === false) {
        $role = 'director';
    } else if (strpos($position, 'รองผู้อำนวยการ') !== false) {
        $role = 'deputy_director';
    } else if (strpos($position, 'แผนงาน') !== false) {
        $role = 'plan_officer';
    } else if (strpos($position, 'หัวหน้า') !== false) {
        $role = 'department_head';
    }

    $defaultPassword = '123'; // รหัสผ่านเริ่มต้น

    $insertStmt = $pdo->prepare("
        INSERT INTO users (school_id, smis_code, id_card, username, password, name, position, department, role, phone, email, must_change_password, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'active')
    ");
    $insertStmt->execute([
        $school['id'],
        $smis_code,
        $id_card,
        $id_card,
        $defaultPassword,
        $name,
        $position,
        $department,
        $role,
        $phone,
        $email
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'ลงทะเบียนบุคลากรสำเร็จเรียบร้อยแล้ว'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
