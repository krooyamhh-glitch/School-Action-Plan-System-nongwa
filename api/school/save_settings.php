<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');

if (empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อสถานศึกษา']);
    exit;
}

$configFile = __DIR__ . '/../../db-config.json';
$schoolsFile = __DIR__ . '/../../schools-data.json';

// Try MySQL
try {
    $dbConfig = ['host' => 'localhost', 'port' => 3306, 'database' => 'school_action_plan', 'user' => 'root', 'password' => ''];
    if (file_exists($configFile)) {
        $dbConfig = array_merge($dbConfig, json_decode(file_get_contents($configFile), true) ?: []);
    }
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['password'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 2]
    );

    $stmt = $pdo->prepare("UPDATE schools SET name = ?, affiliation = ?, province = ?, district = ?, subdistrict = ?, address = ?, postal_code = ?, phone = ?, email = ?, website = ?, director_name = ?, director_position = ?, plan_officer_name = ? WHERE id = 1");
    $stmt->execute([
        $name,
        $data['affiliation'] ?? '',
        $data['province'] ?? '',
        $data['district'] ?? '',
        $data['subdistrict'] ?? '',
        $data['address'] ?? '',
        $data['postal_code'] ?? '',
        $data['phone'] ?? '',
        $data['email'] ?? '',
        $data['website'] ?? '',
        $data['director_name'] ?? '',
        $data['director_position'] ?? '',
        $data['plan_officer_name'] ?? ''
    ]);
} catch (Exception $e) {
    // Ignore
}

// Update JSON
if (file_exists($schoolsFile)) {
    $schools = json_decode(file_get_contents($schoolsFile), true) ?: [];
    if (!empty($schools)) {
        $schools[0] = array_merge($schools[0], $data);
        file_put_contents($schoolsFile, json_encode($schools, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

echo json_encode([
    'status' => 'success',
    'message' => 'บันทึกข้อมูลสถานศึกษาเรียบร้อยแล้ว'
]);
