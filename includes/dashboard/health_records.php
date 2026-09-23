<!-- Health Records Section -->
<div id="record-health" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h3 class="text-xl font-bold text-slate-800">บันทึกน้ำหนัก-ส่วนสูง</h3>
                <p class="text-sm text-slate-500">ติดตามการเจริญเติบโตของนักเรียนรายภาคเรียน</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <select id="health_academic_year" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all text-sm font-bold text-slate-700 cursor-pointer">
                    <!-- Academic years will be loaded here -->
                </select>
                <select id="health_semester" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all text-sm font-bold text-slate-700 cursor-pointer">
                    <option value="1">ภาคเรียนที่ 1</option>
                    <option value="2">ภาคเรียนที่ 2</option>
                </select>
                <select id="health_record_number" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all text-sm font-bold text-slate-700 cursor-pointer">
                    <option value="1">ครั้งที่ 1</option>
                    <option value="2">ครั้งที่ 2</option>
                    <option value="3">ครั้งที่ 3</option>
                    <option value="4">ครั้งที่ 4</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">เลือกห้องเรียนที่รับผิดชอบ</label>
                <div id="health-classroom-list" class="flex flex-wrap gap-2">
                    <!-- Classrooms will be loaded here -->
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">วันที่บันทึก</label>
                <input type="date" id="health_recorded_date" value="<?= date('Y-m-d') ?>" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 transition-all text-sm">
            </div>
        </div>

        <div id="health-records-container" class="hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-500 border-b border-slate-100">
                            <th class="pb-3 font-medium w-16">เลขที่</th>
                            <th class="pb-3 font-medium">ชื่อ-นามสกุล</th>
                            <th class="pb-3 font-medium text-center w-24">น้ำหนัก (กก.)</th>
                            <th class="pb-3 font-medium text-center w-24">ส่วนสูง (ซม.)</th>
                            <th class="pb-3 font-medium text-center">ผลการประเมิน (น./อ. | ส./อ. | น./ส.)</th>
                            <th class="pb-3 font-medium text-center w-20">กราฟ</th>
                        </tr>
                    </thead>
                    <tbody id="health-table-body">
                        <!-- Students will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end pt-6">
                <button onclick="saveHealthRecords()" class="bg-blue-600 text-white px-8 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20 cursor-pointer">บันทึกข้อมูลทั้งหมด</button>
            </div>
        </div>

        <div id="health-empty-state" class="py-12 text-center">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
            </div>
            <h4 class="text-slate-800 font-bold">ยังไม่ได้เลือกห้องเรียน</h4>
            <p class="text-slate-500 text-sm">กรุณาเลือกห้องเรียนด้านบนเพื่อเริ่มบันทึกข้อมูล</p>
        </div>
    </div>
</div>

<!-- Growth Chart Modal -->
<div id="growthChartModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl w-full max-w-4xl p-6 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-bold text-slate-800" id="growth-student-name">กราฟแสดงการเจริญเติบโต</h3>
                <p class="text-sm text-slate-500">เปรียบเทียบน้ำหนักและส่วนสูงตามช่วงเวลา</p>
            </div>
            <button onclick="closeModal('growthChartModal')" class="text-slate-400 hover:text-slate-600 cursor-pointer p-2 hover:bg-slate-100 rounded-full transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto space-y-8">
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <h4 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span> กราฟส่วนสูง (ซม.)
                </h4>
                <div id="height-chart" class="w-full h-64"></div>
            </div>
            
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <h4 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span> กราฟน้ำหนัก (กก.)
                </h4>
                <div id="weight-chart" class="w-full h-64"></div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentHealthClassroom = null;
    let healthStudents = [];

    async function loadHealthClassrooms() {
        const yearEl = document.getElementById('health_academic_year');
        const semesterEl = document.getElementById('health_semester');
        if (!yearEl || !semesterEl) return;

        const year = yearEl.value || '2567';
        const semester = semesterEl.value || 1;
        
        try {
            const res = await fetch(`api/teacher/get_my_ld_classrooms.php?academic_year=${year}&semester=${semester}`);
            const classrooms = await res.json();
            const container = document.getElementById('health-classroom-list');
            if (!container) return;

            if (classrooms.length === 0) {
                container.innerHTML = '<p class="text-sm text-red-500 font-bold italic">ยังไม่มีการกำหนดห้องเรียนที่รับผิดชอบ</p>';
                return;
            }

            container.innerHTML = classrooms.map(c => `
                <button onclick="selectHealthClassroom(${c.id}, '${c.level}/${c.room}')" 
                    id="btn-health-class-${c.id}"
                    class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold transition-all hover:border-blue-500 hover:text-blue-600 cursor-pointer bg-white">
                    ชั้น ${c.level}/${c.room}
                </button>
            `).join('');

            if (currentHealthClassroom) {
                selectHealthClassroom(currentHealthClassroom.id, currentHealthClassroom.name);
            }
        } catch (e) {
            console.error('Error loading health classrooms:', e);
        }
    }

    async function selectHealthClassroom(id, name) {
        currentHealthClassroom = { id, name };
        
        // Update UI
        document.querySelectorAll('[id^="btn-health-class-"]').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-md', 'shadow-blue-600/20');
            btn.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
        });
        
        const activeBtn = document.getElementById(`btn-health-class-${id}`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
            activeBtn.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-md', 'shadow-blue-600/20');
        }

        document.getElementById('health-empty-state').classList.add('hidden');
        document.getElementById('health-records-container').classList.remove('hidden');
        
        loadHealthRecords();
    }

    async function loadHealthRecords() {
        if (!currentHealthClassroom) return;

        const year = document.getElementById('health_academic_year').value;
        const semester = document.getElementById('health_semester').value;
        const recordNum = document.getElementById('health_record_number').value;

        try {
            const res = await fetch(`api/teacher/get_health_records.php?classroom_id=${currentHealthClassroom.id}&academic_year=${year}&semester=${semester}&record_number=${recordNum}`);
            healthStudents = await res.json();
            
            renderHealthTable();
        } catch (e) {
            console.error('Error loading health records:', e);
        }
    }

    function renderHealthTable() {
        const tbody = document.getElementById('health-table-body');
        if (!tbody) return;

        tbody.innerHTML = healthStudents.map((s, index) => {
            const w_a = s.weight_age_result || '-';
            const h_a = s.height_age_result || '-';
            const w_h = s.weight_height_result || '-';
            
            const getBadgeClass = (res) => {
                if (res === 'ตามเกณฑ์' || res === 'สมส่วน') return 'bg-green-100 text-green-700';
                if (res === 'ผอม' || res === 'เตี้ย' || res === 'น้อยกว่าเกณฑ์' || res === 'อ้วน') return 'bg-red-100 text-red-700';
                return 'bg-yellow-100 text-yellow-700';
            };

            return `
                <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                    <td class="py-3 text-slate-600 font-mono text-xs">${index + 1}</td>
                    <td class="py-3 font-medium text-slate-800 text-sm">${s.prefix || ''}${s.name || ''} ${s.last_name || ''}</td>
                    <td class="py-3 text-center">
                        <input type="number" step="0.1" value="${s.weight || ''}" 
                            onchange="updateHealthData(${s.id}, 'weight', this.value)"
                            class="w-20 px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20 text-center text-sm font-bold text-blue-600">
                    </td>
                    <td class="py-3 text-center">
                        <input type="number" step="0.1" value="${s.height || ''}" 
                            onchange="updateHealthData(${s.id}, 'height', this.value)"
                            class="w-20 px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20 text-center text-sm font-bold text-green-600">
                    </td>
                    <td class="py-3 text-center">
                        <div class="flex items-center justify-center gap-1 text-[10px] font-bold">
                            <span class="px-1.5 py-0.5 rounded ${getBadgeClass(w_a)}" title="น้ำหนักตามเกณฑ์อายุ">${w_a}</span>
                            <span class="px-1.5 py-0.5 rounded ${getBadgeClass(h_a)}" title="ส่วนสูงตามเกณฑ์อายุ">${h_a}</span>
                            <span class="px-1.5 py-0.5 rounded ${getBadgeClass(w_h)}" title="น้ำหนักตามเกณฑ์ส่วนสูง">${w_h}</span>
                        </div>
                    </td>
                    <td class="py-3 text-center">
                        <button onclick="viewGrowthChart(${s.id}, '${s.prefix || ''}${s.name || ''} ${s.last_name || ''}')" 
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-all cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function updateHealthData(studentId, field, value) {
        const student = healthStudents.find(s => s.id == studentId);
        if (student) {
            student[field] = parseFloat(value) || 0;
        }
    }

    async function saveHealthRecords() {
        if (!currentHealthClassroom) return;

        const payload = {
            classroom_id: currentHealthClassroom.id,
            academic_year: document.getElementById('health_academic_year').value,
            semester: document.getElementById('health_semester').value,
            record_number: document.getElementById('health_record_number').value,
            recorded_date: document.getElementById('health_recorded_date').value,
            records: healthStudents.map(s => ({
                student_id: s.id,
                weight: s.weight || 0,
                height: s.height || 0
            }))
        };

        try {
            const res = await fetch('api/teacher/save_health_records.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();
            if (result.message) {
                alert(result.message);
                loadHealthRecords();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error('Error saving health records:', e);
        }
    }

    async function viewGrowthChart(studentId, studentName) {
        document.getElementById('growth-student-name').innerText = `กราฟแสดงการเจริญเติบโต - ${studentName}`;
        openModal('growthChartModal');
        
        try {
            const res = await fetch(`api/teacher/get_student_growth.php?student_id=${studentId}`);
            const data = await res.json();
            
            // Wait for modal to be visible and layout to settle
            setTimeout(() => {
                drawChart('height-chart', data, 'height', 'ส่วนสูง (ซม.)', '#3b82f6');
                drawChart('weight-chart', data, 'weight', 'น้ำหนัก (กก.)', '#10b981');
            }, 100);
        } catch (e) {
            console.error('Error loading growth data:', e);
        }
    }

    function drawChart(containerId, data, yField, yLabel, color) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // Clear previous chart
        container.innerHTML = '';
        
        if (data.length === 0) {
            container.innerHTML = '<div class="h-full flex items-center justify-center text-slate-400 text-sm italic">ไม่มีข้อมูลการบันทึก</div>';
            return;
        }

        const margin = { top: 20, right: 30, bottom: 40, left: 50 };
        const width = container.clientWidth - margin.left - margin.right;
        const height = container.clientHeight - margin.top - margin.bottom;

        const svg = d3.select(`#${containerId}`)
            .append('svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

        // X Axis: Time (Academic Year + Semester + Record Number)
        const x = d3.scalePoint()
            .domain(data.map(d => `${d.academic_year}/${d.semester} (${d.record_number})`))
            .range([0, width])
            .padding(0.5);

        svg.append('g')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x))
            .selectAll('text')
            .style('text-anchor', 'end')
            .attr('dx', '-.8em')
            .attr('dy', '.15em')
            .attr('transform', 'rotate(-30)')
            .style('font-size', '10px')
            .style('fill', '#64748b');

        // Y Axis: Value
        const minVal = d3.min(data, d => d[yField]) * 0.9;
        const maxVal = d3.max(data, d => d[yField]) * 1.1;
        
        const y = d3.scaleLinear()
            .domain([minVal, maxVal])
            .range([height, 0]);

        svg.append('g')
            .call(d3.axisLeft(y).ticks(5))
            .selectAll('text')
            .style('font-size', '10px')
            .style('fill', '#64748b');

        // Line
        const line = d3.line()
            .x(d => x(`${d.academic_year}/${d.semester} (${d.record_number})`))
            .y(d => y(d[yField]))
            .curve(d3.curveMonotoneX);

        svg.append('path')
            .datum(data)
            .attr('fill', 'none')
            .attr('stroke', color)
            .attr('stroke-width', 3)
            .attr('d', line);

        // Dots
        svg.selectAll('.dot')
            .data(data)
            .enter()
            .append('circle')
            .attr('class', 'dot')
            .attr('cx', d => x(`${d.academic_year}/${d.semester} (${d.record_number})`))
            .attr('cy', d => y(d[yField]))
            .attr('r', 5)
            .attr('fill', 'white')
            .attr('stroke', color)
            .attr('stroke-width', 2)
            .on('mouseover', function(event, d) {
                d3.select(this).attr('r', 7).attr('fill', color);
                // Simple tooltip
                svg.append('text')
                    .attr('id', 'tooltip')
                    .attr('x', x(`${d.academic_year}/${d.semester} (${d.record_number})`))
                    .attr('y', y(d[yField]) - 10)
                    .attr('text-anchor', 'middle')
                    .attr('font-size', '12px')
                    .attr('font-weight', 'bold')
                    .attr('fill', color)
                    .text(d[yField]);
            })
            .on('mouseout', function() {
                d3.select(this).attr('r', 5).attr('fill', 'white');
                d3.select('#tooltip').remove();
            });
            
        // Labels
        svg.append('text')
            .attr('transform', 'rotate(-90)')
            .attr('y', 0 - margin.left)
            .attr('x', 0 - (height / 2))
            .attr('dy', '1em')
            .style('text-anchor', 'middle')
            .style('font-size', '10px')
            .style('font-weight', 'bold')
            .style('fill', '#94a3b8')
            .text(yLabel);
    }

    // Event Listeners for filters
    document.addEventListener('DOMContentLoaded', () => {
        const yearEl = document.getElementById('health_academic_year');
        if (yearEl) yearEl.addEventListener('change', loadHealthClassrooms);
        const semEl = document.getElementById('health_semester');
        if (semEl) semEl.addEventListener('change', loadHealthClassrooms);
        const recEl = document.getElementById('health_record_number');
        if (recEl) recEl.addEventListener('change', loadHealthRecords);
    });

    // Initial load for academic years
    async function initHealthSection() {
        try {
            const res = await fetch('api/academic/get_academic_years.php');
            const years = await res.json();
            const el = document.getElementById('health_academic_year');
            if (el) {
                // Display current year at top or selected
                const sortedYears = [...years].sort((a, b) => b.year - a.year);
                el.innerHTML = sortedYears.map(y => `<option value="${y.year}" ${y.is_current == 1 ? 'selected' : ''}>ปีการศึกษา ${y.year}</option>`).join('');
                
                // If there's a current year, load classrooms for it
                const currentY = sortedYears.find(y => y.is_current == 1);
                if (currentY) {
                    setTimeout(() => {
                        loadHealthClassrooms();
                    }, 100);
                }
            }
        } catch (e) {
            console.error('Error initializing health section:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', initHealthSection);
</script>

<style>
    #height-chart svg, #weight-chart svg {
        display: block;
        margin: 0 auto;
    }
</style>
