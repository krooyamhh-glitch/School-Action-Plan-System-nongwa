<?php
require_once 'report_header.php';

$year = $_GET['year'] ?? '';
$semester = $_GET['semester'] ?? '1';
$classroom_id = $_GET['classroom_id'] ?? '';
$student_id = $_GET['student_id'] ?? '';
$approval_date_raw = $_GET['approval_date'] ?? '';

if (!$classroom_id || !$student_id) die('Missing parameters');

$approval_date = formatThaiDate($approval_date_raw);

// ดึงข้อมูลโรงเรียน
$stmt = $pdo->prepare('SELECT * FROM schools WHERE id = ?');
$stmt->execute([$_SESSION['school_id']]);
$school = $stmt->fetch();

// ดึงข้อมูลห้องเรียน
$stmt = $pdo->prepare('SELECT * FROM classrooms WHERE id = ?');
$stmt->execute([$classroom_id]);
$classroom = $stmt->fetch();
$clean_level = str_replace(['ป.', 'ม.'], '', $classroom['level'] ?? '');

// ดึงข้อมูลนักเรียน
$students_to_print = [];
$student_fields = "s.*, 
    IFNULL(sp.prefix, s.prefix) AS prefix, 
    IFNULL(sp.name, s.name) AS name, 
    IFNULL(sp.last_name, s.last_name) AS last_name,
    IFNULL(sp.gender, s.gender) AS gender,
    IFNULL(sp.birthday, s.birthday) AS birthday,
    IFNULL(sp.religion, s.religion) AS religion,
    IFNULL(sp.race, s.race) AS race,
    IFNULL(sp.nationality, s.nationality) AS nationality,
    IFNULL(sp.house_no, s.house_no) AS house_no,
    IFNULL(sp.moo, s.moo) AS moo,
    IFNULL(sp.road_soi, s.road_soi) AS road_soi,
    IFNULL(sp.sub_district, s.sub_district) AS sub_district,
    IFNULL(sp.district, s.district) AS district,
    IFNULL(sp.province_name, s.province_name) AS province_name,
    IFNULL(sp.parent_name, s.parent_name) AS parent_name,
    IFNULL(sp.parent_last_name, s.parent_last_name) AS parent_last_name,
    IFNULL(sp.parent_occupation, s.parent_occupation) AS parent_occupation,
    IFNULL(sp.parent_relationship, s.parent_relationship) AS parent_relationship,
    IFNULL(sp.father_name, s.father_name) AS father_name,
    IFNULL(sp.father_last_name, s.father_last_name) AS father_last_name,
    IFNULL(sp.father_occupation, s.father_occupation) AS father_occupation,
    IFNULL(sp.mother_name, s.mother_name) AS mother_name,
    IFNULL(sp.mother_last_name, s.mother_last_name) AS mother_last_name,
    IFNULL(sp.mother_occupation, s.mother_occupation) AS mother_occupation,
    IFNULL(sp.disadvantage, s.disadvantage) AS disadvantage";

if ($student_id === 'all') {
    $stmt = $pdo->prepare("SELECT $student_fields FROM students s LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id WHERE s.classroom_id = ? AND s.academic_year = ? AND s.status = 'studying' ORDER BY s.student_code ASC");
    $stmt->execute([$classroom_id, $year]);
    $students_to_print = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT $student_fields FROM students s LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id WHERE s.id = ?");
    $stmt->execute([$student_id]);
    $students_to_print = [$stmt->fetch()];
}

function getResultText($avg) {
    if (!$avg) return '-';
    if ($avg >= 2.5) return 'ดีเยี่ยม';
    if ($avg >= 1.5) return 'ดี';
    if ($avg >= 0.5) return 'ผ่าน';
    return 'ไม่ผ่าน';
}

function formatPassFail($val) {
    if (empty($val)) return '-';
    if ($val === 'P' || $val === 'ผ่าน') return 'ผ่าน';
    if ($val === 'F' || $val === 'ไม่ผ่าน') return 'ไม่ผ่าน';
    return $val;
}
?>

<style>
    .p6-container {
        font-family: 'Sarabun', sans-serif;
        color: black;
    }
    .p6-header {
        position: relative;
        text-align: center;
        margin-bottom: 20px;
        min-height: 100px;
    }
    .p6-logo-left {
        position: absolute;
        left: 0;
        top: 0;
        width: 80px;
        height: 80px;
        object-fit: contain;
    }
    .p6-logo-center {
        display: block;
        margin: 0 auto 15px;
        width: 100px;
        height: 100px;
        object-fit: contain;
    }
    .p6-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .p6-table th, .p6-table td {
        border: 1px solid black;
        padding: 4px;
        text-align: center;
    }
    .p6-table th {
        background: #fff;
    }
    .text-left { text-align: left !important; }
    .text-center { text-align: center !important; }
    .dotted-line {
        border-bottom: 1px dotted black;
        display: inline-block;
        min-width: 100px;
        padding: 0 5px;
        text-align: center;
        line-height: 1.1;
        vertical-align: bottom;
    }
    .summary-section {
        display: flex;
        margin-top: 10px;
        font-size: 13px;
    }
    .summary-left {
        width: 45%;
    }
    .summary-right {
        width: 55%;
        text-align: center;
    }
    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }
    .summary-table td {
        border: 1px solid black;
        padding: 2px 5px;
    }
    .sig-block {
        margin-top: 15px;
        text-align: center;
    }
    .page-break {
        page-break-after: always;
    }
    .cover-page {
        text-align: center;
        padding: 20mm;
    }
    .guide-page {
        font-size: 14px;
        line-height: 1.6;
    }
    .guide-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 12px;
    }
    .guide-table td {
        border: 1px solid black;
        padding: 3px;
        text-align: center;
    }
    @media print {
        .page { margin: 0; padding: 10mm; border: none; box-shadow: none; }
    }
</style>

<?php
foreach ($students_to_print as $student):
    if (!$student) continue;
    
    // ดึงผลการเรียน
    $stmt = $pdo->prepare('
        SELECT s.code, s.name, s.hours, s.credits, g.score_total, g.grade, g.score_percent
        FROM teacher_assignments ta
        JOIN subjects s ON ta.subject_id = s.id
        JOIN grades g ON s.id = g.subject_id AND g.student_id = ? AND g.academic_year = ? AND g.semester = ?
        WHERE ta.classroom_id = ? AND ta.academic_year = ?
        GROUP BY s.id
        ORDER BY s.code ASC
    ');
    $stmt->execute([$student['id'], $year, $semester === 'annual' ? 0 : $semester, $classroom_id, $year]);
    $grades = $stmt->fetchAll();

    // ดึงกิจกรรมพัฒนาผู้เรียน
    $stmt = $pdo->prepare('SELECT * FROM learner_development_results WHERE student_id = ? AND academic_year = ? AND semester = ?');
    $stmt->execute([$student['id'], $year, $semester === 'annual' ? 0 : $semester]);
    $ld_result = $stmt->fetch();

    // ดึงคะแนนคุณลักษณะ
    $stmt = $pdo->prepare('SELECT average_score FROM characteristics_scores WHERE student_id = ? AND academic_year = ? AND semester = ? LIMIT 1');
    $stmt->execute([$student['id'], $year, $semester === 'annual' ? 0 : $semester]);
    $behavior = $stmt->fetch();

    // ดึงคะแนนอ่านคิดวิเคราะห์
    $stmt = $pdo->prepare('SELECT average_score FROM analytical_scores WHERE student_id = ? AND academic_year = ? AND semester = ? LIMIT 1');
    $stmt->execute([$student['id'], $year, $semester === 'annual' ? 0 : $semester]);
    $analytical = $stmt->fetch();

    // ดึงคะแนนสมรรถนะ
    $stmt = $pdo->prepare('SELECT average_score FROM competency_scores WHERE student_id = ? AND academic_year = ? AND semester = ? LIMIT 1');
    $stmt->execute([$student['id'], $year, $semester === 'annual' ? 0 : $semester]);
    $competency = $stmt->fetch();

    // ดึงชื่อครูประจำชั้น
    $stmt_t = $pdo->prepare('
        SELECT u1.name as t1_name, u1.last_name as t1_last, u1.position as t1_pos,
               u2.name as t2_name, u2.last_name as t2_last, u2.position as t2_pos
        FROM classrooms c
        LEFT JOIN users u1 ON c.teacher_id_1 = u1.id
        LEFT JOIN users u2 ON c.teacher_id_2 = u2.id
        WHERE c.id = ?
    ');
    $stmt_t->execute([$classroom_id]);
    $ct = $stmt_t->fetch();
    
    $teacher_name_1 = $ct['t1_name'] ? $ct['t1_name'] . ' ' . $ct['t1_last'] : '';
    $teacher_name_2 = $ct['t2_name'] ? $ct['t2_name'] . ' ' . $ct['t2_last'] : '';
    $teacher_pos = formatTeacherPosition($ct['t1_pos'] ?? '');

    // Fallback to Learner Development assignment if no classroom teacher is set in the classrooms table
    if (!$teacher_name_1) {
        $stmt_ld = $pdo->prepare('
            SELECT u.name, u.last_name, u.position
            FROM learner_development_assignments lda
            JOIN users u ON lda.teacher_id = u.id
            WHERE lda.classroom_id = ? AND lda.academic_year = ?
            ORDER BY lda.id ASC
        ');
        $stmt_ld->execute([$classroom_id, $year]);
        $ld_teachers = $stmt_ld->fetchAll();
        if (isset($ld_teachers[0])) {
            $teacher_name_1 = $ld_teachers[0]['name'] . ' ' . $ld_teachers[0]['last_name'];
            $teacher_pos = formatTeacherPosition($ld_teachers[0]['position'] ?? '');
        }
        if (isset($ld_teachers[1])) {
            $teacher_name_2 = $ld_teachers[1]['name'] . ' ' . $ld_teachers[1]['last_name'];
        }
    }

    $teacher_name = $teacher_name_1;
    if ($teacher_name_2) {
        $teacher_name .= ' และ ' . $teacher_name_2;
    }

    // ดึงหัวหน้าวิชาการ หรือ รองผู้อำนวยการ ตามที่ผู้ใช้ต้องการ
    $acad_name = !empty($deputy_director_name) ? $deputy_director_name : $academic_head_name;
    $acad_pos = !empty($deputy_director_name) ? $deputy_director_position : $academic_head_position;
    
    // ฟังก์ชันช่วยเปรียบเทียบชื่อเพื่อไม่ให้ซ้ำกับผู้อำนวยการ
    $clean_director = str_replace(['นาย', 'นางสาว', 'นาง', 'เด็กชาย', 'เด็กหญิง', ' ', '.', '(' , ')'], '', (string)$director_name);
    
    // ถ้ายังไม่มีชื่อ หรือชื่อดันไปซ้ำกับผู้อำนวยการ (กรณีดึงจากระบบแล้วตั้งค่าไว้คนเดียวกัน)
    // ให้ลองดึงจากตาราง users (Fallback)
    $clean_acad = str_replace(['นาย', 'นางสาว', 'นาง', 'เด็กชาย', 'เด็กหญิง', ' ', '.', '(' , ')'], '', (string)$acad_name);
    
    if (empty($acad_name) || ($clean_director !== '' && $clean_director === $clean_acad)) {
        // หาครูที่มีงานวิชาการก่อน (is_academic = 1) และต้องไม่ใช่คนเดียวกับผู้อำนวยการ
        $sqlFallback = 'SELECT name, last_name, position FROM users 
                        WHERE school_id = ? AND is_approved = 1';
        
        $paramsFallback = [$_SESSION['school_id'] ?? 0];
        
        // ถ้าเรารู้ชื่อผู้อำนวยการ พยายามเลี่ยง
        if (!empty($director_name)) {
            $core_name = str_replace(['นาย', 'นางสาว', 'นาง', ' '], '', (string)$director_name);
            $sqlFallback .= ' AND (name NOT LIKE ? AND last_name NOT LIKE ?)';
            $paramsFallback[] = '%' . $core_name . '%';
            $paramsFallback[] = '%' . $core_name . '%';
        }
        
        // เรียงลำดับเอาคนที่มีตำแหน่งวิชาการหรือเป็น admin ก่อน
        $sqlFallback .= ' ORDER BY is_academic DESC, (role = "admin") DESC, id ASC LIMIT 1';
        
        $stmt_acad = $pdo->prepare($sqlFallback);
        $stmt_acad->execute($paramsFallback);
        $acad = $stmt_acad->fetch();
        
        if ($acad) {
            $acad_name = $acad['name'] . ' ' . $acad['last_name'];
            $acad_pos = formatTeacherPosition($acad['position']);
            
            // ถ้าเป็นตำแหน่งผู้อำนวยการ (ที่ไม่ใช่รอง) ให้ใช้ตำแหน่งวิชาการแทนเพื่อให้เกียรติบทบาทในหน้านี้
            if ($acad_pos && strpos($acad_pos, 'ผู้อำนวยการ') !== false && strpos($acad_pos, 'รอง') === false) {
                $acad_pos = 'หัวหน้างานวิชาการโรงเรียน';
            }
            if (empty($acad_pos)) {
                $acad_pos = 'หัวหน้างานวิชาการโรงเรียน';
            }
        } else {
            // กรณีหาใครไม่ได้จริงๆ ให้ใช้จุดไปก่อน
            $acad_name = '..........................................................';
            $acad_pos = 'รองผู้อำนวยการฝ่ายวิชาการ/หัวหน้างานวิชาการ';
        }
    }

    // คำนวณข้อมูลเพิ่มเติมสำหรับหน้าปก
    $bday = formatThaiDate($student['birthday'] ?? '');
    $age_years = $student['age'] ?? 0;
    $age_months = 0;
    
    if (!empty($student['birthday'])) {
        $birthDate = new DateTime($student['birthday']);
        $today = new DateTime();
        $age_diff = $today->diff($birthDate);
        $age_years = $age_diff->y;
        $age_months = $age_diff->m;
    }

    // ดึงข้อมูลสุขภาพ (student_health_records)
    $stmt_h = $pdo->prepare('SELECT * FROM student_health_records WHERE student_id = ? AND academic_year = ? ORDER BY semester ASC, record_number ASC');
    $stmt_h->execute([$student['id'], $year]);
    $health_records = $stmt_h->fetchAll();
    
    // จัดกลุ่มข้อมูลสุขภาพ
    $health_data = [
        1 => [1 => null, 2 => null],
        2 => [1 => null, 2 => null]
    ];
    foreach ($health_records as $hr) {
        $health_data[$hr['semester']][$hr['record_number']] = $hr;
    }

    // ดึงสรุปเวลาเรียน (attendance)
    // นับวันที่มีการเช็คชื่อในห้องเรียนนี้ทั้งหมดเป็น "เวลาเต็ม"
    $stmt_att_total = $pdo->prepare('
        SELECT 
            MONTH(check_date) as m, 
            YEAR(check_date) as y,
            COUNT(DISTINCT check_date) as total_days
        FROM attendance
        WHERE classroom_id = ? AND academic_year = ?
        GROUP BY y, m
    ');
    $stmt_att_total->execute([$classroom_id, $year]);
    $attendance_summary = [];
    foreach ($stmt_att_total->fetchAll() as $row) {
        $attendance_summary[$row['y'] . '-' . $row['m']]['total'] = $row['total_days'];
    }

    // นับวันที่นักเรียนมาเรียน (present/late)
    $stmt_att_present = $pdo->prepare('
        SELECT 
            MONTH(check_date) as m, 
            YEAR(check_date) as y,
            COUNT(DISTINCT check_date) as present_days
        FROM attendance
        WHERE student_id = ? AND academic_year = ? AND status IN ("present", "late")
        GROUP BY y, m
    ');
    $stmt_att_present->execute([$student['id'], $year]);
    foreach ($stmt_att_present->fetchAll() as $row) {
        $attendance_summary[$row['y'] . '-' . $row['m']]['present'] = $row['present_days'];
    }

    // ดึงบันทึกพฤติกรรม (ความคิดเห็นครู)
    $target_sem = $semester === 'annual' ? 2 : $semester;
    
    // หากเป็นรายปี ให้ลองหาเทอม 2 ก่อน ถ้าไม่มีให้เอาเทอม 1
    $stmt_behavior = $pdo->prepare('
        SELECT bc.name as category_name, sbr.behavior_text
        FROM behavior_categories bc
        LEFT JOIN student_behavior_records sbr ON bc.id = sbr.category_id 
            AND sbr.student_id = ? 
            AND sbr.academic_year = ?
            AND sbr.id = (
                SELECT id FROM student_behavior_records 
                WHERE student_id = ? AND category_id = bc.id AND academic_year = ? 
                AND semester = (
                    CASE 
                        WHEN ? = 2 THEN (
                            IFNULL((SELECT semester FROM student_behavior_records WHERE student_id = ? AND category_id = bc.id AND academic_year = ? AND semester = 2 LIMIT 1), 1)
                        )
                        ELSE ?
                    END
                )
                ORDER BY check_date DESC LIMIT 1
            )
        WHERE bc.name IN ("หน้าที่รับผิดชอบ ความเอาใจใส่การเรียน", "การใช้เวลาว่าง", "ความสัมพันธ์กับบุคคลรอบข้าง", "อุปนิสัย บุคลิกภาพ", "สุขภาพ")
        ORDER BY FIELD(bc.name, "หน้าที่รับผิดชอบ ความเอาใจใส่การเรียน", "การใช้เวลาว่าง", "ความสัมพันธ์กับบุคคลรอบข้าง", "อุปนิสัย บุคลิกภาพ", "สุขภาพ")
    ');
    
    $stmt_behavior->execute([
        $student['id'], $year,
        $student['id'], $year, $target_sem,
        $student['id'], $year, $target_sem
    ]);
    $behavior_comments = $stmt_behavior->fetchAll();

    // ดึงความคิดเห็นผู้ปกครอง (parent_feedback)
    // หากเป็นรายปี (target_sem = 2) ให้ดึงของเทอม 2 มาก่อน ถ้าไม่มีให้ดึงเทอม 1
    if ($semester === 'annual') {
        $stmt_parent = $pdo->prepare('
            SELECT * FROM parent_feedback 
            WHERE student_id = ? AND academic_year = ? 
            ORDER BY semester DESC 
            LIMIT 1
        ');
        $stmt_parent->execute([$student['id'], $year]);
    } else {
        $stmt_parent = $pdo->prepare('SELECT * FROM parent_feedback WHERE student_id = ? AND academic_year = ? AND semester = ?');
        $stmt_parent->execute([$student['id'], $year, $semester]);
    }
    $parent_feedback = $stmt_parent->fetch();

    // ดึงหน้าต่างๆ มาแสดงตามลำดับใหม่
    include 'p6_page2.php'; // หน้าปก (หน้าที่ 1)
    include 'p6_page3.php'; // หน้าคู่มือ (หน้าที่ 2)
    include 'p6_page4.php'; // ข้อมูลนักเรียน (หน้าที่ 3)
    include 'p6_page1.php'; // หน้าสรุปคะแนน (หน้าที่ 4)
    include 'p6_page5.php'; // สุขภาพและเวลาเรียน (หน้าที่ 5)
    include 'p6_page6.php'; // ความคิดเห็นครูและผู้ปกครอง (หน้าที่ 6)
endforeach;
?>
</body>
</html>
