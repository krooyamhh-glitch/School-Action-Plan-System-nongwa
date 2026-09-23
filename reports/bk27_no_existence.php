<?php
// แบบ บค.๒๗ หนังสือรับรองการไม่มีตัวตน
?>
<div class="doc-page">
    <div class="header-logo">
        <img src="<?= $garuda_url ?>" alt="Garuda" referrerPolicy="no-referrer">
    </div>
    <div class="form-label">แบบ บค.๒๗</div>
    <div class="doc-title">หนังสือรับรองการไม่มีตัวตน</div>
    
    <div class="text-right" style="margin-bottom: 10px;">
        เขียนที่ <span class="dotted-line" style="min-width: 150px;"><?= $_GET['location'] ?? $school_name ?></span>
    </div>
    <div class="text-right" style="margin-bottom: 30px;">
        วันที่ <span class="dotted-line" style="min-width: 30px;"><?= $day ?></span> 
        เดือน <span class="dotted-line" style="min-width: 80px;"><?= $month ?></span> 
        พ.ศ. <span class="dotted-line" style="min-width: 50px;"><?= $year ?></span>
    </div>
    
    <div class="content-row" style="margin-top: 20px;">
        <span class="indent"></span>ด้วยข้าพเจ้า <span class="dotted-line" style="min-width: 200px;">................................</span> 
        ตำแหน่ง <span class="dotted-line" style="min-width: 150px;">................................</span>
    </div>
    
    <div class="content-row">
        <span class="indent"></span>ขอรับรองว่า <?= $student['prefix'] ?><?= $student['name'] ?> <?= $student['last_name'] ?>
        เกิดวันที่ <span class="dotted-line"><?= formatDocDateThai($student['birthday'])[0] ?></span> 
        เดือน <span class="dotted-line"><?= formatDocDateThai($student['birthday'])[1] ?></span> 
        พ.ศ. <span class="dotted-line"><?= formatDocDateThai($student['birthday'])[2] ?></span>
    </div>
    <div class="content-row">
        เลขประจำตัวประชาชน <span class="dotted-line" style="min-width: 150px;"><?= $student['national_id'] ?></span> 
        เป็นบุตรอยู่ในความปกครองของ <span class="dotted-line" style="min-width: 200px;">................................</span>
    </div>
    
    <div class="content-row">
        อาศัยอยู่บ้านเลขที่ <span class="dotted-line" style="min-width: 50px;">.........</span> 
        หมู่ที่ <span class="dotted-line" style="min-width: 30px;">.....</span> 
        แขวง/ตำบล <span class="dotted-line" style="min-width: 100px;">................</span>
        เขต/อำเภอ <span class="dotted-line" style="min-width: 100px;">................</span> 
        จังหวัด <span class="dotted-line" style="min-width: 100px;">................</span>
    </div>
    
    <div class="content-row" style="margin-top: 10px;">
        ซึ่งปัจจุบันไม่มีตัวตนผู้ปกครองและนักเรียนอยู่ในท้องที่ เนื่องจากได้อพยพไปอยู่ที่อื่น โดยไม่ได้แจ้งการย้ายออกจากทะเบียนบ้าน (ทร.๑๔)
    </div>
    
    <div class="signature-section" style="margin-top: 80px;">
        ขอแสดงความนับถือ<br><br><br>
        (ลงชื่อ).......................................................<br>
        ( <?= $registrar_name ?: '.......................................................' ?> )<br>
        ตำแหน่ง นายทะเบียน
    </div>
</div>
