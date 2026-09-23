<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin')) {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$name = $data['name'] ?? '';
$last_name = $data['last_name'] ?? '';
$position = $data['position'] ?? '';
$username = $data['username'] ?? ''; // เลขบัตรประชาชน
$password = $data['password'] ?? '';
$school_id = $data['school_id'] ?? $_SESSION['school_id'];
$is_academic = isset($data['is_academic']) ? (int)$data['is_academic'] : 0;

if (empty($name) || empty($position) || (empty($id) && empty($username))) {
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

try {
    if (!empty($id)) {
        // Update
        $sql = "UPDATE users SET name = ?, last_name = ?, position = ?, is_academic = ? WHERE id = ?";
        $params = [$name, $last_name, $position, $is_academic, $id];
        
        // ถ้าเป็น Admin โรงเรียน ต้องเช็คว่าครูอยู่ในโรงเรียนตัวเอง
        if ($_SESSION['role'] === 'admin') {
            $sql .= " AND school_id = ?";
            $params[] = $_SESSION['school_id'];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status' => 'success', 'message' => 'ปรับปรุงข้อมูลคุณครูเรียบร้อยแล้ว']);
    } else {
        // Create
        // เช็คว่ามี username นี้หรือยัง
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'เลขบัตรประชาชนนี้มีในระบบแล้ว']);
            exit;
        }

        $hashed_password = password_hash($password ?: '123456', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, name, last_name, position, role, school_id, is_approved, is_academic) VALUES (?, ?, ?, ?, ?, 'teacher', ?, 1, ?)");
        $stmt->execute([$username, $hashed_password, $name, $last_name, $position, $school_id, $is_academic]);
        echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลคุณครูเรียบร้อยแล้ว']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
