<?php
// Router script for PHP Built-in Server (php -S 0.0.0.0:3000 router.php)

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rawurldecode($uri);

// Default to index.php for root
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    return true;
}

$filePath = __DIR__ . $uri;

// If it is a static file (not PHP), let the built-in server handle it directly
if (is_file($filePath)) {
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    if ($ext !== 'php') {
        return false;
    }
    // If it's a php file directly requested, include it
    require $filePath;
    return true;
}

// If requested without .php extension (e.g. /dashboard or /api/login)
if (is_file($filePath . '.php')) {
    require $filePath . '.php';
    return true;
}

// If directory exists and has index.php
if (is_dir($filePath) && is_file($filePath . '/index.php')) {
    require $filePath . '/index.php';
    return true;
}

// 404 Not Found
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo "<h1>404 ไม่พบหน้าที่ต้องการ</h1><p>ไม่พบไฟล์: " . htmlspecialchars($uri) . "</p><p><a href='/'>กลับสู่หน้าหลัก</a></p>";
return true;
