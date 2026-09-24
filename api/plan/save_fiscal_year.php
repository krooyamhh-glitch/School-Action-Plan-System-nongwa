<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'], JSON_UNESCAPED_UNICODE);
    exit;
}

ensureDatabaseIntegrity($pdo);

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$action = trim($data['action'] ?? '');
$year_id = (int)($data['id'] ?? $data['year_id'] ?? 0);
$year = trim($data['year'] ?? '');
$start_date = trim($data['start_date'] ?? '');
$end_date = trim($data['end_date'] ?? '');
$is_current = !empty($data['is_current']) ? 1 : 0;
$status = trim($data['status'] ?? 'active');

try {
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

    // กรณีแก้ไขปีงบประมาณเดิม
    if ($year_id > 0) {
        $stmtUp = $pdo->prepare("
            UPDATE fiscal_years 
            SET year = ?, start_date = ?, end_date = ?, is_current = ?, status = ?
            WHERE id = ?
        ");
        $stmtUp->execute([$year, $start_date, $end_date, $is_current, $status, $year_id]);
        $targetYearId = $year_id;
    } else {
        // ตรวจสอบว่ามีปีนี้อยู่แล้วหรือไม่
        $stmtCheck = $pdo->prepare("SELECT id FROM fiscal_years WHERE year = ? LIMIT 1");
        $stmtCheck->execute([$year]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $targetYearId = (int)$existing['id'];
            $stmtUp = $pdo->prepare("
                UPDATE fiscal_years 
                SET start_date = ?, end_date = ?, is_current = ?, status = ?
                WHERE id = ?
            ");
            $stmtUp->execute([$start_date, $end_date, $is_current, $status, $targetYearId]);
        } else {
            $stmtIn = $pdo->prepare("
                INSERT INTO fiscal_years (
                    school_id, year, start_date, end_date, is_current, status, 
                    total_budget_base, utility_reserve, utility_reserve_notes, allocatable_budget
                ) VALUES (
                    1, ?, ?, ?, ?, ?,
                    0.00, 0.00, 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)', 0.00
                )
            ");
            $stmtIn->execute([$year, $start_date, $end_date, $is_current, $status]);
            $targetYearId = (int)$pdo->lastInsertId();
        }
    }

    // กำหนดสัดส่วน 5 ช่องงานมาตรฐาน หากยังไม่มีใน department_allocations
    $stmtAllocCheck = $pdo->prepare("SELECT COUNT(*) FROM department_allocations WHERE fiscal_year_id = ?");
    $stmtAllocCheck->execute([$targetYearId]);
    $allocCount = (int)$stmtAllocCheck->fetchColumn();

    if ($allocCount === 0) {
        $defaults = [
            ['academic', 'งานบริหารงานวิชาการ', 45.00, 'ยกระดับผลสัมฤทธิ์ทางการเรียนและพัฒนาคุณภาพผู้เรียน'],
            ['personnel', 'งานบุคคล', 10.00, 'พัฒนาครูและบุคลากรทางการศึกษา วินัยและมาตรฐานวิชาชีพ'],
            ['budget', 'งานงบประมาณ', 10.00, 'บริหารการเงิน บัญชี พัสดุและสินทรัพย์'],
            ['general', 'งานบริหารงานทั่วไป', 20.00, 'อาคารสถานที่ ความปลอดภัย สัมพันธ์ชุมชน และสิ่งแวดล้อม'],
            ['reserve', 'กันไว้สำหรับค่าใช้จ่ายอื่นๆ', 15.00, 'งบสำรองจ่ายกรณีฉุกเฉินและภารกิจเร่งด่วน']
        ];
        $stmt_alloc = $pdo->prepare("
            INSERT INTO department_allocations (
                school_id, fiscal_year_id, department, department_name, percentage, allocated_amount, notes
            ) VALUES (
                1, ?, ?, ?, ?, 0.00, ?
            )
        ");
        foreach ($defaults as $d) {
            $stmt_alloc->execute([$targetYearId, $d[0], $d[1], $d[2], $d[3]]);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'บันทึกปีงบประมาณ พ.ศ. ' . $year . ' เรียบร้อยแล้ว',
        'year_id' => $targetYearId
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการบันทึกปีงบประมาณ: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
