<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // ตรวจสอบโครงสร้างตารางก่อนบันทึกข้อมูล ป้องกันข้อผิดพลาด column not found
    if (function_exists('ensureDatabaseIntegrity')) {
        ensureDatabaseIntegrity($pdo);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        $data = $_POST;
    }
    $fiscal_year_id = (int)($data['fiscal_year_id'] ?? 0);
    $rates = $data['rates'] ?? [];

    // ตรวจสอบความถูกต้องของ fiscal_year_id และความสัมพันธ์ Foreign Key
    $validYear = null;
    if ($fiscal_year_id > 0) {
        $stmtCheckFy = $pdo->prepare("SELECT id FROM `fiscal_years` WHERE id = ? LIMIT 1");
        $stmtCheckFy->execute([$fiscal_year_id]);
        $validYear = $stmtCheckFy->fetch();
    }

    if (!$validYear) {
        // หาปีงบประมาณที่มีอยู่จริงในระบบ
        $stmtY = $pdo->query("SELECT id FROM `fiscal_years` ORDER BY is_current DESC, id DESC LIMIT 1");
        $validYear = $stmtY ? $stmtY->fetch() : null;
        if ($validYear) {
            $fiscal_year_id = (int)$validYear['id'];
        } else {
            // สร้างปีงบประมาณเริ่มต้น พ.ศ. 2568
            $fyCols = getTableColumns($pdo, 'fiscal_years');
            $insCols = ["`start_date`", "`end_date`", "`is_current`", "`status`"];
            $insPlaceholders = ["?", "?", "?", "?"];
            $insVals = ['2024-10-01', '2025-09-30', 1, 'active'];

            if (in_array('school_id', $fyCols)) {
                $insCols[] = "`school_id`"; $insPlaceholders[] = "?"; $insVals[] = 1;
            }
            if (in_array('year', $fyCols)) {
                $insCols[] = "`year`"; $insPlaceholders[] = "?"; $insVals[] = '2568';
            }
            if (in_array('year_be', $fyCols)) {
                $insCols[] = "`year_be`"; $insPlaceholders[] = "?"; $insVals[] = 2568;
            }

            $sqlCreateY = "INSERT INTO `fiscal_years` (" . implode(', ', $insCols) . ") VALUES (" . implode(', ', $insPlaceholders) . ")";
            $stmtCreateY = $pdo->prepare($sqlCreateY);
            $stmtCreateY->execute($insVals);
            $fiscal_year_id = (int)$pdo->lastInsertId();
        }
    }

    // ตรวจสอบโรงเรียน id=1 สำหรับ Foreign Key
    try {
        $schoolCols = getTableColumns($pdo, 'schools');
        if (!empty($schoolCols)) {
            $schoolCheck = $pdo->query("SELECT id FROM `schools` WHERE id = 1 LIMIT 1")->fetch();
            if (!$schoolCheck) {
                $pdo->exec("INSERT INTO `schools` (`id`, `smis_code`, `code`, `name`) VALUES (1, '10310001', '10310001', 'โรงเรียน')");
            }
        }
    } catch (\Throwable $eSch) {}

    $levelNames = [
        'kindergarten' => 'ระดับก่อนประถมศึกษา (อนุบาล)',
        'primary' => 'ระดับประถมศึกษา (ป.1 - ป.6)',
        'lower_secondary' => 'ระดับมัธยมศึกษาตอนต้น (ม.1 - ม.3)',
        'upper_secondary' => 'ระดับมัธยมศึกษาตอนปลาย (ม.4 - ม.6)'
    ];

    $subCols = getTableColumns($pdo, 'student_subsidies');
    $hasSchoolId = in_array('school_id', $subCols);
    $hasPerHeadSubsidy = in_array('per_head_subsidy', $subCols);
    $hasSmallSchool = in_array('small_school_subsidy', $subCols);
    $hasDevRate = in_array('dev_rate', $subCols);
    $hasPerHeadDev = in_array('per_head_dev', $subCols);
    $hasTotalSubsidy = in_array('total_subsidy_amount', $subCols);
    $hasTotalDev = in_array('total_dev_amount', $subCols);
    $hasTotalAmount = in_array('total_amount', $subCols);

    $pdo->beginTransaction();

    $totalStudentsAll = 0;
    $totalSubsidyAll = 0;
    $totalSmallSchoolAll = 0;
    $totalDevAll = 0;
    $grandTotalBudget = 0;

    $stmtFind = $pdo->prepare("SELECT id FROM `student_subsidies` WHERE fiscal_year_id = ? AND level_key = ? LIMIT 1");

    foreach ($levelNames as $lvlKey => $lvlName) {
        $lvlData = $rates[$lvlKey] ?? [];
        $count = (int)($lvlData['student_count'] ?? 0);
        $subsidyRate = (float)($lvlData['subsidy_rate'] ?? 0);
        $smallRate = (float)($lvlData['small_school_subsidy'] ?? $lvlData['small_school_rate'] ?? 0);
        $devRate = (float)($lvlData['student_dev_rate'] ?? $lvlData['dev_rate'] ?? 0);

        // คำนวณเงินอุดหนุนรวม (เงินอุดหนุนปกติ + เงินเพิ่มโรงเรียนขนาดเล็ก)
        $subTotal = $count * ($subsidyRate + $smallRate);
        $smallTotal = $count * $smallRate;
        $devTotal = $count * $devRate;
        $rowTotal = $subTotal + $devTotal;

        $stmtFind->execute([$fiscal_year_id, $lvlKey]);
        $existing = $stmtFind->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $upFields = [];
            $upParams = [];

            if (in_array('level_name', $subCols)) { $upFields[] = "`level_name` = ?"; $upParams[] = $lvlName; }
            if (in_array('student_count', $subCols)) { $upFields[] = "`student_count` = ?"; $upParams[] = $count; }
            if (in_array('subsidy_rate', $subCols)) { $upFields[] = "`subsidy_rate` = ?"; $upParams[] = $subsidyRate; }
            if ($hasPerHeadSubsidy) { $upFields[] = "`per_head_subsidy` = ?"; $upParams[] = $subsidyRate; }
            if ($hasSmallSchool) { $upFields[] = "`small_school_subsidy` = ?"; $upParams[] = $smallRate; }
            if ($hasDevRate) { $upFields[] = "`dev_rate` = ?"; $upParams[] = $devRate; }
            if ($hasPerHeadDev) { $upFields[] = "`per_head_dev` = ?"; $upParams[] = $devRate; }
            if ($hasTotalSubsidy) { $upFields[] = "`total_subsidy_amount` = ?"; $upParams[] = $subTotal; }
            if ($hasTotalDev) { $upFields[] = "`total_dev_amount` = ?"; $upParams[] = $devTotal; }
            if ($hasTotalAmount) { $upFields[] = "`total_amount` = ?"; $upParams[] = $rowTotal; }

            $upParams[] = $existing['id'];
            $sqlUp = "UPDATE `student_subsidies` SET " . implode(', ', $upFields) . " WHERE id = ?";
            $stmtUp = $pdo->prepare($sqlUp);
            $stmtUp->execute($upParams);
        } else {
            $inCols = ["`fiscal_year_id`", "`level_key`"];
            $inParams = [$fiscal_year_id, $lvlKey];

            if ($hasSchoolId) { $inCols[] = "`school_id`"; $inParams[] = 1; }
            if (in_array('level_name', $subCols)) { $inCols[] = "`level_name`"; $inParams[] = $lvlName; }
            if (in_array('student_count', $subCols)) { $inCols[] = "`student_count`"; $inParams[] = $count; }
            if (in_array('subsidy_rate', $subCols)) { $inCols[] = "`subsidy_rate`"; $inParams[] = $subsidyRate; }
            if ($hasPerHeadSubsidy) { $inCols[] = "`per_head_subsidy`"; $inParams[] = $subsidyRate; }
            if ($hasSmallSchool) { $inCols[] = "`small_school_subsidy`"; $inParams[] = $smallRate; }
            if ($hasDevRate) { $inCols[] = "`dev_rate`"; $inParams[] = $devRate; }
            if ($hasPerHeadDev) { $inCols[] = "`per_head_dev`"; $inParams[] = $devRate; }
            if ($hasTotalSubsidy) { $inCols[] = "`total_subsidy_amount`"; $inParams[] = $subTotal; }
            if ($hasTotalDev) { $inCols[] = "`total_dev_amount`"; $inParams[] = $devTotal; }
            if ($hasTotalAmount) { $inCols[] = "`total_amount`"; $inParams[] = $rowTotal; }

            $placeholders = array_fill(0, count($inCols), '?');
            $sqlIn = "INSERT INTO `student_subsidies` (" . implode(', ', $inCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmtIn = $pdo->prepare($sqlIn);
            $stmtIn->execute($inParams);
        }

        $totalStudentsAll += $count;
        $totalSubsidyAll += ($count * $subsidyRate);
        $totalSmallSchoolAll += $smallTotal;
        $totalDevAll += $devTotal;
        $grandTotalBudget += $rowTotal;
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'บันทึกข้อมูลจำนวนนักเรียนและอัตราเงินอุดหนุนรายหัวเรียบร้อยแล้ว',
        'summary' => [
            'total_students' => $totalStudentsAll,
            'is_small_school' => ($totalStudentsAll > 0 && $totalStudentsAll < 120),
            'total_normal_subsidy' => $totalSubsidyAll,
            'total_small_school_subsidy' => $totalSmallSchoolAll,
            'total_subsidy_combined' => ($totalSubsidyAll + $totalSmallSchoolAll),
            'total_dev_amount' => $totalDevAll,
            'grand_total_budget' => $grandTotalBudget
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
