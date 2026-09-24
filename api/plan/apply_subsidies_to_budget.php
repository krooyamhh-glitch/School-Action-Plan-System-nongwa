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

    // 1. ดึงยอดรวมจาก student_subsidies
    $stmtSub = $pdo->prepare("
        SELECT 
            COALESCE(SUM(student_count), 0) as total_students,
            COALESCE(SUM(student_count * subsidy_rate), 0) as normal_subsidy,
            COALESCE(SUM(student_count * small_school_subsidy), 0) as small_school_subsidy,
            COALESCE(SUM(total_subsidy_amount), 0) as total_subsidy,
            COALESCE(SUM(total_dev_amount), 0) as total_dev,
            COALESCE(SUM(total_amount), 0) as grand_total
        FROM student_subsidies
        WHERE fiscal_year_id = ?
    ");
    $stmtSub->execute([$fiscal_year_id]);
    $subData = $stmtSub->fetch(PDO::FETCH_ASSOC);

    $totalStudents = (int)$subData['total_students'];
    $totalSubsidy = (float)$subData['total_subsidy'];
    $totalDev = (float)$subData['total_dev'];
    $grandTotal = (float)$subData['grand_total'];

    // 2. อัปเดตหรือเพิ่มใน budget_sources สำหรับปีงบประมาณนี้
    // แหล่งที่ 1: เงินอุดหนุนรายหัว (รวมเงินเพิ่ม รร. ขนาดเล็ก)
    $stmtSrc1 = $pdo->prepare("
        SELECT id FROM budget_sources 
        WHERE fiscal_year_id = ? AND category = 'subsidy' 
        LIMIT 1
    ");
    $stmtSrc1->execute([$fiscal_year_id]);
    $src1 = $stmtSrc1->fetch();

    $subsidyName = 'เงินอุดหนุนรายหัวจัดการศึกษาขั้นพื้นฐาน';
    if ((float)$subData['small_school_subsidy'] > 0) {
        $subsidyName .= ' (รวมเงินเพิ่ม รร.ขนาดเล็ก ' . number_format($subData['small_school_subsidy'], 2) . ' บ.)';
    }

    if ($src1) {
        $stmtUp1 = $pdo->prepare("UPDATE budget_sources SET name = ?, amount = ? WHERE id = ?");
        $stmtUp1->execute([$subsidyName, $totalSubsidy, $src1['id']]);
    } else {
        $stmtIn1 = $pdo->prepare("INSERT INTO budget_sources (school_id, fiscal_year_id, code, name, category, amount) VALUES (1, ?, 'SUB-01', ?, 'subsidy', ?)");
        $stmtIn1->execute([$fiscal_year_id, $subsidyName, $totalSubsidy]);
    }

    // แหล่งที่ 2: เงินกิจกรรมพัฒนาคุณภาพผู้เรียน (กพพ.)
    $stmtSrc2 = $pdo->prepare("
        SELECT id FROM budget_sources 
        WHERE fiscal_year_id = ? AND category = 'student_dev' 
        LIMIT 1
    ");
    $stmtSrc2->execute([$fiscal_year_id]);
    $src2 = $stmtSrc2->fetch();

    if ($src2) {
        $stmtUp2 = $pdo->prepare("UPDATE budget_sources SET amount = ? WHERE id = ?");
        $stmtUp2->execute([$totalDev, $src2['id']]);
    } else {
        $stmtIn2 = $pdo->prepare("INSERT INTO budget_sources (school_id, fiscal_year_id, code, name, category, amount) VALUES (1, ?, 'DEV-01', 'เงินกิจกรรมพัฒนาคุณภาพผู้เรียน (กพพ. 4 กิจกรรมหลัก)', 'student_dev', ?)");
        $stmtIn2->execute([$fiscal_year_id, $totalDev]);
    }

    // 3. ดึงค่า fiscal_years และหักค่าสาธารณูปโภค (utility_reserve)
    $stmtYear = $pdo->prepare("SELECT utility_reserve FROM fiscal_years WHERE id = ?");
    $stmtYear->execute([$fiscal_year_id]);
    $yearRow = $stmtYear->fetch();
    $utilityReserve = $yearRow ? (float)$yearRow['utility_reserve'] : 0;

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
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
