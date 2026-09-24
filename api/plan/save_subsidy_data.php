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
    ensureDatabaseIntegrity($pdo);

    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        $data = $_POST;
    }
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
            $stmtCreateY = $pdo->prepare("
                INSERT INTO fiscal_years (
                    school_id, year, year_be, start_date, end_date, is_current, status,
                    total_budget_base, utility_reserve, utility_reserve_notes, allocatable_budget
                ) VALUES (
                    1, '2568', 2568, '2024-10-01', '2025-09-30', 1, 'active',
                    0.00, 0.00, 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)', 0.00
                )
            ");
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

    $pdo->beginTransaction();

    $totalStudentsAll = 0;
    $totalSubsidyAll = 0;
    $totalSmallSchoolAll = 0;
    $totalDevAll = 0;
    $grandTotalBudget = 0;

    $stmtFind = $pdo->prepare("SELECT id FROM student_subsidies WHERE fiscal_year_id = ? AND level_key = ? LIMIT 1");
    $stmtUpdate = $pdo->prepare("
        UPDATE student_subsidies SET
            level_name = ?,
            student_count = ?,
            subsidy_rate = ?,
            per_head_subsidy = ?,
            small_school_subsidy = ?,
            dev_rate = ?,
            per_head_dev = ?,
            total_subsidy_amount = ?,
            total_dev_amount = ?,
            total_amount = ?
        WHERE id = ?
    ");
    $stmtInsert = $pdo->prepare("
        INSERT INTO student_subsidies (
            school_id, fiscal_year_id, level_key, level_name,
            student_count, subsidy_rate, per_head_subsidy, small_school_subsidy,
            dev_rate, per_head_dev, total_subsidy_amount, total_dev_amount, total_amount
        ) VALUES (
            1, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?
        )
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

        $stmtFind->execute([$fiscal_year_id, $lvlKey]);
        $existing = $stmtFind->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmtUpdate->execute([
                $lvlName,
                $count,
                $subsidyRate,
                $subsidyRate,
                $smallRate,
                $devRate,
                $devRate,
                $subTotal,
                $devTotal,
                $rowTotal,
                $existing['id']
            ]);
        } else {
            $stmtInsert->execute([
                $fiscal_year_id,
                $lvlKey,
                $lvlName,
                $count,
                $subsidyRate,
                $subsidyRate,
                $smallRate,
                $devRate,
                $devRate,
                $subTotal,
                $devTotal,
                $rowTotal
            ]);
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
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
