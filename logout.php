<?php
session_start();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'success', 'message' => 'ออกจากระบบเรียบร้อยแล้ว']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ออกจากระบบ...</title>
    <script>
        try {
            localStorage.clear();
            sessionStorage.clear();
        } catch (e) {}
        window.location.replace('index.php');
    </script>
</head>
<body style="font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f8fafc;">
    <div style="text-align: center;">
        <p style="color: #64748b; font-size: 14px;">กำลังออกจากระบบ...</p>
    </div>
</body>
</html>
