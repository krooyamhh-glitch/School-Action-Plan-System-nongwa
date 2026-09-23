<div id="record-learner-development" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-slate-800">บันทึกกิจกรรมพัฒนาผู้เรียน</h3>
                <p class="text-sm text-slate-500">เลือกชั้นเรียนที่ต้องการบันทึกกิจกรรม</p>
            </div>
            <div class="flex gap-2">
                <select id="ld_academic_year" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm cursor-pointer" onchange="loadLearnerDevClassrooms()">
                    <!-- จะถูกเติมด้วย JavaScript -->
                </select>
                <select id="ld_semester" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none text-sm cursor-pointer" onchange="loadLearnerDevClassrooms()">
                    <option value="1">ภาคเรียนที่ 1</option>
                    <option value="2">ภาคเรียนที่ 2</option>
                </select>
            </div>
        </div>

        <div id="ld-classroom-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Classrooms will be loaded here -->
        </div>
    </div>

    <!-- Learner Development Interface (Hidden until classroom selected) -->
    <div id="ld-interface" class="hidden space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <button onclick="backToLDClassrooms()" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1 mb-2 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        กลับไปหน้าเลือกชั้นเรียน
                    </button>
                    <h3 id="ld-selected-classroom-title" class="text-xl font-bold text-slate-800">ชั้นเรียน: -</h3>
                    <div class="flex items-center gap-3 mt-1">
                        <p id="ld-selected-year-title" class="text-sm font-bold text-blue-600">ปีการศึกษา: -</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="openManageClubsModal()" class="bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-600 transition-all cursor-pointer flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        จัดการชุมนุม
                    </button>
                    <button onclick="saveLearnerDevelopment()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-all cursor-pointer">บันทึกข้อมูล</button>
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                            <th class="px-4 py-4 font-bold border-b border-slate-200 sticky left-0 bg-slate-50 z-10" style="min-width: 200px;">ชื่อ-นามสกุล</th>
                            <th class="px-4 py-4 font-bold border-b border-slate-200 text-center" style="min-width: 120px;">
                                แนะแนว
                                <button onclick="batchLDPass('guidance_result')" class="block mx-auto mt-1 text-[10px] text-blue-600 hover:text-blue-800 font-bold cursor-pointer">ผ่านทุกคน</button>
                            </th>
                            <th class="px-4 py-4 font-bold border-b border-slate-200 text-center" style="min-width: 120px;">
                                ลูกเสือเนตรนารี
                                <button onclick="batchLDPass('scout_result')" class="block mx-auto mt-1 text-[10px] text-blue-600 hover:text-blue-800 font-bold cursor-pointer">ผ่านทุกคน</button>
                            </th>
                            <th class="px-4 py-4 font-bold border-b border-slate-200 text-center" style="min-width: 250px;">ชุมนุม (ชื่อชุมนุม)</th>
                            <th class="px-4 py-4 font-bold border-b border-slate-200 text-center" style="min-width: 120px;">
                                ผลชุมนุม
                                <button onclick="batchLDPass('club_result')" class="block mx-auto mt-1 text-[10px] text-blue-600 hover:text-blue-800 font-bold cursor-pointer">ผ่านทุกคน</button>
                            </th>
                            <th class="px-4 py-4 font-bold border-b border-slate-200 text-center" style="min-width: 150px;">
                                เพื่อสังคมฯ
                                <button onclick="batchLDPass('social_result')" class="block mx-auto mt-1 text-[10px] text-blue-600 hover:text-blue-800 font-bold cursor-pointer">ผ่านทุกคน</button>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="ld-grading-table-body">
                        <!-- Students will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: จัดการชุมนุม -->
<div id="manageClubsModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-slate-800">จัดการรายชื่อชุมนุม</h3>
            <button onclick="closeModal('manageClubsModal')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <form id="addClubForm" class="flex gap-2 mb-4">
            <input type="text" id="new_club_name" placeholder="ชื่อชุมนุมใหม่" required class="flex-1 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-all cursor-pointer">เพิ่ม</button>
        </form>

        <div class="max-h-60 overflow-y-auto space-y-2" id="clubs-list">
            <!-- Clubs will be loaded here -->
        </div>
    </div>
</div>

<script>
    let currentLDClassroom = null;
    let currentLDStudents = [];
    let currentClubs = [];

    async function loadLearnerDevClassrooms() {
        const year = document.getElementById('ld_academic_year').value;
        const semester = document.getElementById('ld_semester').value;
        
        try {
            const res = await fetch(`api/teacher/get_my_ld_classrooms.php?academic_year=${year}&semester=${semester}`);
            const classrooms = await res.json();
            
            const container = document.getElementById('ld-classroom-list');
            if (classrooms.length === 0) {
                container.innerHTML = '<div class="col-span-full text-center py-12 text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">ยังไม่ได้รับการมอบหมายกิจกรรมพัฒนาผู้เรียนในภาคเรียนนี้</div>';
                return;
            }

            container.innerHTML = classrooms.map(c => `
                <div onclick="selectLDClassroom(${JSON.stringify(c).replace(/"/g, '&quot;')})" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-400 hover:shadow-md transition-all cursor-pointer group">
                    <div class="flex justify-between items-start mb-3">
                        <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-lg uppercase">ห้องเรียน</span>
                        <span class="text-xs text-slate-400">${c.level}</span>
                    </div>
                    <h4 class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors mb-1">ชั้น ${c.level} ห้อง ${c.room}</h4>
                    <div class="mt-4 flex items-center gap-1 text-blue-600 text-xs font-bold">
                        บันทึกกิจกรรม
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 5"></polyline></svg>
                    </div>
                </div>
            `).join('');
        } catch (e) {
            console.error('Error loading classrooms:', e);
        }
    }

    function selectLDClassroom(classroom) {
        currentLDClassroom = classroom;
        document.getElementById('ld-classroom-list').parentElement.classList.add('hidden');
        document.getElementById('ld-interface').classList.remove('hidden');
        
        document.getElementById('ld-selected-classroom-title').innerText = `ชั้นเรียน: ${classroom.level} ห้อง ${classroom.room}`;
        document.getElementById('ld-selected-year-title').innerText = `ปีการศึกษา: ${document.getElementById('ld_academic_year').value}`;
        
        loadLearnerDevelopment();
    }

    function backToLDClassrooms() {
        document.getElementById('ld-classroom-list').parentElement.classList.remove('hidden');
        document.getElementById('ld-interface').classList.add('hidden');
        currentLDClassroom = null;
    }

    async function loadLearnerDevelopment() {
        if (!currentLDClassroom) return;
        
        const year = document.getElementById('ld_academic_year').value;
        const semester = document.getElementById('ld_semester').value;
        
        try {
            // Load Clubs first
            await loadClubs();

            const res = await fetch(`api/teacher/get_learner_development.php?classroom_id=${currentLDClassroom.id}&academic_year=${year}&semester=${semester}`);
            currentLDStudents = await res.json();
            
            renderLDTable();
        } catch (e) {
            console.error('Error loading learner development:', e);
        }
    }

    async function loadClubs() {
        const year = document.getElementById('ld_academic_year').value;
        const res = await fetch(`api/teacher/get_clubs.php?academic_year=${year}`);
        currentClubs = await res.json();
    }

    function renderLDTable() {
        const tbody = document.getElementById('ld-grading-table-body');
        tbody.innerHTML = currentLDStudents.map((s, index) => `
            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                <td class="px-4 py-3 border-r border-slate-100 sticky left-0 bg-white group-hover:bg-slate-50 z-10">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-400">เลขที่ ${index + 1}</span>
                        <span class="text-xs font-medium text-slate-800">${s.prefix || ''}${s.name || ''}&nbsp;${s.last_name || ''}</span>
                        <span class="text-[10px] text-slate-400 font-mono">${s.student_code}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <select onchange="updateLDValue(${s.id}, 'guidance_result', this.value)" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none cursor-pointer">
                        <option value="" ${s.guidance_result === '' ? 'selected' : ''}>-</option>
                        <option value="P" ${s.guidance_result === 'P' ? 'selected' : ''} class="text-green-600 font-bold">ผ่าน</option>
                        <option value="F" ${s.guidance_result === 'F' ? 'selected' : ''} class="text-red-600 font-bold">ไม่ผ่าน</option>
                    </select>
                </td>
                <td class="px-4 py-3 text-center">
                    <select onchange="updateLDValue(${s.id}, 'scout_result', this.value)" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none cursor-pointer">
                        <option value="" ${s.scout_result === '' ? 'selected' : ''}>-</option>
                        <option value="P" ${s.scout_result === 'P' ? 'selected' : ''} class="text-green-600 font-bold">ผ่าน</option>
                        <option value="F" ${s.scout_result === 'F' ? 'selected' : ''} class="text-red-600 font-bold">ไม่ผ่าน</option>
                    </select>
                </td>
                <td class="px-4 py-3">
                    <select onchange="updateLDValue(${s.id}, 'club_id', this.value)" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none cursor-pointer">
                        <option value="">เลือกชุมนุม</option>
                        ${currentClubs.map(c => `<option value="${c.id}" ${s.club_id == c.id ? 'selected' : ''}>${c.name}</option>`).join('')}
                    </select>
                </td>
                <td class="px-4 py-3 text-center">
                    <select onchange="updateLDValue(${s.id}, 'club_result', this.value)" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none cursor-pointer">
                        <option value="" ${s.club_result === '' ? 'selected' : ''}>-</option>
                        <option value="P" ${s.club_result === 'P' ? 'selected' : ''} class="text-green-600 font-bold">ผ่าน</option>
                        <option value="F" ${s.club_result === 'F' ? 'selected' : ''} class="text-red-600 font-bold">ไม่ผ่าน</option>
                    </select>
                </td>
                <td class="px-4 py-3 text-center">
                    <select onchange="updateLDValue(${s.id}, 'social_result', this.value)" class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none cursor-pointer">
                        <option value="" ${s.social_result === '' ? 'selected' : ''}>-</option>
                        <option value="P" ${s.social_result === 'P' ? 'selected' : ''} class="text-green-600 font-bold">ผ่าน</option>
                        <option value="F" ${s.social_result === 'F' ? 'selected' : ''} class="text-red-600 font-bold">ไม่ผ่าน</option>
                    </select>
                </td>
            </tr>
        `).join('');
    }

    function updateLDValue(studentId, field, value) {
        const student = currentLDStudents.find(s => s.id === studentId);
        if (student) {
            student[field] = value;
        }
    }

    function batchLDPass(field) {
        if (!confirm('ยืนยันการให้ "ผ่าน" สำหรับทุกคนในคอลัมน์นี้?')) return;
        currentLDStudents.forEach(s => {
            s[field] = 'P';
        });
        renderLDTable();
    }

    async function saveLearnerDevelopment() {
        if (!currentLDClassroom) return;
        
        const year = document.getElementById('ld_academic_year').value;
        const semester = document.getElementById('ld_semester').value;
        
        try {
            const res = await fetch('api/teacher/save_learner_development.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    classroom_id: currentLDClassroom.id,
                    academic_year: year,
                    semester: semester,
                    results: currentLDStudents.map(s => ({
                        student_id: s.id,
                        guidance_result: s.guidance_result,
                        scout_result: s.scout_result,
                        club_id: s.club_id,
                        club_result: s.club_result,
                        social_result: s.social_result
                    }))
                })
            });
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                loadLearnerDevelopment();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error saving learner development:', e);
        }
    }

    function openManageClubsModal() {
        renderClubsList();
        openModal('manageClubsModal');
    }

    function renderClubsList() {
        const list = document.getElementById('clubs-list');
        if (currentClubs.length === 0) {
            list.innerHTML = '<p class="text-center text-slate-400 py-4">ยังไม่มีรายชื่อชุมนุม</p>';
            return;
        }
        list.innerHTML = currentClubs.map(c => `
            <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="font-medium text-slate-700">${c.name}</span>
                <button onclick="deleteClub(${c.id})" class="text-red-500 hover:text-red-700 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </button>
            </div>
        `).join('');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const addClubForm = document.getElementById('addClubForm');
        if (addClubForm) {
            addClubForm.onsubmit = async (e) => {
                e.preventDefault();
                const nameEl = document.getElementById('new_club_name');
                const yearEl = document.getElementById('ld_academic_year');
                if (!nameEl || !yearEl) return;

                const name = nameEl.value;
                const year = yearEl.value;
                
                try {
                    const res = await fetch('api/teacher/add_club.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ name, academic_year: year })
                    });
                    const result = await res.json();
                    if (result.message) {
                        nameEl.value = '';
                        await loadClubs();
                        renderClubsList();
                        renderLDTable(); // Update dropdowns in table
                    } else {
                        alert(result.error);
                    }
                } catch (e) {
                    console.error('Error adding club:', e);
                }
            };
        }
    });

    async function deleteClub(id) {
        if (!confirm('ยืนยันการลบชุมนุมนี้?')) return;
        try {
            const res = await fetch('api/teacher/delete_club.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const result = await res.json();
            if (result.message) {
                await loadClubs();
                renderClubsList();
                renderLDTable();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error deleting club:', e);
        }
    }

    // Initialize Year Dropdown
    async function initLDSection() {
        try {
            const res = await fetch('api/academic/get_academic_years.php');
            const years = await res.json();
            const yearSelect = document.getElementById('ld_academic_year');
            if (yearSelect) {
                yearSelect.innerHTML = years.map(y => `<option value="${y.year}" ${y.is_current == 1 ? 'selected' : ''}>ปีการศึกษา ${y.year}</option>`).join('');
                // Initial load
                loadLearnerDevClassrooms();
            }
        } catch (e) {
            console.error('Error initializing LD section:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // ... (existing code if any)
    });
</script>
