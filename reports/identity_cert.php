<?php
// หนังสือรับรองการมีตัวตนของนักเรียน (แบบฟอร์มเปล่า - ปรับปรุงเลย์เอาต์ตามภาพ)
?>
<style>
    .nowrap { white-space: nowrap; }
    .flex-row { display: flex; align-items: baseline; margin-bottom: 8px; width: 100%; }
    .line-fill { border-bottom: 1px dotted #000; flex: 1; margin: 0 4px; height: 1.2em; }
    .line-stretch { border-bottom: 1px dotted #000; display: inline-block; padding: 0 4px; height: 1.2em; }
</style>

<div class="doc-page">
    <div class="header-logo">
        <img src="<?= $garuda_url ?>" alt="Garuda" referrerPolicy="no-referrer">
    </div>
    <div class="doc-title" style="margin-top: 10px;">หนังสือรับรองการมีตัวตนของนักเรียน</div>
    
    <div style="text-align: right; margin-top: 25px; margin-bottom: 30px; padding-right: 20px;">
        <span class="nowrap">วันที่</span> <span class="line-stretch" style="min-width: 20px;"></span> 
        <span class="nowrap">เดือน</span> <span class="line-stretch" style="min-width: 80px;"></span> 
        <span class="nowrap">ปี</span> <span class="line-stretch" style="min-width: 40px;"></span>
    </div>
    
    <div class="flex-row" style="margin-top: 20px;">
        <span class="nowrap" style="margin-left: 80px;">ข้าพเจ้า</span>
        <div class="line-fill"></div>
        <span class="nowrap">อยู่บ้านเลขที่</span>
        <div class="line-stretch" style="min-width: 70px;"></div>
        <span class="nowrap">หมู่ที่</span>
        <div class="line-stretch" style="min-width: 40px;"></div>
        <span class="nowrap">ตำบล</span>
        <div class="line-stretch" style="min-width: 140px;"></div>
    </div>
    
    <div class="flex-row">
        <span class="nowrap">อำเภอ</span>
        <div class="line-stretch" style="min-width: 160px;"></div>
        <span class="nowrap">จังหวัด</span>
        <div class="line-fill"></div>
    </div>
    
    <div class="flex-row">
		<span class="nowrap">ขอรับรองว่า</span>
		<div class="line-stretch" style="min-width: 220px;"></div>
        <span class="nowrap">เกิดวันที่</span>
        <div class="line-stretch" style="min-width: 30px;"></div>
        <span class="nowrap">เดือน</span>
        <div class="line-stretch" style="min-width: 60px;"></div>
        <span class="nowrap">พ.ศ.</span>
        <div class="line-fill"></div>
    </div>
	
	    <div class="flex-row">
        <span class="nowrap">เป็นบุตร/อยู่ในความปกครองของ</span>
        <div class="line-fill"></div>
    </div>
    
    <div class="flex-row">
        <span class="nowrap">อาศัยอยู่บ้านเลขที่</span>
        <div class="line-stretch" style="min-width: 20px;"></div>
        <span class="nowrap">หมู่ที่</span>
        <div class="line-stretch" style="min-width: 20px;"></div>
        <span class="nowrap">ตำบล</span>
        <div class="line-stretch" style="min-width: 80px;"></div>
        <span class="nowrap">อำเภอ</span>
        <div class="line-stretch" style="min-width: 80px;"></div>
        <span class="nowrap">จังหวัด</span>
        <div class="line-fill"></div>
    </div>
    
    <div class="flex-row">
        <span class="nowrap">ซึ่งปัจจุบันมีตัวตน ผู้ปกครองและนักเรียนอยู่ในท้องที่หมู่บ้าน</span>
        <div class="line-fill"></div>
        <span class="nowrap">จริง</span>
    </div>

    <div style="margin-top: 60px;">
        <div class="signature-section" style="margin-bottom: 50px; float: right; clear: both;">
            <div style="text-align: left; display: inline-block;">
                (ลงชื่อ)..........................................................ผู้รับรอง<br>
                (..........................................................)<br>
                ตำแหน่ง..........................................................
            </div>
        </div>

        <div class="signature-section" style="margin-bottom: 50px; float: right; clear: both;">
            <div style="text-align: left; display: inline-block;">
                (ลงชื่อ)..........................................................ผู้รับรอง<br>
                (..........................................................)<br>
                ตำแหน่ง..........................................................
            </div>
        </div>

        <div class="signature-section" style="margin-bottom: 50px; float: right; clear: both;">
            <div style="text-align: left; display: inline-block;">
                (ลงชื่อ)..........................................................ผู้รับรอง<br>
                (..........................................................)<br>
                ตำแหน่ง..........................................................
            </div>
        </div>
    </div>
</div>
