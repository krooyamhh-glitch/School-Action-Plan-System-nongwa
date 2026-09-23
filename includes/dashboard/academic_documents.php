<!-- Academic: Academic Documents -->
<?php if ($role === 'admin' || (isset($_SESSION['is_academic']) && $_SESSION['is_academic'])): ?>
<div id="academic-documents" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-800">เอกสารวิชาการ</h3>
                <p class="text-sm text-slate-500">ออกหนังสือรับรองและแบบฟอร์มต่างๆ สำหรับนักเรียน</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Document Selection -->
            <div class="space-y-4">
                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider">1. เลือกประเภทเอกสาร</h4>
                <div class="grid grid-cols-1 gap-3">
                    <button onclick="selectDocType('cert_performance')" id="btn-doc-cert_performance" class="doc-type-btn flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm group-hover:text-blue-600">
                                <i data-lucide="award" class="w-5 h-5"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-700 group-hover:text-blue-700">ใบรับรองผลการเรียน</p>
                                <p class="text-[10px] text-slate-400">หนังสือรับรองผลการเรียนรายบุคคล</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-blue-400"></i>
                    </button>

                    <button onclick="selectDocType('transfer_request')" id="btn-doc-transfer_request" class="doc-type-btn flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm group-hover:text-blue-600">
                                <i data-lucide="move" class="w-5 h-5"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-700 group-hover:text-blue-700">คำร้องขอย้ายนักเรียน (บค.๑๙)</p>
                                <p class="text-[10px] text-slate-400">แบบคำร้องขอย้ายนักเรียนต่อสถานศึกษา</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-blue-400"></i>
                    </button>

                    <button onclick="selectDocType('transfer_letter')" id="btn-doc-transfer_letter" class="doc-type-btn flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm group-hover:text-blue-600">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-700 group-hover:text-blue-700">หนังสือส่งนักเรียน (บค.๒๐)</p>
                                <p class="text-[10px] text-slate-400">หนังสือส่งนักเรียนไปเข้าเรียนในสถานศึกษาที่ขอย้ายเข้า</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-blue-400"></i>
                    </button>

                    <button onclick="selectDocType('remove_request')" id="btn-doc-remove_request" class="doc-type-btn flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm group-hover:text-blue-600">
                                <i data-lucide="user-minus" class="w-5 h-5"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-700 group-hover:text-blue-700">ขออนุญาตจำหน่ายนักเรียน (บค.๒๑)</p>
                                <p class="text-[10px] text-slate-400">หนังสือขออนุญาตจำหน่ายนักเรียนออกจากทะเบียน</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-blue-400"></i>
                    </button>

                    <button onclick="selectDocType('no_existence')" id="btn-doc-no_existence" class="doc-type-btn flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm group-hover:text-blue-600">
                                <i data-lucide="user-x" class="w-5 h-5"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-700 group-hover:text-blue-700">หนังสือรับรองไม่มีตัวตน (บค.๒๗)</p>
                                <p class="text-[10px] text-slate-400">หนังสือรับรองการไม่มีตัวตนของนักเรียนในพื้นที่</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-blue-400"></i>
                    </button>

                    <button onclick="selectDocType('identity_cert')" id="btn-doc-identity_cert" class="doc-type-btn flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm group-hover:text-blue-600">
                                <i data-lucide="user-check" class="w-5 h-5"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-slate-700 group-hover:text-blue-700">หนังสือรับรองตัวตน</p>
                                <p class="text-[10px] text-slate-400">แบบฟอร์มหนังสือรับรองตัวตน (กรอกมือ)</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-blue-400"></i>
                    </button>
                </div>
            </div>

            <!-- Student Selection -->
            <div class="space-y-4">
                <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider">2. เลือกนักเรียน</h4>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ระดับชั้น</label>
                        <select id="doc_filter_level" onchange="loadDocRooms()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer text-sm">
                            <option value="">เลือกระดับชั้น</option>
                            <option value="ป.1">ป.1</option><option value="ป.2">ป.2</option><option value="ป.3">ป.3</option>
                            <option value="ป.4">ป.4</option><option value="ป.5">ป.5</option><option value="ป.6">ป.6</option>
                            <option value="ม.1">ม.1</option><option value="ม.2">ม.2</option><option value="ม.3">ม.3</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ห้อง</label>
                        <select id="doc_filter_room" onchange="loadDocStudents()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer text-sm">
                            <option value="">เลือกห้อง</option>
                        </select>
                    </div>
                </div>
                <div id="doc-student-list" class="max-h-[350px] overflow-y-auto border border-slate-100 rounded-2xl divide-y divide-slate-50 bg-slate-50/30">
                    <div class="p-8 text-center text-slate-400">
                        <p class="text-sm">กรุณาเลือกระดับชั้นและห้องเรียน</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Form (Hidden until student and doc type selected) -->
        <div id="doc-form-container" class="hidden mt-8 pt-8 border-t border-slate-100 space-y-6">
            <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100">
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-4">
                        <div id="selected-student-avatar" class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-xl font-bold text-blue-600 shadow-sm border border-blue-200">
                            -
                        </div>
                        <div>
                            <h4 id="selected-student-name" class="text-lg font-bold text-slate-800">-</h4>
                            <p id="selected-student-info" class="text-sm text-slate-500">ชั้น - ห้อง - | รหัส: -</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">ประเภทเอกสาร</p>
                        <p id="selected-doc-title" class="text-sm font-bold text-slate-700">-</p>
                    </div>
                </div>

                <form id="docDetailsForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Dynamic fields will be added here -->
                </form>

                <div class="mt-8 flex justify-end gap-3">
                    <button onclick="resetDocSelection()" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-2xl font-bold hover:bg-slate-50 transition-all cursor-pointer">ยกเลิก</button>
                    <button onclick="generateAcademicDoc()" class="px-8 py-3 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all cursor-pointer flex items-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        พิมพ์เอกสาร
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    let selectedDocType = null;
    let selectedStudent = null;
    let docStudents = [];

    function initAcademicDocuments() {
        console.log('Initializing Academic Documents');
        resetDocSelection();
    }

    function selectDocType(type) {
        if (type === 'transfer_request' || type === 'identity_cert') {
            const url = `reports/academic_doc.php?type=${type}`;
            window.open(url, '_blank');
            return;
        }

        selectedDocType = type;
        document.querySelectorAll('.doc-type-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-500/20');
            if (btn.id === `btn-doc-${type}`) {
                btn.classList.add('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-500/20');
            }
        });
        
        const titles = {
            'cert_performance': 'ใบรับรองผลการเรียน',
            'transfer_request': 'คำร้องขอย้ายนักเรียน (บค.๑๙)',
            'transfer_letter': 'หนังสือส่งนักเรียน (บค.๒๐)',
            'remove_request': 'ขออนุญาตจำหน่ายนักเรียน (บค.๒๑)',
            'no_existence': 'หนังสือรับรองไม่มีตัวตน (บค.๒๗)',
            'identity_cert': 'หนังสือรับรองตัวตน'
        };
        document.getElementById('selected-doc-title').innerText = titles[type];
        
        updateDocFormFields();
        checkDocFormVisibility();
    }

    async function loadDocRooms() {
        const level = document.getElementById('doc_filter_level').value;
        const roomSelect = document.getElementById('doc_filter_room');
        roomSelect.innerHTML = '<option value="">เลือกห้อง</option>';
        
        if (!level) {
            document.getElementById('doc-student-list').innerHTML = '<div class="p-8 text-center text-slate-400 text-sm">กรุณาเลือกระดับชั้นและห้องเรียน</div>';
            return;
        }

        try {
            const res = await fetch(`api/teacher/get_rooms.php?level=${encodeURIComponent(level)}`);
            const rooms = await res.json();
            rooms.forEach(room => {
                const opt = document.createElement('option');
                opt.value = room;
                opt.textContent = `ห้อง ${room}`;
                roomSelect.appendChild(opt);
            });
            
            // Auto load students if only one room
            if (rooms.length === 1) {
                roomSelect.value = rooms[0];
                loadDocStudents();
            }
        } catch (e) {
            console.error('Error loading rooms:', e);
        }
    }

    async function loadDocStudents() {
        const level = document.getElementById('doc_filter_level').value;
        const room = document.getElementById('doc_filter_room').value;
        const container = document.getElementById('doc-student-list');
        
        if (!level) return;

        container.innerHTML = '<div class="p-8 text-center text-slate-400 text-sm">กำลังโหลดรายชื่อ...</div>';

        try {
            const res = await fetch(`api/admin/get_students_by_class.php?level=${encodeURIComponent(level)}&room=${encodeURIComponent(room)}`);
            docStudents = await res.json();
            renderDocStudentList();
        } catch (e) {
            console.error('Error loading students:', e);
            container.innerHTML = '<div class="p-8 text-center text-red-400 text-sm">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        }
    }

    function renderDocStudentList() {
        const container = document.getElementById('doc-student-list');
        if (docStudents.length === 0) {
            container.innerHTML = '<div class="p-8 text-center text-slate-400 text-sm">ไม่พบข้อมูลนักเรียน</div>';
            return;
        }

        container.innerHTML = docStudents.map(s => `
            <div onclick="selectDocStudent(${JSON.stringify(s).replace(/"/g, '&quot;')})" class="p-4 hover:bg-white cursor-pointer transition-all flex items-center justify-between group border-l-4 border-transparent hover:border-blue-500">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center font-bold text-slate-500 group-hover:text-blue-600 shadow-sm transition-all">
                        ${s.name.charAt(0)}
                    </div>
                    <div>
                        <p class="font-bold text-slate-700 group-hover:text-blue-700">${s.prefix}${s.name} ${s.last_name || ''}</p>
                        <p class="text-xs text-slate-400">รหัส: ${s.student_code} | เลขบัตร: ${s.national_id}</p>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-blue-400 transition-all"></i>
            </div>
        `).join('');
        lucide.createIcons();
    }

    function selectDocStudent(student) {
        selectedStudent = student;
        document.getElementById('selected-student-name').innerText = `${student.prefix}${student.name} ${student.last_name || ''}`;
        document.getElementById('selected-student-info').innerText = `ชั้น ${student.level}/${student.room} | รหัส: ${student.student_code}`;
        document.getElementById('selected-student-avatar').innerText = student.name.charAt(0);
        
        updateDocFormFields();
        checkDocFormVisibility();
    }

    function checkDocFormVisibility() {
        const container = document.getElementById('doc-form-container');
        if (selectedDocType && selectedStudent) {
            container.classList.remove('hidden');
            container.scrollIntoView({ behavior: 'smooth' });
        } else {
            container.classList.add('hidden');
        }
    }

    function updateDocFormFields() {
        const form = document.getElementById('docDetailsForm');
        form.innerHTML = '';
        
        if (!selectedDocType || !selectedStudent) return;

        if (selectedDocType === 'cert_performance') {
            form.innerHTML = `
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">ปีการศึกษาที่รับรอง</label>
                    <input type="text" id="doc_year" value="${currentAcademicYear}" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">ภาคเรียน</label>
                    <select id="doc_semester" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                        <option value="1">ภาคเรียนที่ 1</option>
                        <option value="2">ภาคเรียนที่ 2</option>
                        <option value="annual">รวมทั้งปีการศึกษา</option>
                    </select>
                </div>
            `;
        } else if (selectedDocType === 'transfer_request') {
            const parentName = selectedStudent.father_name ? `${selectedStudent.father_name} ${selectedStudent.father_last_name || ''}` : 
                             (selectedStudent.mother_name ? `${selectedStudent.mother_name} ${selectedStudent.mother_last_name || ''}` : '');
            
            form.innerHTML = `
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อผู้ปกครอง</label>
                    <input type="text" id="doc_parent_name" value="${parentName}" placeholder="ระบุชื่อผู้ปกครอง" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">ย้ายไปโรงเรียน</label>
                    <input type="text" id="doc_dest_school" placeholder="ระบุชื่อโรงเรียนปลายทาง" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">เหตุผลที่ขอย้าย</label>
                    <input type="text" id="doc_reason" placeholder="เช่น ย้ายที่อยู่อาศัยตามผู้ปกครอง" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
            `;
        } else if (selectedDocType === 'transfer_letter') {
            form.innerHTML = `
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">เลขที่หนังสือ</label>
                    <input type="text" id="doc_no" placeholder="เช่น ศธ 04..." class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">โรงเรียนปลายทาง</label>
                    <input type="text" id="doc_dest_school" placeholder="ระบุชื่อโรงเรียนปลายทาง" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
            `;
        } else if (selectedDocType === 'remove_request') {
            form.innerHTML = `
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">เลขที่หนังสือ</label>
                    <input type="text" id="doc_no" placeholder="เช่น ศธ 04..." class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">สาเหตุการจำหน่าย</label>
                    <select id="doc_reason" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                        <option value="ย้ายสถานศึกษา">ย้ายสถานศึกษา</option>
                        <option value="ถึงแก่กรรม">ถึงแก่กรรม</option>
                        <option value="ไม่มีตัวตนอยู่ในพื้นที่">ไม่มีตัวตนอยู่ในพื้นที่</option>
                        <option value="อายุพ้นเกณฑ์การศึกษาภาคบังคับ">อายุพ้นเกณฑ์การศึกษาภาคบังคับ</option>
                    </select>
                </div>
            `;
        } else if (selectedDocType === 'no_existence') {
            const location = `บ้านเลขที่ ${selectedStudent.house_no || ''} หมู่ที่ ${selectedStudent.moo || ''} ต.${selectedStudent.sub_district || ''} อ.${selectedStudent.district || ''} จ.${selectedStudent.province_name || ''}`;
            form.innerHTML = `
                <div class="space-y-2 md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">เขียนที่ (ที่อยู่ปัจจุบัน)</label>
                    <input type="text" id="doc_location" value="${location}" placeholder="ระบุสถานที่เขียนหนังสือ" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
            `;
        }
    }

    function resetDocSelection() {
        selectedDocType = null;
        selectedStudent = null;
        document.querySelectorAll('.doc-type-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-500/20');
        });
        document.getElementById('doc_filter_level').value = '';
        document.getElementById('doc_filter_room').innerHTML = '<option value="">เลือกห้อง</option>';
        document.getElementById('doc-student-list').innerHTML = '<div class="p-8 text-center text-slate-400 text-sm">กรุณาเลือกระดับชั้นและห้องเรียน</div>';
        document.getElementById('doc-form-container').classList.add('hidden');
    }

    function generateAcademicDoc() {
        if (!selectedDocType || !selectedStudent) {
            alert('กรุณาเลือกประเภทเอกสารและนักเรียน');
            return;
        }

        const params = new URLSearchParams({
            type: selectedDocType,
            student_id: selectedStudent.id
        });

        // Add form fields to params
        const form = document.getElementById('docDetailsForm');
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            params.append(input.id.replace('doc_', ''), input.value);
        });

        const url = `reports/academic_doc.php?${params.toString()}`;
        window.open(url, '_blank');
    }
</script>
