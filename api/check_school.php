<?php
require_once 'config.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';

if (strlen($code) !== 8) {
    echo json_encode(['exists' => false, 'error' => 'รหัสโรงเรียนต้องมี 8 หลัก']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, name FROM schools WHERE code = ?');
    $stmt->execute([$code]);
    $school = $stmt->fetch();
    
    if ($school) {
        echo json_encode(['exists' => true, 'name' => $school['name']]);
    } else {
        echo json_encode(['exists' => false, 'error' => 'ไม่พบรหัสโรงเรียนนี้ในระบบ']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['exists' => false, 'error' => 'ไม่สามารถตรวจสอบข้อมูลได้']);
}
?>
