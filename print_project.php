<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบเสนอโครงการ - แผนปฏิบัติการประจำปี</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f1f5f9; color: #000; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .print-container { box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; padding: 0 !important; }
            @page { size: A4; margin: 20mm 15mm 20mm 20mm; }
        }
    </style>
</head>
<body class="py-8">

    <!-- Top Action Bar for Screen view -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                กลับหน้าระบบ
            </a>
            <span class="text-sm text-slate-500 font-medium">แบบฟอร์มเสนอโครงการตามมาตรฐาน สพฐ.</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                พิมพ์เอกสาร (Print / PDF)
            </button>
        </div>
    </div>

    <!-- Official Document Container (A4 layout) -->
    <div id="documentContent" class="print-container max-w-4xl mx-auto bg-white p-12 rounded-xl shadow-lg border border-slate-200 text-slate-900 leading-relaxed text-[15px]">
        <div class="text-center py-12 text-slate-400 font-medium" id="loadingState">
            กำลังโหลดข้อมูลโครงการ...
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const projectId = urlParams.get('id') || '1';

        async function loadProject() {
            try {
                const res = await fetch(`/api/plan/get_project_detail.php?id=${projectId}`);
                const data = await res.json();
                if (data.status === 'success') {
                    renderDocument(data);
                } else {
                    document.getElementById('documentContent').innerHTML = `<div class="text-center py-12 text-red-500">${data.message || 'ไม่พบโครงการ'}</div>`;
                }
            } catch (err) {
                console.error(err);
                document.getElementById('documentContent').innerHTML = `<div class="text-center py-12 text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>`;
            }
        }

        function formatBaht(num) {
            return Number(num || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function renderDocument(data) {
            const p = data.project;
            const school = data.school;
            const year = data.fiscal_year ? data.fiscal_year.year : '2568';
            const items = data.items || [];

            const deptNames = {
                academic: 'กลุ่มบริหารวิชาการ',
                budget: 'กลุ่มบริหารงบประมาณและสินทรัพย์',
                personnel: 'กลุ่มบริหารงานบุคคล',
                general: 'กลุ่มบริหารทั่วไป',
                reserve: 'งบสำรองจ่าย/ส่วนกลาง'
            };

            const catNames = {
                compensation: 'ค่าตอบแทน',
                operating: 'ค่าใช้สอย',
                materials: 'ค่าวัสดุ',
                utility: 'ค่าสาธารณูปโภค',
                other: 'ค่าใช้จ่ายอื่น'
            };

            let itemsTableHtml = '';
            if (items.length > 0) {
                let total = 0;
                itemsTableHtml = items.map((it, idx) => {
                    const t = parseFloat(it.total_price) || 0;
                    total += t;
                    return `
                        <tr>
                            <td class="border border-slate-300 px-3 py-2 text-center text-sm">${idx + 1}</td>
                            <td class="border border-slate-300 px-3 py-2 text-sm">${catNames[it.category] || it.category}</td>
                            <td class="border border-slate-300 px-3 py-2 text-sm">${it.item_name}</td>
                            <td class="border border-slate-300 px-3 py-2 text-center text-sm">${it.quantity} ${it.unit}</td>
                            <td class="border border-slate-300 px-3 py-2 text-right text-sm">${formatBaht(it.unit_price)}</td>
                            <td class="border border-slate-300 px-3 py-2 text-right text-sm font-medium">${formatBaht(t)}</td>
                        </tr>
                    `;
                }).join('');

                itemsTableHtml += `
                    <tr class="bg-slate-50 font-bold">
                        <td colspan="5" class="border border-slate-300 px-3 py-2 text-right">รวมงบประมาณทั้งสิ้น</td>
                        <td class="border border-slate-300 px-3 py-2 text-right text-blue-900">${formatBaht(p.approved_budget > 0 ? p.approved_budget : total)} บาท</td>
                    </tr>
                `;
            } else {
                itemsTableHtml = `
                    <tr>
                        <td colspan="6" class="border border-slate-300 px-3 py-4 text-center text-slate-500">
                            งบประมาณรวมทั้งสิ้น ${formatBaht(p.approved_budget > 0 ? p.approved_budget : p.requested_budget)} บาท (เบิกจ่ายจาก ${p.budget_source_name})
                        </td>
                    </tr>
                `;
            }

            const html = `
                <!-- Garuda Embelm -->
                <div class="text-center mb-6">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png" 
                         alt="ตราครุฑ" class="w-16 h-auto mx-auto mb-2 opacity-95">
                    <h1 class="text-xl font-bold tracking-tight text-slate-900">แบบเสนอโครงการตามแผนปฏิบัติการประจำปีงบประมาณ พ.ศ. ${year}</h1>
                    <h2 class="text-base font-semibold text-slate-700">${school.name}</h2>
                    <p class="text-sm text-slate-600">${school.affiliation}</p>
                </div>

                <div class="border-t-2 border-b-2 border-slate-800 py-2 mb-6 flex justify-between text-sm font-semibold">
                    <span>รหัสโครงการ: ${p.code || '-'}</span>
                    <span>กลุ่มงานที่รับผิดชอบ: ${deptNames[p.department] || p.department}</span>
                    <span>ปีงบประมาณ: พ.ศ. ${year}</span>
                </div>

                <!-- Section 1 to 13 -->
                <div class="space-y-4 text-justify">
                    <div>
                        <span class="font-bold">1. ชื่อโครงการ:</span> <span class="font-semibold text-blue-950">${p.name}</span>
                    </div>

                    <div>
                        <span class="font-bold">2. ความสอดคล้องกับมาตรฐานและยุทธศาสตร์:</span>
                        <div class="ml-6 space-y-1 text-sm mt-1">
                            <p>• <strong>มาตรฐานการศึกษาของสถานศึกษา:</strong> ${p.standard_alignment || 'มาตรฐานที่ 1 คุณภาพของผู้เรียน'}</p>
                            <p>• <strong>ยุทธศาสตร์/นโยบาย:</strong> ${p.strategy_alignment || 'ยุทธศาสตร์พัฒนาคุณภาพการศึกษาขั้นพื้นฐาน'}</p>
                        </div>
                    </div>

                    <div>
                        <span class="font-bold">3. กลุ่มงานที่รับผิดชอบ:</span> ${deptNames[p.department] || p.department}
                    </div>

                    <div>
                        <span class="font-bold">4. ผู้รับผิดชอบโครงการ:</span> ${p.proposer_name} (${p.proposer_position || 'ครู'})
                    </div>

                    <div>
                        <span class="font-bold">5. ระยะเวลาและสถานที่ดำเนินงาน:</span>
                        <span class="ml-2 text-sm">${p.start_date || 'ตลอดปีการศึกษา'} ถึง ${p.end_date || 'สิ้นสุดปีงบประมาณ'} ณ ${p.location || 'โรงเรียน'}</span>
                    </div>

                    <div>
                        <span class="font-bold">6. หลักการและเหตุผล:</span>
                        <p class="ml-6 text-sm text-slate-800 whitespace-pre-line mt-1 indent-8">${p.rationale || '-'}</p>
                    </div>

                    <div>
                        <span class="font-bold">7. วัตถุประสงค์:</span>
                        <div class="ml-6 text-sm text-slate-800 whitespace-pre-line mt-1">${p.objectives || '-'}</div>
                    </div>

                    <div>
                        <span class="font-bold">8. เป้าหมาย:</span>
                        <div class="ml-6 text-sm space-y-1 mt-1">
                            <p><strong>8.1 เชิงปริมาณ:</strong> ${p.target_qty || '-'}</p>
                            <p><strong>8.2 เชิงคุณภาพ:</strong> ${p.target_quality || '-'}</p>
                        </div>
                    </div>

                    <div>
                        <span class="font-bold">9. แหล่งงบประมาณและการแจกแจงค่าใช้จ่าย:</span>
                        <p class="ml-6 text-sm mb-2">ขอใช้งบประมาณจาก <strong>${p.budget_source_name}</strong> เป็นจำนวนเงิน <strong>${formatBaht(p.approved_budget > 0 ? p.approved_budget : p.requested_budget)}</strong> บาท ดังรายการต่อไปนี้:</p>
                        
                        <div class="overflow-x-auto ml-2 mt-1 mb-2">
                            <table class="w-full border-collapse border border-slate-300 text-left">
                                <thead>
                                    <tr class="bg-slate-100 text-slate-800 text-xs uppercase font-bold">
                                        <th class="border border-slate-300 px-3 py-2 text-center w-12">ลำดับ</th>
                                        <th class="border border-slate-300 px-3 py-2 w-28">หมวดรายจ่าย</th>
                                        <th class="border border-slate-300 px-3 py-2">รายการค่าใช้จ่าย</th>
                                        <th class="border border-slate-300 px-3 py-2 text-center w-24">จำนวน</th>
                                        <th class="border border-slate-300 px-3 py-2 text-right w-24">ราคา/หน่วย</th>
                                        <th class="border border-slate-300 px-3 py-2 text-right w-28">รวมเป็นเงิน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsTableHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <span class="font-bold">10. ตัวชี้วัดความสำเร็จและการประเมินผล:</span>
                        <div class="ml-6 text-sm space-y-1 mt-1">
                            <p><strong>ตัวชี้วัด (KPI):</strong> ${p.indicators || '-'}</p>
                            <p><strong>วิธีการและเครื่องมือประเมินผล:</strong> ${p.evaluation_method || '-'}</p>
                        </div>
                    </div>

                    <div>
                        <span class="font-bold">11. ผลที่คาดว่าจะได้รับ:</span>
                        <p class="ml-6 text-sm whitespace-pre-line mt-1">${p.expected_outcomes || '-'}</p>
                    </div>

                    ${p.screening_note ? `
                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-sm">
                        <span class="font-bold text-amber-900">บันทึกการกลั่นกรองและปรับวงเงิน:</span> ${p.screening_note}
                    </div>` : ''}

                    ${p.director_note ? `
                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-200 text-sm">
                        <span class="font-bold text-blue-900">บันทึกความเห็น/สั่งการของผู้อำนวยการโรงเรียน:</span> ${p.director_note}
                    </div>` : ''}
                </div>

                <!-- Signatures Section -->
                <div class="mt-12 pt-6 border-t border-slate-200 grid grid-cols-3 gap-6 text-center text-sm page-break-inside-avoid">
                    <!-- Proposer -->
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-800">ลงชื่อ..........................................................</p>
                        <p class="font-medium text-slate-700">(${p.proposer_name})</p>
                        <p class="text-xs text-slate-500">${p.proposer_position || 'ครูผู้รับผิดชอบโครงการ'}</p>
                        <p class="text-xs text-slate-500 font-semibold mt-1">ผู้เสนอโครงการ</p>
                    </div>

                    <!-- Endorser / Head of Department -->
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-800">ลงชื่อ..........................................................</p>
                        <p class="font-medium text-slate-700">(${p.supervisor_name || '..........................................................'})</p>
                        <p class="text-xs text-slate-500">${p.supervisor_position || 'หัวหน้ากลุ่มงาน'}</p>
                        <p class="text-xs text-slate-500 font-semibold mt-1">ผู้เห็นชอบโครงการ</p>
                    </div>

                    <!-- Approver / Director -->
                    <div class="space-y-1">
                        <p class="font-semibold text-slate-800">ลงชื่อ..........................................................</p>
                        <p class="font-medium text-slate-700">(${school.director_name || 'ผู้อำนวยการโรงเรียน'})</p>
                        <p class="text-xs text-slate-500">ผู้อำนวยการ${school.name}</p>
                        <p class="text-xs text-slate-500 font-semibold mt-1">ผู้อนุมัติโครงการ</p>
                    </div>
                </div>
            `;

            document.getElementById('documentContent').innerHTML = html;
        }

        loadProject();
    </script>
</body>
</html>
