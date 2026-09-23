<div id="competency-assessment" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-slate-800">ประเมินสมรรถนะสำคัญของผู้เรียน 5 ด้าน</h3>
                <p class="text-sm text-slate-500">สำหรับคุณครูประจำชั้นประเมินนักเรียนในความรับผิดชอบ</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <select id="competency_classroom" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 transition-all font-bold text-slate-700 cursor-pointer">
                    <option value="">เลือกห้องเรียน</option>
                </select>
                <select id="competency_year" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 transition-all font-bold text-slate-700 cursor-pointer">
                    <!-- Loaded via JS -->
                </select>
                <select id="competency_semester" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 transition-all font-bold text-slate-700 cursor-pointer">
                    <option value="1">ภาคเรียนที่ 1</option>
                    <option value="2">ภาคเรียนที่ 2</option>
                </select>
                <button onclick="loadCompetencyData()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-blue-900/20 cursor-pointer flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    ดึงข้อมูล
                </button>
            </div>
        </div>

        <div id="competency-batch-fill" class="hidden mb-6 bg-blue-50 p-4 rounded-2xl border border-blue-100 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2 rounded-lg text-white">
                    <i data-lucide="check-square" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-bold text-blue-900 text-sm">บันทึกคะแนนรวดเร็ว (Batch Fill)</h4>
                    <p class="text-[10px] text-blue-600">ใส่คะแนน 3 ให้ทุกคนในสมรรถนะที่เลือก</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="batchFillCompetency(1)" class="px-3 py-1.5 bg-white border border-blue-200 rounded-lg text-[10px] font-bold text-blue-700 hover:bg-blue-50 transition-all cursor-pointer">ด้าน 1</button>
                <button onclick="batchFillCompetency(2)" class="px-3 py-1.5 bg-white border border-blue-200 rounded-lg text-[10px] font-bold text-blue-700 hover:bg-blue-50 transition-all cursor-pointer">ด้าน 2</button>
                <button onclick="batchFillCompetency(3)" class="px-3 py-1.5 bg-white border border-blue-200 rounded-lg text-[10px] font-bold text-blue-700 hover:bg-blue-50 transition-all cursor-pointer">ด้าน 3</button>
                <button onclick="batchFillCompetency(4)" class="px-3 py-1.5 bg-white border border-blue-200 rounded-lg text-[10px] font-bold text-blue-700 hover:bg-blue-50 transition-all cursor-pointer">ด้าน 4</button>
                <button onclick="batchFillCompetency(5)" class="px-3 py-1.5 bg-white border border-blue-200 rounded-lg text-[10px] font-bold text-blue-700 hover:bg-blue-50 transition-all cursor-pointer">ด้าน 5</button>
                <button onclick="batchFillCompetency('all')" class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-[10px] font-bold hover:bg-blue-700 transition-all cursor-pointer shadow-sm">ให้ 3 ทุกคน ทุกด้าน</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200">
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-16 text-center">เลขที่</th>
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">1. การสื่อสาร</th>
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">2. การคิด</th>
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">3. การแก้ปัญหา</th>
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">4. ทักษะชีวิต</th>
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">5. เทคโนโลยี</th>
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">เฉลี่ย</th>
                    </tr>
                </thead>
                <tbody id="competency_list" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400 italic">กรุณาเลือกห้องเรียนและกดปุ่มดึงข้อมูล</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-8 flex justify-end">
            <button onclick="saveCompetencyScores()" class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-green-900/20 flex items-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                บันทึกข้อมูลทั้งหมด
            </button>
        </div>
    </div>

    <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
        <h4 class="text-blue-800 font-bold mb-2 flex items-center gap-2">
            <i data-lucide="info" class="w-5 h-5"></i>
            เกณฑ์การให้คะแนน
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div class="bg-white p-3 rounded-xl border border-blue-200">
                <span class="font-bold text-blue-600">3 คะแนน:</span> ดีเยี่ยม
            </div>
            <div class="bg-white p-3 rounded-xl border border-blue-200">
                <span class="font-bold text-blue-600">2 คะแนน:</span> ดี
            </div>
            <div class="bg-white p-3 rounded-xl border border-blue-200">
                <span class="font-bold text-blue-600">1 คะแนน:</span> พอใช้
            </div>
            <div class="bg-white p-3 rounded-xl border border-blue-200">
                <span class="font-bold text-blue-600">0 คะแนน:</span> ปรับปรุง
            </div>
        </div>
    </div>
</div>

<script>
async function initCompetencySection() {
    try {
        // Load academic years
        const resYears = await fetch('api/academic/get_academic_years.php');
        const years = await resYears.json();
        const yearSelect = document.getElementById('competency_year');
        if (yearSelect) {
            yearSelect.innerHTML = years.map(y => `<option value="${y.year}" ${y.is_current == 1 ? 'selected' : ''}>ปีการศึกษา ${y.year}</option>`).join('');
        }

        // Load classrooms
        const resCls = await fetch('api/teacher/get_my_classrooms.php');
        const classrooms = await resCls.json();
        const select = document.getElementById('competency_classroom');
        select.innerHTML = '<option value="">เลือกห้องเรียน</option>';
        classrooms.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.level}/${c.room}</option>`;
        });
    } catch (e) {
        console.error('Error initializing competency section:', e);
    }
}

function batchFillCompetency(itemNum) {
    const rows = document.querySelectorAll('#competency_list tr[data-student-id]');
    if (rows.length === 0) return;

    if (confirm('คุณต้องการบันทึกคะแนน 3 ให้กับนักเรียนทุกคนใช่หรือไม่?')) {
        rows.forEach(row => {
            const studentId = row.getAttribute('data-student-id');
            const selects = row.querySelectorAll('select');
            if (itemNum === 'all') {
                selects.forEach(sel => sel.value = '3');
            } else {
                selects[itemNum - 1].value = '3';
            }
            updateAvg(studentId);
        });
    }
}

async function loadCompetencyData() {
    const classroomId = document.getElementById('competency_classroom').value;
    const year = document.getElementById('competency_year').value;
    const semester = document.getElementById('competency_semester').value;

    if (!classroomId) {
        alert('กรุณาเลือกห้องเรียน');
        return;
    }

    const tbody = document.getElementById('competency_list');
    const batchFill = document.getElementById('competency-batch-fill');
    tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-slate-400 italic">กำลังโหลดข้อมูล...</td></tr>';
    if (batchFill) batchFill.classList.add('hidden');

    try {
        const res = await fetch(`api/teacher/get_competency_data.php?classroom_id=${classroomId}&academic_year=${year}&semester=${semester}`);
        const students = await res.json();

        if (students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-slate-400 italic">ไม่พบข้อมูลนักเรียนในห้องนี้</td></tr>';
            return;
        }

        if (batchFill) batchFill.classList.remove('hidden');

        tbody.innerHTML = '';
        students.forEach((s, index) => {
            const avg = s.average_score ? parseFloat(s.average_score).toFixed(2) : '0.00';
            tbody.innerHTML += `
                <tr class="hover:bg-slate-50/50 transition-colors" data-student-id="${s.id}">
                    <td class="px-4 py-4 text-center text-sm text-slate-500">${index + 1}</td>
                    <td class="px-4 py-4">
                        <div class="text-sm font-bold text-slate-700">${s.prefix || ''}${s.name || ''} ${s.last_name || ''}</div>
                        <div class="text-[10px] text-slate-400">${s.student_code}</div>
                    </td>
                    <td class="px-4 py-4 text-center">${renderScoreSelect(s.id, 1, s.item1)}</td>
                    <td class="px-4 py-4 text-center">${renderScoreSelect(s.id, 2, s.item2)}</td>
                    <td class="px-4 py-4 text-center">${renderScoreSelect(s.id, 3, s.item3)}</td>
                    <td class="px-4 py-4 text-center">${renderScoreSelect(s.id, 4, s.item4)}</td>
                    <td class="px-4 py-4 text-center">${renderScoreSelect(s.id, 5, s.item5)}</td>
                    <td class="px-4 py-4 text-center">
                        <span id="avg_${s.id}" class="text-sm font-bold text-blue-600">${avg}</span>
                    </td>
                </tr>
            `;
        });
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-red-500 italic">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
    }
}

function renderScoreSelect(studentId, itemNum, value) {
    return `
        <select onchange="updateAvg(${studentId})" class="score-input-${studentId} w-16 px-2 py-1 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
            <option value="0" ${value == 0 ? 'selected' : ''}>0</option>
            <option value="1" ${value == 1 ? 'selected' : ''}>1</option>
            <option value="2" ${value == 2 ? 'selected' : ''}>2</option>
            <option value="3" ${value == 3 ? 'selected' : ''}>3</option>
        </select>
    `;
}

function updateAvg(studentId) {
    const inputs = document.querySelectorAll(`.score-input-${studentId}`);
    let sum = 0;
    inputs.forEach(input => sum += parseInt(input.value));
    const avg = (sum / 5).toFixed(2);
    document.getElementById(`avg_${studentId}`).innerText = avg;
}

async function saveCompetencyScores() {
    const classroomId = document.getElementById('competency_classroom').value;
    const year = document.getElementById('competency_year').value;
    const semester = document.getElementById('competency_semester').value;
    
    if (!classroomId) return;

    const rows = document.querySelectorAll('#competency_list tr[data-student-id]');
    const scores = [];

    rows.forEach(row => {
        const studentId = row.getAttribute('data-student-id');
        const inputs = row.querySelectorAll('select');
        scores.push({
            student_id: studentId,
            item1: inputs[0].value,
            item2: inputs[1].value,
            item3: inputs[2].value,
            item4: inputs[3].value,
            item5: inputs[4].value
        });
    });

    try {
        const res = await fetch('api/teacher/save_competency.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                classroom_id: classroomId,
                academic_year: year,
                semester: semester,
                scores: scores
            })
        });
        const result = await res.json();
        if (result.message) {
            alert(result.message);
        } else {
            alert(result.error);
        }
    } catch (e) {
        alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    }
}
</script>
