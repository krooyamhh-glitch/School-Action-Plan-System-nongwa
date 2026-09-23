<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$year = trim($data['year'] ?? '');
$start_date = $data['start_date'] ?? null;
$end_date = $data['end_date'] ?? null;
$is_current = !empty($data['is_current']) ? 1 : 0;

if (empty($year)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุปีงบประมาณ']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'บันทึกปีงบประมาณสำเร็จ (จำลอง)']);
    exit;
}

try {
    if ($is_current == 1) {
        $pdo->query("UPDATE fiscal_years SET is_current = 0");
    }

    $stmt = $pdo->prepare("
        INSERT INTO fiscal_years (school_id, year, start_date, end_date, is_current, status)
        VALUES (1, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$year, $start_date, $end_date, $is_current]);
    $newYearId = (int)$pdo->lastInsertId();

    // กำหนดสัดส่วน 4 กลุ่มงานเริ่มต้น (40, 20, 15, 15, 10 = 100%)
    $defaults = [
        ['academic', 'กลุ่มบริหารวิชาการ', 40.00],
        ['budget', 'กลุ่มบริหารงบประมาณและสินทรัพย์', 20.00],
        ['personnel', 'กลุ่มบริหารงานบุคคล', 15.00],
        ['general', 'กลุ่มบริหารทั่วไป', 15.00],
        ['reserve', 'งบสำรองจ่าย/ส่วนกลาง', 10.00]
    ];
    $stmt_alloc = $pdo->prepare("
        INSERT INTO department_allocations (fiscal_year_id, department, department_name, percentage, allocated_amount, notes)
        VALUES (?, ?, ?, ?, 0.00, 'จัดสรรเบื้องต้นตามเกณฑ์ 100%')
    ");
    foreach ($defaults as $d) {
        $stmt_alloc->execute([$newYearId, $d[0], $d[1], $d[2]]);
    }

    echo json_encode(['status' => 'success', 'message' => 'สร้างปีงบประมาณสำเร็จ', 'year_id' => $newYearId]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
