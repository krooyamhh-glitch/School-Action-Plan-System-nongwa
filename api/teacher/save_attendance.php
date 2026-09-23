<?php
session_start();
require_once '../config.php';
require_once '../utils/telegram_notify.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$teacher_id = $_SESSION['user_id'];
$classroom_id = $data['classroom_id'] ?? null;
$check_date = $data['check_date'] ?? date('Y-m-d');
$academic_year = $data['academic_year'] ?? '';
$semester = $data['semester'] ?? 1;
$records = $data['records'] ?? []; // [{student_id, subject_id, period_number, status}]

if (!$classroom_id || empty($academic_year) || empty($records)) {
    echo json_encode(['error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('
        INSERT INTO attendance 
        (student_id, subject_id, activity_type, classroom_id, academic_year, semester, check_date, period_number, status, teacher_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        status = VALUES(status),
        teacher_id = VALUES(teacher_id)
    ');

    foreach ($records as $r) {
        $activity_type = null;
        $real_subject_id = $r['subject_id'];

        if (is_string($r['subject_id']) && strpos($r['subject_id'], 'LD:') === 0) {
            $activity_type = str_replace('LD:', '', $r['subject_id']);
            $real_subject_id = null;
        }

        $stmt->execute([
            $r['student_id'],
            $real_subject_id,
            $activity_type,
            $classroom_id,
            $academic_year,
            $semester,
            $check_date,
            $r['period_number'],
            $r['status'],
            $teacher_id
        ]);
    }

    $pdo->commit();

    // --- TELEGRAM NOTIFICATION SECTION ---
    try {
        // 1. Get School Bot Token
        $school_id = $_SESSION['school_id'];
        $stmt_school = $pdo->prepare("SELECT telegram_bot_token, name as school_name FROM schools WHERE id = ?");
        $stmt_school->execute([$school_id]);
        $school_info = $stmt_school->fetch(PDO::FETCH_ASSOC);
        $bot_token = $school_info['telegram_bot_token'] ?? null;

        if ($bot_token) {
            // 2. Get Teacher Name
            $stmt_teacher = $pdo->prepare("SELECT name, prefix FROM users WHERE id = ?");
            $stmt_teacher->execute([$teacher_id]);
            $teacher_info = $stmt_teacher->fetch(PDO::FETCH_ASSOC);
            $teacher_full_name = ($teacher_info['prefix'] ?? '') . $teacher_info['name'];

            // 3. Process each record for notification
            foreach ($records as $r) {
                // Fetch student info (Prefer profile data)
                $stmt_std = $pdo->prepare("
                    SELECT 
                        IFNULL(sp.name, s.name) AS name, 
                        IFNULL(sp.last_name, s.last_name) AS last_name, 
                        IFNULL(sp.prefix, s.prefix) AS prefix, 
                        IFNULL(sp.parent_telegram_id, s.parent_telegram_id) AS parent_telegram_id 
                    FROM students s
                    LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id
                    WHERE s.id = ?
                ");
                $stmt_std->execute([$r['student_id']]);
                $std = $stmt_std->fetch(PDO::FETCH_ASSOC);

                if ($std && !empty($std['parent_telegram_id'])) {
                    $student_name = ($std['prefix'] ?? '') . $std['name'] . ' ' . ($std['last_name'] ?? '');
                    
                    // Fetch Subject Name
                    $subject_name = "เช็คชื่อรายวัน/คาบเรียน";
                    if (strpos($r['subject_id'], 'LD:') === 0) {
                        $subject_name = str_replace('LD:', '', $r['subject_id']);
                    } else if (!empty($r['subject_id'])) {
                        $stmt_sub = $pdo->prepare("SELECT subject_name FROM subjects WHERE id = ?");
                        $stmt_sub->execute([$r['subject_id']]);
                        $sub = $stmt_sub->fetch(PDO::FETCH_ASSOC);
                        if ($sub) $subject_name = $sub['subject_name'];
                    }

                    $message = createAttendanceMessage(
                        $student_name, 
                        $r['status'], 
                        $subject_name, 
                        $teacher_full_name, 
                        $check_date
                    );

                    sendTelegramNotification($bot_token, $std['parent_telegram_id'], $message);
                }
            }
        }
    } catch (Exception $e) {
        // Notification failure shouldn't crash the main response
        // error_log("Telegram Notification Error: " . $e->getMessage());
    }
    // --- END TELEGRAM NOTIFICATION SECTION ---

    echo json_encode(['message' => 'บันทึกการมาเรียนเรียบร้อยแล้ว']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
