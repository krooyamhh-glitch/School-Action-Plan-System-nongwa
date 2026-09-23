<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Only Admins or Academic staff can use this generalized fetch
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

$teacher_id = $_GET['teacher_id'] ?? null;
$classroom_id = $_GET['classroom_id'] ?? null;
$academic_year = $_GET['academic_year'] ?? '';
$semester = $_GET['semester'] ?? '';

if (empty($academic_year) || empty($semester)) {
    echo json_encode(['error' => 'ระบุปีการศึกษาและภาคเรียนให้ครบถ้วน']);
    exit;
}

try {
    $where = "t.academic_year = ? AND t.semester = ?";
    $params = [$academic_year, $semester];

    if ($teacher_id) {
        $where .= " AND t.teacher_id = ?";
        $params[] = $teacher_id;
    } elseif ($classroom_id) {
        $where .= " AND t.classroom_id = ?";
        $params[] = $classroom_id;
    } else {
        echo json_encode(['error' => 'ระบุครูหรือห้องเรียน']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT t.*, 
               s.name as subject_name, s.code as subject_code, 
               c.level, c.room,
               u.name as teacher_name, u.last_name as teacher_last_name
        FROM timetables t
        LEFT JOIN subjects s ON t.subject_id = s.id
        LEFT JOIN classrooms c ON t.classroom_id = c.id
        LEFT JOIN users u ON t.teacher_id = u.id
        WHERE $where
        ORDER BY t.day_of_week ASC, t.period_number ASC
    ");
    $stmt->execute($params);
    $timetable = $stmt->fetchAll();

    foreach ($timetable as &$t) {
        if (!empty($t['activity_type'])) {
            $activities = [
                'guidance' => ['name' => 'กิจกรรมแนะแนว', 'code' => 'แนะแนว'],
                'scouts' => ['name' => 'กิจกรรมลูกเสือเนตรนารี', 'code' => 'ลูกเสือเนตรนารี'],
                'scout' => ['name' => 'กิจกรรมลูกเสือเนตรนารี', 'code' => 'ลูกเสือเนตรนารี'],
                'club' => ['name' => 'กิจกรรมชุมนุม', 'code' => 'ชุมนุม'],
                'social' => ['name' => 'กิจกรรมเพื่อสังคมและสาธารณประโยชน์', 'code' => 'กิจกรรมเพื่อสังคมฯ'],
                'lunch' => ['name' => 'พักรับประทานอาหาร', 'code' => 'พักกลางวัน'],
                'homeroom' => ['name' => 'Home Room', 'code' => 'โฮมรูม'],
                'reducing_time' => ['name' => 'กิจกรรมลดเวลาเรียน เพิ่มเวลารู้', 'code' => 'ลดเวลาเรียนฯ'],
                'prayer' => ['name' => 'กิจกรรมสวดมนต์', 'code' => 'สวดมนต์']
            ];
            $act = $activities[strtolower($t['activity_type'])] ?? null;
            if ($act) {
                $t['subject_name'] = $act['name'];
                $t['subject_code'] = $act['code'];
            }
        }
    }

    echo json_encode($timetable);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
