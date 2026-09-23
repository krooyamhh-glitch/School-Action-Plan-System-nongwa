<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$configFile = __DIR__ . '/../../db-config.json';
$adminConfigFile = __DIR__ . '/../../admin-config.json';

// Default DB config
$dbConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'school_action_plan',
    'user' => 'root',
    'password' => ''
];

if (file_exists($configFile)) {
    $saved = json_decode(file_get_contents($configFile), true);
    if (is_array($saved)) {
        $dbConfig = array_merge($dbConfig, $saved);
    }
}

// Default Admin config
$adminConfig = [
    'id' => 1,
    'username' => 'superadmin',
    'name' => 'ผู้ดูแลระบบสูงสุด (Super Admin)',
    'position' => 'ผู้ดูแลระบบสูงสุด',
    'phone' => '0812345678',
    'email' => 'superadmin@school.ac.th',
    'role' => 'super_admin',
    'has_custom_password' => false
];

if (file_exists($adminConfigFile)) {
    $savedAdmin = json_decode(file_get_contents($adminConfigFile), true);
    if (is_array($savedAdmin)) {
        $adminConfig = array_merge($adminConfig, $savedAdmin);
    }
}

// Test DB Connection
$connected = false;
$connMsg = '';
try {
    $pdoTest = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['password'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 2
        ]
    );
    $connected = true;
    $connMsg = "เชื่อมต่อ MySQL Server สำเร็จเรียบร้อย ({$dbConfig['host']}:{$dbConfig['port']}/{$dbConfig['database']})";

    // Check if user exists in database
    $stmt = $pdoTest->query("SELECT id, username, name, position, phone, email, password FROM users WHERE role = 'super_admin' LIMIT 1");
    if ($dbUser = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $adminConfig['id'] = $dbUser['id'];
        $adminConfig['username'] = $dbUser['username'];
        if (!empty($dbUser['name'])) $adminConfig['name'] = $dbUser['name'];
        if (!empty($dbUser['position'])) $adminConfig['position'] = $dbUser['position'];
        if (!empty($dbUser['phone'])) $adminConfig['phone'] = $dbUser['phone'];
        if (!empty($dbUser['email'])) $adminConfig['email'] = $dbUser['email'];
        $adminConfig['has_custom_password'] = ($dbUser['password'] !== 'password123' && $dbUser['password'] !== '123' && $dbUser['password'] !== '123456');
    }
} catch (PDOException $e) {
    $connected = false;
    $connMsg = "ไม่สามารถเชื่อมต่อ MySQL ได้: " . $e->getMessage();
}

echo json_encode([
    'status' => 'success',
    'user' => $adminConfig,
    'database' => [
        'config' => [
            'host' => $dbConfig['host'],
            'port' => (int)$dbConfig['port'],
            'database' => $dbConfig['database'],
            'user' => $dbConfig['user']
        ],
        'connected' => $connected,
        'message' => $connMsg
    ]
], JSON_UNESCAPED_UNICODE);
