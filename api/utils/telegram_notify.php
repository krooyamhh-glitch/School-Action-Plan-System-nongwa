<?php

/**
 * ฟังก์ชันสำหรับการแจ้งเตือนผ่าน Telegram
 */
function sendTelegramNotification($bot_token, $chat_id, $message) {
    if (empty($bot_token) || empty($chat_id) || empty($message)) {
        return false;
    }

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result !== false;
}

/**
 * สร้างข้อความแจ้งเตือนการมาเรียน
 */
function createAttendanceMessage($student_name, $status, $subject_name, $teacher_name, $date) {
    $status_emoji = $status === 'present' ? '✅' : ($status === 'late' ? '⏰' : '❌');
    $status_text = $status === 'present' ? 'มาเรียน' : ($status === 'late' ? 'มาสาย' : ($status === 'sick' ? 'ลาป่วย' : ($status === 'business' ? 'ลากิจ' : 'ขาดเรียน')));
    
    $msg = "🔔 <b>แจ้งเตือนการเข้าเรียน</b>\n\n";
    $msg .= "👤 <b>นักเรียน:</b> {$student_name}\n";
    $msg .= "📅 <b>วันที่:</b> {$date}\n";
    $msg .= "📖 <b>วิชา:</b> {$subject_name}\n";
    $msg .= "👨‍🏫 <b>ครูผู้สอน:</b> {$teacher_name}\n";
    $msg .= "📊 <b>สถานะ:</b> {$status_emoji} {$status_text}\n\n";
    $msg .= "<i>ขอบคุณที่ให้ความร่วมมือครับ/ค่ะ</i>";
    
    return $msg;
}
