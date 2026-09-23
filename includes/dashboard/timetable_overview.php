<div id="timetable-overview" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h3 class="text-xl font-black text-slate-800">สำรวจตารางสอนภาพรวม</h3>
                <p class="text-sm text-slate-500 font-medium">จัดการติดตามการจัดการเรียนการสอน</p>
            </div>
            
            <!-- Tab Switcher -->
            <div class="flex bg-slate-100 p-1 rounded-2xl">
                <button onclick="switchTTTab('teacher')" id="tab-btn-teacher" class="px-6 py-2 rounded-xl text-sm font-bold transition-all bg-white text-blue-600 shadow-sm">
                    ตารางสอนรายครู
                </button>
                <button onclick="switchTTTab('classroom')" id="tab-btn-classroom" class="px-6 py-2 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-slate-700">
                    ตารางเรียนรายห้อง
                </button>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1" id="selection-label">เลือกครูผู้สอน</label>
                <select id="tt_target_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 appearance-none cursor-pointer">
                    <option value="">กำลังโหลดข้อมูล...</option>
                </select>
            </div>
            <button onclick="loadOverviewTimetable()" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 flex items-center gap-2">
                <i data-lucide="search" class="w-4 h-4"></i>
                แสดงตาราง
            </button>
        </div>
    </div>

    <!-- Timetable Display Area -->
    <div id="tt_overview_container" class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 hidden">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-5">
                <div id="tt_target_icon" class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center border border-blue-100 shadow-inner">
                    <i data-lucide="user" class="w-7 h-7"></i>
                </div>
                <div>
                    <h4 id="tt_target_name" class="text-xl font-black text-slate-800">-</h4>
                    <p id="tt_target_sub" class="text-xs text-slate-500 font-bold uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        <span>-</span>
                    </p>
                </div>
            </div>
            <button onclick="printOverviewTimetable()" class="flex items-center gap-2 px-5 py-2.5 bg-white text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition-all border border-slate-200 shadow-sm no-print">
                <i data-lucide="printer" class="w-4 h-4 text-blue-600"></i> พิมพ์ตารางสอน (PDF)
            </button>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-100">
            <table class="w-full border-collapse table-fixed min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="p-4 border border-slate-200 text-xs font-black text-slate-400 uppercase w-24">วัน / คาบ</th>
                        <?php for($i=1; $i<=8; $i++): ?>
                            <th class="p-4 border border-slate-200 text-xs font-black text-slate-600">คาบที่ <?= $i ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody id="tt_overview_body">
                    <!-- Data rows injected here -->
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="tt_overview_empty" class="bg-white p-24 rounded-3xl shadow-sm border border-slate-200 flex flex-col items-center justify-center text-slate-400 text-center animate-pulse">
        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 shadow-inner">
            <i data-lucide="calendar" class="w-12 h-12 text-slate-200"></i>
        </div>
        <p class="font-black text-lg text-slate-300">กรุณาเลือกข้อมูลและกดปุ่มแสดงตาราง</p>
        <p class="text-sm mt-1">สำรวจและติดตามการสอนของคุณครูหรืองานสอนรายห้องเรียน</p>
    </div>
</div>

<script>
    let overviewTeachers = [];
    let overviewClassrooms = [];
    let currentTTTab = 'teacher';

    async function initTimetableOverview() {
        console.log('Initializing Timetable Overview tabs...');
        try {
            const [tRes, cRes] = await Promise.all([
                fetch('api/academic/get_teachers.php'),
                fetch('api/academic/get_classrooms.php')
            ]);
            
            const tData = await tRes.json();
            const cData = await cRes.json();

            overviewTeachers = Array.isArray(tData) ? tData : [];
            overviewClassrooms = Array.isArray(cData) ? cData : [];
            
            if (!Array.isArray(tData) && tData.error) console.error('T-Error:', tData.error);
            if (!Array.isArray(cData) && cData.error) console.error('C-Error:', cData.error);

            switchTTTab(currentTTTab);
        } catch (e) {
            console.error('Error initTimetableOverview:', e);
        }
    }

    function switchTTTab(tab) {
        currentTTTab = tab;
        const targetSelect = document.getElementById('tt_target_id');
        const label = document.getElementById('selection-label');
        
        // Update Buttons
        const teacherBtn = document.getElementById('tab-btn-teacher');
        const classroomBtn = document.getElementById('tab-btn-classroom');
        
        if (tab === 'teacher') {
            teacherBtn.className = "px-6 py-2 rounded-xl text-sm font-bold transition-all bg-white text-blue-600 shadow-sm";
            classroomBtn.className = "px-6 py-2 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-slate-700";
            label.innerText = 'เลือกครูผู้สอน';
            targetSelect.innerHTML = '<option value="">-- เลือกครู --</option>' + 
                overviewTeachers.map(t => `<option value="${t.id}">${t.name} ${t.last_name}</option>`).join('');
        } else {
            classroomBtn.className = "px-6 py-2 rounded-xl text-sm font-bold transition-all bg-white text-blue-600 shadow-sm";
            teacherBtn.className = "px-6 py-2 rounded-xl text-sm font-bold transition-all text-slate-500 hover:text-slate-700";
            label.innerText = 'เลือกห้องเรียน';
            targetSelect.innerHTML = '<option value="">-- เลือกห้องเรียน --</option>' + 
                overviewClassrooms.map(c => `<option value="${c.id}">${c.level}/${c.room}</option>`).join('');
        }
    }

    async function loadOverviewTimetable() {
        const targetId = document.getElementById('tt_target_id').value;
        const targetNameEl = document.getElementById('tt_target_name');
        const targetSubEl = document.getElementById('tt_target_sub');
        const container = document.getElementById('tt_overview_container');
        const empty = document.getElementById('tt_overview_empty');
        const targetIcon = document.getElementById('tt_target_icon');

        if (!targetId) {
            alert('กรุณาเลือกเป้าหมายก่อน');
            return;
        }

        try {
            let url = `api/academic/get_timetables.php?academic_year=${currentAcademicYear}&semester=${currentSemester}`;
            if (currentTTTab === 'teacher') {
                url += `&teacher_id=${targetId}`;
                const t = overviewTeachers.find(i => i.id == targetId);
                targetNameEl.innerText = `${t.name} ${t.last_name}`;
                targetSubEl.querySelector('span').innerText = `คุณครูผู้สอน • ${t.position || 'ครู'}`;
                targetIcon.innerHTML = `<i data-lucide="user" class="w-7 h-7"></i>`;
            } else {
                url += `&classroom_id=${targetId}`;
                const c = overviewClassrooms.find(i => i.id == targetId);
                targetNameEl.innerText = `ชั้นประถมศึกษาปีที่ ${c.level}/${c.room}`;
                targetSubEl.querySelector('span').innerText = `ตารางเรียนประจำห้องเรียน`;
                targetIcon.innerHTML = `<i data-lucide="home" class="w-7 h-7"></i>`;
            }

            const res = await fetch(url);
            const data = await res.json();
            
            if (data.error) {
                alert('ไม่สามารถโหลดข้อมูลได้: ' + data.error);
                return;
            }

            renderOverviewTimetable(data);
            container.classList.remove('hidden');
            empty.classList.add('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (e) {
            console.error('Error loadOverviewTimetable:', e);
            alert('เกิดข้อผิดพลาดในการโหลดตารางสอน');
        }
    }

    function printOverviewTimetable() {
        const targetId = document.getElementById('tt_target_id').value;
        if (!targetId) return;
        window.open(`api/teacher/print_timetable.php?academic_year=${currentAcademicYear}&semester=${currentSemester}&target_type=${currentTTTab}&target_id=${targetId}`, '_blank');
    }

    function renderOverviewTimetable(timetable) {
        const tbody = document.getElementById('tt_overview_body');
        const days = [
            { id: 1, name: 'จันทร์', class: 'bg-yellow-50 text-yellow-700' },
            { id: 2, name: 'อังคาร', class: 'bg-pink-50 text-pink-700' },
            { id: 3, name: 'พุธ', class: 'bg-emerald-50 text-emerald-700' },
            { id: 4, name: 'พฤหัสบดี', class: 'bg-orange-50 text-orange-700' },
            { id: 5, name: 'ศุกร์', class: 'bg-sky-50 text-sky-700' }
        ];

        const getSubjectColor = (slot) => {
            if (!slot) return 'bg-white border-slate-100';
            if (slot.activity_type) {
                const actKey = slot.activity_type.toLowerCase();
                const actColors = {
                    'lunch': 'bg-orange-50 text-orange-700 border-orange-100',
                    'scouts': 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    'scout': 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    'club': 'bg-purple-50 text-purple-700 border-purple-100',
                    'homeroom': 'bg-indigo-50 text-indigo-700 border-indigo-100',
                    'guidance': 'bg-sky-50 text-sky-700 border-sky-100',
                    'reducing_time': 'bg-amber-50 text-amber-700 border-amber-100',
                    'social': 'bg-teal-50 text-teal-700 border-teal-100',
                    'prayer': 'bg-rose-50 text-rose-700 border-rose-100'
                };
                return actColors[actKey] || 'bg-slate-50 text-slate-700 border-slate-100';
            }
            
            const palette = [
                'bg-blue-50 text-blue-700 border-blue-100',
                'bg-indigo-50 text-indigo-700 border-indigo-100',
                'bg-cyan-50 text-cyan-700 border-cyan-100',
                'bg-teal-50 text-teal-700 border-teal-100',
                'bg-emerald-50 text-emerald-700 border-emerald-100',
                'bg-violet-50 text-violet-700 border-violet-100',
                'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-100',
                'bg-pink-50 text-pink-700 border-pink-100',
                'bg-rose-50 text-rose-700 border-rose-100',
                'bg-amber-50 text-amber-700 border-amber-100',
                'bg-orange-50 text-orange-700 border-orange-100',
                'bg-lime-50 text-lime-700 border-lime-100',
                'bg-sky-50 text-sky-700 border-sky-100',
                'bg-yellow-50 text-yellow-700 border-yellow-100',
                'bg-slate-100 text-slate-700 border-slate-200'
            ];
            
            const code = slot.subject_code || '';
            let hash = 0;
            for (let i = 0; i < code.length; i++) {
                hash = ((hash << 5) - hash) + code.charCodeAt(i);
                hash |= 0;
            }
            const index = Math.abs(hash) % palette.length;
            return palette[index];
        };

        tbody.innerHTML = days.map(day => `
            <tr>
                <td class="p-3 border border-slate-200 font-black text-xs text-center ${day.class}">${day.name}</td>
                ${Array.from({length: 8}, (_, i) => i + 1).map(period => {
                    let slots = timetable.filter(t => t.day_of_week == day.id && t.period_number == period);
                    
                    // Deduplicate activity slots for classroom view
                    if (currentTTTab === 'classroom') {
                        const seenActivities = new Set();
                        slots = slots.filter(s => {
                            if (s.activity_type) {
                                const actType = s.activity_type.toLowerCase();
                                if (seenActivities.has(actType)) {
                                    return false;
                                }
                                seenActivities.add(actType);
                            }
                            return true;
                        });
                    }

                    const slot = slots[0];
                    const colorClass = getSubjectColor(slot);
                    
                    let innerContent = '';
                    if (slots.length === 0) {
                        innerContent = '<span class="text-[10px] text-slate-300 italic font-medium">ว่าง</span>';
                    } else {
                        innerContent = slots.map(s => {
                            const isActivity = s && !!s.activity_type;
                            const singleLineActs = ['scouts', 'scout', 'club', 'guidance', 'prayer'];
                            const isSingleLine = isActivity && singleLineActs.includes(s.activity_type.toLowerCase());
                            const isLunchSlot = isActivity && s.activity_type.toLowerCase() === 'lunch';
                            
                            if (isSingleLine) {
                                return `<div class="text-[10px] font-black text-blue-700 leading-none py-0.5">${s.subject_code}</div>`;
                            } else {
                                // Hide teacher's name for activities in classroom view
                                let metaText = '';
                                if (currentTTTab === 'teacher') {
                                    metaText = `${s.level}/${s.room}`;
                                } else {
                                    metaText = isActivity ? '' : `${s.teacher_name}`;
                                }

                                return `
                                    <div class="py-0.5 border-b border-dashed border-slate-200/50 last:border-0">
                                        <div class="text-[9px] font-black leading-none">${s.subject_code || 'กิจกรรม'}</div>
                                        <div class="text-[8px] font-medium opacity-80 truncate leading-none min-h-[10px] my-0.5">${s.subject_name || ''}</div>
                                        ${metaText ? `<div class="text-[8px] font-black opacity-60 leading-none">${metaText}</div>` : ''}
                                    </div>
                                `;
                            }
                        }).join('');
                    }
                    
                    return `
                        <td class="p-2 border transition-all text-center relative min-h-[85px] ${colorClass}">
                            <div class="h-full w-full min-h-[65px] flex flex-col justify-center gap-0.5">
                                ${innerContent}
                            </div>
                        </td>
                    `;
                }).join('')}
            </tr>
        `).join('');
    }
</script>
