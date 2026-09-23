<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$current_user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin' || ($_SESSION['is_academic'] ?? false));

$target_id = $_GET['target_id'] ?? $current_user_id;
$target_type = $_GET['target_type'] ?? 'teacher'; // 'teacher' or 'classroom'

// Security check: If not admin, can only print own timetable
if (!$is_admin && $target_id != $current_user_id) {
    die("Unauthorized access to other's timetable");
}

$academic_year = $_GET['academic_year'] ?? '2567';
$semester = $_GET['semester'] ?? 1;

try {
    $teacher_full_name = "";
    $teacher_position = "";
    $classroom_name = "";
    $display_title = "";
    $school_id = $_SESSION['school_id'];
    
    if ($target_type === 'teacher') {
        // Get Teacher Info
        $stmt = $pdo->prepare("SELECT name, last_name, position, school_id FROM users WHERE id = ?");
        $stmt->execute([$target_id]);
        $teacher = $stmt->fetch();
        $teacher_full_name = ($teacher['name'] ?? '') . ' ' . ($teacher['last_name'] ?? '');
        $teacher_position = $teacher['position'] ?: 'ครู';
        $display_title = "คุณครู" . $teacher_full_name;
        $school_id = $teacher['school_id'];
        
        $where = "t.teacher_id = ?";
        $params = [$target_id, $academic_year, $semester];
    } else {
        // Get Classroom Info
        $stmt = $pdo->prepare("SELECT level, room, school_id FROM classrooms WHERE id = ?");
        $stmt->execute([$target_id]);
        $classroom = $stmt->fetch();
        $classroom_name = $classroom['level'] . '/' . $classroom['room'];
        $display_title = "ห้องเรียนชั้นประถมศึกษาปีที่ " . $classroom_name;
        $school_id = $classroom['school_id'];
        
        $where = "t.classroom_id = ?";
        $params = [$target_id, $academic_year, $semester];
    }

    // Get School Info
    $stmt = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
    $stmt->execute([$school_id]);
    $school = $stmt->fetch();
    
    // Fix Logo URL
    $logo_url = $school['logo_url'] ?? '';
    if ($logo_url && !preg_match('/^https?:\/\//', $logo_url)) {
        $logo_url = '../../' . $logo_url;
    }

    // Get Timetable
    $stmt = $pdo->prepare("
        SELECT t.*, 
               s.name as subject_name, s.code as subject_code, 
               c.level, c.room,
               u.name as teacher_name, u.last_name as teacher_last_name
        FROM timetables t
        LEFT JOIN subjects s ON t.subject_id = s.id
        LEFT JOIN classrooms c ON t.classroom_id = c.id
        LEFT JOIN users u ON t.teacher_id = u.id
        WHERE $where AND t.academic_year = ? AND t.semester = ?
        ORDER BY t.day_of_week ASC, t.period_number ASC
    ");
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    $timetable = [];
    foreach ($items as $it) {
        if (!empty($it['activity_type'])) {
            $activities = [
                'guidance' => ['name' => 'กิจกรรมแนะแนว', 'code' => 'แนะแนว'],
                'scouts' => ['name' => 'กิจกรรมลูกเสือเนตรนารี', 'code' => 'ลูกเสือเนตรนารี'],
                'scout' => ['name' => 'กิจกรรมลูกเสือเนตรนารี', 'code' => 'ลูกเสือเนตรนารี'],
                'club' => ['name' => 'กิจกรรมชุมนุม', 'code' => 'ชุมนุม'],
                'social' => ['name' => 'กิจกรรมเพื่อสังคมและสาธารณประโยชน์', 'code' => 'กิจกรรมเพื่อสังคมฯ'],
                'lunch' => ['name' => 'พักรับประทานอาหาร', 'code' => 'พักกลางวัน'],
                'homeroom' => ['name' => 'Home Room', 'code' => 'โฮมรูม'],
                'reducing_time' => ['name' => 'กิจกรรมลดเวลาเรียน เพิ่มเวลารู้', 'code' => 'ลดเวลาเรียนฯ'],
                'prayer' => ['name' => 'กิจกรรมสวดมนต์', 'code' => 'สวดมนต์']
            ];
            $act = $activities[strtolower($it['activity_type'])] ?? null;
            if ($act) {
                $it['subject_name'] = $act['name'];
                $it['subject_code'] = $act['code'];
            }
        }
        
        // Deduplicate activity slots at classroom level (only display once per classroom per day/period for each activity type)
        if ($target_type === 'classroom' && !empty($it['activity_type'])) {
            $day = $it['day_of_week'];
            $period = $it['period_number'];
            $act_type = strtolower($it['activity_type']);
            $already_has_act = false;
            if (isset($timetable[$day][$period])) {
                foreach ($timetable[$day][$period] as $existing) {
                    if (!empty($existing['activity_type']) && strtolower($existing['activity_type']) === $act_type) {
                        $already_has_act = true;
                        break;
                    }
                }
            }
            if ($already_has_act) {
                continue;
            }
        }
        
        $timetable[$it['day_of_week']][$it['period_number']][] = $it;
    }

    // NEW: Calculate teaching workload summary by slot (deduplicate simultaneous combined classes)
    $workload = [];
    $total_hours = 0;
    if ($target_type === 'teacher') {
        $slot_map = [];
        foreach ($items as $it) {
            // Skip lunch from workload calculation
            if (isset($it['activity_type']) && strtolower($it['activity_type']) === 'lunch') continue;

            $period_key = $it['day_of_week'] . '_' . $it['period_number'];
            if (!isset($slot_map[$period_key])) {
                $slot_map[$period_key] = [];
            }
            $slot_map[$period_key][] = $it;
        }

        foreach ($slot_map as $period_key => $period_slots) {
            $sCodes = [];
            $sNames = [];
            $levels = [];
            $is_activity_workload = false;
            
            foreach ($period_slots as $it) {
                if (!empty($it['activity_type'])) {
                    $is_activity_workload = true;
                }
                $sName = $it['subject_name'];
                $sCode = $it['subject_code'];
                
                // Handle activities that might not have subject mapped
                if (empty($sCode) && !empty($it['activity_type'])) {
                    $activities = [
                        'guidance' => ['name' => 'กิจกรรมแนะแนว', 'code' => 'แนะแนว'],
                        'scouts' => ['name' => 'กิจกรรมลูกเสือเนตรนารี', 'code' => 'ลูกเสือเนตรนารี'],
                        'scout' => ['name' => 'กิจกรรมลูกเสือเนตรนารี', 'code' => 'ลูกเสือเนตรนารี'],
                        'club' => ['name' => 'กิจกรรมชุมนุม', 'code' => 'ชุมนุม'],
                        'social' => ['name' => 'กิจกรรมเพื่อสังคมและสาธารณประโยชน์', 'code' => 'กิจกรรมเพื่อสังคมฯ'],
                        'homeroom' => ['name' => 'Home Room', 'code' => 'โฮมรูม'],
                        'reducing_time' => ['name' => 'กิจกรรมลดเวลาเรียน เพิ่มเวลารู้', 'code' => 'ลดเวลาเรียนฯ'],
                        'prayer' => ['name' => 'กิจกรรมสวดมนต์', 'code' => 'สวดมนต์']
                    ];
                    $act = $activities[strtolower($it['activity_type'])] ?? null;
                    if ($act) {
                        $sCode = $act['code'];
                        $sName = $act['name'];
                    } else {
                        $sCode = 'กิจกรรม';
                        $sName = 'กิจกรรมอื่นๆ';
                    }
                }

                if (!empty($sCode)) {
                    $sCodes[] = $sCode;
                    $sNames[] = $sName;
                    if (empty($it['activity_type']) && !empty($it['level'])) {
                        $levels[] = $it['level'] . '/' . $it['room'];
                    }
                }
            }

            if (empty($sCodes)) continue;

            $unique_codes = array_unique($sCodes);
            $unique_names = array_unique($sNames);
            $unique_levels = array_unique($levels);

            // Combine name and code representation for workload row
            $combined_code = implode(' + ', $unique_codes);
            $combined_name = implode(' + ', $unique_names);
            if (!empty($unique_levels)) {
                $combined_name .= ' (' . implode(', ', $unique_levels) . ')';
            }

            if (!isset($workload[$combined_code])) {
                $workload[$combined_code] = [
                    'code' => $combined_code,
                    'name' => $combined_name,
                    'hours' => 0,
                    'is_activity' => $is_activity_workload
                ];
            }
            $workload[$combined_code]['hours']++;
            $total_hours++;
        }
        
        // Sort: general subjects (is_activity = false) first, and activities (is_activity = true) last.
        // Within each group, sort alphabetically by code.
        uasort($workload, function($a, $b) {
            if ($a['is_activity'] !== $b['is_activity']) {
                return $a['is_activity'] ? 1 : -1;
            }
            return strcmp($a['code'], $b['code']);
        });
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$days = [
    1 => ['name' => 'จันทร์', 'color' => '#fefce8'],
    2 => ['name' => 'อังคาร', 'color' => '#fdf2f8'],
    3 => ['name' => 'พุธ', 'color' => '#f0fdf4'],
    4 => ['name' => 'พฤหัสบดี', 'color' => '#fff7ed'],
    5 => ['name' => 'ศุกร์', 'color' => '#eff6ff']
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตารางสอน - <?= $display_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page {
                margin: 0.5cm;
            }
            
            /* First page landscape, others portrait or handled by break */
            .page-landscape {
                page: landscape-page;
                page-break-after: always;
            }
            .page-portrait {
                page: portrait-page;
                page-break-before: always;
            }
            
            body { 
                -webkit-print-color-adjust: exact; 
                padding: 0; 
                background: white !important;
            }
            .a4-landscape, .a4-portrait {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none !important;
                border: none !important;
            }
            .no-print { display: none; }
        }
        
        @page landscape-page { size: A4 landscape; }
        @page portrait-page { size: A4 portrait; }

        body {
            font-family: 'Sarabun', sans-serif;
            background: #f8fafc;
        }
        .a4-landscape {
            width: 297mm;
            height: 210mm;
            margin: 0 auto;
            background: white;
            padding: 0.6cm 1cm 0.4cm 1cm;
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .a4-portrait {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: white;
            padding: 0.6cm 0.8cm 0.4cm 0.8cm;
            position: relative;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        table { border-collapse: collapse; width: 100%; }
        .timetable-table { table-layout: fixed; }
        .timetable-table th, .timetable-table td { border: 1.2px solid #64748b; padding: 2px; text-align: center; height: 50px; overflow: hidden; }
        .timetable-table th { background: #f1f5f9; font-weight: 700; color: #1e293b; font-size: 13px; height: 35px; }
        
        .workload-table th, .workload-table td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; }
        .workload-table th { background: #f8fafc; font-size: 13px; font-weight: 800; }
        .workload-table td { font-size: 13px; }
        .workload-table .center { text-align: center; }
        
        .period-header { font-size: 10px; color: #64748b; font-weight: normal; margin-top: 1px; }
    </style>
</head>
<body class="p-4">
    <div class="no-print mb-8 text-center text-slate-500">
        <p class="mb-2 text-sm italic">แนะนำ: ตั้งค่า "Margins" เป็น "None" หรือ "Minimum" ในหน้าต่างจัดการงานพิมพ์ เพื่อผลลัพธ์ที่ดีที่สุด</p>
        <button onclick="window.print()" class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all cursor-pointer inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            สั่งพิมพ์ตารางสอน
        </button>
    </div>

    <div class="a4-landscape page-landscape">
        <div class="relative mb-2 pb-2 border-b-2 border-slate-100 flex items-center min-h-[70px]">
            <!-- Logo positioned absolutely to keep text centered -->
            <div class="absolute left-0">
                <?php if (!empty($logo_url)): ?>
                    <img src="<?= $logo_url ?>" class="w-16 h-16 object-contain" referrerPolicy="no-referrer">
                <?php else: ?>
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 flex items-center justify-center text-slate-300 text-[10px] text-center p-2 uppercase">Logo</div>
                <?php endif; ?>
            </div>
            
            <!-- Centered Header Text -->
            <div class="w-full text-center px-32">
                <h1 class="text-xl font-black text-slate-800 tracking-tight">ตารางสอนโรงเรียน<?= $school['name'] ?></h1>
                <div class="mt-0.5">
                    <p class="text-base font-bold text-blue-700"><?= $display_title ?> <?= $target_type === 'teacher' ? 'ตำแหน่ง: ' . $teacher_position : '' ?></p>
                </div>
                <div class="flex items-center justify-center gap-3 mt-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                    <span class="bg-slate-100 px-3 py-0.5 rounded-full border border-slate-200">ปีการศึกษา <?= $academic_year ?></span>
                    <span class="bg-indigo-50 text-indigo-600 px-3 py-0.5 rounded-full border border-indigo-100">ภาคเรียนที่ <?= $semester ?></span>
                </div>
            </div>
        </div>

        <div class="overflow-hidden">
            <table class="timetable-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">วัน / คาบ</th>
                        <?php for($i=1; $i<=8; $i++): ?>
                            <th>
                                <div>คาบที่ <?= $i ?></div>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($days as $dayId => $day): ?>
                    <tr>
                        <td style="background: <?= $day['color'] ?>; font-weight: bold; border-left: 5px solid #94a3b8;"><?= $day['name'] ?></td>
                        <?php for($p=1; $p<=8; $p++): 
                                $slots = $timetable[$dayId][$p] ?? [];
                                $isLunch = false;
                                foreach ($slots as $s) {
                                    if (isset($s['activity_type']) && $s['activity_type'] === 'lunch') {
                                        $isLunch = true;
                                    }
                                }
                        ?>
                            <td class="<?= $isLunch ? 'bg-orange-50' : '' ?>">
                                <?php if(!empty($slots)): ?>
                                    <?php 
                                        $isCombined = (count($slots) > 1);
                                        foreach($slots as $slotIdx => $slot): 
                                            $isActivity = !empty($slot['activity_type']);
                                            $singleLineActs = ['scouts', 'scout', 'club', 'guidance', 'prayer'];
                                            $isSingleLine = $isActivity && in_array(strtolower($slot['activity_type']), $singleLineActs);
                                            $isLunchSlot = $isActivity && strtolower($slot['activity_type']) === 'lunch';
                                    ?>
                                        <?php if($slotIdx > 0): ?>
                                            <div style="border-top: 1px dashed #cbd5e1; margin: 4px 0; padding-top: 4px;"></div>
                                        <?php endif; ?>

                                        <?php if($isCombined): ?>
                                            <?php if($isSingleLine || $isLunchSlot): ?>
                                                <div class="text-[11px] font-bold text-blue-700 leading-tight"><?= $slot['subject_code'] ?></div>
                                            <?php else: ?>
                                                <div class="text-[11px] font-bold <?= $isLunch ? 'text-orange-700' : 'text-blue-700' ?> leading-tight">
                                                    <?= $slot['subject_code'] ?>
                                                    <?php if($target_type === 'teacher' && !$isActivity && $slot['level']): ?>
                                                        <span class="text-slate-500 font-normal italic text-[10px] ml-1">(<?= $slot['level'] ?>/<?= $slot['room'] ?>)</span>
                                                    <?php elseif($target_type !== 'teacher' && !$isActivity && !empty($slot['teacher_name'])): ?>
                                                        <span class="text-slate-500 font-normal italic text-[10px] ml-1">(<?= $slot['teacher_name'] ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if($isSingleLine): ?>
                                                <div class="text-[12px] font-bold text-blue-700 leading-tight"><?= $slot['subject_code'] ?></div>
                                            <?php else: ?>
                                                <div class="text-[12px] font-bold <?= $isLunch ? 'text-orange-700' : 'text-blue-700' ?> leading-tight"><?= $slot['subject_code'] ?></div>
                                                <div class="text-[10px] my-0.5"><?= $slot['subject_name'] ?></div>
                                                <?php if($target_type === 'teacher'): ?>
                                                    <?php if(!$isActivity && $slot['level']): ?>
                                                        <div class="text-[10px] font-bold text-slate-500 italic"><?= $slot['level'] ?>/<?= $slot['room'] ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if(!$isActivity): ?>
                                                        <div class="text-[10px] font-bold text-slate-500 italic"><?= ($slot['teacher_name'] ?? '') ?></div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4">
            <div class="text-center">
                <?php if($target_type === 'teacher'): ?>
                    <p class="mb-2">ลงชื่อ..........................................................</p>
                    <p class="font-bold text-sm">( <?= $teacher_full_name ?> )</p>
                    <p class="text-[10px]">ครูผู้สอน</p>
                <?php else: ?>
                    <p class="mb-2">ลงชื่อ..........................................................</p>
                    <p class="font-bold text-sm">( <?= $school['director_name'] ?: '..........................................................' ?> )</p>
                    <p class="text-[10px]">ผู้อำนวยการโรงเรียน</p>
                <?php endif; ?>
            </div>
            <div class="text-center">
                <p class="mb-2">ลงชื่อ..........................................................</p>
                <p class="font-bold text-sm">( <?= $school['director_name'] ?: '..........................................................' ?> )</p>
                <p class="text-[10px]">ผู้อำนวยการโรงเรียน<?= $school['name'] ?></p>
            </div>
        </div>

        <div class="absolute bottom-4 right-8 text-[8px] text-slate-400">
            พิมพ์เมื่อ: <?= date('d/m/Y H:i') ?> | ระบบบริหารงานวิชาการดิจิทัล | หน้าที่ 1
        </div>
    </div>

    <?php if($target_type === 'teacher' && !empty($workload)): ?>
    <div class="a4-portrait page-portrait">
        <!-- Centered Header -->
        <div class="text-center mb-6 border-b pb-4">
             <div class="flex justify-center mb-3">
                <?php if (!empty($logo_url)): ?>
                    <img src="<?= $logo_url ?>" class="w-16 h-16 object-contain" referrerPolicy="no-referrer">
                <?php endif; ?>
            </div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">ภาระงานการสอนของคุณครู</h2>
            <p class="text-lg font-bold text-blue-700 mt-1"><?= $teacher_full_name ?></p>
            <div class="flex items-center justify-center gap-3 mt-2">
                <p class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-0.5 rounded-full border border-slate-200">ภาคเรียนที่ <?= $semester ?>/<?= $academic_year ?></p>
                <p class="text-xs font-semibold text-slate-500">โรงเรียน<?= $school['name'] ?></p>
            </div>
        </div>

        <table class="workload-table">
            <thead>
                <tr>
                    <th class="center" style="width: 12%;">ลำดับ</th>
                    <th style="width: 18%;">รหัสวิชา</th>
                    <th style="width: 50%;">ชื่อวิชา / กิจกรรม</th>
                    <th class="center" style="width: 20%;">ชั่วโมง/สัปดาห์</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $idx = 1;
                foreach($workload as $w): ?>
                <tr>
                    <td class="center"><?= $idx++ ?></td>
                    <td class="font-bold"><?= $w['code'] ?></td>
                    <td><?= $w['name'] ?></td>
                    <td class="center font-bold text-blue-600"><?= $w['hours'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-slate-50">
                    <td colspan="3" class="text-right font-black py-2.5">สรุปภาระงานสอนทั้งสิ้น</td>
                    <td class="center font-black text-base text-blue-700 underline decoration-double"><?= $total_hours ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-4 flex flex-col items-center">
            <p class="text-[10px] text-slate-500 italic font-medium">ขอรับรองว่าข้อมูลภาระงานการสอนดังกล่าวเป็นความจริงทุกประการ</p>
            <div class="mt-4 text-center">
                <p class="mb-3">ลงชื่อ..........................................................</p>
                <p class="font-bold text-sm">( <?= $teacher_full_name ?> )</p>
                <p class="text-[10px] text-slate-400 mt-1">วันที่ <?= date('d') ?> เดือน <?= [
                    '01'=>'มกราคม','02'=>'กุมภาพันธ์','03'=>'มีนาคม','04'=>'เมษายน','05'=>'พฤษภาคม','06'=>'มิถุนายน',
                    '07'=>'กรกฎาคม','08'=>'สิงหาคม','09'=>'กันยายน','10'=>'ตุลาคม','11'=>'พฤศจิกายน','12'=>'ธันวาคม'
                ][date('m')] ?> พ.ศ. <?= date('Y') + 543 ?></p>
            </div>
        </div>

        <div class="absolute bottom-4 left-0 right-0 text-center text-[8px] text-slate-400">
            เอกสารสรุปภาระงานสอนรายบุคคล | ระบบบริหารงานวิชาการดิจิทัล | หน้าที่ 2
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
