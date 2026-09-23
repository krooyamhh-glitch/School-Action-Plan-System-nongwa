<!-- Academic/Admin: Manage Subjects -->
<?php if ($role === 'admin' || (isset($_SESSION['is_academic']) && $_SESSION['is_academic'])): ?>
<div id="manage-subjects" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">เพิ่มรายวิชา</h3>
            <div class="flex gap-2">
                <button onclick="downloadSubjectTemplate()" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-200 cursor-pointer transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    ดาวน์โหลด Template
                </button>
                <input type="file" id="importSubjectExcel" accept=".xlsx, .xls" class="hidden" onchange="handleSubjectExcelImport(event)">
                <button onclick="document.getElementById('importSubjectExcel').click()" class="bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-700 cursor-pointer transition-all">นำเข้าจาก Excel</button>
            </div>
        </div>
        <form id="addSubjectForm" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <input type="text" id="sub_code" placeholder="รหัสวิชา" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <input type="text" id="sub_name" placeholder="ชื่อวิชา" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <select id="sub_learning_area" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none cursor-pointer">
                <option value="">กลุ่มสาระการเรียนรู้</option>
                <option value="ภาษาไทย">ภาษาไทย</option>
                <option value="คณิตศาสตร์">คณิตศาสตร์</option>
                <option value="วิทยาศาสตร์และเทคโนโลยี">วิทยาศาสตร์และเทคโนโลยี</option>
                <option value="สังคมศึกษา ศาสนา และวัฒนธรรม">สังคมศึกษา ศาสนา และวัฒนธรรม</option>
                <option value="สุขศึกษาและพลศึกษา">สุขศึกษาและพลศึกษา</option>
                <option value="ศิลปะ">ศิลปะ</option>
                <option value="การงานอาชีพ">การงานอาชีพ</option>
                <option value="ภาษาต่างประเทศ">ภาษาต่างประเทศ</option>
                <option value="กิจกรรมพัฒนาผู้เรียน">กิจกรรมพัฒนาผู้เรียน</option>
            </select>
            <select id="sub_level" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none cursor-pointer">
                <option value="">ระดับชั้น</option>
                <option value="ป.1">ป.1</option><option value="ป.2">ป.2</option><option value="ป.3">ป.3</option>
                <option value="ป.4">ป.4</option><option value="ป.5">ป.5</option><option value="ป.6">ป.6</option>
                <option value="ม.1">ม.1</option><option value="ม.2">ม.2</option><option value="ม.3">ม.3</option>
            </select>
            <input type="number" id="sub_hours" placeholder="ชั่วโมง" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <input type="number" step="0.5" id="sub_credits" placeholder="หน่วยกิต" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 cursor-pointer transition-all">บันทึก</button>
        </form>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-lg font-bold mb-4">รายชื่อวิชา</h3>
        
        <!-- Subject Filters -->
        <div id="subjectFilters" class="mb-6 border-b border-slate-100 pb-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">เลือกระดับชั้น</p>
            <div id="subjectLevelButtons" class="flex flex-wrap gap-2"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-100">
                        <th class="pb-3 font-medium">รหัสวิชา</th>
                        <th class="pb-3 font-medium">ชื่อวิชา</th>
                        <th class="pb-3 font-medium">กลุ่มสาระฯ</th>
                        <th class="pb-3 font-medium">ระดับชั้น</th>
                        <th class="pb-3 font-medium">ชั่วโมง/หน่วยกิต</th>
                        <th class="pb-3 font-medium">การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="subjectsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Import Subject Preview Modal -->
<div id="importSubjectPreviewModal" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-slate-800">ตรวจสอบข้อมูลรายวิชาก่อนนำเข้า</h3>
                <p id="importSubjectSummaryText" class="text-sm text-slate-500 mt-1"></p>
            </div>
            <button onclick="closeModal('importSubjectPreviewModal')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-100">
                        <th class="pb-3 font-medium">รหัสวิชา</th>
                        <th class="pb-3 font-medium">ชื่อวิชา</th>
                        <th class="pb-3 font-medium">กลุ่มสาระฯ</th>
                        <th class="pb-3 font-medium">ระดับชั้น</th>
                        <th class="pb-3 font-medium">ชั่วโมง</th>
                        <th class="pb-3 font-medium">หน่วยกิต</th>
                    </tr>
                </thead>
                <tbody id="importSubjectPreviewTableBody"></tbody>
            </table>
        </div>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button onclick="closeModal('importSubjectPreviewModal')" class="px-6 py-2 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">ยกเลิก</button>
            <button id="confirmSubjectImportBtn" onclick="confirmSubjectImport()" class="bg-blue-600 text-white px-8 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-all cursor-pointer">ยืนยันการนำเข้า</button>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div id="editSubjectModal" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800">แก้ไขข้อมูลรายวิชา</h3>
            <button onclick="closeModal('editSubjectModal')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="editSubjectForm" class="p-6 space-y-4">
            <input type="hidden" id="edit_sub_id">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">รหัสวิชา</label>
                <input type="text" id="edit_sub_code" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อวิชา</label>
                <input type="text" id="edit_sub_name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">กลุ่มสาระการเรียนรู้</label>
                <select id="edit_sub_learning_area" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none cursor-pointer">
                    <option value="">เลือกกลุ่มสาระฯ</option>
                    <option value="ภาษาไทย">ภาษาไทย</option>
                    <option value="คณิตศาสตร์">คณิตศาสตร์</option>
                    <option value="วิทยาศาสตร์และเทคโนโลยี">วิทยาศาสตร์และเทคโนโลยี</option>
                    <option value="สังคมศึกษา ศาสนา และวัฒนธรรม">สังคมศึกษา ศาสนา และวัฒนธรรม</option>
                    <option value="สุขศึกษาและพลศึกษา">สุขศึกษาและพลศึกษา</option>
                    <option value="ศิลปะ">ศิลปะ</option>
                    <option value="การงานอาชีพ">การงานอาชีพ</option>
                    <option value="ภาษาต่างประเทศ">ภาษาต่างประเทศ</option>
                    <option value="กิจกรรมพัฒนาผู้เรียน">กิจกรรมพัฒนาผู้เรียน</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ระดับชั้น</label>
                <select id="edit_sub_level" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none cursor-pointer">
                    <option value="ป.1">ป.1</option><option value="ป.2">ป.2</option><option value="ป.3">ป.3</option>
                    <option value="ป.4">ป.4</option><option value="ป.5">ป.5</option><option value="ป.6">ป.6</option>
                    <option value="ม.1">ม.1</option><option value="ม.2">ม.2</option><option value="ม.3">ม.3</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ชั่วโมง</label>
                    <input type="number" id="edit_sub_hours" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">หน่วยกิต</label>
                    <input type="number" step="0.5" id="edit_sub_credits" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
                </div>
            </div>
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeModal('editSubjectModal')" class="flex-1 px-4 py-2 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 cursor-pointer transition-all">ยกเลิก</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-blue-700 cursor-pointer transition-all">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<script>
    function downloadSubjectTemplate() {
        const data = [
            ["รหัสวิชา", "ชื่อวิชา", "กลุ่มสาระการเรียนรู้", "ระดับชั้น", "ชั่วโมง", "หน่วยกิต"],
            ["ท11101", "ภาษาไทย 1", "ภาษาไทย", "ป.1", "200", "5.0"],
            ["ค11101", "คณิตศาสตร์ 1", "คณิตศาสตร์", "ป.1", "200", "5.0"]
        ];
        const worksheet = XLSX.utils.aoa_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Template");
        XLSX.writeFile(workbook, "subject_template.xlsx");
    }

    function handleSubjectExcelImport(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                const json = XLSX.utils.sheet_to_json(worksheet);

                subjectsToImport = json.map(row => ({
                    code: String(row['รหัสวิชา'] || row['code'] || ''),
                    name: String(row['ชื่อวิชา'] || row['name'] || ''),
                    learning_area: String(row['กลุ่มสาระการเรียนรู้'] || row['learning_area'] || ''),
                    level: String(row['ระดับชั้น'] || row['level'] || ''),
                    hours: parseInt(row['ชั่วโมง'] || row['hours'] || '40'),
                    credits: parseFloat(row['หน่วยกิต'] || row['credits'] || '1.0')
                })).filter(s => s.code && s.name && s.level);

                if (subjectsToImport.length === 0) {
                    alert('ไม่พบข้อมูลรายวิชาที่ถูกต้องในไฟล์ Excel');
                    return;
                }

                renderSubjectImportPreview();
                openModal('importSubjectPreviewModal');
            } catch (err) {
                console.error('Excel processing error:', err);
                alert('เกิดข้อผิดพลาดในการประมวลผลไฟล์ Excel: ' + err.message);
            }
        };
        reader.readAsArrayBuffer(file);
        event.target.value = '';
    }

    function renderSubjectImportPreview() {
        const tbody = document.getElementById('importSubjectPreviewTableBody');
        const summary = document.getElementById('importSubjectSummaryText');
        
        if (summary) {
            summary.innerText = `พบข้อมูลรายวิชาทั้งหมด ${subjectsToImport.length} รายการ`;
        }

        tbody.innerHTML = subjectsToImport.map(s => `
            <tr class="border-b border-slate-50">
                <td class="py-2">${s.code}</td>
                <td class="py-2">${s.name}</td>
                <td class="py-2">${s.learning_area}</td>
                <td class="py-2">${s.level}</td>
                <td class="py-2">${s.hours}</td>
                <td class="py-2">${s.credits}</td>
            </tr>
        `).join('');
    }

    async function loadSubjects() {
        try {
            const res = await fetch('api/academic/get_subjects.php');
            allSubjects = await res.json();
            
            // Extract unique levels
            const levels = [...new Set(allSubjects.map(s => s.level))].sort();
            renderSubjectLevelButtons(levels);
            
            if (selectedSubjectLevel) {
                filterSubjectsByLevel(selectedSubjectLevel);
            } else {
                const tbody = document.getElementById('subjectsTableBody');
                if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-slate-400">กรุณาเลือกระดับชั้นเพื่อดูข้อมูลรายวิชา</td></tr>';
            }
        } catch (e) {
            console.error('Error in loadSubjects:', e);
        }
    }

    function renderSubjectLevelButtons(levels) {
        const container = document.getElementById('subjectLevelButtons');
        if (!container) return;
        
        container.innerHTML = levels.map(level => `
            <button onclick="filterSubjectsByLevel('${level}')" 
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all cursor-pointer ${selectedSubjectLevel === level ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}">
                ${level}
            </button>
        `).join('');
    }

    function filterSubjectsByLevel(level) {
        selectedSubjectLevel = level;
        
        // Update UI
        const levels = [...new Set(allSubjects.map(s => s.level))].sort();
        renderSubjectLevelButtons(levels);
        
        const tbody = document.getElementById('subjectsTableBody');
        if (!tbody) return;
        
        const filtered = allSubjects.filter(s => s.level === level);
        
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-slate-400">ไม่พบข้อมูลรายวิชาในระดับชั้นนี้</td></tr>';
            return;
        }

        tbody.innerHTML = filtered.map(s => `
            <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                <td class="py-3 text-slate-600 font-mono">${s.code}</td>
                <td class="py-3 font-medium text-slate-800">${s.name}</td>
                <td class="py-3 text-slate-500">${s.learning_area || '-'}</td>
                <td class="py-3 text-slate-500">${s.level}</td>
                <td class="py-3 text-slate-500">${s.hours} ชม. / ${s.credits} นก.</td>
                <td class="py-3">
                    <div class="flex gap-2">
                        <button onclick="editSubject(${s.id}, '${s.code}', '${s.name}', '${s.level}', ${s.hours}, ${s.credits}, '${s.learning_area || ''}')" class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-xl transition-all cursor-pointer border border-blue-100 shadow-sm" title="แก้ไข">
                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                        </button>
                        <button onclick="deleteSubject(${s.id})" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl transition-all cursor-pointer border border-red-100 shadow-sm" title="ลบ">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async function confirmSubjectImport() {
        const confirmSubBtn = document.getElementById('confirmSubjectImportBtn');
        if (!confirmSubBtn) return;

        if (!subjectsToImport || subjectsToImport.length === 0) {
            alert('ไม่พบข้อมูลที่จะนำเข้า กรุณาเลือกไฟล์ใหม่อีกครั้ง');
            return;
        }

        confirmSubBtn.disabled = true;
        confirmSubBtn.innerText = 'กำลังนำเข้า...';

        try {
            const res = await fetch('api/academic/import_subjects.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ subjects: subjectsToImport })
            });
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                closeModal('importSubjectPreviewModal');
                loadSubjects();
            } else {
                alert(result.error || 'เกิดข้อผิดพลาดในการนำเข้า');
            }
        } catch (e) {
            console.error('Error in subject import:', e);
            alert('เกิดข้อผิดพลาดในการนำเข้าข้อมูล');
        } finally {
            confirmSubBtn.disabled = false;
            confirmSubBtn.innerText = 'ยืนยันการนำเข้า';
        }
    }

    async function deleteSubject(id) {
        if (!confirm('ยืนยันการลบรายวิชา?')) return;
        const res = await fetch('api/academic/delete_subject.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.message) {
            alert(result.message);
            loadSubjects();
        } else {
            alert(result.error);
        }
    }

    function editSubject(id, code, name, level, hours, credits, learning_area) {
        document.getElementById('edit_sub_id').value = id;
        document.getElementById('edit_sub_code').value = code;
        document.getElementById('edit_sub_name').value = name;
        document.getElementById('edit_sub_level').value = level;
        document.getElementById('edit_sub_hours').value = hours;
        document.getElementById('edit_sub_credits').value = credits;
        document.getElementById('edit_sub_learning_area').value = learning_area || '';
        openModal('editSubjectModal');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const editSubjectForm = document.getElementById('editSubjectForm');
        if (editSubjectForm) {
            editSubjectForm.onsubmit = async (e) => {
                e.preventDefault();
                const res = await fetch('api/academic/update_subject.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: document.getElementById('edit_sub_id').value,
                        code: document.getElementById('edit_sub_code').value,
                        name: document.getElementById('edit_sub_name').value,
                        level: document.getElementById('edit_sub_level').value,
                        hours: document.getElementById('edit_sub_hours').value,
                        credits: document.getElementById('edit_sub_credits').value,
                        learning_area: document.getElementById('edit_sub_learning_area').value
                    })
                });
                const result = await res.json();
                if (result.message) {
                    alert(result.message);
                    closeModal('editSubjectModal');
                    loadSubjects();
                } else {
                    alert(result.error);
                }
            };
        }
        const addSubjectForm = document.getElementById('addSubjectForm');
        if (addSubjectForm) {
            addSubjectForm.onsubmit = async (e) => {
                e.preventDefault();
                const res = await fetch('api/academic/add_subject.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        code: document.getElementById('sub_code').value,
                        name: document.getElementById('sub_name').value,
                        level: document.getElementById('sub_level').value,
                        hours: document.getElementById('sub_hours').value,
                        credits: document.getElementById('sub_credits').value,
                        learning_area: document.getElementById('sub_learning_area').value
                    })
                });
                const result = await res.json();
                if (result.message) {
                    alert(result.message);
                    addSubjectForm.reset();
                    loadSubjects();
                } else {
                    alert(result.error);
                }
            };
        }
    });
</script>
