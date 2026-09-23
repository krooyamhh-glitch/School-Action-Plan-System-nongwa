<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && (!isset($_SESSION['is_academic']) || !$_SESSION['is_academic']))) {
    die(json_encode(['error' => 'Unauthorized or Insufficient Permissions']));
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['academic_year']) || !isset($data['test_type'])) {
    die(json_encode(['error' => 'Invalid data provided']));
}

$academic_year = $data['academic_year'];
$test_type = strtolower($data['test_type']);
$score_max = (float)($data['score_max'] ?? 100);
$subjects = $data['subjects'] ?? [];

try {
    $pdo->beginTransaction();

    // Calculate average if subjects are provided
    $score_avg = (float)($data['score_avg'] ?? 0);
    if (!empty($subjects)) {
        $total = 0;
        foreach ($subjects as $s) {
            $total += (float)$s['score'];
        }
        $score_avg = $total / count($subjects);
    }

    $stmt = $pdo->prepare("INSERT INTO national_test_results (school_id, academic_year, test_type, score_avg, score_max) 
                          VALUES (?, ?, ?, ?, ?) 
                          ON DUPLICATE KEY UPDATE score_avg = VALUES(score_avg), score_max = VALUES(score_max)");
    $stmt->execute([$school_id, $academic_year, $test_type, $score_avg, $score_max]);
    
    // Get the result ID
    $stmt = $pdo->prepare("SELECT id FROM national_test_results WHERE school_id = ? AND academic_year = ? AND test_type = ?");
    $stmt->execute([$school_id, $academic_year, $test_type]);
    $result_id = $stmt->fetchColumn();

    // Save individual subjects
    if (!empty($subjects)) {
        // Clear existing scores for this result
        $pdo->prepare("DELETE FROM national_test_scores WHERE result_id = ?")->execute([$result_id]);
        
        $stmt_score = $pdo->prepare("INSERT INTO national_test_scores (result_id, subject_name, score) VALUES (?, ?, ?)");
        foreach ($subjects as $s) {
            $stmt_score->execute([$result_id, $s['name'], (float)$s['score']]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลคะแนนสำเร็จ']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
