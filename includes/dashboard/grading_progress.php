<!-- Grading Progress Monitoring Section -->
<div id="grading-progress" class="section hidden space-y-6">
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-tr from-indigo-600 to-blue-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200/50">
                    <i data-lucide="trending-up" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">ความคืบหน้าการบันทึกคะแนน</h3>
                    <p class="text-slate-500 text-sm font-medium">ติดตามสถานะการบันทึกคะแนนรายวิชาจำแนกตามระดับชั้น</p>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-4 bg-slate-50 p-2 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-2 pl-2">
                    <i data-lucide="filter" class="w-4 h-4 text-slate-400"></i>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">ระดับชั้น:</span>
                </div>
                <select id="progress_level_filter" onchange="loadGradingProgress()" class="min-w-[160px] px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-700 shadow-sm">
                    <option value="">ทุกระดับชั้น</option>
                    <option value="ป.1">ประถมศึกษาปีที่ 1</option>
                    <option value="ป.2">ประถมศึกษาปีที่ 2</option>
                    <option value="ป.3">ประถมศึกษาปีที่ 3</option>
                    <option value="ป.4">ประถมศึกษาปีที่ 4</option>
                    <option value="ป.5">ประถมศึกษาปีที่ 5</option>
                    <option value="ป.6">ประถมศึกษาปีที่ 6</option>
                    <option value="ม.1">มัธยมศึกษาปีที่ 1</option>
                    <option value="ม.2">มัธยมศึกษาปีที่ 2</option>
                    <option value="ม.3">มัธยมศึกษาปีที่ 3</option>
                </select>
                <button onclick="loadGradingProgress()" class="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-slate-600 hover:bg-white hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Progress Tabs -->
        <div class="flex overflow-x-auto gap-2 mb-6 border-b border-slate-100 pb-1 custom-scrollbar">
            <button onclick="switchProgressTab('academics')" id="tab-academics" class="progress-tab active flex items-center gap-2 px-6 py-3 rounded-t-2xl font-bold text-sm transition-all border-b-2 border-transparent hover:bg-slate-50">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                หน่วยการเรียนรู้และผลสอบปลายภาค
            </button>
            <button onclick="switchProgressTab('evaluations')" id="tab-evaluations" class="progress-tab flex items-center gap-2 px-6 py-3 rounded-t-2xl font-bold text-sm transition-all border-b-2 border-transparent hover:bg-slate-50">
                <i data-lucide="award" class="w-4 h-4"></i>
                กิจกรรมพัฒนาผู้เรียนและสมรรถนะ
            </button>
            <button onclick="switchProgressTab('characteristics')" id="tab-characteristics" class="progress-tab flex items-center gap-2 px-6 py-3 rounded-t-2xl font-bold text-sm transition-all border-b-2 border-transparent hover:bg-slate-50">
                <i data-lucide="user-check" class="w-4 h-4"></i>
                คุณลักษณะและอ่านคิดวิเคราะห์
            </button>
        </div>

        <style>
            .progress-tab.active {
                color: #2563eb;
                background: white;
                border-bottom-color: #2563eb;
            }
            .progress-tab:not(.active) {
                color: #64748b;
            }
        </style>

        <div class="relative overflow-hidden group">
            <div id="progress_loading_state" class="hidden absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-2xl">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-sm font-bold text-slate-600 animate-pulse">กำลังดึงข้อมูล...</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-5 text-xs font-black text-slate-500 uppercase tracking-widest border-b border-slate-100">รายวิชา / ครูผู้สอน</th>
                            <th id="progress_header_dynamic" class="px-6 py-5 text-xs font-black text-slate-500 uppercase tracking-widest border-b border-slate-100">รายละเอียดความคืบหน้า</th>
                            <th class="px-6 py-5 text-xs font-black text-slate-500 uppercase tracking-widest border-b border-slate-100 w-48 text-right">ภาพรวม</th>
                        </tr>
                    </thead>
                    <tbody id="gradingProgressTableBody" class="divide-y divide-slate-50">
                        <!-- Content loaded via AJAX -->
                    </tbody>
                </table>
            </div>
            
            <div id="progress_empty_state" class="hidden py-24 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                    <i data-lucide="search-x" class="w-10 h-10"></i>
                </div>
                <h4 class="text-lg font-bold text-slate-700">ไม่พบรายวิชาที่ได้รับมอบหมาย</h4>
                <p class="text-slate-500 max-w-xs mx-auto mt-2">โปรดระบุระดับชั้นอื่นหรือตรวจสอบการมอบหมายงานหลักสูตร</p>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Score Modal -->
<div id="score_details_modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeScoreDetailsModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white w-full max-w-6xl rounded-3xl shadow-2xl overflow-hidden ring-1 ring-slate-200">
            <!-- Modal Header -->
            <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-blue-600 shadow-sm border border-slate-100">
                        <i data-lucide="layout-list" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 id="modal_subject_name" class="text-xl font-black text-slate-800">ชื่อวิชา</h3>
                        <p id="modal_teacher_info" class="text-sm font-medium text-slate-500 uppercase tracking-wider">ครูผู้สอน / ห้องเรียน</p>
                    </div>
                </div>
                <button onclick="closeScoreDetailsModal()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-600 hover:border-slate-300 transition-all shadow-sm">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-8 py-4 bg-white border-b border-slate-100 flex flex-wrap items-center gap-6" id="modal_summary_stats">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-xs font-bold text-slate-500 tracking-wider">กำลังสรุปข้อมูลรายชั้นเรียน...</span>
                </div>
            </div>

            <div class="p-0 overflow-x-auto min-h-[400px]">
                <table id="modal_score_table" class="w-full text-left border-collapse hidden">
                    <thead id="modal_score_thead" class="sticky top-0 bg-white z-10">
                        <!-- Head loaded via JS -->
                    </thead>
                    <tbody id="modal_score_tbody" class="divide-y divide-slate-100">
                        <!-- Body loaded via JS -->
                    </tbody>
                </table>
                <div id="modal_score_loading" class="py-20 flex flex-col items-center justify-center gap-4">
                    <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-slate-500 font-bold">กำลังประมวลผลคะแนน...</p>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button onclick="closeScoreDetailsModal()" class="px-6 py-2.5 bg-white border border-slate-200 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition-all shadow-sm">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentProgressTab = 'academics';
    let progressDataCache = [];

    function switchProgressTab(tab) {
        currentProgressTab = tab;
        document.querySelectorAll('.progress-tab').forEach(t => t.classList.remove('active'));
        document.getElementById(`tab-${tab}`).classList.add('active');
        renderGradingProgress();
    }

    async function initGradingProgress() {
        console.log('Initializing Grading Progress Monitoring...');
        await loadGradingProgress();
    }

    async function loadGradingProgress() {
        const loading = document.getElementById('progress_loading_state');
        const empty = document.getElementById('progress_empty_state');
        const level = document.getElementById('progress_level_filter').value;
        
        if (loading) loading.classList.remove('hidden');
        if (empty) empty.classList.add('hidden');
        
        try {
            const url = `api/admin/get_grading_progress.php?academic_year=${currentAcademicYear}&semester=${currentSemester}&level=${encodeURIComponent(level)}`;
            const res = await fetch(url);
            progressDataCache = await res.json();
            
            if (progressDataCache.error) throw new Error(progressDataCache.error);
            
            renderGradingProgress();
            
        } catch (e) {
            console.error('Error loading grading progress:', e);
            alert('เกิดข้อผิดพลาดในการดึงข้อมูล: ' + e.message);
        } finally {
            if (loading) loading.classList.add('hidden');
        }
    }

    function renderGradingProgress() {
        const tbody = document.getElementById('gradingProgressTableBody');
        const empty = document.getElementById('progress_empty_state');
        const header = document.getElementById('progress_header_dynamic');

        if (!progressDataCache || progressDataCache.length === 0) {
            tbody.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');

        // Update dynamic header title
        if (currentProgressTab === 'academics') header.innerText = 'หน่วยการเรียนรู้ / ผลสอบปลายภาค';
        else if (currentProgressTab === 'evaluations') header.innerText = 'สมรรถนะ / กิจกรรมพัฒนาผู้เรียน';
        else header.innerText = 'คุณลักษณะพึงประสงค์ / อ่าน คิดวิเคราะห์';

        tbody.innerHTML = progressDataCache.map(item => {
            const studentCount = parseInt(item.student_count) || 0;
            const totalUnits = parseInt(item.total_units) || 0;
            const completedUnits = parseInt(item.completed_units) || 0;
            
            const unitsP = totalUnits > 0 ? (completedUnits / totalUnits) : 0;
            const finalP = studentCount > 0 ? (parseInt(item.final_count) / studentCount) : 0;
            const charP = studentCount > 0 ? (parseInt(item.characteristics_count) / studentCount) : 0;
            const analyticalP = studentCount > 0 ? (parseInt(item.analytical_count) / studentCount) : 0;
            const competencyP = studentCount > 0 ? (parseInt(item.competency_count) / studentCount) : 0;
            const learnerDevP = studentCount > 0 ? (parseInt(item.learner_dev_count) / studentCount) : 0;

            let tabContent = '';
            let overallP = 0;

            if (currentProgressTab === 'academics') {
                overallP = ((unitsP + finalP) / 2) * 100;
                tabContent = `
                    <div class="grid grid-cols-1 gap-3">
                        ${renderMiniBar('หน่วยการเรียน', unitsP, `${completedUnits}/${totalUnits} หน่วย`)}
                        ${renderMiniBar('สอบปลายภาค', finalP, `${item.final_count}/${studentCount} คน`)}
                    </div>
                `;
            } else if (currentProgressTab === 'evaluations') {
                overallP = ((competencyP + learnerDevP) / 2) * 100;
                tabContent = `
                    <div class="grid grid-cols-1 gap-3">
                        ${renderMiniBar('ประเมินสมรรถนะ', competencyP, `${item.competency_count}/${studentCount} คน`)}
                        ${renderMiniBar('กิจกรรมพัฒนาผู้เรียน', learnerDevP, `${item.learner_dev_count}/${studentCount} คน`)}
                    </div>
                `;
            } else {
                overallP = ((charP + analyticalP) / 2) * 100;
                tabContent = `
                    <div class="grid grid-cols-1 gap-3">
                        ${renderMiniBar('คุณลักษณะพึงประสงค์', charP, `${item.characteristics_count}/${studentCount} คน`)}
                        ${renderMiniBar('ประเมินการอ่าน คิดวิเคราะห์', analyticalP, `${item.analytical_count}/${studentCount} คน`)}
                    </div>
                `;
            }

            const percent = Math.round(overallP);
            let barColor = 'bg-blue-500';
            let textColor = 'text-blue-600';
            if (percent >= 100) { barColor = 'bg-emerald-500'; textColor = 'text-emerald-600'; }
            else if (percent > 0 && percent < 50) { barColor = 'bg-amber-500'; textColor = 'text-amber-600'; }
            else if (percent === 0) { barColor = 'bg-slate-300'; textColor = 'text-slate-400'; }

            return `
                <tr class="hover:bg-blue-50/30 transition-all group cursor-pointer" onclick="viewAssignmentScoreDetails('${item.assignment_id}')">
                    <td class="px-6 py-6 w-72">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 group-hover:bg-white group-hover:shadow-md group-hover:border-blue-200 transition-all flex-shrink-0 flex items-center justify-center font-black text-slate-400 border border-slate-200">
                                ${item.teacher_name ? item.teacher_name.charAt(0) : '?'}
                            </div>
                            <div class="overflow-hidden">
                                <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest block mb-1">${item.subject_code}</span>
                                <h4 class="text-sm font-bold text-slate-800 truncate mb-1 group-hover:text-blue-600 transition-all">${item.subject_name}</h4>
                                <p class="text-xs text-slate-500 font-medium truncate">${item.teacher_name} ${item.teacher_last_name || ''}</p>
                                <div class="flex gap-1.5 mt-2">
                                    <span class="px-2 py-0.5 bg-slate-50 border border-slate-200 rounded text-[9px] font-bold text-slate-500">${item.subject_level}</span>
                                    ${item.room ? `<span class="px-2 py-0.5 bg-blue-50 border border-blue-100 rounded text-[9px] font-black text-blue-600">/ ${item.room}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-6">
                        ${tabContent}
                    </td>
                    <td class="px-6 py-6 text-right">
                        <div class="inline-flex flex-col items-end gap-2">
                            <span class="text-2xl font-black ${textColor} tracking-tight">${percent}%</span>
                            <div class="w-32 h-2.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200/50 p-[2px] shadow-inner">
                                <div class="${barColor} h-full rounded-full transition-all duration-1000 ease-out relative" 
                                     style="width: 0%" 
                                     data-percent="${percent}%">
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">สถานะปัจจุบัน</span>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Trigger animations
        setTimeout(() => {
            document.querySelectorAll('#gradingProgressTableBody [data-percent]').forEach(el => {
                el.style.width = el.getAttribute('data-percent');
            });
        }, 100);

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function renderMiniBar(label, ratio, detail) {
        const p = Math.round(ratio * 100);
        let color = 'bg-blue-400';
        if (p >= 100) color = 'bg-emerald-400';
        else if (p > 0 && p < 100) color = 'bg-amber-400';
        else color = 'bg-slate-200';

        return `
            <div class="flex items-center gap-4">
                <div class="w-32 flex-shrink-0">
                    <p class="text-[11px] font-bold text-slate-600 line-clamp-1">${label}</p>
                </div>
                <div class="flex-1 max-w-[200px]">
                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="${color} h-full rounded-full transition-all duration-1000" style="width: ${p}%"></div>
                    </div>
                </div>
                <div class="w-20 text-right">
                    <span class="text-[10px] font-black text-slate-400 tracking-tighter">${detail}</span>
                </div>
                <div class="w-8 flex justify-end">
                    ${p >= 100 ? '<i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i>' : 
                      p > 0 ? '<i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500 animate-pulse"></i>' : 
                      '<i data-lucide="circle" class="w-3.5 h-3.5 text-slate-200"></i>'}
                </div>
            </div>
        `;
    }

    async function viewAssignmentScoreDetails(assignmentId) {
        // Only allow score details if in academic tab
        if (currentProgressTab !== 'academics') {
            console.log('Detailed scores currently only supported for academic tab');
            return;
        }

        const modal = document.getElementById('score_details_modal');
        const loading = document.getElementById('modal_score_loading');
        const table = document.getElementById('modal_score_table');
        const tbody = document.getElementById('modal_score_tbody');
        const thead = document.getElementById('modal_score_thead');
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        loading.classList.remove('hidden');
        if (table) table.classList.add('hidden');
        tbody.innerHTML = '';
        thead.innerHTML = '';
        
        try {
            const res = await fetch(`api/admin/get_assignment_score_details.php?assignment_id=${assignmentId}`);
            const data = await res.json();
            
            if (data.error) throw new Error(data.error);
            
            const { assignment, units, students } = data;
            
            const teacherFullName = `ครู${assignment.teacher_name}${assignment.teacher_last_name ? ' ' + assignment.teacher_last_name : ''}`;
            document.getElementById('modal_subject_name').innerText = assignment.subject_name;
            document.getElementById('modal_teacher_info').innerText = `${assignment.subject_code} | ${teacherFullName} | ${assignment.level}/${assignment.room} | ปีการศึกษา ${assignment.academic_year} เทอม ${assignment.semester}`;
            
            // Calculate Stats
            let totalAvg = 0;
            let passCount = 0;
            let validScores = 0;
            let gradeDistribution = { '4': 0, '3.5': 0, '3': 0, '2.5': 0, '2': 0, '1.5': 0, '1': 0, '0': 0 };

            students.forEach(s => {
                if (s.total_score !== null) {
                    totalAvg += parseFloat(s.total_score);
                    validScores++;
                    if (parseFloat(s.total_score) >= 50) passCount++;
                }
                if (s.grade && gradeDistribution[s.grade] !== undefined) {
                    gradeDistribution[s.grade]++;
                }
            });

            const avgText = validScores > 0 ? (totalAvg / validScores).toFixed(2) : '-';
            const passRate = students.length > 0 ? ((passCount / students.length) * 100).toFixed(0) : '0';

            const summaryHtml = `
                <div class="flex items-center gap-8 py-1">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">คะแนนเฉลี่ย</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-black text-blue-600">${avgText}</span>
                            <span class="text-xs font-bold text-slate-400">/ 100</span>
                        </div>
                    </div>
                    <div class="h-8 w-[1px] bg-slate-100"></div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">อัตราการสอบผ่าน</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-black text-emerald-500">${passRate}%</span>
                            <span class="text-xs font-bold text-slate-400">ของนักเรียนทั้งหมด</span>
                        </div>
                    </div>
                    <div class="h-8 w-[1px] bg-slate-100"></div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">นักเรียนทั้งหมด</span>
                        <span class="text-2xl font-black text-slate-700">${students.length}<span class="text-xs font-bold text-slate-400 ml-1">คน</span></span>
                    </div>
                </div>
            `;
            document.getElementById('modal_summary_stats').innerHTML = summaryHtml;

            // Build Table Header
            let headHtml = `
                <tr class="bg-slate-50">
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-slate-100 w-16">ที่</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-slate-100 w-24">รหัสประจำตัว</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-slate-100 min-w-[200px]">ชื่อ-นามสกุล</th>
            `;
            
            units.forEach((unit, idx) => {
                headHtml += `
                    <th class="px-4 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest border-b border-slate-100 text-center">
                        หน่วยที่ ${idx + 1}<br>
                        <span class="text-[9px] text-slate-400 font-bold">(${unit.max_score})</span>
                    </th>
                `;
            });
            
            headHtml += `
                    <th class="px-4 py-4 text-[10px] font-black text-blue-600 uppercase tracking-widest border-b border-blue-100 text-center bg-blue-50/50">ปลายภาค</th>
                    <th class="px-4 py-4 text-[10px] font-black text-emerald-600 uppercase tracking-widest border-b border-emerald-100 text-center bg-emerald-50/50">รวม 100</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-700 uppercase tracking-widest border-b border-slate-100 text-center w-20">เกรด</th>
                </tr>
            `;
            thead.innerHTML = headHtml;
            
            // Build Table Body
            if (students.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${units.length + 6}" class="py-12 text-center text-slate-400 font-bold">ไม่พบรายชื่อนักเรียนในห้องนี้</td></tr>`;
            } else {
                tbody.innerHTML = students.map((s, i) => {
                    let unitHtml = '';
                    units.forEach(u => {
                        const score = s.unit_scores[u.id];
                        const isMissing = score === null || score === undefined;
                        unitHtml += `
                            <td class="px-4 py-3 text-sm text-center border-b border-slate-50">
                                ${isMissing ? '<span class="text-rose-500 font-bold">x</span>' : `<span class="font-bold text-slate-700">${score}</span>`}
                            </td>
                        `;
                    });
                    
                    const totalScore = parseFloat(s.total_score || 0);
                    let gradeClass = 'text-slate-700';
                    if (s.grade === '4' || s.grade === '3.5') gradeClass = 'text-emerald-600';
                    else if (s.grade === '0' || s.grade === 'ร' || s.grade === 'มส') gradeClass = 'text-rose-600';

                    return `
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-3 text-xs text-slate-400 font-bold border-b border-slate-50">${i + 1}</td>
                            <td class="px-6 py-3 text-xs text-slate-500 font-bold border-b border-slate-50">${s.student_code}</td>
                            <td class="px-6 py-3 text-sm font-bold text-slate-700 border-b border-slate-50">${s.full_name}</td>
                            ${unitHtml}
                            <td class="px-4 py-3 text-sm text-center border-b border-blue-50 bg-blue-50/20 font-black text-blue-600">
                                ${s.final_score !== null ? s.final_score : '-'}
                            </td>
                            <td class="px-4 py-3 text-sm text-center border-b border-emerald-50 bg-emerald-50/20 font-black text-emerald-600">
                                ${s.total_score !== null ? s.total_score : '-'}
                            </td>
                            <td class="px-6 py-3 text-center border-b border-slate-50">
                                <span class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-sm font-black ${gradeClass} shadow-sm">
                                    ${s.grade || '-'}
                                </span>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
            
            if (table) table.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            
        } catch (e) {
            console.error('Error viewing score details:', e);
            alert('ไม่สามารถโหลดข้อมูลคะแนนได้: ' + e.message);
            closeScoreDetailsModal();
        } finally {
            loading.classList.add('hidden');
        }
    }

    function closeScoreDetailsModal() {
        document.getElementById('score_details_modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>
