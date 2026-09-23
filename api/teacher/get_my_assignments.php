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
        SELECT ta.id as assignment_id, s.id as subject_id, s.code as subject_code, s.code, s.name as subject_name, s.level, c.id as classroom_id, c.room
        FROM teacher_assignments ta
        JOIN subjects s ON ta.subject_id = s.id
        LEFT JOIN classrooms c ON ta.classroom_id = c.id
        WHERE ta.teacher_id = ? AND ta.academic_year = ? AND ta.semester = ?
        ORDER BY s.level ASC, c.room ASC
    ');
    $stmt->execute([$teacher_id, $academic_year, $semester]);
    $assignments = $stmt->fetchAll();

    // ดึงงานกิจกรรมพัฒนาผู้เรียน
    $stmt_ld = $pdo->prepare('
        SELECT lda.classroom_id, c.level, c.room
        FROM learner_development_assignments lda
        JOIN classrooms c ON lda.classroom_id = c.id
        WHERE lda.teacher_id = ? AND lda.academic_year = ? AND lda.semester = ?
    ');
    $stmt_ld->execute([$teacher_id, $academic_year, $semester]);
    $ld_assignments = $stmt_ld->fetchAll();

    foreach ($ld_assignments as $ld) {
        $activities = [
            ['type' => 'guidance', 'name' => 'กิจกรรมแนะแนว', 'code' => 'แนะแนว'],
            ['type' => 'scouts', 'name' => 'กิจกรรมลูกเสือเนตรนารี', 'code' => 'ลูกเสือเนตรนารี'],
            ['type' => 'club', 'name' => 'กิจกรรมชุมนุม', 'code' => 'ชุมนุม'],
            ['type' => 'social', 'name' => 'กิจกรรมเพื่อสังคมฯ', 'code' => 'สังคมฯ']
        ];

        foreach ($activities as $act) {
            $assignments[] = [
                'assignment_id' => null,
                'subject_id' => 'LD:' . $act['type'], // ใช้ prefix เพื่อแยกแยะใน frontend
                'subject_code' => $act['code'],
                'code' => $act['code'],
                'subject_name' => $act['name'],
                'level' => $ld['level'],
                'classroom_id' => $ld['classroom_id'],
                'room' => $ld['room']
            ];
        }
    }

    echo json_encode($assignments);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
