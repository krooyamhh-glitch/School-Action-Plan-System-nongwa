<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Ensure database connection config
require_once __DIR__ . '/../config.php';

if (!$pdo) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล MySQL ได้: ' . ($db_error ?: 'กรุณาตรวจสอบการตั้งค่า Host, Port, Database, User ในระบบ หรือเริ่มการทำงานของ MySQL Server')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$steps = [];

try {
    // 1. schools table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schools (
            id INT AUTO_INCREMENT PRIMARY KEY,
            smis_code VARCHAR(8) NOT NULL UNIQUE,
            code VARCHAR(8),
            name VARCHAR(255) NOT NULL,
            affiliation VARCHAR(255),
            province VARCHAR(100),
            district VARCHAR(100),
            subdistrict VARCHAR(100),
            address TEXT,
            postal_code VARCHAR(10),
            phone VARCHAR(50),
            email VARCHAR(100),
            website VARCHAR(255),
            director_name VARCHAR(150),
            director_position VARCHAR(150),
            plan_officer_name VARCHAR(150),
            assigned_admin_name VARCHAR(150),
            assigned_admin_id INT NULL,
            logo_url TEXT,
            status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "schools",
        "details" => "ตารางสถานศึกษา (รหัส SMIS 8 หลัก, ข้อมูลโรงเรียน, ตราสัญลักษณ์ และสถานะ)"
    ];

    // 2. users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            school_id INT NULL,
            smis_code VARCHAR(8),
            id_card VARCHAR(13) NULL UNIQUE,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(150) NOT NULL,
            last_name VARCHAR(150) NULL,
            position VARCHAR(100) NOT NULL,
            department ENUM('academic', 'budget', 'personnel', 'general', 'central') DEFAULT 'academic',
            role ENUM('super_admin', 'school_admin', 'director', 'deputy_director', 'plan_officer', 'department_head', 'teacher') DEFAULT 'teacher',
            phone VARCHAR(50),
            email VARCHAR(100),
            avatar_url TEXT,
            is_approved TINYINT(1) DEFAULT 1,
            must_change_password BOOLEAN DEFAULT TRUE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "users",
        "details" => "ตารางบุคลากร (สิทธิ์ 7 ระดับ, เลขบัตรประชาชน, รหัสผ่าน)"
    ];

    // 3. fiscal_years table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS fiscal_years (
            id INT AUTO_INCREMENT PRIMARY KEY,
            school_id INT NOT NULL,
            year INT NOT NULL,
            year_be INT NULL,
            start_date DATE NULL,
            end_date DATE NULL,
            status ENUM('active', 'planning', 'closed') DEFAULT 'active',
            is_current BOOLEAN DEFAULT FALSE,
            total_budget_base DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
            utility_reserve DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
            utility_reserve_notes VARCHAR(255) DEFAULT 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)',
            allocatable_budget DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "fiscal_years",
        "details" => "ตารางปีงบประมาณ พ.ศ. (พร้อมยอดงบรวม, งบกันค่าสาธารณูปโภค, งบสุทธิที่จัดสรร)"
    ];

    // 4. student_subsidies table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS student_subsidies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            school_id INT NOT NULL,
            fiscal_year_id INT NOT NULL,
            level_key VARCHAR(50) NOT NULL,
            level_name VARCHAR(100) NOT NULL,
            student_count INT DEFAULT 0,
            subsidy_rate DECIMAL(12, 2) DEFAULT 0.00,
            per_head_subsidy DECIMAL(12, 2) DEFAULT 0.00,
            small_school_subsidy DECIMAL(12, 2) DEFAULT 0.00,
            dev_rate DECIMAL(12, 2) DEFAULT 0.00,
            per_head_dev DECIMAL(12, 2) DEFAULT 0.00,
            total_subsidy_amount DECIMAL(14, 2) DEFAULT 0.00,
            total_dev_amount DECIMAL(14, 2) DEFAULT 0.00,
            total_amount DECIMAL(14, 2) DEFAULT 0.00,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_year_level (fiscal_year_id, level_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "student_subsidies",
        "details" => "ตารางคำนวณเงินอุดหนุนรายหัว (เพิ่มคอลัมน์เงินเพิ่มโรงเรียนขนาดเล็ก 500 บาท/คน) และเงิน กพพ."
    ];

    // 5. budget_sources table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS budget_sources (
            id INT AUTO_INCREMENT PRIMARY KEY,
            school_id INT NOT NULL,
            fiscal_year_id INT NOT NULL,
            name VARCHAR(150) NOT NULL,
            code VARCHAR(50),
            amount DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "budget_sources",
        "details" => "ตารางแหล่งเงินงบประมาณ (เงินอุดหนุน, เงินพัฒนาผู้เรียน, เงินรายได้สถานศึกษา)"
    ];

    // 6. department_allocations table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS department_allocations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            school_id INT NOT NULL,
            fiscal_year_id INT NOT NULL,
            department ENUM('academic', 'budget', 'personnel', 'general', 'central', 'reserve', 'other') NOT NULL,
            department_name VARCHAR(150) NULL,
            percentage DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
            allocated_amount DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
            notes VARCHAR(255) DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_year_dept (fiscal_year_id, department)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "department_allocations",
        "details" => "ตารางจัดสรรงบประมาณ 5 ช่อง (วิชาการ, บุคคล, งบประมาณ, ทั่วไป, กันไว้สำหรับค่าใช้จ่ายอื่นๆ)"
    ];

    // 7. projects table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            school_id INT NOT NULL,
            fiscal_year_id INT NOT NULL,
            budget_source_id INT NULL,
            department ENUM('academic', 'budget', 'personnel', 'general', 'central') NOT NULL,
            code VARCHAR(50),
            name VARCHAR(255) NOT NULL,
            leader_id INT NULL,
            leader_name VARCHAR(150) NULL,
            proposer_id INT NULL,
            strategic_issue VARCHAR(255),
            standard_ref VARCHAR(100),
            rationale TEXT,
            objectives TEXT,
            targets TEXT,
            target_qty TEXT,
            target_quality TEXT,
            indicators TEXT,
            expected_outcomes TEXT,
            duration_start DATE,
            duration_end DATE,
            requested_budget DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            proposed_budget DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            approved_budget DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            status ENUM('draft', 'submitted', 'screened', 'approved', 'rejected', 'in_progress', 'completed') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "projects",
        "details" => "ตารางโครงการตามแบบแผนปฏิบัติการ สพฐ. 15 หัวข้อ"
    ];

    // 8. budget_items table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS budget_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            category ENUM('remuneration', 'compensation', 'operations', 'operating', 'materials', 'equipment', 'utility', 'other') NOT NULL,
            item_name VARCHAR(255) NULL,
            description VARCHAR(255) NULL,
            unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            quantity INT NOT NULL DEFAULT 1,
            unit VARCHAR(50) DEFAULT 'รายการ',
            total_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "budget_items",
        "details" => "ตารางแจกแจงรายการค่าใช้จ่าย (ค่าตอบแทน, ค่าใช้สอย, ค่าวัสดุ, ค่าครุภัณฑ์)"
    ];

    // 9. project_expenses table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS project_expenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            expense_date DATE NOT NULL,
            description VARCHAR(255) NOT NULL,
            amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            category VARCHAR(50),
            doc_ref VARCHAR(100),
            recorder_name VARCHAR(150),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "project_expenses",
        "details" => "ตารางบันทึกการเบิกจ่ายจริงและคำนวณงบคงเหลือ"
    ];

    // 10. project_progress_logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS project_progress_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            project_id INT NOT NULL,
            log_date DATE NOT NULL,
            phase ENUM('P', 'D', 'C', 'A') DEFAULT 'D',
            progress_percentage INT DEFAULT 0,
            summary TEXT NOT NULL,
            problems TEXT,
            solutions TEXT,
            reporter_name VARCHAR(150),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $steps[] = [
        "table" => "project_progress_logs",
        "details" => "ตารางติดตามความก้าวหน้าโครงการตามวงจร PDCA"
    ];

    // Ensure Super Admin exists in users table
    $adminConfigFile = __DIR__ . '/../../admin-config.json';
    $superadminUser = 'superadmin';
    $superadminPass = 'password123';
    $superadminName = 'ผู้ดูแลระบบสูงสุด (Super Admin)';
    $superadminPos = 'ผู้ดูแลระบบสูงสุด';

    if (file_exists($adminConfigFile)) {
        $adm = json_decode(file_get_contents($adminConfigFile), true);
        if ($adm) {
            $superadminUser = $adm['username'] ?? $superadminUser;
            $superadminPass = $adm['password'] ?? $superadminPass;
            $superadminName = $adm['name'] ?? $superadminName;
            $superadminPos = $adm['position'] ?? $superadminPos;
        }
    }

    $saStmt = $pdo->prepare("
        INSERT INTO users (username, password, name, position, role, must_change_password, status)
        VALUES (?, ?, ?, ?, 'super_admin', 0, 'active')
        ON DUPLICATE KEY UPDATE name = VALUES(name), position = VALUES(position)
    ");
    $saStmt->execute([$superadminUser, $superadminPass, $superadminName, $superadminPos]);

    echo json_encode([
        "status" => "success",
        "live_mysql_executed" => true,
        "message" => "ติดตั้งและปรับปรุงโครงสร้างตารางฐานข้อมูลสำเร็จครบถ้วนทั้ง 10 ตาราง",
        "database_version" => "2026.1-PurePHP",
        "total_tables" => count($steps),
        "steps" => $steps,
        "superadmin" => [
            "username" => $superadminUser,
            "name" => $superadminName
        ],
        "timestamp" => date("Y-m-d H:i:s")
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "เกิดข้อผิดพลาดในการติดตั้งตารางฐานข้อมูล: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
