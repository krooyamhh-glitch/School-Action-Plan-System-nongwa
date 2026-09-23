<!-- หน้าที่ 4: ข้อมูลนักเรียน และ บันทึกการเปลี่ยนแปลง -->
<div class="page student-info-page">
    <h3 class="text-center" style="font-size: 20px; margin-top: 20px; margin-bottom: 15px;">ข้อมูลนักเรียน</h3>
    
    <!-- ช่องติดรูปถ่าย (กึ่งกลาง) -->
    <div style="display: flex; justify-content: center; margin-bottom: 30px;">
        <div style="width: 2.5cm; height: 3.2cm; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 11px; padding: 2px; color: #666; line-height: 1.4;">
            รูปถ่ายนักเรียน<br>ขนาด 1 นิ้ว
        </div>
    </div>

    <div style="text-align: left; padding: 0 40px; font-size: 16px; line-height: 1.2;">
        <div style="display: flex; margin-bottom: 20px;">
            <span style="white-space: nowrap;">ชื่อ</span> 
            <span class="dotted-line" style="flex: 1; margin: 0 5px;"><?= $student['prefix'] ?><?= $student['name'] ?></span> 
            <span style="white-space: nowrap;">สกุล</span> 
            <span class="dotted-line" style="flex: 1; margin-left: 5px;"><?= $student['last_name'] ?></span>
        </div>
        <div style="display: flex; margin-bottom: 20px;">
            <span style="white-space: nowrap;">วันเกิด</span> 
            <span class="dotted-line" style="flex: 1; margin: 0 5px;"><?= $bday['day'] ?> <?= $bday['month'] ?> <?= $bday['year'] ?></span> 
            <span style="white-space: nowrap;">อายุ</span> 
            <span class="dotted-line" style="width: 50px; margin: 0 5px;"><?= $age_years ?></span> 
            <span style="white-space: nowrap;">ปี</span> 
            <span class="dotted-line" style="width: 50px; margin: 0 5px;"><?= $age_months ?></span> 
            <span style="white-space: nowrap;">เดือน</span>
        </div>
        <div style="display: flex; margin-bottom: 20px;">
            <span style="white-space: nowrap;">เพศ</span> 
            <span class="dotted-line" style="width: 60px; margin: 0 5px;"><?= $student['gender'] === 'male' || $student['gender'] === 'ชาย' ? 'ชาย' : 'หญิง' ?></span> 
            <span style="white-space: nowrap;">เชื้อชาติ</span> 
            <span class="dotted-line" style="width: 100px; margin: 0 5px;"><?= $student['race'] ?: 'ไทย' ?></span> 
            <span style="white-space: nowrap;">สัญชาติ</span> 
            <span class="dotted-line" style="width: 100px; margin: 0 5px;"><?= $student['nationality'] ?: 'ไทย' ?></span> 
            <span style="white-space: nowrap;">ศาสนา</span> 
            <span class="dotted-line" style="flex: 1; margin-left: 5px;"><?= $student['religion'] ?: 'พุทธ' ?></span>
        </div>
        <div style="display: flex; margin-bottom: 20px;">
            <span style="white-space: nowrap;">เลขประจำตัวนักเรียน</span> 
            <span class="dotted-line" style="width: 150px; margin: 0 5px;"><?= $student['student_code'] ?></span> 
            <span style="white-space: nowrap;">เลขประจำตัวประชาชน</span> 
            <span class="dotted-line" style="flex: 1; margin-left: 5px;"><?= $student['national_id'] ?></span>
        </div>
        <div style="display: flex; margin-bottom: 20px;">
            <span style="white-space: nowrap;">ที่อยู่ปัจจุบัน</span> 
            <span class="dotted-line" style="flex: 1; margin-left: 5px; text-align: left;">
                <?= trim(($student['house_no'] ?? '') . ' ' . 
                    (!empty($student['moo']) ? 'หมู่ที่ ' . $student['moo'] : '') . ' ' . 
                    (!empty($student['road_soi']) ? 'ถ./ซอย ' . $student['road_soi'] : '') . ' ' . 
                    (!empty($student['sub_district']) ? 'ต.' . $student['sub_district'] : '') . ' ' . 
                    (!empty($student['district']) ? 'อ.' . $student['district'] : '') . ' ' . 
                    (!empty($student['province_name']) ? 'จ.' . $student['province_name'] : '')) ?>
            </span>
        </div>
        <div style="display: flex; margin-bottom: 20px;">
            <span style="white-space: nowrap;">ชื่อ-สกุลบิดา</span> 
            <span class="dotted-line" style="flex: 1; margin: 0 5px;"><?= $student['father_name'] ?> <?= $student['father_last_name'] ?></span> 
            <span style="white-space: nowrap;">อาชีพ</span> 
            <span class="dotted-line" style="flex: 1; margin-left: 5px;"><?= $student['father_occupation'] ?></span>
        </div>
        <div style="display: flex; margin-bottom: 20px;">
            <span style="white-space: nowrap;">ชื่อ-สกุลมารดา</span> 
            <span class="dotted-line" style="flex: 1; margin: 0 5px;"><?= $student['mother_name'] ?> <?= $student['mother_last_name'] ?></span> 
            <span style="white-space: nowrap;">อาชีพ</span> 
            <span class="dotted-line" style="flex: 1; margin-left: 5px;"><?= $student['mother_occupation'] ?></span>
        </div>
        <div style="display: flex; margin-bottom: 20px;">
            <span style="white-space: nowrap;">ชื่อ-สกุลผู้ปกครอง</span> 
            <span class="dotted-line" style="flex: 1; margin: 0 5px;"><?= $student['parent_name'] ?> <?= $student['parent_last_name'] ?></span> 
            <span style="white-space: nowrap;">อาชีพ</span> 
            <span class="dotted-line" style="flex: 1; margin-left: 5px;"><?= $student['parent_occupation'] ?></span>
        </div>
        <div style="display: flex; margin-bottom: 20px;">
            <span style="white-space: nowrap;">ความเกี่ยวข้องกับนักเรียน</span> 
            <span class="dotted-line" style="flex: 1; margin-left: 5px;"><?= $student['parent_relationship'] ?></span>
        </div>
    </div>

    <h3 class="text-center" style="font-size: 18px; margin-top: 30px; margin-bottom: 15px;">บันทึกการเปลี่ยนแปลงหรือแก้ไขข้อมูล</h3>
    <div style="padding: 0 40px;">
        <?php for($i=0; $i<12; $i++): ?>
            <div class="dotted-line" style="width: 100%; height: 25px; margin-bottom: 5px;"></div>
        <?php endfor; ?>
    </div>
</div>
