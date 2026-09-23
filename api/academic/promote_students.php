<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$from_year = $data['from_year'] ?? '';
$to_year = $data['to_year'] ?? '';
$school_id = $_SESSION['school_id'];

if (empty($from_year) || empty($to_year)) {
    echo json_encode(['error' => 'ข้อมูลปีการศึกษาไม่ครบถ้วน']);
    exit;
}

try {
    // 1. ตรวจสอบว่าปีการศึกษาปลายทางมีนร.หรือยัง (นับเฉพาะห้องเรียนและระดับชั้น)
    $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE school_id = ? AND academic_year = ?");
    $check->execute([$school_id, $to_year]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['error' => 'มีข้อมูลนักเรียนในปีการศึกษาปลายทางแล้ว กรุณาลบข้อมูลเดิมก่อนหากต้องการดึงใหม่']);
        exit;
    }

    // กำหนดลำดับชั้นเรียน
    $levels_chain = [
        // ระดับอนุบาล
        'อนุบาล 1' => 'อนุบาล 2',
        'อนุบาล 2' => 'อนุบาล 3',
        'อนุบาล 3' => 'ประถมศึกษาปีที่ 1',
        'อ.1' => 'อ.2',
        'อ.2' => 'อ.3',
        'อ.3' => 'ป.1',
        
        // ระดับประถม
        'ประถมศึกษาปีที่ 1' => 'ประถมศึกษาปีที่ 2',
        'ประถมศึกษาปีที่ 2' => 'ประถมศึกษาปีที่ 3',
        'ประถมศึกษาปีที่ 3' => 'ประถมศึกษาปีที่ 4',
        'ประถมศึกษาปีที่ 4' => 'ประถมศึกษาปีที่ 5',
        'ประถมศึกษาปีที่ 5' => 'ประถมศึกษาปีที่ 6',
        'ป.1' => 'ป.2',
        'ป.2' => 'ป.3',
        'ป.3' => 'ป.4',
        'ป.4' => 'ป.5',
        'ป.5' => 'ป.6',

        // ระดับมัธยม (ถ้ามี)
        'มัธยมศึกษาปีที่ 1' => 'มัธยมศึกษาปีที่ 2',
        'มัธยมศึกษาปีที่ 2' => 'มัธยมศึกษาปีที่ 3',
        'ม.1' => 'ม.2',
        'ม.2' => 'ม.3'
    ];
    
    // 2. ดึงข้อมูลนักเรียนจากปีการศึกษาเดิมที่สถานะกำลังเรียนอยู่
    $stmt = $pdo->prepare("SELECT * FROM students WHERE school_id = ? AND academic_year = ? AND status = 'studying'");
    $stmt->execute([$school_id, $from_year]);
    $old_students = $stmt->fetchAll();
    
    $promoted_count = 0;
    $graduated_count = 0;
    
    foreach ($old_students as $student) {
        $current_level = $student['level'];
        $profile_id = $student['student_profile_id'];
        
        // ถ้าไม่มี profile_id ให้ลองหาหรือสร้างใหม่จาก national_id
        if (!$profile_id && !empty($student['national_id'])) {
            $stmt_p = $pdo->prepare("SELECT id FROM student_profiles WHERE national_id = ? AND school_id = ?");
            $stmt_p->execute([$student['national_id'], $school_id]);
            $profile = $stmt_p->fetch();
            
            if ($profile) {
                $profile_id = $profile['id'];
            } else {
                // สร้างโปรไฟล์ใหม่
                $ins_p = $pdo->prepare("INSERT INTO student_profiles (
                    school_id, student_code, national_id, prefix, name, last_name, 
                    gender, birthday, parent_telegram_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $ins_p->execute([
                    $school_id, $student['student_code'], $student['national_id'], 
                    $student['prefix'], $student['name'], $student['last_name'],
                    $student['gender'] ?? null, $student['birthday'] ?? null, 
                    $student['parent_telegram_id'] ?? null
                ]);
                $profile_id = $pdo->lastInsertId();
            }
        }
        
        if (isset($levels_chain[$current_level])) {
            $next_level = $levels_chain[$current_level];
            
            // คัดลอกนักเรียนไปปีการศึกษาใหม่พร้อมเลื่อนชั้น (Full Data Copy + Linked Profile)
            $ins = $pdo->prepare("INSERT INTO students (
                student_profile_id, student_code, prefix, name, last_name, national_id, 
                gender, birthday, level, room, 
                academic_year, school_id, parent_telegram_id, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'studying')");
            
            $ins->execute([
                $profile_id,
                $student['student_code'],
                $student['prefix'],
                $student['name'],
                $student['last_name'],
                $student['national_id'],
                $student['gender'] ?? null,
                $student['birthday'] ?? null,
                $next_level,
                $student['room'] ?? '1',
                $to_year,
                $school_id,
                $student['parent_telegram_id'] ?? null
            ]);
            $promoted_count++;
        } else if (in_array($current_level, ['ป.6', 'ประถมศึกษาปีที่ 6', 'ม.3', 'มัธยมศึกษาปีที่ 3'])) {
            // กรณีชั้นสูงสุด ให้เปลี่ยนสถานะเป็นจบการศึกษา
            $upd = $pdo->prepare("UPDATE students SET status = 'graduated' WHERE id = ?");
            $upd->execute([$student['id']]);
            $graduated_count++;
        }
    }

    // 3. Sync classroom_id อัตโนมัติสำหรับนักเรียนที่เลื่อนมาใหม่
    $pdo->prepare("
        UPDATE students s 
        JOIN classrooms c ON s.level = c.level 
            AND s.room = c.room 
            AND s.school_id = c.school_id
        SET s.classroom_id = c.id
        WHERE s.academic_year = ? AND s.school_id = ? AND s.classroom_id IS NULL
    ")->execute([$to_year, $school_id]);
    
    echo json_encode([
        'message' => "ดำเนินการเรียบร้อยแล้ว\n- เลื่อนชั้นนักเรียน: $promoted_count ราย\n- จบการศึกษา (ป.6): $graduated_count ราย"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
?>
