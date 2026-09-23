<?php
/**
 * Setup Telegram Webhook for a School
 */
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$school_id = $_SESSION['school_id'];

// Get bot token
$stmt = $pdo->prepare("SELECT telegram_bot_token FROM schools WHERE id = ?");
$stmt->execute([$school_id]);
$bot_token = $stmt->fetchColumn();

if (!$bot_token) {
    echo json_encode(['error' => 'กรุณาระบุ Telegram Bot Token ก่อน']);
    exit;
}

// Generate the webhook URL
// Assuming the app is running on a reachable public URL
// In this environment, we use the current host
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$webhook_url = "{$protocol}://{$host}/api/telegram/webhook.php?school_id={$school_id}";

// Telegram API call
$set_webhook_url = "https://api.telegram.org/bot{$bot_token}/setWebhook?url=" . urlencode($webhook_url);

try {
    $response = file_get_contents($set_webhook_url);
    $result = json_decode($response, true);
    
    if ($result['ok']) {
        echo json_encode([
            'success' => true, 
            'message' => 'เชื่อมต่อบอทสำเร็จ! ขณะนี้ผู้ปกครองสามารถใช้บอทในการลงทะเบียนอัตโนมัติได้แล้ว',
            'bot_info' => $result['description']
        ]);
    } else {
        echo json_encode(['error' => 'Telegram Error: ' . ($result['description'] ?? 'Unknown error')]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'ไม่สามารถเชื่อมต่อกับ Telegram ได้: ' . $e->getMessage()]);
}
