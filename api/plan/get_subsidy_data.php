<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

if (!($pdo instanceof PDO)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'], JSON_UNESCAPED_UNICODE);
    exit;
}

$fiscal_year_id = (int)($_GET['fiscal_year_id'] ?? 0);

if ($fiscal_year_id <= 0) {
    $stmtY = $pdo->query("SELECT id FROM fiscal_years ORDER BY is_current DESC, id DESC LIMIT 1");
    $rowY = $stmtY->fetch();
    if ($rowY) {
        $fiscal_year_id = (int)$rowY['id'];
    } else {
        $fyCols = getTableColumns($pdo, 'fiscal_years');
        $insCols = ["`start_date`", "`end_date`", "`is_current`", "`status`"];
        $insVals = ['2024-10-01', '2025-09-30', 1, 'active'];

        if (in_array('school_id', $fyCols)) { $insCols[] = "`school_id`"; $insVals[] = 1; }
        if (in_array('year', $fyCols)) { $insCols[] = "`year`"; $insVals[] = '2568'; }
        if (in_array('year_be', $fyCols)) { $insCols[] = "`year_be`"; $insVals[] = 2568; }
        if (in_array('fiscal_year', $fyCols)) { $insCols[] = "`fiscal_year`"; $insVals[] = 2568; }

        $placeholders = array_fill(0, count($insCols), '?');
        $sqlCreateY = "INSERT INTO `fiscal_years` (" . implode(', ', $insCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmtCreateY = $pdo->prepare($sqlCreateY);
        $stmtCreateY->execute($insVals);
        $fiscal_year_id = (int)$pdo->lastInsertId();
    }
}

$levelDefaults = [
    'kindergarten' => [
        'name' => 'ระดับก่อนประถมศึกษา (อนุบาล)',
        'count' => 0,
        'subsidy_rate' => 1800.00,
        'small_school_subsidy' => 0.00,
        'dev_rate' => 430.00
    ],
    'primary' => [
        'name' => 'ระดับประถมศึกษา (ป.1 - ป.6)',
        'count' => 0,
        'subsidy_rate' => 2000.00,
        'small_school_subsidy' => 0.00,
        'dev_rate' => 490.00
    ],
    'lower_secondary' => [
        'name' => 'ระดับมัธยมศึกษาตอนต้น (ม.1 - ม.3)',
        'count' => 0,
        'subsidy_rate' => 3600.00,
        'small_school_subsidy' => 0.00,
        'dev_rate' => 880.00
    ],
    'upper_secondary' => [
        'name' => 'ระดับมัธยมศึกษาตอนปลาย (ม.4 - ม.6)',
        'count' => 0,
        'subsidy_rate' => 3900.00,
        'small_school_subsidy' => 0.00,
        'dev_rate' => 950.00
    ]
];

try {
    $stmt = $pdo->prepare("SELECT * FROM student_subsidies WHERE fiscal_year_id = ?");
    $stmt->execute([$fiscal_year_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subsidies = [];
    if (!empty($rows)) {
        foreach ($rows as $r) {
            $key = $r['level_key'];
            $subsidies[$key] = [
                'id' => (int)$r['id'],
                'level_key' => $key,
                'level_name' => $r['level_name'],
                'student_count' => (int)$r['student_count'],
                'subsidy_rate' => (float)$r['subsidy_rate'],
                'small_school_subsidy' => (float)($r['small_school_subsidy'] ?? 0),
                'dev_rate' => (float)$r['dev_rate'],
                'total_subsidy_amount' => (float)$r['total_subsidy_amount'],
                'total_dev_amount' => (float)$r['total_dev_amount'],
                'total_amount' => (float)$r['total_amount']
            ];
        }
    }

    // Fill any missing level with defaults
    foreach ($levelDefaults as $key => $def) {
        if (!isset($subsidies[$key])) {
            $subsidies[$key] = [
                'id' => 0,
                'level_key' => $key,
                'level_name' => $def['name'],
                'student_count' => $def['count'],
                'subsidy_rate' => $def['subsidy_rate'],
                'small_school_subsidy' => $def['small_school_subsidy'],
                'dev_rate' => $def['dev_rate'],
                'total_subsidy_amount' => 0,
                'total_dev_amount' => 0,
                'total_amount' => 0
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'fiscal_year_id' => $fiscal_year_id,
        'subsidies' => $subsidies
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
