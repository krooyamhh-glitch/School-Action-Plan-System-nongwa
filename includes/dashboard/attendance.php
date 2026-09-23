<!-- Attendance Recording Section -->
<div id="record-attendance" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h3 class="text-xl font-bold text-slate-800">บันทึกการมาเรียน</h3>
                <p class="text-sm text-slate-500">เช็คชื่อนักเรียนรายวิชาตามตารางสอน</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <select id="att_academic_year" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all text-sm font-bold text-slate-700 cursor-pointer">
                    <!-- Academic years will be loaded here -->
                </select>
                <select id="att_semester" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all text-sm font-bold text-slate-700 cursor-pointer">
                    <option value="1">ภาคเรียนที่ 1</option>
                    <option value="2">ภาคเรียนที่ 2</option>
                </select>
                <input type="date" id="att_check_date" value="<?= date('Y-m-d') ?>" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all text-sm font-bold text-slate-700">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">เลือกห้องเรียน</label>
            <div id="attendance-classroom-list" class="flex flex-wrap gap-2">
                <!-- Classrooms will be loaded here -->
            </div>
        </div>

        <div id="attendance-main-container" class="hidden space-y-6">
            <!-- Subjects of the day -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-bold text-slate-700 text-sm">วิชาที่สอนวันนี้</h4>
                    <button onclick="applyAttendanceToAllSubjects()" class="text-xs bg-blue-100 text-blue-600 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-200 transition-all cursor-pointer">
                        คัดลอกการเช็คชื่อไปทุกวิชาของวันนี้
                    </button>
                </div>
                <div id="attendance-subject-tabs" class="flex flex-wrap gap-2">
                    <!-- Subject tabs will be loaded here -->
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-500 border-b border-slate-100">
                            <th class="pb-3 font-medium w-16">เลขที่</th>
                            <th class="pb-3 font-medium">ชื่อ-นามสกุล</th>
                            <th class="pb-3 font-medium text-center">สถานะการมาเรียน</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-table-body">
                        <!-- Students will be loaded here -->
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end pt-6">
                <button onclick="saveAttendance()" class="bg-blue-600 text-white px-8 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 cursor-pointer">บันทึกการมาเรียน</button>
            </div>
        </div>

        <div id="attendance-empty-state" class="py-12 text-center">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><polyline points="16 11 18 13 22 9"></polyline></svg>
            </div>
            <h4 class="text-slate-800 font-bold">ยังไม่ได้เลือกห้องเรียน</h4>
            <p class="text-slate-500 text-sm">กรุณาเลือกห้องเรียนด้านบนเพื่อเริ่มเช็คชื่อ</p>
        </div>
        
        <div id="attendance-no-subjects-state" class="py-12 text-center hidden">
            <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
            <h4 class="text-slate-800 font-bold">ไม่มีตารางสอนในวันนี้</h4>
            <p class="text-slate-500 text-sm">กรุณาตรวจสอบตารางสอนหรือเลือกวันที่อื่น</p>
        </div>
    </div>
</div>

<script>
    let currentAttClassroom = null;
    let attStudents = [];
    let attSubjects = [];
    let activeAttSubject = null;
    let attendanceData = [];

    async function loadAttendanceClassrooms() {
        const yearEl = document.getElementById('att_academic_year');
        const semesterEl = document.getElementById('att_semester');
        if (!yearEl || !semesterEl) return;

        const year = yearEl.value || '2567';
        const semester = semesterEl.value || 1;
        
        try {
            // We use the same LD classrooms API as it represents classrooms the teacher is responsible for
            const res = await fetch(`api/teacher/get_my_ld_classrooms.php?academic_year=${year}&semester=${semester}`);
            const classrooms = await res.json();
            const container = document.getElementById('attendance-classroom-list');
            if (!container) return;

            if (classrooms.length === 0) {
                container.innerHTML = '<p class="text-sm text-red-500 font-bold italic">ยังไม่มีการกำหนดห้องเรียนที่รับผิดชอบ</p>';
                return;
            }

            container.innerHTML = classrooms.map(c => `
                <button onclick="selectAttendanceClassroom(${c.id}, '${c.level}/${c.room}')" 
                    id="btn-att-class-${c.id}"
                    class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold transition-all hover:border-blue-500 hover:text-blue-600 cursor-pointer bg-white">
                    ชั้น ${c.level}/${c.room}
                </button>
            `).join('');

            if (currentAttClassroom) {
                selectAttendanceClassroom(currentAttClassroom.id, currentAttClassroom.name);
            }
        } catch (e) {
            console.error('Error loading attendance classrooms:', e);
        }
    }

    async function selectAttendanceClassroom(id, name) {
        currentAttClassroom = { id, name };
        
        document.querySelectorAll('[id^="btn-att-class-"]').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-md', 'shadow-blue-600/20');
            btn.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
        });
        
        const activeBtn = document.getElementById(`btn-att-class-${id}`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
            activeBtn.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-md', 'shadow-blue-600/20');
        }

        document.getElementById('attendance-empty-state').classList.add('hidden');
        loadAttendanceData();
    }

    async function loadAttendanceData() {
        if (!currentAttClassroom) return;

        const year = document.getElementById('att_academic_year').value;
        const semester = document.getElementById('att_semester').value;
        const checkDate = document.getElementById('att_check_date').value;

        try {
            const res = await fetch(`api/teacher/get_attendance_data.php?classroom_id=${currentAttClassroom.id}&academic_year=${year}&semester=${semester}&check_date=${checkDate}`);
            const result = await res.json();
            
            if (result.error) {
                alert(result.error);
                return;
            }

            attSubjects = result.subjects;
            attStudents = result.students;
            attendanceData = result.attendance;

            if (attSubjects.length === 0) {
                document.getElementById('attendance-main-container').classList.add('hidden');
                document.getElementById('attendance-no-subjects-state').classList.remove('hidden');
                return;
            }

            if (attStudents.length === 0) {
                alert('ไม่พบรายชื่อนักเรียนในห้องเรียนนี้ กรุณาตรวจสอบข้อมูลนักเรียน');
            }

            document.getElementById('attendance-no-subjects-state').classList.add('hidden');
            document.getElementById('attendance-main-container').classList.remove('hidden');

            renderSubjectTabs();
            // Select first subject by default
            if (attSubjects.length > 0) {
                selectAttSubject(attSubjects[0].subject_id, attSubjects[0].period_number);
            }
        } catch (e) {
            console.error('Error loading attendance data:', e);
            alert('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + e.message);
        }
    }

    function renderSubjectTabs() {
        const container = document.getElementById('attendance-subject-tabs');
        container.innerHTML = attSubjects.map(s => {
            const sid = s.subject_id || 'none';
            const safeId = sid.toString().replace(':', '-');
            return `
                <button onclick="selectAttSubject('${sid}', ${s.period_number})" 
                    id="btn-att-sub-${safeId}-${s.period_number}"
                    class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold transition-all hover:border-blue-500 hover:text-blue-600 cursor-pointer bg-white">
                    คาบ ${s.period_number}: ${s.subject_code || 'ไม่ระบุ'}
                </button>
            `;
        }).join('');
    }

    function selectAttSubject(subjectId, period) {
        activeAttSubject = { subjectId, period };
        const sid = subjectId || 'none';
        const safeId = sid.toString().replace(':', '-');
        
        document.querySelectorAll('[id^="btn-att-sub-"]').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
            btn.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
        });
        
        const activeBtn = document.getElementById(`btn-att-sub-${safeId}-${period}`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
            activeBtn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
        }

        renderAttendanceTable();
    }

    function renderAttendanceTable() {
        const tbody = document.getElementById('attendance-table-body');
        if (!tbody) return;

        tbody.innerHTML = attStudents.map((s, index) => {
            // Find existing status
            const existing = attendanceData.find(a => a.student_id == s.id && a.subject_id == activeAttSubject.subjectId && a.period_number == activeAttSubject.period);
            const status = existing ? existing.status : 'present';

            return `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                    <td class="py-3 text-slate-600 font-mono text-xs">${index + 1}</td>
                    <td class="py-3 font-medium text-slate-800 text-sm">${s.prefix || ''}${s.name || ''} ${s.last_name || ''}</td>
                    <td class="py-3">
                        <div class="flex justify-center gap-2">
                            ${['present', 'absent', 'late', 'sick', 'leave'].map(st => {
                                const labels = { present: 'มา', absent: 'ขาด', late: 'สาย', sick: 'ป่วย', leave: 'ลา' };
                                const colors = { 
                                    present: 'peer-checked:bg-green-600 peer-checked:text-white text-green-600 border-green-600',
                                    absent: 'peer-checked:bg-red-600 peer-checked:text-white text-red-600 border-red-600',
                                    late: 'peer-checked:bg-amber-600 peer-checked:text-white text-amber-600 border-amber-600',
                                    sick: 'peer-checked:bg-blue-600 peer-checked:text-white text-blue-600 border-blue-600',
                                    leave: 'peer-checked:bg-purple-600 peer-checked:text-white text-purple-600 border-purple-600'
                                };
                                return `
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status-${s.id}" value="${st}" ${status === st ? 'checked' : ''} 
                                            onchange="updateLocalAttendance(${s.id}, '${st}')"
                                            class="hidden peer">
                                        <span class="px-3 py-1 rounded-lg border text-[10px] font-bold transition-all ${colors[st]}">
                                            ${labels[st]}
                                        </span>
                                    </label>
                                `;
                            }).join('')}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function updateLocalAttendance(studentId, status) {
        const existingIndex = attendanceData.findIndex(a => a.student_id == studentId && a.subject_id == activeAttSubject.subjectId && a.period_number == activeAttSubject.period);
        if (existingIndex > -1) {
            attendanceData[existingIndex].status = status;
        } else {
            attendanceData.push({
                student_id: studentId,
                subject_id: activeAttSubject.subjectId,
                period_number: activeAttSubject.period,
                status: status
            });
        }
    }

    function applyAttendanceToAllSubjects() {
        if (!activeAttSubject || attSubjects.length <= 1) return;
        
        if (confirm('ต้องการคัดลอกสถานะการมาเรียนของวิชานี้ไปยังทุกวิชาที่สอนในวันนี้ใช่หรือไม่?')) {
            const currentStatuses = attStudents.map(s => {
                const found = attendanceData.find(a => a.student_id == s.id && a.subject_id == activeAttSubject.subjectId && a.period_number == activeAttSubject.period);
                return { student_id: s.id, status: found ? found.status : 'present' };
            });

            attSubjects.forEach(sub => {
                if (sub.subject_id == activeAttSubject.subjectId && sub.period_number == activeAttSubject.period) return;
                
                currentStatuses.forEach(cs => {
                    const idx = attendanceData.findIndex(a => a.student_id == cs.student_id && a.subject_id == sub.subject_id && a.period_number == sub.period_number);
                    if (idx > -1) {
                        attendanceData[idx].status = cs.status;
                    } else {
                        attendanceData.push({
                            student_id: cs.student_id,
                            subject_id: sub.subject_id,
                            period_number: sub.period_number,
                            status: cs.status
                        });
                    }
                });
            });
            
            alert('คัดลอกข้อมูลเรียบร้อยแล้ว อย่าลืมกดบันทึกข้อมูลทั้งหมด');
        }
    }

    async function saveAttendance() {
        if (!currentAttClassroom) return;

        // Prepare records for ALL subjects of the day that have data
        const records = [];
        attSubjects.forEach(sub => {
            attStudents.forEach(s => {
                const found = attendanceData.find(a => a.student_id == s.id && a.subject_id == sub.subject_id && a.period_number == sub.period_number);
                records.push({
                    student_id: s.id,
                    subject_id: sub.subject_id,
                    period_number: sub.period_number,
                    status: found ? found.status : 'present'
                });
            });
        });

        const payload = {
            classroom_id: currentAttClassroom.id,
            academic_year: document.getElementById('att_academic_year').value,
            semester: document.getElementById('att_semester').value,
            check_date: document.getElementById('att_check_date').value,
            records: records
        };

        try {
            const res = await fetch('api/teacher/save_attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                loadAttendanceData();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error saving attendance:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const yearEl = document.getElementById('att_academic_year');
        if (yearEl) yearEl.addEventListener('change', loadAttendanceClassrooms);
        const semEl = document.getElementById('att_semester');
        if (semEl) semEl.addEventListener('change', loadAttendanceClassrooms);
        const dateEl = document.getElementById('att_check_date');
        if (dateEl) dateEl.addEventListener('change', loadAttendanceData);
    });

    async function initAttendanceSection() {
        try {
            const res = await fetch('api/academic/get_academic_years.php');
            const years = await res.json();
            const el = document.getElementById('att_academic_year');
            if (el) {
                const sortedYears = [...years].sort((a, b) => b.year - a.year);
                el.innerHTML = sortedYears.map(y => `<option value="${y.year}" ${Number(y.is_current) === 1 ? 'selected' : ''}>ปีการศึกษา ${y.year}</option>`).join('');
                const current = sortedYears.find(y => Number(y.is_current) === 1);
                if (current) el.value = current.year;
            }
        } catch (e) {
            console.error('Error initializing attendance section:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', initAttendanceSection);
</script>
