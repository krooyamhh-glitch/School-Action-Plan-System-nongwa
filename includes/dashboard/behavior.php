<div id="record-behavior" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-xl font-bold text-slate-800">บันทึกพฤติกรรมนักเรียน</h3>
                <p class="text-sm text-slate-500">เลือกห้องเรียนและวันที่เพื่อบันทึกข้อมูลพฤติกรรม</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-xl border border-slate-200">
                    <input type="date" id="behavior-date" class="bg-transparent border-none text-sm focus:ring-0" value="<?= date('Y-m-d') ?>">
                </div>
                <button onclick="saveBehavior()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all flex items-center gap-2 shadow-lg shadow-blue-900/20 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    บันทึกทั้งหมด
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="space-y-1">
                <label class="text-xs font-semibold text-slate-500 uppercase">ปีการศึกษา</label>
                <select id="behavior-year" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none cursor-pointer">
                    <?php
                    $currentYear = date('Y') + 543;
                    for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                        echo "<option value='$i'>$i</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-semibold text-slate-500 uppercase">ภาคเรียน</label>
                <select id="behavior-semester" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none cursor-pointer">
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </div>
            <div class="md:col-span-2 space-y-1">
                <label class="text-xs font-semibold text-slate-500 uppercase">เลือกห้องเรียน</label>
                <div id="behavior-classroom-list" class="flex flex-wrap gap-2">
                    <!-- Classrooms will be loaded here -->
                </div>
            </div>
        </div>

        <div id="behavior-table-container" class="hidden overflow-x-auto border border-slate-200 rounded-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-bottom border-slate-200">
                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-200 w-12">ที่</th>
                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-200 min-w-[200px]">ชื่อ-นามสกุล</th>
                        <th id="behavior-cat-headers" class="contents">
                            <!-- Category headers will be loaded here -->
                        </th>
                    </tr>
                </thead>
                <tbody id="behavior-table-body">
                    <!-- Students will be loaded here -->
                </tbody>
            </table>
        </div>

        <div id="behavior-empty-state" class="py-12 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-slate-400"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <h4 class="text-slate-800 font-bold">ยังไม่ได้เลือกห้องเรียน</h4>
            <p class="text-slate-500 text-sm">กรุณาเลือกห้องเรียนด้านบนเพื่อเริ่มบันทึกพฤติกรรม</p>
        </div>
    </div>
</div>

<!-- Behavior Selection Modal -->
<div id="behavior-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 id="modal-cat-name" class="text-xl font-bold text-slate-800">เลือกพฤติกรรม</h3>
                <p id="modal-student-name" class="text-sm text-slate-500">นักเรียน: -</p>
            </div>
            <button onclick="closeBehaviorModal()" class="p-2 hover:bg-slate-100 rounded-full transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-slate-400"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="behavior-option-search" placeholder="ค้นหาหรือเพิ่มพฤติกรรมใหม่..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 outline-none">
            </div>
            
            <div id="behavior-options-list" class="max-h-[300px] overflow-y-auto space-y-2 pr-2">
                <!-- Options will be loaded here -->
            </div>

            <div id="add-option-container" class="hidden pt-4 border-t border-slate-100">
                <button onclick="addNewBehaviorOption()" class="w-full py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    เพิ่มเป็นตัวเลือกใหม่: <span id="new-option-text" class="font-bold"></span>
                </button>
            </div>
        </div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
            <button onclick="closeBehaviorModal()" class="px-6 py-2 text-slate-600 font-semibold hover:bg-slate-200 rounded-xl transition-all cursor-pointer">ยกเลิก</button>
            <button onclick="confirmBehaviorSelection()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-lg shadow-blue-900/20 transition-all cursor-pointer">ตกลง</button>
        </div>
    </div>
</div>

<script>
    let behaviorCategories = [];
    let behaviorStudents = [];
    let behaviorRecords = [];
    let currentBehaviorClassroom = null;
    let activeBehaviorCell = null; // { studentId, categoryId }
    let selectedOptionsInModal = [];

    async function initBehaviorSection() {
        try {
            // Load categories and options
            const configRes = await fetch('api/teacher/get_behavior_config.php');
            const configData = await configRes.json();
            behaviorCategories = configData.categories;

            // Load classrooms - wait a bit for academic_management to populate dropdowns
            setTimeout(() => {
                if (typeof loadBehaviorClassrooms === 'function') loadBehaviorClassrooms();
            }, 500);

            // Render category headers
            const headerContainer = document.getElementById('behavior-cat-headers');
            if (headerContainer) {
                headerContainer.innerHTML = '';
                behaviorCategories.forEach(cat => {
                    const th = document.createElement('th');
                    th.className = 'px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-200 min-w-[150px]';
                    th.textContent = cat.name;
                    headerContainer.appendChild(th);
                });
            }

            // Search listener
            const searchInput = document.getElementById('behavior-option-search');
            if (searchInput) {
                searchInput.oninput = (e) => {
                    const text = e.target.value.trim();
                    filterBehaviorOptions(text);
                };
            }

        } catch (e) {
            console.error('Error initializing behavior section:', e);
        }
    }

    async function loadBehaviorClassrooms() {
        const yearEl = document.getElementById('behavior-year');
        const semesterEl = document.getElementById('behavior-semester');
        if (!yearEl || !semesterEl) return;

        const year = yearEl.value;
        const semester = semesterEl.value;
        
        console.log('Loading classrooms for:', year, semester);
        
        try {
            const res = await fetch(`api/teacher/get_my_ld_classrooms.php?academic_year=${year}&semester=${semester}`);
            const classrooms = await res.json();
            
            console.log('Classrooms loaded:', classrooms);
            
            const container = document.getElementById('behavior-classroom-list');
            if (!container) return;
            container.innerHTML = '';
            
            if (classrooms.length === 0) {
                container.innerHTML = '<p class="text-sm text-red-500 font-bold italic">ยังไม่มีการกำหนดห้องเรียนที่รับผิดชอบ</p>';
                const tableContainer = document.getElementById('behavior-table-container');
                if (tableContainer) tableContainer.classList.add('hidden');
                const emptyState = document.getElementById('behavior-empty-state');
                if (emptyState) emptyState.classList.remove('hidden');
                return;
            }

            classrooms.forEach(c => {
                const btn = document.createElement('button');
                btn.className = 'px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium hover:bg-blue-50 hover:border-blue-200 transition-all cursor-pointer';
                btn.textContent = `${c.level}/${c.room}`;
                btn.onclick = () => selectBehaviorClassroom(c, btn);
                container.appendChild(btn);
            });
        } catch (e) {
            console.error('Error loading behavior classrooms:', e);
        }
    }

    // Add listeners for year and semester
    document.addEventListener('DOMContentLoaded', () => {
        const yearEl = document.getElementById('behavior-year');
        if (yearEl) yearEl.onchange = loadBehaviorClassrooms;
        const semesterEl = document.getElementById('behavior-semester');
        if (semesterEl) semesterEl.onchange = loadBehaviorClassrooms;
        const dateEl = document.getElementById('behavior-date');
        if (dateEl) dateEl.onchange = loadBehaviorData;
    });

    async function selectBehaviorClassroom(classroom, btn) {
        try {
            console.log('Selecting classroom:', classroom);
            document.querySelectorAll('#behavior-classroom-list button').forEach(b => {
                b.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-md', 'shadow-blue-600/20');
                b.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
            });
            
            btn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
            btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-md', 'shadow-blue-600/20');
            
            currentBehaviorClassroom = classroom;
            await loadBehaviorData();
        } catch (e) {
            console.error('Error in selectBehaviorClassroom:', e);
            alert('เกิดข้อผิดพลาดในการเลือกห้องเรียน');
        }
    }

    async function loadBehaviorData() {
        if (!currentBehaviorClassroom) return;
        
        const dateEl = document.getElementById('behavior-date');
        if (!dateEl) return;
        const checkDate = dateEl.value;
        const container = document.getElementById('behavior-table-container');
        const emptyState = document.getElementById('behavior-empty-state');
        if (!container || !emptyState) return;
        
        const year = document.getElementById('behavior-year').value;
        try {
            const res = await fetch(`api/teacher/get_behavior_data.php?classroom_id=${currentBehaviorClassroom.id}&check_date=${checkDate}&academic_year=${year}`);
            const result = await res.json();
            
            if (result.error) {
                alert(result.error);
                return;
            }
            
            behaviorStudents = result.students || [];
            behaviorRecords = result.records || [];
            
            if (behaviorStudents.length === 0) {
                container.classList.add('hidden');
                emptyState.classList.remove('hidden');
                emptyState.innerHTML = `
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-slate-400"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <h4 class="text-slate-800 font-bold">ไม่พบรายชื่อนักเรียน</h4>
                    <p class="text-slate-500 text-sm">ห้องเรียนนี้ยังไม่มีรายชื่อนักเรียนในระบบ</p>
                `;
                return;
            }
            
            renderBehaviorTable();
            
            container.classList.remove('hidden');
            emptyState.classList.add('hidden');
            
        } catch (e) {
            console.error('Error loading behavior data:', e);
            alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
        }
    }

    function renderBehaviorTable() {
        const tbody = document.getElementById('behavior-table-body');
        tbody.innerHTML = '';
        
        behaviorStudents.forEach((s, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-slate-100 hover:bg-slate-50/50 transition-colors';
            
            // #
            const tdIdx = document.createElement('td');
            tdIdx.className = 'px-4 py-3 text-sm text-slate-500 text-center border-r border-slate-200';
            tdIdx.textContent = idx + 1;
            tr.appendChild(tdIdx);
            
            // Name
            const tdName = document.createElement('td');
            tdName.className = 'px-4 py-3 text-sm font-medium text-slate-800 border-r border-slate-200';
            tdName.textContent = `${s.prefix}${s.name} ${s.last_name}`;
            tr.appendChild(tdName);
            
            // Categories
            behaviorCategories.forEach(cat => {
                const td = document.createElement('td');
                td.className = 'px-4 py-3 text-sm text-slate-600 border-r border-slate-200 cursor-pointer hover:bg-blue-50/50 transition-all min-h-[40px]';
                
                const record = behaviorRecords.find(r => r.student_id == s.id && r.category_id == cat.id);
                const text = record ? record.behavior_text : '';
                
                td.innerHTML = text ? `<div class="flex flex-wrap gap-1">${text.split(',').map(t => `<span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md text-[11px] font-medium">${t.trim()}</span>`).join('')}</div>` : '<span class="text-slate-300 italic">คลิกเพื่อบันทึก...</span>';
                
                td.onclick = () => openBehaviorModal(s, cat);
                tr.appendChild(td);
            });
            
            tbody.appendChild(tr);
        });
    }

    function openBehaviorModal(student, category) {
        activeBehaviorCell = { studentId: student.id, categoryId: category.id };
        document.getElementById('modal-cat-name').textContent = category.name;
        document.getElementById('modal-student-name').textContent = `นักเรียน: ${student.prefix}${student.name} ${student.last_name}`;
        
        const record = behaviorRecords.find(r => r.student_id == student.id && r.category_id == category.id);
        selectedOptionsInModal = record ? record.behavior_text.split(',').map(t => t.trim()).filter(t => t !== '') : [];
        
        const searchInput = document.getElementById('behavior-option-search');
        if (searchInput) searchInput.value = '';
        renderBehaviorOptions(category.id);
        
        const modal = document.getElementById('behavior-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeBehaviorModal() {
        document.getElementById('behavior-modal').classList.add('hidden');
        document.getElementById('behavior-modal').classList.remove('flex');
    }

    function renderBehaviorOptions(categoryId, filterText = '') {
        const category = behaviorCategories.find(c => c.id == categoryId);
        const list = document.getElementById('behavior-options-list');
        list.innerHTML = '';
        
        let filtered = category.options;
        if (filterText) {
            filtered = category.options.filter(o => o.option_text.toLowerCase().includes(filterText.toLowerCase()));
        }

        filtered.forEach(opt => {
            const isSelected = selectedOptionsInModal.includes(opt.option_text);
            const div = document.createElement('div');
            div.className = `p-3 rounded-xl border cursor-pointer transition-all flex items-center justify-between ${isSelected ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-slate-200 text-slate-700 hover:border-blue-200'}`;
            div.innerHTML = `
                <span class="text-sm font-medium">${opt.option_text}</span>
                ${isSelected ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-blue-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : '<div class="w-4 h-4 rounded-full border border-slate-300"></div>'}
            `;
            div.onclick = () => toggleOption(opt.option_text);
            list.appendChild(div);
        });

        const addContainer = document.getElementById('add-option-container');
        if (filterText && !category.options.some(o => o.option_text === filterText)) {
            addContainer.classList.remove('hidden');
            document.getElementById('new-option-text').textContent = filterText;
        } else {
            addContainer.classList.add('hidden');
        }
        
        // lucide.createIcons();
    }

    function filterBehaviorOptions(text) {
        if (!activeBehaviorCell) return;
        renderBehaviorOptions(activeBehaviorCell.categoryId, text);
    }

    function toggleOption(text) {
        const idx = selectedOptionsInModal.indexOf(text);
        if (idx > -1) {
            selectedOptionsInModal.splice(idx, 1);
        } else {
            selectedOptionsInModal.push(text);
        }
        const searchInput = document.getElementById('behavior-option-search');
        renderBehaviorOptions(activeBehaviorCell.categoryId, searchInput ? searchInput.value : '');
    }

    async function addNewBehaviorOption() {
        const text = document.getElementById('behavior-option-search').value.trim();
        if (!text) return;

        try {
            const res = await fetch('api/teacher/manage_behavior_options.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'add',
                    category_id: activeBehaviorCell.categoryId,
                    option_text: text
                })
            });
            const result = await res.json();
            if (result.status === 'success') {
                // Update local state
                const cat = behaviorCategories.find(c => c.id == activeBehaviorCell.categoryId);
                cat.options.push({ id: result.id, category_id: activeBehaviorCell.categoryId, option_text: text });
                
                toggleOption(text);
                document.getElementById('behavior-option-search').value = '';
                renderBehaviorOptions(activeBehaviorCell.categoryId);
            }
        } catch (e) {
            console.error('Error adding option:', e);
        }
    }

    function confirmBehaviorSelection() {
        const { studentId, categoryId } = activeBehaviorCell;
        const text = selectedOptionsInModal.join(', ');
        
        let recordIdx = behaviorRecords.findIndex(r => r.student_id == studentId && r.category_id == categoryId);
        if (recordIdx > -1) {
            behaviorRecords[recordIdx].behavior_text = text;
        } else {
            behaviorRecords.push({
                student_id: studentId,
                category_id: categoryId,
                behavior_text: text
            });
        }
        
        renderBehaviorTable();
        closeBehaviorModal();
    }

    async function saveBehavior() {
        if (!currentBehaviorClassroom) return;
        
        const year = document.getElementById('behavior-year').value;
        const semester = document.getElementById('behavior-semester').value;
        const checkDate = document.getElementById('behavior-date').value;

        try {
            const res = await fetch('api/teacher/save_behavior.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    records: behaviorRecords,
                    check_date: checkDate,
                    academic_year: year,
                    semester: semester
                })
            });
            const result = await res.json();
            if (result.status === 'success') {
                alert('บันทึกข้อมูลพฤติกรรมเรียบร้อยแล้ว');
            } else {
                alert('เกิดข้อผิดพลาด: ' + result.error);
            }
        } catch (e) {
            console.error('Error saving behavior:', e);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        }
    }

    // Listen for date change
    document.addEventListener('DOMContentLoaded', () => {
        const dateEl = document.getElementById('behavior-date');
        if (dateEl) dateEl.onchange = loadBehaviorData;
        
        initBehaviorSection();
    });
</script>
