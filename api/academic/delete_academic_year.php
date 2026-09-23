<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$school_id = $_SESSION['school_id'];

if (empty($id)) {
    echo json_encode(['error' => 'ไม่พบรหัสปีการศึกษา']);
    exit;
}

try {
    // Check if it's the current year
    $stmt = $pdo->prepare('SELECT is_current FROM academic_years WHERE id = ? AND school_id = ?');
    $stmt->execute([$id, $school_id]);
    $year = $stmt->fetch();
    
    if (!$year) {
        echo json_encode(['error' => 'ไม่พบข้อมูลปีการศึกษา']);
        exit;
    }
    
    if ($year['is_current']) {
        echo json_encode(['error' => 'ไม่สามารถลบปีการศึกษาปัจจุบันได้']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM academic_years WHERE id = ? AND school_id = ?');
    $stmt->execute([$id, $school_id]);

    echo json_encode(['status' => 'success', 'message' => 'ลบปีการศึกษาเรียบร้อยแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถลบปีการศึกษาได้: ' . $e->getMessage()]);
}
?>
