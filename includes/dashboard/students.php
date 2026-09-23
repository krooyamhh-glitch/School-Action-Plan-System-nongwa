<!-- Academic/Admin: Manage Students -->
<?php if ($role === 'admin' || (isset($_SESSION['is_academic']) && $_SESSION['is_academic']) || (isset($is_homeroom_teacher) && $is_homeroom_teacher)): ?>
<div id="manage-students" class="section hidden space-y-6">
    <?php if ($role === 'admin' || (isset($_SESSION['is_academic']) && $_SESSION['is_academic'])): ?>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-lg font-bold mb-4">เพิ่มนักเรียนใหม่</h3>
        <form id="addStudentForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <select id="std_prefix" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none cursor-pointer">
                <option value="">คำนำหน้า</option>
                <option value="เด็กชาย">เด็กชาย</option>
                <option value="เด็กหญิง">เด็กหญิง</option>
                <option value="นาย">นาย</option>
                <option value="นางสาว">นางสาว</option>
            </select>
            <input type="text" id="std_code" placeholder="รหัสประจำตัวนักเรียน" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <input type="text" id="std_national_id" placeholder="เลขบัตรประชาชน (13 หลัก)" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <input type="text" id="std_name" placeholder="ชื่อ-นามสกุล" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <select id="std_level" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none cursor-pointer">
                <option value="">เลือกระดับชั้น</option>
                <option value="ป.1">ป.1</option><option value="ป.2">ป.2</option><option value="ป.3">ป.3</option>
                <option value="ป.4">ป.4</option><option value="ป.5">ป.5</option><option value="ป.6">ป.6</option>
                <option value="ม.1">ม.1</option><option value="ม.2">ม.2</option><option value="ม.3">ม.3</option>
            </select>
            <input type="text" id="std_room" placeholder="ห้อง (เช่น 1)" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none">
            <select id="std_academic_year" required class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none cursor-pointer">
                <!-- จะถูกเติมด้วย JavaScript -->
            </select>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 cursor-pointer transition-all">บันทึก</button>
        </form>
    </div>
    <?php endif; ?>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">รายชื่อนักเรียน</h3>
            <div class="flex gap-2">
                <?php if ($role === 'admin' || (isset($_SESSION['is_academic']) && $_SESSION['is_academic'])): ?>
                <button onclick="downloadStudentTemplate()" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-200 cursor-pointer transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    ดาวน์โหลด Template
                </button>
                <input type="file" id="importExcel" accept=".xlsx, .xls" class="hidden" onchange="handleExcelImport(event)">
                <button onclick="document.getElementById('importExcel').click()" class="bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-700 cursor-pointer transition-all">นำเข้าจาก Excel</button>
                <button onclick="promoteStudents()" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-700 cursor-pointer transition-all">เลื่อนระดับชั้น</button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Student Filters -->
        <div id="studentFilters" class="mb-6 space-y-4 border-b border-slate-100 pb-6">
            <div class="flex gap-4">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">ปีการศึกษา</p>
                    <select id="filter_academic_year" onchange="loadStudents()" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                        <!-- จะถูกเติมด้วย JavaScript -->
                    </select>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">สถานะ</p>
                    <select id="filter_status" onchange="loadStudents()" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                        <option value="studying">กำลังศึกษา</option>
                        <option value="graduated">จบการศึกษา</option>
                        <option value="transferred">ย้ายสถานศึกษา</option>
                        <option value="quit">ลาออก</option>
                    </select>
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">เลือกระดับชั้น</p>
                <div id="studentLevelButtons" class="flex flex-wrap gap-2"></div>
            </div>
            <div id="studentRoomContainer" class="hidden">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">เลือกห้องเรียน</p>
                <div id="studentRoomButtons" class="flex flex-wrap gap-2"></div>
            </div>
        </div>

        <div id="studentsContainer" class="space-y-8">
            <!-- จะถูกเติมด้วย JavaScript แยกตามห้องเรียน -->
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-4xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-slate-800">แก้ไขข้อมูลนักเรียน</h3>
            <button onclick="closeModal('editStudentModal')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="editStudentForm" class="space-y-6">
            <input type="hidden" id="edit_std_id">
            
            <!-- ข้อมูลพื้นฐาน -->
            <div class="space-y-4">
                <h4 class="text-sm font-bold text-blue-600 uppercase tracking-wider border-b border-blue-50 pb-2">ข้อมูลพื้นฐาน</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">คำนำหน้า</label>
                        <select id="edit_std_prefix" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                            <option value="เด็กชาย">เด็กชาย</option>
                            <option value="เด็กหญิง">เด็กหญิง</option>
                            <option value="นาย">นาย</option>
                            <option value="นางสาว">นางสาว</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ชื่อ</label>
                        <input type="text" id="edit_std_name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">นามสกุล</label>
                        <input type="text" id="edit_std_last_name" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">รหัสประจำตัว</label>
                        <input type="text" id="edit_std_code" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">เลขบัตรประชาชน</label>
                        <input type="text" id="edit_std_national_id" required maxlength="13" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">เพศ</label>
                        <select id="edit_std_gender" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                            <option value="">ระบุเพศ</option>
                            <option value="ชาย">ชาย</option>
                            <option value="หญิง">หญิง</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ระดับชั้น</label>
                        <select id="edit_std_level" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                            <option value="ป.1">ป.1</option><option value="ป.2">ป.2</option><option value="ป.3">ป.3</option>
                            <option value="ป.4">ป.4</option><option value="ป.5">ป.5</option><option value="ป.6">ป.6</option>
                            <option value="ม.1">ม.1</option><option value="ม.2">ม.2</option><option value="ม.3">ม.3</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ห้อง</label>
                        <input type="text" id="edit_std_room" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ปีการศึกษา</label>
                        <select id="edit_std_academic_year" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                            <!-- จะถูกเติมด้วย JavaScript -->
                        </select>
                    </div>
                </div>
            </div>

            <!-- ข้อมูลส่วนตัวและสุขภาพ -->
            <div class="space-y-4">
                <h4 class="text-sm font-bold text-blue-600 uppercase tracking-wider border-b border-blue-50 pb-2">ข้อมูลส่วนตัวและสุขภาพ</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">วันเกิด</label>
                        <input type="date" id="edit_std_birthday" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">น้ำหนัก (กก.)</label>
                        <input type="number" step="0.1" id="edit_std_weight" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ส่วนสูง (ซม.)</label>
                        <input type="number" step="0.1" id="edit_std_height" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">กลุ่มเลือด</label>
                        <select id="edit_std_blood_group" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all cursor-pointer">
                            <option value="">ระบุ</option>
                            <option value="A">A</option><option value="B">B</option><option value="AB">AB</option><option value="O">O</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ศาสนา</label>
                        <input type="text" id="edit_std_religion" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">เชื้อชาติ</label>
                        <input type="text" id="edit_std_race" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">สัญชาติ</label>
                        <input type="text" id="edit_std_nationality" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                </div>
            </div>

            <!-- ข้อมูลที่อยู่ -->
            <div class="space-y-4">
                <h4 class="text-sm font-bold text-blue-600 uppercase tracking-wider border-b border-blue-50 pb-2">ข้อมูลที่อยู่</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">บ้านเลขที่</label>
                        <input type="text" id="edit_std_house_no" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">หมู่</label>
                        <input type="text" id="edit_std_moo" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ถนน/ซอย</label>
                        <input type="text" id="edit_std_road_soi" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ตำบล</label>
                        <input type="text" id="edit_std_sub_district" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">อำเภอ</label>
                        <input type="text" id="edit_std_district" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">จังหวัด</label>
                        <input type="text" id="edit_std_province_name" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ความด้อยโอกาส</label>
                        <input type="text" id="edit_std_disadvantage" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                </div>
            </div>

            <!-- ข้อมูลครอบครัว -->
            <div class="space-y-4">
                <h4 class="text-sm font-bold text-blue-600 uppercase tracking-wider border-b border-blue-50 pb-2">ข้อมูลครอบครัว</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ชื่อบิดา</label>
                        <input type="text" id="edit_std_father_name" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">นามสกุลบิดา</label>
                        <input type="text" id="edit_std_father_last_name" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">อาชีพของบิดา</label>
                        <input type="text" id="edit_std_father_occupation" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ชื่อมารดา</label>
                        <input type="text" id="edit_std_mother_name" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">นามสกุลมารดา</label>
                        <input type="text" id="edit_std_mother_last_name" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">อาชีพของมารดา</label>
                        <input type="text" id="edit_std_mother_occupation" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ชื่อผู้ปกครอง</label>
                        <input type="text" id="edit_std_parent_name" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">นามสกุลผู้ปกครอง</label>
                        <input type="text" id="edit_std_parent_last_name" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">อาชีพผู้ปกครอง</label>
                        <input type="text" id="edit_std_parent_occupation" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">ความเกี่ยวข้อง</label>
                        <input type="text" id="edit_std_parent_relationship" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Telegram Chat ID (ผู้ปกครอง)</label>
                        <input type="text" id="edit_std_parent_telegram_id" placeholder="สำหรับแจ้งเตือนการเข้าเรียน" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('editStudentModal')" class="flex-1 px-4 py-3 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer transition-all">ยกเลิก</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-xl font-semibold hover:bg-blue-700 cursor-pointer transition-all shadow-lg shadow-blue-600/20">บันทึกการเปลี่ยนแปลง</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Student Preview Modal -->
<div id="importPreviewModal" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-slate-800">ตรวจสอบข้อมูลก่อนนำเข้า</h3>
                <p id="importSummaryText" class="text-sm text-slate-500 mt-1"></p>
            </div>
            <button onclick="closeModal('importPreviewModal')" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-100">
                        <th class="pb-3 font-medium">รหัส</th>
                        <th class="pb-3 font-medium">เลขบัตรฯ</th>
                        <th class="pb-3 font-medium">คำนำหน้า</th>
                        <th class="pb-3 font-medium">ชื่อ-นามสกุล</th>
                        <th class="pb-3 font-medium">ระดับชั้น</th>
                        <th class="pb-3 font-medium">ห้อง</th>
                        <th class="pb-3 font-medium">ปีการศึกษา</th>
                    </tr>
                </thead>
                <tbody id="importPreviewTableBody"></tbody>
            </table>
        </div>
        <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
            <button onclick="closeModal('importPreviewModal')" class="px-6 py-2 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">ยกเลิก</button>
            <button id="confirmImportBtn" onclick="confirmStudentImport()" class="bg-blue-600 text-white px-8 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-all cursor-pointer">ยืนยันการนำเข้า</button>
        </div>
    </div>
</div>

<script>
    const isAdminOrAcademic = <?php echo ($role === 'admin' || (isset($_SESSION['is_academic']) && $_SESSION['is_academic'])) ? 'true' : 'false'; ?>;

    function downloadStudentTemplate() {
        const data = [
            ['รหัสประจำตัว', 'เลขบัตรประชาชน', 'คำนำหน้า', 'ชื่อ-นามสกุล', 'ระดับชั้น', 'ห้อง', 'ปีการศึกษา'],
            ['67001', '1234567890123', 'เด็กชาย', 'สมชาย ใจดี', 'ป.1', '1', '2567'],
            ['67002', '9876543210987', 'เด็กหญิง', 'สมศรี รักเรียน', 'ป.1', '2', '2567']
        ];
        const worksheet = XLSX.utils.aoa_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Template");
        XLSX.writeFile(workbook, "student_template.xlsx");
    }

    function handleExcelImport(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onerror = (err) => {
            console.error('FileReader error:', err);
            alert('ไม่สามารถอ่านไฟล์ได้');
        };
        reader.onload = (e) => {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                
                // Get raw data to find header row
                const rawData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                
                // Find header row (usually row 3 in DMC, but let's be flexible)
                let headerRowIndex = -1;
                for (let i = 0; i < Math.min(rawData.length, 10); i++) {
                    if (rawData[i].includes('เลขประจำตัวนักเรียน') && rawData[i].includes('ชื่อ')) {
                        headerRowIndex = i;
                        break;
                    }
                }

                if (headerRowIndex === -1) {
                    // Fallback to standard mapping if DMC headers not found
                    const json = XLSX.utils.sheet_to_json(worksheet);
                    const currentAcademicYear = document.getElementById('filter_academic_year')?.value || '2567';
                    
                    studentsToImport = json.map(row => {
                        let rawStudentCode = '';
                        let rawNationalId = '';
                        let rawPrefix = '';
                        let rawFullName = '';
                        let rawLastName = '';
                        let rawLevel = '';
                        let rawRoom = '';
                        let rawAcademicYear = '';

                        // Loop through keys of the row to handle variations and spaces flexibly
                        for (let key in row) {
                            if (!row.hasOwnProperty(key)) continue;
                            const cleanKey = key.replace(/\s+/g, '').trim();
                            const val = String(row[key] || '').trim();

                            if (cleanKey === 'รหัสประจำตัว' || cleanKey === 'student_code' || cleanKey === 'รหัส' || cleanKey === 'เลขประจำตัว' || cleanKey === 'รหัสประจำตัวนักเรียน') {
                                rawStudentCode = val;
                            } else if (cleanKey === 'เลขบัตรประชาชน' || cleanKey === 'national_id' || cleanKey === 'เลขบัตร' || cleanKey === 'เลขประจำตัวประชาชน' || cleanKey === 'เลขบัตรประชาชนนักเรียน') {
                                rawNationalId = val.replace(/[-_\s]/g, ''); // strip hyphens and spaces
                            } else if (cleanKey === 'คำนำหน้า' || cleanKey === 'prefix' || cleanKey === 'คำนำหน้าชื่อ') {
                                rawPrefix = val;
                            } else if (cleanKey === 'ชื่อ-นามสกุล' || cleanKey === 'name' || cleanKey === 'ชื่อ' || cleanKey === 'ชื่อจริง' || cleanKey === 'ชื่อและนามสกุล') {
                                rawFullName = val;
                            } else if (cleanKey === 'นามสกุล' || cleanKey === 'last_name') {
                                rawLastName = val;
                            } else if (cleanKey === 'ระดับชั้น' || cleanKey === 'level' || cleanKey === 'ชั้น') {
                                rawLevel = val;
                            } else if (cleanKey === 'ห้อง' || cleanKey === 'room') {
                                rawRoom = val;
                            } else if (cleanKey === 'ปีการศึกษา' || cleanKey === 'academic_year') {
                                rawAcademicYear = val;
                            }
                        }

                        let student_code = rawStudentCode;
                        let national_id = rawNationalId;
                        let prefix = rawPrefix;
                        let name = rawFullName;
                        let last_name = rawLastName;
                        let level = rawLevel;
                        let room = rawRoom || '1';
                        let academic_year = rawAcademicYear || currentAcademicYear;

                        // Auto split name and last name if they are in a single field and last_name is empty
                        if (!last_name && name.includes(' ')) {
                            const parts = name.split(/\s+/);
                            name = parts[0];
                            last_name = parts.slice(1).join(' ');
                        }

                        return {
                            student_code,
                            national_id,
                            prefix,
                            name,
                            last_name,
                            level,
                            room,
                            academic_year
                        };
                    }).filter(s => s.student_code && s.name && s.level);
                } else {
                    // DMC Mapping
                    const headers = rawData[headerRowIndex];
                    const dataRows = rawData.slice(headerRowIndex + 1);
                    
                    // Helper to get index by header name (handling duplicates like 'เลขประจำตัวนักเรียน')
                    const getIndices = (name) => headers.reduce((acc, h, i) => h === name ? [...acc, i] : acc, []);
                    
                    const nationalIdIdx = getIndices('เลขประจำตัวนักเรียน')[0]; // First one is National ID
                    const studentCodeIdx = getIndices('เลขประจำตัวนักเรียน')[1]; // Second one is Student Code
                    const levelIdx = headers.indexOf('ชั้น');
                    const roomIdx = headers.indexOf('ห้อง');
                    const genderIdx = headers.indexOf('เพศ');
                    const prefixIdx = headers.indexOf('คำนำหน้าชื่อ');
                    const nameIdx = headers.indexOf('ชื่อ');
                    const lastNameIdx = headers.indexOf('นามสกุล');
                    const birthdayIdx = headers.indexOf('วันเกิด');
                    const ageIdx = headers.indexOf('อายุ(ปี)');
                    const weightIdx = headers.indexOf('น้ำหนัก');
                    const heightIdx = headers.indexOf('ส่วนสูง');
                    const bloodIdx = headers.indexOf('กลุ่มเลือด');
                    const religionIdx = headers.indexOf('ศาสนา');
                    const raceIdx = headers.indexOf('เชื้อชาติ');
                    const nationalityIdx = headers.indexOf('สัญชาติ');
                    const houseNoIdx = headers.indexOf('บ้านเลขที่');
                    const mooIdx = headers.indexOf('หมู่');
                    const roadIdx = headers.indexOf('ถนน/ซอย');
                    const subDistrictIdx = headers.indexOf('ตำบล');
                    const districtIdx = headers.indexOf('อำเภอ');
                    const provinceIdx = headers.indexOf('จังหวัด');
                    const parentNameIdx = headers.indexOf('ชื่อผู้ปกครอง');
                    const parentLastNameIdx = headers.indexOf('นามสกุลผู้ปกครอง');
                    const parentOccupationIdx = headers.indexOf('อาชีพของผู้ปกครอง');
                    const parentRelIdx = headers.indexOf('ความเกี่ยวข้องของผู้ปกครองกับนักเรียน');
                    const fatherNameIdx = headers.indexOf('ชื่อบิดา');
                    const fatherLastNameIdx = headers.indexOf('นามสกุลบิดา');
                    const fatherOccupationIdx = headers.indexOf('อาชีพของบิดา');
                    const motherNameIdx = headers.indexOf('ชื่อมารดา');
                    const motherLastNameIdx = headers.indexOf('นามสกุลมารดา');
                    const motherOccupationIdx = headers.indexOf('อาชีพของมารดา');
                    const disadvantageIdx = headers.indexOf('ความด้อยโอกาส');

                    studentsToImport = dataRows.map(row => {
                        if (!row[nameIdx]) return null;
                        return {
                            national_id: String(row[nationalIdIdx] || ''),
                            student_code: String(row[studentCodeIdx] || ''),
                            level: String(row[levelIdx] || ''),
                            room: String(row[roomIdx] || '1'),
                            gender: String(row[genderIdx] || ''),
                            prefix: String(row[prefixIdx] || ''),
                            name: String(row[nameIdx] || ''),
                            last_name: String(row[lastNameIdx] || ''),
                            birthday: String(row[birthdayIdx] || ''),
                            age: row[ageIdx],
                            weight: row[weightIdx],
                            height: row[heightIdx],
                            blood_group: String(row[bloodIdx] || ''),
                            religion: String(row[religionIdx] || ''),
                            race: String(row[raceIdx] || ''),
                            nationality: String(row[nationalityIdx] || ''),
                            house_no: String(row[houseNoIdx] || ''),
                            moo: String(row[mooIdx] || ''),
                            road_soi: String(row[roadIdx] || ''),
                            sub_district: String(row[subDistrictIdx] || ''),
                            district: String(row[districtIdx] || ''),
                            province_name: String(row[provinceIdx] || ''),
                            parent_name: String(row[parentNameIdx] || ''),
                            parent_last_name: String(row[parentLastNameIdx] || ''),
                            parent_occupation: String(row[parentOccupationIdx] || ''),
                            parent_relationship: String(row[parentRelIdx] || ''),
                            father_name: String(row[fatherNameIdx] || ''),
                            father_last_name: String(row[fatherLastNameIdx] || ''),
                            father_occupation: String(row[fatherOccupationIdx] || ''),
                            mother_name: String(row[motherNameIdx] || ''),
                            mother_last_name: String(row[motherLastNameIdx] || ''),
                            mother_occupation: String(row[motherOccupationIdx] || ''),
                            disadvantage: String(row[disadvantageIdx] || ''),
                            academic_year: document.getElementById('filter_academic_year')?.value || '2567'
                        };
                    }).filter(s => s && s.name && s.level);
                }

                if (studentsToImport.length === 0) {
                    alert('ไม่พบข้อมูลนักเรียนที่ถูกต้องในไฟล์ Excel (กรุณาตรวจสอบหัวคอลัมน์)');
                    return;
                }

                renderImportPreview();
                openModal('importPreviewModal');
            } catch (err) {
                console.error('Excel processing error:', err);
                alert('เกิดข้อผิดพลาดในการประมวลผลไฟล์ Excel: ' + err.message);
            }
        };
        reader.readAsArrayBuffer(file);
        event.target.value = ''; // Reset input
    }

    function renderImportPreview() {
        const tbody = document.getElementById('importPreviewTableBody');
        const summary = document.getElementById('importSummaryText');
        
        if (summary) {
            summary.innerText = `พบข้อมูลนักเรียนทั้งหมด ${studentsToImport.length} รายการ`;
        }

        tbody.innerHTML = studentsToImport.map(s => `
            <tr class="border-b border-slate-50">
                <td class="py-2">${s.student_code}</td>
                <td class="py-2">${s.national_id}</td>
                <td class="py-2">${s.prefix}</td>
                <td class="py-2">${s.name} ${s.last_name || ''}</td>
                <td class="py-2">${s.level}</td>
                <td class="py-2">${s.room}</td>
                <td class="py-2">${s.academic_year}</td>
            </tr>
        `).join('');
    }

    async function loadStudents() {
        const year = document.getElementById('filter_academic_year')?.value || '2567';
        const status = document.getElementById('filter_status')?.value || 'studying';
        try {
            const res = await fetch(`api/academic/get_students.php?academic_year=${year}&status=${status}`);
            allStudents = await res.json();
            
            // Extract unique levels
            const levels = [...new Set(allStudents.map(s => s.level))].sort();
            renderStudentLevelButtons(levels);
            
            // If we have a selected level, update rooms and display
            if (selectedStudentLevel && levels.includes(selectedStudentLevel)) {
                filterStudentsByLevel(selectedStudentLevel);
            } else {
                selectedStudentLevel = null;
                selectedStudentRoom = null;
                const container = document.getElementById('studentsContainer');
                if (container) container.innerHTML = '<div class="text-center py-8 text-slate-400">กรุณาเลือกระดับชั้นเพื่อดูข้อมูล</div>';
            }
        } catch (e) {
            console.error('Error in loadStudents:', e);
        }
    }

    function renderStudentLevelButtons(levels) {
        const container = document.getElementById('studentLevelButtons');
        if (!container) return;
        
        container.innerHTML = levels.map(level => `
            <button onclick="filterStudentsByLevel('${level}')" 
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all cursor-pointer ${selectedStudentLevel === level ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}">
                ${level}
            </button>
        `).join('');
    }

    function filterStudentsByLevel(level) {
        selectedStudentLevel = level;
        selectedStudentRoom = null; // Reset room when level changes
        
        // Update level buttons UI
        const levels = [...new Set(allStudents.map(s => s.level))].sort();
        renderStudentLevelButtons(levels);
        
        // Show room container
        const roomContainer = document.getElementById('studentRoomContainer');
        if (roomContainer) roomContainer.classList.remove('hidden');
        
        // Extract unique rooms for this level
        const rooms = [...new Set(allStudents.filter(s => s.level === level).map(s => s.room || '1'))].sort();
        renderStudentRoomButtons(rooms);
        
        const container = document.getElementById('studentsContainer');
        if (container) container.innerHTML = '<div class="text-center py-8 text-slate-400">กรุณาเลือกห้องเรียนเพื่อดูข้อมูล</div>';
    }

    function renderStudentRoomButtons(rooms) {
        const container = document.getElementById('studentRoomButtons');
        if (!container) return;
        
        container.innerHTML = rooms.map(room => `
            <button onclick="filterStudentsByRoom('${room}')" 
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all cursor-pointer ${selectedStudentRoom === room ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}">
                ห้อง ${room}
            </button>
        `).join('');
    }

    function filterStudentsByRoom(room) {
        selectedStudentRoom = room;
        
        // Update room buttons UI
        const rooms = [...new Set(allStudents.filter(s => s.level === selectedStudentLevel).map(s => s.room || '1'))].sort();
        renderStudentRoomButtons(rooms);
        
        const container = document.getElementById('studentsContainer');
        if (!container) return;
        
        const filtered = allStudents.filter(s => s.level === selectedStudentLevel && (s.room || '1') === room);
        
        if (filtered.length === 0) {
            container.innerHTML = '<div class="text-center py-8 text-slate-400">ไม่พบข้อมูลนักเรียนในห้องนี้</div>';
            return;
        }

        container.innerHTML = `
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-1 bg-blue-600 rounded-full"></div>
                        <h4 class="font-bold text-slate-800">ชั้น${selectedStudentLevel} ห้อง ${room} (${filtered.length} คน)</h4>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-slate-500 border-b border-slate-100">
                                <th class="pb-3 font-medium">รหัส</th>
                                <th class="pb-3 font-medium">ชื่อ-นามสกุล</th>
                                <th class="pb-3 font-medium text-right">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${filtered.map(s => `
                                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                                    <td class="py-3 text-slate-600 font-mono">${s.student_code}</td>
                                    <td class="py-3 font-medium text-slate-800">
                                        ${s.prefix || ''}${s.name} ${s.last_name || ''}
                                        ${s.status === 'graduated' ? `<span class="ml-2 text-[10px] bg-amber-100 text-amber-600 px-1.5 py-0.5 rounded-full font-bold">${s.generation || 'ไม่ระบุรุ่น'}</span>` : ''}
                                    </td>
                                    <td class="py-3 text-right flex gap-2 justify-end">
                                        <button onclick='editStudent(${JSON.stringify(s)})' class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-xl transition-all cursor-pointer border border-blue-100 shadow-sm" title="แก้ไข">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        ${isAdminOrAcademic ? `
                                        <button onclick="deleteStudent(${s.id})" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl transition-all cursor-pointer border border-red-100 shadow-sm" title="ลบ">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        ` : ''}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    async function confirmStudentImport() {
        const confirmBtn = document.getElementById('confirmImportBtn');
        if (!confirmBtn) return;
        
        if (!studentsToImport || studentsToImport.length === 0) {
            alert('ไม่พบข้อมูลที่จะนำเข้า กรุณาเลือกไฟล์ใหม่อีกครั้ง');
            return;
        }

        confirmBtn.disabled = true;
        confirmBtn.innerText = 'กำลังนำเข้า...';

        try {
            const res = await fetch('api/academic/import_students.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ students: studentsToImport })
            });
            
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                closeModal('importPreviewModal');
                loadStudents();
            } else {
                alert(result.error || 'เกิดข้อผิดพลาดในการนำเข้า');
            }
        } catch (e) {
            console.error('Error in import:', e);
            alert('เกิดข้อผิดพลาดในการนำเข้าข้อมูล');
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerText = 'ยืนยันการนำเข้า';
        }
    }

    async function clearAllStudents() {
        if (!confirm('!!! คำเตือน: คุณกำลังจะลบข้อมูลนักเรียนทั้งหมดของทุกปีการศึกษา !!!\n\nการดำเนินการนี้จะ:\n1. ลบรายชื่อนักเรียนทั้งหมด\n2. ลบข้อมูลการเข้าเรียน, คะแนน, พฤติกรรม และสุขภาพทั้งหมด\n\n* ข้อมูลครูและรายวิชาจะยังคงอยู่\n\nคุณแน่ใจว่าต้องการล้างข้อมูลเพื่อเริ่มต้นใหม่ใช่หรือไม่?')) {
            return;
        }

        const pass = prompt('กรุณาพิมพ์คำว่า "ยืนยันการลบ" เพื่อดำเนินการต่อ:');
        if (pass !== 'ยืนยันการลบ') {
            alert('คุณพิมพ์ข้อความไม่ถูกต้อง การลบถูกยกเลิก');
            return;
        }

        try {
            const res = await fetch('api/academic/clear_all_students.php', { method: 'POST' });
            const text = await res.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('Server response was not JSON:', text);
                throw new Error('เซิร์ฟเวอร์ตอบกลับผิดพลาด (ข้อมูลไม่ใช่ JSON)');
            }

            if (result.message) {
                alert(result.message);
                if (typeof loadStudents === 'function') loadStudents();
                if (typeof loadAcademicYears === 'function') loadAcademicYears();
            } else {
                alert(result.error || 'เกิดข้อผิดพลาดไม่ทราบสาเหตุ');
            }
        } catch (e) {
            console.error('Error clearing students:', e);
            alert('เกิดข้อผิดพลาด: ' + e.message);
        }
    }

    async function promoteStudents() {
        if (!confirm('ยืนยันการเลื่อนระดับชั้นนักเรียนทั้งหมด? (ป.1 -> ป.2, ป.6 -> จบการศึกษา)')) return;
        const res = await fetch('api/academic/promote_students.php', { method: 'POST' });
        const result = await res.json();
        alert(result.message || result.error);
        loadStudents();
    }

    async function deleteStudent(id) {
        if (!confirm('ยืนยันการลบข้อมูลนักเรียน?')) return;
        const res = await fetch('api/academic/delete_student.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.message) {
            alert(result.message);
            loadStudents();
        } else {
            alert(result.error);
        }
    }

    function editStudent(s) {
        document.getElementById('edit_std_id').value = s.id;
        document.getElementById('edit_std_prefix').value = s.prefix || 'เด็กชาย';
        document.getElementById('edit_std_academic_year').value = s.academic_year || '2567';
        document.getElementById('edit_std_name').value = s.name || '';
        document.getElementById('edit_std_last_name').value = s.last_name || '';
        document.getElementById('edit_std_code').value = s.student_code || '';
        document.getElementById('edit_std_national_id').value = s.national_id || '';
        document.getElementById('edit_std_gender').value = s.gender || '';
        document.getElementById('edit_std_level').value = s.level || '';
        document.getElementById('edit_std_room').value = s.room || '1';
        
        document.getElementById('edit_std_birthday').value = s.birthday || '';
        document.getElementById('edit_std_weight').value = s.weight || '';
        document.getElementById('edit_std_height').value = s.height || '';
        document.getElementById('edit_std_blood_group').value = s.blood_group || '';
        document.getElementById('edit_std_religion').value = s.religion || '';
        document.getElementById('edit_std_race').value = s.race || '';
        document.getElementById('edit_std_nationality').value = s.nationality || '';
        
        document.getElementById('edit_std_house_no').value = s.house_no || '';
        document.getElementById('edit_std_moo').value = s.moo || '';
        document.getElementById('edit_std_road_soi').value = s.road_soi || '';
        document.getElementById('edit_std_sub_district').value = s.sub_district || '';
        document.getElementById('edit_std_district').value = s.district || '';
        document.getElementById('edit_std_province_name').value = s.province_name || '';
        document.getElementById('edit_std_disadvantage').value = s.disadvantage || '';
        
        document.getElementById('edit_std_father_name').value = s.father_name || '';
        document.getElementById('edit_std_father_last_name').value = s.father_last_name || '';
        document.getElementById('edit_std_father_occupation').value = s.father_occupation || '';
        
        document.getElementById('edit_std_mother_name').value = s.mother_name || '';
        document.getElementById('edit_std_mother_last_name').value = s.mother_last_name || '';
        document.getElementById('edit_std_mother_occupation').value = s.mother_occupation || '';
        
        document.getElementById('edit_std_parent_name').value = s.parent_name || '';
        document.getElementById('edit_std_parent_last_name').value = s.parent_last_name || '';
        document.getElementById('edit_std_parent_occupation').value = s.parent_occupation || '';
        document.getElementById('edit_std_parent_relationship').value = s.parent_relationship || '';
        document.getElementById('edit_std_parent_telegram_id').value = s.parent_telegram_id || '';
        
        openModal('editStudentModal');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const addStudentForm = document.getElementById('addStudentForm');
        if (addStudentForm) {
            addStudentForm.onsubmit = async (e) => {
                e.preventDefault();
                const res = await fetch('api/academic/add_student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        student_code: document.getElementById('std_code').value,
                        prefix: document.getElementById('std_prefix').value,
                        national_id: document.getElementById('std_national_id').value,
                        name: document.getElementById('std_name').value,
                        level: document.getElementById('std_level').value,
                        room: document.getElementById('std_room').value,
                        academic_year: document.getElementById('std_academic_year').value
                    })
                });
                const result = await res.json();
                if (result.message) {
                    alert(result.message);
                    addStudentForm.reset();
                    loadStudents();
                } else {
                    alert(result.error);
                }
            };
        }

        const editStudentForm = document.getElementById('editStudentForm');
        if (editStudentForm) {
            editStudentForm.onsubmit = async (e) => {
                e.preventDefault();
                const res = await fetch('api/academic/update_student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: document.getElementById('edit_std_id').value,
                        prefix: document.getElementById('edit_std_prefix').value,
                        name: document.getElementById('edit_std_name').value,
                        last_name: document.getElementById('edit_std_last_name').value,
                        student_code: document.getElementById('edit_std_code').value,
                        national_id: document.getElementById('edit_std_national_id').value,
                        gender: document.getElementById('edit_std_gender').value,
                        level: document.getElementById('edit_std_level').value,
                        room: document.getElementById('edit_std_room').value,
                        academic_year: document.getElementById('edit_std_academic_year').value,
                        
                        birthday: document.getElementById('edit_std_birthday').value,
                        weight: document.getElementById('edit_std_weight').value,
                        height: document.getElementById('edit_std_height').value,
                        blood_group: document.getElementById('edit_std_blood_group').value,
                        religion: document.getElementById('edit_std_religion').value,
                        race: document.getElementById('edit_std_race').value,
                        nationality: document.getElementById('edit_std_nationality').value,
                        
                        house_no: document.getElementById('edit_std_house_no').value,
                        moo: document.getElementById('edit_std_moo').value,
                        road_soi: document.getElementById('edit_std_road_soi').value,
                        sub_district: document.getElementById('edit_std_sub_district').value,
                        district: document.getElementById('edit_std_district').value,
                        province_name: document.getElementById('edit_std_province_name').value,
                        disadvantage: document.getElementById('edit_std_disadvantage').value,
                        
                        father_name: document.getElementById('edit_std_father_name').value,
                        father_last_name: document.getElementById('edit_std_father_last_name').value,
                        father_occupation: document.getElementById('edit_std_father_occupation').value,
                        
                        mother_name: document.getElementById('edit_std_mother_name').value,
                        mother_last_name: document.getElementById('edit_std_mother_last_name').value,
                        mother_occupation: document.getElementById('edit_std_mother_occupation').value,
                        
                        parent_name: document.getElementById('edit_std_parent_name').value,
                        parent_last_name: document.getElementById('edit_std_parent_last_name').value,
                        parent_occupation: document.getElementById('edit_std_parent_occupation').value,
                        parent_relationship: document.getElementById('edit_std_parent_relationship').value,
                        parent_telegram_id: document.getElementById('edit_std_parent_telegram_id').value
                    })
                });
                const result = await res.json();
                if (result.message) {
                    alert(result.message);
                    closeModal('editStudentModal');
                    loadStudents();
                } else {
                    alert(result.error);
                }
            };
        }
    });
</script>
