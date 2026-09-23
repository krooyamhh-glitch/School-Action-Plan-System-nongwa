<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$school_id = $_SESSION['school_id'];
$academic_year = $_GET['academic_year'] ?? '2567';

try {
    $stmt = $pdo->prepare('SELECT * FROM clubs WHERE school_id = ? AND academic_year = ? ORDER BY name ASC');
    $stmt->execute([$school_id, $academic_year]);
    $clubs = $stmt->fetchAll();

    echo json_encode($clubs);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
