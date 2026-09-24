<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$fiscal_year_id = (int)($data['fiscal_year_id'] ?? 0);

if ($fiscal_year_id <= 0) {
    $stmtY = $pdo->query("SELECT id FROM fiscal_years ORDER BY is_current DESC, id DESC LIMIT 1");
    $rowY = $stmtY->fetch();
    $fiscal_year_id = $rowY ? (int)$rowY['id'] : 1;
}

try {
    $pdo->beginTransaction();

    // 1. ดึงยอดรวมจาก student_subsidies อย่างปลอดภัย
    $subCols = getTableColumns($pdo, 'student_subsidies');
    $hasSmall = in_array('small_school_subsidy', $subCols);
    $hasRate = in_array('subsidy_rate', $subCols);
    $hasDev = in_array('dev_rate', $subCols);
    $hasTotSub = in_array('total_subsidy_amount', $subCols);
    $hasTotDev = in_array('total_dev_amount', $subCols);
    $hasTotAll = in_array('total_amount', $subCols);

    $smallSql = $hasSmall ? "COALESCE(SUM(student_count * small_school_subsidy), 0)" : "0";
    $totSubSql = $hasTotSub ? "COALESCE(SUM(total_subsidy_amount), 0)" : ($hasRate ? "COALESCE(SUM(student_count * subsidy_rate), 0)" : "0");
    $totDevSql = $hasTotDev ? "COALESCE(SUM(total_dev_amount), 0)" : ($hasDev ? "COALESCE(SUM(student_count * dev_rate), 0)" : "0");
    $totAllSql = $hasTotAll ? "COALESCE(SUM(total_amount), 0)" : "($totSubSql + $totDevSql)";

    $stmtSub = $pdo->prepare("
        SELECT 
            COALESCE(SUM(student_count), 0) as total_students,
            $smallSql as small_school_subsidy,
            $totSubSql as total_subsidy,
            $totDevSql as total_dev,
            $totAllSql as grand_total
        FROM `student_subsidies`
        WHERE fiscal_year_id = ?
    ");
    $stmtSub->execute([$fiscal_year_id]);
    $subData = $stmtSub->fetch(PDO::FETCH_ASSOC);

    $totalStudents = (int)($subData['total_students'] ?? 0);
    $smallSchoolAmt = (float)($subData['small_school_subsidy'] ?? 0);
    $totalSubsidy = (float)($subData['total_subsidy'] ?? 0);
    $totalDev = (float)($subData['total_dev'] ?? 0);
    $grandTotal = (float)($subData['grand_total'] ?? ($totalSubsidy + $totalDev));

    // 2. อัปเดตหรือเพิ่มใน budget_sources สำหรับปีงบประมาณนี้
    $srcCols = getTableColumns($pdo, 'budget_sources');
    $hasSrcSchool = in_array('school_id', $srcCols);
    $hasCategory = in_array('category', $srcCols);
    $hasSourceType = in_array('source_type', $srcCols);
    $hasCode = in_array('code', $srcCols);

    $subsidyName = 'เงินอุดหนุนรายหัวจัดการศึกษาขั้นพื้นฐาน';
    if ($smallSchoolAmt > 0) {
        $subsidyName .= ' (รวมเงินเพิ่ม รร.ขนาดเล็ก ' . number_format($smallSchoolAmt, 2) . ' บ.)';
    }

    // แหล่งที่ 1: เงินอุดหนุนรายหัว
    $src1 = null;
    if ($hasCategory) {
        $stmtSrc1 = $pdo->prepare("SELECT id FROM `budget_sources` WHERE fiscal_year_id = ? AND `category` = 'subsidy' LIMIT 1");
        $stmtSrc1->execute([$fiscal_year_id]);
        $src1 = $stmtSrc1->fetch();
    }
    if (!$src1 && $hasCode) {
        $stmtSrc1 = $pdo->prepare("SELECT id FROM `budget_sources` WHERE fiscal_year_id = ? AND `code` = 'SUB-01' LIMIT 1");
        $stmtSrc1->execute([$fiscal_year_id]);
        $src1 = $stmtSrc1->fetch();
    }

    if ($src1) {
        $stmtUp1 = $pdo->prepare("UPDATE `budget_sources` SET `name` = ?, `amount` = ? WHERE id = ?");
        $stmtUp1->execute([$subsidyName, $totalSubsidy, $src1['id']]);
    } else {
        $b1Cols = ["`fiscal_year_id`", "`name`", "`amount`"];
        $b1Vals = [$fiscal_year_id, $subsidyName, $totalSubsidy];
        if ($hasSrcSchool) { $b1Cols[] = "`school_id`"; $b1Vals[] = 1; }
        if ($hasCode) { $b1Cols[] = "`code`"; $b1Vals[] = 'SUB-01'; }
        if ($hasCategory) { $b1Cols[] = "`category`"; $b1Vals[] = 'subsidy'; }
        if ($hasSourceType) { $b1Cols[] = "`source_type`"; $b1Vals[] = 'เงินอุดหนุน'; }

        $placeholders1 = array_fill(0, count($b1Cols), '?');
        $sqlIn1 = "INSERT INTO `budget_sources` (" . implode(', ', $b1Cols) . ") VALUES (" . implode(', ', $placeholders1) . ")";
        $stmtIn1 = $pdo->prepare($sqlIn1);
        $stmtIn1->execute($b1Vals);
    }

    // แหล่งที่ 2: เงินกิจกรรมพัฒนาคุณภาพผู้เรียน (กพพ.)
    $devName = 'เงินกิจกรรมพัฒนาคุณภาพผู้เรียน (กพพ. 4 กิจกรรมหลัก)';
    $src2 = null;
    if ($hasCategory) {
        $stmtSrc2 = $pdo->prepare("SELECT id FROM `budget_sources` WHERE fiscal_year_id = ? AND `category` = 'student_dev' LIMIT 1");
        $stmtSrc2->execute([$fiscal_year_id]);
        $src2 = $stmtSrc2->fetch();
    }
    if (!$src2 && $hasCode) {
        $stmtSrc2 = $pdo->prepare("SELECT id FROM `budget_sources` WHERE fiscal_year_id = ? AND `code` = 'DEV-01' LIMIT 1");
        $stmtSrc2->execute([$fiscal_year_id]);
        $src2 = $stmtSrc2->fetch();
    }

    if ($src2) {
        $stmtUp2 = $pdo->prepare("UPDATE `budget_sources` SET `name` = ?, `amount` = ? WHERE id = ?");
        $stmtUp2->execute([$devName, $totalDev, $src2['id']]);
    } else {
        $b2Cols = ["`fiscal_year_id`", "`name`", "`amount`"];
        $b2Vals = [$fiscal_year_id, $devName, $totalDev];
        if ($hasSrcSchool) { $b2Cols[] = "`school_id`"; $b2Vals[] = 1; }
        if ($hasCode) { $b2Cols[] = "`code`"; $b2Vals[] = 'DEV-01'; }
        if ($hasCategory) { $b2Cols[] = "`category`"; $b2Vals[] = 'student_dev'; }
        if ($hasSourceType) { $b2Cols[] = "`source_type`"; $b2Vals[] = 'เงินพัฒนาผู้เรียน'; }

        $placeholders2 = array_fill(0, count($b2Cols), '?');
        $sqlIn2 = "INSERT INTO `budget_sources` (" . implode(', ', $b2Cols) . ") VALUES (" . implode(', ', $placeholders2) . ")";
        $stmtIn2 = $pdo->prepare($sqlIn2);
        $stmtIn2->execute($b2Vals);
    }

    // 3. ดึงค่า fiscal_years และหักค่าสาธารณูปโภค (utility_reserve)
    $stmtYear = $pdo->prepare("SELECT utility_reserve FROM fiscal_years WHERE id = ?");
    $stmtYear->execute([$fiscal_year_id]);
    $yearRow = $stmtYear->fetch();
    $utilityReserve = $yearRow ? (float)($yearRow['utility_reserve'] ?? 0) : 0;

    $allocatableBudget = max(0, $grandTotal - $utilityReserve);

    $stmtUpYear = $pdo->prepare("
        UPDATE fiscal_years 
        SET total_budget_base = ?, allocatable_budget = ? 
        WHERE id = ?
    ");
    $stmtUpYear->execute([$grandTotal, $allocatableBudget, $fiscal_year_id]);

    // 4. ปรับคำนวณ allocated_amount ใน department_allocations อัตโนมัติตาม %
    $stmtAllocs = $pdo->prepare("SELECT id, percentage FROM department_allocations WHERE fiscal_year_id = ?");
    $stmtAllocs->execute([$fiscal_year_id]);
    $allocList = $stmtAllocs->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($allocList)) {
        $stmtUpAlloc = $pdo->prepare("UPDATE department_allocations SET allocated_amount = ? WHERE id = ?");
        foreach ($allocList as $al) {
            $amt = ((float)$al['percentage'] / 100.0) * $allocatableBudget;
            $stmtUpAlloc->execute([$amt, $al['id']]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'นำยอดงบจากการคำนวณรายหัว ' . number_format($grandTotal, 2) . ' บาท ไปตั้งเป็นงบประมาณเรียบร้อยแล้ว',
        'grand_total' => $grandTotal,
        'utility_reserve' => $utilityReserve,
        'allocatable_budget' => $allocatableBudget
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
