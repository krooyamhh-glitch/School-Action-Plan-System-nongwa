// School Action Plan OS - Frontend Logic
let appData = null;
let currentUser = null;
let selectedYearId = 1;
let allocationChartInstance = null;
let deptComparisonChartInstance = null;

const DEPT_NAMES = {
    academic: 'กลุ่มบริหารวิชาการ',
    budget: 'กลุ่มบริหารงบประมาณและสินทรัพย์',
    personnel: 'กลุ่มบริหารงานบุคคล',
    general: 'กลุ่มบริหารทั่วไป',
    reserve: 'งบสำรองจ่าย/ส่วนกลาง',
    central: 'ฝ่ายบริหารส่วนกลาง'
};

const ROLE_TITLES = {
    super_admin: 'ผู้ดูแลระบบสูงสุด (Super Admin)',
    school_admin: 'Admin ดูแลระบบโรงเรียน',
    director: 'ผู้อำนวยการโรงเรียน',
    deputy_director: 'รองผู้อำนวยการโรงเรียน',
    plan_officer: 'เจ้าหน้าที่แผนงาน/งบประมาณ',
    planofficer: 'เจ้าหน้าที่แผนงาน/งบประมาณ',
    department_head: 'หัวหน้ากลุ่มงาน',
    head_academic: 'หัวหน้ากลุ่มบริหารวิชาการ',
    teacher: 'ครู/ผู้รับผิดชอบโครงการ',
    teacher_somchai: 'ครู/ผู้รับผิดชอบโครงการ',
    admin: 'Admin ดูแลระบบโรงเรียน'
};

const CATEGORY_NAMES = {
    materials: 'ค่าวัสดุ',
    operating: 'ค่าใช้สอย',
    compensation: 'ค่าตอบแทน',
    utility: 'ค่าสาธารณูปโภค',
    other: 'ค่าใช้จ่ายอื่น'
};

// Initial Setup
document.addEventListener('DOMContentLoaded', async () => {
    lucide.createIcons();

    const stored = localStorage.getItem('currentUser');
    if (stored) {
        try {
            currentUser = JSON.parse(stored);
        } catch (e) {
            currentUser = null;
        }
    }

    if (!currentUser) {
        window.location.href = 'index.php';
        return;
    }

    updateUserUI();
    await loadData(selectedYearId);

    // Check if user must change password
    if (currentUser && (currentUser.must_change_password === 1 || currentUser.password === '123456' || currentUser.password === '123')) {
        setTimeout(() => {
            openChangePasswordModal(true);
        }, 600);
    }

    // Close dropdowns on outside click
    document.addEventListener('click', (e) => {
        const btn = document.getElementById('userMenuBtn');
        const dd = document.getElementById('userDropdown');
        if (btn && dd && !btn.contains(e.target) && !dd.contains(e.target)) {
            dd.classList.add('hidden');
        }
    });
});

function toggleUserDropdown() {
    const dd = document.getElementById('userDropdown');
    if (dd) dd.classList.toggle('hidden');
}

async function logout() {
    try {
        localStorage.clear();
        sessionStorage.clear();
        await fetch('logout.php', { method: 'POST', headers: { 'Accept': 'application/json' } });
    } catch (e) {
        console.error('Logout error:', e);
    }
    window.location.replace('logout.php');
}

function updateUserUI() {
    if (!currentUser) return;
    const nameEl = document.getElementById('currentUserName');
    const roleEl = document.getElementById('currentUserRoleTitle');
    const avatarEl = document.getElementById('userAvatar');
    const ddName = document.getElementById('ddUserName');
    const ddRole = document.getElementById('ddUserRole');

    if (nameEl) nameEl.innerText = currentUser.name || currentUser.username;
    if (roleEl) roleEl.innerText = ROLE_TITLES[currentUser.role] || currentUser.position || currentUser.role;
    if (avatarEl) avatarEl.innerText = currentUser.name ? currentUser.name.charAt(0) : (currentUser.username ? currentUser.username.charAt(0).toUpperCase() : 'U');
    if (ddName) ddName.innerText = currentUser.name || currentUser.username;
    if (ddRole) ddRole.innerText = ROLE_TITLES[currentUser.role] || currentUser.position || currentUser.role;
}

function showToast(msg, type = 'success') {
    const el = document.getElementById('toastMessage');
    if (!el) return;
    el.innerText = msg;
    el.className = `mb-4 p-4 rounded-xl text-xs font-bold flex items-center justify-between shadow-sm transition ${
        type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'
    }`;
    el.classList.remove('hidden');
    setTimeout(() => {
        el.classList.add('hidden');
    }, 4000);
}

function formatBaht(num) {
    return Number(num || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Data Fetching
async function loadData(yearId = null) {
    try {
        const url = yearId ? `/api/plan/get_data.php?year_id=${yearId}` : `/api/plan/get_data.php`;
        const res = await fetch(url);
        const data = await res.json();
        if (data.status === 'success') {
            appData = data;
            selectedYearId = data.currentFiscalYear?.id ? parseInt(data.currentFiscalYear.id) : (yearId || 1);
            renderFiscalYearSelect();
            renderAllViews();
        } else {
            console.error('Failed to load data:', data.message);
        }
    } catch (err) {
        console.error('Network error loading plan data:', err);
    }
}

function renderFiscalYearSelect() {
    const select = document.getElementById('fiscalYearSelect');
    if (!select || !appData?.fiscalYears) return;

    select.innerHTML = appData.fiscalYears.map(fy => {
        const isCur = fy.is_current == 1;
        const selected = (fy.id == selectedYearId) ? 'selected' : '';
        return `<option value="${fy.id}" ${selected}>${fy.year} ${isCur ? '(ปัจจุบัน)' : ''}</option>`;
    }).join('');
}

async function onFiscalYearChange(yearId) {
    await loadData(yearId);
    showToast(`สลับข้อมูลเป็นปีงบประมาณ พ.ศ. ${appData?.currentFiscalYear?.year || ''}`, 'success');
}

// Navigation Tabs
function switchTab(tabId) {
    document.querySelectorAll('.tab-view').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('nav button').forEach(el => el.classList.remove('sidebar-item-active'));

    const targetView = document.getElementById(`view-${tabId}`);
    const targetTab = document.getElementById(`tab-${tabId}`);
    if (targetView) targetView.classList.remove('hidden');
    if (targetTab) targetTab.classList.add('sidebar-item-active');

    // Trigger tab-specific loaders
    if (tabId === 'overview') {
        renderCharts();
    } else if (tabId === 'subsidies') {
        loadSubsidyData();
    } else if (tabId === 'allocation') {
        renderAllocationsCards();
    } else if (tabId === 'school_settings') {
        loadSchoolSettings();
    } else if (tabId === 'superadmin') {
        loadSuperAdminSchools();
        loadSuperAdminCredentials();
    }
    lucide.createIcons();
}

// Render Everything
function renderAllViews() {
    if (!appData) return;

    // Header & Info
    if (appData.school) {
        const headerLogo = document.getElementById('headerSchoolLogo');
        const headerName = document.getElementById('headerSchoolName');
        const headerSmis = document.getElementById('headerSmisCode');
        if (headerLogo && appData.school.logo_url) headerLogo.src = appData.school.logo_url;
        if (headerSmis && appData.school.smis_code) headerSmis.innerText = appData.school.smis_code;
        if (headerName) {
            headerName.innerHTML = `${appData.school.name} (รหัส SMIS: <span id="headerSmisCode">${appData.school.smis_code || '10310001'}</span>) • ${appData.school.affiliation}`;
        }
    }
    document.getElementById('fyDetailYear').innerText = `พ.ศ. ${appData.currentFiscalYear.year}`;
    document.getElementById('fyDetailDates').innerText = `${appData.currentFiscalYear.start_date} - ${appData.currentFiscalYear.end_date}`;
    document.getElementById('fyCurrentBadge').innerText = appData.currentFiscalYear.is_current ? 'ปีงบประมาณปัจจุบัน' : 'ปีงบประมาณย้อนหลัง';

    renderKPIs();
    renderOverviewDepartmentTable();
    renderOverviewRecentProjects();
    renderCharts();
    renderBudgetSourcesTable();
    renderAllocationsCards();
    renderProjectsList();
    renderScreeningView();
    renderApprovalView();
    renderTrackingView();
    renderExpensesView();
    renderPrintReportsView();
    renderHistoryView();
    renderUsersView();

    lucide.createIcons();
}

// 1. KPI Summary
function renderKPIs() {
    const s = appData.summary;
    const remaining = s.totalApprovedBudget - s.totalSpentAcrossAll;

    document.getElementById('kpi-total-budget').innerText = formatBaht(s.totalBudgetReceived) + ' ฿';
    document.getElementById('kpi-sources-count').innerText = `จาก ${appData.budgetSources.length} แหล่งงบประมาณ`;
    document.getElementById('fyTotalReceived').innerText = formatBaht(s.totalBudgetReceived) + ' บาท';

    document.getElementById('kpi-approved-budget').innerText = formatBaht(s.totalApprovedBudget) + ' ฿';
    document.getElementById('kpi-approved-projects-count').innerText = `อนุมัติแล้ว ${s.approvedProjectsCount} / ${s.totalProjectsCount} โครงการ`;

    document.getElementById('kpi-total-spent').innerText = formatBaht(s.totalSpentAcrossAll) + ' ฿';
    document.getElementById('kpi-disbursement-rate').innerText = `${s.disbursementRate}%`;

    document.getElementById('kpi-remaining-budget').innerText = formatBaht(remaining) + ' ฿';
}

// 2. Charts
function renderCharts() {
    if (!appData) return;

    // Allocation Doughnut
    const allocCtx = document.getElementById('allocationChart');
    if (allocCtx) {
        const labels = appData.departmentAllocations.map(a => a.department_name);
        const data = appData.departmentAllocations.map(a => parseFloat(a.percentage));
        const colors = ['#3b82f6', '#10b981', '#a855f7', '#f59e0b', '#64748b'];

        if (allocationChartInstance) allocationChartInstance.destroy();

        allocationChartInstance = new Chart(allocCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });

        // Custom Legend
        const legendContainer = document.getElementById('allocationLegend');
        if (legendContainer) {
            legendContainer.innerHTML = appData.departmentAllocations.map((a, i) => `
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: ${colors[i % colors.length]}"></span>
                        <span class="truncate">${a.department_name}</span>
                    </div>
                    <span class="font-bold text-slate-800">${a.percentage}%</span>
                </div>
            `).join('');
        }
    }

    // Comparison Bar Chart
    const compCtx = document.getElementById('deptComparisonChart');
    if (compCtx) {
        const deptKeys = ['academic', 'budget', 'personnel', 'general', 'reserve'];
        const labels = ['กลุ่มวิชาการ', 'กลุ่มงบประมาณ', 'กลุ่มบุคคล', 'กลุ่มทั่วไป', 'งบสำรอง/กลาง'];

        const allocatedData = deptKeys.map(k => {
            const alloc = appData.departmentAllocations.find(a => a.department === k);
            return alloc ? parseFloat(alloc.allocated_amount) : 0;
        });

        const approvedData = deptKeys.map(k => {
            return appData.projects
                .filter(p => p.department === k && p.status === 'approved')
                .reduce((sum, p) => sum + (parseFloat(p.approved_budget) || 0), 0);
        });

        const spentData = deptKeys.map(k => {
            return appData.projects
                .filter(p => p.department === k)
                .reduce((sum, p) => sum + (p.financials ? p.financials.spent : 0), 0);
        });

        if (deptComparisonChartInstance) deptComparisonChartInstance.destroy();

        deptComparisonChartInstance = new Chart(compCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'งบจัดสรร', data: allocatedData, backgroundColor: '#93c5fd', borderRadius: 6 },
                    { label: 'งบอนุมัติ', data: approvedData, backgroundColor: '#3b82f6', borderRadius: 6 },
                    { label: 'เบิกจ่ายจริง', data: spentData, backgroundColor: '#f59e0b', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) { return Number(val).toLocaleString() + ' ฿'; }
                        }
                    }
                }
            }
        });
    }
}

// 3. Department Overview Table
function renderOverviewDepartmentTable() {
    const tbody = document.getElementById('overviewDeptTableBody');
    if (!tbody) return;

    tbody.innerHTML = appData.departmentAllocations.map(a => {
        const deptProjects = appData.projects.filter(p => p.department === a.department);
        const approvedSum = deptProjects
            .filter(p => p.status === 'approved')
            .reduce((sum, p) => sum + (parseFloat(p.approved_budget) || 0), 0);
        const spentSum = deptProjects.reduce((sum, p) => sum + (p.financials ? p.financials.spent : 0), 0);
        const remaining = parseFloat(a.allocated_amount) - approvedSum;

        return `
            <tr class="hover:bg-slate-50/80 transition">
                <td class="p-3.5 font-bold text-slate-800">${a.department_name}</td>
                <td class="p-3.5 text-center font-bold text-blue-600">${a.percentage}%</td>
                <td class="p-3.5 text-right font-semibold">${formatBaht(a.allocated_amount)}</td>
                <td class="p-3.5 text-right font-semibold text-purple-900">${formatBaht(approvedSum)}</td>
                <td class="p-3.5 text-right font-semibold text-amber-600">${formatBaht(spentSum)}</td>
                <td class="p-3.5 text-right font-bold ${remaining < 0 ? 'text-red-600' : 'text-emerald-700'}">${formatBaht(remaining)}</td>
                <td class="p-3.5 text-center font-semibold">${deptProjects.length} โครงการ</td>
            </tr>
        `;
    }).join('');
}

// 4. Recent Projects
function renderOverviewRecentProjects() {
    const container = document.getElementById('overviewRecentProjects');
    if (!container) return;

    const recent = appData.projects.slice(0, 5);
    container.innerHTML = recent.map(p => {
        const statusBadge = getStatusBadge(p.status);
        return `
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                        ${p.code || 'P'}
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">${p.name}</h4>
                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                            <span>${DEPT_NAMES[p.department] || p.department}</span>
                            <span>•</span>
                            <span>ผู้รับผิดชอบ: ${p.proposer_name || '-'}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-right">
                    <div>
                        <p class="text-xs font-bold text-slate-900">${formatBaht(p.approved_budget > 0 ? p.approved_budget : p.requested_budget)} ฿</p>
                        <p class="text-[10px] text-slate-400">งบประมาณ</p>
                    </div>
                    ${statusBadge}
                </div>
            </div>
        `;
    }).join('');
}

function getStatusBadge(status) {
    if (status === 'approved') {
        return `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1"><i data-lucide="check" class="w-3 h-3"></i> อนุมัติแล้ว</span>`;
    } else if (status === 'screened') {
        return `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> รอ ผอ. อนุมัติ</span>`;
    } else if (status === 'revision_requested') {
        return `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">ส่งกลับแก้ไข</span>`;
    } else if (status === 'rejected') {
        return `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-800">ตัดแผน/ไม่อนุมัติ</span>`;
    } else {
        return `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">รอพิจารณา</span>`;
    }
}

// 5. Budget Sources Management
function renderBudgetSourcesTable() {
    const tbody = document.getElementById('budgetSourcesTableBody');
    if (!tbody) return;

    let total = 0;
    tbody.innerHTML = appData.budgetSources.map((s, idx) => {
        const amt = parseFloat(s.amount) || 0;
        total += amt;
        return `
            <tr class="hover:bg-slate-50/80 transition">
                <td class="p-3.5 text-center font-bold text-slate-400">${idx + 1}</td>
                <td class="p-3.5 font-mono font-semibold text-slate-700">${s.code || '-'}</td>
                <td class="p-3.5 font-bold text-slate-800">${s.name}</td>
                <td class="p-3.5 text-slate-600">${s.category}</td>
                <td class="p-3.5 text-slate-500">${s.received_date || '-'}</td>
                <td class="p-3.5 text-right font-black text-blue-900">${formatBaht(amt)}</td>
                <td class="p-3.5 text-center">
                    <button onclick="deleteBudgetSource(${s.id})" class="text-red-500 hover:text-red-700 p-1 rounded-lg">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('budgetSourcesTotal').innerText = formatBaht(total) + ' บาท';

    // Update dropdown in Project Modal
    const sel = document.getElementById('proj_budget_source');
    if (sel) {
        sel.innerHTML = appData.budgetSources.map(s => `
            <option value="${s.id}">${s.name} (วงเงิน: ${formatBaht(s.amount)} ฿)</option>
        `).join('');
    }
}

function openNewSourceModal() {
    document.getElementById('src_id').value = '';
    document.getElementById('src_name').value = '';
    document.getElementById('src_amount').value = '';
    document.getElementById('src_received_date').value = new Date().toISOString().split('T')[0];
    document.getElementById('sourceModal').classList.remove('hidden');
    lucide.createIcons();
}
function closeSourceModal() {
    document.getElementById('sourceModal').classList.add('hidden');
}

async function handleSourceSubmit(e) {
    e.preventDefault();
    const payload = {
        fiscal_year_id: selectedYearId,
        id: document.getElementById('src_id').value || null,
        name: document.getElementById('src_name').value.trim(),
        category: document.getElementById('src_category').value,
        amount: parseFloat(document.getElementById('src_amount').value) || 0,
        received_date: document.getElementById('src_received_date').value,
        description: document.getElementById('src_description').value.trim()
    };

    try {
        const res = await fetch('/api/plan/save_budget_source.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeSourceModal();
            showToast('บันทึกแหล่งงบประมาณเรียบร้อยแล้ว', 'success');
            await loadData(selectedYearId);
        }
    } catch (err) {
        console.error(err);
    }
}

async function deleteBudgetSource(id) {
    if (!confirm('คุณต้องการลบแหล่งงบประมาณนี้หรือไม่?')) return;
    try {
        const res = await fetch('/api/plan/delete_budget_source.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast('ลบแหล่งงบประมาณสำเร็จ', 'success');
            await loadData(selectedYearId);
        }
    } catch (err) {
        console.error(err);
    }
}

// Fiscal Year Modal & Management
function openFiscalYearModal() {
    const curYear = appData?.currentFiscalYear?.year ? parseInt(appData.currentFiscalYear.year) : 2568;
    const nextYear = curYear + 1;
    const yInput = document.getElementById('new_fy_year');
    if (yInput) {
        yInput.value = nextYear.toString();
        onFyYearInputChanged(nextYear.toString());
    }
    renderFiscalYearsListModal();
    const modal = document.getElementById('fiscalYearModal');
    if (modal) modal.classList.remove('hidden');
    lucide.createIcons();
}

function closeFiscalYearModal() {
    const modal = document.getElementById('fiscalYearModal');
    if (modal) modal.classList.add('hidden');
}

function onFyYearInputChanged(val) {
    const year = parseInt(val);
    if (!isNaN(year) && year > 2400) {
        const adYear = year - 543;
        const startInput = document.getElementById('new_fy_start');
        const endInput = document.getElementById('new_fy_end');
        if (startInput) startInput.value = `${adYear - 1}-10-01`;
        if (endInput) endInput.value = `${adYear}-09-30`;
    }
}

function renderFiscalYearsListModal() {
    const container = document.getElementById('modalFiscalYearsList');
    if (!container) return;
    const years = appData?.fiscalYears || [];
    if (years.length === 0) {
        container.innerHTML = `<div class="p-4 text-center text-xs text-slate-500">ยังไม่มีข้อมูลปีงบประมาณในระบบ</div>`;
        return;
    }

    container.innerHTML = years.map(fy => {
        const isCur = fy.is_current == 1;
        const isSelected = selectedYearId == fy.id;
        return `
            <div class="p-3.5 flex items-center justify-between hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl ${isCur ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'} flex items-center justify-center font-black text-sm">
                        ${fy.year}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-extrabold text-slate-900">ปีงบประมาณ พ.ศ. ${fy.year}</span>
                            ${isCur ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">ปีปัจจุบัน</span>' : ''}
                            ${isSelected ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">กำลังทำงาน</span>' : ''}
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">${fy.start_date || '-'} ถึง ${fy.end_date || '-'}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    ${!isCur ? `
                        <button type="button" onclick="setFiscalYearCurrent(${fy.id})" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 transition">
                            ตั้งเป็นปีปัจจุบัน
                        </button>
                    ` : ''}
                    <button type="button" onclick="onFiscalYearChange(${fy.id}); closeFiscalYearModal();" class="px-3 py-1.5 ${isSelected ? 'bg-slate-200 text-slate-700' : 'bg-blue-600 hover:bg-blue-700 text-white'} text-xs font-bold rounded-lg transition">
                        ${isSelected ? 'กำลังดูอยู่นี้' : 'เลือกดูปีนี้'}
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

async function setFiscalYearCurrent(yearId) {
    try {
        const res = await fetch('/api/plan/save_fiscal_year.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'set_current', id: yearId })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast(data.message, 'success');
            selectedYearId = yearId;
            await loadData(yearId);
            renderFiscalYearsListModal();
        } else {
            showToast(data.message || 'เกิดข้อผิดพลาดในการตั้งปีงบประมาณ', 'error');
        }
    } catch (err) {
        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + err.message, 'error');
    }
}

async function handleFiscalYearSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveFiscalYear');
    if (btn) btn.disabled = true;

    const payload = {
        year: document.getElementById('new_fy_year').value.trim(),
        start_date: document.getElementById('new_fy_start').value,
        end_date: document.getElementById('new_fy_end').value,
        is_current: document.getElementById('new_fy_is_current').checked ? 1 : 0
    };

    try {
        const res = await fetch('/api/plan/save_fiscal_year.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeFiscalYearModal();
            showToast(data.message || `สร้างปีงบประมาณ พ.ศ. ${payload.year} เรียบร้อย`, 'success');
            selectedYearId = data.year_id || selectedYearId;
            await loadData(selectedYearId);
        } else {
            showToast(data.message || 'ไม่สามารถบันทึกปีงบประมาณได้', 'error');
        }
    } catch (err) {
        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + err.message, 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

// 6. 100% Department Allocation (5 Channels & Utility Reserve)
const ALLOC_DEPT_DEFAULTS = [
    { department: 'academic', department_name: 'งานบริหารงานวิชาการ', badge: 'กลุ่มบริหารวิชาการ', defaultPct: 45.0, icon: 'book-open', color: 'indigo', barHex: '#4f46e5', lightBg: 'bg-indigo-50', textCol: 'text-indigo-700', borderCol: 'border-indigo-200' },
    { department: 'personnel', department_name: 'งานบุคคล', badge: 'กลุ่มบริหารงานบุคคล', defaultPct: 10.0, icon: 'users', color: 'blue', barHex: '#2563eb', lightBg: 'bg-blue-50', textCol: 'text-blue-700', borderCol: 'border-blue-200' },
    { department: 'budget', department_name: 'งานงบประมาณ', badge: 'กลุ่มบริหารงบประมาณ', defaultPct: 10.0, icon: 'wallet', color: 'emerald', barHex: '#059669', lightBg: 'bg-emerald-50', textCol: 'text-emerald-700', borderCol: 'border-emerald-200' },
    { department: 'general', department_name: 'งานบริหารงานทั่วไป', badge: 'กลุ่มบริหารทั่วไป', defaultPct: 20.0, icon: 'building-2', color: 'amber', barHex: '#d97706', lightBg: 'bg-amber-50', textCol: 'text-amber-700', borderCol: 'border-amber-200' },
    { department: 'reserve', department_name: 'กันไว้สำหรับค่าใช้จ่ายอื่นๆ', badge: 'งบอื่นๆ/สำรองจ่าย', defaultPct: 15.0, icon: 'shield-alert', color: 'purple', barHex: '#9333ea', lightBg: 'bg-purple-50', textCol: 'text-purple-700', borderCol: 'border-purple-200' }
];

function getAllocatableBudget() {
    const totalInput = document.getElementById('allocTotalBudgetInput');
    const utilityInput = document.getElementById('allocUtilityReserveInput');
    const totalBase = totalInput ? parseFloat(totalInput.value) || 0 : (appData?.budgetConfig?.totalBudgetBase || 0);
    const utilityReserve = utilityInput ? parseFloat(utilityInput.value) || 0 : (appData?.budgetConfig?.utilityReserve || 0);
    return Math.max(0, totalBase - utilityReserve);
}

function renderAllocationsCards() {
    const container = document.getElementById('allocationCardsContainer');
    if (!container) return;

    // 1. Sync Budget Base & Utility Reserve inputs
    const cfg = appData?.budgetConfig || {};
    const calcTotal = cfg.calculatedSubsidyTotal ?? (appData?.subsidiesSummary?.grand_total || 0);
    let totalBudgetBase = cfg.totalBudgetBase ?? (appData?.summary?.totalBudgetReceived || calcTotal);
    if (totalBudgetBase <= 0 && calcTotal > 0) totalBudgetBase = calcTotal;

    const utilityReserve = cfg.utilityReserve ?? 0;
    const utilityNotes = cfg.utilityReserveNotes || 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)';

    const totalInput = document.getElementById('allocTotalBudgetInput');
    const utilityInput = document.getElementById('allocUtilityReserveInput');
    const utilityNotesInput = document.getElementById('allocUtilityNotesInput');
    const calcRefText = document.getElementById('allocCalcReferenceText');

    if (totalInput && !totalInput.dataset.userEdited) totalInput.value = totalBudgetBase;
    if (utilityInput && !utilityInput.dataset.userEdited) utilityInput.value = utilityReserve;
    if (utilityNotesInput) utilityNotesInput.value = utilityNotes;
    if (calcRefText) calcRefText.innerText = 'ยอดจากการคำนวณรายหัว & กพพ.: ' + formatBaht(calcTotal) + ' บาท';

    // 2. Prepare 5 allocation items
    let allocs = Array.isArray(appData?.departmentAllocations) && appData.departmentAllocations.length > 0 
        ? [...appData.departmentAllocations] 
        : [];

    // Ensure all 5 categories exist
    ALLOC_DEPT_DEFAULTS.forEach(def => {
        const found = allocs.find(a => a.department === def.department || (def.department === 'reserve' && a.department === 'other'));
        if (!found) {
            allocs.push({
                id: 'new_' + def.department,
                department: def.department,
                department_name: def.department_name,
                percentage: def.defaultPct,
                allocated_amount: 0,
                notes: ''
            });
        }
    });

    // Normalize reserve/other name
    allocs = allocs.map(a => {
        if (a.department === 'other') a.department = 'reserve';
        if (a.department === 'reserve' && (!a.department_name || a.department_name === 'งบสำรองจ่าย/ส่วนกลาง')) {
            a.department_name = 'กันไว้สำหรับค่าใช้จ่ายอื่นๆ';
        }
        return a;
    });

    if (appData) appData.departmentAllocations = allocs;
    const allocatable = getAllocatableBudget();

    // 3. Render 5 Cards with percentage slider, numeric input, and baht input
    container.innerHTML = allocs.map((a, idx) => {
        const def = ALLOC_DEPT_DEFAULTS.find(d => d.department === a.department) || ALLOC_DEPT_DEFAULTS[idx] || ALLOC_DEPT_DEFAULTS[0];
        const pct = parseFloat(a.percentage) || 0;
        const amt = a.allocated_amount > 0 ? parseFloat(a.allocated_amount) : ((pct / 100) * allocatable);

        return `
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:border-blue-400 transition group space-y-4">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl ${def.lightBg} ${def.textCol} flex items-center justify-center font-bold">
                            <i data-lucide="${def.icon}" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">${a.department_name}</h4>
                            <span class="text-[10px] font-bold text-slate-400">${def.badge}</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md ${def.lightBg} ${def.textCol} border ${def.borderCol}">ช่องที่ ${idx + 1}</span>
                </div>

                <!-- Percentage Input & Range Slider -->
                <div class="space-y-2 mt-3">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                        <label for="alloc_pct_${a.department}" class="flex items-center gap-1">
                            <span>สัดส่วนเปอร์เซ็นต์ (%):</span>
                        </label>
                        <div class="relative w-24">
                            <input type="number" step="0.01" min="0" max="100" 
                                   id="alloc_pct_${a.department}" 
                                   data-dept="${a.department}"
                                   data-id="${a.id}"
                                   value="${pct.toFixed(2)}" 
                                   oninput="onAllocPercentageInput('${a.department}')"
                                   class="w-full pl-2.5 pr-6 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-extrabold text-blue-900 outline-none focus:bg-white focus:border-blue-500 text-right">
                            <span class="absolute right-2 top-1.5 text-xs font-bold text-slate-400">%</span>
                        </div>
                    </div>

                    <!-- Slider for quick percentage drag -->
                    <input type="range" min="0" max="100" step="0.5" 
                           id="alloc_slider_${a.department}"
                           value="${pct}" 
                           oninput="onAllocSliderInput('${a.department}', this.value)"
                           class="w-full accent-blue-600 cursor-pointer h-1.5 bg-slate-100 rounded-lg">
                </div>

                <!-- Baht Amount Input -->
                <div class="space-y-1.5 mt-3">
                    <label class="block text-[11px] font-bold text-slate-600">จำนวนเงินจัดสรร (บาท):</label>
                    <div class="relative">
                        <input type="number" step="100" min="0" 
                               id="alloc_amt_input_${a.department}" 
                               data-dept="${a.department}"
                               value="${amt.toFixed(2)}" 
                               oninput="onAllocAmountInput('${a.department}')"
                               class="w-full pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 outline-none focus:bg-white focus:border-emerald-500">
                        <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">฿</span>
                    </div>
                </div>

                <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100">
                    <span>ยอดคำนวณสุทธิ:</span>
                    <span class="font-extrabold text-slate-800" id="alloc_amt_${a.department}">${formatBaht(amt)} บาท</span>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <input type="text" id="alloc_notes_${a.department}" value="${a.notes || ''}" placeholder="หมายเหตุ / วัตถุประสงค์งบช่องนี้..." 
                       class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-400">
            </div>
        </div>`;
    }).join('');

    lucide.createIcons();
    onBudgetBaseChanged();
}

function onBudgetSourceTypeChange() {
    const isCalc = document.getElementById('src_calc')?.checked;
    const totalInput = document.getElementById('allocTotalBudgetInput');
    const badge = document.getElementById('budgetSourceBadge');
    const cfg = appData?.budgetConfig || {};
    const calcTotal = cfg.calculatedSubsidyTotal ?? (appData?.subsidiesSummary?.grand_total || 0);

    if (isCalc) {
        if (totalInput) {
            totalInput.value = calcTotal;
            totalInput.dataset.userEdited = '';
        }
        if (badge) {
            badge.innerText = 'จากยอดคำนวณ';
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-100 text-blue-700';
        }
    } else {
        if (badge) {
            badge.innerText = 'ใส่ยอดเอง';
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-800';
        }
    }
    onBudgetBaseChanged();
}

function onBudgetBaseChanged() {
    const totalInput = document.getElementById('allocTotalBudgetInput');
    if (totalInput) totalInput.dataset.userEdited = 'true';

    const allocatable = getAllocatableBudget();
    const allocDisplay = document.getElementById('allocatableBudgetDisplay');
    if (allocDisplay) allocDisplay.innerText = `${formatBaht(allocatable)} ฿`;

    // Recalculate amounts for each department based on its current percentage
    ALLOC_DEPT_DEFAULTS.forEach(def => {
        const pctInput = document.getElementById(`alloc_pct_${def.department}`);
        const slider = document.getElementById(`alloc_slider_${def.department}`);
        const amtInput = document.getElementById(`alloc_amt_input_${def.department}`);
        const amtDisplay = document.getElementById(`alloc_amt_${def.department}`);

        if (pctInput) {
            const pct = parseFloat(pctInput.value) || 0;
            if (slider) slider.value = pct;
            const amt = (pct / 100) * allocatable;
            if (amtInput) amtInput.value = amt.toFixed(2);
            if (amtDisplay) amtDisplay.innerText = `${formatBaht(amt)} บาท`;
        }
    });

    validateAllocationsSum();
}

function onAllocSliderInput(dept, val) {
    const pctInput = document.getElementById(`alloc_pct_${dept}`);
    if (pctInput) pctInput.value = parseFloat(val).toFixed(2);
    onAllocPercentageInput(dept);
}

function onAllocPercentageInput(dept) {
    const allocatable = getAllocatableBudget();
    const pctInput = document.getElementById(`alloc_pct_${dept}`);
    const slider = document.getElementById(`alloc_slider_${dept}`);
    const amtInput = document.getElementById(`alloc_amt_input_${dept}`);
    const amtDisplay = document.getElementById(`alloc_amt_${dept}`);

    if (pctInput) {
        const pct = parseFloat(pctInput.value) || 0;
        if (slider) slider.value = pct;
        const amt = (pct / 100) * allocatable;
        if (amtInput) amtInput.value = amt.toFixed(2);
        if (amtDisplay) amtDisplay.innerText = `${formatBaht(amt)} บาท`;
    }
    validateAllocationsSum();
}

function onAllocAmountInput(dept) {
    const allocatable = getAllocatableBudget();
    const pctInput = document.getElementById(`alloc_pct_${dept}`);
    const slider = document.getElementById(`alloc_slider_${dept}`);
    const amtInput = document.getElementById(`alloc_amt_input_${dept}`);
    const amtDisplay = document.getElementById(`alloc_amt_${dept}`);

    if (amtInput) {
        const amt = parseFloat(amtInput.value) || 0;
        if (allocatable > 0) {
            const pct = (amt / allocatable) * 100;
            if (pctInput) pctInput.value = pct.toFixed(2);
            if (slider) slider.value = pct;
        }
        if (amtDisplay) amtDisplay.innerText = `${formatBaht(amt)} บาท`;
    }
    validateAllocationsSum();
}

function applyPresetAllocation(preset) {
    const presets = {
        standard: { academic: 45.0, personnel: 10.0, budget: 10.0, general: 20.0, reserve: 15.0 },
        academic: { academic: 55.0, personnel: 10.0, budget: 5.0, general: 15.0, reserve: 15.0 },
        balanced: { academic: 35.0, personnel: 15.0, budget: 15.0, general: 20.0, reserve: 15.0 },
        equal: { academic: 20.0, personnel: 20.0, budget: 20.0, general: 20.0, reserve: 20.0 }
    };

    const target = presets[preset];
    if (!target) return;

    Object.keys(target).forEach(dept => {
        const pctInput = document.getElementById(`alloc_pct_${dept}`);
        const slider = document.getElementById(`alloc_slider_${dept}`);
        if (pctInput) pctInput.value = target[dept].toFixed(2);
        if (slider) slider.value = target[dept];
    });

    onBudgetBaseChanged();
    const labels = {
        standard: 'สพฐ. มาตรฐาน (45-10-10-20-15)',
        academic: 'เน้นวิชาการ (55-10-5-15-15)',
        balanced: 'สมดุล 4 กลุ่มงาน (35-15-15-20-15)',
        equal: 'หารเท่ากัน 5 ช่อง (20% ทุกช่อง)'
    };
    showToast(`ใช้สัดส่วนจัดสรรรูปแบบ "${labels[preset] || preset}" เรียบร้อยแล้ว`, 'info');
}

function autoBalanceAllocation() {
    let sumOther = 0;
    ALLOC_DEPT_DEFAULTS.forEach(def => {
        if (def.department !== 'reserve') {
            const input = document.getElementById(`alloc_pct_${def.department}`);
            if (input) sumOther += (parseFloat(input.value) || 0);
        }
    });

    const remainder = Math.max(0, parseFloat((100 - sumOther).toFixed(2)));
    const reserveInput = document.getElementById('alloc_pct_reserve');
    const reserveSlider = document.getElementById('alloc_slider_reserve');
    if (reserveInput) reserveInput.value = remainder.toFixed(2);
    if (reserveSlider) reserveSlider.value = remainder;

    onBudgetBaseChanged();
    showToast(`ปรับสัดส่วนงบอื่นๆ ให้รวมครบ 100.00% แล้ว (${remainder.toFixed(2)}%)`, 'info');
}

function updateStackedBar(allocatable) {
    const bar = document.getElementById('allocStackedBar');
    const legend = document.getElementById('allocLegendContainer');
    const barSumText = document.getElementById('stackedBarSumText');
    if (!bar) return;

    let barHtml = '';
    let legendHtml = '';
    let totalPct = 0;

    ALLOC_DEPT_DEFAULTS.forEach(def => {
        const pctInput = document.getElementById(`alloc_pct_${def.department}`);
        const pct = pctInput ? parseFloat(pctInput.value) || 0 : 0;
        totalPct += pct;
        const amt = (pct / 100) * allocatable;

        if (pct > 0) {
            barHtml += `
                <div style="width: ${pct}%; background-color: ${def.barHex};" 
                     class="h-full transition-all duration-300 relative group flex items-center justify-center text-[10px] text-white font-extrabold" 
                     title="${def.department_name}: ${pct.toFixed(2)}% (${formatBaht(amt)} บ.)">
                    ${pct >= 8 ? `${pct.toFixed(0)}%` : ''}
                </div>
            `;
        }

        legendHtml += `
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: ${def.barHex};"></span>
                <span class="text-slate-600 font-semibold">${def.department_name}:</span>
                <span class="font-extrabold text-slate-900">${pct.toFixed(1)}%</span>
            </div>
        `;
    });

    bar.innerHTML = barHtml;
    if (legend) legend.innerHTML = legendHtml;
    if (barSumText) barSumText.innerText = `รวม ${totalPct.toFixed(2)}%`;
}

function renderAllocationSummaryTable(allocatable) {
    const tbody = document.getElementById('allocationSummaryTableBody');
    if (!tbody) return;

    let totalPct = 0;
    let totalAmt = 0;

    const rows = ALLOC_DEPT_DEFAULTS.map((def, idx) => {
        const pctInput = document.getElementById(`alloc_pct_${def.department}`);
        const notesInput = document.getElementById(`alloc_notes_${def.department}`);
        const pct = pctInput ? parseFloat(pctInput.value) || 0 : 0;
        const amt = (pct / 100) * allocatable;
        const notes = notesInput ? notesInput.value.trim() : '';

        totalPct += pct;
        totalAmt += amt;

        return `
            <tr class="hover:bg-slate-50 transition">
                <td class="p-3 text-center text-slate-500 font-bold">${idx + 1}</td>
                <td class="p-3 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: ${def.barHex};"></span>
                    <span class="font-bold text-slate-900">${def.department_name}</span>
                </td>
                <td class="p-3 text-center font-extrabold text-blue-900">${pct.toFixed(2)}%</td>
                <td class="p-3 text-right font-extrabold text-slate-900">${formatBaht(amt)} บาท</td>
                <td class="p-3 text-slate-600 text-[11px]">${notes || '-'}</td>
            </tr>
        `;
    }).join('');

    tbody.innerHTML = rows;

    const tablePct = document.getElementById('allocTableTotalPct');
    const tableAmt = document.getElementById('allocTableTotalAmt');
    const tableNote = document.getElementById('allocTableStatusNote');

    if (tablePct) tablePct.innerText = `${totalPct.toFixed(2)}%`;
    if (tableAmt) tableAmt.innerText = `${formatBaht(totalAmt)} บาท`;
    if (tableNote) {
        if (Math.abs(totalPct - 100) < 0.05) {
            tableNote.innerText = '✓ สัดส่วนครบ 100% พอดี';
            tableNote.className = 'p-3 text-emerald-600 font-bold';
        } else {
            tableNote.innerText = totalPct > 100 ? `เกิน ${(totalPct - 100).toFixed(2)}%` : `ยังขาด ${(100 - totalPct).toFixed(2)}%`;
            tableNote.className = 'p-3 text-amber-600 font-bold';
        }
    }
}

function validateAllocationsSum() {
    let sumPct = 0;
    let sumAmt = 0;
    const allocatable = getAllocatableBudget();

    ALLOC_DEPT_DEFAULTS.forEach(def => {
        const pctInput = document.getElementById(`alloc_pct_${def.department}`);
        const amtInput = document.getElementById(`alloc_amt_input_${def.department}`);
        const pct = pctInput ? parseFloat(pctInput.value) || 0 : 0;
        const amt = amtInput ? parseFloat(amtInput.value) || 0 : (pct / 100) * allocatable;
        sumPct += pct;
        sumAmt += amt;
    });

    const banner = document.getElementById('percentValidationBanner');
    const badge = document.getElementById('percentTotalBadge');
    const amtBadge = document.getElementById('percentTotalAmountBadge');
    const title = document.getElementById('percentStatusTitle');
    const desc = document.getElementById('percentStatusDesc');
    const icon = document.getElementById('percentIcon');
    const sumNotice = document.getElementById('allocatedSumNotice');

    if (badge) badge.innerText = `${sumPct.toFixed(2)}%`;
    if (amtBadge) amtBadge.innerText = `${formatBaht(sumAmt)} บาท`;
    if (sumNotice) sumNotice.innerText = `รวม ${sumPct.toFixed(2)}% (${formatBaht(sumAmt)} บ.)`;

    updateStackedBar(allocatable);
    renderAllocationSummaryTable(allocatable);

    if (!banner) return;

    if (Math.abs(sumPct - 100) < 0.05) {
        banner.className = 'p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-900 flex items-center justify-between transition-all';
        if (title) title.innerText = 'สัดส่วนครบ 100.00% พอดี (5 ช่องงาน)';
        if (desc) desc.innerText = `ยอดจัดสรรรวม ${formatBaht(sumAmt)} บาท ตรงตามยอดงบสุทธิ พร้อมบันทึกแผนปฏิบัติการ`;
        if (icon) {
            icon.className = 'w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs';
            icon.innerHTML = '✓';
        }
    } else {
        banner.className = 'p-4 rounded-2xl border border-amber-200 bg-amber-50 text-amber-900 flex items-center justify-between transition-all';
        if (title) title.innerText = `สัดส่วนยังไม่เท่ากับ 100.00% (ปัจจุบัน ${sumPct.toFixed(2)}%)`;
        if (desc) desc.innerText = sumPct > 100 
            ? `เกินอยู่ ${(sumPct - 100).toFixed(2)}% (${formatBaht(sumAmt - allocatable)} บ.) กรุณาปรับลดงบประมาณ` 
            : `ยังขาดอีก ${(100 - sumPct).toFixed(2)}% (${formatBaht(allocatable - sumAmt)} บ.) กรุณาเพิ่มสัดส่วนให้ครบ 100%`;
        if (icon) {
            icon.className = 'w-9 h-9 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-sm shadow-xs';
            icon.innerHTML = '!';
        }
    }
}

async function loadAllocationData() {
    await loadData(selectedYearId);
    showToast('รีเฟรชข้อมูลการจัดสรรเรียบร้อยแล้ว', 'info');
}

async function saveAllocations() {
    const allocatable = getAllocatableBudget();
    const totalInput = document.getElementById('allocTotalBudgetInput');
    const utilityInput = document.getElementById('allocUtilityReserveInput');
    const utilityNotesInput = document.getElementById('allocUtilityNotesInput');
    const isCalc = document.getElementById('src_calc')?.checked;

    const totalBase = totalInput ? parseFloat(totalInput.value) || 0 : 0;
    const utilityReserve = utilityInput ? parseFloat(utilityInput.value) || 0 : 0;
    const utilityNotes = utilityNotesInput ? utilityNotesInput.value.trim() : 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)';

    const allocationsToSave = ALLOC_DEPT_DEFAULTS.map(def => {
        const pctInput = document.getElementById(`alloc_pct_${def.department}`);
        const notesInput = document.getElementById(`alloc_notes_${def.department}`);
        const pct = pctInput ? parseFloat(pctInput.value) || 0 : 0;
        const notes = notesInput ? notesInput.value.trim() : '';
        const amt = (pct / 100) * allocatable;

        const existing = (appData?.departmentAllocations || []).find(a => a.department === def.department);

        return {
            id: existing ? existing.id : 0,
            department: def.department,
            department_name: def.department_name,
            percentage: pct,
            allocated_amount: amt,
            notes: notes
        };
    });

    const sumPct = allocationsToSave.reduce((s, a) => s + a.percentage, 0);
    if (Math.abs(sumPct - 100) > 0.05) {
        showToast(`สัดส่วนรวมต้องเท่ากับ 100.00% พอดี (ปัจจุบันได้ ${sumPct.toFixed(2)}%)\nกรุณาปรับสัดส่วนให้ครบ 100% ก่อนบันทึก`, 'error');
        return;
    }

    try {
        const res = await fetch('/api/plan/save_allocations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                fiscal_year_id: selectedYearId,
                total_budget_base: totalBase,
                utility_reserve: utilityReserve,
                utility_reserve_notes: utilityNotes,
                budget_source_type: isCalc ? 'calc' : 'custom',
                allocations: allocationsToSave
            })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast('บันทึกการจัดสรรงบประมาณ 5 ช่องงาน และการกันค่าสาธารณูปโภคเรียบร้อยแล้ว', 'success');
            await loadData(selectedYearId);
        } else {
            showToast(data.message || 'เกิดข้อผิดพลาดในการบันทึก', 'error');
        }
    } catch (err) {
        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + err.message, 'error');
    }
}

// 7. Projects View
function renderProjectsList() {
    const tbody = document.getElementById('projectsTableBody');
    if (!tbody || !appData) return;

    const filterDept = document.getElementById('projectFilterDept').value;
    const filterStatus = document.getElementById('projectFilterStatus').value;
    const search = document.getElementById('projectSearchInput').value.toLowerCase().trim();

    const filtered = appData.projects.filter(p => {
        if (filterDept !== 'all' && p.department !== filterDept) return false;
        if (filterStatus !== 'all' && p.status !== filterStatus) return false;
        if (search) {
            const matchName = (p.name || '').toLowerCase().includes(search);
            const matchProposer = (p.proposer_name || '').toLowerCase().includes(search);
            const matchCode = (p.code || '').toLowerCase().includes(search);
            if (!matchName && !matchProposer && !matchCode) return false;
        }
        return true;
    });

    tbody.innerHTML = filtered.map(p => {
        const approved = parseFloat(p.approved_budget) || 0;
        const requested = parseFloat(p.requested_budget) || 0;
        const progress = p.progress_percentage || 0;
        return `
            <tr class="hover:bg-slate-50/80 transition">
                <td class="p-3.5 font-mono font-bold text-slate-600">${p.code || '-'}</td>
                <td class="p-3.5">
                    <p class="font-bold text-slate-900 text-xs">${p.name}</p>
                    <span class="text-[11px] text-slate-500">${DEPT_NAMES[p.department] || p.department}</span>
                </td>
                <td class="p-3.5 text-slate-700 font-medium">${p.proposer_name || '-'}</td>
                <td class="p-3.5 text-right font-semibold text-slate-600">${formatBaht(requested)}</td>
                <td class="p-3.5 text-right font-bold text-blue-900">${formatBaht(approved > 0 ? approved : requested)}</td>
                <td class="p-3.5 text-center">${getStatusBadge(p.status)}</td>
                <td class="p-3.5 text-center">
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: ${progress}%"></div>
                    </div>
                    <span class="text-[10px] font-bold text-slate-600 mt-0.5 block">${progress}%</span>
                </td>
                <td class="p-3.5 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <a href="print_project.php?id=${p.id}" target="_blank" title="พิมพ์โครงการ (มาตรฐาน สพฐ.)" 
                           class="p-1.5 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                        </a>
                        <button onclick="editProject(${p.id})" title="แก้ไขโครงการ" 
                                class="p-1.5 text-slate-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    lucide.createIcons();
}

// 8. Project Proposal Modal & AI Gemini Assistant
let currentBudgetItems = [];

function openNewProjectModal() {
    document.getElementById('proj_id').value = '';
    document.getElementById('proj_name').value = '';
    document.getElementById('proj_rationale').value = '';
    document.getElementById('proj_objectives').value = '';
    document.getElementById('proj_target_qty').value = '';
    document.getElementById('proj_target_quality').value = '';
    document.getElementById('proj_indicators').value = '';
    document.getElementById('proj_expected_outcomes').value = '';
    document.getElementById('proj_start_date').value = '2024-10-01';
    document.getElementById('proj_end_date').value = '2025-09-30';
    document.getElementById('projectModalTitle').innerText = 'เสนอโครงการตามแผนปฏิบัติการประจำปี';

    currentBudgetItems = [
        { category: 'materials', item_name: 'ค่าวัสดุและอุปกรณ์การจัดกิจกรรม', quantity: 1, unit: 'ชุด', unit_price: 15000, total_price: 15000 }
    ];
    renderBudgetItemsTable();

    document.getElementById('projectModal').classList.remove('hidden');
    lucide.createIcons();
}

function openNewProjectModalWithAI() {
    openNewProjectModal();
    const projName = prompt('กรุณาระบุแนวคิดหรือชื่อโครงการที่ต้องการให้ AI ช่วยร่าง:', 'โครงการยกระดับผลสัมฤทธิ์การเรียนรู้และทักษะแห่งศตวรรษที่ 21');
    if (projName && projName.trim()) {
        document.getElementById('proj_name').value = projName.trim();
        triggerAiDraft();
    }
}

function closeProjectModal() {
    document.getElementById('projectModal').classList.add('hidden');
}

function addBudgetItemRow() {
    currentBudgetItems.push({
        category: 'materials',
        item_name: 'รายการค่าใช้จ่ายใหม่',
        quantity: 1,
        unit: 'รายการ',
        unit_price: 5000,
        total_price: 5000
    });
    renderBudgetItemsTable();
}

function removeBudgetItemRow(idx) {
    currentBudgetItems.splice(idx, 1);
    renderBudgetItemsTable();
}

function updateBudgetItem(idx, field, value) {
    currentBudgetItems[idx][field] = value;
    if (field === 'quantity' || field === 'unit_price') {
        const qty = parseFloat(currentBudgetItems[idx].quantity) || 0;
        const price = parseFloat(currentBudgetItems[idx].unit_price) || 0;
        currentBudgetItems[idx].total_price = qty * price;
    }
    renderBudgetItemsTable();
}

function renderBudgetItemsTable() {
    const tbody = document.getElementById('budgetItemsTableBody');
    if (!tbody) return;

    let total = 0;
    tbody.innerHTML = currentBudgetItems.map((it, idx) => {
        const lineTotal = (parseFloat(it.quantity) || 0) * (parseFloat(it.unit_price) || 0);
        total += lineTotal;
        return `
            <tr class="hover:bg-slate-50">
                <td class="p-1.5">
                    <select onchange="updateBudgetItem(${idx}, 'category', this.value)" class="w-full p-1 bg-white border border-slate-200 rounded text-[11px]">
                        <option value="materials" ${it.category === 'materials' ? 'selected' : ''}>ค่าวัสดุ</option>
                        <option value="operating" ${it.category === 'operating' ? 'selected' : ''}>ค่าใช้สอย</option>
                        <option value="compensation" ${it.category === 'compensation' ? 'selected' : ''}>ค่าตอบแทน</option>
                        <option value="utility" ${it.category === 'utility' ? 'selected' : ''}>ค่าสาธารณูปโภค</option>
                        <option value="other" ${it.category === 'other' ? 'selected' : ''}>ค่าใช้จ่ายอื่น</option>
                    </select>
                </td>
                <td class="p-1.5">
                    <input type="text" value="${it.item_name}" oninput="updateBudgetItem(${idx}, 'item_name', this.value)" class="w-full p-1 bg-white border border-slate-200 rounded text-[11px]">
                </td>
                <td class="p-1.5">
                    <input type="number" step="1" value="${it.quantity}" oninput="updateBudgetItem(${idx}, 'quantity', this.value)" class="w-full p-1 bg-white border border-slate-200 rounded text-center text-[11px]">
                </td>
                <td class="p-1.5">
                    <input type="text" value="${it.unit}" oninput="updateBudgetItem(${idx}, 'unit', this.value)" class="w-full p-1 bg-white border border-slate-200 rounded text-[11px]">
                </td>
                <td class="p-1.5">
                    <input type="number" step="0.01" value="${it.unit_price}" oninput="updateBudgetItem(${idx}, 'unit_price', this.value)" class="w-full p-1 bg-white border border-slate-200 rounded text-right text-[11px]">
                </td>
                <td class="p-1.5 text-right font-bold text-slate-800 text-[11px]">
                    ${formatBaht(lineTotal)}
                </td>
                <td class="p-1.5 text-center">
                    <button type="button" onclick="removeBudgetItemRow(${idx})" class="text-red-500 hover:text-red-700">
                        <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('budgetItemsTotalText').innerText = formatBaht(total);
    document.getElementById('proj_requested_budget').value = total;
    lucide.createIcons();
}

async function triggerAiDraft() {
    const projName = document.getElementById('proj_name').value.trim();
    if (!projName) {
        alert('กรุณากรอกชื่อโครงการก่อน เพื่อให้ AI นำไปร่างเอกสาร');
        return;
    }

    const btn = document.getElementById('btnAiDraft');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin mr-1">⏳</span> AI กำลังวิเคราะห์และร่างโครงการ สพฐ...';

    try {
        const res = await fetch('/api/plan/ai_assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'draft_project',
                title: projName,
                department: document.getElementById('proj_department').value
            })
        });
        const data = await res.json();

        if (data.status === 'success' && data.data) {
            const d = data.data;
            if (d.name) document.getElementById('proj_name').value = d.name;
            if (d.rationale) document.getElementById('proj_rationale').value = d.rationale;
            if (d.objectives) document.getElementById('proj_objectives').value = d.objectives;
            if (d.target_qty) document.getElementById('proj_target_qty').value = d.target_qty;
            if (d.target_quality) document.getElementById('proj_target_quality').value = d.target_quality;
            if (d.indicators) document.getElementById('proj_indicators').value = d.indicators;
            if (d.expected_outcomes) document.getElementById('proj_expected_outcomes').value = d.expected_outcomes;

            if (d.budget_items && Array.isArray(d.budget_items) && d.budget_items.length > 0) {
                currentBudgetItems = d.budget_items.map(it => ({
                    category: it.category || 'materials',
                    item_name: it.item_name || 'รายการค่าใช้จ่าย',
                    quantity: parseFloat(it.quantity) || 1,
                    unit: it.unit || 'ชุด',
                    unit_price: parseFloat(it.unit_price) || 5000,
                    total_price: (parseFloat(it.quantity) || 1) * (parseFloat(it.unit_price) || 5000)
                }));
                renderBudgetItemsTable();
            }

            showToast('✨ AI ร่างรายละเอียดโครงการตามแบบแผน สพฐ. สำเร็จเรียบร้อย!', 'success');
        } else {
            showToast('AI ร่างโครงการไม่สำเร็จ กรุณาลองใหม่อีกครั้ง', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ AI', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="wand-2" class="w-3.5 h-3.5 mr-1"></i> ให้ AI ร่างเอกสาร';
        lucide.createIcons();
    }
}

async function polishTextWithAI(fieldKey) {
    const txtArea = document.getElementById(`proj_${fieldKey}`);
    const original = txtArea.value.trim();
    if (!original) {
        alert('กรุณากรอกข้อความก่อนขัดเกลา');
        return;
    }

    try {
        const res = await fetch('/api/plan/ai_assistant.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'polish_text',
                field: fieldKey,
                content: original
            })
        });
        const data = await res.json();
        if (data.status === 'success' && data.data && data.data.polished_text) {
            txtArea.value = data.data.polished_text;
            showToast('ขัดเกลาภาษาราชการด้วย AI สำเร็จ', 'success');
        }
    } catch (e) {
        console.error(e);
    }
}

async function handleProjectSubmit(e) {
    e.preventDefault();
    const requested = parseFloat(document.getElementById('proj_requested_budget').value) || 0;

    const payload = {
        id: document.getElementById('proj_id').value || null,
        fiscal_year_id: selectedYearId,
        department: document.getElementById('proj_department').value,
        name: document.getElementById('proj_name').value.trim(),
        proposer_id: currentUser.id || 8,
        budget_source_id: document.getElementById('proj_budget_source').value,
        standard_alignment: document.getElementById('proj_standard').value,
        strategy_alignment: document.getElementById('proj_strategy').value,
        start_date: document.getElementById('proj_start_date').value,
        end_date: document.getElementById('proj_end_date').value,
        location: document.getElementById('proj_location').value,
        rationale: document.getElementById('proj_rationale').value,
        objectives: document.getElementById('proj_objectives').value,
        target_qty: document.getElementById('proj_target_qty').value,
        target_quality: document.getElementById('proj_target_quality').value,
        requested_budget: requested,
        indicators: document.getElementById('proj_indicators').value,
        expected_outcomes: document.getElementById('proj_expected_outcomes').value,
        items: currentBudgetItems
    };

    try {
        const res = await fetch('/api/plan/save_project.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeProjectModal();
            showToast('บันทึกและส่งเสนอโครงการเข้าสู่ระบบเรียบร้อยแล้ว', 'success');
            await loadData(selectedYearId);
        } else {
            alert(data.message || 'บันทึกไม่สำเร็จ');
        }
    } catch (err) {
        console.error(err);
    }
}

async function editProject(id) {
    try {
        const res = await fetch(`/api/plan/get_project_detail.php?id=${id}`);
        const data = await res.json();
        if (data.status === 'success') {
            const p = data.project;
            document.getElementById('proj_id').value = p.id;
            document.getElementById('proj_name').value = p.name;
            document.getElementById('proj_department').value = p.department;
            document.getElementById('proj_budget_source').value = p.budget_source_id;
            document.getElementById('proj_standard').value = p.standard_alignment || '';
            document.getElementById('proj_strategy').value = p.strategy_alignment || '';
            document.getElementById('proj_start_date').value = p.start_date || '';
            document.getElementById('proj_end_date').value = p.end_date || '';
            document.getElementById('proj_location').value = p.location || '';
            document.getElementById('proj_rationale').value = p.rationale || '';
            document.getElementById('proj_objectives').value = p.objectives || '';
            document.getElementById('proj_target_qty').value = p.target_qty || '';
            document.getElementById('proj_target_quality').value = p.target_quality || '';
            document.getElementById('proj_indicators').value = p.indicators || '';
            document.getElementById('proj_expected_outcomes').value = p.expected_outcomes || '';
            document.getElementById('projectModalTitle').innerText = 'แก้ไขแบบเสนอโครงการ';

            currentBudgetItems = data.items || [];
            renderBudgetItemsTable();

            document.getElementById('projectModal').classList.remove('hidden');
            lucide.createIcons();
        }
    } catch (e) {
        console.error(e);
    }
}

// 9. Screening & Trimming
function renderScreeningView() {
    const container = document.getElementById('screeningProjectsList');
    const budgetsContainer = document.getElementById('screeningGroupBudgets');
    if (!container || !appData) return;

    // Render Balances for each department
    if (budgetsContainer) {
        budgetsContainer.innerHTML = appData.departmentAllocations.map(a => {
            const deptProjects = appData.projects.filter(p => p.department === a.department);
            const totalRequested = deptProjects.reduce((sum, p) => sum + (parseFloat(p.requested_budget) || 0), 0);
            const totalApproved = deptProjects.reduce((sum, p) => sum + (parseFloat(p.approved_budget) || 0), 0);
            const alloc = parseFloat(a.allocated_amount);
            const diff = alloc - (totalApproved > 0 ? totalApproved : totalRequested);

            return `
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-bold text-slate-500">${a.department_name}</span>
                    <div class="flex justify-between text-xs mt-2">
                        <span class="text-slate-400">กรอบวงเงิน:</span>
                        <span class="font-bold text-slate-800">${formatBaht(alloc)} ฿</span>
                    </div>
                    <div class="flex justify-between text-xs mt-1">
                        <span class="text-slate-400">ยอดโครงการรวม:</span>
                        <span class="font-bold text-blue-900">${formatBaht(totalApproved > 0 ? totalApproved : totalRequested)} ฿</span>
                    </div>
                    <div class="flex justify-between text-xs mt-1 pt-1 border-t border-slate-100">
                        <span class="text-slate-400">คงเหลือกรอบ:</span>
                        <span class="font-bold ${diff < 0 ? 'text-red-600' : 'text-emerald-700'}">${formatBaht(diff)} ฿</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    const pending = appData.projects.filter(p => p.status === 'submitted' || p.status === 'revision_requested');
    document.getElementById('screeningPendingCount').innerText = `${pending.length} รายการ`;

    if (pending.length === 0) {
        container.innerHTML = `<div class="p-8 text-center text-slate-400 text-xs font-semibold">ไม่มีโครงการรอการกลั่นกรองในขณะนี้</div>`;
        return;
    }

    container.innerHTML = pending.map(p => `
        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
            <div>
                <h4 class="text-xs font-bold text-slate-900">${p.name}</h4>
                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                    <span class="font-semibold text-blue-700">${DEPT_NAMES[p.department] || p.department}</span>
                    <span>•</span>
                    <span>ผู้เสนอ: ${p.proposer_name}</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="text-xs font-black text-slate-800">${formatBaht(p.requested_budget)} บาท</span>
                    <p class="text-[10px] text-slate-400">วงเงินที่ขอ</p>
                </div>
                <button onclick="openScreeningModal(${p.id})" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    กลั่นกรอง / ปรับลดงบ
                </button>
            </div>
        </div>
    `).join('');
}

function openScreeningModal(id) {
    const p = appData.projects.find(x => x.id == id);
    if (!p) return;

    document.getElementById('screen_project_id').value = p.id;
    document.getElementById('screen_project_name').innerText = p.name;
    document.getElementById('screen_requested_amount').innerText = `${formatBaht(p.requested_budget)} บาท`;
    document.getElementById('screen_dept_name').innerText = DEPT_NAMES[p.department] || p.department;
    document.getElementById('screen_adjusted_budget').value = p.approved_budget > 0 ? p.approved_budget : p.requested_budget;
    document.getElementById('screen_notes').value = p.screening_note || '';

    document.getElementById('screeningModal').classList.remove('hidden');
}

function closeScreeningModal() {
    document.getElementById('screeningModal').classList.add('hidden');
}

async function submitScreening(action) {
    const pId = document.getElementById('screen_project_id').value;
    const adjustedBudget = parseFloat(document.getElementById('screen_adjusted_budget').value) || 0;
    const note = document.getElementById('screen_notes').value.trim();

    try {
        const res = await fetch('/api/plan/screen_project.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                project_id: pId,
                adjusted_budget: adjustedBudget,
                screening_note: note,
                action: action
            })
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeScreeningModal();
            showToast('บันทึกการกลั่นกรองและปรับวงเงินเรียบร้อย', 'success');
            await loadData(selectedYearId);
        }
    } catch (e) {
        console.error(e);
    }
}

// 10. Director Approval
function renderApprovalView() {
    const container = document.getElementById('approvalProjectsList');
    if (!container || !appData) return;

    const screened = appData.projects.filter(p => p.status === 'screened');
    document.getElementById('approvalPendingCount').innerText = `${screened.length} รายการ`;

    if (screened.length === 0) {
        container.innerHTML = `<div class="p-8 text-center text-slate-400 text-xs font-semibold">ไม่มีโครงการรอการอนุมัติในขณะนี้</div>`;
        return;
    }

    container.innerHTML = screened.map(p => `
        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
            <div>
                <h4 class="text-xs font-bold text-slate-900">${p.name}</h4>
                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                    <span class="font-semibold text-blue-700">${DEPT_NAMES[p.department] || p.department}</span>
                    <span>•</span>
                    <span>ผ่านการกลั่นกรอง: ${formatBaht(p.approved_budget)} บาท</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openApprovalModal(${p.id})" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> ลงนามอนุมัติ (ผอ.)
                </button>
            </div>
        </div>
    `).join('');

    lucide.createIcons();
}

function openApprovalModal(id) {
    const p = appData.projects.find(x => x.id == id);
    if (!p) return;

    document.getElementById('appr_project_id').value = p.id;
    document.getElementById('appr_project_name').innerText = p.name;
    document.getElementById('appr_req_budget').innerText = `${formatBaht(p.requested_budget)} บาท`;
    document.getElementById('appr_screened_budget').innerText = `${formatBaht(p.approved_budget)} บาท`;
    document.getElementById('appr_screening_note').innerText = p.screening_note ? `ข้อคิดเห็นกลั่นกรอง: ${p.screening_note}` : '';
    document.getElementById('appr_final_budget').value = p.approved_budget;
    document.getElementById('appr_director_note').value = p.director_note || 'อนุมัติตามที่เสนอ ขอให้ดำเนินการตามแผนงานและระเบียบพัสดุ';

    document.getElementById('approvalModal').classList.remove('hidden');
    lucide.createIcons();
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
}

async function submitApproval(action) {
    const pId = document.getElementById('appr_project_id').value;
    const finalBudget = parseFloat(document.getElementById('appr_final_budget').value) || 0;
    const note = document.getElementById('appr_director_note').value.trim();

    try {
        const res = await fetch('/api/plan/approve_project.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                project_id: pId,
                action: action,
                approved_budget: finalBudget,
                director_note: note
            })
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeApprovalModal();
            showToast('ลงนามอนุมัติโครงการและบรรจุในเล่มแผนปฏิบัติการสำเร็จ', 'success');
            await loadData(selectedYearId);
        }
    } catch (e) {
        console.error(e);
    }
}

async function approveAllScreened() {
    const screened = appData.projects.filter(p => p.status === 'screened');
    if (screened.length === 0) {
        alert('ไม่มีโครงการที่รออนุมัติ');
        return;
    }
    if (!confirm(`ต้องการอนุมัติทุกโครงการที่ผ่านการกลั่นกรองจำนวน ${screened.length} โครงการ ใช่หรือไม่?`)) return;

    for (const p of screened) {
        await fetch('/api/plan/approve_project.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                project_id: p.id,
                action: 'approved',
                approved_budget: p.approved_budget,
                director_note: 'อนุมัติโดยมติคณะกรรมการบริหารแผนสถานศึกษา'
            })
        });
    }

    showToast(`อนุมัติครบทั้ง ${screened.length} โครงการเรียบร้อยแล้ว`, 'success');
    await loadData(selectedYearId);
}

// 11. Project Tracking View
function renderTrackingView() {
    const container = document.getElementById('trackingCardsContainer');
    if (!container || !appData) return;

    container.innerHTML = appData.projects.map(p => {
        const progress = p.progress_percentage || 0;
        return `
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-slate-500">${p.code || 'P'}</span>
                        <span class="text-xs font-extrabold text-blue-600">${progress}%</span>
                    </div>
                    <h3 class="text-xs font-bold text-slate-900 mt-1">${p.name}</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">${DEPT_NAMES[p.department] || p.department} • ${p.proposer_name}</p>

                    <div class="w-full bg-slate-100 rounded-full h-2 mt-3">
                        <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: ${progress}%"></div>
                    </div>

                    <div class="mt-3 p-2.5 bg-slate-50 rounded-xl text-[11px] text-slate-600 space-y-1">
                        <p><strong>ผลการดำเนินงาน:</strong> ${p.results_summary || 'ยังไม่มีรายงานผล'}</p>
                        ${p.obstacles ? `<p class="text-red-700"><strong>ปัญหา/อุปสรรค:</strong> ${p.obstacles}</p>` : ''}
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-end">
                    <button onclick="openTrackingModal(${p.id})" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> รายงานความก้าวหน้า
                    </button>
                </div>
            </div>
        `;
    }).join('');

    lucide.createIcons();
}

function openTrackingModal(id) {
    const p = appData.projects.find(x => x.id == id);
    if (!p) return;

    document.getElementById('track_project_id').value = p.id;
    document.getElementById('track_project_name').innerText = p.name;
    document.getElementById('track_pct_range').value = p.progress_percentage || 0;
    document.getElementById('track_pct_text').innerText = `${p.progress_percentage || 0}%`;
    document.getElementById('track_exec_status').value = p.execution_status || 'in_progress';
    document.getElementById('track_results').value = p.results_summary || '';
    document.getElementById('track_obstacles').value = p.obstacles || '';
    document.getElementById('track_recommendations').value = p.recommendations || '';

    document.getElementById('trackingModal').classList.remove('hidden');
}

function closeTrackingModal() {
    document.getElementById('trackingModal').classList.add('hidden');
}

async function handleTrackingSubmit(e) {
    e.preventDefault();
    const payload = {
        project_id: document.getElementById('track_project_id').value,
        progress_percentage: parseInt(document.getElementById('track_pct_range').value) || 0,
        execution_status: document.getElementById('track_exec_status').value,
        results_summary: document.getElementById('track_results').value.trim(),
        obstacles: document.getElementById('track_obstacles').value.trim(),
        recommendations: document.getElementById('track_recommendations').value.trim(),
        recorded_by: currentUser.name
    };

    try {
        const res = await fetch('/api/plan/save_progress.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeTrackingModal();
            showToast('บันทึกการติดตามความก้าวหน้าเรียบร้อยแล้ว', 'success');
            await loadData(selectedYearId);
        }
    } catch (e) {
        console.error(e);
    }
}

// 12. Expenses & Actual Disbursement
function renderExpensesView() {
    const s = appData.summary;
    document.getElementById('exp-total-approved').innerText = formatBaht(s.totalApprovedBudget) + ' ฿';
    document.getElementById('exp-total-spent').innerText = formatBaht(s.totalSpentAcrossAll) + ' ฿';
    document.getElementById('exp-total-remaining').innerText = formatBaht(s.totalApprovedBudget - s.totalSpentAcrossAll) + ' ฿';

    // Populate Filter Selector
    const filterSel = document.getElementById('expenseFilterProject');
    if (filterSel) {
        filterSel.innerHTML = '<option value="all">ทุกโครงการ</option>' + appData.projects.map(p => `
            <option value="${p.id}">${p.code || ''} ${p.name}</option>
        `).join('');
    }

    renderExpensesTable();
}

function renderExpensesTable() {
    const tbody = document.getElementById('expensesTableBody');
    if (!tbody || !appData) return;

    const filterVal = document.getElementById('expenseFilterProject').value;
    let allExpenses = [];

    appData.projects.forEach(p => {
        if (filterVal !== 'all' && p.id != filterVal) return;
        if (p.expenses && Array.isArray(p.expenses)) {
            p.expenses.forEach(e => {
                allExpenses.push({ ...e, projectName: p.name });
            });
        }
    });

    if (allExpenses.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="p-6 text-center text-slate-400">ยังไม่มีประวัติการเบิกจ่ายตามเงื่อนไขที่เลือก</td></tr>`;
        return;
    }

    tbody.innerHTML = allExpenses.map(e => `
        <tr class="hover:bg-slate-50 transition">
            <td class="p-3.5 text-slate-600 font-medium">${e.expense_date}</td>
            <td class="p-3.5 font-mono font-bold text-slate-700">${e.doc_number || '-'}</td>
            <td class="p-3.5 font-semibold text-slate-800 text-xs">${e.projectName}</td>
            <td class="p-3.5 text-slate-700">${e.title}</td>
            <td class="p-3.5 text-slate-600">${CATEGORY_NAMES[e.category] || e.category}</td>
            <td class="p-3.5 text-right font-black text-amber-700">${formatBaht(e.amount)}</td>
            <td class="p-3.5 text-slate-600">${e.disbursed_by || '-'}</td>
            <td class="p-3.5 text-center">
                <button onclick="deleteExpenseItem(${e.id})" class="text-red-400 hover:text-red-600">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        </tr>
    `).join('');

    lucide.createIcons();
}

function openNewExpenseModal() {
    const sel = document.getElementById('exp_project_id');
    sel.innerHTML = appData.projects.map(p => `
        <option value="${p.id}">${p.name} (อนุมัติ: ${formatBaht(p.approved_budget)} ฿)</option>
    `).join('');

    if (appData.projects.length > 0) {
        updateExpenseModalProjectInfo(appData.projects[0].id);
    }

    document.getElementById('exp_date').value = new Date().toISOString().split('T')[0];
    document.getElementById('exp_doc_number').value = `ฎีกาที่ ${Math.floor(10 + Math.random() * 90)}/${appData.currentFiscalYear.year}`;
    document.getElementById('exp_title').value = '';
    document.getElementById('exp_amount').value = '';
    document.getElementById('exp_disbursed_by').value = currentUser.name;
    document.getElementById('exp_receipt_note').value = '';

    document.getElementById('expenseModal').classList.remove('hidden');
}

function updateExpenseModalProjectInfo(projectId) {
    const p = appData.projects.find(x => x.id == projectId);
    if (!p) return;
    const fin = p.financials || { approved: p.approved_budget, remaining: p.approved_budget };
    document.getElementById('expModalApprovedBudget').innerText = `${formatBaht(fin.approved)} บาท`;
    document.getElementById('expModalRemainingBudget').innerText = `${formatBaht(fin.remaining)} บาท`;
}

function closeExpenseModal() {
    document.getElementById('expenseModal').classList.add('hidden');
}

async function handleExpenseSubmit(e) {
    e.preventDefault();
    const payload = {
        project_id: document.getElementById('exp_project_id').value,
        expense_date: document.getElementById('exp_date').value,
        doc_number: document.getElementById('exp_doc_number').value.trim(),
        title: document.getElementById('exp_title').value.trim(),
        category: document.getElementById('exp_category').value,
        amount: parseFloat(document.getElementById('exp_amount').value) || 0,
        disbursed_by: document.getElementById('exp_disbursed_by').value.trim(),
        receipt_note: document.getElementById('exp_receipt_note').value.trim()
    };

    try {
        const res = await fetch('/api/plan/save_expense.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeExpenseModal();
            showToast('บันทึกรายการเบิกจ่ายจริงและคำนวณงบคงเหลือใหม่สำเร็จ', 'success');
            await loadData(selectedYearId);
        }
    } catch (err) {
        console.error(err);
    }
}

async function deleteExpenseItem(id) {
    if (!confirm('ต้องการลบรายการเบิกจ่ายนี้หรือไม่?')) return;
    try {
        const res = await fetch('/api/plan/delete_expense.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast('ลบรายการเบิกจ่ายสำเร็จ', 'success');
            await loadData(selectedYearId);
        }
    } catch (e) {
        console.error(e);
    }
}

// 13. Print & Reports
function renderPrintReportsView() {
    const sel = document.getElementById('printProjectSelector');
    if (!sel || !appData) return;

    sel.innerHTML = appData.projects.map(p => `
        <option value="${p.id}">${p.code || 'P'} - ${p.name} (${DEPT_NAMES[p.department] || p.department})</option>
    `).join('');
}

function printSelectedProject() {
    const sel = document.getElementById('printProjectSelector');
    if (!sel || !sel.value) return;
    window.open(`print_project.php?id=${sel.value}`, '_blank');
}

// 14. History & Copy Plan
function renderHistoryView() {
    const container = document.getElementById('historyYearsList');
    if (!container || !appData) return;

    container.innerHTML = appData.fiscalYears.map(fy => `
        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl ${fy.is_current ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700'}">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">ปีงบประมาณ พ.ศ. ${fy.year}</h4>
                    <p class="text-[11px] text-slate-500">${fy.start_date} ถึง ${fy.end_date}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                ${fy.is_current ? '<span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[10px] font-bold">ปีปัจจุบัน</span>' : '<span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold">ข้อมูลย้อนหลัง</span>'}
                <button onclick="onFiscalYearChange(${fy.id})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                    ดูข้อมูลปีนี้
                </button>
            </div>
        </div>
    `).join('');

    lucide.createIcons();
}

async function executeCopyPlan() {
    const fromYear = document.getElementById('copyFromYear').value;
    const toYear = document.getElementById('copyToYear').value;

    if (!confirm(`ต้องการคัดลอกโครงการทั้งหมดจากปีงบประมาณที่เลือก มายังปีงบประมาณปัจจุบัน ใช่หรือไม่?`)) return;

    try {
        const res = await fetch('/api/plan/copy_year_plan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                from_year_id: fromYear,
                to_year_id: toYear
            })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showToast(data.message || 'คัดลอกแผนงานสำเร็จเรียบร้อย', 'success');
            await loadData(selectedYearId);
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
        }
    } catch (e) {
        console.error(e);
    }
}

// 15. Users & Roles
function renderUsersView() {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;

    const users = (appData && Array.isArray(appData.users)) ? appData.users : [];
    if (users.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="p-6 text-center text-slate-400">ยังไม่มีข้อมูลบุคลากรในฐานข้อมูล หรือกำลังโหลดข้อมูล...</td></tr>`;
        return;
    }

    tbody.innerHTML = users.map(u => `
        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
            <td class="p-3.5 font-bold text-slate-900">${u.name || '-'}</td>
            <td class="p-3.5 text-slate-600">${u.position || '-'}</td>
            <td class="p-3.5 text-slate-600">${DEPT_NAMES[u.department] || u.department || '-'}</td>
            <td class="p-3.5">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-800">
                    ${ROLE_TITLES[u.role] || u.role || 'ผู้ใช้งาน'}
                </span>
            </td>
            <td class="p-3.5 text-center text-xs text-slate-600 font-mono">
                ${u.username || u.id_card || '-'}
            </td>
        </tr>
    `).join('');
}

function openNewUserModal() {
    document.getElementById('usr_name').value = '';
    document.getElementById('usr_position').value = '';
    document.getElementById('userModal').classList.remove('hidden');
}
function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
}

async function handleUserSubmit(e) {
    e.preventDefault();
    const payload = {
        name: document.getElementById('usr_name').value.trim(),
        position: document.getElementById('usr_position').value.trim(),
        department: document.getElementById('usr_department').value,
        role: document.getElementById('usr_role').value
    };

    try {
        const res = await fetch('/api/plan/save_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            closeUserModal();
            showToast('บันทึกข้อมูลบุคลากรเรียบร้อยแล้ว', 'success');
            renderUsersView();
        }
    } catch (err) {
        console.error(err);
    }
}

// ==========================================
// 12. SUBSIDIES CALCULATOR (เจ้าหน้าที่แผนงาน)
// ==========================================
async function loadSubsidyData() {
    try {
        const res = await fetch(`/api/plan/get_subsidy_data.php?fiscal_year_id=${selectedYearId}`);
        const result = await res.json();
        if (result.status === 'success') {
            const data = result.subsidies || result.data || {};
            const levels = ['kindergarten', 'primary', 'lower_secondary', 'upper_secondary'];
            levels.forEach(lvl => {
                const item = data[lvl];
                if (item) {
                    const countEl = document.getElementById(`sub_count_${lvl}`);
                    const rateEl = document.getElementById(`sub_rate_${lvl}`);
                    const smallRateEl = document.getElementById(`sub_small_rate_${lvl}`);
                    const devRateEl = document.getElementById(`sub_dev_rate_${lvl}`);

                    if (countEl) countEl.value = item.student_count || 0;
                    if (rateEl) rateEl.value = item.subsidy_rate || 0;
                    if (smallRateEl) smallRateEl.value = item.small_school_subsidy ?? 0;
                    if (devRateEl) devRateEl.value = item.dev_rate ?? item.student_dev_rate ?? 0;
                }
            });
            recalcSubsidiesLocal();
        }
    } catch (err) {
        console.error('Error loading subsidies:', err);
    }
}

function setSmallSchoolRatePreset(rate) {
    const levels = ['kindergarten', 'primary', 'lower_secondary', 'upper_secondary'];
    levels.forEach(lvl => {
        const el = document.getElementById(`sub_small_rate_${lvl}`);
        if (el) el.value = rate;
    });
    recalcSubsidiesLocal();
    showToast(`กำหนดเงินเพิ่ม รร. ขนาดเล็ก ${rate.toLocaleString()} บาท/คน ทุกระดับชั้นเรียบร้อย`, 'info');
}

function recalcSubsidiesLocal() {
    const levels = ['kindergarten', 'primary', 'lower_secondary', 'upper_secondary'];
    let totalStudents = 0;
    let grandNormalSubsidy = 0;
    let grandSmallSubsidy = 0;
    let grandSubsidy = 0;
    let grandDev = 0;

    levels.forEach(lvl => {
        const count = parseFloat(document.getElementById(`sub_count_${lvl}`)?.value) || 0;
        const rate = parseFloat(document.getElementById(`sub_rate_${lvl}`)?.value) || 0;
        const smallRate = parseFloat(document.getElementById(`sub_small_rate_${lvl}`)?.value) || 0;
        const devRate = parseFloat(document.getElementById(`sub_dev_rate_${lvl}`)?.value) || 0;

        const normalTotal = count * rate;
        const smallTotal = count * smallRate;
        const subTotal = normalTotal + smallTotal;
        const devTotal = count * devRate;
        const rowGrand = subTotal + devTotal;

        const subEl = document.getElementById(`sub_total_subsidy_${lvl}`);
        const devEl = document.getElementById(`sub_total_dev_${lvl}`);
        const grandEl = document.getElementById(`sub_grand_${lvl}`);

        if (subEl) subEl.innerText = formatBaht(subTotal);
        if (devEl) devEl.innerText = formatBaht(devTotal);
        if (grandEl) grandEl.innerText = formatBaht(rowGrand);

        totalStudents += count;
        grandNormalSubsidy += normalTotal;
        grandSmallSubsidy += smallTotal;
        grandSubsidy += subTotal;
        grandDev += devTotal;
    });

    const netTotal = grandSubsidy + grandDev;

    // Small School Banner & Badge detection (< 120 students)
    const isSmall = totalStudents > 0 && totalStudents < 120;
    const banner = document.getElementById('smallSchoolBanner');
    const badge = document.getElementById('smallSchoolBadge');
    const sizeKpi = document.getElementById('sub-kpi-school-size');
    const studentCountKpi = document.getElementById('sub-kpi-student-count');

    if (banner) {
        if (isSmall) {
            banner.className = 'p-4 rounded-2xl border border-amber-300 bg-amber-50/90 text-amber-900 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-xs';
            if (badge) {
                badge.innerText = 'รร.ขนาดเล็ก (< 120 คน)';
                badge.className = 'text-[11px] font-bold px-2.5 py-1 rounded-lg bg-amber-500 text-white shadow-xs';
            }
        } else {
            banner.className = 'p-4 rounded-2xl border border-slate-200 bg-slate-50 text-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-3';
            if (badge) {
                badge.innerText = totalStudents >= 120 ? 'รร.ขนาดปกติ (≥ 120 คน)' : 'รอระบุจำนวน นร.';
                badge.className = 'text-[11px] font-bold px-2.5 py-1 rounded-lg bg-slate-200 text-slate-700';
            }
        }
    }
    if (studentCountKpi) studentCountKpi.innerText = `${totalStudents.toLocaleString('th-TH')} คน`;
    if (sizeKpi) sizeKpi.innerText = isSmall ? 'รร.ขนาดเล็ก (< 120 คน)' : (totalStudents >= 120 ? 'รร.ขนาดปกติ (≥ 120 คน)' : 'ยังไม่ระบุนักเรียน');

    // Update KPI Summary Cards
    const kpiStudents = document.getElementById('sub-kpi-students');
    const kpiSubsidy = document.getElementById('sub-kpi-subsidy-total');
    const kpiSmall = document.getElementById('sub-kpi-small-total');
    const kpiDev = document.getElementById('sub-kpi-dev-total');
    const kpiGrand = document.getElementById('sub-kpi-grand-total');

    if (kpiStudents) kpiStudents.innerText = `${totalStudents.toLocaleString('th-TH')} คน`;
    if (kpiSubsidy) kpiSubsidy.innerText = `${formatBaht(grandSubsidy)} ฿`;
    if (kpiSmall) kpiSmall.innerText = `${formatBaht(grandSmallSubsidy)} ฿`;
    if (kpiDev) kpiDev.innerText = `${formatBaht(grandDev)} ฿`;
    if (kpiGrand) kpiGrand.innerText = `${formatBaht(netTotal)} ฿`;

    // Update Footers
    const footCount = document.getElementById('sub_foot_count');
    const footSmall = document.getElementById('sub_foot_small_subsidy');
    const footSubsidy = document.getElementById('sub_foot_subsidy');
    const footDev = document.getElementById('sub_foot_dev');
    const footGrand = document.getElementById('sub_foot_grand');

    if (footCount) footCount.innerText = `${totalStudents.toLocaleString('th-TH')} คน`;
    if (footSmall) footSmall.innerText = formatBaht(grandSmallSubsidy);
    if (footSubsidy) footSubsidy.innerText = formatBaht(grandSubsidy);
    if (footDev) footDev.innerText = formatBaht(grandDev);
    if (footGrand) footGrand.innerText = formatBaht(netTotal);

    // Keep allocation reference in sync if present
    const calcRefText = document.getElementById('allocCalcReferenceText');
    if (calcRefText) calcRefText.innerText = `ยอดจากการคำนวณรายหัว & กพพ.: ${formatBaht(netTotal)} บาท`;
}

function getSubsidyPayload() {
    const levels = ['kindergarten', 'primary', 'lower_secondary', 'upper_secondary'];
    const rates = {};
    levels.forEach(lvl => {
        rates[lvl] = {
            student_count: parseFloat(document.getElementById(`sub_count_${lvl}`)?.value) || 0,
            subsidy_rate: parseFloat(document.getElementById(`sub_rate_${lvl}`)?.value) || 0,
            small_school_subsidy: parseFloat(document.getElementById(`sub_small_rate_${lvl}`)?.value) || 0,
            student_dev_rate: parseFloat(document.getElementById(`sub_dev_rate_${lvl}`)?.value) || 0
        };
    });
    return {
        fiscal_year_id: selectedYearId,
        rates: rates
    };
}

async function saveSubsidyDataOnly() {
    try {
        const payload = getSubsidyPayload();
        const res = await fetch('/api/plan/save_subsidy_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await res.json();
        if (result.status === 'success') {
            showToast('บันทึกข้อมูลนักเรียน เงินอุดหนุนรายหัว และเงินเพิ่ม รร.ขนาดเล็ก สำเร็จ', 'success');
            return true;
        } else {
            showToast(result.message || 'บันทึกไม่สำเร็จ', 'error');
            return false;
        }
    } catch (err) {
        console.error(err);
        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
        return false;
    }
}

async function applySubsidiesToBudget() {
    try {
        const saved = await saveSubsidyDataOnly();
        if (!saved) return;

        const res = await fetch('/api/plan/apply_subsidies_to_budget.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fiscal_year_id: selectedYearId })
        });
        const result = await res.json();
        if (result.status === 'success') {
            showToast('นำยอดเงินคำนวณเข้าเป็นแหล่งงบประมาณ และเตรียมจัดสรร 5 ช่องงาน เรียบร้อยแล้ว', 'success');
            await loadData(selectedYearId);
            switchTab('allocation');
        } else {
            showToast(result.message || 'ไม่สามารถตัดงบได้', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('เกิดข้อผิดพลาดในการประมวลผล', 'error');
    }
}

// ==========================================
// 13. SCHOOL SETTINGS & HEADER LOGO (Admin โรงเรียน)
// ==========================================
async function loadSchoolSettings() {
    try {
        const res = await fetch('/api/admin/get_school_info.php');
        const result = await res.json();
        const school = (result.status === 'success' && result.data) ? result.data : (appData ? appData.school : null);
        
        if (school) {
            document.getElementById('set_smis_code').value = school.smis_code || '10310001';
            document.getElementById('set_school_name').value = school.name || '';
            document.getElementById('set_affiliation').value = school.affiliation || '';
            document.getElementById('set_logo_url').value = school.logo_url || '';
            document.getElementById('set_logo_preview').src = school.logo_url || 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png';
            document.getElementById('set_address').value = school.address || '';
            document.getElementById('set_subdistrict').value = school.subdistrict || '';
            document.getElementById('set_district').value = school.district || '';
            document.getElementById('set_province').value = school.province || '';
            document.getElementById('set_postal_code').value = school.postal_code || '';
            document.getElementById('set_phone').value = school.phone || '';
            document.getElementById('set_email').value = school.email || '';
            document.getElementById('set_director_name').value = school.director_name || '';
            document.getElementById('set_director_position').value = school.director_position || '';
            document.getElementById('set_plan_officer_name').value = school.plan_officer_name || '';

            updateHeaderPreview();
        }
    } catch (err) {
        console.error('Error loading school settings:', err);
    }
}

function updateHeaderPreview() {
    const name = document.getElementById('set_school_name').value || 'ชื่อโรงเรียน';
    const smis = document.getElementById('set_smis_code').value || '10310001';
    const aff = document.getElementById('set_affiliation').value || 'สังกัดสถานศึกษา';
    const logoUrl = document.getElementById('set_logo_url').value || 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png';

    const prevLogo = document.getElementById('previewHeaderLogo');
    const prevText = document.getElementById('previewHeaderSchoolText');

    if (prevLogo) prevLogo.src = logoUrl;
    if (prevText) prevText.innerText = `${name} (รหัส SMIS: ${smis}) • ${aff}`;
}

function onLogoUrlInput(url) {
    const preview = document.getElementById('set_logo_preview');
    if (preview && url) {
        preview.src = url;
    }
    updateHeaderPreview();
}

function selectPresetLogo(url) {
    document.getElementById('set_logo_url').value = url;
    document.getElementById('set_logo_preview').src = url;
    updateHeaderPreview();
}

async function handleSaveSchoolSettings(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveSchoolSettings');
    const originalText = btn.innerHTML;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> กำลังบันทึก...`;
    lucide.createIcons();

    const payload = {
        name: document.getElementById('set_school_name').value.trim(),
        affiliation: document.getElementById('set_affiliation').value.trim(),
        logo_url: document.getElementById('set_logo_url').value.trim(),
        address: document.getElementById('set_address').value.trim(),
        subdistrict: document.getElementById('set_subdistrict').value.trim(),
        district: document.getElementById('set_district').value.trim(),
        province: document.getElementById('set_province').value.trim(),
        postal_code: document.getElementById('set_postal_code').value.trim(),
        phone: document.getElementById('set_phone').value.trim(),
        email: document.getElementById('set_email').value.trim(),
        director_name: document.getElementById('set_director_name').value.trim(),
        director_position: document.getElementById('set_director_position').value.trim(),
        plan_officer_name: document.getElementById('set_plan_officer_name').value.trim()
    };

    try {
        const res = await fetch('/api/school/save_settings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await res.json();
        if (result.status === 'success') {
            showToast('บันทึกข้อมูลสถานศึกษาและอัปเดต Header เรียบร้อยแล้ว', 'success');

            // Instantly update header branding in real time
            const headerLogo = document.getElementById('headerSchoolLogo');
            const headerName = document.getElementById('headerSchoolName');
            const smis = document.getElementById('set_smis_code').value;

            if (headerLogo && payload.logo_url) headerLogo.src = payload.logo_url;
            if (headerName) {
                headerName.innerHTML = `${payload.name} (รหัส SMIS: <span id="headerSmisCode">${smis}</span>) • ${payload.affiliation}`;
            }

            if (appData && appData.school) {
                Object.assign(appData.school, payload);
            }
        } else {
            showToast(result.message || 'บันทึกไม่สำเร็จ', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('เกิดข้อผิดพลาดในการบันทึกข้อมูล', 'error');
    } finally {
        btn.innerHTML = originalText;
        lucide.createIcons();
    }
}

// ==========================================
// 14. SUPER ADMIN CONTROL CENTER (รหัส SMIS & Database Update)
// ==========================================

let currentAssignAdminSchoolId = null;

// Toggle Password Visibility
function togglePasswordVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

// Toggle DB Config Panel
function toggleDbConfigPanel() {
    const panel = document.getElementById('dbConfigPanel');
    if (panel) {
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
}
window.toggleDbConfigPanel = toggleDbConfigPanel;

// Load Super Admin Credentials & Database Config
async function loadSuperAdminCredentials() {
    try {
        const res = await fetch('/api/superadmin/get_credentials.php');
        if (!res.ok) {
            console.warn('Super Admin credentials API returned HTTP', res.status);
            return;
        }
        const data = await res.json();
        if (data.status === 'success') {
            const u = data.user || {};
            const saUserEl = document.getElementById('sa_username');
            const saNameEl = document.getElementById('sa_name');
            const saPosEl = document.getElementById('sa_position');
            const saPhoneEl = document.getElementById('sa_phone');
            const saEmailEl = document.getElementById('sa_email');
            const saPwdStatusEl = document.getElementById('sa-pwd-status');

            if (saUserEl) saUserEl.value = u.username || 'superadmin';
            if (saNameEl) saNameEl.value = u.name || '';
            if (saPosEl) saPosEl.value = u.position || '';
            if (saPhoneEl) saPhoneEl.value = u.phone || '';
            if (saEmailEl) saEmailEl.value = u.email || '';

            if (saPwdStatusEl) {
                if (u.has_custom_password) {
                    saPwdStatusEl.className = "px-2 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px]";
                    saPwdStatusEl.innerText = "✓ ตั้งค่ารหัสผ่านส่วนตัวแล้ว";
                } else {
                    saPwdStatusEl.className = "px-2 py-0.5 rounded-full font-bold bg-amber-50 text-amber-700 border border-amber-200 text-[11px]";
                    saPwdStatusEl.innerText = "เริ่มต้น (password123)";
                }
            }

            // DB Config
            const db = data.database || {};
            const cfg = db.config || {};
            if (document.getElementById('db_host') && cfg.host) document.getElementById('db_host').value = cfg.host;
            if (document.getElementById('db_port') && cfg.port) document.getElementById('db_port').value = cfg.port;
            if (document.getElementById('db_name') && cfg.database) document.getElementById('db_name').value = cfg.database;
            if (document.getElementById('db_user') && cfg.user) document.getElementById('db_user').value = cfg.user;

            const badge = document.getElementById('dbConnBadge');
            if (badge) {
                if (db.connected) {
                    badge.className = "px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30";
                    badge.innerText = "✓ เชื่อมต่อ MySQL Server สำเร็จ";
                } else {
                    badge.className = "px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30";
                    badge.innerText = "โหมดระบบพร้อมใช้งาน (MySQL ภายนอกรอเชื่อมต่อ)";
                }
            }
        }
    } catch (err) {
        console.error('Error loading Super Admin credentials:', err);
    }
}
window.loadSuperAdminCredentials = loadSuperAdminCredentials;

// Handle Super Admin Credentials Update
async function handleSuperAdminCredentialsSubmit(event) {
    event.preventDefault();
    const btn = document.getElementById('btnSaveSaCredentials');
    const alertBox = document.getElementById('saCredentialsAlert');
    const username = (document.getElementById('sa_username').value || '').trim();
    const newPass = (document.getElementById('sa_new_password').value || '').trim();
    const confirmPass = (document.getElementById('sa_confirm_password').value || '').trim();
    const name = (document.getElementById('sa_name').value || '').trim();
    const position = (document.getElementById('sa_position').value || '').trim();
    const phone = (document.getElementById('sa_phone').value || '').trim();
    const email = (document.getElementById('sa_email').value || '').trim();

    if (!username) {
        showToast('กรุณาระบุ Username ของ Super Admin', 'error');
        return;
    }

    if (newPass) {
        if (newPass.length < 6) {
            showToast('รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร', 'error');
            return;
        }
        if (newPass !== confirmPass) {
            showToast('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน', 'error');
            return;
        }
    }

    const origBtnHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> <span>กำลังบันทึกข้อมูล...</span>`;
    if (window.lucide) lucide.createIcons();

    try {
        const res = await fetch('/api/superadmin/update_credentials.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username,
                new_password: newPass,
                name,
                position,
                phone,
                email
            })
        });
        const data = await res.json();

        if (data.status === 'success') {
            if (alertBox) {
                alertBox.className = "p-3 rounded-xl text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-800";
                alertBox.innerHTML = `✓ ${data.message}`;
                alertBox.classList.remove('hidden');
            }
            showToast(data.message, 'success');

            // Clear password fields
            document.getElementById('sa_new_password').value = '';
            document.getElementById('sa_confirm_password').value = '';

            // Update session user in local storage if currently logged in as super admin
            const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
            if (currentUser.role === 'super_admin' || currentUser.id === 1) {
                currentUser.username = username;
                if (name) currentUser.name = name;
                if (position) currentUser.position = position;
                localStorage.setItem('currentUser', JSON.stringify(currentUser));
                
                // Update header display
                const userNameDisplay = document.getElementById('userNameDisplay');
                if (userNameDisplay) userNameDisplay.innerText = name || username;
            }

            // Reload status badge
            await loadSuperAdminCredentials();
        } else {
            if (alertBox) {
                alertBox.className = "p-3 rounded-xl text-xs font-semibold bg-red-50 border border-red-200 text-red-800";
                alertBox.innerHTML = `✗ ${data.message || 'บันทึกไม่สำเร็จ'}`;
                alertBox.classList.remove('hidden');
            }
            showToast(data.message || 'บันทึกไม่สำเร็จ', 'error');
        }
    } catch (err) {
        console.error('Error updating Super Admin credentials:', err);
        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origBtnHtml;
        if (window.lucide) lucide.createIcons();
    }
}

// Test MySQL Database Connection
async function testDatabaseConnection() {
    const btn = document.getElementById('btnTestDb');
    const badge = document.getElementById('dbConnBadge');
    const msgEl = document.getElementById('dbTestMsg');

    const host = (document.getElementById('db_host').value || 'localhost').trim();
    const port = (document.getElementById('db_port').value || '3306').trim();
    const database = (document.getElementById('db_name').value || 'school_action_plan').trim();
    const user = (document.getElementById('db_user').value || 'root').trim();
    const password = document.getElementById('db_pass').value;

    const origBtnHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> <span>กำลังทดสอบ...</span>`;
    if (window.lucide) lucide.createIcons();

    try {
        const res = await fetch('/api/superadmin/test_db_connection.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ host, port, database, user, password })
        });
        const data = await res.json();

        if (data.connected || data.status === 'success') {
            badge.className = "px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30";
            badge.innerText = "✓ เชื่อมต่อสำเร็จ";
            msgEl.className = "text-[11px] text-emerald-300 font-semibold";
            msgEl.innerText = data.message || `เชื่อมต่อ MySQL (${host}:${port}/${database}) สำเร็จเรียบร้อย!`;
            showToast('เชื่อมต่อฐานข้อมูล MySQL สำเร็จ!', 'success');
        } else {
            badge.className = "px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-500/20 text-red-300 border border-red-500/30";
            badge.innerText = "✗ ไม่สามารถเชื่อมต่อได้";
            msgEl.className = "text-[11px] text-red-300";
            msgEl.innerText = data.message || 'ไม่สามารถเชื่อมต่อ MySQL ได้ ตรวจสอบ Host, Port, User, Password';
            showToast(data.message || 'ไม่สามารถเชื่อมต่อ MySQL ได้', 'error');
        }
    } catch (err) {
        badge.className = "px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-500/20 text-red-300 border border-red-500/30";
        badge.innerText = "✗ ข้อผิดพลาด";
        msgEl.className = "text-[11px] text-red-300";
        msgEl.innerText = `ข้อผิดพลาด: ${err.message}`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = origBtnHtml;
        if (window.lucide) lucide.createIcons();
    }
}

// Save MySQL Database Connection Configuration
async function saveDatabaseConnection() {
    const btn = document.getElementById('btnSaveDb');
    const msgEl = document.getElementById('dbTestMsg');

    const host = (document.getElementById('db_host').value || 'localhost').trim();
    const port = (document.getElementById('db_port').value || '3306').trim();
    const database = (document.getElementById('db_name').value || 'school_action_plan').trim();
    const user = (document.getElementById('db_user').value || 'root').trim();
    const password = document.getElementById('db_pass').value;

    const origBtnHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> <span>กำลังบันทึก...</span>`;
    if (window.lucide) lucide.createIcons();

    try {
        const res = await fetch('/api/superadmin/save_db_config.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ host, port, database, user, password })
        });
        const data = await res.json();

        if (data.status === 'success') {
            msgEl.className = "text-[11px] text-emerald-300 font-semibold";
            msgEl.innerText = `✓ บันทึกการตั้งค่าแล้ว (Host: ${host}, DB: ${database}) รันไฟล์ config.php สำเร็จ`;
            showToast('บันทึกการตั้งค่าการเชื่อมต่อฐานข้อมูลเรียบร้อยแล้ว', 'success');
            await loadSuperAdminCredentials();
        } else {
            showToast(data.message || 'บันทึกไม่สำเร็จ', 'error');
        }
    } catch (err) {
        showToast('เกิดข้อผิดพลาดในการบันทึกการตั้งค่า', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origBtnHtml;
        if (window.lucide) lucide.createIcons();
    }
}

// Auto-Install and Update Database Tables
async function runInstallDatabase() {
    const btn = document.getElementById('btnInstallDb');
    const resultBox = document.getElementById('dbInstallResultBox');
    const logContent = document.getElementById('dbInstallLogContent');
    const statusBadge = document.getElementById('dbInstallStatusBadge');

    const origBtnHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> <span>กำลังติดตั้งและอัปเดตฐานข้อมูล...</span>`;
    if (window.lucide) lucide.createIcons();

    if (resultBox) resultBox.classList.remove('hidden');
    if (logContent) {
        logContent.innerHTML = `<div class="text-indigo-300 animate-pulse">กำลังเริ่มกระบวนการตรวจสอบโครงสร้างฐานข้อมูลและบันทึกข้อมูลเริ่มต้น...</div>`;
    }
    if (statusBadge) {
        statusBadge.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300";
        statusBadge.innerText = "สถานะ: กำลังประมวลผล...";
    }

    try {
        const res = await fetch('/api/superadmin/install_database.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();

        if (data.status === 'success') {
            const steps = data.steps || [];
            let html = '';
            html += `<div class="text-emerald-400 font-bold mb-2">========================================================</div>`;
            html += `<div class="text-emerald-300 font-bold mb-1">🚀 เริ่มต้นกระบวนการติดตั้งและอัปเดตฐานข้อมูลระบบ (Database Migration)</div>`;
            
            if (data.live_mysql_executed) {
                html += `<div class="text-emerald-400 font-bold mb-2">✓ โหมด: รันคำสั่ง SQL สร้างตารางลงใน MySQL Server จริงสำเร็จเรียบร้อย</div>`;
            } else {
                html += `<div class="text-indigo-300 mb-2">ℹ โหมด: ตรวจสอบและเตรียมความพร้อมโครงสร้าง 10 ตารางในระบบสมบูรณ์</div>`;
            }

            if (data.message) {
                html += `<div class="text-slate-300 mb-2">รายละเอียด: ${data.message}</div>`;
            }

            html += `<div class="text-slate-400 mb-2">เวอร์ชันระบบ: ${data.database_version || '2026.1-SMIS8'} | เวลา: ${data.timestamp || new Date().toLocaleString('th-TH')}</div>`;
            
            if (data.superadmin) {
                html += `<div class="text-amber-300 mb-2">👑 บัญชี Super Admin: Username "${data.superadmin.username}" (${data.superadmin.name || 'ผู้ดูแลระบบ'}) ได้รับการซิงค์พร้อมใช้งาน</div>`;
            }

            html += `<div class="text-slate-500 mb-3">--------------------------------------------------------</div>`;

            steps.forEach(s => {
                html += `<div class="flex items-start gap-2 py-0.5">
                    <span class="text-emerald-400 font-bold shrink-0">[OK]</span>
                    <span class="text-amber-300 font-semibold shrink-0">ตาราง ${s.table}:</span>
                    <span class="text-slate-200">${s.details}</span>
                </div>`;
            });

            html += `<div class="text-slate-500 mt-2">--------------------------------------------------------</div>`;
            html += `<div class="text-emerald-400 font-bold mt-2">✓ ติดตั้งและอัปเดตโครงสร้างครบทั้ง ${data.total_tables || steps.length} ตารางหลักเรียบร้อยสมบูรณ์ พร้อมใช้งาน 100%</div>`;
            html += `<div class="text-emerald-400 font-bold">========================================================</div>`;

            if (logContent) logContent.innerHTML = html;
            if (statusBadge) {
                statusBadge.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300";
                statusBadge.innerText = `อัปเดตล่าสุด: ${data.timestamp || 'สมบูรณ์'}`;
            }

            showToast('ติดตั้งและอัปเดตโครงสร้างฐานข้อมูลสำเร็จครบ 10 ตาราง!', 'success');
            await loadSuperAdminCredentials();
        } else {
            if (logContent) {
                logContent.innerHTML = `<div class="text-red-400 font-bold">[ERROR] ${data.message || 'เกิดข้อผิดพลาดในการอัปเดตฐานข้อมูล'}</div>`;
            }
            if (statusBadge) {
                statusBadge.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/20 text-red-300";
                statusBadge.innerText = "สถานะ: เกิดข้อผิดพลาด";
            }
            showToast(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    } catch (err) {
        console.error('Database migration error:', err);
        if (logContent) {
            logContent.innerHTML = `<div class="text-red-400 font-bold">[ERROR] ไม่สามารถเชื่อมต่อกับบริการอัปเดตฐานข้อมูลได้ (${err.message})</div>`;
        }
        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origBtnHtml;
        if (window.lucide) lucide.createIcons();
    }
}

async function loadSuperAdminSchools() {
    try {
        const res = await fetch('/api/superadmin/get_schools.php');
        if (!res.ok) {
            console.warn('Super Admin schools API returned HTTP', res.status);
            return;
        }
        const result = await res.json();
        if (result.status === 'success') {
            const schools = result.schools || result.data || [];
            
            // Update stats
            const total = schools.length;
            const active = schools.filter(s => s.status === 'active' || s.is_active === 1).length;
            const pending = total - active;
            const totalUsers = (result.users && result.users.length) || (schools.length * 5);
            
            const saTotal = document.getElementById('sa-total-schools');
            const saActive = document.getElementById('sa-active-schools');
            const saPending = document.getElementById('sa-pending-schools');
            const saUsers = document.getElementById('sa-total-users');

            if (saTotal) saTotal.innerText = `${total} แห่ง`;
            if (saActive) saActive.innerText = `${active} แห่ง`;
            if (saPending) saPending.innerText = `${pending} แห่ง`;
            if (saUsers) saUsers.innerText = `${totalUsers} คน`;

            // Populate table
            const tbody = document.getElementById('superAdminSchoolsTableBody');
            if (tbody) {
                if (schools.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-slate-400">ยังไม่มีข้อมูลสถานศึกษาในระบบ</td></tr>`;
                    return;
                }
                tbody.innerHTML = schools.map(s => {
                    const isActive = (s.status === 'active' || s.is_active === 1);
                    const adminName = (s.assigned_admin_name || s.admin_name || '').trim();
                    const hasAdmin = adminName && adminName !== 'ผู้ดูแลระบบโรงเรียน' && !adminName.includes('ยังไม่ได้กำหนด');

                    return `
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-3.5 font-mono font-bold text-indigo-900">
                            <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-200/70 rounded-md">${s.smis_code}</span>
                        </td>
                        <td class="p-3.5 font-bold text-slate-900">
                            <div class="flex items-center gap-2.5">
                                <img src="${s.logo_url || 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png'}" 
                                     alt="logo" class="w-7 h-7 object-contain rounded-md border border-slate-100 bg-white p-0.5 shadow-xs">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">${s.name}</div>
                                    <div class="text-[11px] text-slate-400 font-normal">${s.district || 'เมือง'} • จ.${s.province || 'บุรีรัมย์'}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3.5 text-slate-600 text-[11px]">${s.affiliation || '-'}</td>
                        <td class="p-3.5">
                            ${hasAdmin ? `
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold shadow-2xs">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>${adminName}</span>
                                </div>
                            ` : `
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200/80 text-[11px] font-medium">
                                    <i data-lucide="user-x" class="w-3.5 h-3.5 text-amber-500"></i>
                                    <span>ยังไม่ได้แต่งตั้ง (รอครูสมัคร)</span>
                                </div>
                            `}
                        </td>
                        <td class="p-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold ${
                                isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                            }">
                                ${isActive ? 'เปิดใช้งาน (Active)' : 'ระงับชั่วคราว'}
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button onclick="openAssignAdminModal(${s.id})" 
                                        title="เลือกคุณครูที่สมัครสมาชิกมาเป็น Admin โรงเรียน"
                                        class="px-2.5 py-1.5 text-[11px] font-bold rounded-lg border transition flex items-center gap-1 shadow-2xs ${
                                            hasAdmin 
                                            ? 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' 
                                            : 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100'
                                        }">
                                    <i data-lucide="${hasAdmin ? 'user-cog' : 'user-plus'}" class="w-3.5 h-3.5"></i>
                                    <span>${hasAdmin ? 'เปลี่ยน Admin' : 'เลือก Admin'}</span>
                                </button>
                                <button onclick="toggleSchoolActive(${s.id}, ${isActive ? 0 : 1})" 
                                        class="px-2.5 py-1.5 text-[11px] font-bold rounded-lg border transition shadow-2xs ${
                                            isActive 
                                            ? 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100' 
                                            : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                                        }">
                                    ${isActive ? 'ระงับ' : 'เปิดใช้งาน'}
                                </button>
                            </div>
                        </td>
                    </tr>
                    `;
                }).join('');
                if (window.lucide) lucide.createIcons();
            }
        }
    } catch (err) {
        console.error('Error loading superadmin schools:', err);
    }
}
window.loadSuperAdminSchools = loadSuperAdminSchools;
window.testDatabaseConnection = testDatabaseConnection;
window.saveDatabaseConnection = saveDatabaseConnection;
window.runInstallDatabase = runInstallDatabase;
window.handleSuperAdminCredentialsSubmit = handleSuperAdminCredentialsSubmit;

// Super Admin: Open New School (No school admin required!)
async function handleSuperAdminAddSchool(e) {
    e.preventDefault();
    const smis = document.getElementById('sa_new_smis').value.trim();
    if (smis.length !== 8) {
        showToast('รหัส SMIS ต้องเป็นตัวเลข 8 หลักเท่านั้น', 'error');
        return;
    }

    const payload = {
        smis_code: smis,
        name: document.getElementById('sa_new_name').value.trim(),
        affiliation: document.getElementById('sa_new_affiliation').value.trim(),
        district: document.getElementById('sa_new_district') ? document.getElementById('sa_new_district').value.trim() : 'เมืองบุรีรัมย์',
        province: document.getElementById('sa_new_province').value.trim(),
        status: document.getElementById('sa_new_active').checked ? 'active' : 'pending'
    };

    try {
        const res = await fetch('/api/superadmin/save_school.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await res.json();
        if (result.status === 'success') {
            showToast(`เปิดใช้งานสถานศึกษา "${payload.name}" (SMIS: ${payload.smis_code}) สำเร็จ! คุณครูสามารถสมัครเข้าใช้งานเพื่อแต่งตั้งเป็น Admin ได้แล้ว`, 'success');
            document.getElementById('newSchoolForm').reset();
            document.getElementById('sa_new_active').checked = true;
            await loadSuperAdminSchools();
        } else {
            showToast(result.message || 'บันทึกไม่สำเร็จ', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('เกิดข้อผิดพลาดในการเปิดใช้งานสถานศึกษา', 'error');
    }
}

// Super Admin: Toggle School Active/Inactive
async function toggleSchoolActive(schoolId, newStatus) {
    try {
        const res = await fetch('/api/superadmin/toggle_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ school_id: schoolId, is_active: newStatus })
        });
        const result = await res.json();
        if (result.status === 'success') {
            showToast(newStatus === 1 ? 'เปิดใช้งานสถานศึกษาสำเร็จ' : 'ระงับสถานศึกษาเรียบร้อยแล้ว', 'success');
            await loadSuperAdminSchools();
        } else {
            showToast(result.message || 'ไม่สามารถเปลี่ยนสถานะได้', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('เกิดข้อผิดพลาดในการเปลี่ยนสถานะ', 'error');
    }
}

// Modal: Open Assign School Admin Modal and fetch teachers who registered for that school
async function openAssignAdminModal(schoolId) {
    currentAssignAdminSchoolId = schoolId;
    const modal = document.getElementById('assignAdminModal');
    const teacherList = document.getElementById('assignAdminTeacherList');
    const schoolNameEl = document.getElementById('assignAdminSchoolName');
    const smisBadge = document.getElementById('assignAdminSmisBadge');
    const affilEl = document.getElementById('assignAdminAffiliation');
    const currentAdminEl = document.getElementById('assignAdminCurrentAdmin');
    const teacherCountEl = document.getElementById('assignAdminTeacherCount');

    if (modal) modal.classList.remove('hidden');

    if (schoolNameEl) schoolNameEl.innerText = 'กำลังโหลดข้อมูลสถานศึกษา...';
    if (teacherList) {
        teacherList.innerHTML = `<div class="p-8 text-center text-slate-400 flex items-center justify-center gap-2">
            <i data-lucide="loader-2" class="w-5 h-5 animate-spin text-indigo-600"></i>
            <span>กำลังตรวจสอบรายชื่อคุณครูที่ลงทะเบียนในโรงเรียนนี้...</span>
        </div>`;
        if (window.lucide) lucide.createIcons();
    }

    try {
        const res = await fetch(`/api/superadmin/get_school_teachers.php?school_id=${schoolId}`);
        const data = await res.json();

        if (data.status === 'success') {
            const school = data.school;
            const teachers = data.teachers || [];

            if (schoolNameEl) schoolNameEl.innerText = school.name;
            if (smisBadge) smisBadge.innerText = `SMIS: ${school.smis_code}`;
            if (affilEl) affilEl.innerText = school.affiliation || 'สังกัดสถานศึกษา';
            
            const currentAdminName = school.assigned_admin_name || '';
            const hasCurrentAdmin = currentAdminName && !currentAdminName.includes('ยังไม่ได้กำหนด');
            if (currentAdminEl) {
                currentAdminEl.innerHTML = hasCurrentAdmin 
                    ? `<span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">${currentAdminName}</span>`
                    : `<span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">ยังไม่ได้แต่งตั้ง</span>`;
            }

            if (teacherCountEl) teacherCountEl.innerText = `${teachers.length} คน`;

            if (teachers.length === 0) {
                // Empty state: No teachers have registered yet for this school
                teacherList.innerHTML = `
                    <div class="p-6 bg-amber-50/70 border-2 border-dashed border-amber-200 rounded-2xl text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                            <i data-lucide="user-x" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-900">ยังไม่มีคุณครูหรือบุคลากรสมัครสมาชิกภายใต้รหัส SMIS นี้ (${school.smis_code})</h4>
                            <p class="text-xs text-amber-700 mt-1 max-w-md mx-auto leading-relaxed">
                                การแต่งตั้ง Admin ดูแลระบบของโรงเรียน จำเป็นต้องให้คุณครูของโรงเรียนนี้ลงทะเบียนสมัครสมาชิกเข้าสู่ระบบก่อน
                            </p>
                        </div>
                        <div class="p-3 bg-white/80 border border-amber-200 rounded-xl text-xs text-slate-600 text-left max-w-md mx-auto space-y-1">
                            <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                <i data-lucide="info" class="w-4 h-4 text-indigo-600"></i> คำแนะนำสำหรับโรงเรียน:
                            </div>
                            <div>1. ให้คุณครูไปที่หน้าเข้าสู่ระบบและคลิก <b>"สมัครสมาชิก"</b></div>
                            <div>2. กรอกรหัส SMIS 8 หลัก: <span class="font-mono font-bold text-indigo-700">${school.smis_code}</span></div>
                            <div>3. ระบุเลขประจำตัวประชาชน 13 หลัก และชื่อ-ตำแหน่ง</div>
                            <div>4. เมื่อสมัครสำเร็จ รายชื่อจะปรากฏในหน้านี้ทันทีเพื่อให้ Super Admin เลือกแต่งตั้งเป็น Admin</div>
                        </div>
                    </div>
                `;
            } else {
                // List of registered teachers
                teacherList.innerHTML = teachers.map(t => {
                    const isCurrentAdmin = (school.assigned_admin_id === t.id || t.is_school_admin);
                    const maskedIdCard = t.id_card ? `${t.id_card.substring(0, 1)}-${t.id_card.substring(1, 5)}-xxxxx-${t.id_card.substring(10, 12)}-${t.id_card.substring(12, 13)}` : '-';
                    
                    const deptMap = {
                        academic: 'กลุ่มบริหารวิชาการ',
                        budget: 'กลุ่มบริหารงบประมาณ',
                        personnel: 'กลุ่มบริหารงานบุคคล',
                        general: 'กลุ่มบริหารทั่วไป',
                        central: 'ผู้บริหาร/ส่วนกลาง'
                    };

                    return `
                        <div class="p-3.5 bg-white border border-slate-200 hover:border-indigo-200 rounded-2xl transition shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 ${
                            isCurrentAdmin ? 'bg-indigo-50/30 border-indigo-300' : ''
                        }">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 ${
                                    isCurrentAdmin ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'
                                }">
                                    ${t.name.charAt(0)}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">${t.name}</span>
                                        ${isCurrentAdmin ? `
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                                <i data-lucide="shield-check" class="w-3 h-3"></i> Admin โรงเรียนปัจจุบัน
                                            </span>
                                        ` : ''}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex flex-wrap items-center gap-x-2.5 gap-y-1">
                                        <span>ตำแหน่ง: <b class="text-slate-700">${t.position || 'ครู'}</b></span>
                                        <span>•</span>
                                        <span>เลข ปชช.: <span class="font-mono text-slate-600">${maskedIdCard}</span></span>
                                        <span>•</span>
                                        <span>${deptMap[t.department] || t.department}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                ${isCurrentAdmin ? `
                                    <span class="px-3 py-1.5 bg-slate-100 text-slate-500 text-xs font-bold rounded-xl flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i> เป็น Admin แล้ว
                                    </span>
                                ` : `
                                    <button onclick="assignTeacherAsSchoolAdmin(${school.id}, ${t.id}, '${t.name.replace(/'/g, "\\'")}')" 
                                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 active:scale-95">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                        <span>แต่งตั้งเป็น Admin</span>
                                    </button>
                                `}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            if (window.lucide) lucide.createIcons();
        } else {
            showToast(data.message || 'ไม่สามารถดึงข้อมูลคุณครูได้', 'error');
        }
    } catch (err) {
        console.error('Error fetching teachers for school:', err);
        showToast('เกิดข้อผิดพลาดในการโหลดรายชื่อคุณครู', 'error');
    }
}

// Super Admin: Confirm assigning teacher as School Admin
async function assignTeacherAsSchoolAdmin(schoolId, teacherId, teacherName) {
    if (!confirm(`ยืนยันการแต่งตั้งคุณครู "${teacherName}" เป็น Admin ผู้ดูแลระบบประจำโรงเรียนนี้หรือไม่?`)) {
        return;
    }

    try {
        const res = await fetch('/api/superadmin/assign_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                school_id: schoolId,
                admin_id: teacherId,
                admin_name: teacherName
            })
        });
        const result = await res.json();
        if (result.status === 'success') {
            showToast(result.message || `แต่งตั้ง ${teacherName} เป็น Admin เรียบร้อยแล้ว`, 'success');
            // Reload the modal list to show updated badge
            await openAssignAdminModal(schoolId);
            // Refresh main table
            await loadSuperAdminSchools();
        } else {
            showToast(result.message || 'แต่งตั้งไม่สำเร็จ', 'error');
        }
    } catch (err) {
        console.error('Error assigning admin:', err);
        showToast('เกิดข้อผิดพลาดในการแต่งตั้ง Admin', 'error');
    }
}

function closeAssignAdminModal() {
    const modal = document.getElementById('assignAdminModal');
    if (modal) modal.classList.add('hidden');
    currentAssignAdminSchoolId = null;
}

// ==========================================
// 15. PASSWORD CHANGE MODAL & ENFORCEMENT
// ==========================================
function openChangePasswordModal(isEnforced = false) {
    const alertBox = document.getElementById('mustChangePwdAlert');
    if (alertBox) {
        if (isEnforced) alertBox.classList.remove('hidden');
        else alertBox.classList.add('hidden');
    }
    const modal = document.getElementById('changePasswordModal');
    if (modal) modal.classList.remove('hidden');
}

function closeChangePasswordModal() {
    const modal = document.getElementById('changePasswordModal');
    if (modal) modal.classList.add('hidden');
}

async function handleChangePasswordSubmit(e) {
    e.preventDefault();
    const oldPwd = document.getElementById('chg_old_password').value;
    const newPwd = document.getElementById('chg_new_password').value;
    const confirmPwd = document.getElementById('chg_confirm_password').value;

    if (newPwd !== confirmPwd) {
        showToast('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน', 'error');
        return;
    }

    if (newPwd.length < 6) {
        showToast('รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร', 'error');
        return;
    }

    try {
        const res = await fetch('/api/auth/change_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: currentUser ? currentUser.id : 1,
                old_password: oldPwd,
                new_password: newPwd
            })
        });
        const result = await res.json();
        if (result.status === 'success') {
            showToast('เปลี่ยนรหัสผ่านสำเร็จเรียบร้อยแล้ว', 'success');
            if (currentUser) {
                currentUser.must_change_password = 0;
                currentUser.password = newPwd;
                localStorage.setItem('currentUser', JSON.stringify(currentUser));
            }
            closeChangePasswordModal();
        } else {
            showToast(result.message || 'เปลี่ยนรหัสผ่านไม่สำเร็จ ตรวจสอบรหัสผ่านเดิม', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน', 'error');
    }
}

