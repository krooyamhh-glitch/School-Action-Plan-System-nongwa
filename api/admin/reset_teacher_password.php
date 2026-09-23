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

if (empty($id)) {
    echo json_encode(['error' => 'ไม่พบรหัสผู้ใช้งาน']);
    exit;
}

try {
    $hashed_password = password_hash('123456', PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $params = [$hashed_password, $id];
    
    // ถ้าเป็น Admin โรงเรียน ต้องเช็คว่าครูอยู่ในโรงเรียนตัวเอง
    if ($_SESSION['role'] === 'admin') {
        $sql .= " AND school_id = ?";
        $params[] = $_SESSION['school_id'];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'รีเซ็ตรหัสผ่านเป็น 123456 เรียบร้อยแล้ว']);
    } else {
        echo json_encode(['error' => 'ไม่พบข้อมูลที่ต้องการแก้ไข หรือไม่มีสิทธิ์']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
