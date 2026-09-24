<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if (function_exists('ensureDatabaseIntegrity')) {
        ensureDatabaseIntegrity($pdo);
    }

    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = trim($data['action'] ?? '');
    $year_id = (int)($data['id'] ?? $data['year_id'] ?? 0);
    $year = trim($data['year'] ?? '');
    $start_date = trim($data['start_date'] ?? '');
    $end_date = trim($data['end_date'] ?? '');
    $is_current = !empty($data['is_current']) ? 1 : 0;
    $status = trim($data['status'] ?? 'active');

    // กรณีเลือกตั้งให้เป็นปีปัจจุบันทันที (Quick Switch Current Year)
    if ($action === 'set_current' && $year_id > 0) {
        $pdo->query("UPDATE fiscal_years SET is_current = 0");
        $stmtSet = $pdo->prepare("UPDATE fiscal_years SET is_current = 1, status = 'active' WHERE id = ?");
        $stmtSet->execute([$year_id]);

        echo json_encode([
            'status' => 'success',
            'message' => 'ตั้งเป็นปีงบประมาณปัจจุบันเรียบร้อยแล้ว',
            'year_id' => $year_id
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($year) && $year_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุปีงบประมาณ (เช่น 2568, 2569)'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // คำนวณวันเริ่มต้น - สิ้นสุดปีงบประมาณตามเกณฑ์ราชการไทย (1 ต.ค. ปีก่อนหน้า ถึง 30 ก.ย. ปีงบ)
    if (!empty($year)) {
        $intYear = (int)$year;
        if ($intYear > 2400) {
            $adYear = $intYear - 543;
        } else {
            $adYear = $intYear;
        }
        if (empty($start_date)) {
            $start_date = ($adYear - 1) . '-10-01';
        }
        if (empty($end_date)) {
            $end_date = $adYear . '-09-30';
        }
    }

    if ($is_current == 1) {
        $pdo->query("UPDATE fiscal_years SET is_current = 0");
    }

    $fyCols = getTableColumns($pdo, 'fiscal_years');
    $hasYear = in_array('year', $fyCols);
    $hasYearBe = in_array('year_be', $fyCols);
    $hasSchoolId = in_array('school_id', $fyCols);
    $yearBe = !empty($year) ? (int)$year : 2568;

    // กรณีแก้ไขปีงบประมาณเดิม
    if ($year_id > 0) {
        $updateFields = [];
        $updateParams = [];

        if ($hasYear) {
            $updateFields[] = "`year` = ?";
            $updateParams[] = $year;
        }
        if ($hasYearBe) {
            $updateFields[] = "`year_be` = ?";
            $updateParams[] = $yearBe;
        }
        if (in_array('start_date', $fyCols)) {
            $updateFields[] = "`start_date` = ?";
            $updateParams[] = $start_date;
        }
        if (in_array('end_date', $fyCols)) {
            $updateFields[] = "`end_date` = ?";
            $updateParams[] = $end_date;
        }
        if (in_array('is_current', $fyCols)) {
            $updateFields[] = "`is_current` = ?";
            $updateParams[] = $is_current;
        }
        if (in_array('status', $fyCols)) {
            $updateFields[] = "`status` = ?";
            $updateParams[] = $status;
        }

        $updateParams[] = $year_id;
        $sqlUp = "UPDATE `fiscal_years` SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmtUp = $pdo->prepare($sqlUp);
        $stmtUp->execute($updateParams);
        $targetYearId = $year_id;
    } else {
        // ตรวจสอบว่ามีปีนี้อยู่แล้วหรือไม่
        $existing = null;
        if ($hasYear && $hasYearBe) {
            $stmtCheck = $pdo->prepare("SELECT id FROM `fiscal_years` WHERE `year` = ? OR `year_be` = ? LIMIT 1");
            $stmtCheck->execute([$year, $yearBe]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        } elseif ($hasYear) {
            $stmtCheck = $pdo->prepare("SELECT id FROM `fiscal_years` WHERE `year` = ? LIMIT 1");
            $stmtCheck->execute([$year]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        } elseif ($hasYearBe) {
            $stmtCheck = $pdo->prepare("SELECT id FROM `fiscal_years` WHERE `year_be` = ? LIMIT 1");
            $stmtCheck->execute([$yearBe]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        }

        if ($existing) {
            $targetYearId = (int)$existing['id'];
            $updateFields = [];
            $updateParams = [];

            if ($hasYear) {
                $updateFields[] = "`year` = ?";
                $updateParams[] = $year;
            }
            if ($hasYearBe) {
                $updateFields[] = "`year_be` = ?";
                $updateParams[] = $yearBe;
            }
            if (in_array('start_date', $fyCols)) {
                $updateFields[] = "`start_date` = ?";
                $updateParams[] = $start_date;
            }
            if (in_array('end_date', $fyCols)) {
                $updateFields[] = "`end_date` = ?";
                $updateParams[] = $end_date;
            }
            if (in_array('is_current', $fyCols)) {
                $updateFields[] = "`is_current` = ?";
                $updateParams[] = $is_current;
            }
            if (in_array('status', $fyCols)) {
                $updateFields[] = "`status` = ?";
                $updateParams[] = $status;
            }

            $updateParams[] = $targetYearId;
            $sqlUp = "UPDATE `fiscal_years` SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmtUp = $pdo->prepare($sqlUp);
            $stmtUp->execute($updateParams);
        } else {
            $insCols = [];
            $insPlaceholders = [];
            $insValues = [];

            if ($hasSchoolId) {
                $insCols[] = "`school_id`";
                $insPlaceholders[] = "?";
                $insValues[] = 1;
            }
            if ($hasYear) {
                $insCols[] = "`year`";
                $insPlaceholders[] = "?";
                $insValues[] = $year;
            }
            if ($hasYearBe) {
                $insCols[] = "`year_be`";
                $insPlaceholders[] = "?";
                $insValues[] = $yearBe;
            }
            if (in_array('start_date', $fyCols)) {
                $insCols[] = "`start_date`";
                $insPlaceholders[] = "?";
                $insValues[] = $start_date;
            }
            if (in_array('end_date', $fyCols)) {
                $insCols[] = "`end_date`";
                $insPlaceholders[] = "?";
                $insValues[] = $end_date;
            }
            if (in_array('is_current', $fyCols)) {
                $insCols[] = "`is_current`";
                $insPlaceholders[] = "?";
                $insValues[] = $is_current;
            }
            if (in_array('status', $fyCols)) {
                $insCols[] = "`status`";
                $insPlaceholders[] = "?";
                $insValues[] = $status;
            }
            if (in_array('total_budget_base', $fyCols)) {
                $insCols[] = "`total_budget_base`";
                $insPlaceholders[] = "?";
                $insValues[] = 0.00;
            }
            if (in_array('utility_reserve', $fyCols)) {
                $insCols[] = "`utility_reserve`";
                $insPlaceholders[] = "?";
                $insValues[] = 0.00;
            }
            if (in_array('utility_reserve_notes', $fyCols)) {
                $insCols[] = "`utility_reserve_notes`";
                $insPlaceholders[] = "?";
                $insValues[] = 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)';
            }
            if (in_array('allocatable_budget', $fyCols)) {
                $insCols[] = "`allocatable_budget`";
                $insPlaceholders[] = "?";
                $insValues[] = 0.00;
            }

            $sqlIns = "INSERT INTO `fiscal_years` (" . implode(', ', $insCols) . ") VALUES (" . implode(', ', $insPlaceholders) . ")";
            $stmtIn = $pdo->prepare($sqlIns);
            $stmtIn->execute($insValues);
            $targetYearId = (int)$pdo->lastInsertId();
        }
    }

    // กำหนดสัดส่วน 5 ช่องงานมาตรฐาน หากยังไม่มีใน department_allocations
    try {
        $stmtAllocCheck = $pdo->prepare("SELECT COUNT(*) FROM `department_allocations` WHERE `fiscal_year_id` = ?");
        $stmtAllocCheck->execute([$targetYearId]);
        $allocCount = (int)$stmtAllocCheck->fetchColumn();

        if ($allocCount === 0) {
            $allocCols = getTableColumns($pdo, 'department_allocations');
            $hasAllocSchool = in_array('school_id', $allocCols);
            $hasAllocNotes = in_array('notes', $allocCols);
            $hasDeptName = in_array('department_name', $allocCols);

            $defaults = [
                ['academic', 'งานบริหารงานวิชาการ', 45.00, 'ยกระดับผลสัมฤทธิ์ทางการเรียนและพัฒนาคุณภาพผู้เรียน'],
                ['personnel', 'งานบุคคล', 10.00, 'พัฒนาครูและบุคลากรทางการศึกษา วินัยและมาตรฐานวิชาชีพ'],
                ['budget', 'งานงบประมาณ', 10.00, 'บริหารการเงิน บัญชี พัสดุและสินทรัพย์'],
                ['general', 'งานบริหารงานทั่วไป', 20.00, 'อาคารสถานที่ ความปลอดภัย สัมพันธ์ชุมชน และสิ่งแวดล้อม'],
                ['reserve', 'กันไว้สำหรับค่าใช้จ่ายอื่นๆ', 15.00, 'งบสำรองจ่ายกรณีฉุกเฉินและภารกิจเร่งด่วน']
            ];

            foreach ($defaults as $d) {
                $daCols = ["`fiscal_year_id`", "`department`", "`percentage`", "`allocated_amount`"];
                $daVals = [$targetYearId, $d[0], $d[2], 0.00];

                if ($hasAllocSchool) {
                    $daCols[] = "`school_id`";
                    $daVals[] = 1;
                }
                if ($hasDeptName) {
                    $daCols[] = "`department_name`";
                    $daVals[] = $d[1];
                }
                if ($hasAllocNotes) {
                    $daCols[] = "`notes`";
                    $daVals[] = $d[3];
                }

                $placeholders = array_fill(0, count($daCols), '?');
                $sqlDa = "INSERT INTO `department_allocations` (" . implode(', ', $daCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmtDa = $pdo->prepare($sqlDa);
                $stmtDa->execute($daVals);
            }
        }
    } catch (\Throwable $eAlloc) {
        error_log("Alloc default warning: " . $eAlloc->getMessage());
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'บันทึกปีงบประมาณ พ.ศ. ' . $year . ' เรียบร้อยแล้ว',
        'year_id' => $targetYearId
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการบันทึกปีงบประมาณ: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
