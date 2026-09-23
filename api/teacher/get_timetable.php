<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$is_admin_or_academic = ($_SESSION['role'] === 'admin' || (isset($_SESSION['is_academic']) && $_SESSION['is_academic'] == 1));
$teacher_id = $_SESSION['user_id'];
if ($is_admin_or_academic && isset($_GET['teacher_id']) && !empty($_GET['teacher_id'])) {
    $teacher_id = $_GET['teacher_id'];
}
$academic_year = $_GET['academic_year'] ?? '2567';
$semester = $_GET['semester'] ?? 1;

try {
    $stmt = $pdo->prepare('
        SELECT t.*, 
               s.name as subject_name, s.code as subject_code, 
               c.level, c.room
        FROM timetables t
        LEFT JOIN subjects s ON t.subject_id = s.id
        LEFT JOIN classrooms c ON t.classroom_id = c.id
        WHERE t.teacher_id = ? AND t.academic_year = ? AND t.semester = ?
        ORDER BY t.day_of_week ASC, t.period_number ASC
    ');
    $stmt->execute([$teacher_id, $academic_year, $semester]);
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
