<?php
session_start();
if (isset($_SESSION['parent_logged_in'])) {
    header('Location: parent_dashboard.php');
    exit;
}
require_once 'api/config.php';

$app_name = 'ระบบติดตามนักเรียนสำหรับผู้ปกครอง';
$app_logo = 'https://picsum.photos/seed/school/192/192';

try {
    $stmt_app = $pdo->query("SELECT setting_key, setting_value FROM app_settings");
    $settings = $stmt_app->fetchAll(PDO::FETCH_KEY_PAIR);
    if (isset($settings['app_name'])) $app_name = $settings['app_name'];
    if (isset($settings['app_logo'])) $app_logo = $settings['app_logo'];
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>เข้าสู่ระบบ - <?= $app_name ?></title>
    <link rel="manifest" href="manifest.php">
    <meta name="theme-color" content="#f59e0b">
    <link rel="apple-touch-icon" href="<?= $app_logo ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { 
            font-family: 'Sarabun', sans-serif;
            background-color: #fffbeb;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%23f59e0b' fill-opacity='0.1'%3E%3Cpath d='M0 0h40v40H0V0zm40 40h40v40H40V40zm0-40h40v40H40V0zM0 40h40v40H0V40z'/%3E%3C/g%3E%3C/svg%3E");
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 50px rgba(245, 158, 11, 0.15);
            border: 1px solid #fef3c7;
        }
        .input-focus:focus-within {
            border-color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 relative overflow-x-hidden">
    <!-- Decorative Accents -->
    <div class="absolute -top-24 -left-24 w-80 h-80 bg-orange-400/20 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-amber-300/20 rounded-full blur-3xl animate-pulse"></div>
    
    <div class="w-full max-w-sm space-y-8 relative z-10">
        <div class="text-center space-y-3">
            <div class="w-24 h-24 bg-gradient-to-br from-orange-400 to-amber-500 rounded-[2rem] mx-auto flex items-center justify-center shadow-xl shadow-orange-500/30 mb-6 transform transition-transform hover:scale-110">
                <i data-lucide="user" class="w-12 h-12 text-white"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">ระบบติดตามนักเรียน</h1>
            <p class="text-amber-700 font-bold bg-amber-100/80 inline-block px-4 py-1 rounded-full text-xs">สำหรับผู้ปกครองและนักเรียนเข้าดูข้อมูล</p>
        </div>

        <div class="login-card p-10 rounded-[2.5rem] space-y-8">
            <form id="loginForm" class="space-y-6">
                <div class="space-y-2.5">
                    <label class="block text-sm font-black text-slate-700 ml-1">เลขบัตรประชาชนนักเรียน</label>
                    <div class="relative group input-focus border border-slate-200 rounded-2xl bg-slate-50 transition-all overflow-hidden">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-orange-500 transition-colors">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                        </div>
                        <input type="text" id="national_id" inputmode="numeric" placeholder="ตัวเลข 13 หลัก" required 
                            class="w-full pl-12 pr-4 py-4 bg-transparent outline-none text-slate-700 font-bold tracking-widest text-lg placeholder:text-slate-300 placeholder:font-normal">
                    </div>
                </div>

                <div class="space-y-2.5">
                    <label class="block text-sm font-black text-slate-700 ml-1">รหัสนักเรียน</label>
                    <div class="relative group input-focus border border-slate-200 rounded-2xl bg-slate-50 transition-all overflow-hidden">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-orange-500 transition-colors">
                            <i data-lucide="key-round" class="w-5 h-5"></i>
                        </div>
                        <input type="text" id="student_code" inputmode="numeric" placeholder="รหัสประจำตัวนักเรียน" required
                            class="w-full pl-12 pr-4 py-4 bg-transparent outline-none text-slate-700 font-bold tracking-widest text-lg placeholder:text-slate-300 placeholder:font-normal">
                    </div>
                </div>

                <button type="submit" id="loginBtn" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 text-white py-4 rounded-2xl font-black text-xl hover:from-orange-600 hover:to-amber-600 shadow-xl shadow-orange-500/30 transition-all active:scale-95 flex items-center justify-center gap-3">
                    เข้าสู่ระบบ
                    <i data-lucide="arrow-right" class="w-6 h-6"></i>
                </button>
            </form>
            
            <div id="errorMsg" class="hidden p-4 bg-red-50 text-red-600 rounded-2xl text-center text-sm font-bold border border-red-100"></div>
        </div>

        <div class="text-center">
            <p class="text-xs text-amber-800 font-bold opacity-40 mb-8 tracking-widest">โดย ครูสยาม เชียงเครือ</p>
            <button id="installBtn" class="hidden mx-auto flex items-center gap-2 px-8 py-3 bg-white border border-amber-200 rounded-full text-orange-600 font-black text-xs shadow-sm hover:bg-orange-50 transition-all cursor-pointer">
                <i data-lucide="download" class="w-4 h-4"></i>
                ติดตั้งแอปไว้บนมือถือ
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const form = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const errorMsg = document.getElementById('errorMsg');

        // ตรวจสอบข้อมูลที่บันทึกไว้ (Auto-fill)
        window.onload = () => {
             const savedId = localStorage.getItem('parent_national_id');
             const savedCode = localStorage.getItem('parent_student_code');
             if (savedId) document.getElementById('national_id').value = savedId;
             if (savedCode) document.getElementById('student_code').value = savedCode;
        };

        form.onsubmit = async (e) => {
            e.preventDefault();
            const nid = document.getElementById('national_id').value;
            const scode = document.getElementById('student_code').value;

            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> กำลังตรวจสอบ...';
            lucide.createIcons();
            errorMsg.classList.add('hidden');

            try {
                const res = await fetch('api/parent/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ national_id: nid, student_code: scode })
                });
                const result = await res.json();

                if (result.success) {
                    // บันทึกข้อมูลไว้ใช้ครั้งหน้า
                    localStorage.setItem('parent_national_id', nid);
                    localStorage.setItem('parent_student_code', scode);
                    window.location.href = 'parent_dashboard.php';
                } else {
                    errorMsg.innerText = result.message;
                    errorMsg.classList.remove('hidden');
                }
            } catch (err) {
                errorMsg.innerText = 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่';
                errorMsg.classList.remove('hidden');
            } finally {
                loginBtn.disabled = false;
                loginBtn.innerHTML = 'เข้าสู่ระบบ <i data-lucide="arrow-right" class="w-5 h-5"></i>';
                lucide.createIcons();
            }
        };

        // Service Worker registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(reg => console.log('Service Worker registered'))
                    .catch(err => console.log('Service Worker failed', err));
            });
        }

        // ระบบติดตั้ง PWA
        let deferredPrompt;
        const installBtn = document.getElementById('installBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installBtn.classList.remove('hidden');
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    installBtn.classList.add('hidden');
                }
                deferredPrompt = null;
            }
        });
    </script>
</body>
</html>
