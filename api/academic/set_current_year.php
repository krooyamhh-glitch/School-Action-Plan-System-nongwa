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
    $pdo->beginTransaction();

    // 1. Reset all to false
    $stmt = $pdo->prepare('UPDATE academic_years SET is_current = FALSE WHERE school_id = ?');
    $stmt->execute([$school_id]);

    // 2. Set selected to true
    $stmt = $pdo->prepare('UPDATE academic_years SET is_current = TRUE WHERE id = ? AND school_id = ?');
    $stmt->execute([$id, $school_id]);

    $pdo->commit();
    echo json_encode(['message' => 'กำหนดปีการศึกษาปัจจุบันสำเร็จ']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถกำหนดปีการศึกษาปัจจุบันได้: ' . $e->getMessage()]);
}
?>
