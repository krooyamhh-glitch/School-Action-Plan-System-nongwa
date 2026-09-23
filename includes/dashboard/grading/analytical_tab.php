<div class="space-y-4">
    <div class="flex justify-between items-center bg-purple-50 p-4 rounded-2xl border border-purple-100">
        <div class="flex items-center gap-4">
            <div class="bg-purple-600 p-2 rounded-lg text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </div>
            <div>
                <h4 class="font-bold text-purple-900 text-sm">กรอกคะแนนด่วน (Batch Fill)</h4>
                <p class="text-[10px] text-purple-600">ใส่คะแนนเดียวกันให้นักเรียนทุกคนในห้อง</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <select id="batch-anal-score" class="px-3 py-2 bg-white border border-purple-200 rounded-xl outline-none text-sm font-bold text-purple-600 focus:ring-2 focus:ring-purple-500/20 cursor-pointer">
                <option value="3">3 (ดีเยี่ยม)</option>
                <option value="2">2 (ดี)</option>
                <option value="1">1 (พอใช้)</option>
                <option value="0">0 (ปรับปรุง)</option>
            </select>
            <button onclick="applyBatchAnalScore()" class="bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-purple-700 transition-all shadow-md shadow-purple-600/10 cursor-pointer">ตกลง</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="text-slate-500 border-b border-slate-100">
                    <th class="pb-3 font-medium w-12">เลขที่</th>
                    <th class="pb-3 font-medium w-48">ชื่อ-นามสกุล</th>
                    <th class="pb-3 font-medium text-center w-12">1</th>
                    <th class="pb-3 font-medium text-center w-12">2</th>
                    <th class="pb-3 font-medium text-center w-12">3</th>
                    <th class="pb-3 font-medium text-center w-12">4</th>
                    <th class="pb-3 font-medium text-center w-12">5</th>
                    <th class="pb-3 font-medium text-center w-24">สรุปผล</th>
                </tr>
            </thead>
            <tbody id="analytical-table-body">
                <!-- Students will be loaded here -->
            </tbody>
        </table>
    </div>
    <div class="bg-slate-50 p-4 rounded-xl text-xs text-slate-500 space-y-1">
        <p><strong>เกณฑ์การให้คะแนน:</strong> 3 = ดีเยี่ยม, 2 = ดี, 1 = พอใช้, 0 = ปรับปรุง</p>
        <p><strong>เกณฑ์การสรุปผล:</strong> ดีเยี่ยม (เฉลี่ย 2.50-3.00), ดี (เฉลี่ย 1.50-2.49), พอใช้ (เฉลี่ย 1.00-1.49), ปรับปรุง (เฉลี่ย 0.00-0.99)</p>
    </div>
    <div class="flex justify-end pt-4">
        <button onclick="saveAnalytical()" class="bg-blue-600 text-white px-8 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 cursor-pointer">บันทึกอ่านคิดวิเคราะห์</button>
    </div>
</div>

<script>
    function getAnalSummary(avg) {
        if (avg >= 2.5) return { text: 'ดีเยี่ยม', class: 'text-green-600' };
        if (avg >= 1.5) return { text: 'ดี', class: 'text-blue-600' };
        if (avg >= 1.0) return { text: 'พอใช้', class: 'text-orange-600' };
        return { text: 'ปรับปรุง', class: 'text-red-600' };
    }

    function applyBatchAnalScore() {
        const score = parseInt(document.getElementById('batch-anal-score').value);
        if (isNaN(score)) return;

        if (confirm(`ต้องการใส่คะแนน ${score} ให้กับนักเรียนทุกคนใช่หรือไม่?`)) {
            currentStudents.forEach(s => {
                for (let i = 1; i <= 5; i++) {
                    s['anal_item' + i] = score;
                }
                s.analytical_avg = score;
            });
            renderAnalyticalTable();
        }
    }

    function renderAnalyticalTable() {
        const tbody = document.getElementById('analytical-table-body');
        if (!tbody) return;

        tbody.innerHTML = currentStudents.map((s, index) => {
            const total = [1,2,3,4,5].reduce((sum, i) => sum + (parseInt(s['anal_item'+i]) || 0), 0);
            const avg = total / 5;
            s.analytical_avg = avg;
            const summary = getAnalSummary(avg);

            return `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-3 text-slate-600 font-mono text-xs">${index + 1}</td>
                    <td class="py-3 font-medium text-slate-800 text-xs">${s.prefix || ''}${s.name || ''}&nbsp;${s.last_name || ''}</td>
                    ${[1,2,3,4,5].map(i => `
                        <td class="py-3 text-center">
                            <select onchange="updateAnalyticalScore(${s.id}, ${i-1}, this.value)" 
                                class="w-10 px-0.5 py-1 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20 text-center text-[10px] cursor-pointer">
                                <option value="0" ${s['anal_item'+i] == 0 ? 'selected' : ''}>0</option>
                                <option value="1" ${s['anal_item'+i] == 1 ? 'selected' : ''}>1</option>
                                <option value="2" ${s['anal_item'+i] == 2 ? 'selected' : ''}>2</option>
                                <option value="3" ${s['anal_item'+i] == 3 ? 'selected' : ''}>3</option>
                            </select>
                        </td>
                    `).join('')}
                    <td class="py-3 text-center font-bold text-xs ${summary.class}" id="anal-summary-${s.id}">${summary.text}</td>
                </tr>
            `;
        }).join('');
    }

    function updateAnalyticalScore(studentId, itemIndex, value) {
        const student = currentStudents.find(s => s.id == studentId);
        if (!student) return;

        student['anal_item' + (itemIndex + 1)] = parseInt(value) || 0;
        
        let total = 0;
        for (let i = 1; i <= 5; i++) {
            total += (parseInt(student['anal_item' + i]) || 0);
        }
        const avg = total / 5;
        student.analytical_avg = avg;
        
        const summary = getAnalSummary(avg);
        const summaryEl = document.getElementById(`anal-summary-${studentId}`);
        if (summaryEl) {
            summaryEl.innerText = summary.text;
            summaryEl.className = `py-3 text-center font-bold text-xs ${summary.class}`;
        }
    }

    async function saveAnalytical() {
        const assignment = currentAssignment;
        if (!assignment) return;
        
        const yearEl = document.getElementById('grade_academic_year');
        const semEl = document.getElementById('grade_semester');
        
        const payload = {
            subject_id: assignment.subject_id,
            classroom_id: assignment.classroom_id,
            academic_year: yearEl.value,
            semester: semEl.value,
            scores: currentStudents.map(s => ({
                student_id: s.id,
                items: [
                    parseInt(s.anal_item1) || 0,
                    parseInt(s.anal_item2) || 0,
                    parseInt(s.anal_item3) || 0,
                    parseInt(s.anal_item4) || 0,
                    parseInt(s.anal_item5) || 0
                ]
            }))
        };

        try {
            const res = await fetch('api/teacher/save_analytical.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                loadStudentsByAssignment();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error saving analytical scores:', e);
        }
    }
</script>
