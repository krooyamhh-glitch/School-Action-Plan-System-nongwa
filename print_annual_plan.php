<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รูปเล่มแผนปฏิบัติการประจำปีของโรงเรียน - ฉบับสมบูรณ์</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        @media print {
            body { background: #fff !important; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .page { 
                page-break-after: always; 
                padding: 25mm 20mm 20mm 25mm !important; 
                min-height: 297mm; 
                box-shadow: none !important; 
                margin: 0 !important; 
                width: 100% !important; 
                max-width: 100% !important; 
            }
            .page-cover {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                page-break-after: always;
                min-height: 297mm;
                padding: 30mm 25mm !important;
            }
            @page { size: A4; margin: 0; }
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 25mm 20mm 20mm 25mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="py-6">

    <!-- Top Action Bar for Screen view -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                กลับหน้าระบบ
            </a>
            <span class="text-sm font-bold text-slate-800">รูปเล่มแผนปฏิบัติการประจำปีสถานศึกษา (ฉบับพิมพ์เล่มรวม)</span>
        </div>
        <div class="flex items-center gap-3">
            <select id="yearSelector" onchange="loadBook(this.value)" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold">
                <option value="1">ปีงบประมาณ 2568</option>
                <option value="2">ปีงบประมาณ 2567</option>
            </select>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-blue-500/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                สั่งพิมพ์เล่มแผน (Print / PDF)
            </button>
        </div>
    </div>

    <div id="bookContainer">
        <!-- Rendered Pages will appear here -->
        <div class="text-center py-24 text-slate-400 font-medium">กำลังเตรียมรูปเล่มแผนปฏิบัติการประจำปี...</div>
    </div>

    <script>
        function formatBaht(num) {
            return Number(num || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        async function loadBook(yearId = 1) {
            try {
                const res = await fetch(`/api/plan/get_data.php?year_id=${yearId}`);
                const data = await res.json();
                if (data.status === 'success') {
                    renderBook(data);
                } else {
                    document.getElementById('bookContainer').innerHTML = `<div class="text-center py-12 text-red-500">${data.message || 'ไม่พบข้อมูล'}</div>`;
                }
            } catch (err) {
                console.error(err);
                document.getElementById('bookContainer').innerHTML = `<div class="text-center py-12 text-red-500">เกิดข้อผิดพลาดในการโหลดรูปเล่ม</div>`;
            }
        }

        function renderBook(data) {
            const school = data.school;
            const year = data.currentFiscalYear.year;
            const sources = data.budgetSources || [];
            const allocations = data.departmentAllocations || [];
            const projects = data.projects || [];
            const summary = data.summary;

            const deptNames = {
                academic: 'กลุ่มบริหารวิชาการ',
                budget: 'กลุ่มบริหารงบประมาณและสินทรัพย์',
                personnel: 'กลุ่มบริหารงานบุคคล',
                general: 'กลุ่มบริหารทั่วไป',
                reserve: 'งบสำรองจ่าย/ส่วนกลาง'
            };

            // Build projects rows for summary table
            let projectSummaryRows = '';
            projects.forEach((p, idx) => {
                projectSummaryRows += `
                    <tr>
                        <td class="border border-slate-300 px-3 py-2 text-center text-sm">${idx + 1}</td>
                        <td class="border border-slate-300 px-3 py-2 text-sm font-semibold">${p.code || '-'}</td>
                        <td class="border border-slate-300 px-3 py-2 text-sm">${p.name}</td>
                        <td class="border border-slate-300 px-3 py-2 text-sm">${deptNames[p.department] || p.department}</td>
                        <td class="border border-slate-300 px-3 py-2 text-sm">${p.proposer_name || '-'}</td>
                        <td class="border border-slate-300 px-3 py-2 text-right text-sm font-semibold">${formatBaht(p.approved_budget > 0 ? p.approved_budget : p.requested_budget)}</td>
                    </tr>
                `;
            });

            // Build detailed project cards
            let detailedProjectsHtml = '';
            projects.forEach((p, idx) => {
                detailedProjectsHtml += `
                    <div class="page text-[15px] leading-relaxed">
                        <div class="border-b-2 border-slate-800 pb-2 mb-4 flex justify-between items-center">
                            <span class="font-bold text-slate-800">แผนปฏิบัติการประจำปีงบประมาณ พ.ศ. ${year}</span>
                            <span class="text-sm font-semibold text-slate-600">${school.name}</span>
                        </div>

                        <div class="text-center mb-6">
                            <h3 class="text-lg font-bold text-slate-900">${idx + 1}. แบบเสนอโครงการตามแผนปฏิบัติการ</h3>
                            <p class="text-base font-semibold text-blue-900">${p.name}</p>
                            <p class="text-sm text-slate-600">รหัสโครงการ: ${p.code} | กลุ่มงาน: ${deptNames[p.department] || p.department}</p>
                        </div>

                        <div class="space-y-3 text-sm text-justify">
                            <div><strong>1. ผู้รับผิดชอบโครงการ:</strong> ${p.proposer_name}</div>
                            <div><strong>2. สนองมาตรฐานการศึกษา:</strong> ${p.standard_alignment || 'มาตรฐานที่ 1 คุณภาพของผู้เรียน'}</div>
                            <div><strong>3. สนองนโยบาย/ยุทธศาสตร์:</strong> ${p.strategy_alignment || 'ยุทธศาสตร์พัฒนาคุณภาพการศึกษา'}</div>
                            <div><strong>4. ระยะเวลาดำเนินการ:</strong> ${p.start_date || 'ตลอดปีงบประมาณ'} ถึง ${p.end_date || 'สิ้นสุดปีงบประมาณ'} ณ ${p.location || 'โรงเรียน'}</div>
                            <div>
                                <strong>5. หลักการและเหตุผล:</strong>
                                <p class="mt-1 text-slate-800 indent-6">${p.rationale || '-'}</p>
                            </div>
                            <div>
                                <strong>6. วัตถุประสงค์:</strong>
                                <div class="mt-1 whitespace-pre-line text-slate-800 ml-4">${p.objectives || '-'}</div>
                            </div>
                            <div>
                                <strong>7. เป้าหมาย:</strong>
                                <div class="ml-4 space-y-0.5 mt-1">
                                    <p>• เชิงปริมาณ: ${p.target_qty || '-'}</p>
                                    <p>• เชิงคุณภาพ: ${p.target_quality || '-'}</p>
                                </div>
                            </div>
                            <div>
                                <strong>8. งบประมาณที่ขอรับการจัดสรร:</strong>
                                <span class="font-bold text-blue-900">${formatBaht(p.approved_budget > 0 ? p.approved_budget : p.requested_budget)} บาท</span>
                                <span class="text-slate-600">(แหล่งงบประมาณ: ${p.budget_source_name})</span>
                            </div>
                            <div>
                                <strong>9. ตัวชี้วัดความสำเร็จ:</strong>
                                <p class="ml-4 mt-1">${p.indicators || '-'}</p>
                            </div>
                            <div>
                                <strong>10. ผลที่คาดว่าจะได้รับ:</strong>
                                <p class="ml-4 mt-1 whitespace-pre-line">${p.expected_outcomes || '-'}</p>
                            </div>
                        </div>

                        <div class="mt-10 pt-4 border-t border-slate-200 grid grid-cols-3 gap-4 text-center text-xs">
                            <div>
                                <p>ลงชื่อ............................................</p>
                                <p class="mt-1 font-semibold">(${p.proposer_name})</p>
                                <p class="text-slate-500">ผู้เสนอโครงการ</p>
                            </div>
                            <div>
                                <p>ลงชื่อ............................................</p>
                                <p class="mt-1 font-semibold">(${school.plan_officer_name || 'เจ้าหน้าที่แผนงาน'})</p>
                                <p class="text-slate-500">ผู้กลั่นกรองแผนงาน</p>
                            </div>
                            <div>
                                <p>ลงชื่อ............................................</p>
                                <p class="mt-1 font-semibold">(${school.director_name})</p>
                                <p class="text-slate-500">ผู้อำนวยการสถานศึกษา</p>
                            </div>
                        </div>
                    </div>
                `;
            });

            const html = `
                <!-- PAGE 1: COVER PAGE -->
                <div class="page page-cover text-center relative border-8 border-double border-blue-900/40 p-16">
                    <div class="pt-8">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png" 
                             alt="ตราครุฑ" class="w-24 h-auto mx-auto mb-6 opacity-95">
                        <p class="text-blue-900 font-extrabold tracking-widest text-lg uppercase mb-2">School Action Plan</p>
                        <h1 class="text-3xl font-extrabold text-slate-900 mb-2">แผนปฏิบัติการประจำปี</h1>
                        <h2 class="text-2xl font-bold text-blue-900 mb-4">ปีงบประมาณ พ.ศ. ${year}</h2>
                        <div class="w-24 h-1 bg-blue-600 mx-auto my-4 rounded-full"></div>
                    </div>

                    <div class="my-auto py-8">
                        <h3 class="text-2xl font-bold text-slate-800">${school.name}</h3>
                        <p class="text-lg text-slate-600 mt-2">${school.affiliation}</p>
                        <p class="text-base text-slate-500 mt-1">สำนักงานคณะกรรมการการศึกษาขั้นพื้นฐาน กระทรวงศึกษาธิการ</p>
                    </div>

                    <div class="pb-8 text-sm text-slate-600 font-medium">
                        <p>กรอบวงเงินงบประมาณรวมทั้งสิ้น <strong>${formatBaht(summary.totalBudgetReceived)}</strong> บาท</p>
                        <p class="mt-1">จำนวนโครงการตามแผนทั้งสิ้น <strong>${summary.totalProjectsCount}</strong> โครงการ</p>
                    </div>
                </div>

                <!-- PAGE 2: PREFACE (คำนำ) & TABLE OF CONTENTS -->
                <div class="page text-[15px] leading-relaxed">
                    <h2 class="text-2xl font-bold text-center text-slate-900 mb-6">คำนำ</h2>
                    <p class="indent-8 text-justify text-slate-800 mb-4">
                        แผนปฏิบัติการประจำปีงบประมาณ พ.ศ. ${year} ของ${school.name} จัดทำขึ้นเพื่อเป็นกรอบทิศทางในการบริหารจัดการศึกษาและการดำเนินงานตามภารกิจของสถานศึกษาให้บรรลุเป้าหมายตามมาตรฐานการศึกษาขั้นพื้นฐาน นโยบายของสำนักงานคณะกรรมการการศึกษาขั้นพื้นฐาน และความต้องการของชุมชนท้องถิ่น
                    </p>
                    <p class="indent-8 text-justify text-slate-800 mb-4">
                        การจัดทำแผนปฏิบัติการฉบับนี้ ได้รับความร่วมมือร่วมใจจากคณะครู บุคลากรทางการศึกษา คณะกรรมการสถานศึกษาขั้นพื้นฐาน และผู้มีส่วนเกี่ยวข้องทุกฝ่าย ในการร่วมกันกำหนดวิสัยทัศน์ พันธกิจ เป้าหมาย รวมถึงการจัดสรรงบประมาณอย่างมีประสิทธิภาพ โปร่งใส ตรวจสอบได้ ให้เกิดประโยชน์สูงสุดต่อผู้เรียนเป็นสำคัญ
                    </p>
                    <p class="indent-8 text-justify text-slate-800 mb-8">
                        ขอขอบคุณคณะทำงานและผู้มีส่วนเกี่ยวข้องทุกท่านที่ได้ทุ่มเท เสียสละ และร่วมมือกันจัดทำแผนปฏิบัติการประจำปีงบประมาณ พ.ศ. ${year} จนสำเร็จลุล่วงด้วยดี และหวังเป็นอย่างยิ่งว่าผู้รับผิดชอบโครงการทุกท่านจะนำแผนฉบับนี้ไปปฏิบัติให้เกิดผลสัมฤทธิ์ต่อไป
                    </p>
                    <div class="text-right pr-8 mt-12 text-sm">
                        <p class="font-bold">(${school.director_name})</p>
                        <p class="text-slate-600 mt-1">ผู้อำนวยการ${school.name}</p>
                    </div>

                    <div class="mt-16 border-t-2 border-slate-200 pt-8">
                        <h3 class="text-xl font-bold text-center text-slate-900 mb-4">สารบัญ</h3>
                        <div class="space-y-2 text-sm font-medium text-slate-700">
                            <div class="flex justify-between border-b border-dashed border-slate-300 pb-1">
                                <span>บันทึกการให้ความเห็นชอบแผนปฏิบัติการประจำปี</span>
                                <span>ส่วนหน้า</span>
                            </div>
                            <div class="flex justify-between border-b border-dashed border-slate-300 pb-1">
                                <span>ส่วนที่ 1: ข้อมูลพื้นฐานและกรอบวงเงินงบประมาณประจำปี พ.ศ. ${year}</span>
                                <span>หน้า 1</span>
                            </div>
                            <div class="flex justify-between border-b border-dashed border-slate-300 pb-1">
                                <span>ส่วนที่ 2: สรุปบัญชีโครงการตามแผนปฏิบัติการประจำปี แยกตาม 4 กลุ่มงาน</span>
                                <span>หน้า 2</span>
                            </div>
                            <div class="flex justify-between border-b border-dashed border-slate-300 pb-1">
                                <span>ส่วนที่ 3: รายละเอียดแบบเสนอโครงการตามแผนปฏิบัติการทั้งหมด</span>
                                <span>หน้า 3 เป็นต้นไป</span>
                            </div>
                            <div class="flex justify-between border-b border-dashed border-slate-300 pb-1">
                                <span>ส่วนที่ 4: การติดตาม ตรวจสอบ และประเมินผลการดำเนินงาน</span>
                                <span>ภาคผนวก</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PAGE 3: SECTION 1 BUDGET SOURCES & ALLOCATION -->
                <div class="page text-[15px] leading-relaxed">
                    <div class="border-b-2 border-slate-800 pb-2 mb-6 flex justify-between items-center">
                        <span class="font-bold text-slate-800">แผนปฏิบัติการประจำปีงบประมาณ พ.ศ. ${year}</span>
                        <span class="text-sm font-semibold text-slate-600">${school.name}</span>
                    </div>

                    <h2 class="text-xl font-bold text-slate-900 mb-4">ส่วนที่ 1: แหล่งงบประมาณและการจัดสรร 100% ให้ 4 กลุ่มงาน</h2>

                    <h3 class="text-base font-bold text-slate-800 mb-2">1.1 แหล่งงบประมาณที่สถานศึกษาได้รับ (รวม ${formatBaht(summary.totalBudgetReceived)} บาท)</h3>
                    <table class="w-full border-collapse border border-slate-300 text-left text-sm mb-6">
                        <thead class="bg-slate-100 font-bold">
                            <tr>
                                <th class="border border-slate-300 px-3 py-2 text-center w-12">ที่</th>
                                <th class="border border-slate-300 px-3 py-2 w-28">รหัสแหล่งงบ</th>
                                <th class="border border-slate-300 px-3 py-2">ชื่อแหล่งงบประมาณ</th>
                                <th class="border border-slate-300 px-3 py-2 text-right w-36">จำนวนเงิน (บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${sources.map((s, idx) => `
                                <tr>
                                    <td class="border border-slate-300 px-3 py-2 text-center">${idx + 1}</td>
                                    <td class="border border-slate-300 px-3 py-2 font-mono text-xs">${s.code || '-'}</td>
                                    <td class="border border-slate-300 px-3 py-2">${s.name}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-right font-medium">${formatBaht(s.amount)}</td>
                                </tr>
                            `).join('')}
                            <tr class="bg-slate-50 font-bold">
                                <td colspan="3" class="border border-slate-300 px-3 py-2 text-right">รวมงบประมาณที่ได้รับทั้งสิ้น</td>
                                <td class="border border-slate-300 px-3 py-2 text-right text-blue-900">${formatBaht(summary.totalBudgetReceived)}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h3 class="text-base font-bold text-slate-800 mb-2">1.2 การจัดสรรงบประมาณเป็นเปอร์เซ็นต์ (รวม 100%) ให้ 4 กลุ่มงาน</h3>
                    <table class="w-full border-collapse border border-slate-300 text-left text-sm mb-6">
                        <thead class="bg-slate-100 font-bold">
                            <tr>
                                <th class="border border-slate-300 px-3 py-2 text-center w-12">ที่</th>
                                <th class="border border-slate-300 px-3 py-2">กลุ่มงาน / ภารกิจ</th>
                                <th class="border border-slate-300 px-3 py-2 text-center w-28">สัดส่วนร้อยละ</th>
                                <th class="border border-slate-300 px-3 py-2 text-right w-36">งบจัดสรร (บาท)</th>
                                <th class="border border-slate-300 px-3 py-2">หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${allocations.map((a, idx) => `
                                <tr>
                                    <td class="border border-slate-300 px-3 py-2 text-center">${idx + 1}</td>
                                    <td class="border border-slate-300 px-3 py-2 font-semibold">${a.department_name}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center font-bold text-blue-900">${a.percentage}%</td>
                                    <td class="border border-slate-300 px-3 py-2 text-right font-medium">${formatBaht(a.allocated_amount)}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-xs text-slate-500">${a.notes || '-'}</td>
                                </tr>
                            `).join('')}
                            <tr class="bg-slate-50 font-bold">
                                <td colspan="2" class="border border-slate-300 px-3 py-2 text-right">รวมสัดส่วนและการจัดสรรทั้งสิ้น</td>
                                <td class="border border-slate-300 px-3 py-2 text-center text-blue-900 font-extrabold">100.00%</td>
                                <td class="border border-slate-300 px-3 py-2 text-right text-blue-900 font-extrabold">${formatBaht(summary.totalAllocatedAmount || summary.totalBudgetReceived)}</td>
                                <td class="border border-slate-300 px-3 py-2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGE 4: SECTION 2 PROJECT SUMMARY TABLE -->
                <div class="page text-[15px] leading-relaxed">
                    <div class="border-b-2 border-slate-800 pb-2 mb-6 flex justify-between items-center">
                        <span class="font-bold text-slate-800">แผนปฏิบัติการประจำปีงบประมาณ พ.ศ. ${year}</span>
                        <span class="text-sm font-semibold text-slate-600">${school.name}</span>
                    </div>

                    <h2 class="text-xl font-bold text-slate-900 mb-3">ส่วนที่ 2: บัญชีสรุปโครงการตามแผนปฏิบัติการประจำปี</h2>
                    <p class="text-sm text-slate-600 mb-4">จำแนกตาม 4 กลุ่มงานและงบประมาณที่ได้รับอนุมัติในแผนปฏิบัติการ</p>

                    <table class="w-full border-collapse border border-slate-300 text-left text-sm mb-6">
                        <thead class="bg-slate-100 font-bold">
                            <tr>
                                <th class="border border-slate-300 px-3 py-2 text-center w-12">ที่</th>
                                <th class="border border-slate-300 px-3 py-2 w-24">รหัส</th>
                                <th class="border border-slate-300 px-3 py-2">ชื่อโครงการ</th>
                                <th class="border border-slate-300 px-3 py-2 w-32">กลุ่มงาน</th>
                                <th class="border border-slate-300 px-3 py-2 w-32">ผู้รับผิดชอบ</th>
                                <th class="border border-slate-300 px-3 py-2 text-right w-28">งบอนุมัติ (บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${projectSummaryRows}
                            <tr class="bg-slate-50 font-bold">
                                <td colspan="5" class="border border-slate-300 px-3 py-2 text-right">รวมงบประมาณโครงการทั้งหมด</td>
                                <td class="border border-slate-300 px-3 py-2 text-right text-blue-900 font-extrabold">${formatBaht(summary.totalApprovedBudget)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SECTION 3: ALL DETAILED PROJECT SHEETS -->
                ${detailedProjectsHtml}
            `;

            document.getElementById('bookContainer').innerHTML = html;
        }

        loadBook(1);
    </script>
</body>
</html>
