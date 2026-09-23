<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-bold text-slate-800">บันทึกคะแนนรายวิชา</h3>
        <div class="flex gap-2">
            <button onclick="saveUnitScores('units')" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 cursor-pointer">
                บันทึกคะแนนหน่วยการเรียนรู้
            </button>
            <button onclick="saveUnitScores('final')" class="bg-amber-500 text-white px-6 py-2 rounded-xl font-semibold hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 cursor-pointer">
                บันทึกคะแนนสอบปลายภาค
            </button>
        </div>
    </div>

    <!-- Grading Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[1000px]">
            <thead>
                <tr id="academic-header-row" class="text-slate-500 border-b border-slate-100 text-xs">
                    <th class="pb-3 font-medium w-10">เลขที่</th>
                    <th class="pb-3 font-medium w-60">ชื่อ-นามสกุล</th>
                    <th class="pb-3 font-medium">หน่วยการเรียนรู้</th>
                    <th class="pb-3 font-medium w-16 text-center">รวมหน่วย</th>
                    <th class="pb-3 font-medium w-16 text-center">ปลายภาค</th>
                    <th class="pb-3 font-medium w-16 text-center">คะแนนรวม</th>
                    <th class="pb-3 font-medium w-16 text-center">ร้อยละ</th>
                    <th class="pb-3 font-medium w-16 text-center">ผลการเรียน</th>
                </tr>
            </thead>
            <tbody id="academic-table-body">
                <!-- Students will be loaded here -->
            </tbody>
        </table>
    </div>

    <!-- Learning Units Management (Moved to bottom) -->
    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 mt-6">
        <div class="flex justify-between items-center mb-4">
            <h4 class="text-sm font-bold text-slate-700">จัดการหน่วยการเรียนรู้</h4>
            <button onclick="openAddUnitModal()" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-blue-700 transition-all flex items-center gap-1 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                เพิ่มหน่วยการเรียนรู้
            </button>
        </div>
        <div id="units-list" class="flex flex-wrap gap-2">
            <!-- Units will be loaded here -->
        </div>
    </div>

    <!-- Unit Details Note -->
    <div id="unit-details-note" class="bg-amber-50 p-4 rounded-xl border border-amber-100 text-xs text-amber-800 space-y-1">
        <p class="font-bold mb-1">รายละเอียดหน่วยการเรียนรู้:</p>
        <div id="unit-details-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-1">
            <!-- Unit details will be loaded here -->
        </div>
    </div>
</div>

<!-- Modal: Add/Edit Unit -->
<div id="unitModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-[60]">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 id="unitModalTitle" class="text-xl font-bold text-slate-800">เพิ่มหน่วยการเรียนรู้</h3>
            <button onclick="closeModal('unitModal')" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form id="unitForm" class="p-6 space-y-4">
            <input type="hidden" id="unit_id">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อหน่วยการเรียนรู้</label>
                <input type="text" id="unit_name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">คะแนนเต็ม</label>
                <input type="number" id="unit_max_score" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20">
            </div>
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal('unitModal')" class="flex-1 px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50">ยกเลิก</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-blue-700">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentUnits = [];

    async function loadLearningUnits() {
        if (!currentAssignment) return;
        const year = document.getElementById('grade_academic_year').value;
        const semester = document.getElementById('grade_semester').value;
        
        try {
            const res = await fetch(`api/teacher/get_learning_units.php?subject_id=${currentAssignment.subject_id}&classroom_id=${currentAssignment.classroom_id}&academic_year=${year}&semester=${semester}`);
            currentUnits = await res.json();
            renderUnitsList();
            renderAcademicTable();
        } catch (e) {
            console.error('Error loading units:', e);
        }
    }

    function renderUnitsList() {
        const container = document.getElementById('units-list');
        const noteList = document.getElementById('unit-details-list');
        
        container.innerHTML = currentUnits.map((u, i) => `
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 text-xs shadow-sm">
                <span class="font-bold text-slate-700">หน่วยที่ ${i + 1}</span>
                <span class="text-slate-400">(${u.max_score} ค.)</span>
                <div class="flex gap-1 ml-2">
                    <button onclick="editUnit(${JSON.stringify(u).replace(/"/g, '&quot;')})" class="text-blue-600 hover:text-blue-800 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </button>
                    <button onclick="deleteUnit(${u.id})" class="text-red-600 hover:text-red-800 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>
            </div>
        `).join('');

        noteList.innerHTML = currentUnits.map((u, i) => `
            <div class="flex gap-2">
                <span class="font-bold">หน่วยที่ ${i + 1}:</span>
                <span>${u.unit_name} (${u.max_score} คะแนน)</span>
            </div>
        `).join('');
    }

    function openAddUnitModal() {
        document.getElementById('unitModalTitle').innerText = 'เพิ่มหน่วยการเรียนรู้';
        document.getElementById('unit_id').value = '';
        document.getElementById('unit_name').value = '';
        document.getElementById('unit_max_score').value = 10;
        openModal('unitModal');
    }

    function editUnit(unit) {
        document.getElementById('unitModalTitle').innerText = 'แก้ไขหน่วยการเรียนรู้';
        document.getElementById('unit_id').value = unit.id;
        document.getElementById('unit_name').value = unit.unit_name;
        document.getElementById('unit_max_score').value = unit.max_score;
        openModal('unitModal');
    }

    const unitForm = document.getElementById('unitForm');
    if (unitForm) {
        unitForm.onsubmit = async (e) => {
            e.preventDefault();
            const payload = {
                id: document.getElementById('unit_id').value,
                subject_id: currentAssignment.subject_id,
                classroom_id: currentAssignment.classroom_id,
                academic_year: document.getElementById('grade_academic_year').value,
                semester: document.getElementById('grade_semester').value,
                unit_name: document.getElementById('unit_name').value,
                max_score: document.getElementById('unit_max_score').value
            };

            const res = await fetch('api/teacher/save_learning_unit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.message) {
                closeModal('unitModal');
                loadLearningUnits();
            } else {
                alert(result.error);
            }
        };
    }

    async function deleteUnit(id) {
        if (!confirm('ยืนยันการลบหน่วยการเรียนรู้นี้? คะแนนที่บันทึกไว้จะถูกลบด้วย')) return;
        const res = await fetch('api/teacher/delete_learning_unit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.message) {
            loadLearningUnits();
        } else {
            alert(result.error);
        }
    }

    let unlockedUnitId = null;
    let isFinalUnlocked = false;
    let isGradeUnlocked = false;
    let finalMaxScore = 30; // Default

    function toggleUnitLock(unitId) {
        unlockedUnitId = (unlockedUnitId === unitId) ? null : unitId;
        isFinalUnlocked = false;
        renderAcademicTable();
    }

    function toggleFinalLock() {
        isFinalUnlocked = !isFinalUnlocked;
        unlockedUnitId = null;
        isGradeUnlocked = false;
        renderAcademicTable();
    }

    function toggleGradeLock() {
        isGradeUnlocked = !isGradeUnlocked;
        unlockedUnitId = null;
        isFinalUnlocked = false;
        renderAcademicTable();
    }

    function updateFinalMax(val) {
        finalMaxScore = parseFloat(val) || 30;
        calculateAll();
    }

    function calculateAll() {
        currentStudents.forEach(s => recalculateRow(s.id));
        renderAcademicTable();
    }

    function calculateGradeFromPercent(percent) {
        if (isNaN(percent) || percent === null) return '0';
        if (percent >= 80) return '4';
        if (percent >= 75) return '3.5';
        if (percent >= 70) return '3';
        if (percent >= 65) return '2.5';
        if (percent >= 60) return '2';
        if (percent >= 55) return '1.5';
        if (percent >= 50) return '1';
        return '0';
    }

    function renderAcademicTable() {
        const tbody = document.getElementById('academic-table-body');
        const headerRow = document.getElementById('academic-header-row');
        
        if (!tbody || !headerRow) return;

        if (!Array.isArray(currentStudents) || currentStudents.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="100%" class="py-12 text-center text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <div class="flex flex-col items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <p class="font-medium">ไม่พบรายชื่อนักเรียนในห้องเรียนนี้</p>
                            <p class="text-xs">กรุณาตรวจสอบว่าเลือก "ปีการศึกษา" ถูกต้อง หรือได้นำเข้านักเรียนในปีการศึกษานี้แล้วหรือไม่</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        // Rebuild Header Row
        let headerHtml = `
            <th class="pb-3 font-medium w-10">เลขที่</th>
            <th class="pb-3 font-medium w-60">ชื่อ-นามสกุล</th>
        `;

        if (Array.isArray(currentUnits)) {
            currentUnits.forEach((u, i) => {
                const isUnlocked = unlockedUnitId == u.id;
                headerHtml += `
                    <th onclick="toggleUnitLock(${u.id})" class="pb-3 font-medium w-16 text-center cursor-pointer group transition-all">
                        <div class="text-[10px] font-bold ${isUnlocked ? 'text-green-600' : 'text-slate-700'} group-hover:text-blue-600">หน่วยที่ ${i + 1}</div>
                        <div class="text-[9px] ${isUnlocked ? 'text-green-500' : 'text-slate-400'}">เต็ม ${u.max_score}</div>
                        <div class="mt-1">
                            <span class="px-1.5 py-0.5 rounded-full text-[8px] font-bold ${isUnlocked ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'}">
                                ${isUnlocked ? 'กำลังแก้ไข' : 'ล็อคอยู่'}
                            </span>
                        </div>
                    </th>
                `;
            });
        }

        headerHtml += `
            <th class="pb-3 font-medium w-16 text-center">
                <div class="flex flex-col items-center">
                    <span class="text-[10px]">รวมหน่วย</span>
                </div>
            </th>
            <th class="pb-3 font-medium w-16 text-center group transition-all">
                <div onclick="toggleFinalLock()" class="cursor-pointer">
                    <div class="text-[10px] font-bold ${isFinalUnlocked ? 'text-green-600' : 'text-slate-700'} group-hover:text-blue-600">ปลายภาค</div>
                    <div class="mt-1">
                        <span class="px-1.5 py-0.5 rounded-full text-[8px] font-bold ${isFinalUnlocked ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'}">
                            ${isFinalUnlocked ? 'กำลังแก้ไข' : 'ล็อคอยู่'}
                        </span>
                    </div>
                </div>
                <div class="mt-1 flex items-center justify-center gap-1">
                    <span class="text-[8px] text-slate-400">เต็ม</span>
                    <input type="number" value="${finalMaxScore}" onchange="updateFinalMax(this.value)" class="w-8 text-[9px] border border-slate-200 rounded text-center outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </th>
            <th class="pb-3 font-medium w-16 text-center">คะแนนรวม</th>
            <th class="pb-3 font-medium w-16 text-center">ร้อยละ</th>
            <th class="pb-3 font-medium w-16 text-center group transition-all">
                <div onclick="toggleGradeLock()" class="cursor-pointer">
                    <div class="text-[10px] font-bold ${isGradeUnlocked ? 'text-green-600' : 'text-slate-700'} group-hover:text-blue-600">ผลการเรียน</div>
                    <div class="mt-1">
                        <span class="px-1.5 py-0.5 rounded-full text-[8px] font-bold ${isGradeUnlocked ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'}">
                            ${isGradeUnlocked ? 'กำลังแก้ไข' : 'ล็อคอยู่'}
                        </span>
                    </div>
                </div>
            </th>
        `;
        headerRow.innerHTML = headerHtml;

        const rowsHtml = currentStudents.map((s, index) => {
            const totalMax = Array.isArray(currentUnits) ? currentUnits.reduce((sum, u) => sum + parseFloat(u.max_score), 0) : 0;
            
            let currentTotal = 0;
            if (s.unit_scores && Array.isArray(currentUnits)) {
                s.unit_scores.forEach(us => {
                    if (currentUnits.find(u => u.id == us.learning_unit_id)) {
                        currentTotal += parseFloat(us.score) || 0;
                    }
                });
            }
            
            const unitInputs = Array.isArray(currentUnits) ? currentUnits.map(u => {
                const scoreObj = s.unit_scores ? s.unit_scores.find(us => us.learning_unit_id == u.id) : null;
                const score = scoreObj ? parseFloat(scoreObj.score) : 0;
                const isUnlocked = unlockedUnitId == u.id;
                
                return `
                    <td class="py-3 text-center">
                        <input type="number" step="0.1" max="${u.max_score}" value="${score}" 
                            oninput="updateUnitScore(${s.id}, ${u.id}, this)"
                            ${!isUnlocked ? 'disabled' : ''}
                            class="w-14 px-1 py-1 ${isUnlocked ? 'bg-white border-green-300 ring-2 ring-green-500/10 cursor-pointer' : 'bg-slate-50 border-slate-200 opacity-60'} border rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20 text-center text-xs transition-all">
                    </td>
                `;
            }).join('') : '';

            const scoreFinal = parseFloat(s.score_final) || 0;
            const totalScore = parseFloat(currentTotal + scoreFinal) || 0;
            const totalMaxWithFinal = parseFloat(totalMax + finalMaxScore) || 0;
            const percent = totalMaxWithFinal > 0 ? Math.min((totalScore / totalMaxWithFinal) * 100, 100) : 0;
            
            // ตรวจสอบว่ามีการป้อนคะแนนหรือยัง (ถ้ามีคะแนนหน่วย หรือคะแนนปลายภาคไม่เป็นค่าว่าง)
            const hasUnitScores = s.unit_scores && s.unit_scores.length > 0;
            const hasFinalScore = s.score_final !== null && s.score_final !== undefined && s.score_final !== '';
            const hasScores = hasUnitScores || hasFinalScore;
            
            const calculatedGrade = hasScores ? calculateGradeFromPercent(percent) : '-';
            
            // ใช้เกรดที่บันทึกไว้ในฐานข้อมูลถ้ามี (เพื่อรองรับ ร, มส, มผ) หรือใช้ค่าที่คำนวณได้
            const currentGrade = isGradeUnlocked ? (s.grade || calculatedGrade) : calculatedGrade;
            const isZero = currentGrade === '0';

            // Store calculated values in student object for summary
            s.score_total = totalScore;
            s.score_percent = percent;
            s.score_units = currentTotal;
            if (!s.grade) s.grade = calculatedGrade;

            return `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-3 text-slate-600 font-mono text-xs">${index + 1}</td>
                    <td class="py-3 font-medium text-slate-800 text-xs">${s.prefix || ''}${s.name || ''}&nbsp;${s.last_name || ''}</td>
                    ${unitInputs}
                    <td class="py-3 text-center font-bold text-slate-700 text-xs" id="units-total-${s.id}">${currentTotal.toFixed(1)}</td>
                    <td class="py-3 text-center">
                        <input type="number" step="0.1" value="${scoreFinal}" 
                            oninput="updateFinalScore(${s.id}, this)"
                            ${!isFinalUnlocked ? 'disabled' : ''}
                            class="w-14 px-1 py-1 ${isFinalUnlocked ? 'bg-white border-green-300 ring-2 ring-green-500/10 cursor-pointer' : 'bg-slate-50 border-slate-200 opacity-60'} border rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20 text-center text-xs transition-all">
                    </td>
                    <td class="py-3 text-center font-bold text-slate-800 text-xs" id="total-${s.id}">${totalScore.toFixed(1)}</td>
                    <td class="py-3 text-center font-bold text-blue-600 text-xs" id="percent-${s.id}">${percent.toFixed(1)}%</td>
                    <td class="py-3 text-center font-bold text-xs">
                        <input type="text" 
                            id="grade-${s.id}"
                            value="${currentGrade}"
                            oninput="updateManualGrade(${s.id}, this.value)"
                            ${!isGradeUnlocked ? 'disabled' : ''}
                            class="w-full bg-transparent border-none outline-none text-center ${isZero ? 'text-red-600' : 'text-slate-800'} ${isGradeUnlocked ? 'bg-white ring-1 ring-green-300 rounded cursor-pointer' : ''}">
                    </td>
                </tr>
            `;
        }).join('');

        tbody.innerHTML = rowsHtml;

        // Add Summary Row
        const validStudents = currentStudents.filter(s => {
            const scoreFinal = parseFloat(s.score_final) || 0;
            return (s.unit_scores && s.unit_scores.length > 0) || scoreFinal > 0;
        });

        if (validStudents.length > 0) {
            const avgTotal = validStudents.reduce((sum, s) => sum + (parseFloat(s.score_total) || 0), 0) / validStudents.length;
            const avgPercent = validStudents.reduce((sum, s) => sum + (parseFloat(s.score_percent) || 0), 0) / validStudents.length;
            const avgGrade = calculateGradeFromPercent(avgPercent);

            const summaryRow = document.createElement('tr');
            summaryRow.id = "class-summary-row";
            summaryRow.className = "bg-slate-100/50 font-bold border-t-2 border-slate-200";
            summaryRow.innerHTML = `
                <td colspan="2" class="py-4 px-4 text-right text-slate-700 text-xs uppercase tracking-wider">เฉลี่ยรวมทั้งห้อง</td>
                ${Array.isArray(currentUnits) ? currentUnits.map(() => `<td class="py-4"></td>`).join('') : ''}
                <td class="py-4"></td>
                <td class="py-4"></td>
                <td class="py-4 text-center text-slate-800 text-xs" id="avg-total">${avgTotal.toFixed(1)}</td>
                <td class="py-4 text-center text-blue-600 text-xs" id="avg-percent">${avgPercent.toFixed(1)}%</td>
                <td class="py-4 text-center text-slate-900 text-sm" id="avg-grade">${avgGrade}</td>
            `;
            tbody.appendChild(summaryRow);
        }
    }

    function updateSummaryRow() {
        const validStudents = currentStudents.filter(s => {
            const scoreFinal = parseFloat(s.score_final) || 0;
            return (s.unit_scores && s.unit_scores.length > 0) || scoreFinal > 0;
        });

        if (validStudents.length > 0) {
            const avgTotal = validStudents.reduce((sum, s) => sum + (parseFloat(s.score_total) || 0), 0) / validStudents.length;
            const avgPercent = validStudents.reduce((sum, s) => sum + (parseFloat(s.score_percent) || 0), 0) / validStudents.length;
            const avgGrade = calculateGradeFromPercent(avgPercent);

            const avgTotalEl = document.getElementById('avg-total');
            const avgPercentEl = document.getElementById('avg-percent');
            const avgGradeEl = document.getElementById('avg-grade');

            if (avgTotalEl) avgTotalEl.innerText = avgTotal.toFixed(1);
            if (avgPercentEl) avgPercentEl.innerText = avgPercent.toFixed(1) + '%';
            if (avgGradeEl) avgGradeEl.innerText = avgGrade;
        }
    }

    function updateFinalScore(studentId, input) {
        let val = parseFloat(input.value) || 0;
        if (val > finalMaxScore) {
            alert(`คะแนนสอบปลายภาคต้องไม่เกิน ${finalMaxScore} คะแนน`);
            val = finalMaxScore;
            input.value = finalMaxScore;
        }
        const student = currentStudents.find(s => s.id == studentId);
        if (student) {
            student.score_final = val;
            recalculateRow(studentId);
        }
    }

    function recalculateRow(studentId) {
        const student = currentStudents.find(s => s.id == studentId);
        if (!student) return;

        const totalMax = Array.isArray(currentUnits) ? currentUnits.reduce((sum, u) => sum + parseFloat(u.max_score), 0) : 0;
        
        // คำนวณคะแนนหน่วยทั้งหมดจากข้อมูลในตัวแปร student
        const currentTotal = student.unit_scores ? student.unit_scores.reduce((sum, us) => {
            if (Array.isArray(currentUnits) && currentUnits.find(u => u.id == us.learning_unit_id)) {
                return sum + parseFloat(us.score);
            }
            return sum;
        }, 0) : 0;
        
        const scoreFinal = parseFloat(student.score_final) || 0;
        const totalScore = parseFloat(currentTotal + scoreFinal);
        const totalMaxWithFinal = parseFloat(totalMax + finalMaxScore);
        const percent = totalMaxWithFinal > 0 ? Math.min((totalScore / totalMaxWithFinal) * 100, 100) : 0;
        
        const unitsTotalEl = document.getElementById(`units-total-${studentId}`);
        const totalEl = document.getElementById(`total-${studentId}`);
        const percentEl = document.getElementById(`percent-${studentId}`);
        const gradeEl = document.getElementById(`grade-${studentId}`);

        if (unitsTotalEl) unitsTotalEl.innerText = currentTotal.toFixed(1);
        if (totalEl) totalEl.innerText = totalScore.toFixed(1);
        if (percentEl) percentEl.innerText = percent.toFixed(1) + '%';
        
        // คำนวณเกรดเบื้องต้น
        const hasUnitScores = student.unit_scores && student.unit_scores.length > 0;
        const hasFinalScore = student.score_final !== null && student.score_final !== undefined && student.score_final !== '';
        const hasScores = hasUnitScores || hasFinalScore;
        
        const calculatedGrade = hasScores ? calculateGradeFromPercent(percent) : '-';
        
        if (gradeEl) {
            // ถ้าไม่ได้ล็อคเกรดไว้ (ล็อคอยู่) ให้ใช้ค่า Auto
            if (!isGradeUnlocked) {
                student.grade = calculatedGrade;
                gradeEl.value = calculatedGrade;
                if (calculatedGrade === '0') {
                    gradeEl.classList.add('text-red-600');
                    gradeEl.classList.remove('text-slate-800');
                } else {
                    gradeEl.classList.remove('text-red-600');
                    gradeEl.classList.add('text-slate-800');
                }
            }
        }
        
        student.score_total = totalScore;
        student.score_percent = percent;
        student.score_units = currentTotal;

        // Update Class Summary
        updateSummaryRow();
    }

    function updateManualGrade(studentId, value) {
        const student = currentStudents.find(s => s.id == studentId);
        if (student) {
            const gradeEl = document.getElementById(`grade-${studentId}`);
            student.grade = value;

            // อัปเดตสี
            if (gradeEl) {
                if (value === '0') {
                    gradeEl.classList.add('text-red-600');
                    gradeEl.classList.remove('text-slate-800');
                } else {
                    gradeEl.classList.remove('text-red-600');
                    gradeEl.classList.add('text-slate-800');
                }
            }
            
            updateSummaryRow();
        }
    }

    function updateUnitScore(studentId, unitId, input) {
        if (!Array.isArray(currentUnits)) return;
        const unit = currentUnits.find(u => u.id == unitId);
        if (!unit) return;

        const maxScore = parseFloat(unit.max_score);
        let val = parseFloat(input.value) || 0;

        if (val > maxScore) {
            alert(`คะแนนหน่วยนี้ต้องไม่เกิน ${maxScore} คะแนน`);
            val = maxScore;
            input.value = maxScore;
        }

        const student = currentStudents.find(s => s.id == studentId);
        if (!student) return;

        if (!student.unit_scores) student.unit_scores = [];
        
        let scoreObj = student.unit_scores.find(us => us.learning_unit_id == unitId);
        if (!scoreObj) {
            scoreObj = { learning_unit_id: unitId, score: 0 };
            student.unit_scores.push(scoreObj);
        }
        scoreObj.score = val;
        
        recalculateRow(studentId);
    }

    async function saveUnitScores(mode = 'units') {
        if (!currentAssignment) return;
        
        // Ensure all data is recalculated before save
        currentStudents.forEach(s => recalculateRow(s.id));
        
        const scores = [];
        const grades = [];

        currentStudents.forEach(s => {
            if (mode === 'units') {
                if (s.unit_scores) {
                    s.unit_scores.forEach(us => {
                        if (currentUnits.find(u => u.id == us.learning_unit_id)) {
                            scores.push({
                                student_id: s.id,
                                unit_id: us.learning_unit_id,
                                score: us.score
                            });
                        }
                    });
                }
            }
            // Always send grades to update the summary table (score_units or score_final)
            grades.push({
                student_id: s.id,
                score_units: s.score_units || 0,
                score_final: s.score_final || 0,
                score_total: s.score_total || 0,
                score_percent: s.score_percent || 0,
                grade: s.grade || '0'
            });
        });

        const payload = {
            subject_id: currentAssignment.subject_id,
            classroom_id: currentAssignment.classroom_id,
            academic_year: document.getElementById('grade_academic_year').value,
            semester: document.getElementById('grade_semester').value,
            scores: mode === 'units' ? scores : [], // Only send unit scores in 'units' mode
            grades: grades
        };

        try {
            const res = await fetch('api/teacher/save_unit_scores.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.message) {
                const successMsg = mode === 'units' ? 'บันทึกคะแนนหน่วยการเรียนรู้สำเร็จ' : 'บันทึกคะแนนสอบปลายภาคสำเร็จ';
                alert(successMsg);
                loadStudentsByAssignment();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error saving scores:', e);
        }
    }
</script>
