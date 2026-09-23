<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// ต้องเป็น Super Admin เท่านั้นที่สามารถรันสคริปต์นี้ได้
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้']);
    exit;
}

$results = [];

try {
    // 1. ตรวจสอบและเพิ่มคอลัมน์ is_academic ในตาราง users
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_academic'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_academic BOOLEAN DEFAULT FALSE AFTER role");
        $results[] = "เพิ่มคอลัมน์ is_academic ในตาราง users สำเร็จ";
    } else {
        $results[] = "ตาราง users มีคอลัมน์ is_academic อยู่แล้ว";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_name'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_name VARCHAR(255) AFTER name");
        $results[] = "เพิ่มคอลัมน์ last_name ในตาราง users สำเร็จ";
    }

    // เพิ่มคอลัมน์สถิติการใช้งาน
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'login_count'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0 AFTER is_academic");
        $results[] = "เพิ่มคอลัมน์ login_count ในตาราง users สำเร็จ";
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_login'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login DATETIME AFTER login_count");
        $results[] = "เพิ่มคอลัมน์ last_login ในตาราง users สำเร็จ";
    }

    // 2. ตรวจสอบและเพิ่มคอลัมน์ national_id ในตาราง students
    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'national_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN national_id VARCHAR(13) NOT NULL AFTER student_code");
        $results[] = "เพิ่มคอลัมน์ national_id ในตาราง students สำเร็จ";
    } else {
        $results[] = "ตาราง students มีคอลัมน์ national_id อยู่แล้ว";
    }

    // 3. ตรวจสอบและเพิ่มคอลัมน์ hours ในตาราง subjects
    $stmt = $pdo->query("SHOW COLUMNS FROM subjects LIKE 'hours'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN hours INT DEFAULT 40 AFTER level");
        $results[] = "เพิ่มคอลัมน์ hours ในตาราง subjects สำเร็จ";
    } else {
        $results[] = "ตาราง subjects มีคอลัมน์ hours อยู่แล้ว";
    }

    // 4. ตรวจสอบและเพิ่มคอลัมน์ credits ในตาราง subjects
    $stmt = $pdo->query("SHOW COLUMNS FROM subjects LIKE 'credits'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN credits FLOAT DEFAULT 1.0 AFTER hours");
        $results[] = "เพิ่มคอลัมน์ credits ในตาราง subjects สำเร็จ";
    } else {
        $results[] = "ตาราง subjects มีคอลัมน์ credits อยู่แล้ว";
    }

    // 4.1 ตรวจสอบและเพิ่มคอลัมน์ learning_area ในตาราง subjects
    $stmt = $pdo->query("SHOW COLUMNS FROM subjects LIKE 'learning_area'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN learning_area VARCHAR(255) AFTER credits");
        $results[] = "เพิ่มคอลัมน์ learning_area ในตาราง subjects สำเร็จ";
    } else {
        $results[] = "ตาราง subjects มีคอลัมน์ learning_area อยู่แล้ว";
    }

    // 4.5 ตรวจสอบและเพิ่มคอลัมน์ logo_url ในตาราง schools
    $stmt = $pdo->query("SHOW COLUMNS FROM schools LIKE 'logo_url'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE schools ADD COLUMN logo_url TEXT AFTER province");
        $results[] = "เพิ่มคอลัมน์ logo_url ในตาราง schools สำเร็จ";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM schools LIKE 'director_name'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE schools ADD COLUMN director_name VARCHAR(255) AFTER logo_url");
        $pdo->exec("ALTER TABLE schools ADD COLUMN academic_head_name VARCHAR(255) AFTER director_name");
        $pdo->exec("ALTER TABLE schools ADD COLUMN academic_head_position VARCHAR(255) DEFAULT 'หัวหน้างานวิชาการ' AFTER academic_head_name");
        $results[] = "เพิ่มคอลัมน์สำหรับผู้บริหารและหัวหน้างานวิชาการในตาราง schools สำเร็จ";
    }

    // เพิ่มคอลัมน์ affiliation และ district ในตาราง schools
    $stmt = $pdo->query("SHOW COLUMNS FROM schools LIKE 'affiliation'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE schools ADD COLUMN affiliation VARCHAR(255) AFTER name");
        $results[] = "เพิ่มคอลัมน์ affiliation ในตาราง schools สำเร็จ";
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM schools LIKE 'district'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE schools ADD COLUMN district VARCHAR(100) AFTER affiliation");
        $results[] = "เพิ่มคอลัมน์ district ในตาราง schools สำเร็จ";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM schools LIKE 'show_grades'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE schools ADD COLUMN show_grades TINYINT(1) DEFAULT 1 AFTER telegram_bot_token");
        $results[] = "เพิ่มคอลัมน์ show_grades ในตาราง schools สำเร็จ";
    }

    // 5. เพิ่มตาราง classrooms
    $pdo->exec("CREATE TABLE IF NOT EXISTS classrooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT,
        level VARCHAR(50) NOT NULL,
        room VARCHAR(10) NOT NULL,
        teacher_id_1 INT NULL,
        teacher_id_2 INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id_1) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (teacher_id_2) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_classroom (school_id, level, room)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $pdo->query("SHOW COLUMNS FROM classrooms LIKE 'teacher_id_1'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE classrooms ADD COLUMN teacher_id_1 INT NULL AFTER room");
        $pdo->exec("ALTER TABLE classrooms ADD COLUMN teacher_id_2 INT NULL AFTER teacher_id_1");
        $pdo->exec("ALTER TABLE classrooms ADD FOREIGN KEY (teacher_id_1) REFERENCES users(id) ON DELETE SET NULL");
        $pdo->exec("ALTER TABLE classrooms ADD FOREIGN KEY (teacher_id_2) REFERENCES users(id) ON DELETE SET NULL");
        $results[] = "เพิ่มคอลัมน์ครูประจำชั้นในตาราง classrooms สำเร็จ";
    }
    $results[] = "ตรวจสอบ/สร้างตาราง classrooms สำเร็จ";

    // 6. เพิ่มคอลัมน์ room และ classroom_id ในตาราง students
    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'room'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN room VARCHAR(10) AFTER level");
        $results[] = "เพิ่มคอลัมน์ room ในตาราง students สำเร็จ";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'classroom_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN classroom_id INT AFTER room");
        $pdo->exec("ALTER TABLE students ADD FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE SET NULL");
        $results[] = "เพิ่มคอลัมน์ classroom_id และ Foreign Key ในตาราง students สำเร็จ";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'academic_year'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN academic_year VARCHAR(4) DEFAULT '2567' AFTER school_id");
        $results[] = "เพิ่มคอลัมน์ academic_year ในตาราง students สำเร็จ";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'prefix'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN prefix VARCHAR(20) AFTER student_code");
        $results[] = "เพิ่มคอลัมน์ prefix ในตาราง students สำเร็จ";
    }

    // ซิงค์ classroom_id ให้กับนักเรียนที่ยังไม่มี (อิงตาม level และ room)
    $stmt = $pdo->query("SELECT year FROM academic_years WHERE is_current = 1 LIMIT 1");
    $current_year_row = $stmt->fetch();
    $current_year = $current_year_row ? $current_year_row['year'] : '2567';
    
    $pdo->prepare("UPDATE students SET academic_year = ? WHERE academic_year IS NULL OR academic_year = ''")->execute([$current_year]);
    
    // ซิงค์แบบปกติ
    $stmt_sync = $pdo->exec("UPDATE students s 
                JOIN classrooms c ON s.school_id = c.school_id AND s.level = c.level AND s.room = c.room
                SET s.classroom_id = c.id
                WHERE s.classroom_id IS NULL OR s.classroom_id = 0");

    // ซิงค์แบบล้าง prefix (เช่น ป.1 -> 1, ม.1 -> 1)
    $stmt_sync_clean = $pdo->exec("UPDATE students s 
                JOIN classrooms c ON s.school_id = c.school_id AND REPLACE(REPLACE(s.level, 'ป.', ''), 'ม.', '') = c.level AND s.room = c.room
                SET s.classroom_id = c.id
                WHERE s.classroom_id IS NULL OR s.classroom_id = 0");

    $results[] = "ซิงค์ข้อมูลห้องเรียนให้นักเรียนสำเร็จ (ปกติ: $stmt_sync, ล้าง prefix: $stmt_sync_clean) และตั้งปีการศึกษาเป็น $current_year";

    // แก้ไข teacher_assignments ที่ไม่มี classroom_id (ขยายให้ครบทุกห้องในระดับชั้นนั้น)
    $stmt = $pdo->query("SELECT ta.*, s.level, s.school_id FROM teacher_assignments ta JOIN subjects s ON ta.subject_id = s.id WHERE ta.classroom_id IS NULL OR ta.classroom_id = 0");
    $null_assignments = $stmt->fetchAll();
    if (count($null_assignments) > 0) {
        $pdo->beginTransaction();
        foreach ($null_assignments as $ta) {
            // หาห้องเรียนทั้งหมดในระดับชั้นนั้น
            $stmt_rooms = $pdo->prepare("SELECT id FROM classrooms WHERE level = ? AND school_id = ?");
            $stmt_rooms->execute([$ta['level'], $ta['school_id']]);
            $rooms = $stmt_rooms->fetchAll();
            
            foreach ($rooms as $r) {
                // ตรวจสอบว่ามีอยู่แล้วหรือไม่
                $stmt_check = $pdo->prepare("SELECT id FROM teacher_assignments WHERE teacher_id = ? AND subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester = ?");
                $stmt_check->execute([$ta['teacher_id'], $ta['subject_id'], $r['id'], $ta['academic_year'], $ta['semester']]);
                if (!$stmt_check->fetch()) {
                    $stmt_ins = $pdo->prepare("INSERT INTO teacher_assignments (teacher_id, subject_id, classroom_id, academic_year, semester) VALUES (?, ?, ?, ?, ?)");
                    $stmt_ins->execute([$ta['teacher_id'], $ta['subject_id'], $r['id'], $ta['academic_year'], $ta['semester']]);
                }
            }
            // ลบตัวที่ไม่มี classroom_id หรือ classroom_id = 0 ออก
            $pdo->prepare("DELETE FROM teacher_assignments WHERE id = ?")->execute([$ta['id']]);
        }
        $pdo->commit();
        $results[] = "ขยายงานสอนที่ไม่มีห้องเรียนให้ครบทุกห้องสำเร็จ (" . count($null_assignments) . " รายการ)";
    }

    // 7. ตรวจสอบและเพิ่มคอลัมน์ classroom_id ในตาราง teacher_assignments
    $stmt = $pdo->query("SHOW COLUMNS FROM teacher_assignments LIKE 'classroom_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE teacher_assignments ADD COLUMN classroom_id INT AFTER subject_id");
        $pdo->exec("ALTER TABLE teacher_assignments ADD FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE");
        $results[] = "เพิ่มคอลัมน์ classroom_id ในตาราง teacher_assignments สำเร็จ";
    }

    // 8. เพิ่มตารางบันทึกคะแนน
    $pdo->exec("CREATE TABLE IF NOT EXISTS grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        subject_id INT,
        classroom_id INT,
        teacher_id INT,
        academic_year VARCHAR(4),
        semester INT,
        score_midterm FLOAT DEFAULT 0,
        score_final FLOAT DEFAULT 0,
        score_total FLOAT DEFAULT 0,
        grade VARCHAR(5),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_grade (student_id, subject_id, classroom_id, academic_year, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ตรวจสอบและเพิ่มคอลัมน์ classroom_id ใน grades หากไม่มี
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'classroom_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN classroom_id INT AFTER subject_id");
        $pdo->exec("ALTER TABLE grades ADD FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE");
        $results[] = "เพิ่มคอลัมน์ classroom_id ในตาราง grades สำเร็จ";
    }

    // ตรวจสอบและเพิ่มคอลัมน์ teacher_id ใน grades หากไม่มี
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'teacher_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN teacher_id INT AFTER classroom_id");
        $pdo->exec("ALTER TABLE grades ADD FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE");
        $results[] = "เพิ่มคอลัมน์ teacher_id ในตาราง grades สำเร็จ";
    }

    // ตรวจสอบและเพิ่มคอลัมน์ score_units ใน grades หากไม่มี
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'score_units'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_units FLOAT DEFAULT 0 AFTER semester");
        $results[] = "เพิ่มคอลัมน์ score_units ในตาราง grades สำเร็จ";
    }

    // ตรวจสอบและเพิ่มคอลัมน์ score_percent ใน grades หากไม่มี
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'score_percent'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_percent FLOAT DEFAULT 0 AFTER score_total");
        $results[] = "เพิ่มคอลัมน์ score_percent ในตาราง grades สำเร็จ";
    }
    
    $results[] = "ตรวจสอบ/สร้างตาราง grades สำเร็จ";

    // ตรวจสอบและเพิ่มคอลัมน์ใน grades
    $grade_cols = [
        'classroom_id' => "INT AFTER subject_id",
        'teacher_id' => "INT AFTER classroom_id",
        'academic_year' => "VARCHAR(4) AFTER teacher_id",
        'semester' => "INT AFTER academic_year"
    ];
    foreach ($grade_cols as $col => $def) {
        $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE '$col'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE grades ADD COLUMN $col $def");
            $results[] = "เพิ่มคอลัมน์ $col ในตาราง grades สำเร็จ";
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS characteristics_scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        subject_id INT,
        classroom_id INT,
        teacher_id INT,
        academic_year VARCHAR(4),
        semester INT,
        item1 INT DEFAULT 0,
        item2 INT DEFAULT 0,
        item3 INT DEFAULT 0,
        item4 INT DEFAULT 0,
        item5 INT DEFAULT 0,
        item6 INT DEFAULT 0,
        item7 INT DEFAULT 0,
        item8 INT DEFAULT 0,
        average_score FLOAT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_char (student_id, subject_id, classroom_id, academic_year, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = "ตรวจสอบ/สร้างตาราง characteristics_scores สำเร็จ";

    $pdo->exec("CREATE TABLE IF NOT EXISTS analytical_scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        subject_id INT,
        classroom_id INT,
        teacher_id INT,
        academic_year VARCHAR(4),
        semester INT,
        item1 INT DEFAULT 0,
        item2 INT DEFAULT 0,
        item3 INT DEFAULT 0,
        item4 INT DEFAULT 0,
        item5 INT DEFAULT 0,
        average_score FLOAT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_analytical (student_id, subject_id, classroom_id, academic_year, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ensure columns exist if table was already created
    $stmt = $pdo->query("SHOW COLUMNS FROM analytical_scores LIKE 'item1'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE analytical_scores 
            ADD COLUMN item1 INT DEFAULT 0 AFTER semester,
            ADD COLUMN item2 INT DEFAULT 0 AFTER item1,
            ADD COLUMN item3 INT DEFAULT 0 AFTER item2,
            ADD COLUMN item4 INT DEFAULT 0 AFTER item3,
            ADD COLUMN item5 INT DEFAULT 0 AFTER item4,
            ADD COLUMN average_score FLOAT DEFAULT 0 AFTER item5
        ");
    }
    $results[] = "ตรวจสอบ/สร้างตาราง analytical_scores สำเร็จ";

    // 9. ปรับปรุงตาราง learning_units และเพิ่ม unit_scores
    $pdo->exec("CREATE TABLE IF NOT EXISTS learning_units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT,
        classroom_id INT,
        academic_year VARCHAR(4),
        semester INT,
        unit_name VARCHAR(255) NOT NULL,
        max_score FLOAT DEFAULT 10,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ตรวจสอบและเพิ่มคอลัมน์ classroom_id ใน learning_units หากไม่มี
    $stmt = $pdo->query("SHOW COLUMNS FROM learning_units LIKE 'classroom_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE learning_units ADD COLUMN classroom_id INT AFTER subject_id");
        $pdo->exec("ALTER TABLE learning_units ADD FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM learning_units LIKE 'academic_year'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE learning_units ADD COLUMN academic_year VARCHAR(4) AFTER classroom_id");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM learning_units LIKE 'semester'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE learning_units ADD COLUMN semester INT AFTER academic_year");
    }
    $results[] = "ตรวจสอบ/สร้างตาราง learning_units สำเร็จ";

    // ปรับปรุงตาราง grades ให้รองรับคะแนนหน่วยและร้อยละ
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'score_units'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_units FLOAT DEFAULT 0 AFTER semester");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'score_midterm'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_midterm FLOAT DEFAULT 0 AFTER score_units");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'score_final'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_final FLOAT DEFAULT 0 AFTER score_midterm");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'score_total'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_total FLOAT DEFAULT 0 AFTER score_final");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'score_percent'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_percent FLOAT DEFAULT 0 AFTER score_total");
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'grade'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN grade VARCHAR(5) AFTER score_percent");
    }

    // เพิ่ม Unique Key ให้กับตาราง grades หากยังไม่มี เพื่อให้ ON DUPLICATE KEY UPDATE ทำงานถูกต้อง
    $stmt = $pdo->query("SHOW INDEX FROM grades WHERE Key_name = 'unique_grade'");
    if (!$stmt->fetch()) {
        try {
            // ลบข้อมูลซ้ำออกก่อน โดยเก็บใบที่มี ID สูงสุดไว้
            $pdo->exec("DELETE g1 FROM grades g1
                       INNER JOIN grades g2 
                       WHERE g1.id < g2.id 
                       AND g1.student_id = g2.student_id 
                       AND g1.subject_id = g2.subject_id 
                       AND g1.classroom_id = g2.classroom_id 
                       AND g1.academic_year = g2.academic_year 
                       AND g1.semester = g2.semester");
            
            $pdo->exec("ALTER TABLE grades ADD UNIQUE KEY unique_grade (student_id, subject_id, classroom_id, academic_year, semester)");
            $results[] = "เพิ่ม Unique Key ให้กับตาราง grades สำเร็จ (และลบข้อมูลซ้ำ)";
        } catch (Exception $e) {
            $results[] = "ไม่สามารถเพิ่ม Unique Key ให้กับตาราง grades: " . $e->getMessage();
        }
    }

    $results[] = "ตรวจสอบ/ปรับปรุงตาราง grades สำเร็จ";

    $pdo->exec("CREATE TABLE IF NOT EXISTS unit_scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        learning_unit_id INT,
        score FLOAT DEFAULT 0,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (learning_unit_id) REFERENCES learning_units(id) ON DELETE CASCADE,
        UNIQUE KEY unique_unit_score (student_id, learning_unit_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = "ตรวจสอบ/สร้างตาราง unit_scores สำเร็จ";

    // 10. ปรับปรุงตาราง grades ให้รองรับคะแนนรายปี
    $stmt = $pdo->query("SHOW COLUMNS FROM grades LIKE 'score_semester1'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_semester1 FLOAT DEFAULT 0 AFTER semester");
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_semester2 FLOAT DEFAULT 0 AFTER score_semester1");
        $pdo->exec("ALTER TABLE grades ADD COLUMN score_annual_avg FLOAT DEFAULT 0 AFTER score_semester2");
        $results[] = "เพิ่มคอลัมน์คะแนนรายปีในตาราง grades สำเร็จ";
    }

    // 11. เพิ่มตาราง academic_years และคอลัมน์สถานะนักเรียน
    $pdo->exec("CREATE TABLE IF NOT EXISTS academic_years (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT,
        year VARCHAR(4) NOT NULL,
        is_current BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
        UNIQUE KEY unique_year (school_id, year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = "ตรวจสอบ/สร้างตาราง academic_years สำเร็จ";

    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'status'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN status ENUM('studying', 'graduated', 'transferred', 'quit') DEFAULT 'studying' AFTER academic_year");
        $results[] = "เพิ่มคอลัมน์ status ในตาราง students สำเร็จ";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'generation'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN generation VARCHAR(50) AFTER status");
        $results[] = "เพิ่มคอลัมน์ generation ในตาราง students สำเร็จ";
    }

    // 11.5 เพิ่มคอลัมน์สำหรับข้อมูล DMC
    $dmc_columns = [
        'last_name' => "VARCHAR(255) AFTER name",
        'gender' => "VARCHAR(10) AFTER student_code",
        'birthday' => "DATE AFTER last_name",
        'age' => "INT AFTER birthday",
        'weight' => "FLOAT AFTER age",
        'height' => "FLOAT AFTER weight",
        'blood_group' => "VARCHAR(5) AFTER height",
        'religion' => "VARCHAR(50) AFTER blood_group",
        'race' => "VARCHAR(50) AFTER religion",
        'nationality' => "VARCHAR(50) AFTER race",
        'house_no' => "VARCHAR(50) AFTER nationality",
        'moo' => "VARCHAR(10) AFTER house_no",
        'road_soi' => "VARCHAR(100) AFTER moo",
        'sub_district' => "VARCHAR(100) AFTER road_soi",
        'district' => "VARCHAR(100) AFTER sub_district",
        'province_name' => "VARCHAR(100) AFTER district",
        'parent_name' => "VARCHAR(255) AFTER province_name",
        'parent_last_name' => "VARCHAR(255) AFTER parent_name",
        'parent_occupation' => "VARCHAR(100) AFTER parent_last_name",
        'parent_relationship' => "VARCHAR(100) AFTER parent_occupation",
        'father_name' => "VARCHAR(255) AFTER parent_relationship",
        'father_last_name' => "VARCHAR(255) AFTER father_name",
        'father_occupation' => "VARCHAR(100) AFTER father_last_name",
        'mother_name' => "VARCHAR(255) AFTER father_occupation",
        'mother_last_name' => "VARCHAR(255) AFTER mother_name",
        'mother_occupation' => "VARCHAR(100) AFTER mother_last_name",
        'disadvantage' => "VARCHAR(255) AFTER mother_occupation"
    ];

    foreach ($dmc_columns as $col => $def) {
        $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE '$col'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE students ADD COLUMN $col $def");
            $results[] = "เพิ่มคอลัมน์ $col ในตาราง students สำเร็จ";
        }
    }

    // 12. เพิ่มตารางกิจกรรมพัฒนาผู้เรียน
    $pdo->exec("CREATE TABLE IF NOT EXISTS clubs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT,
        name VARCHAR(255) NOT NULL,
        academic_year VARCHAR(4) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = "ตรวจสอบ/สร้างตาราง clubs สำเร็จ";

    $pdo->exec("CREATE TABLE IF NOT EXISTS learner_development_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        classroom_id INT,
        academic_year VARCHAR(4),
        semester INT,
        guidance_result ENUM('P', 'F', '') DEFAULT '',
        scout_result ENUM('P', 'F', '') DEFAULT '',
        club_id INT,
        club_result ENUM('P', 'F', '') DEFAULT '',
        social_result ENUM('P', 'F', '') DEFAULT '',
        teacher_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
        FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE SET NULL,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_learner_dev (student_id, academic_year, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ตรวจสอบและเพิ่มคอลัมน์ teacher_id ใน learner_development_results หากไม่มี
    $stmt = $pdo->query("SHOW COLUMNS FROM learner_development_results LIKE 'teacher_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE learner_development_results ADD COLUMN teacher_id INT AFTER social_result");
        $pdo->exec("ALTER TABLE learner_development_results ADD FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL");
        $results[] = "เพิ่มคอลัมน์ teacher_id ในตาราง learner_development_results สำเร็จ";
    }

    $results[] = "ตรวจสอบ/สร้างตาราง learner_development_results สำเร็จ";

    $pdo->exec("CREATE TABLE IF NOT EXISTS learner_development_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT,
        classroom_id INT,
        academic_year VARCHAR(4),
        semester INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
        UNIQUE KEY unique_ld_assignment (teacher_id, classroom_id, academic_year, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = "ตรวจสอบ/สร้างตาราง learner_development_assignments สำเร็จ";
    
    // 13. เพิ่มตารางบันทึกน้ำหนักส่วนสูง
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_health_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        classroom_id INT,
        academic_year VARCHAR(4),
        semester INT,
        record_number INT,
        weight FLOAT,
        height FLOAT,
        weight_age_result VARCHAR(100) NULL,
        height_age_result VARCHAR(100) NULL,
        weight_height_result VARCHAR(100) NULL,
        recorded_date DATE,
        teacher_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_health_record (student_id, academic_year, semester, record_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ตรวจสอบและเพิ่มคอลัมน์ผลการประเมินหากยังไม่มี
    $health_cols = [
        'weight_age_result' => "VARCHAR(100) NULL AFTER height",
        'height_age_result' => "VARCHAR(100) NULL AFTER weight_age_result",
        'weight_height_result' => "VARCHAR(100) NULL AFTER height_age_result"
    ];
    foreach ($health_cols as $col => $def) {
        $stmt = $pdo->query("SHOW COLUMNS FROM student_health_records LIKE '$col'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE student_health_records ADD COLUMN $col $def");
            $results[] = "เพิ่มคอลัมน์ $col ในตาราง student_health_records สำเร็จ";
        }
    }
    $results[] = "ตรวจสอบ/สร้างตาราง student_health_records สำเร็จ";

    // 14. เพิ่มตารางตารางสอน
    $pdo->exec("CREATE TABLE IF NOT EXISTS timetables (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT,
        subject_id INT NULL,
        activity_type VARCHAR(50) NULL, -- 'guidance', 'scouts', 'club', 'social'
        classroom_id INT,
        academic_year VARCHAR(4),
        semester INT,
        day_of_week INT, -- 1=Mon, 2=Tue, ..., 7=Sun
        period_number INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
        UNIQUE KEY unique_timetable (classroom_id, teacher_id, academic_year, semester, day_of_week, period_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // รัน Migration ตรวจสอบ/แทนที่ดัชนี unique_timetable ให้รวม teacher_id เพื่อปลดล็อกการจัดตารางวิชาและวันหยุด/พักกลางวัน ซ้ำห้องเรียนเดียวกัน ได้ทั่วถึงทุกคุณครู
    try {
        $stmt_idx = $pdo->query("SHOW INDEX FROM timetables WHERE Key_name = 'unique_timetable'");
        $indices = $stmt_idx->fetchAll();
        $has_teacher_id = false;
        foreach ($indices as $idx) {
            $col_name = $idx['Column_name'] ?? $idx['column_name'] ?? $idx['COLUMN_NAME'] ?? null;
            if ($col_name === 'teacher_id') {
                $has_teacher_id = true;
                break;
            }
        }
        if (!$has_teacher_id) {
            try { $pdo->exec("ALTER TABLE timetables ADD INDEX temp_classroom_idx (classroom_id)"); } catch (PDOException $e) {}
            try { $pdo->exec("ALTER TABLE timetables DROP INDEX unique_timetable"); } catch (PDOException $e) {}
            try { $pdo->exec("ALTER TABLE timetables DROP INDEX unique_timetable_new"); } catch (PDOException $e) {}
            try {
                $pdo->exec("ALTER TABLE timetables ADD UNIQUE KEY unique_timetable (classroom_id, teacher_id, academic_year, semester, day_of_week, period_number)");
                $results[] = "อัปเกรดดัชนีตารางสอนสำเร็จ (รองรับการจัดกิจกรรมร่วมชั้น/สระเวลาทับซ้อนอย่างสมบูรณ์)";
            } catch (PDOException $e) {
                try {
                    $pdo->exec("ALTER TABLE timetables ADD UNIQUE KEY unique_timetable_new (classroom_id, teacher_id, academic_year, semester, day_of_week, period_number)");
                    $results[] = "อัปเกรดดัชนีตารางสอนสำรองสำเร็จ";
                } catch (PDOException $e_inner) {
                    $results[] = "ไม่สามารถเปลี่ยนดัชนีตารางสอนได้: " . $e_inner->getMessage();
                }
            }
            try { $pdo->exec("ALTER TABLE timetables DROP INDEX temp_classroom_idx"); } catch (PDOException $e) {}
        }
    } catch (PDOException $ex) {
        $results[] = "วิเคราะห์/ปรับดัชนีตารางสอนไม่สำเร็จ: " . $ex->getMessage();
    }

    // ตรวจสอบและเพิ่มคอลัมน์ activity_type ใน timetables
    $stmt = $pdo->query("SHOW COLUMNS FROM timetables LIKE 'activity_type'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE timetables ADD COLUMN activity_type VARCHAR(50) NULL AFTER subject_id");
        $pdo->exec("ALTER TABLE timetables MODIFY COLUMN subject_id INT NULL");
    }
    $results[] = "ตรวจสอบ/สร้างตาราง timetables สำเร็จ";

    // 15. เพิ่มตารางบันทึกการมาเรียน
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        subject_id INT NULL,
        activity_type VARCHAR(50) NULL,
        classroom_id INT,
        academic_year VARCHAR(4),
        semester INT,
        check_date DATE,
        period_number INT,
        status ENUM('present', 'absent', 'late', 'sick', 'leave') DEFAULT 'present',
        teacher_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_attendance (student_id, subject_id, activity_type, check_date, period_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ตรวจสอบและเพิ่มคอลัมน์ต่างๆ ใน attendance
    $attendance_cols = [
        'activity_type' => "VARCHAR(50) NULL AFTER subject_id",
        'classroom_id' => "INT AFTER activity_type",
        'academic_year' => "VARCHAR(4) AFTER classroom_id",
        'semester' => "INT AFTER academic_year",
        'check_date' => "DATE AFTER semester",
        'period_number' => "INT AFTER check_date",
        'teacher_id' => "INT AFTER status"
    ];

    foreach ($attendance_cols as $col => $def) {
        $stmt = $pdo->query("SHOW COLUMNS FROM attendance LIKE '$col'");
        if (!$stmt->fetch()) {
            // ถ้าเป็น check_date ลองดูว่ามีคอลัมน์ date เดิมไหม
            if ($col === 'check_date') {
                $stmt_old = $pdo->query("SHOW COLUMNS FROM attendance LIKE 'date'");
                if ($stmt_old->fetch()) {
                    $pdo->exec("ALTER TABLE attendance CHANGE COLUMN `date` check_date DATE");
                    $results[] = "เปลี่ยนชื่อคอลัมน์ date เป็น check_date ในตาราง attendance สำเร็จ";
                    continue;
                }
            }
            $pdo->exec("ALTER TABLE attendance ADD COLUMN $col $def");
            $results[] = "เพิ่มคอลัมน์ $col ในตาราง attendance สำเร็จ";
        }
    }

    // ปรับปรุง subject_id ให้เป็น NULL ได้
    $pdo->exec("ALTER TABLE attendance MODIFY COLUMN subject_id INT NULL");

    // อัปเดต Unique Key
    try {
        $pdo->exec("ALTER TABLE attendance DROP INDEX unique_attendance");
    } catch (Exception $e) {}
    
    try {
        $pdo->exec("ALTER TABLE attendance ADD UNIQUE KEY unique_attendance (student_id, subject_id, activity_type, check_date, period_number)");
        $results[] = "อัปเดต Unique Key ในตาราง attendance สำเร็จ";
    } catch (Exception $e) {
        // ถ้าซ้ำอาจต้องลบข้อมูลที่ซ้ำออกก่อน (ในกรณีใช้งานจริงอาจต้องระวัง)
        $results[] = "คำเตือน: ไม่สามารถสร้าง Unique Key ได้เนื่องจากมีข้อมูลซ้ำ";
    }

    $results[] = "ตรวจสอบ/สร้างตาราง attendance สำเร็จ";

    // 16. เพิ่มตารางบันทึกพฤติกรรม
    $pdo->exec("CREATE TABLE IF NOT EXISTS behavior_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        school_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS behavior_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        option_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES behavior_categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS student_behavior_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        category_id INT,
        behavior_text TEXT,
        check_date DATE,
        academic_year VARCHAR(4),
        semester INT,
        teacher_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES behavior_categories(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_behavior (student_id, category_id, check_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 16.5 เพิ่มตารางบันทึกสมรรถนะสำคัญของผู้เรียน 5 ด้าน
    $pdo->exec("CREATE TABLE IF NOT EXISTS competency_scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        classroom_id INT,
        teacher_id INT,
        academic_year VARCHAR(4),
        semester INT,
        item1 INT DEFAULT 0, -- ความสามารถในการสื่อสาร
        item2 INT DEFAULT 0, -- ความสามารถในการคิด
        item3 INT DEFAULT 0, -- ความสามารถในการแก้ปัญหา
        item4 INT DEFAULT 0, -- ความสามารถในการใช้ทักษะชีวิต
        item5 INT DEFAULT 0, -- ความสามารถในการใช้เทคโนโลยี
        average_score FLOAT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_competency (student_id, academic_year, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = "ตรวจสอบ/สร้างตาราง competency_scores สำเร็จ";

    // เพิ่มหมวดหมู่พฤติกรรมเริ่มต้น (ถ้ายังไม่มี)
    $default_categories = [
        'หน้าที่รับผิดชอบ ความเอาใจใส่การเรียน',
        'การใช้เวลาว่าง',
        'ความสัมพันธ์กับบุคคลรอบข้าง',
        'อุปนิสัย บุคลิกภาพ',
        'สุขภาพ'
    ];

    foreach ($default_categories as $cat_name) {
        $stmt = $pdo->prepare("SELECT id FROM behavior_categories WHERE name = ? AND school_id IS NULL");
        $stmt->execute([$cat_name]);
        $cat = $stmt->fetch();
        if (!$cat) {
            $pdo->prepare("INSERT INTO behavior_categories (name) VALUES (?)")->execute([$cat_name]);
            $cat_id = $pdo->lastInsertId();
            $results[] = "เพิ่มหมวดหมู่พฤติกรรมเริ่มต้น: $cat_name";
        } else {
            $cat_id = $cat['id'];
        }

        // เพิ่มตัวเลือกเริ่มต้นสำหรับแต่ละหมวดหมู่
        $default_options = [];
        if ($cat_name === 'หน้าที่รับผิดชอบ ความเอาใจใส่การเรียน') {
            $default_options = ['รับผิดชอบทำความสะอาดห้องเรียน', 'ส่งงานตรงเวลา', 'ตั้งใจเรียน', 'ขาดความรับผิดชอบในการทำงาน', 'ส่งงานล่าช้า', 'ไม่ตั้งใจเรียน'];
        } else if ($cat_name === 'การใช้เวลาว่าง') {
            $default_options = ['เล่นกีฬา', 'อ่านหนังสือในห้องสมุด', 'ฝึกซ้อมดนตรี', 'ทำกิจกรรมจิตอาสา', 'เล่นเกมมากเกินไป'];
        } else if ($cat_name === 'ความสัมพันธ์กับบุคคลรอบข้าง') {
            $default_options = ['มีมนุษยสัมพันธ์ดี', 'ช่วยเหลือเพื่อน', 'สุภาพอ่อนน้อม', 'ทะเลาะกับเพื่อน', 'ก้าวร้าว'];
        } else if ($cat_name === 'อุปนิสัย บุคลิกภาพ') {
            $default_options = ['ร่าเริงแจ่มใส', 'มีความเป็นผู้นำ', 'กล้าแสดงออก', 'เก็บตัว', 'ขาดความมั่นใจ'];
        } else if ($cat_name === 'สุขภาพ') {
            $default_options = ['สุขภาพแข็งแรง', 'ร่างกายสมบูรณ์', 'เจ็บป่วยบ่อย', 'พักผ่อนไม่เพียงพอ'];
        }

        foreach ($default_options as $opt_text) {
            $stmt_opt = $pdo->prepare("SELECT id FROM behavior_options WHERE category_id = ? AND option_text = ?");
            $stmt_opt->execute([$cat_id, $opt_text]);
            if (!$stmt_opt->fetch()) {
                $pdo->prepare("INSERT INTO behavior_options (category_id, option_text) VALUES (?, ?)")->execute([$cat_id, $opt_text]);
            }
        }
    }

    // สร้างตาราง school_officials สำหรับจัดการรายชื่อผู้บริหารและหัวหน้างานต่างๆ
    $pdo->exec("CREATE TABLE IF NOT EXISTS school_officials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        position VARCHAR(255) NOT NULL,
        role_key VARCHAR(50) NOT NULL, -- 'director', 'academic_head', 'deputy_academic', etc.
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $results[] = "สร้างตาราง school_officials สำเร็จ";

    // ย้ายข้อมูลจาก schools ไปยัง school_officials (ถ้ามี)
    $stmt = $pdo->query("SELECT id, director_name, academic_head_name, academic_head_position FROM schools");
    while ($school = $stmt->fetch()) {
        if (!empty($school['director_name'])) {
            $check = $pdo->prepare("SELECT id FROM school_officials WHERE school_id = ? AND role_key = 'director'");
            $check->execute([$school['id']]);
            if (!$check->fetch()) {
                $ins = $pdo->prepare("INSERT INTO school_officials (school_id, name, position, role_key) VALUES (?, ?, ?, 'director')");
                $ins->execute([$school['id'], $school['director_name'], 'ผู้อำนวยการโรงเรียน']);
                $results[] = "ย้ายข้อมูลผู้อำนวยการโรงเรียน {$school['director_name']} ไปยังตารางใหม่";
            }
        }
        if (!empty($school['academic_head_name'])) {
            $check = $pdo->prepare("SELECT id FROM school_officials WHERE school_id = ? AND role_key = 'academic_head'");
            $check->execute([$school['id']]);
            if (!$check->fetch()) {
                $ins = $pdo->prepare("INSERT INTO school_officials (school_id, name, position, role_key) VALUES (?, ?, ?, 'academic_head')");
                $ins->execute([$school['id'], $school['academic_head_name'], $school['academic_head_position'] ?: 'หัวหน้างานวิชาการ']);
                $results[] = "ย้ายข้อมูลหัวหน้างานวิชาการ {$school['academic_head_name']} ไปยังตารางใหม่";
            }
        }
    }

    // 17. เพิ่มตาราง app_settings สำหรับการตั้งค่าส่วนกลาง
    $pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 18. เพิ่มตารางสำหรับบันทึกคะแนนสอบระดับชาติ (RT, NT, O-NET)
    $pdo->exec("CREATE TABLE IF NOT EXISTS national_test_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT NOT NULL,
        academic_year VARCHAR(4) NOT NULL,
        test_type ENUM('rt', 'nt', 'onet_p6', 'onet_m3') NOT NULL,
        score_avg FLOAT DEFAULT 0,
        score_max FLOAT DEFAULT 100,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
        UNIQUE KEY unique_test (school_id, academic_year, test_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 19. เพิ่มตารางสำหรับบันทึกคะแนนย่อยของแต่ละวิชา/ส่วน
    $pdo->exec("CREATE TABLE IF NOT EXISTS national_test_scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        result_id INT NOT NULL,
        subject_name VARCHAR(100) NOT NULL,
        score FLOAT DEFAULT 0,
        FOREIGN KEY (result_id) REFERENCES national_test_results(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $results[] = "สร้างตารางสำหรับการบันทึกคะแนนสอบระดับชาติยิบย่อยสำเร็จ";
    
    // ตั้งค่าชื่อแอปเริ่มต้น
    $stmt = $pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'app_name'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('app_name', 'ระบบบริหารงานวิชาการ')")->execute();
        $results[] = "ตั้งค่าชื่อแอปเริ่มต้นสำเร็จ";
    } else {
        // อัปเดตชื่อแอปตามที่ผู้ใช้ขอ
        $pdo->prepare("UPDATE app_settings SET setting_value = 'ระบบบริหารงานวิชาการ' WHERE setting_key = 'app_name'")->execute();
        $results[] = "อัปเดตชื่อแอปเป็น 'ระบบบริหารงานวิชาการ' สำเร็จ";
    }

    // 20. เพิ่มคอลัมน์สำหรับการแจ้งเตือน Telegram
    $stmt = $pdo->query("SHOW COLUMNS FROM schools LIKE 'telegram_bot_token'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE schools ADD COLUMN telegram_bot_token VARCHAR(255) AFTER logo_url");
        $results[] = "เพิ่มคอลัมน์ telegram_bot_token ในตาราง schools สำเร็จ";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'parent_telegram_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN parent_telegram_id VARCHAR(100) AFTER generation");
        $results[] = "เพิ่มคอลัมน์ parent_telegram_id ในตาราง students สำเร็จ";
    }

    $results[] = "ปรับปรุงระบบแจ้งเตือน Telegram สำเร็จ";

    // ตารางความคิดเห็นผู้ปกครอง (สำหรับ ปพ.6)
    $pdo->exec("CREATE TABLE IF NOT EXISTS parent_feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        academic_year VARCHAR(4),
        semester INT,
        feedback_text TEXT,
        tags VARCHAR(255),
        responsibility_comment TEXT,
        spare_time_comment TEXT,
        relationship_comment TEXT,
        personality_comment TEXT,
        health_comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ตรวจสอบคอลัมน์ใหม่ใน parent_feedback
    $stmt = $pdo->query("SHOW COLUMNS FROM parent_feedback LIKE 'responsibility_comment'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE parent_feedback 
            ADD COLUMN responsibility_comment TEXT,
            ADD COLUMN spare_time_comment TEXT,
            ADD COLUMN relationship_comment TEXT,
            ADD COLUMN personality_comment TEXT,
            ADD COLUMN health_comment TEXT");
        $results[] = "เพิ่มคอลัมน์ความคิดเห็น 5 ด้านใน parent_feedback สำเร็จ";
    }

    // 21. ปรับโครงสร้าง Master-Detail (Normalization)
    // 21.1 สร้างตาราง student_profiles
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT NOT NULL,
        student_code VARCHAR(50),
        national_id VARCHAR(13) NOT NULL,
        prefix VARCHAR(20),
        name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255),
        gender VARCHAR(10),
        birthday DATE,
        parent_telegram_id VARCHAR(100),
        blood_group VARCHAR(5),
        religion VARCHAR(50),
        race VARCHAR(50),
        nationality VARCHAR(50),
        house_no VARCHAR(50),
        moo VARCHAR(10),
        road_soi VARCHAR(100),
        sub_district VARCHAR(100),
        district VARCHAR(100),
        province_name VARCHAR(100),
        parent_name VARCHAR(255),
        parent_last_name VARCHAR(255),
        parent_occupation VARCHAR(100),
        parent_relationship VARCHAR(100),
        father_name VARCHAR(255),
        father_last_name VARCHAR(255),
        father_occupation VARCHAR(100),
        mother_name VARCHAR(255),
        mother_last_name VARCHAR(255),
        mother_occupation VARCHAR(100),
        disadvantage VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_national_id (school_id, national_id),
        FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $results[] = "ตรวจสอบ/สร้างตาราง student_profiles สำเร็จ";

    // 21.2 เพิ่มคอลัมน์ student_profile_id ในตาราง students
    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'student_profile_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN student_profile_id INT AFTER id");
        $pdo->exec("ALTER TABLE students ADD FOREIGN KEY (student_profile_id) REFERENCES student_profiles(id) ON DELETE SET NULL");
        $results[] = "เพิ่มคอลัมน์ student_profile_id ในตาราง students สำเร็จ";
    }

    // 21.3 Migration: ย้ายข้อมูลจาก students ไปยัง student_profiles
    $stmt = $pdo->query("SELECT * FROM students WHERE student_profile_id IS NULL AND national_id != ''");
    $to_migrate = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $migrated_count = 0;
    
    if (count($to_migrate) > 0) {
        $pdo->beginTransaction();
        foreach ($to_migrate as $s) {
            // เช็คว่ามีโปรไฟล์นี้อยู่แล้วหรือยัง (อิงจาก national_id และ school_id)
            $check = $pdo->prepare("SELECT id FROM student_profiles WHERE national_id = ? AND school_id = ?");
            $check->execute([$s['national_id'], $s['school_id']]);
            $profile = $check->fetch();
            
            if (!$profile) {
                // สร้างโปรไฟล์ใหม่
                $ins = $pdo->prepare("INSERT INTO student_profiles (
                    school_id, student_code, national_id, prefix, name, last_name, 
                    gender, birthday, parent_telegram_id, blood_group, religion, 
                    race, nationality, house_no, moo, road_soi, sub_district, 
                    district, province_name, parent_name, parent_last_name, 
                    parent_occupation, parent_relationship, father_name, father_last_name, 
                    father_occupation, mother_name, mother_last_name, mother_occupation, disadvantage
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $birthday_val = (!isset($s['birthday']) || trim($s['birthday']) === '') ? null : $s['birthday'];
                $ins->execute([
                    $s['school_id'], $s['student_code'], $s['national_id'], $s['prefix'], $s['name'], $s['last_name'],
                    $s['gender'] ?? null, $birthday_val, $s['parent_telegram_id'] ?? null, 
                    $s['blood_group'] ?? null, $s['religion'] ?? null, $s['race'] ?? null, $s['nationality'] ?? null,
                    $s['house_no'] ?? null, $s['moo'] ?? null, $s['road_soi'] ?? null, $s['sub_district'] ?? null,
                    $s['district'] ?? null, $s['province_name'] ?? null, $s['parent_name'] ?? null, $s['parent_last_name'] ?? null,
                    $s['parent_occupation'] ?? null, $s['parent_relationship'] ?? null, $s['father_name'] ?? null, $s['father_last_name'] ?? null,
                    $s['father_occupation'] ?? null, $s['mother_name'] ?? null, $s['mother_last_name'] ?? null, $s['mother_occupation'] ?? null, $s['disadvantage'] ?? null
                ]);
                $profile_id = $pdo->lastInsertId();
            } else {
                $profile_id = $profile['id'];
            }
            
            // อัปเดต student_profile_id กลับไปที่ตาราง students (enrollment)
            $upd = $pdo->prepare("UPDATE students SET student_profile_id = ? WHERE id = ?");
            $upd->execute([$profile_id, $s['id']]);
            $migrated_count++;
        }
        $pdo->commit();
        $results[] = "ทำ Migration ข้อมูลนักเรียนไปยังระบบโปรไฟล์หลักสำเร็จ ($migrated_count รายการ)";
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'ตรวจสอบและปรับปรุงฐานข้อมูลเรียบร้อยแล้ว',
        'details' => $results
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการปรับปรุงฐานข้อมูล: ' . $e->getMessage(),
        'details' => $results
    ]);
}
?>
