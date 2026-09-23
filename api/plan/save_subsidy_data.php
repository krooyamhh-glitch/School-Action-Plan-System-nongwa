<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$yId = (int)($data['fiscal_year_id'] ?? 1);
$subsidies = $data['subsidies'] ?? [];

$subsidiesFile = __DIR__ . '/../../subsidies-data.json';
$allSubsidies = [];
if (file_exists($subsidiesFile)) {
    $allSubsidies = json_decode(file_get_contents($subsidiesFile), true) ?: [];
}

$allSubsidies[$yId] = $subsidies;
file_put_contents($subsidiesFile, json_encode($allSubsidies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'status' => 'success',
    'message' => 'บันทึกจำนวนนักเรียนและอัตราเงินอุดหนุนรายหัว/กพพ. สำเร็จ'
]);
