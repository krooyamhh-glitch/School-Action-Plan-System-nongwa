<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล MySQL ได้ กรุณาตรวจสอบการตั้งค่าฐานข้อมูล'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$smis_code = trim($data['smis_code'] ?? '');
$name = trim($data['name'] ?? '');
$affiliation = trim($data['affiliation'] ?? '');
$province = trim($data['province'] ?? '');
$district = trim($data['district'] ?? '');
$status = trim($data['status'] ?? 'active');

if (empty($smis_code) || strlen($smis_code) !== 8) {
    echo json_encode(['status' => 'error', 'message' => 'รหัส SMIS ต้องเป็นตัวเลข 8 หลัก']);
    exit;
}
if (empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุชื่อสถานศึกษา']);
    exit;
}

try {
    // Check if school already exists
    $checkStmt = $pdo->prepare("SELECT id FROM schools WHERE code = ? LIMIT 1");
    $checkStmt->execute([$smis_code]);
    if ($checkStmt->fetch()) {
        $updateStmt = $pdo->prepare("
            UPDATE schools 
            SET name = ?, affiliation = ?, province = ?, district = ?, status = ?
            WHERE code = ?
        ");
        $updateStmt->execute([$name, $affiliation, $province, $district, $status, $smis_code]);
    } else {
        $insertStmt = $pdo->prepare("
            INSERT INTO schools (code, name, affiliation, province, district, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insertStmt->execute([$smis_code, $name, $affiliation, $province, $district, $status]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => "เปิดใช้งานสถานศึกษา $name (SMIS: $smis_code) สำเร็จเรียบร้อยแล้ว"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลโรงเรียน: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
