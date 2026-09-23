<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$name = $data['name'] ?? '';
$affiliation = $data['affiliation'] ?? '';
$district = $data['district'] ?? '';
$province = $data['province'] ?? '';

if (empty($id) || empty($name)) {
    echo json_encode(['error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE schools SET name = ?, affiliation = ?, district = ?, province = ? WHERE id = ?');
    $stmt->execute([$name, $affiliation, $district, $province, $id]);
    echo json_encode(['message' => 'แก้ไขข้อมูลโรงเรียนสำเร็จแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถแก้ไขข้อมูลได้: ' . $e->getMessage()]);
}
?>
