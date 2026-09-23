<!-- Academic Achievement Analytics Section -->
<div id="academic-achievement" class="section hidden space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 box-border p-1">
        <!-- Filter Controls -->
        <div class="col-span-full bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">ปีการศึกษา</label>
                <select id="achievement-year-select" class="w-full bg-slate-50 border-none rounded-xl px-4 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-500">
                    <!-- Academic years will be loaded here -->
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">เทอม</label>
                <select id="achievement-semester-select" class="w-full bg-slate-50 border-none rounded-xl px-4 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-500">
                    <option value="1">ภาคเรียนที่ 1</option>
                    <option value="2">ภาคเรียนที่ 2</option>
                </select>
            </div>
            <button onclick="loadAcademicAchievement()" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-bold text-sm shadow-lg shadow-blue-200">
                ประมวลผลข้อมูล
            </button>
        </div>

        <!-- Charts Container -->
        <div class="col-span-full">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-8 w-1.5 bg-blue-600 rounded-full"></div>
                <h3 class="text-xl font-black text-slate-800">สรุปผลสัมฤทธิ์ทางการเรียน <span id="achievement-info-display" class="text-blue-600"></span></h3>
            </div>
        </div>

        <div class="col-span-full grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- GPA Bar Chart -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                <h4 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="bar-chart" class="w-5 h-5 text-blue-500"></i>
                    คะแนนเฉลี่ยผลสัมฤทธิ์ทางการเรียน (GPA) แยกตามระดับชั้น
                </h4>
                <div class="h-[400px] w-full">
                    <canvas id="gpaChart"></canvas>
                </div>
            </div>

            <!-- Grade Distribution Chart -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                <h4 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-purple-500"></i>
                    สัดส่วนผลการเรียน (Grade Distribution)
                </h4>
                <div class="h-[400px] w-full">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Grade Breakdown Table -->
        <div class="col-span-full bg-white p-8 rounded-3xl shadow-sm border border-slate-200 overflow-x-auto">
            <h4 class="text-lg font-black text-slate-800 mb-6">ตารางสรุปผลสัมฤทธิ์ทางการเรียนรายระดับชั้น</h4>
            <table class="w-full text-center border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">ระดับชั้น</th>
                        <th class="px-2 py-4 text-xs font-bold text-blue-600">GPA เฉลี่ย</th>
                        <th class="px-2 py-4 text-xs font-bold text-slate-500">4</th>
                        <th class="px-2 py-4 text-xs font-bold text-slate-500">3.5</th>
                        <th class="px-2 py-4 text-xs font-bold text-slate-500">3</th>
                        <th class="px-2 py-4 text-xs font-bold text-slate-500">2.5</th>
                        <th class="px-2 py-4 text-xs font-bold text-slate-500">2</th>
                        <th class="px-2 py-4 text-xs font-bold text-slate-500">1.5</th>
                        <th class="px-2 py-4 text-xs font-bold text-slate-500">1</th>
                        <th class="px-2 py-4 text-xs font-bold text-red-500">0</th>
                        <th class="px-2 py-4 text-xs font-bold text-orange-500">ร/มส/มผ</th>
                    </tr>
                </thead>
                <tbody id="achievement-table-body" class="divide-y divide-slate-100">
                    <!-- Data here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let gpaChart = null;
let distChart = null;

async function loadAcademicAchievement() {
    const yearSelect = document.getElementById('achievement-year-select');
    const semesterSelect = document.getElementById('achievement-semester-select');
    const year = yearSelect.value;
    const semester = semesterSelect.value;
    
    if (!year) return;

    // Update display text
    const yearText = yearSelect.options[yearSelect.selectedIndex].text;
    const semesterText = semesterSelect.options[semesterSelect.selectedIndex].text;
    document.getElementById('achievement-info-display').innerText = `${yearText} ${semesterText}`;

    try {
        const response = await fetch(`api/admin/get_academic_achievement_stats.php?academic_year=${year}&semester=${semester}`);
        const data = await response.json();

        if (data.error) {
            console.error(data.error);
            return;
        }

        renderGPAChart(data.averages);
        renderDistributionChart(data.distribution);
        renderAchievementTable(data.averages, data.distribution);

    } catch (error) {
        console.error('Error loading achievement stats:', error);
    }
}

function renderGPAChart(averages) {
    const ctx = document.getElementById('gpaChart').getContext('2d');
    if (gpaChart) gpaChart.destroy();

    const labels = averages.map(a => a.level);
    const values = averages.map(a => parseFloat(a.avg_gpa || 0).toFixed(2));

    gpaChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'GPA เฉลี่ย',
                data: values,
                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 4.0,
                    ticks: { font: { weight: 'bold' } }
                }
            }
        }
    });
}

function renderDistributionChart(distribution) {
    const ctx = document.getElementById('distributionChart').getContext('2d');
    if (distChart) distChart.destroy();

    // Sum up counts across all levels
    const totals = { '4': 0, '3.5': 0, '3': 0, '2.5': 0, '2': 0, '1.5': 0, '1': 0, '0': 0, 'others': 0 };
    
    Object.values(distribution).forEach(levelDist => {
        Object.entries(levelDist).forEach(([grade, count]) => {
            if (totals.hasOwnProperty(grade)) {
                totals[grade] += count;
            } else {
                totals['others'] += count;
            }
        });
    });

    const labels = ['เกรด 4', 'เกรด 3.5', 'เกรด 3', 'เกรด 2.5', 'เกรด 2', 'เกรด 1.5', 'เกรด 1', 'เกรด 0', 'อื่นๆ (ร/มส)'];
    const values = [totals['4'], totals['3.5'], totals['3'], totals['2.5'], totals['2'], totals['1.5'], totals['1'], totals['0'], totals['others']];
    const colors = [
        '#22c55e', '#4ade80', '#84cc16', '#a3e635', 
        '#eab308', '#facc15', '#f97316', '#ef4444', 
        '#94a3b8'
    ];

    distChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 0,
                hoverOffset: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { family: 'Sarabun', size: 12 }
                    }
                }
            }
        }
    });
}

function renderAchievementTable(averages, distribution) {
    const tbody = document.getElementById('achievement-table-body');
    tbody.innerHTML = '';

    const levels = averages.map(a => a.level).sort();
    
    levels.forEach(level => {
        const avgData = averages.find(a => a.level === level);
        const distData = distribution[level] || {};
        
        const others = Object.entries(distData)
            .filter(([k]) => !['4','3.5','3','2.5','2','1.5','1','0'].includes(k))
            .reduce((sum, [_, v]) => sum + v, 0);

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50 transition-colors';
        tr.innerHTML = `
            <td class="px-4 py-4 text-sm font-bold text-slate-700">${level}</td>
            <td class="px-2 py-4 text-sm font-black text-blue-600">${parseFloat(avgData.avg_gpa || 0).toFixed(2)}</td>
            <td class="px-2 py-4 text-xs font-medium text-slate-600">${distData['4'] || 0}</td>
            <td class="px-2 py-4 text-xs font-medium text-slate-600">${distData['3.5'] || 0}</td>
            <td class="px-2 py-4 text-xs font-medium text-slate-600">${distData['3'] || 0}</td>
            <td class="px-2 py-4 text-xs font-medium text-slate-600">${distData['2.5'] || 0}</td>
            <td class="px-2 py-4 text-xs font-medium text-slate-600">${distData['2'] || 0}</td>
            <td class="px-2 py-4 text-xs font-medium text-slate-600">${distData['1.5'] || 0}</td>
            <td class="px-2 py-4 text-xs font-medium text-slate-600">${distData['1'] || 0}</td>
            <td class="px-2 py-4 text-xs font-bold text-red-500">${distData['0'] || 0}</td>
            <td class="px-2 py-4 text-xs font-bold text-orange-500">${others}</td>
        `;
        tbody.appendChild(tr);
    });
}

async function loadAchievementFilterYears() {
    try {
        const res = await fetch('api/admin/get_grading_progress.php?action=get_years');
        const years = await res.json();
        const select = document.getElementById('achievement-year-select');
        select.innerHTML = '';
        years.forEach(year => {
            const opt = document.createElement('option');
            opt.value = year.year;
            opt.textContent = 'ปีการศึกษา ' + year.year;
            if (year.is_current == 1) opt.selected = true;
            select.appendChild(opt);
        });
    } catch (err) {
        console.error('Failed to load years', err);
    }
}

// Initial Call
document.addEventListener('DOMContentLoaded', () => {
    // Note: Since scripts are in includes, we might need a way to trigger this
    // It's already handled by showSection and loadAchievementFilterYears
});

// Expose to global for showSection
window.loadAcademicAchievement = loadAcademicAchievement;
window.initAcademicAchievement = async () => {
    await loadAchievementFilterYears();
    loadAcademicAchievement();
};
</script>
