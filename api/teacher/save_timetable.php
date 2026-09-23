<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$is_admin_or_academic = ($_SESSION['role'] === 'admin' || (isset($_SESSION['is_academic']) && $_SESSION['is_academic'] == 1));
$teacher_id = $_SESSION['user_id'];
if ($is_admin_or_academic && isset($data['teacher_id']) && !empty($data['teacher_id'])) {
    $teacher_id = $data['teacher_id'];
}
$academic_year = $data['academic_year'] ?? '';
$semester = $data['semester'] ?? 1;
$day_of_week = $data['day_of_week'] ?? null;
$period_number = $data['period_number'] ?? null;
$subject_id = $data['subject_id'] ?? null;
$classroom_id = $data['classroom_id'] ?? null;

if (!$day_of_week || !$period_number || !$academic_year) {
    echo json_encode(['error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    // 💡 รัน Migration ดัชนี Unique ของ timetables ให้รวม teacher_id ด้วย เพื่อให้หลายคนจัดซ้ำห้องเรียน / กิจกรรมเดียวกันได้
    try {
        $stmt_idx = $pdo->query("SHOW INDEX FROM timetables WHERE Key_name = 'unique_timetable'");
        $indices = $stmt_idx->fetchAll();
        $has_teacher_id = false;
        foreach ($indices as $idx) {
            $col_name = $idx['Column_name'] ?? $idx['column_name'] ?? $idx['COLUMN_NAME'] ?? null;
            if ($col_name === 'teacher_id') {
                $has_teacher_id = true;
                break;
            }
        }
        if (!$has_teacher_id) {
            // 1. เพิ่มดัชนีชั่วคราวเพื่อปลดล็อก Foreign Key ของ classroom_id ใน MySQL
            try {
                $pdo->exec("ALTER TABLE timetables ADD INDEX temp_classroom_idx (classroom_id)");
            } catch (PDOException $e) {}

            // 2. ลบดัชนี unique_timetable เดิมทางอ้อม
            try {
                $pdo->exec("ALTER TABLE timetables DROP INDEX unique_timetable");
            } catch (PDOException $e) {}
            try {
                $pdo->exec("ALTER TABLE timetables DROP INDEX unique_timetable_new");
            } catch (PDOException $e) {}

            // 3. สร้างดัชนีใหม่ที่รวม teacher_id และมี classroom_id อยู่คอลัมน์แรกเพื่อช่วยเรื่อง Foreign Key คืน
            try {
                $pdo->exec("ALTER TABLE timetables ADD UNIQUE KEY unique_timetable (classroom_id, teacher_id, academic_year, semester, day_of_week, period_number)");
            } catch (PDOException $e) {
                try {
                    $pdo->exec("ALTER TABLE timetables ADD UNIQUE KEY unique_timetable_new (classroom_id, teacher_id, academic_year, semester, day_of_week, period_number)");
                } catch (PDOException $e_inner) {}
            }

            // 4. ลบดัชนีชั่วคราวออกเมื่อตัวหลักกลับมาคุมเรียบร้อยแล้ว
            try {
                $pdo->exec("ALTER TABLE timetables DROP INDEX temp_classroom_idx");
            } catch (PDOException $e) {}
        }
    } catch (PDOException $ex) {
        // จัดการอย่างปลอดภัย
    }

    // ถอดข้อมูลรายวิชาแบบอาร์เรย์ (รองรับการควบชั้น) หรือแบบค่าเดี่ยว
    $assignments = $data['assignments'] ?? null;
    if ($assignments === null) {
        if ($subject_id !== null) {
            $assignments = [
                ['subject_id' => $subject_id, 'classroom_id' => $classroom_id]
            ];
        } else {
            $assignments = [];
        }
    }

    if (empty($assignments)) {
        // ลบข้อมูลทั้งหมดที่มีสำหรับคาบสอนนี้ของคุณครูท่านนี้
        $stmt = $pdo->prepare('DELETE FROM timetables WHERE teacher_id = ? AND academic_year = ? AND semester = ? AND day_of_week = ? AND period_number = ?');
        $stmt->execute([$teacher_id, $academic_year, $semester, $day_of_week, $period_number]);
    } else {
        // ชำระข้อมูลเดิมของคุณครูคนนี้ในคาบเวลานี้ออกให้หมดก่อน แล้วเขียนชุดใหม่ลงไปแทน
        $stmt = $pdo->prepare('DELETE FROM timetables WHERE teacher_id = ? AND academic_year = ? AND semester = ? AND day_of_week = ? AND period_number = ?');
        $stmt->execute([$teacher_id, $academic_year, $semester, $day_of_week, $period_number]);

        foreach ($assignments as $item) {
            $sub_id = $item['subject_id'] ?? null;
            $class_id = $item['classroom_id'] ?? null;
            
            if ($sub_id === null) continue;

            $activity_type = null;
            $real_subject_id = $sub_id;

            // ตรวจสอบว่าเป็นกิจกรรมพัฒนาผู้เรียนหรือไม่
            if (is_string($sub_id) && strpos($sub_id, 'LD:') === 0) {
                $activity_type = str_replace('LD:', '', $sub_id);
                $real_subject_id = null;
            }

            $stmt = $pdo->prepare('
                INSERT INTO timetables (teacher_id, subject_id, activity_type, classroom_id, academic_year, semester, day_of_week, period_number)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$teacher_id, $real_subject_id, $activity_type, $class_id, $academic_year, $semester, $day_of_week, $period_number]);
        }
    }

    echo json_encode(['message' => 'บันทึกตารางสอนเรียบร้อยแล้ว']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
