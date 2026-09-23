<?php
// แบบ บค.๒๐ หนังสือส่งนักเรียน
?>
<div class="doc-page">
    <div class="form-label">แบบ บค.๒๐</div>
    <div class="header-logo">
        <img src="<?= $garuda_url ?>" alt="Garuda" referrerPolicy="no-referrer">
    </div>
    
    <div class="content-row">
        ที่ <span class="dotted-line" style="min-width: 150px;"><?= $_GET['no'] ?? '................' ?></span>
        <span style="float: right;">โรงเรียน <span class="dotted-line" style="min-width: 150px;"><?= $school_name ?></span></span>
    </div>
    <div class="text-right" style="margin-bottom: 30px;">
        วันที่ <span class="dotted-line" style="min-width: 30px;"><?= $day ?></span> 
        เดือน <span class="dotted-line" style="min-width: 80px;"><?= $month ?></span> 
        พ.ศ. <span class="dotted-line" style="min-width: 50px;"><?= $year ?></span>
    </div>
    
    <div class="content-row">
        <span class="font-bold">เรื่อง</span> ส่งนักเรียนขอย้ายมาเข้าเรียน
    </div>
    <div class="content-row">
        <span class="font-bold">เรียน</span> ผู้อำนวยการสถานศึกษาโรงเรียน <span class="dotted-line" style="min-width: 200px;"><?= $_GET['dest_school'] ?? '................................' ?></span>
    </div>
    
    <div class="content-row">
        <span class="font-bold">สิ่งที่ส่งมาด้วย</span> ๑. แบบ ปพ.๑ จำนวน ๑ ฉบับ
    </div>
    <div class="content-row">
        <span class="indent" style="width: 2.8cm;"></span> ๒. แบบ ปพ.๘ จำนวน ๑ ฉบับ
    </div>
    <div class="content-row">
        <span class="indent" style="width: 2.8cm;"></span> ๓. แบบ ปพ.๙ จำนวน ๑ ฉบับ
    </div>
    
    <div class="content-row" style="margin-top: 20px; display: flex;">
        <span class="indent"></span>ด้วย <span class="dotted-line" style="flex: 1;"><?= $student['prefix'] ?? '' ?><?= $student['name'] ?? '................' ?> <?= $student['last_name'] ?? '................' ?></span> 
        ได้ขอย้ายนักเรียนในปกครองมาเข้าเรียน
    </div>
    <div class="content-row">
        ในสถานศึกษานี้ ได้แก่
    </div>
    
    <div class="content-row" style="display: flex; margin-top: 10px;">
        <span class="indent"></span>๑. <span class="dotted-line" style="flex: 1;"><?= $student['prefix'] ?? '' ?><?= $student['name'] ?? '................' ?> <?= $student['last_name'] ?? '................' ?></span>
        เกิดวันที่ <span class="dotted-line" style="min-width: 30px;"><?= $student['birthday'] ? formatDocDateThai($student['birthday'])[0] : '....' ?></span> 
        เดือน <span class="dotted-line" style="min-width: 100px;"><?= $student['birthday'] ? formatDocDateThai($student['birthday'])[1] : '................' ?></span> 
        พ.ศ. <span class="dotted-line" style="min-width: 50px;"><?= $student['birthday'] ? formatDocDateThai($student['birthday'])[2] : '........' ?></span>
    </div>
    <div class="content-row" style="display: flex;">
        เลขประจำตัวประชาชน <span class="dotted-line" style="flex: 1;"><?= $student['national_id'] ?? '...........................' ?></span> 
        นักเรียนชั้น <span class="dotted-line" style="flex: 1;"><?= formatLevelName($student['level'] ?? '................') ?></span>
    </div>
    
    <div class="content-row" style="margin-top: 20px;">
        <span class="indent"></span>จึงเรียนมาเพื่อโปรดทราบและดำเนินการต่อไป
    </div>
    
    <div class="signature-section">
        ขอแสดงความนับถือ<br><br><br>
        (ลงชื่อ).......................................................<br>
        ( <?= $director_name ?> )<br>
        ผู้อำนวยการโรงเรียน<?= $school_name ?>
    </div>
</div>
