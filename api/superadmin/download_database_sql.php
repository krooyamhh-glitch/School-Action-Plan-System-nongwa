<?php
$sqlPath = __DIR__ . '/../../database.sql';
if (file_exists($sqlPath)) {
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="school_action_plan_database.sql"');
    header('Content-Length: ' . filesize($sqlPath));
    readfile($sqlPath);
    exit;
} else {
    http_response_code(404);
    echo "File not found";
}
