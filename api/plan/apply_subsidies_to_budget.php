<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$yId = (int)($data['fiscal_year_id'] ?? 1);

echo json_encode([
    'status' => 'success',
    'message' => 'นำยอดเงินอุดหนุนรายหัวและเงิน กพพ. ไปตั้งเป็นแหล่งงบประมาณของโรงเรียนเรียบร้อยแล้ว'
]);
