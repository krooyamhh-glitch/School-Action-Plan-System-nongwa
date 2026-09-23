<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// ต้องเป็น Admin หรือ งานวิชาการ
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && !$_SESSION['is_academic'])) {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$students = $data['students'] ?? [];
$school_id = $_SESSION['school_id'];

if (empty($students)) {
    echo json_encode(['error' => 'ไม่พบข้อมูลนักเรียนที่จะนำเข้า']);
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($students as $s) {
        $student_code = trim($s['student_code'] ?? '');
        $prefix = trim($s['prefix'] ?? '');
        $national_id = trim($s['national_id'] ?? '');
        $name = trim($s['name'] ?? '');
        $last_name = trim($s['last_name'] ?? '');
        $level = trim($s['level'] ?? '');
        $room = trim($s['room'] ?? '');
        $academic_year = trim($s['academic_year'] ?? '2567');

        // DMC Fields
        $gender = trim($s['gender'] ?? '');
        $birthday = trim($s['birthday'] ?? '');
        $age = intval($s['age'] ?? 0);
        $weight = floatval($s['weight'] ?? 0);
        $height = floatval($s['height'] ?? 0);
        $blood_group = trim($s['blood_group'] ?? '');
        $religion = trim($s['religion'] ?? '');
        $race = trim($s['race'] ?? '');
        $nationality = trim($s['nationality'] ?? '');
        $house_no = trim($s['house_no'] ?? '');
        $moo = trim($s['moo'] ?? '');
        $road_soi = trim($s['road_soi'] ?? '');
        $sub_district = trim($s['sub_district'] ?? '');
        $district = trim($s['district'] ?? '');
        $province_name = trim($s['province_name'] ?? '');
        $parent_name = trim($s['parent_name'] ?? '');
        $parent_last_name = trim($s['parent_last_name'] ?? '');
        $parent_occupation = trim($s['parent_occupation'] ?? '');
        $parent_relationship = trim($s['parent_relationship'] ?? '');
        $father_name = trim($s['father_name'] ?? '');
        $father_last_name = trim($s['father_last_name'] ?? '');
        $father_occupation = trim($s['father_occupation'] ?? '');
        $mother_name = trim($s['mother_name'] ?? '');
        $mother_last_name = trim($s['mother_last_name'] ?? '');
        $mother_occupation = trim($s['mother_occupation'] ?? '');
        $disadvantage = trim($s['disadvantage'] ?? '');

        // Convert birthday format if needed (DMC is DD/MM/YYYY in Buddhist Era)
        if (!empty($birthday) && strpos($birthday, '/') !== false) {
            $parts = explode('/', $birthday);
            if (count($parts) === 3) {
                $day = $parts[0];
                $month = $parts[1];
                $year = intval($parts[2]) - 543; // Convert BE to AD
                $birthday = "$year-$month-$day";
            }
        }

        if (empty($birthday) || trim($birthday) === '') {
            $birthday = null;
        }

        if (empty($student_code) || empty($name) || empty($level)) continue;

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

        // 2. ตรวจสอบ/สร้าง/อัปเดต โปรไฟล์นักเรียนหลัก
        $profile_id = null;
        if (!empty($national_id)) {
            $stmt_p = $pdo->prepare("SELECT id FROM student_profiles WHERE national_id = ? AND school_id = ?");
            $stmt_p->execute([$national_id, $school_id]);
            $profile = $stmt_p->fetch();
            
            if ($profile) {
                $profile_id = $profile['id'];
                // อัปเดตข้อมูลโปรไฟล์หลักด้วยข้อมูลใหม่จาก Excel
                $sql_upd_p = "UPDATE student_profiles SET 
                    prefix = ?, name = ?, last_name = ?, student_code = ?,
                    gender = ?, birthday = ?, blood_group = ?, religion = ?, 
                    race = ?, nationality = ?, house_no = ?, moo = ?, 
                    road_soi = ?, sub_district = ?, district = ?, province_name = ?,
                    parent_name = ?, parent_last_name = ?, parent_occupation = ?, 
                    parent_relationship = ?, father_name = ?, father_last_name = ?, 
                    father_occupation = ?, mother_name = ?, mother_last_name = ?, 
                    mother_occupation = ?, disadvantage = ?
                    WHERE id = ?";
                $pdo->prepare($sql_upd_p)->execute([
                    $prefix, $name, $last_name, $student_code,
                    $gender, $birthday, $blood_group, $religion,
                    $race, $nationality, $house_no, $moo,
                    $road_soi, $sub_district, $district, $province_name,
                    $parent_name, $parent_last_name, $parent_occupation,
                    $parent_relationship, $father_name, $father_last_name,
                    $father_occupation, $mother_name, $mother_last_name,
                    $mother_occupation, $disadvantage, $profile_id
                ]);
            } else {
                // สร้างโปรไฟล์ใหม่
                $sql_ins_p = "INSERT INTO student_profiles (
                    school_id, student_code, national_id, prefix, name, last_name,
                    gender, birthday, blood_group, religion, race, nationality,
                    house_no, moo, road_soi, sub_district, district, province_name,
                    parent_name, parent_last_name, parent_occupation, parent_relationship,
                    father_name, father_last_name, father_occupation, 
                    mother_name, mother_last_name, mother_occupation, disadvantage
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql_ins_p)->execute([
                    $school_id, $student_code, $national_id, $prefix, $name, $last_name,
                    $gender, $birthday, $blood_group, $religion, $race, $nationality,
                    $house_no, $moo, $road_soi, $sub_district, $district, $province_name,
                    $parent_name, $parent_last_name, $parent_occupation, $parent_relationship,
                    $father_name, $father_last_name, $father_occupation,
                    $mother_name, $mother_last_name, $mother_occupation, $disadvantage
                ]);
                $profile_id = $pdo->lastInsertId();
            }
        }

        // 3. ตรวจสอบนักเรียนเดิม (ในตารางลงทะเบียนรายปี)
        $stmt = $pdo->prepare('SELECT id FROM students WHERE school_id = ? AND academic_year = ? AND (student_code = ? OR (national_id = ? AND national_id != ""))');
        $stmt->execute([$school_id, $academic_year, $student_code, $national_id]);
        $existing = $stmt->fetch();

        $params = [
            $profile_id, $prefix, $name, $last_name, $level, $room, $classroom_id, $national_id, $academic_year,
            $gender, $birthday, $age, $weight, $height, $blood_group, $religion, $race, $nationality,
            $house_no, $moo, $road_soi, $sub_district, $district, $province_name,
            $parent_name, $parent_last_name, $parent_occupation, $parent_relationship,
            $father_name, $father_last_name, $father_occupation,
            $mother_name, $mother_last_name, $mother_occupation,
            $disadvantage
        ];

        if ($existing) {
            // อัปเดตข้อมูลลงทะเบียน
            $sql = 'UPDATE students SET 
                student_profile_id = ?, prefix = ?, name = ?, last_name = ?, level = ?, room = ?, classroom_id = ?, national_id = ?, academic_year = ?,
                gender = ?, birthday = ?, age = ?, weight = ?, height = ?, blood_group = ?, religion = ?, race = ?, nationality = ?,
                house_no = ?, moo = ?, road_soi = ?, sub_district = ?, district = ?, province_name = ?,
                parent_name = ?, parent_last_name = ?, parent_occupation = ?, parent_relationship = ?,
                father_name = ?, father_last_name = ?, father_occupation = ?,
                mother_name = ?, mother_last_name = ?, mother_occupation = ?,
                disadvantage = ?
                WHERE id = ?';
            $params[] = $existing['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            // เพิ่มการลงทะเบียนใหม่
            $sql = 'INSERT INTO students (
                student_profile_id, prefix, name, last_name, level, room, classroom_id, national_id, academic_year,
                gender, birthday, age, weight, height, blood_group, religion, race, nationality,
                house_no, moo, road_soi, sub_district, district, province_name,
                parent_name, parent_last_name, parent_occupation, parent_relationship,
                father_name, father_last_name, father_occupation,
                mother_name, mother_last_name, mother_occupation,
                disadvantage, school_id, student_code
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $params[] = $school_id;
            $params[] = $student_code;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
    }

    $pdo->commit();
    echo json_encode(['message' => 'นำเข้าข้อมูลนักเรียนสำเร็จแล้ว']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'ไม่สามารถนำเข้าข้อมูลได้: ' . $e->getMessage()]);
}
?>
