<?php
session_start();
header('Content-Type: application/manifest+json');
require_once 'api/config.php';

// ค่าพื้นฐาน (Fallback)
$app_name = "ระบบติดตามนักเรียน";
$app_logo = "https://picsum.photos/seed/school/192/192";
$app_logo_512 = "https://picsum.photos/seed/school/512/512";

// 1. ตรวจสอบว่ามีการเข้าสู่ระบบในระดับโรงเรียนหรือไม่ (สำหรับผู้ปกครอง/นักเรียน)
if (isset($_SESSION['school_id'])) {
    try {
        $stmt_school = $pdo->prepare("SELECT name, logo_url FROM schools WHERE id = ?");
        $stmt_school->execute([$_SESSION['school_id']]);
        $school = $stmt_school->fetch();
        
        if ($school) {
            $app_name = $school['name'];
            if (!empty($school['logo_url'])) {
                $app_logo = $school['logo_url'];
                $app_logo_512 = $school['logo_url'];
            }
        }
    } catch (Exception $e) {}
} else {
    // 2. ถ้าไม่มี Session โรงเรียน ให้ใช้ค่าตั้งค่าส่วนกลางจาก Super Admin
    try {
        $stmt_app = $pdo->query("SELECT setting_key, setting_value FROM app_settings");
        $settings = $stmt_app->fetchAll(PDO::FETCH_KEY_PAIR);
        
        if (isset($settings['app_name'])) {
            $app_name = $settings['app_name'];
        }
        
        if (isset($settings['app_logo'])) {
            $app_logo = $settings['app_logo'];
            $app_logo_512 = $settings['app_logo'];
        }
    } catch (Exception $e) {}
}

$manifest = [
    "name" => $app_name,
    "short_name" => $app_name,
    "description" => "ระบบบริหารงานวิชาการและติดตามนักเรียน " . $app_name,
    "start_url" => "index.php",
    "display" => "standalone",
    "background_color" => "#ffffff",
    "theme_color" => "#2563eb",
    "icons" => [
        [
            "src" => $app_logo,
            "sizes" => "192x192",
            "type" => "image/png"
        ],
        [
            "src" => $app_logo_512,
            "sizes" => "512x512",
            "type" => "image/png"
        ]
    ]
];

echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
