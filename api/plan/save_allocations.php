<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$fiscal_year_id = (int)($data['fiscal_year_id'] ?? 0);
$allocations = $data['allocations'] ?? [];
$total_budget_base = (float)($data['total_budget_base'] ?? 0);
$utility_reserve = (float)($data['utility_reserve'] ?? 0);
$utility_reserve_notes = trim($data['utility_reserve_notes'] ?? 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)');

if ($fiscal_year_id <= 0) {
    $stmtY = $pdo->query("SELECT id FROM fiscal_years ORDER BY is_current DESC, id DESC LIMIT 1");
    $rowY = $stmtY->fetch();
    $fiscal_year_id = $rowY ? (int)$rowY['id'] : 1;
}

if (empty($allocations)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีข้อมูลการจัดสรร 5 ช่อง'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ตรวจสอบผลรวมเปอร์เซ็นต์
$sumPct = 0;
foreach ($allocations as $a) {
    $sumPct += (float)($a['percentage'] ?? 0);
}

if (abs($sumPct - 100.0) > 0.05) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'ผลรวมเปอร์เซ็นต์ต้องได้ 100.00% พอดี (ปัจจุบันคำนวณได้ ' . number_format($sumPct, 2) . '%)'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// คำนวณงบสุทธิที่นำมาจัดสรร
$allocatableBudget = max(0, $total_budget_base - $utility_reserve);

try {
    $pdo->beginTransaction();

    // 1. อัปเดตข้อมูลปีงบประมาณ
    $stmtUpYear = $pdo->prepare("
        UPDATE fiscal_years 
        SET total_budget_base = ?, 
            utility_reserve = ?, 
            utility_reserve_notes = ?, 
            allocatable_budget = ? 
        WHERE id = ?
    ");
    $stmtUpYear->execute([
        $total_budget_base,
        $utility_reserve,
        $utility_reserve_notes,
        $allocatableBudget,
        $fiscal_year_id
    ]);

    // 2. ถ้าใส่ยอดงบประมาณเอง และไม่มี budget_sources หรือต้องการซิงค์ยอด
    if ($total_budget_base > 0) {
        $stmtSrcCheck = $pdo->prepare("SELECT COUNT(*) FROM budget_sources WHERE fiscal_year_id = ?");
        $stmtSrcCheck->execute([$fiscal_year_id]);
        $hasSources = $stmtSrcCheck->fetchColumn() > 0;

        if (!$hasSources) {
            $stmtInsertSrc = $pdo->prepare("
                INSERT INTO budget_sources (fiscal_year_id, code, name, category, amount, description) 
                VALUES (?, 'SRC-01', 'งบประมาณที่กำหนดสำหรับจัดสรร', 'subsidy', ?, 'กำหนดโดยผู้ใช้')
            ");
            $stmtInsertSrc->execute([$fiscal_year_id, $total_budget_base]);
        }
    }

    // 3. จัดการบันทึก department_allocations (ทั้ง 5 ช่อง)
    $stmtUpsert = $pdo->prepare("
        INSERT INTO department_allocations (
            school_id, fiscal_year_id, department, department_name, percentage, allocated_amount, notes
        ) VALUES (
            1, ?, ?, ?, ?, ?, ?
        )
        ON DUPLICATE KEY UPDATE
            department_name = VALUES(department_name),
            percentage = VALUES(percentage),
            allocated_amount = VALUES(allocated_amount),
            notes = VALUES(notes)
    ");

    foreach ($allocations as $a) {
        $deptKey = $a['department'];
        $deptName = $a['department_name'] ?? '';
        $pct = (float)($a['percentage'] ?? 0);
        $amt = (float)($a['allocated_amount'] ?? (($pct / 100) * $allocatableBudget));
        $notes = $a['notes'] ?? '';

        $stmtUpsert->execute([
            $fiscal_year_id,
            $deptKey,
            $deptName,
            $pct,
            $amt,
            $notes
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'บันทึกการจัดสรรงบประมาณ 100% เรียบร้อยแล้ว',
        'total_budget_base' => $total_budget_base,
        'utility_reserve' => $utility_reserve,
        'allocatable_budget' => $allocatableBudget
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
