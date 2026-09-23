<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// ต้องเป็น Admin โรงเรียนเท่านั้น
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$teacher_id = $data['teacher_id'] ?? '';
$type = $data['type'] ?? 'individual'; // 'individual' or 'bulk_level'
$subject_ids = $data['subject_ids'] ?? []; // for individual
$classroom_id = $data['classroom_id'] ?? null; // for individual
$level = $data['level'] ?? ''; // for bulk_level
$academic_year = $data['academic_year'] ?? date('Y');
$semester = $data['semester'] ?? 1;

if (empty($teacher_id)) {
    echo json_encode(['error' => 'ไม่พบรหัสคุณครู']);
    exit;
}

try {
    // ตรวจสอบว่าครูอยู่ในโรงเรียนเดียวกัน
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ? AND school_id = ?');
    $stmt->execute([$teacher_id, $_SESSION['school_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'คุณครูไม่ได้อยู่ในโรงเรียนของคุณ']);
        exit;
    }

    if ($type === 'bulk_level') {
        if (empty($level)) {
            echo json_encode(['error' => 'กรุณาระบุระดับชั้น']);
            exit;
        }
        // ดึงวิชาทั้งหมดในระดับชั้นนั้นของโรงเรียนนี้
        $stmt = $pdo->prepare('SELECT id FROM subjects WHERE level = ? AND school_id = ?');
        $stmt->execute([$level, $_SESSION['school_id']]);
        $subjects = $stmt->fetchAll();
        $subject_ids = array_column($subjects, 'id');

        // ดึงห้องเรียนทั้งหมดในระดับชั้นนั้น
        $stmt = $pdo->prepare('SELECT id FROM classrooms WHERE level = ? AND school_id = ?');
        $stmt->execute([$level, $_SESSION['school_id']]);
        $classrooms = $stmt->fetchAll();
        $classroom_ids = array_column($classrooms, 'id');

        if (empty($subject_ids) || empty($classroom_ids)) {
            echo json_encode(['error' => 'ไม่พบรายวิชาหรือห้องเรียนในระดับชั้นที่เลือก']);
            exit;
        }

        $pdo->beginTransaction();
        foreach ($subject_ids as $sid) {
            foreach ($classroom_ids as $cid) {
                $stmt = $pdo->prepare('SELECT id FROM teacher_assignments WHERE teacher_id = ? AND subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester = ?');
                $stmt->execute([$teacher_id, $sid, $cid, $academic_year, $semester]);
                if (!$stmt->fetch()) {
                    $stmt = $pdo->prepare('INSERT INTO teacher_assignments (teacher_id, subject_id, classroom_id, academic_year, semester) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$teacher_id, $sid, $cid, $academic_year, $semester]);
                }
            }
        }
        $pdo->commit();
        echo json_encode(['message' => 'มอบหมายงานสอนระดับชั้นสำเร็จแล้ว']);
        exit;
    }

    if (empty($subject_ids)) {
        echo json_encode(['error' => 'ไม่พบรายวิชาที่ต้องการมอบหมาย']);
        exit;
    }

    // มอบหมายงานสอน (ข้ามวิชาที่มอบหมายไปแล้ว)
    $pdo->beginTransaction();
    foreach ($subject_ids as $sid) {
        $stmt = $pdo->prepare('SELECT id FROM teacher_assignments WHERE teacher_id = ? AND subject_id = ? AND classroom_id <=> ? AND academic_year = ? AND semester = ?');
        $stmt->execute([$teacher_id, $sid, $classroom_id, $academic_year, $semester]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare('INSERT INTO teacher_assignments (teacher_id, subject_id, classroom_id, academic_year, semester) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$teacher_id, $sid, $classroom_id, $academic_year, $semester]);
        }
    }
    $pdo->commit();

    echo json_encode(['message' => 'มอบหมายงานสอนสำเร็จแล้ว']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถมอบหมายงานได้: ' . $e->getMessage()]);
}
?>
