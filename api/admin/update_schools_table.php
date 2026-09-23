<?php
require_once 'api/config.php';
header('Content-Type: application/json');

try {
    // Add affiliation and district to schools table
    $pdo->exec("ALTER TABLE schools ADD COLUMN IF NOT EXISTS affiliation VARCHAR(255) AFTER name");
    $pdo->exec("ALTER TABLE schools ADD COLUMN IF NOT EXISTS district VARCHAR(100) AFTER affiliation");
    
    echo json_encode(['status' => 'success', 'message' => 'Database updated successfully']);
} catch (Exception $e) {
    echo json_encode(['error' => $e.getMessage()]);
}
