<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารแผนปฏิบัติการประจำปีของโรงเรียน (School Action Plan OS)</title>
    <meta name="description" content="ระบบบริหารแผนปฏิบัติการประจำปีและงบประมาณสถานศึกษา รองรับการกำหนดปีงบประมาณ การจัดสรร 4 กลุ่มงาน เสนอโครงการ กลั่นกรอง อนุมัติ AI ช่วยร่างโครงการ ติดตามผล เบิกจ่ายจริง และพิมพ์รูปเล่มแผนปฏิบัติการราชการ สพฐ.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8fafc; }
        .sidebar-item-active {
            background-color: #2563eb;
            color: #ffffff !important;
            font-weight: 600;
        }
        .sidebar-item-active svg {
            color: #ffffff !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 flex flex-col antialiased">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand & Fiscal Year -->
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-white border border-slate-200 rounded-xl flex items-center justify-center p-1 shadow-xs shrink-0 overflow-hidden">
                    <img id="headerSchoolLogo" src="<?= $school_logo ?: 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png' ?>" 
                         alt="ตราสัญลักษณ์โรงเรียน" class="w-full h-full object-contain">
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-slate-900 text-base leading-tight tracking-tight">ระบบบริหารแผนปฏิบัติการประจำปี</span>
                        <span id="headerStatusBadge" class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                            สถานะ: เปิดดำเนินงาน
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 truncate" id="headerSchoolName">
                        <?= $school_name ?> (รหัส SMIS: <span id="headerSmisCode"><?= $smis_code ?></span>) • <?= $affiliation ?>
                    </p>
                </div>
            </div>

            <!-- Fiscal Year Switcher & User Profile Controls -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Fiscal Year Selector -->
                <div class="flex items-center bg-slate-100 rounded-xl p-1 border border-slate-200">
                    <span class="text-xs font-semibold text-slate-500 pl-2 pr-1 hidden md:inline">ปีงบ:</span>
                    <select id="fiscalYearSelect" onchange="onFiscalYearChange(this.value)" class="bg-transparent text-xs font-bold text-slate-800 outline-none pr-1 cursor-pointer">
                        <option value="1">2568 (ปัจจุบัน)</option>
                        <option value="2">2567 (ย้อนหลัง)</option>
                    </select>
                </div>

                <!-- Print Full Book Quick Button -->
                <a href="print_annual_plan.php" target="_blank" class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition border border-slate-200">
                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-blue-600"></i>
                    รูปเล่มแผนรวม
                </a>

                <!-- Role / User Switcher Dropdown -->
                <div class="relative">
                    <button id="userMenuBtn" onclick="toggleUserDropdown()" class="flex items-center gap-2 p-1.5 sm:px-3 sm:py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition text-left">
                        <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0" id="userAvatar">
                            ผ
                        </div>
                        <div class="hidden sm:block text-left leading-tight">
                            <p class="text-xs font-bold text-slate-900 truncate max-w-[130px]" id="currentUserName">นายธีระพล เกียรติวิทยา</p>
                            <p class="text-[11px] text-blue-600 font-semibold" id="currentUserRoleTitle">ผู้อำนวยการโรงเรียน</p>
                        </div>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                    </button>

                    <!-- Dropdown for user menu & logout -->
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-800" id="ddUserName">ผู้ใช้งาน</p>
                            <p class="text-[11px] text-blue-600 font-semibold mt-0.5" id="ddUserRole">ตำแหน่ง</p>
                        </div>
                        <button onclick="openChangePasswordModal()" class="w-full px-4 py-2.5 text-left text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition">
                            <i data-lucide="key" class="w-3.5 h-3.5 text-amber-600"></i>
                            เปลี่ยนรหัสผ่าน (Change Password)
                        </button>
                        <div class="border-t border-slate-100 my-1"></div>
                        <button onclick="logout()" class="w-full px-4 py-2.5 text-left text-xs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-2 transition">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            ออกจากระบบ (Sign Out)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Workspace Container (Sidebar + Content) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-grow flex flex-col md:flex-row gap-6">

        <!-- Left Navigation Sidebar -->
        <aside class="w-full md:w-64 shrink-0">
            <div class="bg-white rounded-2xl border border-slate-200 p-3 shadow-xs sticky top-22">
                <div class="px-3 py-2 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                    เมนูการบริหารแผน
                </div>
                <nav class="space-y-1">
                    <button onclick="switchTab('overview')" id="tab-overview" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition sidebar-item-active text-left">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>1. ภาพรวมและ Dashboard</span>
                    </button>

                    <button onclick="switchTab('fiscal_budget')" id="tab-fiscal_budget" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        <span>2. ปีงบ & แหล่งงบประมาณ</span>
                    </button>

                    <button onclick="switchTab('subsidies')" id="tab-subsidies" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50/50 hover:bg-emerald-100/70 border border-emerald-100/80 transition text-left">
                        <i data-lucide="calculator" class="w-4 h-4 text-emerald-600"></i>
                        <span class="font-bold">2.1 คำนวณงบรายหัว & กพพ.</span>
                    </button>

                    <button onclick="switchTab('allocation')" id="tab-allocation" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="pie-chart" class="w-4 h-4"></i>
                        <span>3. จัดสรร 100% (4 กลุ่มงาน)</span>
                    </button>

                    <button onclick="switchTab('projects')" id="tab-projects" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="folder-kanban" class="w-4 h-4"></i>
                        <span>4. เสนอโครงการ & แผนงาน</span>
                    </button>

                    <button onclick="switchTab('screening')" id="tab-screening" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                        <span>5. กลั่นกรอง & ปรับลดงบ</span>
                    </button>

                    <button onclick="switchTab('approval')" id="tab-approval" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>6. อนุมัติโครงการ (ผอ.)</span>
                    </button>

                    <button onclick="switchTab('tracking')" id="tab-tracking" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                        <span>7. ติดตามความก้าวหน้า</span>
                    </button>

                    <button onclick="switchTab('expenses')" id="tab-expenses" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                        <span>8. บันทึกเบิกจ่าย & งบคงเหลือ</span>
                    </button>

                    <button onclick="switchTab('print_reports')" id="tab-print_reports" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>9. พิมพ์โครงการ & รูปเล่ม</span>
                    </button>

                    <div class="border-t border-slate-100 my-2"></div>

                    <button onclick="switchTab('history')" id="tab-history" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="history" class="w-4 h-4"></i>
                        <span>10. ประวัติย้อนหลัง & คัดลอกแผน</span>
                    </button>

                    <button onclick="switchTab('users')" id="tab-users" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition text-left">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span>11. ผู้ใช้งาน & กำหนดสิทธิ์</span>
                    </button>

                    <button onclick="switchTab('school_settings')" id="tab-school_settings" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-blue-700 bg-blue-50/40 hover:bg-blue-100/70 border border-blue-100/80 transition text-left">
                        <i data-lucide="building-2" class="w-4 h-4 text-blue-600"></i>
                        <span class="font-bold">12. ตั้งค่าโรงเรียน & โลโก้</span>
                    </button>

                    <button onclick="switchTab('superadmin')" id="tab-superadmin" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50/40 hover:bg-indigo-100/70 border border-indigo-100/80 transition text-left">
                        <i data-lucide="globe-2" class="w-4 h-4 text-indigo-600"></i>
                        <span class="font-bold">13. Super Admin (รหัส SMIS)</span>
                    </button>
                </nav>

                <!-- AI Highlight Box -->
                <div class="mt-4 p-3 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl border border-indigo-100 text-xs">
                    <div class="flex items-center gap-1.5 font-bold text-indigo-900 mb-1">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-600"></i>
                        <span>AI ผู้ช่วยร่างโครงการ</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-normal">
                        สร้างร่างโครงการ สพฐ. หลักการ วัตถุประสงค์ และแจกแจงงบประมาณอัตโนมัติ
                    </p>
                    <button onclick="openNewProjectModalWithAI()" class="mt-2.5 w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[11px] font-bold shadow-xs transition">
                        ทดลองใช้ AI ร่างโครงการ
                    </button>
                </div>
            </div>
        </aside>

        <!-- Right Main View Panel -->
        <main class="flex-grow min-w-0">
            <!-- Alert / Notification Toast -->
            <div id="toastMessage" class="hidden mb-4 p-4 rounded-xl text-sm font-semibold flex items-center justify-between shadow-sm transition"></div>

            <!-- View 1: Overview Dashboard -->
            <div id="view-overview" class="tab-view space-y-6">
                <!-- Top Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Budget Received -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">งบประมาณที่ได้รับ</span>
                            <div class="p-2 rounded-xl bg-blue-50 text-blue-600"><i data-lucide="wallet" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-slate-900 mt-2" id="kpi-total-budget">0.00</p>
                        <p class="text-xs text-slate-500 mt-1" id="kpi-sources-count">รวมจาก 0 แหล่งงบประมาณ</p>
                    </div>

                    <!-- Total Approved Project Budget -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">งบที่อนุมัติในแผน</span>
                            <div class="p-2 rounded-xl bg-purple-50 text-purple-600"><i data-lucide="check-square" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-purple-900 mt-2" id="kpi-approved-budget">0.00</p>
                        <p class="text-xs text-slate-500 mt-1" id="kpi-approved-projects-count">อนุมัติแล้ว 0 โครงการ</p>
                    </div>

                    <!-- Total Disbursed / Spent -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">เบิกจ่ายจริงแล้ว</span>
                            <div class="p-2 rounded-xl bg-amber-50 text-amber-600"><i data-lucide="trending-down" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-amber-900 mt-2" id="kpi-total-spent">0.00</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs font-bold text-amber-600" id="kpi-disbursement-rate">0.0%</span>
                            <span class="text-xs text-slate-400">ของงบอนุมัติ</span>
                        </div>
                    </div>

                    <!-- Net Remaining Budget -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">งบประมาณคงเหลือสุทธิ</span>
                            <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="piggy-bank" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-emerald-900 mt-2" id="kpi-remaining-budget">0.00</p>
                        <p class="text-xs text-emerald-600 mt-1 font-medium">พร้อมบริหารจัดการ</p>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- 100% Department Allocation Pie/Doughnut -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">สัดส่วนจัดสรร 4 กลุ่มงาน (100%)</h3>
                            <span class="text-xs text-blue-600 font-semibold cursor-pointer" onclick="switchTab('allocation')">แก้ไข</span>
                        </div>
                        <div class="h-56 relative flex items-center justify-center">
                            <canvas id="allocationChart"></canvas>
                        </div>
                        <div id="allocationLegend" class="mt-4 space-y-1.5 text-xs text-slate-600"></div>
                    </div>

                    <!-- Department Budget Comparison Bar Chart -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs lg:col-span-2">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-slate-800">เปรียบเทียบ งบจัดสรร vs งบอนุมัติ vs เบิกจ่ายจริง</h3>
                            <span class="text-xs text-slate-400">หน่วย: บาท</span>
                        </div>
                        <div class="h-64">
                            <canvas id="deptComparisonChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Summary by 4 Work Groups Table -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">สรุปการบริหารงบประมาณจำแนกตามกลุ่มงาน</h3>
                            <p class="text-xs text-slate-500">สถานะงบประมาณและจำนวนโครงการประจำปีงบประมาณ</p>
                        </div>
                        <button onclick="switchTab('projects')" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            ดูโครงการทั้งหมด <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3.5">กลุ่มงาน</th>
                                    <th class="p-3.5 text-center">สัดส่วน %</th>
                                    <th class="p-3.5 text-right">งบจัดสรร (บาท)</th>
                                    <th class="p-3.5 text-right">งบอนุมัติในแผน</th>
                                    <th class="p-3.5 text-right">เบิกจ่ายจริง</th>
                                    <th class="p-3.5 text-right">งบคงเหลือ</th>
                                    <th class="p-3.5 text-center">จำนวนโครงการ</th>
                                </tr>
                            </thead>
                            <tbody id="overviewDeptTableBody" class="divide-y divide-slate-100">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Projects List -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">โครงการล่าสุดในแผนปฏิบัติการ</h3>
                        <button onclick="openNewProjectModal()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> เสนอโครงการใหม่
                        </button>
                    </div>
                    <div class="divide-y divide-slate-100" id="overviewRecentProjects">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- View 2: Fiscal Year & Budget Sources -->
            <div id="view-fiscal_budget" class="tab-view hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">กำหนดปีงบประมาณและแหล่งงบประมาณที่ได้รับ</h2>
                        <p class="text-xs text-slate-500">บันทึกวงเงินที่ได้รับจัดสรรจาก สพฐ., กพพ., รายได้สถานศึกษา, เงินระดมทรัพยากร</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openNewSourceModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="plus" class="w-4 h-4"></i> เพิ่มแหล่งงบประมาณ
                        </button>
                        <button onclick="openFiscalYearModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition border border-slate-200 flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-4 h-4"></i> จัดการปีงบประมาณ
                        </button>
                    </div>
                </div>

                <!-- Fiscal Year Details Card -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">ปีงบประมาณที่กำลังทำงาน</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800" id="fyCurrentBadge">ปีปัจจุบัน</span>
                        </div>
                        <h3 class="text-2xl font-extrabold text-slate-900 mt-1" id="fyDetailYear">พ.ศ. 2568</h3>
                        <p class="text-xs text-slate-500 mt-0.5" id="fyDetailDates">1 ตุลาคม 2567 - 30 กันยายน 2568</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-slate-400">ยอดงบประมาณรวมที่ได้รับ</span>
                        <p class="text-2xl font-black text-blue-900 mt-0.5" id="fyTotalReceived">0.00 บาท</p>
                    </div>
                </div>

                <!-- Sources Table -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900">รายการแหล่งงบประมาณที่ได้รับ</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3.5 w-12 text-center">#</th>
                                    <th class="p-3.5 w-28">รหัส</th>
                                    <th class="p-3.5">ชื่อแหล่งงบประมาณ</th>
                                    <th class="p-3.5 w-32">ประเภท</th>
                                    <th class="p-3.5 w-32">วันที่รับเงิน</th>
                                    <th class="p-3.5 text-right w-36">จำนวนเงิน (บาท)</th>
                                    <th class="p-3.5 w-24 text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="budgetSourcesTableBody" class="divide-y divide-slate-100">
                                <!-- Populated dynamically -->
                            </tbody>
                            <tfoot class="bg-slate-50 font-bold border-t border-slate-200 text-xs">
                                <tr>
                                    <td colspan="5" class="p-3.5 text-right text-slate-700">รวมงบประมาณที่ได้รับทั้งสิ้น</td>
                                    <td class="p-3.5 text-right text-blue-900 text-sm font-black" id="budgetSourcesTotal">0.00 บาท</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View 3: 100% Department Allocation -->
            <div id="view-allocation" class="tab-view hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">จัดสรรงบประมาณเป็นเปอร์เซ็นต์ (รวม 100%)</h2>
                        <p class="text-xs text-slate-500">แบ่งให้ 4 กลุ่มงาน (วิชาการ, งบประมาณ, บุคคล, ทั่วไป) และงบสำรองจ่าย/ส่วนกลาง</p>
                    </div>
                    <button onclick="saveAllocations()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="save" class="w-4 h-4"></i> บันทึกสัดส่วนการจัดสรร
                    </button>
                </div>

                <!-- Total Percentage Status Banner -->
                <div id="percentValidationBanner" class="p-4 rounded-2xl border flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div id="percentIcon" class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm"></div>
                        <div>
                            <p class="text-xs font-bold" id="percentStatusTitle">สถานะสัดส่วนรวม</p>
                            <p class="text-xs" id="percentStatusDesc">ผลรวมเปอร์เซ็นต์ต้องได้ 100.00% พอดี</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black" id="percentTotalBadge">100.00%</span>
                    </div>
                </div>

                <!-- Allocation Form Cards for the 4 departments + reserve -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="allocationCardsContainer">
                    <!-- Populated dynamically -->
                </div>
            </div>

            <!-- View 4: Projects Management & Proposals -->
            <div id="view-projects" class="tab-view hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">โครงการตามแผนปฏิบัติการประจำปี</h2>
                        <p class="text-xs text-slate-500">ครูและบุคลากรเสนอโครงการ กำหนดกิจกรรม และประมาณการค่าใช้จ่าย</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="openNewProjectModalWithAI()" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-500/20 transition flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4"></i> AI ช่วยร่างโครงการใหม่
                        </button>
                        <button onclick="openNewProjectModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="plus" class="w-4 h-4"></i> เสนอโครงการ
                        </button>
                    </div>
                </div>

                <!-- Filters & Search Toolbar -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
                    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                        <select id="projectFilterDept" onchange="renderProjectsList()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                            <option value="all">ทุกกลุ่มงาน</option>
                            <option value="academic">กลุ่มบริหารวิชาการ</option>
                            <option value="budget">กลุ่มบริหารงบประมาณ</option>
                            <option value="personnel">กลุ่มบริหารงานบุคคล</option>
                            <option value="general">กลุ่มบริหารทั่วไป</option>
                            <option value="reserve">งบสำรองจ่าย/ส่วนกลาง</option>
                        </select>

                        <select id="projectFilterStatus" onchange="renderProjectsList()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                            <option value="all">ทุกสถานะการพิจารณา</option>
                            <option value="submitted">รอพิจารณา/รอความเห็นชอบ</option>
                            <option value="screened">ผ่านการกลั่นกรอง (รอ ผอ.)</option>
                            <option value="approved">อนุมัติแล้ว (ในเล่มแผน)</option>
                            <option value="revision_requested">ขอให้แก้ไข</option>
                        </select>
                    </div>

                    <div class="relative w-full md:w-64">
                        <input type="text" id="projectSearchInput" oninput="renderProjectsList()" placeholder="ค้นหาชื่อโครงการ หรือผู้รับผิดชอบ..." 
                               class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    </div>
                </div>

                <!-- Projects Table -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3.5 w-20">รหัส</th>
                                    <th class="p-3.5">ชื่อโครงการ / กลุ่มงาน</th>
                                    <th class="p-3.5 w-36">ผู้รับผิดชอบ</th>
                                    <th class="p-3.5 text-right w-28">งบที่ขอ</th>
                                    <th class="p-3.5 text-right w-28">งบอนุมัติ</th>
                                    <th class="p-3.5 w-32 text-center">สถานะพิจารณา</th>
                                    <th class="p-3.5 w-28 text-center">ความก้าวหน้า</th>
                                    <th class="p-3.5 w-36 text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="projectsTableBody" class="divide-y divide-slate-100">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View 5: Screening & Budget Trimming -->
            <div id="view-screening" class="tab-view hidden space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">ระบบตรวจสอบ กลั่นกรอง และปรับลดงบประมาณ</h2>
                    <p class="text-xs text-slate-500">สำหรับเจ้าหน้าที่แผนงานและหัวหน้ากลุ่มงาน: ปรับเพิ่ม-ลดงบประมาณให้เหมาะสมกับวงเงินที่จัดสรร</p>
                </div>

                <!-- Group Budget Balances Checklist -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="screeningGroupBudgets">
                    <!-- Populated dynamically -->
                </div>

                <!-- Pending Projects for Screening -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">รายการโครงการที่ต้องกลั่นกรองและตรวจสอบวงเงิน</h3>
                        <span class="text-xs text-slate-500" id="screeningPendingCount">0 รายการ</span>
                    </div>
                    <div class="divide-y divide-slate-100" id="screeningProjectsList">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- View 6: Director Approval Workflow -->
            <div id="view-approval" class="tab-view hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">การยืนยันและอนุมัติโครงการ (ผู้อำนวยการโรงเรียน)</h2>
                        <p class="text-xs text-slate-500">ลงนามอนุมัติโครงการเพื่อบรรจุเข้าในรูปเล่มแผนปฏิบัติการประจำปี</p>
                    </div>
                    <button onclick="approveAllScreened()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check-check" class="w-4 h-4"></i> อนุมัติทุกโครงการที่ผ่านการกลั่นกรอง
                    </button>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">โครงการที่รอกรรมการ / ผู้อำนวยการอนุมัติ</h3>
                        <span class="text-xs text-slate-500" id="approvalPendingCount">0 รายการ</span>
                    </div>
                    <div class="divide-y divide-slate-100" id="approvalProjectsList">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- View 7: Project Tracking -->
            <div id="view-tracking" class="tab-view hidden space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">ระบบติดตามการดำเนินงานของแต่ละโครงการ</h2>
                    <p class="text-xs text-slate-500">บันทึกร้อยละความก้าวหน้า รายงานผลตามตัวชี้วัด ปัญหาอุปสรรคและแนวทางแก้ไข</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="trackingCardsContainer">
                    <!-- Populated dynamically -->
                </div>
            </div>

            <!-- View 8: Actual Expenses & Disbursement -->
            <div id="view-expenses" class="tab-view hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">บันทึกรายการค่าใช้จ่ายจริงและคำนวณงบคงเหลือ</h2>
                        <p class="text-xs text-slate-500">บันทึกเลขที่ฎีกา/ใบสำคัญเบิกจ่าย คำนวณงบประมาณใช้ไปและคงเหลืออัตโนมัติ</p>
                    </div>
                    <button onclick="openNewExpenseModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-4 h-4"></i> บันทึกการเบิกจ่าย
                    </button>
                </div>

                <!-- Financial Status Cards for Disbursement -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <span class="text-xs font-bold text-slate-500 uppercase">งบอนุมัติรวมทั้งสิ้น</span>
                        <p class="text-2xl font-black text-slate-900 mt-1" id="exp-total-approved">0.00 บาท</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <span class="text-xs font-bold text-slate-500 uppercase">เบิกจ่ายจริงสะสม</span>
                        <p class="text-2xl font-black text-amber-600 mt-1" id="exp-total-spent">0.00 บาท</p>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <span class="text-xs font-bold text-slate-500 uppercase">งบประมาณคงเหลือ</span>
                        <p class="text-2xl font-black text-emerald-600 mt-1" id="exp-total-remaining">0.00 บาท</p>
                    </div>
                </div>

                <!-- Expenses History Table -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900">ประวัติรายการเบิกจ่ายเงินจริง</h3>
                        <div class="flex items-center gap-2">
                            <select id="expenseFilterProject" onchange="renderExpensesTable()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                                <option value="all">ทุกโครงการ</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3.5 w-24">วันที่</th>
                                    <th class="p-3.5 w-28">เลขที่เอกสาร</th>
                                    <th class="p-3.5">โครงการ</th>
                                    <th class="p-3.5">รายการค่าใช้จ่าย</th>
                                    <th class="p-3.5 w-24">หมวด</th>
                                    <th class="p-3.5 text-right w-28">จำนวนเงิน</th>
                                    <th class="p-3.5 w-32">ผู้เบิก</th>
                                    <th class="p-3.5 w-16 text-center">ลบ</th>
                                </tr>
                            </thead>
                            <tbody id="expensesTableBody" class="divide-y divide-slate-100">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View 9: Print & Reports -->
            <div id="view-print_reports" class="tab-view hidden space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">ระบบพิมพ์โครงการและรูปเล่มแผนปฏิบัติการประจำปี</h2>
                    <p class="text-xs text-slate-500">พิมพ์เอกสารราชการ สพฐ. ตราครุฑ รายโครงการ หรือพิมพ์รวมเป็นรูปเล่มสมบูรณ์</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Print Full Book Card -->
                    <div class="bg-white p-6 rounded-2xl border border-blue-200 shadow-sm flex flex-col justify-between relative overflow-hidden">
                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-blue-50 rounded-full pointer-events-none"></div>
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center mb-4 shadow-md shadow-blue-500/20">
                                <i data-lucide="book" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">พิมพ์รูปเล่มแผนปฏิบัติการประจำปีทั้งเล่ม</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                เอกสารฉบับสมบูรณ์ ประกอบด้วย: หน้าปกเล่ม, คำนำ, สารบัญ, บันทึกการอนุมัติ, ตารางแหล่งงบประมาณ, ตารางจัดสรร 100%, บัญชีโครงการ, และแบบเสนอโครงการทุกโครงการ
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400 font-medium">จัดหน้าพิมพ์ A4 พร้อมสั่งพิมพ์ทันที</span>
                            <a href="print_annual_plan.php" target="_blank" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center gap-2">
                                <i data-lucide="printer" class="w-4 h-4"></i> สั่งพิมพ์รูปเล่มแผน
                            </a>
                        </div>
                    </div>

                    <!-- Print Single Project Card -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center mb-4">
                                <i data-lucide="file-text" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">พิมพ์แบบเสนอโครงการรายโครงการ</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                เลือกโครงการที่ต้องการเพื่อพิมพ์แบบเสนอโครงการตามมาตรฐานราชการ สพฐ. ตราครุฑ พร้อมตารางแจกแจงค่าใช้จ่ายและช่องลงนาม 3 ระดับ
                            </p>
                            <div class="mt-4">
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">เลือกโครงการที่จะพิมพ์</label>
                                <select id="printProjectSelector" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none">
                                    <!-- Populated dynamically -->
                                </select>
                            </div>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end">
                            <button onclick="printSelectedProject()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                                <i data-lucide="printer" class="w-4 h-4"></i> พิมพ์โครงการที่เลือก
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View 10: Historical Data & Copy Plan -->
            <div id="view-history" class="tab-view hidden space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">ระบบจัดเก็บข้อมูลย้อนหลังและคัดลอกแผนงาน</h2>
                    <p class="text-xs text-slate-500">สลับดูข้อมูลแผนปฏิบัติการปีก่อนหน้า และคัดลอกโครงการมาใช้ในปีงบประมาณใหม่</p>
                </div>

                <!-- Copy Plan Card -->
                <div class="bg-white p-6 rounded-2xl border border-indigo-100 shadow-xs bg-gradient-to-br from-white to-indigo-50/40">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2.5 bg-indigo-100 text-indigo-700 rounded-xl">
                            <i data-lucide="copy" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-indigo-950">คัดลอกแผนงานจากปีก่อนหน้า (Copy Plan to Current Year)</h3>
                            <p class="text-xs text-slate-500">ช่วยให้ครูไม่ต้องพิมพ์โครงการเดิมซ้ำ สามารถปรับปรุงข้อมูลต่อเนื่องได้ทันที</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">จากปีงบประมาณ (ต้นทาง)</label>
                            <select id="copyFromYear" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                                <option value="2">ปีงบประมาณ 2567</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ไปยังปีงบประมาณ (ปลายทาง)</label>
                            <select id="copyToYear" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                                <option value="1">ปีงบประมาณ 2568 (ปัจจุบัน)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button onclick="executeCopyPlan()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="copy-plus" class="w-4 h-4"></i> ดำเนินการคัดลอกโครงการ
                        </button>
                    </div>
                </div>

                <!-- Archived Years List -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900">ประวัติปีงบประมาณที่จัดเก็บในฐานข้อมูล</h3>
                    </div>
                    <div class="divide-y divide-slate-100" id="historyYearsList">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- View 11: Users & Role Management -->
            <div id="view-users" class="tab-view hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">ผู้ใช้งานและกำหนดสิทธิ์ (5 บทบาท)</h2>
                        <p class="text-xs text-slate-500">ผู้ดูแลระบบ, ผู้อำนวยการ, เจ้าหน้าที่แผน/งบประมาณ, หัวหน้ากลุ่มงาน, ครู</p>
                    </div>
                    <button onclick="openNewUserModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> เพิ่มผู้ใช้งาน
                    </button>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3.5">ชื่อ-นามสกุล</th>
                                    <th class="p-3.5">ตำแหน่ง</th>
                                    <th class="p-3.5">กลุ่มงาน</th>
                                    <th class="p-3.5 w-36">บทบาทในระบบ</th>
                                    <th class="p-3.5 w-24 text-center">สลับใช้งาน</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody" class="divide-y divide-slate-100">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View 12: Plan Officer Subsidies Calculator (งบอุดหนุนรายหัว & กพพ.) -->
            <div id="view-subsidies" class="tab-view hidden space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-xs font-bold mb-2">
                            <i data-lucide="calculator" class="w-3.5 h-3.5 text-emerald-600"></i>
                            สำหรับเจ้าหน้าที่แผนงานและงบประมาณ
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">คำนวณงบประมาณเงินอุดหนุนรายหัวและเงินกิจกรรมพัฒนาผู้เรียน (กพพ.)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            กำหนดจำนวนนักเรียนในแต่ละช่วงชั้น เพื่อนำอัตราที่รัฐบาลจัดสรรมาคูณคำนวณยอดเงินงบประมาณ และนำไปตัดงบจัดสรร 100% เข้า 4 กลุ่มงาน
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="saveSubsidyDataOnly()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 border border-slate-200">
                            <i data-lucide="save" class="w-4 h-4"></i> บันทึกข้อมูลนักเรียน
                        </button>
                        <button type="button" onclick="applySubsidiesToBudget()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-md shadow-emerald-600/20 flex items-center gap-2">
                            <i data-lucide="arrow-right-circle" class="w-4 h-4"></i> ตัดงบเข้า 4 กลุ่มงาน 100%
                        </button>
                    </div>
                </div>

                <!-- Subsidies Summary KPI Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">จำนวนนักเรียนทั้งหมด</span>
                            <div class="p-2 rounded-xl bg-blue-50 text-blue-600"><i data-lucide="users" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-slate-900 mt-2" id="sub-kpi-students">0 คน</p>
                        <p class="text-xs text-slate-500 mt-1">4 ช่วงชั้น (อนุบาล - ม.ปลาย)</p>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">เงินอุดหนุนรายหัวรวม</span>
                            <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="wallet" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-emerald-600 mt-2" id="sub-kpi-subsidy-total">0.00 ฿</p>
                        <p class="text-xs text-slate-500 mt-1">การจัดการศึกษาขั้นพื้นฐาน</p>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">เงินกิจกรรมพัฒนาผู้เรียน (กพพ.)</span>
                            <div class="p-2 rounded-xl bg-purple-50 text-purple-600"><i data-lucide="sparkles" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-purple-600 mt-2" id="sub-kpi-dev-total">0.00 ฿</p>
                        <p class="text-xs text-slate-500 mt-1">4 กิจกรรมพัฒนาคุณภาพผู้เรียน</p>
                    </div>

                    <div class="bg-gradient-to-br from-blue-900 to-indigo-950 p-5 rounded-2xl text-white shadow-md">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-200 uppercase tracking-wider">รวมยอดงบประมาณที่ได้</span>
                            <div class="p-2 rounded-xl bg-white/10 text-white"><i data-lucide="check-check" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-white mt-2" id="sub-kpi-grand-total">0.00 ฿</p>
                        <p class="text-xs text-blue-200 mt-1">พร้อมนำไปจัดสรรร้อยละ 100%</p>
                    </div>
                </div>

                <!-- Interactive Rates and Students Count Table -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">ตารางกำหนดจำนวนนักเรียนและอัตราเงินอุดหนุนต่อหัว (พ.ศ. 2568)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">แก้ไขจำนวนนักเรียนหรืออัตราตามหนังสือจัดสรร แล้วระบบจะคำนวณยอดเงินให้อัตโนมัติ</p>
                        </div>
                        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">
                            เกณฑ์อัตรา สพฐ. กระทรวงศึกษาธิการ
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3 w-40">ระดับช่วงชั้น</th>
                                    <th class="p-3 w-28 text-center">จำนวนนักเรียน (คน)</th>
                                    <th class="p-3 w-32 text-right">เงินอุดหนุนรายหัว (บ./คน/ปี)</th>
                                    <th class="p-3 w-36 text-right">รวมเงินอุดหนุน (บาท)</th>
                                    <th class="p-3 w-32 text-right">เงิน กพพ. (บ./คน/ปี)</th>
                                    <th class="p-3 w-36 text-right">รวมเงิน กพพ. (บาท)</th>
                                    <th class="p-3 w-40 text-right bg-slate-100/60">รวมงบทั้งสิ้น (บาท)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <!-- Kindergarten -->
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3 font-bold text-slate-900 flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span>
                                        ระดับก่อนประถม (อนุบาล)
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="number" id="sub_count_kindergarten" value="120" min="0" oninput="recalcSubsidiesLocal()" 
                                               class="w-20 text-center font-bold px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right">
                                        <input type="number" id="sub_rate_kindergarten" value="1800" min="0" step="10" oninput="recalcSubsidiesLocal()" 
                                               class="w-24 text-right font-medium px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right font-bold text-emerald-700" id="sub_total_subsidy_kindergarten">216,000.00</td>
                                    <td class="p-3 text-right">
                                        <input type="number" id="sub_dev_rate_kindergarten" value="430" min="0" step="10" oninput="recalcSubsidiesLocal()" 
                                               class="w-24 text-right font-medium px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right font-bold text-purple-700" id="sub_total_dev_kindergarten">51,600.00</td>
                                    <td class="p-3 text-right font-black text-slate-900 bg-slate-50/50" id="sub_grand_kindergarten">267,600.00</td>
                                </tr>

                                <!-- Primary -->
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3 font-bold text-slate-900 flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                        ระดับประถมศึกษา (ป.1 - ป.6)
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="number" id="sub_count_primary" value="380" min="0" oninput="recalcSubsidiesLocal()" 
                                               class="w-20 text-center font-bold px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right">
                                        <input type="number" id="sub_rate_primary" value="2000" min="0" step="10" oninput="recalcSubsidiesLocal()" 
                                               class="w-24 text-right font-medium px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right font-bold text-emerald-700" id="sub_total_subsidy_primary">760,000.00</td>
                                    <td class="p-3 text-right">
                                        <input type="number" id="sub_dev_rate_primary" value="490" min="0" step="10" oninput="recalcSubsidiesLocal()" 
                                               class="w-24 text-right font-medium px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right font-bold text-purple-700" id="sub_total_dev_primary">186,200.00</td>
                                    <td class="p-3 text-right font-black text-slate-900 bg-slate-50/50" id="sub_grand_primary">946,200.00</td>
                                </tr>

                                <!-- Lower Secondary -->
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3 font-bold text-slate-900 flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        ระดับมัธยมศึกษาตอนต้น (ม.1 - ม.3)
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="number" id="sub_count_lower_secondary" value="220" min="0" oninput="recalcSubsidiesLocal()" 
                                               class="w-20 text-center font-bold px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right">
                                        <input type="number" id="sub_rate_lower_secondary" value="3600" min="0" step="10" oninput="recalcSubsidiesLocal()" 
                                               class="w-24 text-right font-medium px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right font-bold text-emerald-700" id="sub_total_subsidy_lower_secondary">792,000.00</td>
                                    <td class="p-3 text-right">
                                        <input type="number" id="sub_dev_rate_lower_secondary" value="880" min="0" step="10" oninput="recalcSubsidiesLocal()" 
                                               class="w-24 text-right font-medium px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right font-bold text-purple-700" id="sub_total_dev_lower_secondary">193,600.00</td>
                                    <td class="p-3 text-right font-black text-slate-900 bg-slate-50/50" id="sub_grand_lower_secondary">985,600.00</td>
                                </tr>

                                <!-- Upper Secondary -->
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3 font-bold text-slate-900 flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                        ระดับมัธยมศึกษาตอนปลาย (ม.4 - ม.6)
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="number" id="sub_count_upper_secondary" value="130" min="0" oninput="recalcSubsidiesLocal()" 
                                               class="w-20 text-center font-bold px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right">
                                        <input type="number" id="sub_rate_upper_secondary" value="3900" min="0" step="10" oninput="recalcSubsidiesLocal()" 
                                               class="w-24 text-right font-medium px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right font-bold text-emerald-700" id="sub_total_subsidy_upper_secondary">507,000.00</td>
                                    <td class="p-3 text-right">
                                        <input type="number" id="sub_dev_rate_upper_secondary" value="950" min="0" step="10" oninput="recalcSubsidiesLocal()" 
                                               class="w-24 text-right font-medium px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:border-blue-500">
                                    </td>
                                    <td class="p-3 text-right font-bold text-purple-700" id="sub_total_dev_upper_secondary">123,500.00</td>
                                    <td class="p-3 text-right font-black text-slate-900 bg-slate-50/50" id="sub_grand_upper_secondary">630,500.00</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-100 font-extrabold text-slate-900 border-t-2 border-slate-200">
                                <tr>
                                    <td class="p-3">รวมทั้งสิ้น (4 ช่วงชั้น)</td>
                                    <td class="p-3 text-center text-blue-700 text-sm" id="sub_foot_count">850 คน</td>
                                    <td class="p-3 text-right text-slate-400">-</td>
                                    <td class="p-3 text-right text-emerald-800 text-sm" id="sub_foot_subsidy">2,275,000.00</td>
                                    <td class="p-3 text-right text-slate-400">-</td>
                                    <td class="p-3 text-right text-purple-800 text-sm" id="sub_foot_dev">554,900.00</td>
                                    <td class="p-3 text-right text-blue-950 text-base bg-blue-100/50" id="sub_foot_grand">2,829,900.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Explanation & 4 Activities Box -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="flex items-center gap-2 mb-2 font-bold text-slate-900 text-xs">
                            <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                            <span>รายละเอียดเงินกิจกรรมพัฒนาคุณภาพผู้เรียน (4 กิจกรรมหลัก)</span>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside">
                            <li><b>1. กิจกรรมวิชาการ:</b> กิจกรรมค่ายวิชาการ ทักษะกระบวนการเรียนรู้</li>
                            <li><b>2. กิจกรรมคุณธรรม/จริยธรรม:</b> ลูกเสือ เนตรนารี ยุวกาชาด ค่ายธรรมะ</li>
                            <li><b>3. กิจกรรมทัศนศึกษา:</b> แหล่งเรียนรู้นอกห้องเรียนตามระดับชั้น</li>
                            <li><b>4. กิจกรรมการจัดการเรียนรู้ ICT:</b> คอมพิวเตอร์ เทคโนโลยี และดิจิทัล</li>
                        </ul>
                    </div>

                    <div class="bg-emerald-50/70 p-5 rounded-2xl border border-emerald-100 shadow-xs">
                        <div class="flex items-center gap-2 mb-2 font-bold text-emerald-900 text-xs">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                            <span>การเชื่อมโยงระบบจัดสรร 100% อัตโนมัติ</span>
                        </div>
                        <p class="text-xs text-emerald-800 leading-relaxed">
                            เมื่อกดปุ่ม <b>"ตัดงบเข้า 4 กลุ่มงาน 100%"</b> ระบบจะนำยอดเงินอุดหนุนรายหัวและเงิน กพพ. ที่คำนวณได้ 
                            ไปตั้งเป็นยอดเงินที่ได้รับในแหล่งงบประมาณ พร้อมคำนวณยอดจัดสรรร้อยละ 100 ให้กลุ่มวิชาการ งบประมาณ บุคคล และทั่วไป ทันที
                        </p>
                    </div>
                </div>
            </div>

            <!-- View 13: School Admin Settings & Header Logo Management -->
            <div id="view-school_settings" class="tab-view hidden space-y-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 text-blue-800 border border-blue-200 rounded-full text-xs font-bold mb-2">
                        <i data-lucide="settings" class="w-3.5 h-3.5 text-blue-600"></i>
                        สำหรับผู้ดูแลระบบประจำโรงเรียน (School Admin)
                    </div>
                    <h2 class="text-xl font-bold text-slate-900">ตั้งค่าข้อมูลพื้นฐานสถานศึกษาและตราสัญลักษณ์ (School Profile & Logo)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Admin ของโรงเรียนมีหน้าที่ตั้งค่าชื่อโรงเรียน หน่วยงานสังกัด ที่อยู่โรงเรียน นำโลโก้โรงเรียนมาใส่ 
                        และเมื่อตั้งค่าเสร็จแล้ว โลโก้และชื่อโรงเรียนจะปรากฏบนส่วนของ Header ทันที
                    </p>
                </div>

                <!-- Live Header Preview Card -->
                <div class="p-4 bg-slate-900 text-white rounded-2xl shadow-md border border-slate-800">
                    <div class="flex items-center justify-between text-xs text-slate-400 mb-2 font-semibold">
                        <span>ตัวอย่างการแสดงผลบน Header ระบบจริง (Live Header Preview):</span>
                        <span class="text-emerald-400 font-bold flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> อัปเดตสด</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md p-3 rounded-xl border border-white/10 flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center p-1 shrink-0 overflow-hidden shadow-sm">
                            <img id="previewHeaderLogo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png" 
                                 alt="ตราตัวอย่าง" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-white text-base leading-tight">ระบบบริหารแผนปฏิบัติการประจำปี</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">เปิดดำเนินงาน</span>
                            </div>
                            <p class="text-xs text-blue-200 truncate" id="previewHeaderSchoolText">
                                โรงเรียนอนุบาลพัฒนาวิทยา (รหัส SMIS: 10310001) • สังกัดสถานศึกษา
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Settings Form -->
                <form id="schoolSettingsForm" onsubmit="handleSaveSchoolSettings(event)" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- SMIS Code (Read-Only verified by Super Admin) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                รหัส SMIS สถานศึกษา 8 หลัก <span class="text-slate-400 font-normal">(เปิดโดย Super Admin)</span>
                            </label>
                            <input type="text" id="set_smis_code" readonly 
                                   class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-600 outline-none cursor-not-allowed">
                        </div>

                        <!-- School Name -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อโรงเรียน / สถานศึกษา <span class="text-red-500">*</span></label>
                            <input type="text" id="set_school_name" required oninput="updateHeaderPreview()" 
                                   placeholder="เช่น โรงเรียนอนุบาลพัฒนาวิทยา" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 outline-none focus:bg-white focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Affiliation -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">หน่วยงานต้นสังกัด <span class="text-red-500">*</span></label>
                        <input type="text" id="set_affiliation" required oninput="updateHeaderPreview()" 
                               placeholder="เช่น สำนักงานคณะกรรมการการศึกษาขั้นพื้นฐาน / องค์กรปกครองส่วนท้องถิ่น" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:border-blue-500">
                    </div>

                    <!-- Logo Settings -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                        <label class="block text-xs font-bold text-slate-800">
                            ตราสัญลักษณ์โรงเรียน / ตราประจำสถานศึกษา (แสดงบน Header และรูปเล่มแผน) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <div class="w-16 h-16 bg-white border border-slate-200 rounded-2xl flex items-center justify-center p-1.5 shrink-0 shadow-xs overflow-hidden">
                                <img id="set_logo_preview" src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png" 
                                     alt="ตัวอย่างโลโก้" class="w-full h-full object-contain">
                            </div>
                            <div class="flex-1 w-full space-y-2">
                                <input type="url" id="set_logo_url" oninput="onLogoUrlInput(this.value)" 
                                       placeholder="ใส่ URL รูปภาพตราสัญลักษณ์ เช่น https://domain.com/logo.png" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono text-slate-700 outline-none focus:border-blue-500">
                                
                                <!-- Quick Preset Logo Buttons -->
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[11px] text-slate-500 font-semibold">หรือเลือกตรามาตรฐาน:</span>
                                    <button type="button" onclick="selectPresetLogo('https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png')" 
                                            class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-bold rounded-lg border border-slate-200 transition">
                                        ตราครุฑราชการ
                                    </button>
                                    <button type="button" onclick="selectPresetLogo('https://upload.wikimedia.org/wikipedia/th/thumb/f/f9/OBEC_Logo.png/200px-OBEC_Logo.png')" 
                                            class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-bold rounded-lg border border-slate-200 transition">
                                        ตรา สพฐ.
                                    </button>
                                    <button type="button" onclick="selectPresetLogo('https://upload.wikimedia.org/wikipedia/commons/thumb/5/52/Seal_of_the_Ministry_of_Education_of_Thailand.svg/200px-Seal_of_the_Ministry_of_Education_of_Thailand.svg.png')" 
                                            class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 text-[11px] font-bold rounded-lg border border-slate-200 transition">
                                        ตรา เสมาธรรมจักร
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ที่ตั้ง / ถนน</label>
                            <input type="text" id="set_address" placeholder="เช่น 123 หมู่ 4 ถนนนิเวศกิจ" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ตำบล / แขวง</label>
                            <input type="text" id="set_subdistrict" placeholder="เช่น ในเมือง" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">อำเภอ / เขต</label>
                            <input type="text" id="set_district" placeholder="เช่น เมืองบุรีรัมย์" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">จังหวัด</label>
                            <input type="text" id="set_province" placeholder="เช่น บุรีรัมย์" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">รหัสไปรษณีย์</label>
                            <input type="text" id="set_postal_code" placeholder="เช่น 31000" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">เบอร์โทรศัพท์</label>
                            <input type="text" id="set_phone" placeholder="เช่น 044-611234" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">อีเมลโรงเรียน</label>
                            <input type="email" id="set_email" placeholder="school@obec.mail.go.th" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                    </div>

                    <!-- Officials / Signers -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ชื่อผู้อำนวยการสถานศึกษา</label>
                            <input type="text" id="set_director_name" placeholder="นายธีระพล เกียรติวิทยา" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">ตำแหน่งผู้อำนวยการ</label>
                            <input type="text" id="set_director_position" placeholder="ผู้อำนวยการโรงเรียนอนุบาลพัฒนาวิทยา" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">เจ้าหน้าที่แผนงานและงบประมาณ</label>
                            <input type="text" id="set_plan_officer_name" placeholder="นางวิไลพร งบมั่นคง" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" id="btnSaveSchoolSettings" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i> บันทึกข้อมูลและนำตราสัญลักษณ์ขึ้น Header ทันที
                        </button>
                    </div>
                </form>
            </div>

            <!-- View 14: Super Admin Control Center (SMIS 8-Digit School Activation) -->
            <div id="view-superadmin" class="tab-view hidden space-y-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-800 border border-indigo-200 rounded-full text-xs font-bold mb-2">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                        สำหรับ ผู้ดูแลระบบสูงสุด (Super Admin)
                    </div>
                    <h2 class="text-xl font-bold text-slate-900">ศูนย์ควบคุม Super Admin (เปิดใช้งานสถานศึกษา SMIS 8 หลัก)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Super Admin มีหน้าที่ในการเปิดใช้งานของโรงเรียน และแต่งตั้ง Admin ดูแลระบบของแต่ละโรงเรียนจากคุณครูที่สมัครสมาชิกเข้ามา
                    </p>
                </div>

                <!-- Card 1: Super Admin Credentials Management (Edit Username & Password) -->
                <div class="bg-white p-6 rounded-3xl border border-indigo-100 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-indigo-600 text-white rounded-2xl shadow-xs">
                                <i data-lucide="user-cog" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                    ตั้งค่าบัญชีและรหัสผ่าน Super Admin
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold border border-indigo-200">
                                        ผู้ดูแลระบบสูงสุด
                                    </span>
                                </h3>
                                <p class="text-xs text-slate-500">สามารถแก้ไข Username และ Password ของผู้ดูแลระบบสูงสุด (Super Admin) ได้ที่นี่</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-slate-500">สถานะรหัสผ่าน:</span>
                            <span id="sa-pwd-status" class="px-2 py-0.5 rounded-full font-bold bg-amber-50 text-amber-700 border border-amber-200 text-[11px]">
                                เริ่มต้น (password123)
                            </span>
                        </div>
                    </div>

                    <form id="superAdminCredentialsForm" onsubmit="handleSuperAdminCredentialsSubmit(event)" class="mt-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Username Super Admin <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="at-sign" class="w-4 h-4"></i>
                                    </div>
                                    <input type="text" id="sa_username" required placeholder="เช่น superadmin" 
                                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 outline-none focus:bg-white focus:border-indigo-500 transition">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    ชื่อ-นามสกุล Super Admin
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="user" class="w-4 h-4"></i>
                                    </div>
                                    <input type="text" id="sa_name" placeholder="ผู้ดูแลระบบสูงสุด (Super Admin)" 
                                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 outline-none focus:bg-white focus:border-indigo-500 transition">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    กำหนดรหัสผ่านใหม่ (หากต้องการเปลี่ยน)
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="key" class="w-4 h-4"></i>
                                    </div>
                                    <input type="password" id="sa_new_password" minlength="6" placeholder="รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)" 
                                           class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono outline-none focus:bg-white focus:border-indigo-500 transition">
                                    <button type="button" onclick="togglePasswordVisibility('sa_new_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    ยืนยันรหัสผ่านใหม่
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    </div>
                                    <input type="password" id="sa_confirm_password" minlength="6" placeholder="ยืนยันรหัสผ่านใหม่อีกครั้ง" 
                                           class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono outline-none focus:bg-white focus:border-indigo-500 transition">
                                    <button type="button" onclick="togglePasswordVisibility('sa_confirm_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">ตำแหน่ง / หน้าที่</label>
                                <input type="text" id="sa_position" placeholder="ผู้ดูแลระบบสูงสุด" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:border-indigo-500 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">เบอร์โทรศัพท์</label>
                                <input type="text" id="sa_phone" placeholder="เช่น 0812345678" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:border-indigo-500 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">อีเมลติดต่อ</label>
                                <input type="email" id="sa_email" placeholder="superadmin@school.ac.th" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:border-indigo-500 transition">
                            </div>
                        </div>

                        <div id="saCredentialsAlert" class="hidden p-3 rounded-xl text-xs font-medium"></div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                            <span class="text-[11px] text-slate-400">
                                * บันทึกแล้วสามารถใช้ Username และ Password ใหม่ในการเข้าสู่ระบบครั้งถัดไปได้ทันที
                            </span>
                            <button type="submit" id="btnSaveSaCredentials" 
                                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow-md shadow-indigo-500/20 flex items-center justify-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>บันทึกการแก้ไขบัญชี Super Admin</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Card 2: Real Database Installation, Connection & Migration -->
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 rounded-3xl shadow-xl border border-indigo-500/30">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="space-y-1.5 max-w-2xl">
                            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[11px] font-bold border border-emerald-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                ศูนย์บริหารและติดตั้งฐานข้อมูลจริง (MySQL / MariaDB Migrations)
                            </div>
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <i data-lucide="database" class="w-5 h-5 text-indigo-400"></i>
                                ติดตั้งและเชื่อมต่อโครงสร้างฐานข้อมูลระบบจริง
                            </h3>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                สั่งการติดตั้งตาราง MySQL ทั้ง 10 ตารางหลักโดยอัตโนมัติ (ตาราง schools, users พร้อม Super Admin, student_subsidies, fiscal_years, budget_sources, department_allocations, projects, budget_items, expenses, progress_logs)
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0 w-full md:w-auto">
                            <button type="button" onclick="toggleDbConfigPanel()" 
                                    class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl border border-white/10 transition flex items-center gap-1.5">
                                <i data-lucide="settings" class="w-3.5 h-3.5 text-indigo-300"></i>
                                <span>ตั้งค่าการเชื่อมต่อ MySQL</span>
                            </button>
                            <a href="/database.sql" download="school_action_plan_database.sql"
                               class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl border border-white/10 transition flex items-center gap-1.5">
                                <i data-lucide="download" class="w-3.5 h-3.5 text-emerald-300"></i>
                                <span>ดาวน์โหลด .sql</span>
                            </a>
                            <button id="btnInstallDb" onclick="runInstallDatabase()" 
                                    class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                <span>ติดตั้ง / อัปเดตตารางฐานข้อมูล</span>
                            </button>
                        </div>
                    </div>

                    <!-- MySQL Connection Configuration Panel (Collapsible) -->
                    <div id="dbConfigPanel" class="hidden mt-5 pt-4 border-t border-indigo-500/20">
                        <div class="bg-black/30 rounded-2xl p-4 border border-white/10">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-bold text-indigo-200 flex items-center gap-2">
                                    <i data-lucide="server" class="w-4 h-4 text-indigo-400"></i>
                                    กำหนดค่าเชื่อมต่อฐานข้อมูล MySQL จริง (Real MySQL Server Connection)
                                </h4>
                                <span id="dbConnBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                    สถานะ: รอการทดสอบ
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                                <div>
                                    <label class="block text-slate-400 text-[11px] mb-1">Host / IP</label>
                                    <input type="text" id="db_host" value="localhost" 
                                           class="w-full px-3 py-2 bg-white/5 border border-white/15 rounded-xl text-white outline-none focus:border-indigo-400">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-[11px] mb-1">Port</label>
                                    <input type="number" id="db_port" value="3306" 
                                           class="w-full px-3 py-2 bg-white/5 border border-white/15 rounded-xl text-white outline-none focus:border-indigo-400">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-[11px] mb-1">ชื่อฐานข้อมูล (Database)</label>
                                    <input type="text" id="db_name" value="school_action_plan" 
                                           class="w-full px-3 py-2 bg-white/5 border border-white/15 rounded-xl text-white outline-none focus:border-indigo-400">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-[11px] mb-1">ผู้ใช้ (User)</label>
                                    <input type="text" id="db_user" value="root" 
                                           class="w-full px-3 py-2 bg-white/5 border border-white/15 rounded-xl text-white outline-none focus:border-indigo-400">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-[11px] mb-1">รหัสผ่าน (Password)</label>
                                    <input type="password" id="db_pass" placeholder="เว้นว่างถ้าไม่มี" 
                                           class="w-full px-3 py-2 bg-white/5 border border-white/15 rounded-xl text-white outline-none focus:border-indigo-400">
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 mt-3 pt-3 border-t border-white/5">
                                <div id="dbTestMsg" class="text-[11px] text-slate-300">
                                    ค่านี้จะถูกบันทึกใน <code class="text-indigo-300">api/config.php</code> และ <code class="text-indigo-300">db-config.json</code>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="testDatabaseConnection()" id="btnTestDb" 
                                            class="px-3.5 py-1.5 bg-indigo-600/60 hover:bg-indigo-600 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5">
                                        <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                                        <span>ทดสอบการเชื่อมต่อ</span>
                                    </button>
                                    <button type="button" onclick="saveDatabaseConnection()" id="btnSaveDb" 
                                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition flex items-center gap-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>บันทึกการตั้งค่า</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Migration Progress / Output Console Box -->
                    <div id="dbInstallResultBox" class="hidden mt-5 pt-4 border-t border-indigo-500/20">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-xs font-bold text-indigo-300 flex items-center gap-1.5">
                                <i data-lucide="terminal" class="w-3.5 h-3.5 text-emerald-400"></i>
                                บันทึกผลการติดตั้งและอัปเดตฐานข้อมูล (Migration Log)
                            </span>
                            <span id="dbInstallStatusBadge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300">
                                อัปเดตล่าสุด: กำลังประมวลผล...
                            </span>
                        </div>
                        <div id="dbInstallLogContent" class="bg-black/40 rounded-xl p-3.5 font-mono text-[11px] text-slate-200 max-h-56 overflow-y-auto space-y-1.5 border border-white/5">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>

                <!-- Super Admin Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">สถานศึกษาทั้งหมด</span>
                            <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600"><i data-lucide="school" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-slate-900 mt-2" id="sa-total-schools">0 แห่ง</p>
                        <p class="text-xs text-slate-500 mt-1">ในระบบบริหารจัดการ</p>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">เปิดใช้งานแล้ว (Active)</span>
                            <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="check-circle" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-emerald-600 mt-2" id="sa-active-schools">0 แห่ง</p>
                        <p class="text-xs text-slate-500 mt-1">บุคลากรสามารถลงทะเบียนได้</p>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">รอเปิดใช้งาน (Pending)</span>
                            <div class="p-2 rounded-xl bg-amber-50 text-amber-600"><i data-lucide="clock" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-amber-600 mt-2" id="sa-pending-schools">0 แห่ง</p>
                        <p class="text-xs text-slate-500 mt-1">รอดำเนินการตรวจสอบ</p>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">บุคลากรในระบบ</span>
                            <div class="p-2 rounded-xl bg-blue-50 text-blue-600"><i data-lucide="users" class="w-5 h-5"></i></div>
                        </div>
                        <p class="text-2xl font-extrabold text-blue-600 mt-2" id="sa-total-users">0 คน</p>
                        <p class="text-xs text-slate-500 mt-1">ลงทะเบียนด้วยเลข 13 หลัก</p>
                    </div>
                </div>

                <!-- Open New School Card -->
                <div class="bg-white p-6 rounded-2xl border border-indigo-100 shadow-xs bg-gradient-to-br from-white to-indigo-50/30">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2.5 bg-indigo-600 text-white rounded-xl shadow-xs">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">เปิดใช้งานสถานศึกษาใหม่ (Activate School ด้วยรหัส SMIS 8 หลัก)</h3>
                            <p class="text-xs text-slate-500">
                                ระบุรหัส SMIS 8 หลักเพื่อเปิดระบบให้โรงเรียน (ยังไม่ต้องกำหนด Admin ในขั้นตอนนี้ เมื่อครูสมัครเข้ามาแล้วจึงเลือกแต่งตั้งต่อไป)
                            </p>
                        </div>
                    </div>

                    <!-- Instructions Banner -->
                    <div class="p-3 bg-amber-50 border border-amber-200/70 rounded-xl text-xs text-amber-900 mb-4 flex items-start gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-bold">ขั้นตอนการเปิดใช้งาน:</span> เมื่อ Super Admin เปิดใช้งานโรงเรียนแล้ว คุณครูและบุคลากรของโรงเรียนนั้นจะสามารถสมัครสมาชิกที่หน้าระบบด้วยรหัส SMIS 8 หลักได้ จากนั้น Super Admin จึงจะมาคลิกปุ่ม <b>"เลือก Admin จากครูในโรงเรียน"</b> เพื่อแต่งตั้งผู้ดูแลระบบของโรงเรียนนั้น
                        </div>
                    </div>

                    <form id="newSchoolForm" onsubmit="handleSuperAdminAddSchool(event)" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    รหัส SMIS 8 หลัก <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="sa_new_smis" maxlength="8" required 
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       placeholder="เช่น 10310004" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold outline-none focus:border-indigo-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    ชื่อสถานศึกษา <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="sa_new_name" required 
                                       placeholder="เช่น โรงเรียนมัธยมวิทยาคมบุรีรัมย์" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">สังกัดหน่วยงาน</label>
                                <input type="text" id="sa_new_affiliation" placeholder="เช่น สังกัดสถานศึกษา / สพฐ." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">อำเภอ / เขต</label>
                                <input type="text" id="sa_new_district" placeholder="อำเภอ / เขต" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">จังหวัด</label>
                                <input type="text" id="sa_new_province" placeholder="จังหวัด" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none">
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="sa_new_active" checked class="w-4 h-4 rounded text-indigo-600 cursor-pointer">
                                <label for="sa_new_active" class="text-xs font-bold text-slate-700 cursor-pointer">เปิดใช้งานทันที (Active Status)</label>
                            </div>
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-md shadow-indigo-500/20 flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4"></i> บันทึกและเปิดใช้งานสถานศึกษา
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Schools Management Table -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">บัญชีสถานศึกษาในระบบ (SMIS Management)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">เปิด-ปิดการใช้งาน และเลือกแต่งตั้งผู้ดูแลระบบของโรงเรียนจากครูที่สมัครสมาชิก</p>
                        </div>
                        <button onclick="loadSuperAdminSchools()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition flex items-center gap-1.5">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> รีเฟรชข้อมูล
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 text-slate-600 uppercase font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3.5 w-28">รหัส SMIS (8 หลัก)</th>
                                    <th class="p-3.5">ชื่อสถานศึกษา</th>
                                    <th class="p-3.5">หน่วยงานสังกัด</th>
                                    <th class="p-3.5">Admin โรงเรียนที่กำหนด</th>
                                    <th class="p-3.5 w-28 text-center">สถานะ</th>
                                    <th class="p-3.5 w-44 text-center">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="superAdminSchoolsTableBody" class="divide-y divide-slate-100">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ========================================== -->
    <!-- MODALS SECTION                             -->
    <!-- ========================================== -->

    <!-- Modal 1: Project Proposal & AI Assistant Modal -->
    <div id="projectModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden my-auto">
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-600 text-white rounded-xl shadow-xs">
                        <i data-lucide="file-plus-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="projectModalTitle">เสนอโครงการตามแผนปฏิบัติการ</h3>
                        <p class="text-xs text-slate-500">แบบฟอร์มมาตรฐาน สพฐ. พร้อมระบบ AI ช่วยร่างโครงการ</p>
                    </div>
                </div>
                <button onclick="closeProjectModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body (Form) -->
            <form id="projectForm" onsubmit="handleProjectSubmit(event)" class="flex-grow overflow-y-auto p-6 space-y-5">
                <input type="hidden" id="proj_id">

                <!-- AI Quick Draft Banner -->
                <div class="p-4 bg-gradient-to-r from-indigo-500/10 via-blue-500/10 to-purple-500/10 rounded-2xl border border-indigo-200">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-indigo-600 text-white rounded-lg"><i data-lucide="sparkles" class="w-4 h-4"></i></span>
                            <div>
                                <h4 class="text-xs font-bold text-indigo-950">AI ช่วยเขียนและปรับปรุงโครงการ (Gemini 3.8 Flash)</h4>
                                <p class="text-[11px] text-slate-600">เพียงใส่แนวคิดหรือชื่อโครงการสั้นๆ AI จะร่างเอกสารราชการ สพฐ. ให้ครบทุกหัวข้อ</p>
                            </div>
                        </div>
                        <button type="button" onclick="triggerAiDraft()" id="btnAiDraft" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0 flex items-center gap-1.5">
                            <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                            ให้ AI ร่างเอกสาร
                        </button>
                    </div>
                </div>

                <!-- Basic Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อโครงการ <span class="text-red-500">*</span></label>
                        <input type="text" id="proj_name" required placeholder="เช่น โครงการส่งเสริมความเป็นเลิศทางคณิตศาสตร์และวิทยาศาสตร์" 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">กลุ่มงานที่รับผิดชอบ <span class="text-red-500">*</span></label>
                        <select id="proj_department" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                            <option value="academic">กลุ่มบริหารวิชาการ</option>
                            <option value="budget">กลุ่มบริหารงบประมาณและสินทรัพย์</option>
                            <option value="personnel">กลุ่มบริหารงานบุคคล</option>
                            <option value="general">กลุ่มบริหารทั่วไป</option>
                            <option value="reserve">งบสำรองจ่าย/ส่วนกลาง</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">แหล่งงบประมาณที่ขอใช้</label>
                        <select id="proj_budget_source" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                            <!-- Populated dynamically -->
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">สนองมาตรฐานสถานศึกษา</label>
                        <input type="text" id="proj_standard" value="มาตรฐานที่ 1 คุณภาพของผู้เรียน" 
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ยุทธศาสตร์/นโยบาย</label>
                        <input type="text" id="proj_strategy" value="ยุทธศาสตร์พัฒนาคุณภาพการศึกษาขั้นพื้นฐาน" 
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">วันที่เริ่มต้น - สิ้นสุด</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" id="proj_start_date" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                            <input type="date" id="proj_end_date" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">สถานที่ดำเนินงาน</label>
                        <input type="text" id="proj_location" value="โรงเรียนอนุบาลพัฒนาวิทยา" 
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                    </div>
                </div>

                <!-- Details & Text Areas -->
                <div class="space-y-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-slate-700">หลักการและเหตุผล</label>
                            <button type="button" onclick="polishTextWithAI('rationale')" class="text-[11px] text-indigo-600 font-bold hover:underline flex items-center gap-1">
                                <i data-lucide="sparkles" class="w-3 h-3"></i> ขัดเกลาภาษาราชการ
                            </button>
                        </div>
                        <textarea id="proj_rationale" rows="3" placeholder="ระบุความเป็นมา ความสำคัญ สภาพปัญหา และความจำเป็น..." 
                                  class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white"></textarea>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-slate-700">วัตถุประสงค์ (ข้อ 1, 2, 3)</label>
                        </div>
                        <textarea id="proj_objectives" rows="2" placeholder="1. เพื่อส่งเสริม...&#10;2. เพื่อยกระดับ..." 
                                  class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">เป้าหมายเชิงปริมาณ</label>
                            <textarea id="proj_target_qty" rows="2" placeholder="เช่น นักเรียนชั้น ป.1-ป.6 จำนวน 320 คน เข้าร่วมกิจกรรมร้อยละ 100" 
                                      class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">เป้าหมายเชิงคุณภาพ</label>
                            <textarea id="proj_target_quality" rows="2" placeholder="เช่น ผู้เรียนมีผลสัมฤทธิ์ผ่านเกณฑ์ร้อยละ 85 ขึ้นไป" 
                                      class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Itemized Budget Breakdown Table -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-slate-700">รายการประมาณการค่าใช้จ่าย (แจกแจงตามหมวด)</label>
                            <button type="button" onclick="addBudgetItemRow()" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> เพิ่มรายการ
                            </button>
                        </div>
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-slate-100 font-bold text-slate-700">
                                    <tr>
                                        <th class="p-2 w-28">หมวด</th>
                                        <th class="p-2">รายการค่าใช้จ่าย</th>
                                        <th class="p-2 w-16 text-center">จำนวน</th>
                                        <th class="p-2 w-16">หน่วย</th>
                                        <th class="p-2 w-20 text-right">ราคา/หน่วย</th>
                                        <th class="p-2 w-24 text-right">รวมเงิน</th>
                                        <th class="p-2 w-10 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="budgetItemsTableBody" class="divide-y divide-slate-100">
                                    <!-- Dynamic Rows -->
                                </tbody>
                                <tfoot class="bg-slate-50 font-bold">
                                    <tr>
                                        <td colspan="5" class="p-2 text-right">รวมงบประมาณที่ขอ:</td>
                                        <td class="p-2 text-right text-blue-900 font-black" id="budgetItemsTotalText">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <input type="hidden" id="proj_requested_budget" value="0">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ตัวชี้วัดความสำเร็จ (KPI)</label>
                            <textarea id="proj_indicators" rows="2" placeholder="เช่น ร้อยละของนักเรียนที่มีผลคะแนนสอบผ่านเกณฑ์..." 
                                      class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ผลที่คาดว่าจะได้รับ</label>
                            <textarea id="proj_expected_outcomes" rows="2" placeholder="เช่น นักเรียนมีทักษะและเจตคติที่ดีต่อการเรียนรู้..." 
                                      class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeProjectModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                        ยกเลิก
                    </button>
                    <button type="submit" id="btnSaveProject" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i> บันทึกและเสนอโครงการ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Screening & Budget Trimming Modal -->
    <div id="screeningModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">กลั่นกรองและปรับวงเงินโครงการ</h3>
                <button onclick="closeScreeningModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="mt-4 space-y-4">
                <input type="hidden" id="screen_project_id">
                <div>
                    <span class="text-xs text-slate-500">โครงการ:</span>
                    <p class="text-sm font-bold text-slate-900 mt-0.5" id="screen_project_name">-</p>
                </div>
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl text-xs">
                    <div>
                        <span class="text-slate-500">งบที่ขอเสนอ:</span>
                        <p class="text-sm font-bold text-slate-800" id="screen_requested_amount">0.00 บาท</p>
                    </div>
                    <div>
                        <span class="text-slate-500">กลุ่มงาน:</span>
                        <p class="text-sm font-bold text-blue-700" id="screen_dept_name">-</p>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">วงเงินที่ผ่านการกลั่นกรอง/ปรับยอด (บาท)</label>
                    <input type="number" id="screen_adjusted_budget" step="0.01" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-blue-950 outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">บันทึกข้อคิดเห็นการกลั่นกรอง / เหตุผลปรับงบ</label>
                    <textarea id="screen_notes" rows="3" placeholder="ระบุเหตุผลการปรับลด หรือเงื่อนไขเพิ่มเติม..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" onclick="submitScreening('rejected')" class="px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold rounded-xl transition">
                        ตัดแผน (ไม่อนุมัติ)
                    </button>
                    <div class="flex gap-2">
                        <button type="button" onclick="submitScreening('revision_requested')" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-xl transition">
                            ส่งกลับแก้ไข
                        </button>
                        <button type="button" onclick="submitScreening('screened')" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                            ให้ความเห็นชอบ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 3: Director Approval Modal -->
    <div id="approvalModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">พิจารณาอนุมัติโครงการ (ผู้อำนวยการโรงเรียน)</h3>
                <button onclick="closeApprovalModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="mt-4 space-y-4">
                <input type="hidden" id="appr_project_id">
                <div>
                    <span class="text-xs text-slate-500">โครงการ:</span>
                    <p class="text-sm font-bold text-slate-900 mt-0.5" id="appr_project_name">-</p>
                </div>
                <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-xl text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500">งบที่ขอ:</span>
                        <span class="font-semibold text-slate-800" id="appr_req_budget">0.00 บาท</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">งบหลังกลั่นกรอง:</span>
                        <span class="font-bold text-blue-900" id="appr_screened_budget">0.00 บาท</span>
                    </div>
                    <div class="text-slate-600 pt-1" id="appr_screening_note"></div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">วงเงินอนุมัติขั้นสุดท้าย (บาท)</label>
                    <input type="number" id="appr_final_budget" step="0.01" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-blue-950 outline-none focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ข้อสั่งการ / บันทึกความเห็นผู้อำนวยการ</label>
                    <textarea id="appr_director_note" rows="3" placeholder="อนุมัติตามที่เสนอ ขอให้ดำเนินการตามระเบียบพัสดุและรายงานผล..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" onclick="submitApproval('revision_requested')" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-xl transition">
                        ขอให้แก้ไข
                    </button>
                    <button type="button" onclick="submitApproval('approved')" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-md shadow-emerald-600/20 flex items-center gap-1.5">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> อนุมัติและบรรจุในเล่มแผน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 4: Actual Expense Modal -->
    <div id="expenseModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">บันทึกรายการเบิกจ่ายจริง (Disbursement)</h3>
                <button onclick="closeExpenseModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form id="expenseForm" onsubmit="handleExpenseSubmit(event)" class="mt-4 space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">โครงการที่เบิกจ่าย <span class="text-red-500">*</span></label>
                    <select id="exp_project_id" required onchange="updateExpenseModalProjectInfo(this.value)" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none">
                        <!-- Populated dynamically -->
                    </select>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl text-xs flex justify-between">
                    <div>
                        <span class="text-slate-500">งบอนุมัติ:</span>
                        <p class="font-bold text-slate-800" id="expModalApprovedBudget">0.00 บาท</p>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-500">คงเหลือปัจจุบัน:</span>
                        <p class="font-bold text-emerald-600" id="expModalRemainingBudget">0.00 บาท</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">วันที่เบิกจ่าย</label>
                        <input type="date" id="exp_date" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">เลขที่ฎีกา/ใบสำคัญ</label>
                        <input type="text" id="exp_doc_number" required placeholder="เช่น ขบ. 45/2568" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">รายการค่าใช้จ่าย <span class="text-red-500">*</span></label>
                    <input type="text" id="exp_title" required placeholder="เช่น ค่าวัสดุอบรมและเอกสารคู่มือ" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">หมวดรายจ่าย</label>
                        <select id="exp_category" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                            <option value="materials">ค่าวัสดุ</option>
                            <option value="operating">ค่าใช้สอย</option>
                            <option value="compensation">ค่าตอบแทน</option>
                            <option value="utility">ค่าสาธารณูปโภค</option>
                            <option value="other">ค่าใช้จ่ายอื่น</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">จำนวนเงินที่เบิก (บาท) <span class="text-red-500">*</span></label>
                        <input type="number" id="exp_amount" step="0.01" required placeholder="0.00" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-blue-900 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ผู้เบิกเงิน</label>
                    <input type="text" id="exp_disbursed_by" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">เอกสารอ้างอิง/ใบเสร็จ</label>
                    <input type="text" id="exp_receipt_note" placeholder="เช่น ใบเสร็จรับเงินเล่มที่ 03 เลขที่ 12" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeExpenseModal()" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">ยกเลิก</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">บันทึกเบิกจ่าย</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 5: Tracking / Progress Update Modal -->
    <div id="trackingModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">รายงานผลและติดตามความก้าวหน้า</h3>
                <button onclick="closeTrackingModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form id="trackingForm" onsubmit="handleTrackingSubmit(event)" class="mt-4 space-y-4">
                <input type="hidden" id="track_project_id">
                <div>
                    <span class="text-xs text-slate-500">โครงการ:</span>
                    <p class="text-sm font-bold text-slate-900 mt-0.5" id="track_project_name">-</p>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-700 mb-1">
                        <span>ร้อยละความก้าวหน้าโครงการ</span>
                        <span id="track_pct_text" class="text-blue-600 font-extrabold text-sm">50%</span>
                    </div>
                    <input type="range" id="track_pct_range" min="0" max="100" value="50" oninput="document.getElementById('track_pct_text').innerText = this.value + '%'" class="w-full cursor-pointer">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">สถานะการดำเนินงาน</label>
                    <select id="track_exec_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                        <option value="not_started">ยังไม่เริ่มดำเนินการ</option>
                        <option value="in_progress">อยู่ระหว่างดำเนินงาน</option>
                        <option value="completed">เสร็จสิ้นโครงการแล้ว</option>
                        <option value="delayed">ล่าช้ากว่ากำหนด</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">สรุปผลการดำเนินงานจริง</label>
                    <textarea id="track_results" rows="3" placeholder="ระบุกิจกรรมที่จัดสำเร็จ จำนวนผู้เข้าร่วม และผลที่ได้ตามตัวชี้วัด..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ปัญหาและอุปสรรค</label>
                        <textarea id="track_obstacles" rows="2" placeholder="อุปสรรคในการจัดกิจกรรม..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">แนวทางแก้ไข/ข้อเสนอแนะ</label>
                        <textarea id="track_recommendations" rows="2" placeholder="แนวทางปรับปรุง..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none"></textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeTrackingModal()" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">ยกเลิก</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">บันทึกรายงานผล</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 6: Budget Source Modal -->
    <div id="sourceModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">เพิ่ม/แก้ไขแหล่งงบประมาณ</h3>
                <button onclick="closeSourceModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form id="sourceForm" onsubmit="handleSourceSubmit(event)" class="mt-4 space-y-3.5">
                <input type="hidden" id="src_id">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อแหล่งงบประมาณ <span class="text-red-500">*</span></label>
                    <input type="text" id="src_name" required placeholder="เช่น เงินอุดหนุนรายหัว หรือ เงินกิจกรรมพัฒนาผู้เรียน" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ประเภทงบประมาณ</label>
                    <select id="src_category" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                        <option value="subsidy">เงินอุดหนุนรายหัวการศึกษาขั้นพื้นฐาน</option>
                        <option value="student_dev">เงินกิจกรรมพัฒนาคุณภาพผู้เรียน (กพพ.)</option>
                        <option value="school_income">เงินรายได้สถานศึกษา</option>
                        <option value="donation">เงินระดมทรัพยากรและการบริจาค</option>
                        <option value="poverty_fund">เงินปัจจัยพื้นฐานนักเรียนยากจน (CCT)</option>
                        <option value="other">เงินอื่นๆ</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">จำนวนเงิน (บาท) <span class="text-red-500">*</span></label>
                        <input type="number" id="src_amount" step="0.01" required placeholder="0.00" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-blue-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">วันที่ได้รับ</label>
                        <input type="date" id="src_received_date" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">คำอธิบายเพิ่มเติม</label>
                    <textarea id="src_description" rows="2" placeholder="รายละเอียดการจัดสรร..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none"></textarea>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeSourceModal()" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">ยกเลิก</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">บันทึกแหล่งงบ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 7: Fiscal Year Setting Modal -->
    <div id="fiscalYearModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">กำหนดปีงบประมาณใหม่</h3>
                <button onclick="closeFiscalYearModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form id="fiscalYearForm" onsubmit="handleFiscalYearSubmit(event)" class="mt-4 space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ปีงบประมาณ (พ.ศ.) <span class="text-red-500">*</span></label>
                    <input type="text" id="new_fy_year" required placeholder="เช่น 2569" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">วันเริ่มต้นปีงบ</label>
                        <input type="date" id="new_fy_start" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">วันสิ้นสุดปีงบ</label>
                        <input type="date" id="new_fy_end" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="new_fy_is_current" class="w-4 h-4 rounded text-blue-600 cursor-pointer">
                    <label for="new_fy_is_current" class="text-xs font-bold text-slate-700 cursor-pointer">กำหนดให้เป็นปีงบประมาณปัจจุบัน</label>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeFiscalYearModal()" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">ยกเลิก</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">สร้างปีงบประมาณ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 8: User Profile Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">เพิ่มบุคลากรและกำหนดสิทธิ์</h3>
                <button onclick="closeUserModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form id="userForm" onsubmit="handleUserSubmit(event)" class="mt-4 space-y-3.5">
                <input type="hidden" id="usr_id">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                    <input type="text" id="usr_name" required placeholder="เช่น นายสมเกียรติ มั่นคง" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ตำแหน่ง</label>
                    <input type="text" id="usr_position" placeholder="เช่น ครูชำนาญการพิเศษ" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">กลุ่มงาน</label>
                        <select id="usr_department" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none">
                            <option value="academic">วิชาการ</option>
                            <option value="budget">งบประมาณ</option>
                            <option value="personnel">บุคคล</option>
                            <option value="general">ทั่วไป</option>
                            <option value="central">ส่วนกลาง</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">บทบาทสิทธิ์ <span class="text-red-500">*</span></label>
                        <select id="usr_role" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold outline-none">
                            <option value="teacher">ครู/ผู้รับผิดชอบโครงการ</option>
                            <option value="department_head">หัวหน้ากลุ่มงาน</option>
                            <option value="plan_officer">เจ้าหน้าที่แผน/งบประมาณ</option>
                            <option value="director">ผู้อำนวยการโรงเรียน</option>
                            <option value="admin">ผู้ดูแลระบบ</option>
                        </select>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeUserModal()" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">ยกเลิก</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">บันทึกผู้ใช้</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 9: Change Password Modal (First-time login or on-demand) -->
    <div id="changePasswordModal" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-amber-100 text-amber-800 rounded-xl">
                        <i data-lucide="key" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">เปลี่ยนรหัสผ่านเพื่อความปลอดภัย</h3>
                        <p class="text-xs text-slate-500">สำหรับผู้ใช้งานครั้งแรกหรือเปลี่ยนรหัสผ่านใหม่</p>
                    </div>
                </div>
                <button type="button" onclick="closeChangePasswordModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <div id="mustChangePwdAlert" class="hidden my-3 p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-xs flex items-start gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                <div>
                    <b>แจ้งเตือนความปลอดภัย:</b> บัญชีของท่านยังใช้รหัสผ่านเริ่มต้น (1-6) กรุณากำหนดรหัสผ่านใหม่เพื่อความปลอดภัยของข้อมูลสถานศึกษา
                </div>
            </div>

            <form id="changePasswordForm" onsubmit="handleChangePasswordSubmit(event)" class="mt-4 space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">รหัสผ่านปัจจุบัน</label>
                    <input type="password" id="chg_old_password" required placeholder="ใส่รหัสผ่านเดิม (เช่น 123456)" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร) <span class="text-red-500">*</span></label>
                    <input type="password" id="chg_new_password" required minlength="6" placeholder="รหัสผ่านใหม่" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ยืนยันรหัสผ่านใหม่ <span class="text-red-500">*</span></label>
                    <input type="password" id="chg_confirm_password" required minlength="6" placeholder="ยืนยันรหัสผ่านใหม่อีกครั้ง" 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:border-amber-500">
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeChangePasswordModal()" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">ยกเลิก</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-md shadow-amber-500/20 flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i> บันทึกรหัสผ่านใหม่
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 10: Assign School Admin Modal (Select registered teacher) -->
    <div id="assignAdminModal" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-indigo-100 text-indigo-700 rounded-xl">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">แต่งตั้งผู้ดูแลระบบประจำโรงเรียน (School Admin)</h3>
                        <p class="text-xs text-slate-500">เลือกคุณครูที่ลงทะเบียนในสถานศึกษานี้เพื่อมอบหมายสิทธิ์ Admin ดูแลระบบ</p>
                    </div>
                </div>
                <button type="button" onclick="closeAssignAdminModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- School Info Summary Box -->
            <div class="my-4 p-4 bg-indigo-50/60 border border-indigo-100 rounded-2xl shrink-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span id="assignAdminSchoolName" class="font-bold text-slate-900 text-sm">กำลังโหลด...</span>
                            <span id="assignAdminSmisBadge" class="px-2 py-0.5 bg-indigo-200/60 text-indigo-800 text-[11px] font-mono font-bold rounded-md">SMIS: -</span>
                        </div>
                        <p id="assignAdminAffiliation" class="text-xs text-slate-500 mt-0.5">-</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-[11px] text-slate-500 block">Admin ปัจจุบัน:</span>
                        <span id="assignAdminCurrentAdmin" class="text-xs font-bold text-indigo-900">-</span>
                    </div>
                </div>
            </div>

            <!-- Registered Teachers List Container -->
            <div class="flex-1 overflow-y-auto pr-1">
                <div class="flex items-center justify-between mb-2.5">
                    <h4 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="users" class="w-4 h-4 text-indigo-600"></i>
                        รายชื่อคุณครูและบุคลากรที่สมัครสมาชิกแล้วในโรงเรียนนี้
                        <span id="assignAdminTeacherCount" class="px-2 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-bold rounded-full">0 คน</span>
                    </h4>
                </div>

                <!-- Teacher List Content or Empty State -->
                <div id="assignAdminTeacherList" class="space-y-2.5">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end shrink-0 mt-4">
                <button type="button" onclick="closeAssignAdminModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>

    <!-- Frontend Scripting Logic -->
    <script src="dashboard.js"></script>
</body>
</html>
