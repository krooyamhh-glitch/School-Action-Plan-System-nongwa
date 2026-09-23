<script>
    console.log('teachers.php script block started');
    async function loadSchoolTeachers() {
        const schoolId = '<?= $_SESSION['school_id'] ?? '' ?>';
        const mockRole = new URLSearchParams(window.location.search).get('mock_role') || '';
        console.log('loadSchoolTeachers: school_id =', schoolId, 'mock_role =', mockRole);
        
        if (!schoolId) {
            console.warn('loadSchoolTeachers: No school_id found in session');
            const tbody = document.getElementById('schoolTeachersTableBody');
            if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-red-400">ไม่พบรหัสโรงเรียนใน Session กรุณาเข้าสู่ระบบใหม่</td></tr>`;
            return;
        }

        try {
            const res = await fetch(`api/get_school_teachers.php?school_id=${schoolId}&mock_role=${mockRole}`);
            const teachers = await res.json();
            console.log('loadSchoolTeachers: Received teachers:', teachers);
            
            if (teachers.error) {
                console.error('loadSchoolTeachers: API Error:', teachers.error);
                alert(teachers.error);
                return;
            }

            const tbody = document.getElementById('schoolTeachersTableBody');
            if (!tbody) {
                console.error('loadSchoolTeachers: schoolTeachersTableBody not found');
                return;
            }
            
            if (!Array.isArray(teachers) || teachers.length === 0) {
                console.log('loadSchoolTeachers: No teachers found or invalid response');
                tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-slate-400">ยังไม่มีข้อมูลคุณครูในโรงเรียนนี้</td></tr>`;
                return;
            }

            // Store globally for safety
            window.lastLoadedTeachers = teachers;

            tbody.innerHTML = teachers.map((t, index) => {
                const fullName = (t.name || 'ไม่ระบุชื่อ') + (t.last_name ? ' ' + t.last_name : '');
                const safeName = fullName.replace(/'/g, "\\'");
                const isApproved = t.is_approved == 1 || t.is_approved === true || t.is_approved === '1';
                const isAcademic = t.is_academic == 1 || t.is_academic === true || t.is_academic === '1';
                
                return `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50 group">
                    <td class="py-3">
                        <div class="font-medium text-slate-800">${fullName}</div>
                        <div class="text-[10px] text-slate-400">ID: ${t.username || '-'}</div>
                        ${t.homeroom_classrooms ? `
                            <div class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 bg-blue-50/70 px-2 py-0.5 rounded-md border border-blue-100">
                                <i data-lucide="home" class="w-3 h-3 text-blue-500"></i>
                                ครูประจำชั้น: ชั้น ${t.homeroom_classrooms}
                            </div>
                        ` : ''}
                    </td>
                    <td class="py-3 text-slate-500">${t.position || '-'}</td>
                    <td class="py-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" ${isAcademic ? 'checked' : ''} onchange="toggleAcademic(${t.id}, this.checked)">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </td>
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold ${isApproved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}">
                                ${isApproved ? 'อนุมัติแล้ว' : 'รออนุมัติ'}
                            </span>
                            ${isApproved ? `
                                <button onclick="openAssignSubjectsModal(${t.id}, '${safeName}')" class="text-blue-600 hover:text-blue-800 text-xs font-bold cursor-pointer flex items-center gap-1">
                                    <i data-lucide="book-open" class="w-3 h-3"></i>
                                    มอบหมายงานสอน
                                </button>
                            ` : ''}
                        </div>
                    </td>
                    <td class="py-3 text-right">
                        <div class="flex justify-end gap-2 transition-all">
                            <button onclick="resetTeacherPassword(${t.id}, '${safeName}')" class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-xl transition-all cursor-pointer border border-amber-100 shadow-sm" title="รีเซ็ตรหัสผ่าน">
                                <i data-lucide="key" class="w-4 h-4"></i>
                            </button>
                            <button onclick="openEditTeacherModal(window.lastLoadedTeachers[${index}])" class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-xl transition-all cursor-pointer border border-blue-100 shadow-sm" title="แก้ไข">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteTeacher(${t.id})" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl transition-all cursor-pointer border border-red-100 shadow-sm" title="ลบ">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `}).join('');
            
        if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            console.error('Error in loadSchoolTeachers:', e);
            alert('เกิดข้อผิดพลาดในการโหลดข้อมูลคุณครู');
        }
    }

    function openEditTeacherModal(t = null, schoolId = null) {
        console.log('openEditTeacherModal: t =', t, 'schoolId =', schoolId);
        const modal = document.getElementById('editTeacherModal');
        const title = document.getElementById('editTeacherModalTitle');
        const form = document.getElementById('editTeacherForm');
        const userField = document.getElementById('username_field_container');
        
        if (!modal || !form) {
            console.error('openEditTeacherModal: Modal or Form not found');
            return;
        }

        form.reset();
        document.getElementById('edit_teacher_id').value = '';
        document.getElementById('edit_teacher_school_id').value = schoolId || '<?= $_SESSION['school_id'] ?? '' ?>';
        document.getElementById('edit_teacher_last_name').value = '';
        
        if (t) {
            title.innerText = 'แก้ไขข้อมูลคุณครู';
            document.getElementById('edit_teacher_id').value = t.id;
            document.getElementById('edit_teacher_name').value = t.name;
            document.getElementById('edit_teacher_last_name').value = t.last_name || '';
            document.getElementById('edit_teacher_position').value = t.position;
            document.getElementById('edit_teacher_is_academic').checked = t.is_academic == 1;
            if (userField) userField.classList.add('hidden');
            const usernameInput = document.getElementById('edit_teacher_username');
            if (usernameInput) {
                usernameInput.value = t.username || '';
                usernameInput.required = false;
            }
        } else {
            title.innerText = 'เพิ่มข้อมูลคุณครู';
            if (userField) userField.classList.remove('hidden');
            const usernameInput = document.getElementById('edit_teacher_username');
            if (usernameInput) usernameInput.required = true;
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('editTeacherForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                id: document.getElementById('edit_teacher_id').value,
                school_id: document.getElementById('edit_teacher_school_id').value,
                username: document.getElementById('edit_teacher_username').value,
                name: document.getElementById('edit_teacher_name').value,
                last_name: document.getElementById('edit_teacher_last_name').value,
                position: document.getElementById('edit_teacher_position').value,
                is_academic: document.getElementById('edit_teacher_is_academic').checked ? 1 : 0
            };

            try {
                const res = await fetch('api/admin/save_teacher.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if (result.status === 'success') {
                    alert(result.message || 'บันทึกข้อมูลสำเร็จ');
                    closeModal('editTeacherModal');
                    loadSchoolTeachers();
                    
                    // If Super Admin modal is open, refresh it
                    if (typeof viewTeachers === 'function' && window.currentViewingSchool && document.getElementById('teacherModal')?.classList.contains('flex')) {
                        viewTeachers(window.currentViewingSchool.id, window.currentViewingSchool.name);
                    }
                } else {
                    alert(result.error);
                }
            } catch (e) {
                console.error('Error saving teacher:', e);
            }
        });
    });

    async function deleteTeacher(id) {
        if (!confirm('คุณต้องการลบข้อมูลคุณครูท่านนี้ใช่หรือไม่? การดำเนินการนี้จะลบข้อมูลงานสอนและผลการเรียนที่เกี่ยวข้องทั้งหมด')) return;
        
        try {
            const res = await fetch('api/admin/delete_teacher.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.status === 'success') {
                alert('ลบข้อมูลสำเร็จ');
                loadSchoolTeachers();
                
                // If Super Admin modal is open, refresh it
                if (typeof viewTeachers === 'function' && window.currentViewingSchool && document.getElementById('teacherModal')?.classList.contains('flex')) {
                    viewTeachers(window.currentViewingSchool.id, window.currentViewingSchool.name);
                }
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error deleting teacher:', e);
        }
    }

    async function resetTeacherPassword(id, name) {
        if (!confirm(`คุณต้องการรีเซ็ตรหัสผ่านของ คุณครู ${name} เป็น 123456 ใช่หรือไม่?`)) return;
        
        try {
            const res = await fetch('api/admin/reset_teacher_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.status === 'success') {
                alert(`รีเซ็ตรหัสผ่านสําเร็จ!\nรหัสผ่านใหม่ของคุณครู ${name} คือ 123456 (1-6)`);
            } else {
                alert(result.error || 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน');
            }
        } catch (e) {
            console.error('Error resetting teacher password:', e);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }
    }

    async function toggleAcademic(userId, isAcademic) {
        const res = await fetch('api/admin/set_academic_role.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, is_academic: isAcademic })
        });
        const result = await res.json();
        if (!result.message) {
            alert(result.error);
            loadSchoolTeachers(); // Revert UI
        }
    }

    // Use existing globals if available, otherwise initialize
    window.currentAssignTeacherId = window.currentAssignTeacherId || null;
    window.currentAcademicYear = window.currentAcademicYear || '2567';
    window.currentSemester = window.currentSemester || 1;

    async function openAssignSubjectsModal(teacherId, teacherName) {
        window.currentAssignTeacherId = teacherId;
        const nameEl = document.getElementById('assignTeacherName');
        const teacher = window.lastLoadedTeachers ? window.lastLoadedTeachers.find(t => t.id === teacherId) : null;
        const homeroomInfo = teacher && teacher.homeroom_classrooms ? ` (ครูประจำชั้น: ชั้น ${teacher.homeroom_classrooms})` : '';
        if (nameEl) nameEl.innerText = `มอบหมายงานสอน - ${teacherName}${homeroomInfo}`;
        
        // Reset form
        document.getElementById('assign_level').value = '';
        document.getElementById('room_selection_container').classList.add('hidden');
        document.getElementById('subject_selection_container').classList.add('hidden');
        
        openModal('assignSubjectsModal');
        loadTeacherAssignments(teacherId);
        loadTeacherLDAssignments(teacherId);
    }

    async function onLevelChange() {
        const level = document.getElementById('assign_level').value;
        if (!level) {
            document.getElementById('room_selection_container').classList.add('hidden');
            document.getElementById('subject_selection_container').classList.add('hidden');
            return;
        }

        // Load rooms for this level
        const resRooms = await fetch('api/academic/get_classrooms.php');
        const allClassrooms = await resRooms.json();
        const rooms = allClassrooms.filter(c => c.level === level);

        const roomContainer = document.getElementById('room_selection_container');
        const roomSelect = document.getElementById('assign_room');
        
        if (rooms.length > 1) {
            roomContainer.classList.remove('hidden');
            roomSelect.innerHTML = '<option value="">เลือกห้อง</option>' + 
                rooms.map(r => `<option value="${r.id}">ห้อง ${r.room}</option>`).join('');
            roomSelect.required = true;
        } else if (rooms.length === 1) {
            roomContainer.classList.add('hidden');
            roomSelect.innerHTML = `<option value="${rooms[0].id}" selected>ห้อง ${rooms[0].room}</option>`;
            roomSelect.required = false;
        } else {
            roomContainer.classList.add('hidden');
            roomSelect.innerHTML = '<option value="">ไม่มีข้อมูลห้อง</option>';
            roomSelect.required = false;
        }

        // Load subjects for this level
        const resSubjects = await fetch('api/academic/get_subjects.php');
        const allSubjects = await resSubjects.json();
        const subjects = allSubjects.filter(s => s.level === level);

        const subjectContainer = document.getElementById('subject_selection_container');
        const subjectList = document.getElementById('subject_list');
        
        if (subjects.length > 0) {
            subjectContainer.classList.remove('hidden');
            subjectList.innerHTML = subjects.map(s => `
                <label class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-lg cursor-pointer transition-all">
                    <input type="checkbox" name="assign_subjects" value="${s.id}" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-700">${s.code}</span>
                        <span class="text-xs text-slate-500">${s.name}</span>
                    </div>
                </label>
            `).join('');
        } else {
            subjectContainer.classList.add('hidden');
            alert('ไม่พบรายวิชาในระดับชั้นนี้ กรุณาเพิ่มรายวิชาก่อน');
        }
    }

    function toggleSelectAllSubjects() {
        const checkboxes = document.getElementsByName('assign_subjects');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    }

    async function submitAssignment() {
        const level = document.getElementById('assign_level').value;
        const roomId = document.getElementById('assign_room').value;
        const checkboxes = document.getElementsByName('assign_subjects');
        const selectedSubjectIds = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selectedSubjectIds.length === 0) {
            alert('กรุณาเลือกอย่างน้อย 1 รายวิชา');
            return;
        }

        const res = await fetch('api/admin/assign_subjects.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                teacher_id: currentAssignTeacherId,
                type: 'individual',
                subject_ids: selectedSubjectIds,
                classroom_id: roomId || null,
                academic_year: currentAcademicYear,
                semester: currentSemester
            })
        });

        const result = await res.json();
        if (result.message) {
            alert(result.message);
            loadTeacherAssignments(currentAssignTeacherId);
            // Reset selection
            checkboxes.forEach(cb => cb.checked = false);
        } else {
            alert(result.error);
        }
    }

    async function loadTeacherAssignments(teacherId) {
        const tbody = document.getElementById('teacherAssignmentsTableBody');
        if (!tbody) return;
        
        const res = await fetch(`api/admin/get_teacher_assignments.php?teacher_id=${teacherId}&academic_year=${currentAcademicYear}&semester=${currentSemester}`);
        const assignments = await res.json();
        
        if (assignments.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="py-8 text-center text-slate-400">ยังไม่มีงานสอนที่มอบหมาย</td></tr>`;
        } else {
            tbody.innerHTML = assignments.map(a => `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-mono text-slate-600">${a.code}</td>
                    <td class="px-4 py-3 font-medium text-slate-800">${a.name}</td>
                    <td class="px-4 py-3 text-slate-500">${a.level}${a.room ? ' / ห้อง ' + a.room : ''}</td>
                    <td class="px-4 py-3 text-slate-500">${a.hours} ชม. / ${a.credits} นก.</td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="removeAssignment(${a.assignment_id}, ${teacherId})" class="text-red-600 hover:text-red-800 font-bold cursor-pointer">ยกเลิก</button>
                    </td>
                </tr>
            `).join('');
        }
    }

    async function removeAssignment(assignmentId, teacherId) {
        if (!confirm('ยืนยันการยกเลิกงานสอนนี้?')) return;
        const res = await fetch('api/admin/remove_assignment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ assignment_id: assignmentId })
        });
        const result = await res.json();
        if (result.message) {
            alert(result.message);
            loadTeacherAssignments(teacherId);
        } else {
            alert(result.error);
        }
    }

    async function onLDLevelChange() {
        const level = document.getElementById('ld_assign_level').value;
        if (!level) {
            document.getElementById('ld_room_selection_container').classList.add('hidden');
            return;
        }

        const resRooms = await fetch('api/academic/get_classrooms.php');
        const allClassrooms = await resRooms.json();
        const rooms = allClassrooms.filter(c => c.level === level);

        const roomContainer = document.getElementById('ld_room_selection_container');
        const roomSelect = document.getElementById('ld_assign_room');
        
        if (rooms.length > 0) {
            roomContainer.classList.remove('hidden');
            roomSelect.innerHTML = '<option value="">เลือกห้อง</option>' + 
                rooms.map(r => `<option value="${r.id}">ห้อง ${r.room}</option>`).join('');
        } else {
            roomContainer.classList.add('hidden');
            alert('ไม่พบข้อมูลห้องเรียนในระดับชั้นนี้');
        }
    }

    async function submitLDAssignment() {
        const roomId = document.getElementById('ld_assign_room').value;
        if (!roomId) {
            alert('กรุณาเลือกห้องเรียน');
            return;
        }

        const res = await fetch('api/admin/assign_learner_dev.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                teacher_id: currentAssignTeacherId,
                classroom_id: roomId,
                academic_year: currentAcademicYear,
                semester: currentSemester
            })
        });

        const result = await res.json();
        if (result.message) {
            alert(result.message);
            loadTeacherLDAssignments(currentAssignTeacherId);
        } else {
            alert(result.error);
        }
    }

    async function loadTeacherLDAssignments(teacherId) {
        const tbody = document.getElementById('teacherLDAssignmentsTableBody');
        if (!tbody) return;
        
        const res = await fetch(`api/admin/get_teacher_ld_assignments.php?teacher_id=${teacherId}&academic_year=${currentAcademicYear}&semester=${currentSemester}`);
        const assignments = await res.json();
        
        if (assignments.length === 0) {
            tbody.innerHTML = `<tr><td colspan="2" class="py-8 text-center text-slate-400">ยังไม่มีกิจกรรมพัฒนาผู้เรียนที่มอบหมาย</td></tr>`;
        } else {
            tbody.innerHTML = assignments.map(a => `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-medium text-slate-800">ชั้น ${a.level} ห้อง ${a.room}</td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="removeLDAssignment(${a.assignment_id}, ${teacherId})" class="text-red-600 hover:text-red-800 font-bold cursor-pointer">ยกเลิก</button>
                    </td>
                </tr>
            `).join('');
        }
    }

    async function removeLDAssignment(assignmentId, teacherId) {
        if (!confirm('ยืนยันการยกเลิกการมอบหมายกิจกรรมพัฒนาผู้เรียนนี้?')) return;
        const res = await fetch('api/admin/remove_ld_assignment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ assignment_id: assignmentId })
        });
        const result = await res.json();
        if (result.message) {
            alert(result.message);
            loadTeacherLDAssignments(teacherId);
        } else {
            alert(result.error);
        }
    }

    async function copyPreviousAssignments() {
        if (!currentAssignTeacherId) return;
        if (!confirm(`ยืนยันการคัดลอกงานสอนจากปีการศึกษา ${parseInt(currentAcademicYear)-1} ภาคเรียนที่ ${currentSemester} มายังปีปัจจุบัน?`)) return;

        try {
            const res = await fetch('api/admin/copy_assignments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    teacher_id: currentAssignTeacherId,
                    target_year: currentAcademicYear,
                    target_semester: currentSemester
                })
            });
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                loadTeacherAssignments(currentAssignTeacherId);
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error copying assignments:', e);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }
    }
</script>

<!-- School Admin: Manage Teachers -->
<?php if ($role === 'admin'): ?>
<div id="manage-teachers" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <i data-lucide="users"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">ข้อมูลคุณครูในโรงเรียน</h3>
                    <p class="text-xs text-slate-500">จัดการรายชื่อและมอบหมายหน้าที่งานวิชาการ</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="loadSchoolTeachers()" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all cursor-pointer" title="รีเฟรช">
                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                </button>
                <button onclick="console.log('Add Teacher clicked'); openEditTeacherModal()" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all cursor-pointer shadow-md shadow-blue-600/10">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    เพิ่มคุณครู
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-100">
                        <th class="pb-3 font-medium text-xs uppercase tracking-wider">ชื่อ-นามสกุล</th>
                        <th class="pb-3 font-medium text-xs uppercase tracking-wider">ตำแหน่ง</th>
                        <th class="pb-3 font-medium text-xs uppercase tracking-wider">งานวิชาการ</th>
                        <th class="pb-3 font-medium text-xs uppercase tracking-wider">สถานะ</th>
                        <th class="pb-3 font-medium text-xs uppercase tracking-wider text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="schoolTeachersTableBody"></tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal: เพิ่ม/แก้ไขคุณครู -->
<div id="editTeacherModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-[60]">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 id="editTeacherModalTitle" class="text-xl font-bold text-slate-800">เพิ่มข้อมูลคุณครู</h3>
            <button onclick="closeModal('editTeacherModal')" class="text-slate-400 hover:text-slate-600 cursor-pointer p-2 hover:bg-slate-200 rounded-full transition-all">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="editTeacherForm" class="p-6 space-y-4">
            <input type="hidden" id="edit_teacher_id">
            <input type="hidden" id="edit_teacher_school_id">
            
            <div id="username_field_container">
                <label class="block text-sm font-medium text-slate-700 mb-1">เลขบัตรประชาชน (ใช้เป็นชื่อผู้ใช้)</label>
                <input type="text" id="edit_teacher_username" maxlength="13" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                <p class="text-[10px] text-slate-400 mt-1">* รหัสผ่านเริ่มต้นคือ 123456</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อ</label>
                    <input type="text" id="edit_teacher_name" required placeholder="เช่น สมชาย" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">นามสกุล</label>
                    <input type="text" id="edit_teacher_last_name" required placeholder="เช่น ใจดี" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none transition-all">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ตำแหน่ง</label>
                <select id="edit_teacher_position" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none transition-all cursor-pointer">
                    <option value="ครูอัตราจ้าง">ครูอัตราจ้าง</option>
                    <option value="พนักงานราชการ">พนักงานราชการ</option>
                    <option value="ครูผู้ช่วย">ครูผู้ช่วย</option>
                    <option value="ครู">ครู</option>
                    <option value="ครูชำนาญการ">ครูชำนาญการ</option>
                    <option value="ครูชำนาญการพิเศษ">ครูชำนาญการพิเศษ</option>
                    <option value="ครูเชี่ยวชาญ">ครูเชี่ยวชาญ</option>
                    <option value="ครูเชี่ยวชาญพิเศษ">ครูเชี่ยวชาญพิเศษ</option>
                    <option value="รองผู้อำนวยการโรงเรียน">รองผู้อำนวยการโรงเรียน</option>
                    <option value="ผู้อำนวยการโรงเรียน">ผู้อำนวยการโรงเรียน</option>
                </select>
            </div>
            
            <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl border border-blue-100">
                <input type="checkbox" id="edit_teacher_is_academic" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                <label for="edit_teacher_is_academic" class="text-sm font-medium text-blue-800 cursor-pointer">มอบหมายงานวิชาการ (สามารถจัดการนักเรียนและวิชาได้)</label>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeModal('editTeacherModal')" class="flex-1 px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 cursor-pointer transition-all">ยกเลิก</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-blue-700 cursor-pointer transition-all shadow-lg shadow-blue-200">บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: รายชื่อคุณครู (Super Admin) -->
<div id="teacherModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 id="modalSchoolName" class="text-xl font-bold text-slate-800">รายชื่อคุณครู</h3>
            <div class="flex items-center gap-2">
                <button id="addTeacherBtnSuperAdmin" class="flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition-all cursor-pointer">
                    <i data-lucide="plus" class="w-3 h-3"></i>
                    เพิ่มคุณครู
                </button>
                <button onclick="closeModal('teacherModal')" class="text-slate-400 hover:text-slate-600 cursor-pointer p-2 hover:bg-slate-200 rounded-full transition-all">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto flex-1">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-100">
                        <th class="pb-3 font-medium text-slate-800">ชื่อ-นามสกุล</th>
                        <th class="pb-3 font-medium text-slate-800">ตำแหน่ง</th>
                        <th class="pb-3 font-medium text-slate-800">สถานะ</th>
                        <th class="pb-3 font-medium text-right text-slate-800">การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="modalTeacherTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: มอบหมายงานสอน -->
<div id="assignSubjectsModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div>
                <h3 id="assignTeacherName" class="text-xl font-bold text-slate-800">มอบหมายงานสอน</h3>
                <p class="text-xs text-slate-500 mt-1">กำหนดรายวิชาที่คุณครูรับผิดชอบ</p>
            </div>
            <button onclick="closeModal('assignSubjectsModal')" class="text-slate-400 hover:text-slate-600 cursor-pointer p-2 hover:bg-slate-200 rounded-full transition-all">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1 space-y-8">
            <!-- ส่วนที่ 1: เลือกชั้นเรียนและวิชา -->
            <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                <h4 class="text-sm font-bold text-blue-800 mb-4 flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    มอบหมายงานสอนรายวิชา
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-blue-700 mb-1 uppercase tracking-wider">ระดับชั้น</label>
                        <select id="assign_level" onchange="onLevelChange()" class="w-full px-4 py-2 bg-white border border-blue-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                            <option value="">เลือกระดับชั้น</option>
                            <?php foreach(['ป.1', 'ป.2', 'ป.3', 'ป.4', 'ป.5', 'ป.6', 'ม.1', 'ม.2', 'ม.3'] as $l): ?>
                                <option value="<?= $l ?>"><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="room_selection_container" class="hidden">
                        <label class="block text-xs font-bold text-blue-700 mb-1 uppercase tracking-wider">ห้อง</label>
                        <select id="assign_room" class="w-full px-4 py-2 bg-white border border-blue-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
                            <option value="">เลือกห้อง</option>
                        </select>
                    </div>
                </div>

                <div id="subject_selection_container" class="hidden space-y-4">
                    <div class="flex justify-between items-center">
                        <h5 class="text-sm font-bold text-slate-700">เลือกรายวิชา</h5>
                        <button onclick="toggleSelectAllSubjects()" class="text-xs font-bold text-blue-600 hover:text-blue-800 cursor-pointer">เลือกทั้งหมด / ยกเลิกทั้งหมด</button>
                    </div>
                    <div id="subject_list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 bg-white rounded-xl border border-blue-100">
                        <!-- Subjects will be loaded here -->
                    </div>
                    <div class="pt-2">
                        <button onclick="submitAssignment()" class="w-full bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 cursor-pointer">
                            บันทึกการมอบหมายงานสอน
                        </button>
                    </div>
                </div>
            </div>

            <!-- ส่วนที่ 1.5: มอบหมายกิจกรรมพัฒนาผู้เรียน -->
            <div class="bg-amber-50 p-6 rounded-2xl border border-amber-100">
                <h4 class="text-sm font-bold text-amber-800 mb-4 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    กำหนดค่าครูที่ปรึกษาเพื่อบันทึกกิจกรรมพัฒนาผู้เรียน กิจกรรมพัฒนาผู้เรียน (แนะแนว, ลูกเสือ, ชุมนุม, เพื่อสังคมฯ )
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-amber-700 mb-1 uppercase tracking-wider">ระดับชั้น</label>
                        <select id="ld_assign_level" onchange="onLDLevelChange()" class="w-full px-4 py-2 bg-white border border-amber-200 rounded-xl outline-none focus:ring-2 focus:ring-amber-500 transition-all cursor-pointer">
                            <option value="">เลือกระดับชั้น</option>
                            <?php foreach(['ป.1', 'ป.2', 'ป.3', 'ป.4', 'ป.5', 'ป.6', 'ม.1', 'ม.2', 'ม.3'] as $l): ?>
                                <option value="<?= $l ?>"><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="ld_room_selection_container" class="hidden">
                        <label class="block text-xs font-bold text-amber-700 mb-1 uppercase tracking-wider">ห้อง</label>
                        <select id="ld_assign_room" class="w-full px-4 py-2 bg-white border border-amber-200 rounded-xl outline-none focus:ring-2 focus:ring-amber-500 transition-all cursor-pointer">
                            <option value="">เลือกห้อง</option>
                        </select>
                    </div>
                </div>
                <button onclick="submitLDAssignment()" class="w-full bg-amber-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-amber-700 transition-all shadow-lg shadow-amber-200 cursor-pointer">
                    บันทึกข้อมูลครูที่ปรึกษาเพื่อบันทึกกิจกรรมพัฒนาผู้เรียน
                </button>
            </div>

            <!-- ส่วนที่ 2: รายการที่มอบหมายแล้ว -->
            <div class="grid grid-cols-1 gap-8">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                            รายวิชาที่รับผิดชอบในปัจจุบัน
                        </h4>
                        <button onclick="copyPreviousAssignments()" class="text-xs bg-amber-500 text-white px-3 py-1.5 rounded-lg font-bold hover:bg-amber-600 transition-all shadow-sm cursor-pointer flex items-center gap-1">
                            <i data-lucide="copy" class="w-3 h-3"></i>
                            คัดลอกงานสอนจากปีที่แล้ว
                        </button>
                    </div>
                    <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50">
                                <tr class="text-slate-500 text-xs uppercase tracking-wider">
                                    <th class="px-4 py-3 font-medium">รหัสวิชา</th>
                                    <th class="px-4 py-3 font-medium">ชื่อวิชา</th>
                                    <th class="px-4 py-3 font-medium">ระดับชั้น/ห้อง</th>
                                    <th class="px-4 py-3 font-medium">ชั่วโมง/หน่วยกิต</th>
                                    <th class="px-4 py-3 font-medium text-right">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="teacherAssignmentsTableBody" class="text-sm">
                                <tr><td colspan="5" class="py-8 text-center text-slate-400">ยังไม่มีงานสอนที่มอบหมาย</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        กิจกรรมพัฒนาผู้เรียนที่รับผิดชอบ
                    </h4>
                    <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50">
                                <tr class="text-slate-500 text-xs uppercase tracking-wider">
                                    <th class="px-4 py-3 font-medium">ระดับชั้น</th>
                                    <th class="px-4 py-3 font-medium text-right">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="teacherLDAssignmentsTableBody" class="text-sm">
                                <tr><td colspan="2" class="py-8 text-center text-slate-400">ยังไม่มีกิจกรรมพัฒนาผู้เรียนที่มอบหมาย</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
