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

// ตรวจสอบโครงสร้างตารางก่อนบันทึกข้อมูล ป้องกันข้อผิดพลาด column not found
ensureDatabaseIntegrity($pdo);

$data = json_decode(file_get_contents('php://input'), true);
$fiscal_year_id = (int)($data['fiscal_year_id'] ?? 0);
$rates = $data['rates'] ?? [];

if ($fiscal_year_id <= 0) {
    // Check if there is an active fiscal year
    $stmtY = $pdo->query("SELECT id FROM fiscal_years ORDER BY is_current DESC, id DESC LIMIT 1");
    $rowY = $stmtY->fetch();
    if ($rowY) {
        $fiscal_year_id = (int)$rowY['id'];
    } else {
        // Create default fiscal year 2568
        $stmtCreateY = $pdo->prepare("INSERT INTO fiscal_years (school_id, year, start_date, end_date, is_current, status) VALUES (1, '2568', '2024-10-01', '2025-09-30', 1, 'active')");
        $stmtCreateY->execute();
        $fiscal_year_id = (int)$pdo->lastInsertId();
    }
}

$levelNames = [
    'kindergarten' => 'ระดับก่อนประถมศึกษา (อนุบาล)',
    'primary' => 'ระดับประถมศึกษา (ป.1 - ป.6)',
    'lower_secondary' => 'ระดับมัธยมศึกษาตอนต้น (ม.1 - ม.3)',
    'upper_secondary' => 'ระดับมัธยมศึกษาตอนปลาย (ม.4 - ม.6)'
];

try {
    $pdo->beginTransaction();

    $totalStudentsAll = 0;
    $totalSubsidyAll = 0;
    $totalSmallSchoolAll = 0;
    $totalDevAll = 0;
    $grandTotalBudget = 0;

    $stmtUpsert = $pdo->prepare("
        INSERT INTO student_subsidies (
            school_id, fiscal_year_id, level_key, level_name,
            student_count, subsidy_rate, per_head_subsidy, small_school_subsidy, dev_rate, per_head_dev,
            total_subsidy_amount, total_dev_amount, total_amount
        ) VALUES (
            1, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?
        )
        ON DUPLICATE KEY UPDATE
            student_count = VALUES(student_count),
            subsidy_rate = VALUES(subsidy_rate),
            per_head_subsidy = VALUES(per_head_subsidy),
            small_school_subsidy = VALUES(small_school_subsidy),
            dev_rate = VALUES(dev_rate),
            per_head_dev = VALUES(per_head_dev),
            total_subsidy_amount = VALUES(total_subsidy_amount),
            total_dev_amount = VALUES(total_dev_amount),
            total_amount = VALUES(total_amount),
            updated_at = NOW()
    ");

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

        $stmtUpsert->execute([
            $fiscal_year_id,
            $lvlKey,
            $lvlName,
            $count,
            $subsidyRate,
            $subsidyRate, // per_head_subsidy
            $smallRate,
            $devRate,
            $devRate,     // per_head_dev
            $subTotal,
            $devTotal,
            $rowTotal
        ]);

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

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
