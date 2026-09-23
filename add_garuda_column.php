<?php
require_once 'api/config.php';

try {
    $pdo->exec("ALTER TABLE schools ADD COLUMN garuda_url VARCHAR(255) DEFAULT NULL AFTER logo_url");
    echo "Column garuda_url added successfully";
} catch (PDOException $e) {
    echo "Error or column already exists: " . $e->getMessage();
}
unlink(__FILE__);
