<?php
// แบบ บค.๒๑ ขออนุญาตจำหน่ายนักเรียน
?>
<div class="doc-page">
    <div class="form-label">แบบ บค.๒๑</div>
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
        <span class="font-bold">เรื่อง</span> ขออนุญาตจำหน่ายนักเรียน
    </div>
    <div class="content-row">
        <span class="font-bold">เรียน</span> ผู้อำนวยการสำนักงานเขตพื้นที่การศึกษา <span class="dotted-line" style="min-width: 200px;">................................</span>
    </div>
    
    <div class="content-row" style="margin-top: 20px;">
        <span class="indent"></span>ด้วยโรงเรียน <span class="dotted-line" style="min-width: 150px;"><?= $school_name ?></span> 
        มีความประสงค์ขออนุญาตจำหน่ายนักเรียนออกจากทะเบียนนักเรียนด้วยสาเหตุ <span class="dotted-line" style="min-width: 150px;"><?= $_GET['reason'] ?? '................' ?></span> ดังนี้
    </div>
    
    <div class="content-row">
        <span class="indent"></span>๑. <?= $student['prefix'] ?><?= $student['name'] ?> <?= $student['last_name'] ?>
        เกิดวันที่ <span class="dotted-line"><?= formatDocDateThai($student['birthday'])[0] ?></span> 
        เดือน <span class="dotted-line"><?= formatDocDateThai($student['birthday'])[1] ?></span> 
        พ.ศ. <span class="dotted-line"><?= formatDocDateThai($student['birthday'])[2] ?></span>
    </div>
    <div class="content-row">
        เลขประจำตัวประชาชน <span class="dotted-line" style="min-width: 150px;"><?= $student['national_id'] ?></span> 
        นักเรียนชั้น <span class="dotted-line" style="min-width: 50px;"><?= formatLevelName($student['level']) ?></span>
    </div>
    
    <div class="content-row" style="margin-top: 20px;">
        <span class="indent"></span>จึงเรียนมาเพื่อโปรดพิจารณาอนุญาต
    </div>
    
    <div class="signature-section">
        ขอแสดงความนับถือ<br><br><br>
        (ลงชื่อ).......................................................<br>
        ( <?= $director_name ?> )<br>
        ผู้อำนวยการโรงเรียน<?= $school_name ?>
    </div>
</div>
