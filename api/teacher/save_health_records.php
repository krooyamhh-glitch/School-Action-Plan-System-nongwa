<?php
session_start();
require_once '../config.php';
require_once 'growth_calc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$teacher_id = $_SESSION['user_id'];
$classroom_id = $data['classroom_id'] ?? null;
$academic_year = $data['academic_year'] ?? '';
$semester = $data['semester'] ?? 1;
$record_number = $data['record_number'] ?? 1;
$records = $data['records'] ?? [];
$recorded_date = $data['recorded_date'] ?? date('Y-m-d');

if (!$classroom_id || empty($academic_year) || empty($records)) {
    echo json_encode(['error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    $pdo->beginTransaction();

    // เตรียมคำสั่งดึงข้อมูลนักเรียนเพื่อใช้คำนวณอายุและเพศ
    $stmt_student = $pdo->prepare('SELECT gender, birthday FROM students WHERE id = ?');

    $stmt = $pdo->prepare('
        INSERT INTO student_health_records 
        (student_id, classroom_id, academic_year, semester, record_number, weight, height, weight_age_result, height_age_result, weight_height_result, recorded_date, teacher_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        weight = VALUES(weight),
        height = VALUES(height),
        weight_age_result = VALUES(weight_age_result),
        height_age_result = VALUES(height_age_result),
        weight_height_result = VALUES(weight_height_result),
        recorded_date = VALUES(recorded_date),
        teacher_id = VALUES(teacher_id)
    ');

    foreach ($records as $r) {
        $weight = floatval($r['weight'] ?? 0);
        $height = floatval($r['height'] ?? 0);
        
        $weight_age_result = '-';
        $height_age_result = '-';
        $weight_height_result = '-';

        if ($weight > 0 || $height > 0) {
            // ดึงข้อมูลนักเรียนมาคำนวณอายุ
            $stmt_student->execute([$r['student_id']]);
            $student = $stmt_student->fetch();
            
            if ($student && !empty($student['birthday'])) {
                $birthDate = new DateTime($student['birthday']);
                $checkDate = new DateTime($recorded_date);
                $diff = $checkDate->diff($birthDate);
                $ageMonths = ($diff->y * 12) + $diff->m;
                $gender = $student['gender'];

                if ($weight > 0) {
                    $weight_age_result = GrowthCalc::getWeightForAge($gender, $ageMonths, $weight);
                }
                if ($height > 0) {
                    $height_age_result = GrowthCalc::getHeightForAge($gender, $ageMonths, $height);
                }
                if ($weight > 0 && $height > 0) {
                    $weight_height_result = GrowthCalc::getWeightForHeight($gender, $ageMonths, $weight, $height);
                }
            }
        }

        $stmt->execute([
            $r['student_id'],
            $classroom_id,
            $academic_year,
            $semester,
            $record_number,
            $weight,
            $height,
            $weight_age_result,
            $height_age_result,
            $weight_height_result,
            $recorded_date,
            $teacher_id
        ]);

        // Update main students table with the latest record
        $stmt_upd = $pdo->prepare("UPDATE students SET weight = ?, height = ? WHERE id = ?");
        $stmt_upd->execute([$weight, $height, $r['student_id']]);
    }

    $pdo->commit();
    echo json_encode(['message' => 'บันทึกข้อมูลและประเมินภาวะโภชนาการเรียบร้อยแล้ว']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
