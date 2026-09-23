<?php
/**
 * หน้าที่ 1: สรุปผลการเรียนรายหน่วย
 */

// 1. ดึงข้อมูลหน่วยการเรียนรู้
$semester_query = $semester === 'annual' ? "IN (1, 2)" : "= ?";
$semester_params = $semester === 'annual' ? [] : [$semester];

$stmt_units = $pdo->prepare("SELECT * FROM learning_units WHERE subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester $semester_query ORDER BY id ASC");
$stmt_units->execute(array_merge([$subject_id, $classroom_id, $year], $semester_params));
$units = $stmt_units->fetchAll();

// 2. ดึงคะแนนรายหน่วย
$stmt_scores = $pdo->prepare("
    SELECT us.* 
    FROM unit_scores us
    JOIN learning_units lu ON us.learning_unit_id = lu.id
    WHERE lu.subject_id = ? AND lu.classroom_id = ? AND lu.academic_year = ? AND lu.semester $semester_query
");
$stmt_scores->execute(array_merge([$subject_id, $classroom_id, $year], $semester_params));
$unit_scores_raw = $stmt_scores->fetchAll();
$unit_scores = [];
foreach ($unit_scores_raw as $s) {
    $unit_scores[$s['student_id']][$s['learning_unit_id']] = $s['score'];
}

// 3. ดึงคะแนนสรุปและเกรด
$stmt_grades = $pdo->prepare("SELECT * FROM grades WHERE subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester $semester_query");
$stmt_grades->execute(array_merge([$subject_id, $classroom_id, $year], $semester_params));
$grades_raw = $stmt_grades->fetchAll();
$student_grades = [];
foreach ($grades_raw as $g) {
    $student_grades[$g['student_id']] = $g;
}
?>

<!-- Page 1: Unit Scores -->
<style>
    .page-unit-summary {
        padding-left: 10mm !important;
        padding-right: 10mm !important;
    }
    .page-unit-summary table {
        font-size: 12px; /* ลดขนาดตัวอักษร */
    }
    .page-unit-summary th, .page-unit-summary td {
        padding: 3px 2px !important; /* ลด padding */
    }
    .col-unit {
        width: 30px !important; /* ปรับคอลัมน์หน่วยให้แคบลง */
    }
    .col-name {
        width: auto !important; /* ให้ชื่อขยายตามพื้นที่ */
        min-width: 180px;
    }
    .col-summary {
        width: 45px !important;
    }
</style>
<div class="page page-unit-summary">
    <div class="header">
        <h3 style="margin: 0;">สรุปผลการเรียนรายหน่วยการเรียนรู้</h3>
        <p style="margin: 5px 0;">รายวิชา <?= $subject_code ?> <?= $subject_name ?> ชั้น <?= $level ?>/<?= $room ?> <?= $semester === 'annual' ? '' : 'ภาคเรียนที่ ' . $semester ?> ปีการศึกษา <?= $year ?></p>
    </div>

    <table class="table-bordered">
        <thead>
            <tr>
                <th style="width: 35px;">เลขที่</th>
                <th class="col-name">ชื่อ - นามสกุล</th>
                <?php foreach ($units as $index => $unit): ?>
                    <th class="col-unit">น.<?= $index + 1 ?></th>
                <?php endforeach; ?>
                <th class="col-summary">รวมหน่วย</th>
                <th class="col-summary">ปลายภาค</th>
                <th class="col-summary">รวม</th>
                <th style="width: 35px;">เกรด</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $student): ?>
                <?php $g = $student_grades[$student['id']] ?? null; ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="text-left"><?= $student['prefix'] ?><?= $student['name'] ?> <?= $student['last_name'] ?></td>
                    <?php foreach ($units as $unit): ?>
                        <td><?= $unit_scores[$student['id']][$unit['id']] ?? '-' ?></td>
                    <?php endforeach; ?>
                    <td><?= $g['score_units'] ?? '-' ?></td>
                    <td><?= $g['score_final'] ?? '-' ?></td>
                    <td><?= $g['score_total'] ?? '-' ?></td>
                    <td class="font-bold"><?= $g['grade'] ?? '-' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Page 2: Unit Notes -->
<div class="page">
    <div class="header">
        <h3 style="margin: 0;">หมายเหตุหน่วยการเรียนรู้</h3>
        <p style="margin: 5px 0;">รายวิชา <?= $subject_code ?> <?= $subject_name ?> ชั้น <?= $level ?>/<?= $room ?> <?= $semester === 'annual' ? '' : 'ภาคเรียนที่ ' . $semester ?> ปีการศึกษา <?= $year ?></p>
    </div>

    <div style="margin-top: 20px; font-size: 16px;">
        <table class="table-bordered">
            <thead>
                <tr>
                    <th style="width: 80px;">หน่วยที่</th>
                    <th>ชื่อหน่วยการเรียนรู้</th>
                    <th style="width: 100px;">คะแนนเต็ม</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($units as $index => $unit): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="text-left"><?= $unit['unit_name'] ?></td>
                        <td><?= $unit['max_score'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
/**
 * หน้าที่ 3: เวลาเรียน
 */
// ดึงข้อมูลการมาเรียน
$stmt_att = $pdo->prepare('
    SELECT student_id, check_date, status 
    FROM attendance 
    WHERE classroom_id = ? AND academic_year = ? AND semester = ?
    ORDER BY check_date ASC
');
$stmt_att->execute([$classroom_id, $year, $semester]);
$att_data = $stmt_att->fetchAll();

$months = []; // [ 'YYYY-MM' => [ 'name' => '...', 'days' => [date1, date2, ...] ] ]
$student_att = []; // [ student_id => [ 'YYYY-MM' => count_present ] ]

$thai_months = [
    '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
    '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
    '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
];

foreach ($att_data as $row) {
    $m_key = substr($row['check_date'], 0, 7);
    if (!isset($months[$m_key])) {
        $m_parts = explode('-', $m_key);
        $months[$m_key] = [
            'name' => $thai_months[$m_parts[1]],
            'days' => []
        ];
    }
    if (!in_array($row['check_date'], $months[$m_key]['days'])) {
        $months[$m_key]['days'][] = $row['check_date'];
    }
    
    if (!isset($student_att[$row['student_id']][$m_key])) {
        $student_att[$row['student_id']][$m_key] = 0;
    }
    if ($row['status'] === 'present') {
        $student_att[$row['student_id']][$m_key]++;
    }
}

$total_school_days = 0;
foreach ($months as $m) $total_school_days += count($m['days']);
?>

<div class="page">
    <div class="header">
        <h3 style="margin: 0;">บันทึกเวลาเรียน</h3>
        <p style="margin: 5px 0;">รายวิชา <?= $subject_code ?> <?= $subject_name ?> ชั้น <?= $level ?>/<?= $room ?> <?= $semester === 'annual' ? '' : 'ภาคเรียนที่ ' . $semester ?> ปีการศึกษา <?= $year ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 40px;">เลขที่</th>
                <th rowspan="2">ชื่อ - นามสกุล</th>
                <?php foreach ($months as $m): ?>
                    <th colspan="2"><?= $m['name'] ?></th>
                <?php endforeach; ?>
                <th colspan="2">รวม</th>
                <th rowspan="2">%</th>
            </tr>
            <tr>
                <?php foreach ($months as $m): ?>
                    <th style="width: 40px;">มา</th>
                    <th style="width: 40px;">เต็ม</th>
                <?php endforeach; ?>
                <th style="width: 40px;">มา</th>
                <th style="width: 40px;">เต็ม</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $student): ?>
                <?php 
                $total_present = 0; 
                ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="text-left"><?= $student['prefix'] ?><?= $student['name'] ?> <?= $student['last_name'] ?></td>
                    <?php foreach ($months as $m_key => $m): ?>
                        <?php 
                        $present = $student_att[$student['id']][$m_key] ?? 0;
                        $full = count($m['days']);
                        $total_present += $present;
                        ?>
                        <td><?= $present ?></td>
                        <td><?= $full ?></td>
                    <?php endforeach; ?>
                    <td><?= $total_present ?></td>
                    <td><?= $total_school_days ?></td>
                    <td><?= $total_school_days > 0 ? round(($total_present / $total_school_days) * 100, 1) : 0 ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
/**
 * หน้าที่ 3: คุณลักษณะอันพึงประสงค์
 */
$stmt_char = $pdo->prepare('SELECT * FROM characteristics_scores WHERE subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester = ?');
$stmt_char->execute([$subject_id, $classroom_id, $year, $semester]);
$char_scores = [];
foreach ($stmt_char->fetchAll() as $row) {
    $char_scores[$row['student_id']] = $row;
}
?>

<div class="page">
    <div class="header">
        <h3 style="margin: 0;">บันทึกผลการประเมินคุณลักษณะอันพึงประสงค์</h3>
        <p style="margin: 5px 0;">รายวิชา <?= $subject_code ?> <?= $subject_name ?> ชั้น <?= $level ?>/<?= $room ?> <?= $semester === 'annual' ? '' : 'ภาคเรียนที่ ' . $semester ?> ปีการศึกษา <?= $year ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 40px;">เลขที่</th>
                <th rowspan="2">ชื่อ - นามสกุล</th>
                <th colspan="8">คุณลักษณะอันพึงประสงค์ (ข้อที่)</th>
                <th rowspan="2">เฉลี่ย</th>
                <th rowspan="2">สรุป</th>
            </tr>
            <tr>
                <?php for($i=1; $i<=8; $i++): ?>
                    <th style="width: 30px;"><?= $i ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $student): ?>
                <?php 
                $s = $char_scores[$student['id']] ?? null; 
                $avg = $s ? $s['average_score'] : 0;
                $result = '-';
                if ($s) {
                    if ($avg >= 2.5) $result = 'ดีเยี่ยม (3)';
                    else if ($avg >= 1.5) $result = 'ดี (2)';
                    else if ($avg >= 0.5) $result = 'ผ่าน (1)';
                    else $result = 'ไม่ผ่าน (0)';
                }
                ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="text-left"><?= $student['prefix'] ?><?= $student['name'] ?> <?= $student['last_name'] ?></td>
                    <?php for($i=1; $i<=8; $i++): ?>
                        <td><?= $s ? $s['item'.$i] : '-' ?></td>
                    <?php endfor; ?>
                    <td><?= $s ? round($avg, 2) : '-' ?></td>
                    <td><?= $result ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 13px;">
        <p class="font-bold">รายการประเมิน:</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px;">
            <div>1. รักชาติ ศาสน์ กษัตริย์</div>
            <div>2. ซื่อสัตย์สุจริต</div>
            <div>3. มีวินัย</div>
            <div>4. ใฝ่เรียนรู้</div>
            <div>5. อยู่อย่างพอเพียง</div>
            <div>6. มุ่งมั่นในการทำงาน</div>
            <div>7. รักความเป็นไทย</div>
            <div>8. มีจิตสาธารณะ</div>
        </div>
    </div>
</div>

<?php
/**
 * หน้าที่ 4: อ่าน คิดวิเคราะห์ และเขียน
 */
$stmt_ana = $pdo->prepare('SELECT * FROM analytical_scores WHERE subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester = ?');
$stmt_ana->execute([$subject_id, $classroom_id, $year, $semester]);
$ana_scores = [];
foreach ($stmt_ana->fetchAll() as $row) {
    $ana_scores[$row['student_id']] = $row;
}
?>

<div class="page">
    <div class="header">
        <h3 style="margin: 0;">บันทึกผลการประเมินการอ่าน คิดวิเคราะห์ และเขียน</h3>
        <p style="margin: 5px 0;">รายวิชา <?= $subject_code ?> <?= $subject_name ?> ชั้น <?= $level ?>/<?= $room ?> <?= $semester === 'annual' ? '' : 'ภาคเรียนที่ ' . $semester ?> ปีการศึกษา <?= $year ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 40px;">เลขที่</th>
                <th rowspan="2">ชื่อ - นามสกุล</th>
                <th colspan="5">รายการประเมิน (ข้อที่)</th>
                <th rowspan="2">เฉลี่ย</th>
                <th rowspan="2">สรุป</th>
            </tr>
            <tr>
                <?php for($i=1; $i<=5; $i++): ?>
                    <th style="width: 40px;"><?= $i ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $student): ?>
                <?php 
                $s = $ana_scores[$student['id']] ?? null; 
                $avg = $s ? $s['average_score'] : 0;
                $result = '-';
                if ($s) {
                    if ($avg >= 2.5) $result = 'ดีเยี่ยม (3)';
                    else if ($avg >= 1.5) $result = 'ดี (2)';
                    else if ($avg >= 0.5) $result = 'ผ่าน (1)';
                    else $result = 'ไม่ผ่าน (0)';
                }
                ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="text-left"><?= $student['prefix'] ?><?= $student['name'] ?> <?= $student['last_name'] ?></td>
                    <?php for($i=1; $i<=5; $i++): ?>
                        <td><?= $s ? $s['item'.$i] : '-' ?></td>
                    <?php endfor; ?>
                    <td><?= $s ? round($avg, 2) : '-' ?></td>
                    <td><?= $result ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 13px;">
        <p class="font-bold">รายการประเมิน:</p>
        <div>1. สามารถอ่านเพื่อการหาข้อมูล สารสนเทศ เสริมสร้างความรู้ ประสบการณ์และการประยุกต์ใช้ในชีวิตประจำวัน</div>
        <div>2. สามารถจับประเด็นสำคัญ ลำดับเหตุการณ์จากการอ่านสื่อที่มีความซับซ้อน</div>
        <div>3. สามารถวิเคราะห์สิ่งที่ผู้เขียนต้องการสื่อสารกับผู้อ่าน และสามารถวิพากษ์ให้ข้อเสนอแนะในแง่มุมต่างๆ</div>
        <div>4. สามารถประเมินความถูกต้องเหมาะสม ความน่าเชื่อถือของสิ่งที่อ่านในแง่มุมต่างๆ</div>
        <div>5. สามารถเขียนแสดงความคิดเห็น วางแผน ตัดสินใจ แก้ปัญหา และถ่ายทอดผ่านการเขียนที่มีขั้นตอน</div>
    </div>
</div>

<?php
/**
 * หน้าที่ 6: สมรรถนะสำคัญของผู้เรียน
 */
$stmt_comp = $pdo->prepare('SELECT * FROM competency_scores WHERE classroom_id = ? AND academic_year = ? AND semester = ?');
$stmt_comp->execute([$classroom_id, $year, $semester]);
$comp_scores = [];
foreach ($stmt_comp->fetchAll() as $row) {
    $comp_scores[$row['student_id']] = $row;
}
?>

<div class="page">
    <div class="header">
        <h3 style="margin: 0;">บันทึกผลการประเมินสมรรถนะสำคัญของผู้เรียน</h3>
        <p style="margin: 5px 0;">ชั้น <?= $level ?>/<?= $room ?> <?= $semester === 'annual' ? '' : 'ภาคเรียนที่ ' . $semester ?> ปีการศึกษา <?= $year ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 40px;">เลขที่</th>
                <th rowspan="2">ชื่อ - นามสกุล</th>
                <th colspan="5">สมรรถนะสำคัญ (ข้อที่)</th>
                <th rowspan="2">เฉลี่ย</th>
                <th rowspan="2">สรุป</th>
            </tr>
            <tr>
                <?php for($i=1; $i<=5; $i++): ?>
                    <th style="width: 40px;"><?= $i ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $student): ?>
                <?php 
                $s = $comp_scores[$student['id']] ?? null; 
                $avg = $s ? $s['average_score'] : 0;
                $result = '-';
                if ($s) {
                    if ($avg >= 2.5) $result = 'ดีเยี่ยม (3)';
                    else if ($avg >= 1.5) $result = 'ดี (2)';
                    else if ($avg >= 0.5) $result = 'ผ่าน (1)';
                    else $result = 'ไม่ผ่าน (0)';
                }
                ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="text-left"><?= $student['prefix'] ?><?= $student['name'] ?> <?= $student['last_name'] ?></td>
                    <?php for($i=1; $i<=5; $i++): ?>
                        <td><?= $s ? $s['item'.$i] : '-' ?></td>
                    <?php endfor; ?>
                    <td><?= $s ? round($avg, 2) : '-' ?></td>
                    <td><?= $result ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 13px;">
        <p class="font-bold">รายการประเมิน:</p>
        <div>1. ความสามารถในการสื่อสาร</div>
        <div>2. ความสามารถในการคิด</div>
        <div>3. ความสามารถในการแก้ปัญหา</div>
        <div>4. ความสามารถในการใช้ทักษะชีวิต</div>
        <div>5. ความสามารถในการใช้เทคโนโลยี</div>
    </div>
</div>
