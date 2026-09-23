<div class="space-y-4">
    <div class="flex justify-between items-center bg-blue-50 p-4 rounded-2xl border border-blue-100">
        <div class="flex items-center gap-4">
            <div class="bg-blue-600 p-2 rounded-lg text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div>
                <h4 class="font-bold text-blue-900 text-sm">กรอกคะแนนด่วน (Batch Fill)</h4>
                <p class="text-[10px] text-blue-600">ใส่คะแนนเดียวกันให้นักเรียนทุกคนในห้อง</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <select id="batch-char-score" class="px-3 py-2 bg-white border border-blue-200 rounded-xl outline-none text-sm font-bold text-blue-600 focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                <option value="3">3 (ดีเยี่ยม)</option>
                <option value="2">2 (ดี)</option>
                <option value="1">1 (ผ่าน)</option>
                <option value="0">0 (ไม่ผ่าน)</option>
            </select>
            <button onclick="applyBatchCharScore()" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-600/10 cursor-pointer">ตกลง</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[1000px]">
            <thead>
                <tr class="text-slate-500 border-b border-slate-100">
                    <th class="pb-3 font-medium w-12">เลขที่</th>
                    <th class="pb-3 font-medium w-48">ชื่อ-นามสกุล</th>
                    <th class="pb-3 font-medium text-center w-12">1</th>
                    <th class="pb-3 font-medium text-center w-12">2</th>
                    <th class="pb-3 font-medium text-center w-12">3</th>
                    <th class="pb-3 font-medium text-center w-12">4</th>
                    <th class="pb-3 font-medium text-center w-12">5</th>
                    <th class="pb-3 font-medium text-center w-12">6</th>
                    <th class="pb-3 font-medium text-center w-12">7</th>
                    <th class="pb-3 font-medium text-center w-12">8</th>
                    <th class="pb-3 font-medium text-center w-24">สรุปผล</th>
                </tr>
            </thead>
            <tbody id="characteristics-table-body">
                <!-- Students will be loaded here -->
            </tbody>
        </table>
    </div>
    <div class="bg-slate-50 p-4 rounded-xl text-xs text-slate-500 space-y-1">
        <p><strong>คำอธิบายคุณลักษณะ 8 ข้อ:</strong> 1.รักชาติ ศาสน์ กษัตริย์ 2.ซื่อสัตย์สุจริต 3.มีวินัย 4.ใฝ่เรียนรู้ 5.อยู่อย่างพอเพียง 6.มุ่งมั่นในการทำงาน 7.รักความเป็นไทย 8.มีจิตสาธารณะ</p>
        <p><strong>เกณฑ์การให้คะแนน:</strong> 3 = ดีเยี่ยม, 2 = ดี, 1 = ผ่าน, 0 = ไม่ผ่าน</p>
        <p><strong>เกณฑ์การสรุปผล:</strong> ดีเยี่ยม (เฉลี่ย 2.50-3.00), ดี (เฉลี่ย 1.50-2.49), ผ่าน (เฉลี่ย 1.00-1.49), ไม่ผ่าน (เฉลี่ย 0.00-0.99)</p>
    </div>
    <div class="flex justify-end pt-4">
        <button onclick="saveCharacteristics()" class="bg-blue-600 text-white px-8 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 cursor-pointer">บันทึกคุณลักษณะอันพึงประสงค์</button>
    </div>
</div>

<script>
    function getCharSummary(avg) {
        if (avg >= 2.5) return { text: 'ดีเยี่ยม', class: 'text-green-600' };
        if (avg >= 1.5) return { text: 'ดี', class: 'text-blue-600' };
        if (avg >= 1.0) return { text: 'ผ่าน', class: 'text-orange-600' };
        return { text: 'ไม่ผ่าน', class: 'text-red-600' };
    }

    function applyBatchCharScore() {
        const score = parseInt(document.getElementById('batch-char-score').value);
        if (isNaN(score)) return;

        if (confirm(`ต้องการใส่คะแนน ${score} ให้กับนักเรียนทุกคนใช่หรือไม่?`)) {
            currentStudents.forEach(s => {
                for (let i = 1; i <= 8; i++) {
                    s['item' + i] = score;
                }
                s.average_score = score;
            });
            renderCharacteristicsTable();
        }
    }

    function renderCharacteristicsTable() {
        const tbody = document.getElementById('characteristics-table-body');
        if (!tbody) return;

        tbody.innerHTML = currentStudents.map((s, index) => {
            const total = [1,2,3,4,5,6,7,8].reduce((sum, i) => sum + (parseInt(s['item'+i]) || 0), 0);
            const avg = total / 8;
            s.average_score = avg;
            const summary = getCharSummary(avg);

            return `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-3 text-slate-600 font-mono text-xs">${index + 1}</td>
                    <td class="py-3 font-medium text-slate-800 text-xs">${s.prefix || ''}${s.name || ''}&nbsp;${s.last_name || ''}</td>
                    ${[1,2,3,4,5,6,7,8].map(i => `
                        <td class="py-3 text-center">
                            <select onchange="updateCharScore(${s.id}, ${i-1}, this.value)" 
                                class="w-10 px-0.5 py-1 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20 text-center text-[10px] cursor-pointer">
                                <option value="0" ${s['item'+i] == 0 ? 'selected' : ''}>0</option>
                                <option value="1" ${s['item'+i] == 1 ? 'selected' : ''}>1</option>
                                <option value="2" ${s['item'+i] == 2 ? 'selected' : ''}>2</option>
                                <option value="3" ${s['item'+i] == 3 ? 'selected' : ''}>3</option>
                            </select>
                        </td>
                    `).join('')}
                    <td class="py-3 text-center font-bold text-xs ${summary.class}" id="char-summary-${s.id}">${summary.text}</td>
                </tr>
            `;
        }).join('');
    }

    function updateCharScore(studentId, itemIndex, value) {
        const student = currentStudents.find(s => s.id == studentId);
        if (!student) return;

        student['item' + (itemIndex + 1)] = parseInt(value) || 0;
        
        let total = 0;
        for (let i = 1; i <= 8; i++) {
            total += (parseInt(student['item' + i]) || 0);
        }
        const avg = total / 8;
        student.average_score = avg;
        
        const summary = getCharSummary(avg);
        const summaryEl = document.getElementById(`char-summary-${studentId}`);
        if (summaryEl) {
            summaryEl.innerText = summary.text;
            summaryEl.className = `py-3 text-center font-bold text-xs ${summary.class}`;
        }
    }

    async function saveCharacteristics() {
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
                    parseInt(s.item1) || 0,
                    parseInt(s.item2) || 0,
                    parseInt(s.item3) || 0,
                    parseInt(s.item4) || 0,
                    parseInt(s.item5) || 0,
                    parseInt(s.item6) || 0,
                    parseInt(s.item7) || 0,
                    parseInt(s.item8) || 0
                ]
            }))
        };

        try {
            const res = await fetch('api/teacher/save_characteristics.php', {
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
            console.error('Error saving characteristics:', e);
        }
    }
</script>
