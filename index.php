<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารแผนปฏิบัติการประจำปี - School Action Plan Management System</title>
    <meta name="description" content="ระบบบริหารแผนปฏิบัติการประจำปีและงบประมาณสถานศึกษา รองรับการจัดสรร 5 ช่องงาน เงินเพิ่มโรงเรียนขนาดเล็ก และการกันงบสาธารณูปโภค">
    <meta property="og:title" content="ระบบบริหารแผนปฏิบัติการประจำปี">
    <meta property="og:description" content="ระบบบริหารแผนปฏิบัติการประจำปีและงบประมาณสถานศึกษา รองรับการจัดสรร 5 ช่องงาน เงินเพิ่มโรงเรียนขนาดเล็ก และการกันงบสาธารณูปโภค">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 min-h-screen flex items-center justify-center p-4 text-slate-800">

    <div class="w-full max-w-xl my-6">
        <!-- Header / Logo & System Title -->
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-3 border border-white/20 shadow-2xl p-2">
                <img id="loginLogoImg" src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Garuda_Emb_Thailand.svg/200px-Garuda_Emb_Thailand.svg.png" 
                     alt="ตราสัญลักษณ์โรงเรียน" class="w-14 h-auto drop-shadow-md object-contain">
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-500/20 text-blue-300 border border-blue-400/30 rounded-full text-xs font-semibold tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                ปีงบประมาณ พ.ศ. 2568
            </div>
            <!-- Row 1: Thai Name -->
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">ระบบบริหารแผนปฏิบัติการประจำปี</h1>
            <!-- Row 2: English Name -->
            <p class="text-blue-200 text-sm md:text-base font-medium mt-1 tracking-wide">
                School Action Plan Management System
            </p>
        </div>

        <!-- Main Card with Tab Switcher -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 transition-all">
            <!-- Tabs Header -->
            <div class="flex items-center p-1 bg-slate-100 rounded-2xl mb-6">
                <button type="button" id="tabBtnLogin" onclick="switchAuthTab('login')"
                        class="flex-1 py-2.5 px-4 rounded-xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2 bg-white text-blue-600 shadow-sm">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    เข้าสู่ระบบ (Sign In)
                </button>
                <button type="button" id="tabBtnRegister" onclick="switchAuthTab('register')"
                        class="flex-1 py-2.5 px-4 rounded-xl font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    ลงทะเบียนบุคลากร (Register)
                </button>
            </div>

            <!-- ============================================== -->
            <!-- TAB 1: LOGIN -->
            <!-- ============================================== -->
            <div id="tabContentLogin">
                <!-- Super Admin Initial Guide Note -->
                <div class="mb-5 p-3.5 bg-indigo-50/70 border border-indigo-100 rounded-2xl flex items-start gap-3">
                    <div class="p-2 bg-indigo-600 text-white rounded-xl shrink-0 mt-0.5 shadow-xs">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <div class="text-xs text-indigo-950 leading-relaxed">
                        <div class="font-bold flex items-center gap-1.5 text-indigo-900">
                            <span>บัญชีผู้ดูแลระบบสูงสุด (Super Admin)</span>
                        </div>
                        <p class="text-[11px] text-indigo-800 mt-0.5">
                            Username: <code class="px-1.5 py-0.5 bg-white rounded font-mono font-bold text-indigo-900 border border-indigo-200">superadmin</code> 
                            &nbsp;|&nbsp; รหัสผ่าน: <code class="px-1.5 py-0.5 bg-white rounded font-mono font-bold text-indigo-900 border border-indigo-200">password123</code>
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            * สำหรับจัดการฐานข้อมูล ติดตั้งระบบ และบริหารจัดการบัญชีผู้ใช้งาน
                        </p>
                    </div>
                </div>

                <!-- Real Login Form -->
                <form id="loginForm" class="space-y-4" onsubmit="handleFormLogin(event)">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">ชื่อผู้ใช้งาน หรือ หมายเลขประจำตัวประชาชน (13 หลัก)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <input type="text" id="username" required autocomplete="username"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none text-sm transition" 
                                   placeholder="ระบุชื่อผู้ใช้งาน หรือหมายเลขประจำตัวประชาชน 13 หลัก">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">รหัสผ่าน</label>
                            <span class="text-[11px] text-slate-400">รหัสผ่านเริ่มต้น 123 หรือตามที่ลงทะเบียน</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input type="password" id="password" required autocomplete="current-password"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none text-sm transition" 
                                   placeholder="กรอกรหัสผ่าน">
                        </div>
                    </div>

                    <div id="loginError" class="text-xs text-red-600 bg-red-50 p-3 rounded-xl border border-red-200 hidden"></div>

                    <button type="submit" id="loginBtn" 
                            class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white rounded-xl font-bold text-sm transition shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        เข้าสู่ระบบ
                    </button>
                </form>
            </div>

            <!-- ============================================== -->
            <!-- TAB 2: REGISTER -->
            <!-- ============================================== -->
            <div id="tabContentRegister" class="hidden">
                <div class="mb-5 p-3.5 bg-blue-50/70 border border-blue-100 rounded-2xl">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="info" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5"></i>
                        <div class="text-xs text-blue-900 leading-relaxed">
                            <span class="font-bold">คำแนะนำการลงทะเบียน:</span> 
                            ระบุรหัส SMIS 8 หลักของโรงเรียน และหมายเลขประจำตัวประชาชน 13 หลัก 
                            โดย <span class="font-bold">รหัสผ่านเริ่มต้นคือ 123</span> และสามารถเปลี่ยนรหัสผ่านได้เมื่อเข้าสู่ระบบ
                        </div>
                    </div>
                </div>

                <form id="registerForm" class="space-y-4" onsubmit="handleFormRegister(event)">
                    <!-- SMIS 8-Digit Code Input & Live Verify -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">
                                รหัส SMIS 8 หลักของสถานศึกษา <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400">ตรวจสอบในฐานข้อมูล</span>
                        </div>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="school" class="w-4 h-4"></i>
                                </div>
                                <input type="text" id="regSmisCode" maxlength="8" required 
                                       oninput="this.value = this.value.replace(/[^0-9]/g, ''); checkSmisRealtime();"
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none text-sm font-mono tracking-wider transition" 
                                       placeholder="กรอกรหัส SMIS 8 หลัก">
                            </div>
                            <button type="button" onclick="verifySmisCode(true)" 
                                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 border border-slate-200">
                                <i data-lucide="check-circle" class="w-4 h-4 text-blue-600"></i> ตรวจสอบ
                            </button>
                        </div>
                        <!-- SMIS Verification Status Box -->
                        <div id="smisFeedbackBox" class="mt-2 text-xs p-2.5 rounded-xl border flex items-center gap-2 bg-slate-50 text-slate-600 border-slate-200 hidden">
                        </div>
                    </div>

                    <!-- Citizen ID (13 Digits) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            หมายเลขประจำตัวประชาชน (13 หลัก) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="id-card" class="w-4 h-4"></i>
                            </div>
                            <input type="text" id="regIdCard" maxlength="13" required 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none text-sm font-mono tracking-widest transition" 
                                   placeholder="กรอกเลขบัตรประชาชน 13 หลัก">
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            ชื่อ - นามสกุล (พร้อมคำนำหน้า) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="user-check" class="w-4 h-4"></i>
                            </div>
                            <input type="text" id="regName" required 
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none text-sm transition" 
                                   placeholder="เช่น นายวัชระ พัฒนาการ, นางสาวสุจิตรา แก้วมณี">
                        </div>
                    </div>

                    <!-- Position Selection -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                ตำแหน่งในสถานศึกษา <span class="text-red-500">*</span>
                            </label>
                            <select id="regPosition" required 
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none text-xs sm:text-sm transition">
                                <optgroup label="สายงานบริหารสถานศึกษา">
                                    <option value="ผู้อำนวยการโรงเรียน">ผู้อำนวยการโรงเรียน</option>
                                    <option value="รองผู้อำนวยการโรงเรียน">รองผู้อำนวยการโรงเรียน</option>
                                </optgroup>
                                <optgroup label="สายงานการสอน / บุคลากรทางการศึกษา">
                                    <option value="ครูชำนาญการพิเศษ (คศ.3)">ครูชำนาญการพิเศษ (คศ.3)</option>
                                    <option value="ครูชำนาญการ (คศ.2)" selected>ครูชำนาญการ (คศ.2)</option>
                                    <option value="ครู (คศ.1)">ครู (คศ.1)</option>
                                    <option value="ครูผู้ช่วย">ครูผู้ช่วย</option>
                                    <option value="ครูอัตราจ้าง">ครูอัตราจ้าง</option>
                                    <option value="พนักงานราชการ">พนักงานราชการ</option>
                                    <option value="เจ้าหน้าที่แผนงานและงบประมาณ">เจ้าหน้าที่แผนงานและงบประมาณ</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Department Selection -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                กลุ่มงานที่สังกัด <span class="text-red-500">*</span>
                            </label>
                            <select id="regDepartment" required 
                                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none text-xs sm:text-sm transition">
                                <option value="academic">กลุ่มบริหารวิชาการ</option>
                                <option value="budget">กลุ่มบริหารงบประมาณและสินทรัพย์</option>
                                <option value="personnel">กลุ่มบริหารงานบุคคล</option>
                                <option value="general">กลุ่มบริหารทั่วไป</option>
                            </select>
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">เบอร์โทรศัพท์ (ถ้ามี)</label>
                            <input type="text" id="regPhone" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none text-sm transition" 
                                   placeholder="เช่น 081-234-5678">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">อีเมล (ถ้ามี)</label>
                            <input type="email" id="regEmail" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none text-sm transition" 
                                   placeholder="name@school.ac.th">
                        </div>
                    </div>

                    <!-- Password Info Note -->
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-slate-600">
                            <i data-lucide="key" class="w-4 h-4 text-amber-500"></i>
                            <span>รหัสผ่านเริ่มต้นของระบบ:</span>
                            <span class="font-mono font-bold text-slate-900 bg-amber-100 text-amber-900 px-2 py-0.5 rounded">123</span>
                        </div>
                        <span class="text-[11px] text-slate-500">เปลี่ยนรหัสเมื่อล็อกอินครั้งแรก</span>
                    </div>

                    <div id="registerError" class="text-xs text-red-600 bg-red-50 p-3 rounded-xl border border-red-200 hidden"></div>
                    <div id="registerSuccess" class="text-xs text-emerald-700 bg-emerald-50 p-3 rounded-xl border border-emerald-200 hidden"></div>

                    <button type="submit" id="registerBtn" 
                            class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl font-bold text-sm transition shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        ยืนยันการลงทะเบียนบุคลากร
                    </button>
                </form>
            </div>

            <!-- System Info -->
            <div class="mt-8 pt-6 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs text-slate-600">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    <span>ระบบฐานข้อมูลจริง (MySQL)</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    <span>คำนวณงบประมาณตามเกณฑ์</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    <span>จัดสรรงบ 4 กลุ่มงาน 100%</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    <span>ติดตามโครงการ & เบิกจ่ายจริง</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-400 mt-6">
            ระบบบริหารแผนปฏิบัติการประจำปีของโรงเรียน (School Action Plan Management System)
        </p>
    </div>

    <script>
        lucide.createIcons();

        let smisVerified = false;

        function switchAuthTab(tab) {
            const loginTab = document.getElementById('tabContentLogin');
            const registerTab = document.getElementById('tabContentRegister');
            const btnLogin = document.getElementById('tabBtnLogin');
            const btnRegister = document.getElementById('tabBtnRegister');

            if (tab === 'login') {
                loginTab.classList.remove('hidden');
                registerTab.classList.add('hidden');
                btnLogin.className = "flex-1 py-2.5 px-4 rounded-xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2 bg-white text-blue-600 shadow-sm";
                btnRegister.className = "flex-1 py-2.5 px-4 rounded-xl font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900";
            } else {
                loginTab.classList.add('hidden');
                registerTab.classList.remove('hidden');
                btnRegister.className = "flex-1 py-2.5 px-4 rounded-xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2 bg-white text-emerald-600 shadow-sm";
                btnLogin.className = "flex-1 py-2.5 px-4 rounded-xl font-semibold text-sm transition-all duration-200 flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900";
            }
            lucide.createIcons();
        }

        let debounceTimer;
        function checkSmisRealtime() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const code = document.getElementById('regSmisCode').value.trim();
                if (code.length === 8) {
                    verifySmisCode(false);
                } else {
                    const box = document.getElementById('smisFeedbackBox');
                    box.classList.remove('hidden');
                    box.className = "mt-2 text-xs p-2.5 rounded-xl border flex items-center gap-2 bg-amber-50 text-amber-800 border-amber-200";
                    box.innerHTML = `<i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 shrink-0"></i> <span>กรุณาระบุรหัส SMIS ให้ครบ 8 หลัก (ปัจจุบัน ${code.length}/8)</span>`;
                    smisVerified = false;
                    lucide.createIcons();
                }
            }, 300);
        }

        async function verifySmisCode(showAlert) {
            const code = document.getElementById('regSmisCode').value.trim();
            const box = document.getElementById('smisFeedbackBox');
            box.classList.remove('hidden');

            if (!code || code.length !== 8) {
                box.className = "mt-2 text-xs p-2.5 rounded-xl border flex items-center gap-2 bg-red-50 text-red-800 border-red-200";
                box.innerHTML = `<i data-lucide="x-circle" class="w-4 h-4 text-red-600 shrink-0"></i> <span>กรุณากรอกรหัส SMIS ให้ถูกต้องครบ 8 หลัก</span>`;
                smisVerified = false;
                lucide.createIcons();
                return;
            }

            try {
                const res = await fetch('/api/auth/verify_smis.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ smis_code: code })
                });
                const data = await res.json();

                if (data.status === 'success' && data.school) {
                    smisVerified = true;
                    box.className = "mt-2 text-xs p-2.5 rounded-xl border flex items-center gap-2 bg-emerald-50 text-emerald-800 border-emerald-200";
                    box.innerHTML = `
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                        <div>
                            <span class="font-bold text-emerald-950">${data.school.name}</span>
                            <span class="text-[11px] text-emerald-700 block">รหัส SMIS: ${data.school.smis_code}</span>
                        </div>
                    `;
                } else {
                    smisVerified = false;
                    box.className = "mt-2 text-xs p-2.5 rounded-xl border flex items-center gap-2 bg-red-50 text-red-800 border-red-200";
                    box.innerHTML = `<i data-lucide="x-circle" class="w-4 h-4 text-red-600 shrink-0"></i> <span>${data.message || 'ไม่พบรหัส SMIS นี้ในระบบ'}</span>`;
                }
            } catch (err) {
                console.error(err);
                box.className = "mt-2 text-xs p-2.5 rounded-xl border flex items-center gap-2 bg-amber-50 text-amber-800 border-amber-200";
                box.innerHTML = `<i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 shrink-0"></i> <span>ไม่สามารถเชื่อมต่อตรวจสอบรหัส SMIS ได้ในขณะนี้</span>`;
            }
            lucide.createIcons();
        }

        async function handleFormLogin(e) {
            e.preventDefault();
            const u = document.getElementById('username').value.trim();
            const p = document.getElementById('password').value.trim();
            await doLogin(u, p);
        }

        async function doLogin(username, password) {
            const errDiv = document.getElementById('loginError');
            const btn = document.getElementById('loginBtn');
            errDiv.classList.add('hidden');
            btn.disabled = true;
            btn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> กำลังตรวจสอบข้อมูล...';

            try {
                const res = await fetch('/api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });
                const data = await res.json();

                if (data.status === 'success' && data.user) {
                    localStorage.setItem('currentUser', JSON.stringify(data.user));
                    if (data.school) {
                        localStorage.setItem('schoolInfo', JSON.stringify(data.school));
                    }
                    window.location.href = 'dashboard.php';
                } else {
                    errDiv.innerText = data.message || 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                errDiv.innerText = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' + (err.message || 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
                errDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="log-in" class="w-4 h-4 mr-2"></i> เข้าสู่ระบบ';
                lucide.createIcons();
            }
        }

        async function handleFormRegister(e) {
            e.preventDefault();
            const errDiv = document.getElementById('registerError');
            const succDiv = document.getElementById('registerSuccess');
            const btn = document.getElementById('registerBtn');

            errDiv.classList.add('hidden');
            succDiv.classList.add('hidden');

            const smis_code = document.getElementById('regSmisCode').value.trim();
            const id_card = document.getElementById('regIdCard').value.trim();
            const name = document.getElementById('regName').value.trim();
            const position = document.getElementById('regPosition').value;
            const department = document.getElementById('regDepartment').value;
            const phone = document.getElementById('regPhone').value.trim();
            const email = document.getElementById('regEmail').value.trim();

            if (!smisVerified) {
                errDiv.innerText = 'กรุณาระบุรหัส SMIS 8 หลักของสถานศึกษาและตรวจสอบให้ถูกต้องก่อน';
                errDiv.classList.remove('hidden');
                return;
            }

            if (id_card.length !== 13) {
                errDiv.innerText = 'กรุณากรอกหมายเลขประจำตัวประชาชนให้ถูกต้องครบ 13 หลัก';
                errDiv.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> กำลังบันทึกข้อมูล...';

            try {
                const res = await fetch('/api/auth/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        smis_code,
                        id_card,
                        name,
                        position,
                        department,
                        phone,
                        email
                    })
                });
                const data = await res.json();

                if (data.status === 'success') {
                    succDiv.innerHTML = `
                        <div class="font-bold mb-1">🎉 ${data.message}</div>
                        <div>ท่านสามารถเข้าสู่ระบบด้วย <b>เลขบัตรประชาชน</b> และรหัสผ่านเริ่มต้น <b>123</b> ได้ทันที</div>
                    `;
                    succDiv.classList.remove('hidden');

                    document.getElementById('username').value = id_card;
                    document.getElementById('password').value = '123';

                    setTimeout(() => {
                        switchAuthTab('login');
                    }, 2000);
                } else {
                    errDiv.innerText = data.message || 'เกิดข้อผิดพลาดในการลงทะเบียน';
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                errDiv.innerText = 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล';
                errDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="user-plus" class="w-4 h-4 mr-2"></i> ยืนยันการลงทะเบียนบุคลากร';
                lucide.createIcons();
            }
        }

        // On load, fetch school branding if exists in database
        window.addEventListener('DOMContentLoaded', async () => {
            try {
                const res = await fetch('/api/admin/get_school_info.php');
                const data = await res.json();
                if (data && data.school && data.school.logo_url) {
                    document.getElementById('loginLogoImg').src = data.school.logo_url;
                }
            } catch (e) {}
        });
    </script>
</body>
</html>
