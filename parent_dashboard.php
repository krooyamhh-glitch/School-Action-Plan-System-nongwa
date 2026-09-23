<?php
session_start();
if (!isset($_SESSION['parent_logged_in'])) {
    header('Location: parent_login.php');
    exit;
}
require_once 'api/config.php';
$student_name = $_SESSION['student_name'];
$school_id = $_SESSION['school_id'];
$school_name = $_SESSION['school_name'] ?? 'ระบบติดตามนักเรียน';

$app_logo = 'https://picsum.photos/seed/school/192/192';

try {
    // 1. ลองดึงโลโก้จากโรงเรียนก่อน
    $stmt_school = $pdo->prepare("SELECT logo_url FROM schools WHERE id = ?");
    $stmt_school->execute([$school_id]);
    $school_logo = $stmt_school->fetchColumn();
    
    if (!empty($school_logo)) {
        $app_logo = $school_logo;
    } else {
        // 2. ถ้าไม่มีโลโก้โรงเรียน ให้ใช้โลโก้ส่วนกลาง
        $stmt_app = $pdo->query("SELECT setting_value FROM app_settings WHERE setting_key = 'app_logo'");
        $logo_val = $stmt_app->fetchColumn();
        if ($logo_val) $app_logo = $logo_val;
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no text-size-adjust=none">
    <title><?= $school_name ?> - ข้อมูลนักเรียน: <?= $student_name ?></title>
    <link rel="manifest" href="manifest.php">
    <meta name="theme-color" content="#f43f5e">
    <link rel="apple-touch-icon" href="<?= $app_logo ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            -webkit-tap-highlight-color: transparent;
            background-color: #fff1f2;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%23f43f5e' fill-opacity='0.05'%3E%3Cpath d='M0 0h40v40H0V0zm40 40h40v40H40V40zm0-40h40v40H40V0zM0 40h40v40H0V40z'/%3E%3C/g%3E%3C/svg%3E");
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #fecdd3; border-radius: 10px; }
        .pull-to-refresh { transition: transform 0.2s ease-out; }
    </style>
</head>
<body class="min-h-screen">

    <!-- Header -->
    <header id="app_header" class="bg-gradient-to-br from-rose-500 via-pink-500 to-pink-600 text-white rounded-b-[40px] p-6 pt-10 shadow-xl shadow-rose-500/20 sticky top-0 z-40">
        <div class="flex justify-between items-center gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div id="student_avatar" class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30 shadow-inner shrink-0 leading-none">
                    <i id="avatar_icon" data-lucide="user" class="w-8 h-8"></i>
                </div>
                <div class="space-y-0.5 min-w-0">
                    <p id="header_school_name" class="text-[10px] text-rose-50 font-bold opacity-80 uppercase tracking-widest leading-none mb-1"></p>
                    <h1 id="header_student_name" class="text-base font-bold leading-tight truncate"><?= $student_name ?></h1>
                    <div class="flex items-center gap-2">
                        <span id="header_student_level" class="bg-white/20 px-2 py-0.5 rounded-lg text-[9px] font-bold backdrop-blur-sm">ชั้น: -</span>
                        <span id="header_student_age" class="text-[9px] text-rose-50 border-l border-white/20 pl-2">อายุ: -</span>
                        <span id="header_student_id" class="text-[9px] text-rose-50/70 ml-1">รหัส: <?= $_SESSION['student_code'] ?></span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3 shrink-0">
                <!-- Compact GPA Pill -->
                <div class="bg-white/10 backdrop-blur-xl border border-white/30 px-4 py-2 rounded-2xl text-center shadow-lg relative overflow-hidden">
                    <p class="text-[8px] text-rose-50 font-black uppercase tracking-widest mb-1 opacity-80 leading-none">GPA เทอมนี้</p>
                    <div id="header_avg_gpa" class="text-xl font-black text-white leading-none tracking-tighter">-</div>
                    <div class="absolute -right-2 -top-2 w-6 h-6 bg-white/10 rounded-full blur-md"></div>
                </div>
                
                <button onclick="logout()" class="p-2.5 bg-white/10 rounded-2xl hover:bg-white/20 transition-all cursor-pointer">
                    <i data-lucide="log-out" class="w-4 h-4 text-rose-50"></i>
                </button>
            </div>
        </div>
        
        <!-- Tab Navigation -->
        <div class="flex items-center mt-8 bg-black/10 rounded-2xl p-1 backdrop-blur-md">
            <button onclick="switchTab('academic')" class="tab-btn active flex-1 py-2 text-xs font-bold rounded-xl transition-all">ผลการเรียน</button>
            <button onclick="switchTab('behavior')" class="tab-btn flex-1 py-2 text-xs font-bold rounded-xl transition-all">บันทึกพฤติกรรม</button>
            <button onclick="switchTab('health')" class="tab-btn flex-1 py-2 text-xs font-bold rounded-xl transition-all">สุขภาพ</button>
        </div>
    </header>

    <style>
        .tab-btn { color: rgba(255,255,255,0.6); }
        .tab-btn.active { background: white; color: #f43f5e; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .section-tab { display: none; }
        .section-tab.active { display: block; }
    </style>

    <main id="content" class="p-4 space-y-6 mt-2">
        <!-- Academic Filters -->
        <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-3">
             <div class="flex-1 space-y-1">
                 <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">ปีการศึกษา</label>
                 <select id="filter_year" onchange="loadData()" class="w-full bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-700 focus:ring-0">
                 </select>
             </div>
             <div class="flex-1 space-y-1">
                 <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">ภาคเรียน</label>
                 <select id="filter_semester" onchange="loadData()" class="w-full bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-700 focus:ring-0">
                     <option value="1">ภาคเรียนที่ 1</option>
                     <option value="2">ภาคเรียนที่ 2</option>
                     <option value="annual">รวมทั้งปีการศึกษา</option>
                 </select>
             </div>
        </div>

        <!-- TAB 1: ผลการเรียน -->
        <div id="tab_academic" class="section-tab active space-y-6">
            <!-- Attendance Section -->
            <section class="space-y-3">
                <div class="flex items-center gap-2 mx-2">
                    <i data-lucide="calendar-check" class="w-4 h-4 text-rose-500"></i>
                    <h2 class="font-bold text-slate-800">สถิติการเข้าเรียน</h2>
                </div>
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-rose-100 grid grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-xl font-bold text-emerald-600" id="att_present">0</div>
                        <p class="text-[10px] text-slate-400 font-bold">มาเรียน</p>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-pink-500" id="att_late">0</div>
                        <p class="text-[10px] text-slate-400 font-bold">มาสาย</p>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-rose-500" id="att_absent">0</div>
                        <p class="text-[10px] text-slate-400 font-bold">ขาดเรียน</p>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-violet-500" id="att_leave">0</div>
                        <p class="text-[10px] text-slate-400 font-bold">ลา/ป่วย</p>
                    </div>
                </div>
            </section>

            <section class="space-y-3">
                <div class="flex items-center justify-between mx-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4 text-rose-500"></i>
                        <h2 class="font-bold text-slate-800">ผลการเรียนประจำภาคเรียน</h2>
                    </div>
                    <div class="flex gap-4 text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">
                        <span class="w-16 text-center">คะแนนรวม</span>
                        <span class="w-12 text-center">เกรด</span>
                    </div>
                </div>
                <div id="grades_list" class="space-y-2">
                </div>
            </section>
        </div>

        <!-- TAB 2: บันทึกพฤติกรรม -->
        <div id="tab_behavior" class="section-tab space-y-6">
            <section class="space-y-4">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-rose-100 space-y-6">
                    <div class="flex items-center gap-2 mb-2">
                        <i data-lucide="user-check" class="w-5 h-5 text-rose-500"></i>
                        <h2 class="font-bold text-slate-800">บันทึกพฤติกรรมโดยผู้ปกครอง</h2>
                    </div>
                    <p class="text-xs text-slate-500 italic">กรุณาระบุความคิดเห็นของท่านในแต่ละด้าน เพื่อใช้ประกอบในเล่ม ปพ.6 ครับ</p>
                    
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">1. ด้านหน้าที่รับผิดชอบ ความเอาใจใส่การเรียน</label>
                            <textarea id="fb_responsibility" rows="2" placeholder="เช่น ตั้งใจทำการบ้านด้วยตนเอง..." class="w-full p-4 bg-slate-50 border-none rounded-2xl text-sm outline-none focus:ring-4 focus:ring-rose-500/10 transition-all resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">2. ด้านการใช้เวลาว่าง</label>
                            <textarea id="fb_spare_time" rows="2" placeholder="เช่น ชอบอ่านหนังสือนิทาน วาดรูป..." class="w-full p-4 bg-slate-50 border-none rounded-2xl text-sm outline-none focus:ring-4 focus:ring-rose-500/10 transition-all resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">3. ด้านความสัมพันธ์กับบุคคลรอบข้าง</label>
                            <textarea id="fb_relationship" rows="2" placeholder="เช่น เข้ากับเพื่อนบ้านและพี่น้องได้ดี..." class="w-full p-4 bg-slate-50 border-none rounded-2xl text-sm outline-none focus:ring-4 focus:ring-rose-500/10 transition-all resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">4. ด้านอุปนิสัย บุคลิกภาพ</label>
                            <textarea id="fb_personality" rows="2" placeholder="เช่น ร่าเริง แจ่มใส มีระเบียบวินัย..." class="w-full p-4 bg-slate-50 border-none rounded-2xl text-sm outline-none focus:ring-4 focus:ring-rose-500/10 transition-all resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">5. ด้านสุขภาพ</label>
                            <textarea id="fb_health" rows="2" placeholder="เช่น ร่างกายแข็งแรง นอนหลับพักผ่อนเพียงพอ..." class="w-full p-4 bg-slate-50 border-none rounded-2xl text-sm outline-none focus:ring-4 focus:ring-rose-500/10 transition-all resize-none"></textarea>
                        </div>
                    </div>

                    <button onclick="saveFeedback()" id="saveFeedbackBtn" class="w-full bg-gradient-to-r from-rose-500 to-pink-500 text-white py-4 rounded-2xl font-bold hover:from-rose-600 hover:to-pink-600 shadow-lg shadow-rose-500/20 active:scale-95 transition-all">
                        บันทึกข้อมูล ปพ.6
                    </button>
                </div>
            </section>
        </div>

        <!-- TAB 3: สุขภาพ -->
        <div id="tab_health" class="section-tab space-y-6">
            <section class="bg-white p-6 rounded-3xl shadow-sm border border-rose-100 space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                         <i data-lucide="trending-up" class="w-5 h-5 text-rose-500"></i>
                         <h2 class="font-bold text-slate-800">พัฒนาการด้านส่วนสูง (ซม.)</h2>
                    </div>
                </div>
                <div id="height_chart" class="w-full h-48 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 relative transition-all overflow-hidden">
                     <p class="text-xs italic">กำลังประมวลผลข้อมูลส่วนสูง...</p>
                </div>
            </section>

            <section class="bg-white p-6 rounded-3xl shadow-sm border border-rose-100 space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                         <i data-lucide="activity" class="w-5 h-5 text-pink-500"></i>
                         <h2 class="font-bold text-slate-800">พัฒนาการด้านน้ำหนัก (กก.)</h2>
                    </div>
                </div>
                <div id="weight_chart" class="w-full h-48 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 relative transition-all overflow-hidden">
                     <p class="text-xs italic">กำลังประมวลผลข้อมูลน้ำหนัก...</p>
                </div>
            </section>

            <section class="bg-white p-5 rounded-3xl shadow-sm border border-rose-100">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100 text-center">
                        <p class="text-[10px] text-rose-400 font-bold uppercase mb-1">น้ำหนักล่าสุด</p>
                        <p class="text-xl font-black text-rose-600"><span id="health_weight">-</span> กก.</p>
                    </div>
                    <div class="p-4 bg-violet-50 rounded-2xl border border-violet-100 text-center">
                        <p class="text-[10px] text-violet-400 font-bold uppercase mb-1">ส่วนสูงล่าสุด</p>
                        <p class="text-xl font-black text-violet-600"><span id="health_height">-</span> ซม.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
        lucide.createIcons();

        let isFirstLoad = true;
        let healthHistory = [];

        function switchTab(tabId) {
            document.querySelectorAll('.section-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            
            document.getElementById('tab_' + tabId).classList.add('active');
            event.currentTarget.classList.add('active');
            
            if (tabId === 'health') {
                setTimeout(renderHealthCharts, 100);
            }
        }

        async function loadData() {
            const yearSelect = document.getElementById('filter_year');
            const semesterSelect = document.getElementById('filter_semester');
            
            let year = yearSelect ? yearSelect.value : '';
            let semester = semesterSelect ? semesterSelect.value : '';
            
            const url = `api/parent/get_student_data.php?academic_year=${year}&semester=${semester}`;

            try {
                const res = await fetch(url);
                const data = await res.json();
                
                if (!res.ok || data.error) {
                    return alert('ไม่สามารถโหลดข้อมูลได้: ' + (data.error || 'Server Error'));
                }

                // Profile Header & School Logo Visibility
                const student = data.student;
                const full_name = student.name + (student.last_name ? ' ' + student.last_name : '');
                document.getElementById('header_student_name').innerText = full_name;
                document.getElementById('header_school_name').innerText = student.school_name || '';
                document.getElementById('header_student_level').innerText = 'ชั้น: ' + (student.level || '-') + (student.classroom_name ? ' ห้อง: ' + student.classroom_name : '');
                
                // Age Calculation
                const age = calculateDetailedAge(student.birthday);
                if (age) {
                    document.getElementById('header_student_age').innerText = `อายุ: ${age.years} ปี ${age.months} เดือน ${age.days} วัน`;
                    
                    // Birthday Check
                    if (isBirthday(student.birthday)) {
                        const modal = document.getElementById('birthday_modal');
                        const ageText = document.getElementById('celebration_age');
                        ageText.innerText = `ครบรอบอายุ ${age.years} ปี`;
                        modal.classList.remove('hidden');
                        setTimeout(playBirthdayMusic, 500);
                    }
                } else {
                    document.getElementById('header_student_age').classList.add('hidden');
                }

                const avatarContainer = document.getElementById('student_avatar');
                if (student.school_logo_url) {
                    avatarContainer.innerHTML = `<img src="${student.school_logo_url}" alt="School Logo" class="w-full h-full object-contain p-1 rounded-2xl" referrerPolicy="no-referrer">`;
                } else {
                    const avatarIcon = document.getElementById('avatar_icon');
                    if (student.name.includes('เด็กชาย') || student.name.includes('นาย') || student.name.includes('ด.ช.')) {
                        avatarContainer.innerHTML = `<i data-lucide="user" class="w-8 h-8"></i>`;
                    } else {
                        avatarContainer.innerHTML = `<i data-lucide="user-round" class="w-8 h-8"></i>`; // Fixed icon name
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
                
                // Filters
                if (isFirstLoad) {
                    if (data.filters && data.filters.available_years) {
                        const years = [...data.filters.available_years];
                        const currentY = data.filters.current_year ? data.filters.current_year.toString() : '';
                        if (currentY && !years.includes(currentY)) years.unshift(currentY);
                        yearSelect.innerHTML = years.map(y => `<option value="${y}" ${y == currentY ? 'selected' : ''}>ปีการศึกษา ${y}</option>`).join('');
                    }
                    if (data.filters.current_semester) semesterSelect.value = data.filters.current_semester;
                    isFirstLoad = false;
                }

                // Attendance
                let counts = { present: 0, late: 0, absent: 0, leave: 0, sick: 0 };
                data.attendance.forEach(a => { counts[a.status] = a.count; });
                document.getElementById('att_present').innerText = counts.present;
                document.getElementById('att_late').innerText = counts.late;
                document.getElementById('att_absent').innerText = counts.absent;
                document.getElementById('att_leave').innerText = (parseInt(counts.leave) || 0) + (parseInt(counts.sick) || 0);

                // Grades & Visibility
                const list = document.getElementById('grades_list');
                const isHidden = data.is_grades_hidden;
                const systemYear = data.filters.system_current_year ? data.filters.system_current_year.toString() : '';

                if (isHidden) {
                    list.innerHTML = `
                        <div class="bg-rose-50 p-8 rounded-3xl border border-rose-100 text-center space-y-3">
                            <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto shadow-sm">
                                <i data-lucide="lock" class="w-6 h-6"></i>
                            </div>
                            <div class="space-y-1">
                                <h3 class="font-bold text-rose-900 text-sm">ยังไม่เปิดให้ดูผลการเรียนในปีการศึกษา ${systemYear}</h3>
                                <p class="text-xs text-rose-700 leading-relaxed px-4">เจ้าหน้าที่วิชาการกำลังอยู่ระหว่างการประมวลผลข้อมูลปีล่าสุดครับ<br>ท่านยังสามารถเลือกดูผลการเรียน "ปีการศึกษาก่อนหน้า" ได้ตามปกติครับ</p>
                            </div>
                        </div>
                    `;
                    document.getElementById('header_avg_gpa').innerText = '-';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else if (!data.grades || data.grades.length === 0) {
                    list.innerHTML = '<div class="bg-white p-8 text-center text-slate-400 rounded-2xl italic shadow-sm">ไม่มีข้อมูลในคัดเลือกนี้</div>';
                    document.getElementById('header_avg_gpa').innerText = '-';
                } else {
                    let totalWeightedGrade = 0;
                    let totalCredits = 0;
                    const processedGrades = data.grades.map(g => {
                        let displayGrade = '-';
                        let numericGrade = null;
                        const credits = parseFloat(g.credits) || 0;
                        if (g.grade !== null && g.grade !== undefined && g.grade !== '') {
                            displayGrade = g.grade;
                            const parsed = parseFloat(g.grade);
                            if (!isNaN(parsed)) { numericGrade = parsed; totalWeightedGrade += (numericGrade * credits); totalCredits += credits; }
                        } else if (g.grade_point !== undefined && g.grade_point !== null) {
                            displayGrade = g.grade_point;
                            numericGrade = parseFloat(g.grade_point);
                            if (!isNaN(numericGrade)) { totalWeightedGrade += (numericGrade * credits); totalCredits += credits; }
                        }
                        return { ...g, displayGrade, numericGrade };
                    });

                    document.getElementById('header_avg_gpa').innerText = totalCredits > 0 ? (totalWeightedGrade / totalCredits).toFixed(2) : '-';
                    list.innerHTML = processedGrades.map(g => `
                        <div class="bg-white p-3 pr-4 rounded-2xl shadow-sm border border-rose-50 flex items-center gap-3">
                            <div class="flex-1 flex items-center gap-3 overflow-hidden">
                                <div class="w-8 h-8 shrink-0 bg-rose-50 flex items-center justify-center rounded-lg font-black text-rose-500 text-[9px] border border-rose-100 shadow-inner">${g.subject_code}</div>
                                <div class="truncate"><h4 class="text-xs font-bold text-slate-700 truncate leading-tight">${g.subject_name}</h4></div>
                            </div>
                            <div class="w-16 text-center"><span class="bg-rose-50 px-2 py-1 rounded-lg text-xs font-black text-rose-600 border border-rose-100">${g.score_total || '0'}</span></div>
                            <div class="w-12 text-center"><div class="text-base font-black text-rose-600 leading-none">${g.displayGrade}</div></div>
                        </div>
                    `).join('');
                }

                // Feedback
                if (data.parent_feedback) {
                    document.getElementById('fb_responsibility').value = data.parent_feedback.responsibility_comment || '';
                    document.getElementById('fb_spare_time').value = data.parent_feedback.spare_time_comment || '';
                    document.getElementById('fb_relationship').value = data.parent_feedback.relationship_comment || '';
                    document.getElementById('fb_personality').value = data.parent_feedback.personality_comment || '';
                    document.getElementById('fb_health').value = data.parent_feedback.health_comment || '';
                } else {
                    ['fb_responsibility','fb_spare_time','fb_relationship','fb_personality','fb_health'].forEach(id => {
                        const el = document.getElementById(id); if (el) el.value = '';
                    });
                }

                // Health
                healthHistory = data.health_history || [];
                let weight = 0, height = 0;
                if (healthHistory.length > 0) {
                    for (let i = healthHistory.length - 1; i >= 0; i--) {
                        const w = parseFloat(healthHistory[i].weight) || 0;
                        const h = parseFloat(healthHistory[i].height) || 0;
                        if (w > 0 && weight === 0) weight = w;
                        if (h > 0 && height === 0) height = h;
                        if (weight > 0 && height > 0) break;
                    }
                }
                if (weight === 0) weight = parseFloat(student.weight) || 0;
                if (height === 0) height = parseFloat(student.height) || 0;
                document.getElementById('health_weight').textContent = weight > 0 ? weight : '-';
                document.getElementById('health_height').textContent = height > 0 ? height : '-';

                if (typeof lucide !== 'undefined') lucide.createIcons();
            } catch (err) { console.error(err); }
        }

        function renderHealthCharts() {
            if (!healthHistory || healthHistory.length <= 1) {
                const msg = '<div class="text-center p-4"><p class="text-xs italic text-slate-400">ยังไม่มีพัฒนาการด้านการเจริญเติบโต</p><p class="text-[10px] text-slate-300 mt-1">(ต้องการข้อมูลอย่างน้อย 2 ครั้งเพื่อแสดงกราฟ)</p></div>';
                document.getElementById('height_chart').innerHTML = msg;
                document.getElementById('weight_chart').innerHTML = msg;
                return;
            }
            renderSingleChart('height_chart', healthHistory, 'height', '#f43f5e');
            renderSingleChart('weight_chart', healthHistory, 'weight', '#8b5cf6');
        }

        function renderSingleChart(containerId, data, key, color) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            const margin = {top: 20, right: 30, bottom: 35, left: 40},
                  width = container.clientWidth - margin.left - margin.right,
                  height = container.clientHeight - margin.top - margin.bottom;

            const svg = d3.select("#" + containerId).append("svg")
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom)
                .append("g").attr("transform", `translate(${margin.left},${margin.top})`);

            const chartData = data.map((d, i) => ({ index: i, value: parseFloat(d[key]), label: "ครั้งที่ " + (d.record_number || (i+1)) })).filter(d => !isNaN(d.value));
            const x = d3.scaleLinear().domain([0, chartData.length - 1]).range([0, width]);
            const minV = d3.min(chartData, d => d.value), maxV = d3.max(chartData, d => d.value);
            const pad = (maxV - minV) * 0.2 || 5;
            const y = d3.scaleLinear().domain([minV - pad, maxV + pad]).range([height, 0]);

            svg.append("g").attr("transform", `translate(0,${height})`).attr("class", "text-[8px] text-slate-300").call(d3.axisBottom(x).ticks(5).tickFormat(i => chartData[i] ? chartData[i].label : '')).select(".domain").attr("stroke", "#e2e8f0");
            svg.append("g").attr("class", "text-[8px] text-slate-300").call(d3.axisLeft(y).ticks(5)).select(".domain").attr("stroke", "#e2e8f0");

            const line = d3.line().x(d => x(d.index)).y(d => y(d.value)).curve(d3.curveMonotoneX);
            const area = d3.area().x(d => x(d.index)).y1(d => y(d.value)).y0(height).curve(d3.curveMonotoneX);

            const gradId = "grad-" + containerId;
            const defs = svg.append("defs");
            const grad = defs.append("linearGradient").attr("id", gradId).attr("x1", "0%").attr("y1", "0%").attr("x2", "0%").attr("y2", "100%");
            grad.append("stop").attr("offset", "0%").attr("stop-color", color).attr("stop-opacity", 0.2);
            grad.append("stop").attr("offset", "100%").attr("stop-color", color).attr("stop-opacity", 0);

            svg.append("path").datum(chartData).attr("fill", "url(#" + gradId + ")").attr("d", area);
            svg.append("path").datum(chartData).attr("fill", "none").attr("stroke", color).attr("stroke-width", 2.5).attr("d", line);
            svg.selectAll("dot").data(chartData).enter().append("circle").attr("cx", d => x(d.index)).attr("cy", d => y(d.value)).attr("r", 3.5).attr("fill", "white").attr("stroke", color).attr("stroke-width", 2);
            svg.selectAll("text").data(chartData).enter().append("text").attr("x", d => x(d.index)).attr("y", d => y(d.value) - 10).attr("text-anchor", "middle").attr("class", "text-[9px] font-bold").attr("fill", color).text(d => d.value);
        }

        async function saveFeedback() {
            const btn = document.getElementById('saveFeedbackBtn');
            const original = btn.innerText;
            btn.disabled = true; btn.innerText = 'กำลังบันทึก...';

            const payload = {
                academic_year: document.getElementById('filter_year').value,
                semester: document.getElementById('filter_semester').value,
                responsibility: document.getElementById('fb_responsibility').value,
                spare_time: document.getElementById('fb_spare_time').value,
                relationship: document.getElementById('fb_relationship').value,
                personality: document.getElementById('fb_personality').value,
                health_comment: document.getElementById('fb_health').value
            };

            try {
                const res = await fetch('api/parent/save_feedback.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                if ((await res.json()).success) alert('บันทึกข้อมูลเรียบร้อยแล้ว ขอบคุณสำหรับข้อมูลครับ');
            } catch (e) { alert('เกิดข้อผิดพลาด'); } finally { btn.disabled = false; btn.innerText = original; }
        }

        function logout() { if (confirm('คุณต้องการออกจากระบบใช่หรือไม่?')) window.location.href = 'parent_logout.php'; }

        // PWA Service Worker & Install Logic
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker failed', err));
            });
        }

        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            // For parents, we might not want to show a button in the main UI to keep it clean, 
            // but we could show a snackbar or just rely on the login page.
            // Let's add a small install banner if prompt exists.
            const banner = document.createElement('div');
            banner.className = 'fixed bottom-4 left-4 right-4 bg-white p-4 rounded-2xl shadow-2xl border border-rose-100 flex items-center justify-between z-[60] animate-bounce';
            banner.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center">
                        <i data-lucide="download" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800">ติดตั้งแอปหน้าจอหลัก</p>
                        <p class="text-[10px] text-slate-400">เพื่อการใช้งานที่สะดวกยิ่งขึ้น</p>
                    </div>
                </div>
                <button id="pwa-install-parent" class="bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-rose-200">ติดตั้ง</button>
            `;
            document.body.appendChild(banner);
            if (typeof lucide !== 'undefined') lucide.createIcons();

            document.getElementById('pwa-install-parent').addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') banner.remove();
                    deferredPrompt = null;
                }
            });
        });

        loadData();

        function calculateDetailedAge(birthDateString) {
            if (!birthDateString || birthDateString === '0000-00-00') return null;
            const birthDate = new Date(birthDateString);
            const today = new Date();

            let years = today.getFullYear() - birthDate.getFullYear();
            let months = today.getMonth() - birthDate.getMonth();
            let days = today.getDate() - birthDate.getDate();

            if (days < 0) {
                months--;
                const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                days += lastMonth.getDate();
            }
            if (months < 0) {
                years--;
                months += 12;
            }

            return { years, months, days };
        }

        function isBirthday(birthDateString) {
            if (!birthDateString || birthDateString === '0000-00-00') return false;
            const birthDate = new Date(birthDateString);
            const today = new Date();
            return birthDate.getDate() === today.getDate() && birthDate.getMonth() === today.getMonth();
        }

        function closeBirthday() {
            const modal = document.getElementById('birthday_modal');
            const music = document.getElementById('birthday_music');
            modal.classList.add('hidden');
            if (music) music.pause();
        }

        function playBirthdayMusic() {
            const music = document.getElementById('birthday_music');
            if (music) {
                music.play().catch(e => console.log('Autoplay blocked, waiting for interaction'));
            }
        }
    </script>
    
    <!-- Birthday Modal -->
    <div id="birthday_modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md hidden" onclick="playBirthdayMusic()">
        <div class="bg-white rounded-[2rem] w-full max-w-sm overflow-hidden shadow-2xl relative animate-[scaleIn_0.3s_ease-out]">
            <!-- Confetti Decors -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-20">
                <div class="absolute top-4 left-10 w-2 h-2 bg-rose-400 rotate-45 animate-ping"></div>
                <div class="absolute top-10 right-10 w-2 h-2 bg-violet-400 rounded-full animate-bounce"></div>
                <div class="absolute bottom-20 left-1/4 w-3 h-3 bg-pink-400 rotate-12 animate-pulse"></div>
            </div>
            
            <div class="p-8 text-center space-y-6 relative">
                <div class="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center mx-auto shadow-inner border-4 border-white overflow-hidden">
                    <img src="https://picsum.photos/seed/birthday/200/200" class="w-full h-full object-cover">
                </div>
                <div class="space-y-2">
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">สุขสันต์วันเกิด! 🎂</h2>
                    <p class="text-slate-500 font-medium leading-relaxed">ขอให้น้องมีความสุข สุขภาพแข็งแรง<br>และเป็นที่รักของทุกคนนะครับ</p>
                </div>
                <div class="bg-rose-50 py-4 rounded-3xl border border-rose-100">
                    <p id="celebration_age" class="text-rose-600 font-black text-2xl tracking-tighter"></p>
                </div>
                <button onclick="closeBirthday()" class="w-full bg-gradient-to-r from-rose-500 to-pink-500 text-white py-4 rounded-2xl font-black shadow-lg shadow-rose-200 active:scale-95 transition-all text-lg">
                    รับคำอวยพร 🎉
                </button>
            </div>
        </div>
        <audio id="birthday_music" loop>
            <source src="https://cdn.pixabay.com/audio/2022/03/10/audio_5b3eb5264b.mp3" type="audio/mpeg">
        </audio>
    </div>

    <style>
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.8) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
</body>
</html>
