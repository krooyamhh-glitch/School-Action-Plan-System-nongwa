<?php
require_once 'api/config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("DESCRIBE schools");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT * FROM schools LIMIT 5");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'columns' => $columns,
        'schools' => $schools
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
