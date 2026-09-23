<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[1000px]">
        <thead>
            <tr class="bg-slate-50 text-slate-600 border-b border-slate-200">
                <th rowspan="2" class="p-4 font-bold text-sm border-r border-slate-200 w-16 text-center">ที่</th>
                <th rowspan="2" class="p-4 font-bold text-sm border-r border-slate-200">ชื่อ-นามสกุล</th>
                <th colspan="3" class="p-2 font-bold text-sm border-r border-slate-200 text-center bg-blue-50/50">ภาคเรียนที่ 1</th>
                <th colspan="3" class="p-2 font-bold text-sm border-r border-slate-200 text-center bg-green-50/50">ภาคเรียนที่ 2</th>
                <th colspan="2" class="p-2 font-bold text-sm text-center bg-purple-50/50">รวมทั้งปีการศึกษา</th>
            </tr>
            <tr class="bg-slate-50 text-slate-600 border-b border-slate-200">
                <th class="p-2 font-bold text-xs border-r border-slate-200 text-center w-20">คะแนนรวม</th>
                <th class="p-2 font-bold text-xs border-r border-slate-200 text-center w-20">ร้อยละ</th>
                <th class="p-2 font-bold text-xs border-r border-slate-200 text-center w-16">เกรด</th>
                <th class="p-2 font-bold text-xs border-r border-slate-200 text-center w-20">คะแนนรวม</th>
                <th class="p-2 font-bold text-xs border-r border-slate-200 text-center w-20">ร้อยละ</th>
                <th class="p-2 font-bold text-xs border-r border-slate-200 text-center w-16">เกรด</th>
                <th class="p-2 font-bold text-xs border-r border-slate-200 text-center w-24">ร้อยละเฉลี่ย</th>
                <th class="p-2 font-bold text-xs text-center w-20">เกรดเฉลี่ย</th>
            </tr>
        </thead>
        <tbody id="annual-grading-table-body">
            <!-- จะถูกเติมด้วย JavaScript -->
        </tbody>
    </table>
</div>

<script>
function renderAnnualTable() {
    const tbody = document.getElementById('annual-grading-table-body');
    if (!tbody) return;

    if (currentStudents.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="p-8 text-center text-slate-400">ไม่พบข้อมูลนักเรียน</td></tr>';
        return;
    }

    tbody.innerHTML = currentStudents.map((s, index) => {
        const annualPercent = parseFloat(s.annual_percent || 0);
        const annualGrade = calculateGradeFromPercent(annualPercent);

        return `
            <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                <td class="p-3 text-center text-sm text-slate-500 border-r border-slate-100">${index + 1}</td>
                <td class="p-3 border-r border-slate-100">
                    <div class="font-medium text-slate-800">${s.prefix || ''}${s.name || ''}&nbsp;${s.last_name || ''}</div>
                    <div class="text-[10px] text-slate-400 font-mono">${s.student_code}</div>
                </td>
                <td class="p-3 text-center text-sm border-r border-slate-100 bg-blue-50/10">${s.sem1_units || 0}</td>
                <td class="p-3 text-center text-sm border-r border-slate-100 bg-blue-50/10 font-bold text-blue-600">${parseFloat(s.sem1_percent || 0).toFixed(1)}</td>
                <td class="p-3 text-center text-sm border-r border-slate-100 bg-blue-50/10 font-bold">${s.sem1_grade || '-'}</td>
                
                <td class="p-3 text-center text-sm border-r border-slate-100 bg-green-50/10">${s.sem2_units || 0}</td>
                <td class="p-3 text-center text-sm border-r border-slate-100 bg-green-50/10 font-bold text-green-600">${parseFloat(s.sem2_percent || 0).toFixed(1)}</td>
                <td class="p-3 text-center text-sm border-r border-slate-100 bg-green-50/10 font-bold">${s.sem2_grade || '-'}</td>
                
                <td class="p-3 text-center text-sm border-r border-slate-100 bg-purple-50/10 font-bold text-purple-600">${annualPercent.toFixed(1)}</td>
                <td class="p-3 text-center text-sm bg-purple-50/10 font-bold text-purple-700 text-lg">${annualGrade}</td>
            </tr>
        `;
    }).join('');
}

function calculateGradeFromPercent(percent) {
    if (percent >= 80) return '4';
    if (percent >= 75) return '3.5';
    if (percent >= 70) return '3';
    if (percent >= 65) return '2.5';
    if (percent >= 60) return '2';
    if (percent >= 55) return '1.5';
    if (percent >= 50) return '1';
    return '0';
}
</script>
