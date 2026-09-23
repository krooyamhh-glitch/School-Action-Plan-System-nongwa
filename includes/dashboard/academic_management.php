<!-- Academic Management Section -->
<div id="academic-management" class="section hidden space-y-6">
    <!-- Academic Year Management -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-bold text-slate-800">จัดการปีการศึกษา</h3>
                <p class="text-sm text-slate-500">กำหนดปีการศึกษาเริ่มต้นสำหรับระบบ</p>
            </div>
            <button onclick="openModal('addYearModal')" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-all cursor-pointer shadow-lg shadow-blue-500/20">เพิ่มปีการศึกษา</button>
        </div>
        
        <!-- Current Year Card -->
        <div id="currentYearDisplay" class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i data-lucide="calendar" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider">ปีการศึกษาปัจจุบัน</h4>
                    <p id="currentYearValue" class="text-xl font-black text-slate-800">-</p>
                </div>
            </div>
            <div class="px-3 py-1 bg-green-500 text-white text-[10px] font-black rounded-full uppercase">Active</div>
        </div>

        <!-- Promotion Banner -->
        <div id="promotionBanner" class="hidden mb-4 p-6 bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 rounded-3xl space-y-4">
             <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">เลื่อนชั้นนักเรียนจากปีการศึกษาที่แล้ว</h4>
                    <p class="text-xs text-slate-500 mt-0.5">พบว่าปีการศึกษาปัจจุบันยังไม่มีข้อมูลนักเรียน ท่านสามารถดึงข้อมูลนักเรียนและเลื่อนชั้นอัตโนมัติจากปีการศึกษาก่อนหน้าได้ครับ</p>
                </div>
             </div>
             <div class="flex items-center gap-3">
                 <select id="promote_from_year" class="px-4 py-2 bg-white border border-amber-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-500/20 cursor-pointer">
                     <!-- Populated by JS -->
                 </select>
                 <button onclick="promoteStudents()" class="flex-1 bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-amber-700 transition-all cursor-pointer shadow-lg shadow-amber-600/20">
                    ดำเนินการเลื่อนชั้นนักเรียน
                 </button>
             </div>
        </div>

        <!-- Academic Plan Copy Banner -->
        <div id="academicPlanBanner" class="hidden mb-8 p-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-3xl space-y-4">
             <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                    <i data-lucide="copy" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">คัดลอกแผนการสอน (งานวิชาการ)</h4>
                    <p class="text-xs text-slate-500 mt-0.5">คัดลอกการมอบหมายวิชาที่สอนจากปีการศึกษาก่อนหน้ามายังปีการศึกษาปัจจุบัน (ไม่ต้องตั้งค่ารายวิชาใหม่)</p>
                </div>
             </div>
             <div class="flex items-center gap-3">
                 <select id="copy_plan_from_year" class="px-4 py-2 bg-white border border-blue-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                     <!-- Populated by JS -->
                 </select>
                 <button onclick="copyAcademicPlan()" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition-all cursor-pointer shadow-lg shadow-blue-600/20">
                    คัดลอกแผนการสอนเดิม
                 </button>
             </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">ปีการศึกษาอื่น ๆ</h4>
            <div class="overflow-hidden border border-slate-100 rounded-2xl">
                <table class="w-full text-left">
                    <thead class="bg-slate-50">
                        <tr class="text-slate-500">
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider">ปีการศึกษา</th>
                            <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="academicYearsTableBody" class="bg-white">
                        <!-- จะถูกเติมด้วย JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Graduation Management -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-lg font-bold mb-4 text-slate-800">จัดการจบการศึกษา</h3>
        <p class="text-sm text-slate-500 mb-6">บันทึกการจบการศึกษาสำหรับนักเรียนชั้น ป.6 และ ม.3 เพื่อกำหนดรุ่นและเก็บเป็นประวัติ</p>
        
        <form id="graduationForm" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ระดับชั้นที่จบ</label>
                <select id="grad_level" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                    <option value="">เลือกระดับชั้น</option>
                    <option value="ป.6">ป.6</option>
                    <option value="ม.3">ม.3</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">รุ่นที่จบ (เช่น รุ่นที่ 50)</label>
                <input type="text" id="grad_generation" placeholder="ระบุรุ่น" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
            </div>
            <button type="submit" class="bg-amber-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-amber-700 transition-all h-[42px] cursor-pointer">บันทึกการจบการศึกษา</button>
        </form>
    </div>

    <!-- Classroom & Teacher Assignment -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-slate-800">จัดการห้องเรียนและครูประจำชั้น</h3>
            <div class="flex gap-2">
                <button onclick="loadClassroomTeachers()" class="p-2 text-slate-400 hover:text-blue-600 transition-all cursor-pointer" title="รีเฟรช">
                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-100">
                        <th class="pb-3 font-medium">ห้องเรียน</th>
                        <th class="pb-3 font-medium">ครูประจำชั้น 1</th>
                        <th class="pb-3 font-medium">ครูประจำชั้น 2</th>
                        <th class="pb-3 font-medium text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="classroomsTableBody">
                    <!-- จะถูกเติมด้วย JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Classroom Teachers Modal -->
<div id="editClassroomTeachersModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h3 class="text-xl font-bold mb-4 text-slate-800">กำหนดครูประจำชั้น</h3>
        <p id="edit_classroom_title" class="text-sm text-slate-500 mb-4 font-bold"></p>
        <form id="editClassroomTeachersForm" class="space-y-4">
            <input type="hidden" id="edit_classroom_id">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ครูประจำชั้นคนที่ 1</label>
                <select id="edit_teacher_id_1" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                    <option value="">เลือกครูประจำชั้น</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ครูประจำชั้นคนที่ 2 (ถ้ามี)</label>
                <select id="edit_teacher_id_2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                    <option value="">เลือกครูประจำชั้น</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('editClassroomTeachersModal')" class="flex-1 px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition-all cursor-pointer">ยกเลิก</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all cursor-pointer">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Academic Year Modal -->
<div id="addYearModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <h3 class="text-xl font-bold mb-4 text-slate-800">เพิ่มปีการศึกษาใหม่</h3>
        <form id="addYearForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ปีการศึกษา (พ.ศ.)</label>
                <input type="text" id="new_academic_year" placeholder="เช่น 2568" required maxlength="4" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('addYearModal')" class="flex-1 px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition-all cursor-pointer">ยกเลิก</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all cursor-pointer">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
    async function loadAcademicYears() {
        try {
            const res = await fetch('api/academic/get_academic_years.php');
            const years = await res.json();
            const tbody = document.getElementById('academicYearsTableBody');
            const currentYearValue = document.getElementById('currentYearValue');
            if (!tbody) return;
            
            const currentYearObj = years.find(y => y.is_current == 1);
            if (currentYearObj) {
                currentYearValue.innerText = `ปีการศึกษา ${currentYearObj.year}`;
                if (typeof currentAcademicYear !== 'undefined') currentAcademicYear = currentYearObj.year;
            } else {
                currentYearValue.innerText = 'ยังไม่ได้กำหนด';
            }

            const otherYears = years.filter(y => y.is_current != 1);
            
            // Check if current year has students to determine if we should show promotion banner
            const currentYear = years.find(y => y.is_current == 1);
            if (currentYear) {
                // Students check
                const sRes = await fetch(`api/academic/get_students.php?academic_year=${currentYear.year}&status=studying`);
                const students = await sRes.json();
                const banner = document.getElementById('promotionBanner');
                if (students.length === 0 && otherYears.length > 0) {
                    banner.classList.remove('hidden');
                    const promoteFrom = document.getElementById('promote_from_year');
                    promoteFrom.innerHTML = otherYears.map(y => `<option value="${y.year}">จากปีการศึกษา ${y.year}</option>`).join('');
                } else {
                    banner.classList.add('hidden');
                }

                // Academic Plan check
                // Check if current year has assignments
                const aRes = await fetch(`api/teacher/get_my_assignments.php?academic_year=${currentYear.year}`);
                const assignments = await aRes.json();
                const planBanner = document.getElementById('academicPlanBanner');
                
                // If current year has no assignments but previous years exist, show copy plan banner
                if (assignments.length === 0 && otherYears.length > 0) {
                    planBanner.classList.remove('hidden');
                    const copyFrom = document.getElementById('copy_plan_from_year');
                    copyFrom.innerHTML = otherYears.map(y => `<option value="${y.year}">ข้อมูลปีการศึกษา ${y.year}</option>`).join('');
                } else {
                    planBanner.classList.add('hidden');
                }
            }
            
            if (otherYears.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="2" class="px-6 py-8 text-center text-slate-400 italic text-xs">ไม่มีปีการศึกษาพิ่มเติม</td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = otherYears.map(y => `
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-700 text-sm">ปีการศึกษา ${y.year}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <button onclick="setCurrentYear(${y.id})" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold flex items-center gap-1.5 cursor-pointer bg-emerald-50 px-3 py-2 rounded-xl border border-emerald-100 transition-all shadow-sm">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                ตั้งเป็นปีปัจจุบัน
                            </button>
                            <button onclick="deleteAcademicYear(${y.id})" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl border border-red-100 transition-all cursor-pointer shadow-sm" title="ลบ">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                    </tr>
                `).join('');
            }

            // Update dropdowns in other sections if they exist
            updateAcademicYearDropdowns(years);
            
            if (typeof lucide !== 'undefined') lucide.createIcons();
            
            // Load classrooms as well
            loadClassroomTeachers();
        } catch (e) {
            console.error('Error loading academic years:', e);
        }
    }

    async function deleteAcademicYear(id) {
        if (!confirm('ยืนยันการลบปีการศึกษานี้?\n* ข้อมูลคะแนนและนักเรียนที่ผูกกับปีการศึกษานี้จะไม่ถูกลบออกจากฐานข้อมูลหลักแต่จะไม่แสดงในตัวเลือก')) return;
        try {
            const res = await fetch('api/academic/delete_academic_year.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.status === 'success') {
                loadAcademicYears();
            } else {
                alert(result.error || 'เกิดข้อผิดพลาด');
            }
        } catch (e) {
            console.error('Error deleting year:', e);
        }
    }

    let allTeachers = [];
    async function loadClassroomTeachers() {
        try {
            // Load teachers first if not loaded
            if (allTeachers.length === 0) {
                const tRes = await fetch('api/get_school_teachers.php');
                allTeachers = await tRes.json();
                
                // Populate modal dropdowns
                const t1 = document.getElementById('edit_teacher_id_1');
                const t2 = document.getElementById('edit_teacher_id_2');
                if (t1 && t2) {
                    const options = allTeachers.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
                    t1.innerHTML = '<option value="">เลือกครูประจำชั้น</option>' + options;
                    t2.innerHTML = '<option value="">เลือกครูประจำชั้น (ถ้ามี)</option>' + options;
                }
            }

            const res = await fetch('api/academic/get_classrooms.php');
            const classrooms = await res.json();
            const tbody = document.getElementById('classroomsTableBody');
            if (!tbody) return;

            tbody.innerHTML = classrooms.map(c => `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-4 font-medium text-slate-800">ชั้น ${c.level}/${c.room}</td>
                    <td class="py-4 text-sm text-slate-600">${c.teacher_name_1 ? c.teacher_name_1 : '<span class="text-slate-300 italic">ยังไม่ได้กำหนด</span>'}</td>
                    <td class="py-4 text-sm text-slate-600">${c.teacher_name_2 ? c.teacher_name_2 : '<span class="text-slate-300 italic">-</span>'}</td>
                    <td class="py-4 text-right">
                        <button onclick="openEditClassroomModal(${JSON.stringify(c).replace(/"/g, '&quot;')})" class="flex items-center gap-1.5 px-3 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-xl border border-blue-100 transition-all font-bold text-xs cursor-pointer shadow-sm ml-auto">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            กำหนดครู
                        </button>
                    </td>
                </tr>
            `).join('');
            
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            console.error('Error loading classrooms:', e);
        }
    }

    function openEditClassroomModal(c) {
        document.getElementById('edit_classroom_id').value = c.id;
        document.getElementById('edit_classroom_title').innerText = `ชั้น ${c.level}/${c.room}`;
        document.getElementById('edit_teacher_id_1').value = c.teacher_id_1 || '';
        document.getElementById('edit_teacher_id_2').value = c.teacher_id_2 || '';
        openModal('editClassroomTeachersModal');
    }

    document.getElementById('editClassroomTeachersForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            classroom_id: document.getElementById('edit_classroom_id').value,
            teacher_id_1: document.getElementById('edit_teacher_id_1').value,
            teacher_id_2: document.getElementById('edit_teacher_id_2').value
        };

        try {
            const res = await fetch('api/academic/update_classroom_teachers.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.status === 'success') {
                alert(result.message);
                closeModal('editClassroomTeachersModal');
                loadClassroomTeachers();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error updating classroom teachers:', e);
        }
    });

    function updateAcademicYearDropdowns(years) {
        const dropdowns = ['std_academic_year', 'edit_std_academic_year', 'filter_academic_year', 'grade_academic_year', 'char_academic_year', 'anal_academic_year', 'ld_academic_year', 'behavior-year', 'att_academic_year'];
        years.sort((a, b) => b.year - a.year);
        
        const currentYearObj = years.find(y => Number(y.is_current) === 1);
        if (currentYearObj) {
            // Update global variables if they exist
            if (typeof currentAcademicYear !== 'undefined') currentAcademicYear = currentYearObj.year;
        }

        dropdowns.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                const currentValue = el.value;
                el.innerHTML = years.map(y => `<option value="${y.year}" ${Number(y.is_current) === 1 ? 'selected' : ''}>ปีการศึกษา ${y.year}</option>`).join('');
                
                // Prioritize setting to the current academic year if found
                if (currentYearObj) {
                    el.value = currentYearObj.year;
                } else if (currentValue && years.some(y => y.year === currentValue)) {
                    el.value = currentValue;
                }
                
                // Trigger reload for relevant sections when years are populated/updated
                if (id === 'filter_academic_year' && typeof loadStudents === 'function') loadStudents();
                if (id === 'grade_academic_year' && typeof loadMyAssignments === 'function') loadMyAssignments();
                if (id === 'ld_academic_year' && typeof loadLearnerDevClassrooms === 'function') loadLearnerDevClassrooms();
                if (id === 'behavior-year' && typeof loadBehaviorClassrooms === 'function') loadBehaviorClassrooms();
                if (id === 'att_academic_year' && typeof loadAttendanceClassrooms === 'function') loadAttendanceClassrooms();
            }
        });
    }

    async function setCurrentYear(id) {
        if (!confirm('ยืนยันการเปลี่ยนปีการศึกษาปัจจุบัน?')) return;
        try {
            const res = await fetch('api/academic/set_current_year.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                loadAcademicYears();
                if (typeof loadStudents === 'function') loadStudents();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error setting current year:', e);
        }
    }

    async function promoteStudents() {
        const fromYearSelect = document.getElementById('promote_from_year');
        const fromYear = fromYearSelect.value;
        const toYearDisplay = document.getElementById('currentYearValue').innerText.replace('ปีการศึกษา ', '');
        
        if (!fromYear) return;
        
        if (!confirm(`ยืนยันการดึงข้อมูลนักเรียนจากปี ${fromYear} มายังปี ${toYearDisplay}?\nนักเรียนจะถูกเลื่อนชั้นขึ้น 1 ระดับโดยอัตโนมัติ (เช่น ป.1 -> ป.2)\n* ยกเว้นชั้นสูงสุด ม.3 ต้องดำเนินการในเมนูจบการศึกษา`)) return;
        
        try {
            const res = await fetch('api/academic/promote_students.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ from_year: fromYear, to_year: toYearDisplay })
            });
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                loadAcademicYears();
                if (typeof loadStudents === 'function') loadStudents();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error promoting students:', e);
        }
    }

    async function copyAcademicPlan() {
        const fromYearSelect = document.getElementById('copy_plan_from_year');
        const fromYear = fromYearSelect.value;
        const toYearDisplay = document.getElementById('currentYearValue').innerText.replace('ปีการศึกษา ', '');
        
        if (!fromYear) return;
        
        if (!confirm(`ยืนยันการคัดลอกแผนการจัดตารางสอนจากปี ${fromYear} มายังปี ${toYearDisplay}?\n* การดำเนินการนี้จะคัดลอกการมอบหมายงานสอนของครูทุกคนในโรงเรียน`)) return;
        
        try {
            const res = await fetch('api/academic/copy_academic_plan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ from_year: fromYear, to_year: toYearDisplay })
            });
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                loadAcademicYears();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error copying academic plan:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const addYearForm = document.getElementById('addYearForm');
        if (addYearForm) {
            addYearForm.onsubmit = async (e) => {
                e.preventDefault();
                const year = document.getElementById('new_academic_year').value;
                try {
                    const res = await fetch('api/academic/add_academic_year.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ year })
                    });
                    const result = await res.json();
                    if (result.message) {
                        alert(result.message);
                        closeModal('addYearModal');
                        addYearForm.reset();
                        loadAcademicYears();
                    } else {
                        alert(result.error);
                    }
                } catch (e) {
                    console.error('Error adding academic year:', e);
                }
            };
        }

        const graduationForm = document.getElementById('graduationForm');
        if (graduationForm) {
            graduationForm.onsubmit = async (e) => {
                e.preventDefault();
                const level = document.getElementById('grad_level').value;
                const generation = document.getElementById('grad_generation').value;
                
                if (!confirm(`ยืนยันการจบการศึกษาสำหรับนักเรียนชั้น ${level} รุ่น ${generation}?\nการดำเนินการนี้จะเปลี่ยนสถานะนักเรียนเป็น "จบการศึกษา"`)) return;
                
                try {
                    const res = await fetch('api/academic/graduate_students.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ level, generation })
                    });
                    const result = await res.json();
                    if (result.message) {
                        alert(result.message);
                        graduationForm.reset();
                        if (typeof loadStudents === 'function') loadStudents();
                    } else {
                        alert(result.error);
                    }
                } catch (e) {
                    console.error('Error graduating students:', e);
                }
            };
        }

        // Initial load
        loadAcademicYears();
    });
</script>
