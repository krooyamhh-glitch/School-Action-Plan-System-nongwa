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
            $srcCols = getTableColumns($pdo, 'budget_sources');
            $bCols = ["`fiscal_year_id`", "`name`", "`amount`"];
            $bVals = [$fiscal_year_id, 'งบประมาณที่กำหนดสำหรับจัดสรร', $total_budget_base];

            if (in_array('school_id', $srcCols)) { $bCols[] = "`school_id`"; $bVals[] = 1; }
            if (in_array('code', $srcCols)) { $bCols[] = "`code`"; $bVals[] = 'SRC-01'; }
            if (in_array('source_type', $srcCols)) { $bCols[] = "`source_type`"; $bVals[] = 'สพฐ.'; }
            if (in_array('notes', $srcCols)) { $bCols[] = "`notes`"; $bVals[] = 'กำหนดโดยผู้ใช้'; }

            $placeholders = array_fill(0, count($bCols), '?');
            $sqlSrc = "INSERT INTO `budget_sources` (" . implode(', ', $bCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmtInsertSrc = $pdo->prepare($sqlSrc);
            $stmtInsertSrc->execute($bVals);
        }
    }

    // 3. จัดการบันทึก department_allocations (ทั้ง 5 ช่อง)
    $allocCols = getTableColumns($pdo, 'department_allocations');
    $hasAllocSchool = in_array('school_id', $allocCols);
    $hasDeptName = in_array('department_name', $allocCols);
    $hasNotes = in_array('notes', $allocCols);

    $stmtFindAlloc = $pdo->prepare("SELECT id FROM department_allocations WHERE fiscal_year_id = ? AND department = ? LIMIT 1");

    foreach ($allocations as $a) {
        $deptKey = $a['department'];
        $deptName = $a['department_name'] ?? '';
        $pct = (float)($a['percentage'] ?? 0);
        $amt = (float)($a['allocated_amount'] ?? (($pct / 100) * $allocatableBudget));
        $notes = $a['notes'] ?? '';

        $stmtFindAlloc->execute([$fiscal_year_id, $deptKey]);
        $existingAlloc = $stmtFindAlloc->fetch(PDO::FETCH_ASSOC);

        if ($existingAlloc) {
            $upFields = ["`percentage` = ?", "`allocated_amount` = ?"];
            $upParams = [$pct, $amt];
            if ($hasDeptName) { $upFields[] = "`department_name` = ?"; $upParams[] = $deptName; }
            if ($hasNotes) { $upFields[] = "`notes` = ?"; $upParams[] = $notes; }
            $upParams[] = $existingAlloc['id'];

            $sqlUpA = "UPDATE `department_allocations` SET " . implode(', ', $upFields) . " WHERE id = ?";
            $stmtUpA = $pdo->prepare($sqlUpA);
            $stmtUpA->execute($upParams);
        } else {
            $inCols = ["`fiscal_year_id`", "`department`", "`percentage`", "`allocated_amount`"];
            $inVals = [$fiscal_year_id, $deptKey, $pct, $amt];
            if ($hasAllocSchool) { $inCols[] = "`school_id`"; $inVals[] = 1; }
            if ($hasDeptName) { $inCols[] = "`department_name`"; $inVals[] = $deptName; }
            if ($hasNotes) { $inCols[] = "`notes`"; $inVals[] = $notes; }

            $placeholders = array_fill(0, count($inCols), '?');
            $sqlInA = "INSERT INTO `department_allocations` (" . implode(', ', $inCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmtInA = $pdo->prepare($sqlInA);
            $stmtInA->execute($inVals);
        }
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'บันทึกการจัดสรรงบประมาณ 100% เรียบร้อยแล้ว',
        'total_budget_base' => $total_budget_base,
        'utility_reserve' => $utility_reserve,
        'allocatable_budget' => $allocatableBudget
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
