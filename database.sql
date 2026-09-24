-- =======================================================
-- ระบบบริหารแผนปฏิบัติการประจำปีของโรงเรียน (School Action Plan OS)
-- รองรับการบริหารงบประมาณและโครงการของสถานศึกษา
-- =======================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. ตารางข้อมูลโรงเรียน (รองรับรหัส SMIS 8 หลัก และการเปิดใช้งานโดย Super Admin)
CREATE TABLE IF NOT EXISTS `schools` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(10) UNIQUE NOT NULL, -- รหัสโรงเรียน
  `smis_code` VARCHAR(20) DEFAULT '10310001', -- รหัส SMIS 8 หลัก เช่น 10310001
  `name` VARCHAR(255) NOT NULL,
  `province` VARCHAR(100) NOT NULL,
  `district` VARCHAR(100) DEFAULT '',
  `subdistrict` VARCHAR(100) DEFAULT '',
  `address` TEXT,
  `postal_code` VARCHAR(10) DEFAULT '',
  `phone` VARCHAR(30) DEFAULT '',
  `email` VARCHAR(100) DEFAULT '',
  `website` VARCHAR(255) DEFAULT '',
  `affiliation` VARCHAR(255) NOT NULL DEFAULT 'สังกัดสถานศึกษา',
  `director_name` VARCHAR(255) DEFAULT 'นายธีระพล เกียรติวิทยา',
  `director_position` VARCHAR(255) DEFAULT 'ผู้อำนวยการโรงเรียน',
  `plan_officer_name` VARCHAR(255) DEFAULT 'นางวิไลพร งบมั่นคง',
  `assigned_admin_name` VARCHAR(255) DEFAULT 'ผู้ดูแลระบบโรงเรียน',
  `assigned_admin_id` INT DEFAULT NULL,
  `logo_url` TEXT,
  `status` ENUM('active', 'pending', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ตารางผู้ใช้งานและกำหนดสิทธิ์
-- super_admin: ผู้ดูแลระบบสูงสุด (Super Admin - เปิดใช้งานโรงเรียนด้วยรหัส SMIS 8 หลัก)
-- school_admin: ผู้ดูแลระบบของโรงเรียน (ตั้งค่าข้อมูลโรงเรียนและโลโก้)
-- director: ผู้อำนวยการโรงเรียน
-- deputy_director: รองผู้อำนวยการโรงเรียน
-- plan_officer: เจ้าหน้าที่แผนงานและงบประมาณ (กำหนดจำนวนนักเรียน อัตราอุดหนุน คำนวณตัดงบ)
-- department_head: หัวหน้ากลุ่มงาน 4 กลุ่ม
-- teacher: ครู/บุคลากรผู้รับผิดชอบโครงการ
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `school_id` INT DEFAULT 1,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `id_card` VARCHAR(13) DEFAULT '', -- เลขประจำตัวประชาชน 13 หลัก
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(100) DEFAULT 'ครูชำนาญการ',
  `department` ENUM('academic', 'budget', 'personnel', 'general', 'central') DEFAULT 'academic',
  `role` ENUM('super_admin', 'school_admin', 'director', 'deputy_director', 'plan_officer', 'department_head', 'teacher') DEFAULT 'teacher',
  `phone` VARCHAR(30) DEFAULT '',
  `email` VARCHAR(100) DEFAULT '',
  `must_change_password` TINYINT(1) DEFAULT 1, -- ต้องเปลี่ยนรหัสผ่านเมื่อเข้าใช้งานครั้งแรก (จาก 123456)
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.1 ตารางกำหนดจำนวนนักเรียนและอัตราเงินอุดหนุนรายหัว/กพพ. แต่ละช่วงชั้น
CREATE TABLE IF NOT EXISTS `student_subsidies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `school_id` INT NOT NULL DEFAULT 1,
  `fiscal_year_id` INT NOT NULL DEFAULT 1,
  `level_key` ENUM('kindergarten', 'primary', 'lower_secondary', 'upper_secondary') NOT NULL,
  `level_name` VARCHAR(100) NOT NULL, -- เช่น ก่อนประถมศึกษา (อนุบาล), ประถมศึกษา, มัธยมศึกษาตอนต้น, มัธยมศึกษาตอนปลาย
  `student_count` INT NOT NULL DEFAULT 0,
  `subsidy_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- อัตราเงินอุดหนุนรายหัวปกติ (บาท/คน/ปี)
  `small_school_subsidy` DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- เงินเพิ่มโรงเรียนขนาดเล็ก (เด็ก < 120 คน เพิ่ม 500 บ./คน หรือป้อนตามจริง)
  `dev_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- อัตราเงินกิจกรรมพัฒนาคุณภาพผู้เรียน กพพ. (บาท/คน/ปี)
  `total_subsidy_amount` DECIMAL(14,2) DEFAULT 0.00,
  `total_dev_amount` DECIMAL(14,2) DEFAULT 0.00,
  `total_amount` DECIMAL(14,2) DEFAULT 0.00,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ตารางปีงบประมาณ
CREATE TABLE IF NOT EXISTS `fiscal_years` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `school_id` INT DEFAULT 1,
  `year` VARCHAR(4) NOT NULL, -- เช่น 2568
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `is_current` TINYINT(1) DEFAULT 0,
  `status` ENUM('planning', 'active', 'closed') DEFAULT 'active', -- planning=เปิดรับคำขอ, active=กำลังดำเนินงานตามแผน, closed=ปิดงบสิ้นปี
  `total_budget_base` DECIMAL(14,2) NOT NULL DEFAULT 0.00, -- งบประมาณรวมที่ตั้งไว้จัดสรร (จากคำนวณรายหัวหรือกำหนดยอดเอง)
  `utility_reserve` DECIMAL(14,2) NOT NULL DEFAULT 0.00, -- งบประมาณกันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ-ค่าไฟ)
  `utility_reserve_notes` VARCHAR(255) DEFAULT 'กันไว้สำหรับค่าสาธารณูปโภค (ค่าน้ำ ค่าไฟ)',
  `allocatable_budget` DECIMAL(14,2) NOT NULL DEFAULT 0.00, -- ยอดงบสุทธิที่นำมาจัดสรร 5 ช่อง 100%
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ตารางบันทึกงบประมาณที่โรงเรียนได้รับ แยกตามแหล่งงบประมาณ
CREATE TABLE IF NOT EXISTS `budget_sources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fiscal_year_id` INT NOT NULL,
  `code` VARCHAR(50) DEFAULT '',
  `name` VARCHAR(255) NOT NULL, -- เช่น เงินอุดหนุนรายหัว, เงินกิจกรรมพัฒนาผู้เรียน, เงินรายได้สถานศึกษา
  `category` ENUM('subsidy', 'student_dev', 'school_income', 'donation', 'poverty_fund', 'other') DEFAULT 'subsidy',
  `amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `description` TEXT,
  `received_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ตารางการจัดสรรงบประมาณเป็นเปอร์เซ็นต์ รวม 100% ให้ 4 กลุ่มงาน + งบสำรอง/ส่วนกลาง
CREATE TABLE IF NOT EXISTS `department_allocations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fiscal_year_id` INT NOT NULL,
  `department` ENUM('academic', 'budget', 'personnel', 'general', 'reserve') NOT NULL,
  `department_name` VARCHAR(150) NOT NULL,
  `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00, -- เช่น 40.00%
  `allocated_amount` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `notes` VARCHAR(255) DEFAULT '',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ตารางโครงการตามแผนปฏิบัติการประจำปี
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fiscal_year_id` INT NOT NULL,
  `department` ENUM('academic', 'budget', 'personnel', 'general', 'reserve') NOT NULL DEFAULT 'academic',
  `code` VARCHAR(50) DEFAULT '', -- รหัสโครงการ เช่น อบ-01, บก-02
  `name` VARCHAR(255) NOT NULL,
  `proposer_id` INT NOT NULL, -- ผู้เสนอโครงการ
  `supervisor_id` INT DEFAULT NULL, -- หัวหน้ากลุ่มงานที่เห็นชอบ
  `strategy_alignment` TEXT, -- สอดคล้องยุทธศาสตร์ชาติ/สพฐ./โรงเรียน
  `standard_alignment` VARCHAR(255) DEFAULT 'มาตรฐานที่ 1 คุณภาพของผู้เรียน',
  `rationale` TEXT, -- หลักการและเหตุผล
  `objectives` TEXT, -- วัตถุประสงค์ (1, 2, 3...)
  `target_qty` TEXT, -- เป้าหมายเชิงปริมาณ
  `target_quality` TEXT, -- เป้าหมายเชิงคุณภาพ
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT 'โรงเรียน',
  `budget_source_id` INT DEFAULT NULL, -- แหล่งงบประมาณที่ขอใช้
  `requested_budget` DECIMAL(14,2) NOT NULL DEFAULT 0.00, -- งบประมาณที่ขอ
  `approved_budget` DECIMAL(14,2) NOT NULL DEFAULT 0.00, -- งบประมาณที่อนุมัติหลังกลั่นกรอง
  `expected_outcomes` TEXT, -- ผลที่คาดว่าจะได้รับ
  `indicators` TEXT, -- ตัวชี้วัดความสำเร็จ (KPI)
  `evaluation_method` TEXT, -- วิธีการและเครื่องมือประเมินผล
  
  -- สถานะการพิจารณาและการอนุมัติ
  -- draft: ร่าง
  -- submitted: เสนอโครงการแล้ว (รอหัวหน้ากลุ่มงาน)
  -- dept_approved: หัวหน้ากลุ่มงานเห็นชอบแล้ว (รอกลั่นกรองงบ)
  -- screened: เจ้าหน้าที่แผนกลั่นกรองและปรับงบแล้ว (รอ ผอ. อนุมัติ)
  -- approved: ผอ. อนุมัติและบรรจุในเล่มแผนปฏิบัติการ
  -- revision_requested: ส่งกลับแก้ไข
  -- rejected: ตัดแผน / ไม่อนุมัติ
  `status` ENUM('draft', 'submitted', 'dept_approved', 'screened', 'approved', 'revision_requested', 'rejected') DEFAULT 'draft',
  `screening_note` TEXT, -- บันทึกข้อคิดเห็นการกลั่นกรอง/เหตุผลตัดลดงบ
  `director_note` TEXT, -- ข้อสั่งการ/บันทึกความเห็นของ ผอ.
  `approved_at` DATETIME DEFAULT NULL,
  
  -- การติดตามความก้าวหน้า
  `progress_percentage` INT DEFAULT 0, -- 0 - 100%
  `execution_status` ENUM('not_started', 'in_progress', 'completed', 'delayed', 'cancelled') DEFAULT 'not_started',
  `results_summary` TEXT, -- สรุปผลการดำเนินงานจริง
  `obstacles` TEXT, -- ปัญหาและอุปสรรค
  `recommendations` TEXT, -- ข้อเสนอแนะ
  
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`proposer_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`budget_source_id`) REFERENCES `budget_sources`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ตารางขั้นตอนและกิจกรรมสำคัญของโครงการ
CREATE TABLE IF NOT EXISTS `project_activities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `step_number` INT NOT NULL DEFAULT 1,
  `activity_name` VARCHAR(255) NOT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `responsible_person` VARCHAR(255) DEFAULT '',
  `budget_amount` DECIMAL(12,2) DEFAULT 0.00,
  `is_done` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. ตารางรายละเอียดประมาณการค่าใช้จ่าย (แจกแจงตามหมวดงบประมาณ)
-- compensation: ค่าตอบแทน
-- operating: ค่าใช้สอย
-- materials: ค่าวัสดุ
-- utility: ค่าสาธารณูปโภค
-- other: ค่าใช้จ่ายอื่น
CREATE TABLE IF NOT EXISTS `project_budget_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `category` ENUM('compensation', 'operating', 'materials', 'utility', 'other') DEFAULT 'materials',
  `item_name` VARCHAR(255) NOT NULL,
  `quantity` DECIMAL(10,2) DEFAULT 1.00,
  `unit` VARCHAR(50) DEFAULT 'รายการ',
  `unit_price` DECIMAL(12,2) DEFAULT 0.00,
  `total_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ตารางบันทึกรายการค่าใช้จ่ายจริง (เบิกจ่ายจริง)
CREATE TABLE IF NOT EXISTS `project_expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `expense_date` DATE NOT NULL,
  `doc_number` VARCHAR(100) NOT NULL, -- เลขที่ใบสำคัญ/บันทึกข้อความขอเบิก
  `title` VARCHAR(255) NOT NULL, -- รายการเบิกจ่าย
  `category` ENUM('compensation', 'operating', 'materials', 'utility', 'other') DEFAULT 'materials',
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `disbursed_by` VARCHAR(255) NOT NULL, -- ผู้เบิกเงิน
  `receipt_note` VARCHAR(255) DEFAULT '',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. ตารางบันทึกรายงานผลความก้าวหน้า
CREATE TABLE IF NOT EXISTS `project_progress_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `log_date` DATE NOT NULL,
  `progress_percent` INT NOT NULL DEFAULT 0,
  `details` TEXT NOT NULL,
  `obstacles` TEXT,
  `solutions` TEXT,
  `recorded_by` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. ตารางการตั้งค่าระบบ
CREATE TABLE IF NOT EXISTS `app_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- ข้อมูลเริ่มต้น (Seed Data สำหรับโรงเรียนมาตรฐาน)
-- =======================================================

INSERT INTO `schools` (`id`, `code`, `smis_code`, `name`, `province`, `affiliation`, `director_name`, `director_position`, `plan_officer_name`, `logo_url`) VALUES
(1, '10310001', '10310001', 'โรงเรียนอนุบาลพัฒนาวิทยา', 'กรุงเทพมหานคร', 'สังกัดสถานศึกษา', 'นายธีระพล เกียรติวิทยา', 'ผู้อำนวยการโรงเรียนอนุบาลพัฒนาวิทยา', 'นางวิไลพร งบมั่นคง', '');

-- ตั้งค่าระบบ
INSERT INTO `app_settings` (`setting_key`, `setting_value`) VALUES
('app_name', 'ระบบบริหารแผนปฏิบัติการประจำปีของโรงเรียน'),
('school_name', 'โรงเรียนอนุบาลพัฒนาวิทยา'),
('affiliation', 'สังกัดสถานศึกษา'),
('director_name', 'นายธีระพล เกียรติวิทยา'),
('plan_officer_name', 'นางวิไลพร งบมั่นคง')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- บัญชีผู้ใช้งานระบบตัวอย่าง (ครอบคลุม Super Admin และทุกตำแหน่ง)
-- รหัสผ่านเริ่มต้น Super Admin: password123 (หรือ 123456)
INSERT INTO `users` (`id`, `school_id`, `username`, `id_card`, `password`, `name`, `position`, `department`, `role`, `phone`, `email`, `must_change_password`, `is_approved`) VALUES
(1, NULL, 'superadmin', '1310000000001', 'password123', 'ผู้ดูแลระบบสูงสุด (Super Admin)', 'ผู้ดูแลระบบสูงสุด', 'central', 'super_admin', '0812345678', 'superadmin@school.ac.th', 0, 1),
(2, 1, 'schooladmin', '1310000000002', '123456', 'นางสาวสุภาวดี ดูแลระบบ', 'ผู้ดูแลระบบสารสนเทศโรงเรียน', 'budget', 'school_admin', '0823456789', 'admin@anubanpat.ac.th', 0, 1),
(3, 1, 'director', '1310000000003', '123456', 'นายธีระพล เกียรติวิทยา', 'ผู้อำนวยการโรงเรียนอนุบาลพัฒนาวิทยา', 'central', 'director', '0891234567', 'director@anubanpat.ac.th', 0, 1),
(4, 1, 'planofficer', '1310000000005', '123456', 'นางวิไลพร งบมั่นคง', 'เจ้าหน้าที่แผนงานและงบประมาณ', 'budget', 'plan_officer', '0867891234', 'plan@anubanpat.ac.th', 0, 1),
(5, 1, 'head_academic', '1310000000006', '123456', 'นางกัญญา วิชาการดี', 'หัวหน้ากลุ่มบริหารวิชาการ', 'academic', 'department_head', '0856781234', 'academic@anubanpat.ac.th', 0, 1),
(6, 1, 'head_budget', '1310000000007', '123456', 'นายสุรชัย บัญชีทรัพย์', 'หัวหน้ากลุ่มบริหารงบประมาณ', 'budget', 'department_head', '0845671234', 'budget@anubanpat.ac.th', 0, 1),
(7, 1, 'head_personnel', '1310000000008', '123456', 'นางสาวพิมพ์ใจ เสริมบุคคล', 'หัวหน้ากลุ่มบริหารงานบุคคล', 'personnel', 'department_head', '0834561234', 'personnel@anubanpat.ac.th', 0, 1),
(8, 1, 'head_general', '1310000000009', '123456', 'นายพิชิต สภาพแวดล้อม', 'หัวหน้ากลุ่มบริหารทั่วไป', 'general', 'department_head', '0823451234', 'general@anubanpat.ac.th', 0, 1),
(9, 1, 'teacher_somchai', '1310000000010', '123456', 'นายสมชาย สอนสนุก', 'ครูชำนาญการ (วิชาการ)', 'academic', 'teacher', '0811112222', 'somchai@anubanpat.ac.th', 0, 1),
(10, 1, 'teacher_somying', '1310000000011', '123456', 'นางสมหญิง กิจกรรมเลิศ', 'ครูผู้ช่วย (ทั่วไป)', 'general', 'teacher', '0822223333', 'somying@anubanpat.ac.th', 0, 1);

-- ปีงบประมาณ 2568 (ปีปัจจุบัน) และ 2567 (ปีก่อนหน้าสำหรับดูย้อนหลัง)
INSERT INTO `fiscal_years` (`id`, `school_id`, `year`, `start_date`, `end_date`, `is_current`, `status`, `notes`) VALUES
(1, 1, '2568', '2024-10-01', '2025-09-30', 1, 'active', 'แผนปฏิบัติการประจำปีงบประมาณ พ.ศ. 2568 ขับเคลื่อนสู่ความเป็นเลิศ'),
(2, 1, '2567', '2023-10-01', '2024-09-30', 0, 'closed', 'แผนปฏิบัติการประจำปีงบประมาณ พ.ศ. 2567 (ปิดยอดงบสมบูรณ์แล้ว)');

-- แหล่งงบประมาณปี 2568 (ยอดรวม 1,850,000 บาท)
INSERT INTO `budget_sources` (`id`, `fiscal_year_id`, `code`, `name`, `category`, `amount`, `description`, `received_date`) VALUES
(1, 1, 'SRC-68-01', 'เงินอุดหนุนรายหัวการจัดการศึกษาขั้นพื้นฐาน', 'subsidy', 1150000.00, 'จัดสรรตามจำนวนนักเรียนอนุบาลถึงประถมศึกษาปีที่ 6', '2024-10-15'),
(2, 1, 'SRC-68-02', 'เงินกิจกรรมพัฒนาคุณภาพผู้เรียน (กพพ.)', 'student_dev', 380000.00, 'กิจกรรมพัฒนาคุณภาพผู้เรียน 4 กิจกรรมหลักและทัศนศึกษา', '2024-10-20'),
(3, 1, 'SRC-68-03', 'เงินรายได้สถานศึกษา', 'school_income', 180000.00, 'ค่าเช่าพื้นที่ ค่าธรรมเนียม และเงินผลประโยชน์', '2024-11-01'),
(4, 1, 'SRC-68-04', 'เงินระดมทรัพยากรและการบริจาคเพื่อการศึกษา', 'donation', 90000.00, 'ผ้าป่าเพื่อการศึกษาและเงินสมทบจากชุมชน', '2024-11-15'),
(5, 1, 'SRC-68-05', 'เงินอุดหนุนปัจจัยพื้นฐานนักเรียนยากจน (CCT)', 'poverty_fund', 50000.00, 'ช่วยเหลือค่านักเรียนยากจนพิเศษแบบมีเงื่อนไข', '2024-10-25');

-- แหล่งงบประมาณปี 2567 (ยอดรวม 1,600,000 บาท สำหรับเปรียบเทียบย้อนหลัง)
INSERT INTO `budget_sources` (`id`, `fiscal_year_id`, `code`, `name`, `category`, `amount`, `description`, `received_date`) VALUES
(6, 2, 'SRC-67-01', 'เงินอุดหนุนรายหัวการจัดการศึกษาขั้นพื้นฐาน', 'subsidy', 1000000.00, 'งบปี 2567', '2023-10-10'),
(7, 2, 'SRC-67-02', 'เงินกิจกรรมพัฒนาคุณภาพผู้เรียน', 'student_dev', 350000.00, 'งบปี 2567', '2023-10-15'),
(8, 2, 'SRC-67-03', 'เงินรายได้สถานศึกษา', 'school_income', 150000.00, 'งบปี 2567', '2023-11-01'),
(9, 2, 'SRC-67-04', 'เงินระดมทรัพยากรเพื่อการศึกษา', 'donation', 100000.00, 'งบปี 2567', '2023-11-10');

-- จัดสรรร้อยละ 100% ให้ 4 กลุ่มงาน + งบกลาง ปี 2568
-- รวมงบประมาณปี 68 = 1,850,000 บาท
-- วิชาการ 45% = 832,500
-- งบประมาณ 15% = 277,500
-- บุคคล 10% = 185,000
-- ทั่วไป 20% = 370,000
-- งบสำรองจ่าย/ส่วนกลาง 10% = 185,000
-- รวม = 100%
INSERT INTO `department_allocations` (`id`, `fiscal_year_id`, `department`, `department_name`, `percentage`, `allocated_amount`, `notes`) VALUES
(1, 1, 'academic', 'กลุ่มบริหารวิชาการ', 45.00, 832500.00, 'เน้นการยกระดับผลสัมฤทธิ์ทางการเรียนและการอ่านออกเขียนได้'),
(2, 1, 'budget', 'กลุ่มบริหารงบประมาณและสินทรัพย์', 15.00, 277500.00, 'จัดทำบัญชี จัดซื้อจัดจ้าง และควบคุมพัสดุครุภัณฑ์'),
(3, 1, 'personnel', 'กลุ่มบริหารงานบุคคล', 10.00, 185000.00, 'พัฒนาทักษะสมรรถนะครูและการอบรมเชิงปฏิบัติการ'),
(4, 1, 'general', 'กลุ่มบริหารทั่วไป', 20.00, 370000.00, 'ปรับปรุงสภาพแวดล้อม อาคารสถานที่และสุขอนามัย'),
(5, 1, 'reserve', 'งบสำรองจ่าย/ส่วนกลาง', 10.00, 185000.00, 'รองรับกรณีฉุกเฉิน กิจกรรมเฉพาะกิจและภัยธรรมชาติ');

-- จัดสรรร้อยละ 100% ปี 2567
INSERT INTO `department_allocations` (`id`, `fiscal_year_id`, `department`, `department_name`, `percentage`, `allocated_amount`, `notes`) VALUES
(6, 2, 'academic', 'กลุ่มบริหารวิชาการ', 45.00, 720000.00, 'งบจัดสรรปี 2567'),
(7, 2, 'budget', 'กลุ่มบริหารงบประมาณและสินทรัพย์', 15.00, 240000.00, 'งบจัดสรรปี 2567'),
(8, 2, 'personnel', 'กลุ่มบริหารงานบุคคล', 10.00, 160000.00, 'งบจัดสรรปี 2567'),
(9, 2, 'general', 'กลุ่มบริหารทั่วไป', 20.00, 320000.00, 'งบจัดสรรปี 2567'),
(10, 2, 'reserve', 'งบสำรองจ่าย/ส่วนกลาง', 10.00, 160000.00, 'งบจัดสรรปี 2567');

-- ตัวอย่างโครงการปี 2568
INSERT INTO `projects` (`id`, `fiscal_year_id`, `department`, `code`, `name`, `proposer_id`, `supervisor_id`, `strategy_alignment`, `standard_alignment`, `rationale`, `objectives`, `target_qty`, `target_quality`, `start_date`, `end_date`, `location`, `budget_source_id`, `requested_budget`, `approved_budget`, `expected_outcomes`, `indicators`, `evaluation_method`, `status`, `screening_note`, `director_note`, `approved_at`, `progress_percentage`, `execution_status`, `results_summary`) VALUES
(1, 1, 'academic', 'วิชาการ-01', 'โครงการยกระดับผลสัมฤทธิ์ทางการเรียนและการประเมิน RT, NT, O-NET', 8, 4, 'ยุทธศาสตร์ที่ 1 พัฒนาคุณภาพและมาตรฐานการศึกษาขั้นพื้นฐาน', 'มาตรฐานที่ 1 คุณภาพของผู้เรียน', 'เนื่องจากผลการทดสอบระดับชาติในปีการศึกษาที่ผ่านมายังต้องพัฒนาอย่างต่อเนื่อง จึงจำเป็นต้องจัดกิจกรรมสอนเสริมและค่ายวิชาการเพื่อเสริมสร้างสมรรถนะการเรียนรู้ของผู้เรียนให้มีความพร้อมในศตวรรษที่ 21', '1. เพื่อยกระดับผลการประเมิน RT, NT และ O-NET สูงกว่าระดับประเทศ\n2. เพื่อพัฒนาทักษะการคิดวิเคราะห์และการแก้ปัญหาของผู้เรียน', 'นักเรียนชั้น ป.1, ป.3 และ ป.6 ทุกคนจำนวน 180 คน เข้าร่วมกิจกรรมสอนเสริมและค่ายวิชาการ', 'ร้อยละ 85 ของนักเรียนมีผลการเรียนรู้ผ่านเกณฑ์ที่กำหนด และมีคะแนนเฉลี่ยสูงกว่าเป้าหมายสถานศึกษา', '2024-11-01', '2025-03-31', 'ห้องประชุมและห้องเรียนโรงเรียนอนุบาลพัฒนาวิทยา', 1, 95000.00, 90000.00, 'นักเรียนมีผลสัมฤทธิ์ทางการเรียนสูงขึ้น มีเจตคติที่ดีต่อการเรียนรู้', 'ร้อยละของนักเรียนที่มีผลคะแนนสอบผ่านเกณฑ์ร้อยละ 50 ขึ้นไป', 'แบบทดสอบ, แบบสังเกตพฤติกรรม, แบบประเมินความพึงพอใจ', 'approved', 'ปรับลดค่าเอกสารประกอบการติวลง 5,000 บาท ให้อยู่ในกรอบงบประมาณวิชาการ เห็นควรอนุมัติ', 'อนุมัติตามที่กลั่นกรอง ขอให้ครูผู้สอนติดตามผลการทดสอบอย่างใกล้ชิด', '2024-10-28 09:30:00', 65, 'in_progress', 'จัดค่ายเสริมทักษะภาษาไทยและคณิตศาสตร์เรียบร้อยแล้ว อยู่ระหว่างเตรียมสอบ RT ป.1 และ NT ป.3'),

(2, 1, 'academic', 'วิชาการ-02', 'โครงการส่งเสริมการอ่านออกเขียนได้และห้องสมุดมีชีวิตดิจิทัล', 8, 4, 'ยุทธศาสตร์ที่ 1 พัฒนาคุณภาพการศึกษา', 'มาตรฐานที่ 1 คุณภาพของผู้เรียน', 'การอ่านและการรู้หนังสือเป็นรากฐานสำคัญของการเรียนรู้ทุกกลุ่มสาระ การพัฒนาห้องสมุดให้ทันสมัยจะช่วยกระตุ้นนิสัยรักการอ่าน', '1. ส่งเสริมให้นักเรียนทุกคนอ่านคล่องเขียนคล่องตามระดับชั้น\n2. จัดหาสื่อเทคโนโลยีดิจิทัลและหนังสือน่าอ่านสู่ห้องสมุด', 'นักเรียนระดับชั้นอนุบาลถึง ป.6 จำนวน 320 คน', 'นักเรียนทุกคนอ่านออกเขียนได้ตามเกณฑ์มาตรฐาน สพฐ. ร้อยละ 100', '2024-10-15', '2025-08-31', 'ห้องสมุดและลานการเรียนรู้', 2, 75000.00, 75000.00, 'นักเรียนมีนิสัยรักการอ่าน ค้นคว้าข้อมูลด้วยตนเองได้อย่างมีประสิทธิภาพ', 'ผลการประเมินการอ่านออกเขียนได้ของ สพฐ.', 'แบบบันทึกการยืมคืนหนังสือ, แบบประเมินการอ่าน', 'approved', 'โครงการมีความสำคัญต่อพื้นฐานการเรียนรู้ วงเงินเหมาะสม ให้ความเห็นชอบ', 'อนุมัติโครงการเพื่อสร้างนิสัยรักการอ่านแก่เด็กทุกคน', '2024-10-28 10:00:00', 50, 'in_progress', 'จัดซื้อหนังสือนิทาน วรรณกรรมเยาวชน และระบบยืม-คืนบาร์โค้ดแล้ว'),

(3, 1, 'general', 'ทั่วไป-01', 'โครงการปรับปรุงภูมิทัศน์และสิ่งแวดล้อมเพื่อสุขภาวะโรงเรียนน่าอยู่', 9, 7, 'ยุทธศาสตร์ที่ 3 เสริมสร้างบรรยากาศและสิ่งแวดล้อมที่เอื้อต่อการเรียนรู้', 'มาตรฐานที่ 2 กระบวนการบริหารและการจัดการ', 'สภาพแวดล้อมที่ปลอดภัย สะอาด ร่มรื่น มีผลโดยตรงต่อการส่งเสริมสุขอนามัยและความปลอดภัยของผู้เรียนในสถานศึกษา', '1. ปรับปรุงระบบระบายน้ำและลานกิจกรรมกลางแจ้ง\n2. ซ่อมบำรุงห้องน้ำห้องสุขาและจุดล้างมือให้ถูกสุขลักษณะ', 'พื้นที่โดยรอบอาคารเรียน 3 หลัง ลานกิจกรรม และห้องสุขา 4 หลัง', 'สภาพแวดล้อมในโรงเรียนมีความปลอดภัย สะอาด และผ่านมาตรฐานสุขาภิบาลโรงเรียน', '2024-11-01', '2025-04-30', 'บริเวณโดยรอบโรงเรียน', 1, 120000.00, 110000.00, 'โรงเรียนมีภูมิทัศน์สวยงาม ปลอดภัย และเอื้อต่อการจัดกิจกรรมการเรียนรู้', 'แบบสำรวจความปลอดภัยและความพึงพอใจของนักเรียนและผู้ปกครอง', 'แบบตรวจสุขอนามัย, แบบประเมินความพึงพอใจ', 'approved', 'ปรับลดค่าป้ายประชาสัมพันธ์ลง 10,000 บาท คงเหลืองบ 110,000 บาท', 'อนุมัติเพื่อความปลอดภัยและสุขภาวะที่ดีของบุคลากรและนักเรียน', '2024-10-29 11:15:00', 40, 'in_progress', 'ดำเนินการปรับปรุงลานกิจกรรมและทาสีแนวขอบทางเสร็จสิ้นแล้ว อยู่ระหว่างปรับปรุงสุขภัณฑ์'),

(4, 1, 'personnel', 'บุคคล-01', 'โครงการพัฒนาสมรรถนะครูสู่การจัดการเรียนรู้เชิงรุก (Active Learning) และ AI เพื่อการศึกษา', 6, 6, 'ยุทธศาสตร์ที่ 2 พัฒนาครูและบุคลากรทางการศึกษา', 'มาตรฐานที่ 2 กระบวนการบริหารและการจัดการ', 'เพื่อส่งเสริมให้ครูสามารถนำเทคโนโลยีดิจิทัลและปัญญาประดิษฐ์มาประยุกต์ใช้ในการจัดการเรียนการสอนและการวัดประเมินผลอย่างมีประสิทธิภาพ', '1. พัฒนาครูให้สามารถออกแบบแผนการจัดการเรียนรู้แบบ Active Learning\n2. ส่งเสริมการใช้เครื่องมือ AI ในการพัฒนาสื่อการสอน', 'ข้าราชการครูและบุคลากรทางการศึกษาทุกคนจำนวน 22 คน', 'ครูร้อยละ 100 มีแผนจัดการเรียนรู้ Active Learning และผลิตสื่อนวัตกรรมอย่างน้อยคนละ 1 ชิ้น', '2024-12-01', '2025-05-31', 'ห้องประชุมสารสนเทศ', 1, 65000.00, 60000.00, 'ครูมีทักษะการสอนสมัยใหม่ นักเรียนได้รับการจัดการเรียนรู้ที่กระตุ้นการคิดสร้างสรรค์', 'จำนวนสื่อนวัตกรรมและผลการประเมินการนิเทศการสอน', 'แผนการสอน, รายงานนวัตกรรม, แบบประเมินการอบรม', 'approved', 'สอดคล้องกับนโยบายกระทรวงศึกษาธิการ วงเงินอยู่ในกรอบงานบุคคล', 'อนุมัติโครงการเพื่อพัฒนาครูให้ก้าวทันเทคโนโลยี', '2024-10-29 14:00:00', 30, 'in_progress', 'จัดอบรมเชิงปฏิบัติการครั้งที่ 1 เรียบร้อยแล้ว'),

(5, 1, 'budget', 'งบประมาณ-01', 'โครงการพัฒนาระบบเทคโนโลยีสารสนเทศและการบริหารพัสดุสินทรัพย์ดิจิทัล', 5, 5, 'ยุทธศาสตร์ที่ 4 เพิ่มประสิทธิภาพการบริหารจัดการภาครัฐ', 'มาตรฐานที่ 2 กระบวนการบริหารและการจัดการ', 'การบริหารจัดการพัสดุและงบประมาณด้วยระบบดิจิทัลจะช่วยสร้างความโปร่งใส ตรวจสอบได้ และลดระยะเวลาการทำงาน', '1. พัฒนาระบบทะเบียนคุมพัสดุและครุภัณฑ์ด้วย QR Code\n2. จัดหาระบบสำรองข้อมูลและคอมพิวเตอร์สำหรับการเงินพัสดุ', 'ระบบงานพัสดุ การเงิน และบัญชีของโรงเรียน 100%', 'ข้อมูลพัสดุถูกต้อง มีการตรวจสอบประจำปีได้รวดเร็วขึ้นร้อยละ 50', '2024-10-01', '2025-07-31', 'ห้องกลุ่มบริหารงบประมาณและสินทรัพย์', 3, 50000.00, 48000.00, 'การบริหารพัสดุมีความถูกต้อง โปร่งใส เป็นไปตามระเบียบพัสดุภาครัฐ', 'รายงานการตรวจสอบพัสดุประจำปี', 'รายงานการตรวจสอบพัสดุ, การสแกน QR Code', 'approved', 'วงเงินเหมาะสมและช่วยเพิ่มประสิทธิภาพการควบคุมสินทรัพย์', 'อนุมัติเพื่อความโปร่งใสและตรวจสอบได้', '2024-10-30 15:30:00', 80, 'in_progress', 'ติดแท็ก QR Code ครุภัณฑ์เสร็จแล้วกว่า 80%'),

(6, 1, 'academic', 'วิชาการ-03', 'โครงการส่งเสริมความเป็นเลิศทางคณิตศาสตร์และวิทยาศาสตร์ (STEM Education)', 8, 4, 'ยุทธศาสตร์ที่ 1 พัฒนาคุณภาพผู้เรียน', 'มาตรฐานที่ 1 คุณภาพของผู้เรียน', 'สะเต็มศึกษาช่วยบูรณาการความรู้ด้านวิทยาศาสตร์ เทคโนโลยี วิศวกรรมศาสตร์ และคณิตศาสตร์ เพื่อสร้างนักคิดและนวัตกรน้อย', '1. จัดซื้อชุดทดลองสะเต็มศึกษาและหุ่นยนต์เบื้องต้น\n2. จัดนิทรรศการสัปดาห์วิทยาศาสตร์และคณิตศาสตร์', 'นักเรียนชั้น ป.4 - ป.6 จำนวน 120 คน', 'นักเรียนมีทักษะการคิดเชิงคำนวณและการแก้ปัญหาตามแนวทางสะเต็มศึกษา', '2025-01-10', '2025-08-20', 'ห้องปฏิบัติการวิทยาศาสตร์', 1, 80000.00, 70000.00, 'นักเรียนสามารถสร้างชิ้นงานหรือสิ่งประดิษฐ์ทางวิทยาศาสตร์ได้', 'ผลงานโครงงานวิทยาศาสตร์และสะเต็มศึกษา', 'แบบประเมินโครงงาน, แบบสังเกตพฤติกรรม', 'screened', 'ปรับลดงบประมาณจาก 80,000 เป็น 70,000 บาท เนื่องจากรายการชุดทดลองบางชิ้นโรงเรียนมีอยู่แล้ว รอ ผอ. ลงนามอนุมัติ', '', NULL, 0, 'not_started', ''),

(7, 1, 'academic', 'วิชาการ-04', 'โครงการค่ายภาษาอังกฤษเพื่อการสื่อสารสู่อาเซียน (English Camp)', 8, 4, 'ยุทธศาสตร์ที่ 1 พัฒนาคุณภาพการศึกษา', 'มาตรฐานที่ 1 คุณภาพของผู้เรียน', 'การฝึกทักษะภาษาอังกฤษผ่านค่ายกิจกรรมเชิงรุกช่วยสร้างความมั่นใจในการสื่อสาร', '1. ให้นักเรียนได้ฝึกสนทนาภาษาอังกฤษกับเจ้าของภาษา\n2. จัดกิจกรรมบูรณาการทักษะฟัง-พูด-อ่าน-เขียนอย่างสนุกสนาน', 'นักเรียนชั้น ป.1 - ป.6 ทุกคน 320 คน', 'นักเรียนมีความกล้าแสดงออกในการสื่อสารภาษาอังกฤษเพิ่มขึ้นร้อยละ 80', '2025-02-15', '2025-02-17', 'ค่ายลูกเสือ/หอประชุมโรงเรียน', 2, 60000.00, 0.00, 'นักเรียนมีเจตคติที่ดีและกล้าสื่อสารภาษาอังกฤษในชีวิตประจำวัน', 'แบบประเมินทักษะการสนทนาภาษาอังกฤษ', 'แบบทดสอบก่อน-หลังเรียน, แบบสอบถามความพึงพอใจ', 'submitted', '', '', NULL, 0, 'not_started', '');

-- รายการแจกแจงค่าใช้จ่ายของโครงการยกระดับผลสัมฤทธิ์ทางการเรียน (โครงการที่ 1)
INSERT INTO `project_budget_items` (`id`, `project_id`, `category`, `item_name`, `quantity`, `unit`, `unit_price`, `total_price`) VALUES
(1, 1, 'compensation', 'ค่าตอบแทนวิทยากรภายนอกติวเข้ม O-NET/NT (6 ชม. x 600 บาท)', 6.00, 'ชั่วโมง', 600.00, 3600.00),
(2, 1, 'operating', 'ค่าอาหารกลางวันนักเรียนและคณะทำงานค่ายติวเข้ม (180 คน x 60 บาท x 3 วัน)', 540.00, 'มื้อ', 60.00, 32400.00),
(3, 1, 'operating', 'ค่าอาหารว่างและเครื่องดื่ม (180 คน x 25 บาท x 2 มื้อ x 3 วัน)', 1080.00, 'ชุด', 25.00, 27000.00),
(4, 1, 'materials', 'ค่าจัดพิมพ์คู่มือแบบฝึกทักษะและข้อสอบเสมือนจริง RT, NT, O-NET', 180.00, 'เล่ม', 120.00, 21600.00),
(5, 1, 'materials', 'ค่าเครื่องเขียนและกระดาษคำตอบสำหรับทดสอบเสมือนจริง', 1.00, 'ชุด', 5400.00, 5400.00);

-- รายการเบิกจ่ายจริงของโครงการยกระดับผลสัมฤทธิ์ (โครงการที่ 1) รวมเบิกจ่ายแล้ว 57,600 บาท จากที่อนุมัติ 90,000 บาท คงเหลือ 32,400 บาท
INSERT INTO `project_expenses` (`id`, `project_id`, `expense_date`, `doc_number`, `title`, `category`, `amount`, `disbursed_by`, `receipt_note`) VALUES
(1, 1, '2024-11-20', 'ขบ. 12/2568', 'ค่าจัดพิมพ์คู่มือแบบฝึกทักษะข้อสอบเสมือนจริง RT, NT, O-NET', 'materials', 21600.00, 'นายสมชาย สอนสนุก', 'ใบเสร็จรับเงินเล่มที่ 04 เลขที่ 28 ร้านวิทยาภัณฑ์การพิมพ์'),
(2, 1, '2024-12-15', 'ขบ. 25/2568', 'ค่าอาหารกลางวันและอาหารว่างค่ายติวเข้มรอบที่ 1', 'operating', 20600.00, 'นายสมชาย สอนสนุก', 'ใบสำคัญรับเงินกลุ่มแม่บ้านประกอบอาหาร'),
(3, 1, '2025-01-18', 'ขบ. 44/2568', 'ค่าตอบแทนวิทยากรภายนอกติววิชาวิทยาศาสตร์และคณิตศาสตร์', 'compensation', 3600.00, 'นายสมชาย สอนสนุก', 'ใบสำคัญรับเงินวิทยากร อ.สมศักดิ์ ชัยเจริญ'),
(4, 1, '2025-01-25', 'ขบ. 51/2568', 'ค่าเครื่องเขียนและกระดาษคำตอบ Pre-test', 'materials', 5400.00, 'นายสมชาย สอนสนุก', 'ใบเสร็จร้านบุญครองเครื่องเขียน');

-- รายการเบิกจ่ายจริงของโครงการปรับปรุงภูมิทัศน์ (โครงการที่ 3) เบิกจ่าย 45,000 จาก 110,000
INSERT INTO `project_expenses` (`id`, `project_id`, `expense_date`, `doc_number`, `title`, `category`, `amount`, `disbursed_by`, `receipt_note`) VALUES
(5, 3, '2024-11-28', 'ขบ. 18/2568', 'ค่าสีทาแนวขอบทางและปูนซีเมนต์ซ่อมแซมลานกิจกรรม', 'materials', 28000.00, 'นายพิชิต สภาพแวดล้อม', 'ใบเสร็จรับเงิน หจก.บุรีรัมย์โฮมมาร์ท'),
(6, 3, '2024-12-20', 'ขบ. 31/2568', 'ค่าจ้างเหมาซ่อมแซมระบบท่อระบายน้ำรอบอาคารเรียน 1', 'operating', 17000.00, 'นายพิชิต สภาพแวดล้อม', 'ใบตรวจรับพัสดุและใบเสร็จช่างชุมชน');

-- รายการเบิกจ่ายจริงของโครงการพัฒนาสมรรถนะครู (โครงการที่ 4) เบิกจ่าย 22,000 จาก 60,000
INSERT INTO `project_expenses` (`id`, `project_id`, `expense_date`, `doc_number`, `title`, `category`, `amount`, `disbursed_by`, `receipt_note`) VALUES
(7, 4, '2024-12-18', 'ขบ. 29/2568', 'ค่าอาหารว่างและเครื่องดื่มการอบรม Active Learning รุ่นที่ 1', 'operating', 8000.00, 'นางสาวพิมพ์ใจ เสริมบุคคล', 'ใบเสร็จรับเงินร้านกาแฟชุมชน'),
(8, 4, '2024-12-18', 'ขบ. 30/2568', 'ค่าวิทยากรการอบรมการประยุกต์ใช้ AI ในการสร้างสื่อการสอน', 'compensation', 14000.00, 'นางสาวพิมพ์ใจ เสริมบุคคล', 'ใบสำคัญรับเงิน ดร.ปิยะ วงศ์ใหญ่');

-- รายการเบิกจ่ายจริงของโครงการพัฒนาระบบเทคโนโลยีสารสนเทศ (โครงการที่ 5) เบิกจ่าย 38,000 จาก 48,000
INSERT INTO `project_expenses` (`id`, `project_id`, `expense_date`, `doc_number`, `title`, `category`, `amount`, `disbursed_by`, `receipt_note`) VALUES
(9, 5, '2024-10-25', 'ขบ. 05/2568', 'ค่าเครื่องสแกนบาร์โค้ดและฉลาก QR Code สำหรับทะเบียนพัสดุ', 'materials', 18000.00, 'นายสุรชัย บัญชีทรัพย์', 'ใบกำกับภาษี บจก.ไทยไอทีเซอร์วิส'),
(10, 5, '2024-11-10', 'ขบ. 10/2568', 'ค่าอุปกรณ์จัดเก็บข้อมูลสำรองความปลอดภัยของระบบบัญชี (NAS & HDD)', 'materials', 20000.00, 'นายสุรชัย บัญชีทรัพย์', 'ใบกำกับภาษี บจก.เจเนอรัลคอมพิวเตอร์');

-- บันทึกความก้าวหน้าโครงการ (Project progress logs)
INSERT INTO `project_progress_logs` (`id`, `project_id`, `log_date`, `progress_percent`, `details`, `obstacles`, `solutions`, `recorded_by`) VALUES
(1, 1, '2024-11-25', 30, 'จัดทำเอกสารคู่มือแบบฝึกทักษะและจัดกิจกรรม Pre-test ครบทั้ง 3 ระดับชั้น', 'นักเรียนบางส่วนยังขาดทักษะการอ่านจับใจความในข้อสอบภาษาไทย', 'ครูประจำวิชาเพิ่มเวลาฝึกอ่านจับใจความช่วงพักกลางวัน 15 นาที', 'นายสมชาย สอนสนุก'),
(2, 1, '2025-01-20', 65, 'จัดค่ายติวเข้มเข้มข้นร่วมกับวิทยากรภายนอก นักเรียนมีความพร้อมและมั่นใจมากขึ้น', 'ช่วงเวลาติวชนกับกิจกรรมกีฬาอำเภอ', 'ปรับเปลี่ยนตารางการสอนเสริมในช่วงบ่ายวันเสาร์เพื่อไม่ให้กระทบเวลาเรียน', 'นายสมชาย สอนสนุก'),
(3, 3, '2024-12-25', 40, 'ซ่อมแซมระบบระบายน้ำและทาสีลานกิจกรรมเสร็จแล้ว โรงเรียนมีความปลอดภัยน่าอยู่มากขึ้น', 'ช่วงต้นเดือนมีฝนตกประปรายทำให้งานทาสีล่าช้าเล็กน้อย', 'จัดเวรช่างเร่งดำเนินงานในช่วงวันหยุดสุดสัปดาห์', 'นายพิชิต สภาพแวดล้อม');

SET FOREIGN_KEY_CHECKS = 1;
