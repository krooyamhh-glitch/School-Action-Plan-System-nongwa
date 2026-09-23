<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$smis_code = trim($data['smis_code'] ?? '');

if (empty($smis_code) || strlen($smis_code) !== 8) {
    echo json_encode([
        'status' => 'error',
        'message' => 'กรุณาระบุรหัส SMIS ให้ถูกต้องครบ 8 หลัก'
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
    $stmt = $pdo->prepare("SELECT id, name, affiliation, smis_code, code, status FROM schools WHERE smis_code = ? OR code = ? LIMIT 1");
    $stmt->execute([$smis_code, $smis_code]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($school) {
        echo json_encode([
            'status' => 'success',
            'school' => [
                'id' => $school['id'],
                'name' => $school['name'],
                'affiliation' => $school['affiliation'] ?: '',
                'smis_code' => $school['smis_code'] ?: $school['code'],
                'status' => $school['status']
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => "ไม่พบรหัส SMIS ($smis_code) ในระบบ หรือยังไม่ได้รับการสร้างในฐานข้อมูล"
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการค้นหาข้อมูล: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
