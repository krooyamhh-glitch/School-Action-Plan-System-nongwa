<?php
require_once 'report_header.php';

$year = $_GET['year'] ?? '';
$semester = $_GET['semester'] ?? '1';
$type = $_GET['type'] ?? 'subject';
$assignment_id = $_GET['assignment_id'] ?? '';
$classroom_id = $_GET['classroom_id'] ?? '';
$approval_date_raw = $_GET['approval_date'] ?? '';
$approval_date = formatThaiDate($approval_date_raw);

if ($type === 'subject' && $assignment_id) {
    // ดึงข้อมูลการสอน
    $stmt = $pdo->prepare('
        SELECT ta.*, s.name as subject_name, s.code as subject_code, s.hours, s.credits,
               c.level, c.room, u.name as teacher_name
        FROM teacher_assignments ta
        JOIN subjects s ON ta.subject_id = s.id
        JOIN classrooms c ON ta.classroom_id = c.id
        JOIN users u ON ta.teacher_id = u.id
        WHERE ta.id = ?
    ');
    $stmt->execute([$assignment_id]);
    $assignment = $stmt->fetch();
    
    if (!$assignment) die('Assignment not found');
    
    $classroom_id = $assignment['classroom_id'];
    $subject_id = $assignment['subject_id'];
    $level = formatLevelName($assignment['level']);
    $room = $assignment['room'];
    $subject_name = $assignment['subject_name'];
    $subject_code = $assignment['subject_code'];
    $teacher_name = $assignment['teacher_name'];
} else if ($type === 'class' && $classroom_id) {
    // ดึงข้อมูลห้องเรียน
    $stmt = $pdo->prepare('SELECT * FROM classrooms WHERE id = ?');
    $stmt->execute([$classroom_id]);
    $classroom = $stmt->fetch();
    
    if (!$classroom) die('Classroom not found');
    
    $level = formatLevelName($classroom['level']);
    $room = $classroom['room'];
    
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
    
    $teacher_name = $ct['t1_name'] ? $ct['t1_name'] . ' ' . $ct['t1_last'] : '';
    $teacher_pos = formatTeacherPosition($ct['t1_pos'] ?? 'ครู');
    if ($ct['t2_name']) {
        $teacher_name .= ($teacher_name ? ' / ' : '') . $ct['t2_name'] . ' ' . $ct['t2_last'];
    }
    
    // Fallback to Learner Development assignment if no classroom teacher is set in the classrooms table
    if (!$teacher_name) {
        $stmt_ld = $pdo->prepare('
            SELECT u.name, u.last_name, u.position
            FROM learner_development_assignments lda
            JOIN users u ON lda.teacher_id = u.id
            WHERE lda.classroom_id = ? AND lda.academic_year = ?
            LIMIT 1
        ');
        $stmt_ld->execute([$classroom_id, $year]);
        $ld_t = $stmt_ld->fetch();
        if ($ld_t) {
            $teacher_name = $ld_t['name'] . ' ' . $ld_t['last_name'];
            $teacher_pos = formatTeacherPosition($ld_t['position'] ?? 'ครู');
        }
    }
    
    if (!$teacher_name) {
        $teacher_name = $_SESSION['name'];
        $teacher_pos = formatTeacherPosition($_SESSION['position'] ?? 'ครู');
    }
} else {
    die('Invalid parameters');
}

// Fetch teacher position for subject type if not already fetched
if ($type === 'subject' && !isset($teacher_pos)) {
    $stmt_p = $pdo->prepare('SELECT position FROM users WHERE id = ?');
    $stmt_p->execute([$assignment['teacher_id']]);
    $u_pos = $stmt_p->fetch();
    $teacher_pos = formatTeacherPosition($u_pos['position'] ?? 'ครู');
}

// ดึงรายชื่อนักเรียน
$student_fields = "s.*, 
    IFNULL(sp.prefix, s.prefix) AS prefix, 
    IFNULL(sp.name, s.name) AS name, 
    IFNULL(sp.last_name, s.last_name) AS last_name,
    IFNULL(sp.gender, s.gender) AS gender,
    IFNULL(sp.birthday, s.birthday) AS birthday";

$stmt = $pdo->prepare("SELECT $student_fields FROM students s LEFT JOIN student_profiles sp ON s.student_profile_id = sp.id WHERE s.classroom_id = ? AND s.academic_year = ? AND s.status = 'studying' ORDER BY s.student_code ASC");
$stmt->execute([$classroom_id, $year]);
$students = $stmt->fetchAll();

if ($type === 'class' && $classroom_id) {
    include 'p5_classroom_cover.php';
    exit;
} else if ($type === 'subject' && $assignment_id) {
    $no_footer = true;
    include 'p5_cover.php';
    include 'p5_subject_pages.php';
    echo '</body></html>';
    exit;
}

// สถิตินักเรียน
$total_students = count($students);
$male_students = count(array_filter($students, fn($s) => $s['gender'] === 'ชาย' || $s['prefix'] === 'เด็กชาย' || $s['prefix'] === 'นาย'));
$female_students = $total_students - $male_students;

?>

<div class="page">
    <div class="header">
        <img src="<?= !empty($logo_url) ? $logo_url : $garuda_url ?>" class="logo" referrerPolicy="no-referrer">
        <h2 style="margin: 5px 0;">สมุดบันทึกการพัฒนาคุณภาพผู้เรียน (ปพ.5)</h2>
        <h3 style="margin: 5px 0;"><?= $school_name ?></h3>
        <p><?= $affiliation ?></p>
    </div>

    <div style="margin-bottom: 20px;">
        <table class="border-none no-border">
            <tr>
                <td class="text-left">ชั้น <?= $level ?>/<?= $room ?></td>
                <td class="text-left"><?= $semester === 'annual' ? '' : 'ภาคเรียนที่ ' . $semester ?></td>
                <td class="text-left">ปีการศึกษา <?= $year ?></td>
            </tr>
            <?php if ($type === 'subject'): ?>
            <tr>
                <td class="text-left" colspan="2">รายวิชา <?= $subject_code ?> <?= $subject_name ?></td>
                <td class="text-left">ครูผู้สอน <?= $teacher_name ?></td>
            </tr>
            <?php else: ?>
            <tr>
                <td class="text-left" colspan="3">ครูประจำชั้น <?= $teacher_name ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <table class="no-border" style="margin-bottom: 10px;">
        <tr>
            <td class="text-left">นักเรียนต้นปีการศึกษา</td>
            <td>ชาย <?= $male_students ?> คน</td>
            <td>หญิง <?= $female_students ?> คน</td>
            <td>รวม <?= $total_students ?> คน</td>
        </tr>
    </table>

    <h4 style="text-align: center; margin: 10px 0;">สรุปผลสัมฤทธิ์ทางการเรียนรู้</h4>
    <table>
        <thead>
            <tr>
                <th rowspan="2">รหัส</th>
                <th rowspan="2">รายวิชา</th>
                <th colspan="10">ระดับผลการเรียน</th>
            </tr>
            <tr>
                <th>มส</th>
                <th>ร</th>
                <th>0</th>
                <th>1</th>
                <th>1.5</th>
                <th>2</th>
                <th>2.5</th>
                <th>3</th>
                <th>3.5</th>
                <th>4</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // ถ้าเป็นรายวิชาเดียว
            if ($type === 'subject') {
                $grades_count = array_fill_keys(['มส', 'ร', '0', '1', '1.5', '2', '2.5', '3', '3.5', '4'], 0);
                
                $stmt = $pdo->prepare('SELECT grade FROM grades WHERE subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester = ?');
                $stmt->execute([$subject_id, $classroom_id, $year, $semester === 'annual' ? 0 : $semester]); // สมมติ 0 คือรายปี
                $grades = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($grades as $g) {
                    if (isset($grades_count[$g])) $grades_count[$g]++;
                }
                
                echo "<tr>";
                echo "<td>$subject_code</td>";
                echo "<td class='text-left'>$subject_name</td>";
                foreach ($grades_count as $count) {
                    echo "<td>" . ($count > 0 ? $count : '-') . "</td>";
                }
                echo "</tr>";
            } else {
                // ถ้าเป็นรายชั้น ต้องดึงทุกวิชาในห้องนั้น
                $stmt = $pdo->prepare('
                    SELECT DISTINCT s.id, s.code, s.name 
                    FROM teacher_assignments ta
                    JOIN subjects s ON ta.subject_id = s.id
                    WHERE ta.classroom_id = ? AND ta.academic_year = ?
                ');
                $stmt->execute([$classroom_id, $year]);
                $subjects = $stmt->fetchAll();
                
                foreach ($subjects as $s) {
                    $grades_count = array_fill_keys(['มส', 'ร', '0', '1', '1.5', '2', '2.5', '3', '3.5', '4'], 0);
                    $stmt_g = $pdo->prepare('SELECT grade FROM grades WHERE subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester = ?');
                    $stmt_g->execute([$s['id'], $classroom_id, $year, $semester === 'annual' ? 0 : $semester]);
                    $grades = $stmt_g->fetchAll(PDO::FETCH_COLUMN);
                    
                    foreach ($grades as $g) {
                        if (isset($grades_count[$g])) $grades_count[$g]++;
                    }
                    
                    echo "<tr>";
                    echo "<td>{$s['code']}</td>";
                    echo "<td class='text-left'>{$s['name']}</td>";
                    foreach ($grades_count as $count) {
                        echo "<td>" . ($count > 0 ? $count : '-') . "</td>";
                    }
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>

    <div style="margin-top: 40px;">
        <table class="border-none no-border">
            <tr>
                <td style="width: 33%; text-align: center;">
                    <p>ลงชื่อ..........................................................</p>
                    <p>( <?= $teacher_name ?> )</p>
                    <p>ตำแหน่ง <?= $teacher_pos ?></p>
                    <p style="font-size: 12px; color: #666;"><?= $type === 'subject' ? 'ครูผู้สอน' : 'ครูประจำชั้น' ?></p>
                </td>
                <td style="width: 33%; text-align: center;">
                    <p>ลงชื่อ..........................................................</p>
                    <?php if ($deputy_director_name): ?>
                    <p>( <?= $deputy_director_name ?> )</p>
                    <p>ตำแหน่ง <?= $deputy_director_position ?></p>
                    <?php else: ?>
                    <p>( <?= $academic_head_name ?: '..........................................................' ?> )</p>
                    <p>ตำแหน่ง <?= $academic_head_position ?></p>
                    <?php endif; ?>
                </td>
                <td style="width: 33%; text-align: center;">
                    <p>ลงชื่อ..........................................................</p>
                    <p>( <?= $director_name ?: '..........................................................' ?> )</p>
                    <p>ตำแหน่ง ผู้อำนวยการโรงเรียน<?= $school_name ?></p>
                </td>
            </tr>
        </table>
        <?php if ($approval_date['day']): ?>
        <div style="text-align: center; margin-top: 15px;">
            วันที่ <?= $approval_date['day'] ?> เดือน <?= $approval_date['month'] ?> พ.ศ. <?= $approval_date['year'] ?>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
