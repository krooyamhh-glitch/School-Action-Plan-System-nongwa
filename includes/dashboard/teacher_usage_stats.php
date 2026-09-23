<!-- Teacher Usage Statistics Section -->
<div id="teacher-usage-stats" class="section hidden space-y-6">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-xl font-black text-slate-800">สถิติการใช้งานระบบของคุณครู</h3>
                <p class="text-sm text-slate-500">ข้อมูลการเข้าใช้งานระบบล่าสุดและจำนวนครั้งที่เข้าใช้งาน</p>
            </div>
            <button onclick="loadTeacherUsageStats()" class="flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors font-bold text-sm">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> รีเฟรชข้อมูล
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ลำดับ</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">จำนวนการล็อกอิน</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">การใช้งานล่าสุด</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">สถานะ</th>
                    </tr>
                </thead>
                <tbody id="usage-stats-table-body" class="divide-y divide-slate-100">
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function loadTeacherUsageStats() {
    const tbody = document.getElementById('usage-stats-table-body');
    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">กำลังโหลดข้อมูล...</td></tr>';

    try {
        const response = await fetch('api/admin/get_teacher_usage_stats.php');
        const data = await response.json();

        if (data.error) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-red-500">${data.error}</td></tr>`;
            return;
        }

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-10 text-center text-slate-400">ไม่มีข้อมูลการใช้งาน</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        data.forEach((teacher, index) => {
            const lastLogin = teacher.last_login ? new Date(teacher.last_login).toLocaleString('th-TH') : 'ยังไม่เคยเข้าใช้งาน';
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition-colors';
            tr.innerHTML = `
                <td class="px-6 py-4 text-sm text-slate-600 font-medium">${index + 1}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold capitalize">
                            ${teacher.name.charAt(0)}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-800">${teacher.name} ${teacher.last_name || ''}</div>
                            <div class="text-[10px] text-slate-400">${teacher.position || 'คุณครู'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500">${teacher.username}</td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-bold">
                        ${teacher.login_count || 0} ครั้ง
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">${lastLogin}</td>
                <td class="px-6 py-4">
                    ${getStatusBadge(teacher.last_login)}
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    } catch (error) {
        console.error('Error loading usage stats:', error);
        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>`;
    }
}

function getStatusBadge(lastLogin) {
    if (!lastLogin) return '<span class="w-2 h-2 rounded-full bg-slate-300 inline-block mr-1"></span> <span class="text-xs text-slate-400">ออฟไลน์</span>';
    
    const lastDate = new Date(lastLogin);
    const now = new Date();
    const diffHours = (now - lastDate) / (1000 * 60 * 60);

    if (diffHours < 1) {
        return '<span class="w-2 h-2 rounded-full bg-green-500 inline-block mr-1 animate-pulse"></span> <span class="text-xs text-green-600 font-medium">ออนไลน์</span>';
    } else if (diffHours < 24) {
        return '<span class="w-2 h-2 rounded-full bg-blue-400 inline-block mr-1"></span> <span class="text-xs text-blue-500">ใช้งานวันนี้</span>';
    } else {
        return '<span class="w-2 h-2 rounded-full bg-slate-300 inline-block mr-1"></span> <span class="text-xs text-slate-400">ออฟไลน์</span>';
    }
}
</script>
