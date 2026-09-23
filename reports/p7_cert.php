<?php
// ใบรับรองผลการศึกษา (ปพ.7)
$gpa_level = $_GET['gpa_level'] ?? ''; // e.g. "ป.4"
$gpa_semester = $_GET['gpa_semester'] ?? 'annual'; // "1", "2", or "annual"

if ($gpa_level) {
    $sem_query = ($gpa_semester === 'annual') ? "" : "AND g.semester = " . (int)$gpa_semester;
    $stmt_gpa = $pdo->prepare("
        SELECT SUM(g.grade_point * s.credits) / NULLIF(SUM(s.credits), 0)
        FROM grades g 
        JOIN subjects s ON g.subject_id = s.id 
        WHERE g.student_id = ? AND s.level = ? $sem_query
    ");
    $stmt_gpa->execute([$student_id, $gpa_level]);
    $avg_gpa = $stmt_gpa->fetchColumn();
    $avg_gpa = $avg_gpa ? number_format($avg_gpa, 2) : '';
} else {
    // Default: Fetch GPA for level 4 and 5 from grades and subjects tables
    $stmt_gpa4 = $pdo->prepare('
        SELECT SUM(g.grade_point * s.credits) / NULLIF(SUM(s.credits), 0)
        FROM grades g 
        JOIN subjects s ON g.subject_id = s.id 
        WHERE g.student_id = ? AND s.level = "ป.4"
    ');
    $stmt_gpa4->execute([$student_id]);
    $gpa4 = $stmt_gpa4->fetchColumn();

    $stmt_gpa5 = $pdo->prepare('
        SELECT SUM(g.grade_point * s.credits) / NULLIF(SUM(s.credits), 0)
        FROM grades g 
        JOIN subjects s ON g.subject_id = s.id 
        WHERE g.student_id = ? AND s.level = "ป.5"
    ');
    $stmt_gpa5->execute([$student_id]);
    $gpa5 = $stmt_gpa5->fetchColumn();

    $avg_gpa = '';
    if ($gpa4 && $gpa5) {
        $avg_gpa = number_format(($gpa4 + $gpa5) / 2, 2);
    } elseif ($gpa4 || $gpa5) {
        $avg_gpa = number_format($gpa4 ?: $gpa5, 2);
    }
}
?>
<style>
    /* P7 Specific Styles */
    .p7-header {
        position: absolute;
        top: 15mm;
        right: 25mm;
        font-weight: bold;
        font-size: 14pt;
    }

    .p7-garuda {
        text-align: center;
        margin-bottom: 5px;
    }

    .p7-garuda img {
        height: 3cm;
    }

    .p7-title {
        text-align: center;
        font-weight: bold;
        font-size: 18pt;
        margin-bottom: 25px; /* Increased spacing */
    }

    :root {
        --p7-line-height: 1.5;
        --p7-row-margin: 1px;
        --p7-dotted-spacing: 3px;
    }

    .p7-content {
        font-size: 13pt;
        line-height: var(--p7-line-height);
    }

    .p7-row {
        display: flex;
        justify-content: flex-start;
        margin-bottom: var(--p7-row-margin);
        align-items: baseline;
        flex-wrap: nowrap;
    }

    .p7-line {
        border-bottom: 1px dotted black;
        margin: 0 var(--p7-dotted-spacing);
        padding: 0 2px;
        text-align: center;
        min-height: 1em;
        line-height: 0.8;
        display: inline-block;
        flex: 1;
    }

    .p7-footer-note {
        position: absolute;
        bottom: 10mm;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 11pt;
        font-style: italic;
    }

    .p7-photo-box {
        width: 3cm;
        height: 4cm;
        border: 1px solid black;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10pt;
        text-align: center;
    }
</style>

<div class="doc-page" style="padding: 15mm 20mm;">
    <div class="p7-header">ปพ.7</div>
    <div class="p7-garuda">
        <img src="<?= $garuda_url ?>" alt="Garuda" referrerPolicy="no-referrer">
    </div>
    <div class="p7-title">ใบรับรองผลการศึกษา</div>
    
    <div class="p7-content">
        <div class="p7-row">
            โรงเรียน <span class="p7-line"><?= $school_name ?></span>
        </div>
        <div class="p7-row">
            อำเภอ / เขต <span class="p7-line"><?= $school_district ?: '-' ?></span> 
            จังหวัด <span class="p7-line"><?= $school_province ?: '-' ?></span>
        </div>
        <div class="p7-row">
            ขอรับรองว่า <span class="p7-line"><?= trim(($student['prefix'] ?? '') . ($student['name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?: '-' ?></span>
        </div>
        <div class="p7-row">
            เลขประจำตัวนักเรียน <span class="p7-line"><?= $student['student_code'] ?: '-' ?></span> 
            เลขประจำตัวประชาชน <span class="p7-line"><?= $student['national_id'] ?: '-' ?></span>
        </div>
        <div class="p7-row">
            เกิดเมื่อวันที่ <span class="p7-line" style="min-width: 40px; flex: 0 0 auto;"><?= formatDocDateThai($student['birthday'])[0] ?: '-' ?></span> 
            เดือน <span class="p7-line"><?= formatDocDateThai($student['birthday'])[1] ?: '-' ?></span> 
            พ.ศ. <span class="p7-line" style="min-width: 60px; flex: 0 0 auto;"><?= formatDocDateThai($student['birthday'])[2] ?: '-' ?></span>
            เชื้อชาติ <span class="p7-line" style="min-width: 60px; flex: 0 0 auto;"><?= $student['race'] ?: '-' ?></span>
            สัญชาติ <span class="p7-line" style="min-width: 60px; flex: 0 0 auto;"><?= $student['nationality'] ?: '-' ?></span>
        </div>
        <div class="p7-row">
            ชื่อ – ชื่อสกุลบิดา <span class="p7-line"><?= trim(($student['father_name'] ?? '') . ' ' . ($student['father_last_name'] ?? '')) ?: '-' ?></span> 
            ชื่อ – ชื่อสกุลมารดา <span class="p7-line"><?= trim(($student['mother_name'] ?? '') . ' ' . ($student['mother_last_name'] ?? '')) ?: '-' ?></span>
        </div>
        
        <div style="margin-top: 25px; margin-bottom: 10px;">มีสภาพทางการเรียน ดังนี้</div>
        <div class="p7-row" style="padding-left: 60px;">
            กำลังศึกษาอยู่ในโรงเรียน <span class="p7-line"><?= $school_name ?></span>
        </div>
        
        <div class="p7-row">
            ได้ผลการเรียนเฉลี่ยสะสม <span class="p7-line"><?= $avg_gpa ?></span>
        </div>
        
        <div class="p7-row" style="margin-top: 35px; padding-left: 60px;">
            ออกให้ ณ วันที่ <span class="p7-line" style="flex: 0 0 40px;"><?= $day ?></span> 
            เดือน <span class="p7-line" style="flex: 0 0 120px;"><?= $month ?></span> 
            พ.ศ. <span class="p7-line" style="flex: 0 0 60px;"><?= $year ?></span>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; margin-top: 50px; align-items: flex-start;">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 15px;">
            <div class="p7-photo-box">
                รูปถ่าย<br>1.5 นิ้ว
            </div>
            <div style="text-align: center; width: 250px;">
                .......................................................<br>
                ( <?= $registrar_name ?: '.......................................................' ?> )<br>
                นายทะเบียน
            </div>
        </div>
        <div style="width: 350px; margin-top: 60px;">
            <div style="text-align: center;">
                .......................................................<br>
                ( <?= $director_name ?: '.......................................................' ?> )<br>
                ผู้อำนวยการโรงเรียน<?= $school_name ?>
            </div>
        </div>
    </div>

    <div class="p7-footer-note">
        ( หมายเหตุ : ใบรับรองนี้มีกำหนดใช้ภายใน 120 วัน นับตั้งแต่วันออก )
    </div>
</div>
