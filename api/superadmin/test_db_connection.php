<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$host = trim($data['host'] ?? 'localhost');
$port = trim($data['port'] ?? '3306');
$db   = trim($data['database'] ?? 'school_action_plan');
$user = trim($data['user'] ?? 'root');
$pass = $data['password'] ?? '';

try {
    // 1. Try connecting directly to the specified database (standard for shared hosting / cPanel)
    try {
        $pdoTest = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 4
        ]);
        
        echo json_encode([
            'status' => 'success',
            'connected' => true,
            'message' => "เชื่อมต่อ MySQL Server และฐานข้อมูล `$db` สำเร็จเรียบร้อย พร้อมใช้งานทันที"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (PDOException $eDb) {
        // If database doesn't exist yet, try to create it if privileged
        if (strpos($eDb->getMessage(), 'Unknown database') !== false || $eDb->getCode() == 1049) {
            $pdoRoot = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 4
            ]);
            $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdoRoot->exec("USE `$db`");

            echo json_encode([
                'status' => 'success',
                'connected' => true,
                'message' => "เชื่อมต่อ MySQL Server และสร้างฐานข้อมูล `$db` สำเร็จเรียบร้อย พร้อมใช้งานทันที"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            throw $eDb;
        }
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'connected' => false,
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล MySQL ได้: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
