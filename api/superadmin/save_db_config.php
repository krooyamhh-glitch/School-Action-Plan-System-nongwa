<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$host = trim($data['host'] ?? 'localhost');
$port = trim($data['port'] ?? '3306');
$db   = trim($data['database'] ?? 'school_action_plan');
$user = trim($data['user'] ?? 'root');
$pass = $data['password'] ?? '';

// Save to config.php or db-config.json
$jsonConfig = [
    'host' => $host,
    'port' => $port,
    'database' => $db,
    'user' => $user,
    'password' => $pass
];

file_put_contents(__DIR__ . '/../../db-config.json', json_encode($jsonConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Also generate updated config.php
$phpConfigContent = "<?php\n" .
    "// การตั้งค่าฐานข้อมูล MySQL สำหรับระบบแผนปฏิบัติการสถานศึกษา\n" .
    "\$host = " . var_export($host, true) . ";\n" .
    "\$port = " . var_export($port, true) . ";\n" .
    "\$db   = " . var_export($db, true) . ";\n" .
    "\$user = " . var_export($user, true) . ";\n" .
    "\$pass = " . var_export($pass, true) . ";\n\n" .
    "\$pdo = null;\n" .
    "try {\n" .
    "    \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db;charset=utf8mb4\", \$user, \$pass, [\n" .
    "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n" .
    "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n" .
    "        PDO::ATTR_TIMEOUT => 3,\n" .
    "    ]);\n" .
    "} catch (PDOException \$e) {\n" .
    "    \$db_error = \$e->getMessage();\n" .
    "}\n" .
    "?>\n";

file_put_contents(__DIR__ . '/../config.php', $phpConfigContent);

echo json_encode([
    'status' => 'success',
    'message' => 'บันทึกการตั้งค่าเชื่อมต่อฐานข้อมูล MySQL เรียบร้อยแล้ว',
    'config' => [
        'host' => $host,
        'port' => $port,
        'database' => $db,
        'user' => $user
    ]
]);
