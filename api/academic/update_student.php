<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

$is_allowed = false;
if ($_SESSION['role'] === 'admin' || !empty($_SESSION['is_academic'])) {
    $is_allowed = true;
} else {
    // ตรวจสอบว่าเป็นครูประจำชั้นของนักเรียนคนนี้หรือไม่
    if (!empty($id)) {
        $stmt_check = $pdo->prepare('
            SELECT c.id 
            FROM classrooms c
            JOIN students s ON s.classroom_id = c.id
            WHERE s.id = ? AND (c.teacher_id_1 = ? OR c.teacher_id_2 = ?) AND s.school_id = ?
        ');
        $stmt_check->execute([$id, $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['school_id']]);
        if ($stmt_check->fetch()) {
            $is_allowed = true;
        }
    }
}

if (!$is_allowed) {
    http_response_code(403);
    echo json_encode(['error' => 'คุณไม่มีสิทธิ์แก้ไขข้อมูลนักเรียนคนนี้ (สิทธิ์นี้สำหรับ Admin, งานวิชาการ หรือครูประจำชั้นของนักเรียนเท่านั้น)']);
    exit;
}
$prefix = $data['prefix'] ?? '';
$name = $data['name'] ?? '';
$last_name = $data['last_name'] ?? '';
$student_code = $data['student_code'] ?? '';
$national_id = $data['national_id'] ?? '';
$level = $data['level'] ?? '';
$room = $data['room'] ?? '1';
$academic_year = $data['academic_year'] ?? '2567';
$school_id = $_SESSION['school_id'];

// DMC Fields
$gender = $data['gender'] ?? '';
$birthday = $data['birthday'] ?? null;
if (empty($birthday) || trim($birthday) === '') {
    $birthday = null;
}
$age = intval($data['age'] ?? 0);
$weight = floatval($data['weight'] ?? 0);
$height = floatval($data['height'] ?? 0);
$blood_group = $data['blood_group'] ?? '';
$religion = $data['religion'] ?? '';
$race = $data['race'] ?? '';
$nationality = $data['nationality'] ?? '';
$house_no = $data['house_no'] ?? '';
$moo = $data['moo'] ?? '';
$road_soi = $data['road_soi'] ?? '';
$sub_district = $data['sub_district'] ?? '';
$district = $data['district'] ?? '';
$province_name = $data['province_name'] ?? '';
$parent_name = $data['parent_name'] ?? '';
$parent_last_name = $data['parent_last_name'] ?? '';
$parent_occupation = $data['parent_occupation'] ?? '';
$parent_relationship = $data['parent_relationship'] ?? '';
$father_name = $data['father_name'] ?? '';
$father_last_name = $data['father_last_name'] ?? '';
$father_occupation = $data['father_occupation'] ?? '';
$mother_name = $data['mother_name'] ?? '';
$mother_last_name = $data['mother_last_name'] ?? '';
$mother_occupation = $data['mother_occupation'] ?? '';
$disadvantage = $data['disadvantage'] ?? '';
$parent_telegram_id = $data['parent_telegram_id'] ?? '';

if (empty($id) || empty($name) || empty($level)) {
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. ตรวจสอบ/สร้างห้องเรียน
    $stmt = $pdo->prepare('SELECT id FROM classrooms WHERE school_id = ? AND level = ? AND room = ?');
    $stmt->execute([$school_id, $level, $room]);
    $classroom = $stmt->fetch();

    $classroom_id = null;
    if (!$classroom) {
        $stmt = $pdo->prepare('INSERT INTO classrooms (school_id, level, room) VALUES (?, ?, ?)');
        $stmt->execute([$school_id, $level, $room]);
        $classroom_id = $pdo->lastInsertId();
    } else {
        $classroom_id = $classroom['id'];
    }

    // 2. ดึง/สร้าง/ซิงค์ student_profile_id
    $stmt_p_check = $pdo->prepare("SELECT student_profile_id FROM students WHERE id = ?");
    $stmt_p_check->execute([$id]);
    $s_rec = $stmt_p_check->fetch();
    $profile_id = $s_rec['student_profile_id'] ?? null;

    if ($profile_id) {
        // อัปเดตโปรไฟล์หลัก (ข้อมูล DMC และ Telegram)
        $sql_profile = 'UPDATE student_profiles SET 
            prefix = ?, name = ?, last_name = ?, student_code = ?, national_id = ?,
            gender = ?, birthday = ?, parent_telegram_id = ?,
            blood_group = ?, religion = ?, race = ?, nationality = ?,
            house_no = ?, moo = ?, road_soi = ?, sub_district = ?, district = ?, province_name = ?,
            parent_name = ?, parent_last_name = ?, parent_occupation = ?, parent_relationship = ?,
            father_name = ?, father_last_name = ?, father_occupation = ?,
            mother_name = ?, mother_last_name = ?, mother_occupation = ?, disadvantage = ?
            WHERE id = ? AND school_id = ?';
        
        $pdo->prepare($sql_profile)->execute([
            $prefix, $name, $last_name, $student_code, $national_id,
            $gender, $birthday, $parent_telegram_id,
            $blood_group, $religion, $race, $nationality,
            $house_no, $moo, $road_soi, $sub_district, $district, $province_name,
            $parent_name, $parent_last_name, $parent_occupation, $parent_relationship,
            $father_name, $father_last_name, $father_occupation,
            $mother_name, $mother_last_name, $mother_occupation, $disadvantage,
            $profile_id, $school_id
        ]);
    }

    // 3. อัปเดตตารางนักเรียน (ลงทะเบียนรายปี)
    $sql = 'UPDATE students SET 
        prefix = ?, name = ?, last_name = ?, student_code = ?, national_id = ?, level = ?, room = ?, classroom_id = ?, academic_year = ?,
        gender = ?, birthday = ?, age = ?, weight = ?, height = ?, blood_group = ?, religion = ?, race = ?, nationality = ?,
        house_no = ?, moo = ?, road_soi = ?, sub_district = ?, district = ?, province_name = ?,
        parent_name = ?, parent_last_name = ?, parent_occupation = ?, parent_relationship = ?,
        father_name = ?, father_last_name = ?, father_occupation = ?,
        mother_name = ?, mother_last_name = ?, mother_occupation = ?,
        disadvantage = ?, parent_telegram_id = ?
        WHERE id = ? AND school_id = ?';
    
    $params = [
        $prefix, $name, $last_name, $student_code, $national_id, $level, $room, $classroom_id, $academic_year,
        $gender, $birthday, $age, $weight, $height, $blood_group, $religion, $race, $nationality,
        $house_no, $moo, $road_soi, $sub_district, $district, $province_name,
        $parent_name, $parent_last_name, $parent_occupation, $parent_relationship,
        $father_name, $father_last_name, $father_occupation,
        $mother_name, $mother_last_name, $mother_occupation,
        $disadvantage, $parent_telegram_id, $id, $school_id
    ];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $pdo->commit();
    echo json_encode(['message' => 'อัปเดตข้อมูลนักเรียนสำเร็จแล้ว']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถอัปเดตข้อมูลได้: ' . $e->getMessage()]);
}
?>
