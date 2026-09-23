<?php
/**
 * API to fetch all relevant data for a parent
 */
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['parent_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];

// รับพารามิเตอร์ตัวกรอง
$academic_year = !empty($_GET['academic_year']) ? $_GET['academic_year'] : null;
$semester = !empty($_GET['semester']) ? $_GET['semester'] : null;

try {
    // 0. Fetch Available Academic Years
    $stmt = $pdo->prepare("SELECT DISTINCT academic_year FROM grades WHERE student_id = ? ORDER BY academic_year DESC");
    $stmt->execute([$student_id]);
    $available_years = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // If no year/semester provided, use the most recent ones
    if (!$academic_year && !empty($available_years)) {
        $academic_year = $available_years[0];
        
        $stmt = $pdo->prepare("SELECT MAX(semester) FROM grades WHERE student_id = ? AND academic_year = ?");
        $stmt->execute([$student_id, $academic_year]);
        $semester = $stmt->fetchColumn();
    }

    // 1. Student Info
    $stmt = $pdo->prepare("SELECT s.*, sch.name as school_name, sch.logo_url as school_logo_url, sch.show_grades as school_show_grades, CONCAT(cl.level, '/', cl.room) as classroom_name 
                           FROM students s 
                           JOIN schools sch ON s.school_id = sch.id 
                           LEFT JOIN classrooms cl ON s.classroom_id = cl.id
                           WHERE s.id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    // 2. Attendance Summary
    $att_sql = "SELECT status, COUNT(*) as count FROM attendance WHERE student_id = ?";
    $att_params = [$student_id];
    
    // Note: Attendance might not be strictly linked to semester in schema, 
    // but we can filter by date if needed. For now summary is global or we could add academic_year to attendance later.
    // However, for dashboard we'll keep it total unless specific dates are needed.
    
    $stmt = $pdo->prepare("$att_sql GROUP BY status");
    $stmt->execute($att_params);
    $attendance = $stmt->fetchAll();

    // 3. Grades
    // Fallback if no academic year/semester found in grades yet
    if (!$academic_year && $student && isset($student['academic_year'])) {
        $academic_year = $student['academic_year'];
    }
    // Fallback if still no year (completely new student)
    if (!$academic_year) {
        $academic_year = '2568'; 
    }
    if (!$semester) {
        $semester = '1';
    }

    // Use a subquery to get the latest grade ID for each subject to avoid duplicates and ONLY_FULL_GROUP_BY issues
    $sub_where = "student_id = ?";
    $sub_params = [$student_id];
    if ($academic_year) {
        $sub_where .= " AND academic_year = ?";
        $sub_params[] = $academic_year;
    }
    if ($semester && $semester !== 'annual') {
        $sub_where .= " AND semester = ?";
        $sub_params[] = $semester;
    }

    $grade_sql = "SELECT g.*, sub.name as subject_name, sub.code as subject_code, sub.credits 
                  FROM grades g 
                  JOIN subjects sub ON g.subject_id = sub.id 
                  WHERE g.id IN (
                      SELECT MAX(id) FROM grades WHERE $sub_where GROUP BY subject_id
                  )
                  ORDER BY sub.code ASC";
    
    $stmt = $pdo->prepare($grade_sql);
    $stmt->execute($sub_params);
    $grades = $stmt->fetchAll();

    // 4. Latest Behavior
    $behavior = null;
    if ($academic_year) {
        $bh_sql = "SELECT * FROM evaluation_scores WHERE student_id = ? AND category = 'characteristics' AND academic_year = ?";
        $bh_params = [$student_id, $academic_year];
        if ($semester && $semester !== 'annual') {
            $bh_sql .= " AND semester = ?";
            $bh_params[] = $semester;
        }
        $bh_sql .= " ORDER BY semester DESC LIMIT 1";
        $stmt = $pdo->prepare($bh_sql);
        $stmt->execute($bh_params);
        $behavior = $stmt->fetch();
    }

    // 5. Parent Feedback
    $parent_feedback = null;
    if ($academic_year) {
        $fb_sql = "SELECT * FROM parent_feedback WHERE student_id = ? AND academic_year = ?";
        $fb_params = [$student_id, $academic_year];
        if ($semester && $semester !== 'annual') {
            $fb_sql .= " AND semester = ?";
            $fb_params[] = $semester;
        }
        $fb_sql .= " ORDER BY semester DESC LIMIT 1";
        $stmt = $pdo->prepare($fb_sql);
        $stmt->execute($fb_params);
        $parent_feedback = $stmt->fetch();
    }
    
    // 6. Student Health History
    $stmt = $pdo->prepare("SELECT weight, height, academic_year, semester, record_number, created_at 
                           FROM student_health_records 
                           WHERE student_id = ? 
                           ORDER BY academic_year ASC, semester ASC, record_number ASC");
    $stmt->execute([$student_id]);
    $health_history = $stmt->fetchAll();

    // 7. Get System Current Year for visibility logic
    $stmt = $pdo->prepare("SELECT year FROM academic_years WHERE school_id = ? AND is_current = 1 LIMIT 1");
    $stmt->execute([$school_id]);
    $sys_year = $stmt->fetchColumn();
    
    // Fallback logic if no current year is set
    if (!$sys_year) {
        $stmt = $pdo->prepare("SELECT MAX(year) FROM academic_years WHERE school_id = ?");
        $stmt->execute([$school_id]);
        $sys_year = $stmt->fetchColumn();
        
        if (!$sys_year) {
            $sys_year = '2568'; // Global fallback
        }
    }

    // Calculate if grades should be hidden based on admin setting
    // Hidden if: school.show_grades is 0 AND (requested year is the system current year OR no year requested)
    $show_grades_setting = (isset($student['school_show_grades']) && $student['school_show_grades'] == 1);
    $is_grades_hidden = false;
    
    if (!$show_grades_setting) {
        // We only hide if it's the current year. 
        // Use trim to ensure comparison doesn't fail due to whitespace in DB
        $trimmed_academic_year = $academic_year ? trim($academic_year) : null;
        $trimmed_sys_year = $sys_year ? trim($sys_year) : null;
        
        // If no year requested, we assume current year (and hide)
        // If year is requested, we hide only if it matches sys_year
        if (!$trimmed_academic_year || $trimmed_academic_year == $trimmed_sys_year) {
            $is_grades_hidden = true;
        }
    }

    echo json_encode([
        'student' => $student,
        'attendance' => $attendance,
        'grades' => $is_grades_hidden ? [] : $grades, // SECURITY: Don't even send grades if hidden
        'behavior' => $behavior,
        'parent_feedback' => $parent_feedback,
        'health_history' => $health_history,
        'is_grades_hidden' => $is_grades_hidden, // Explicit flag
        'filters' => [
            'available_years' => $available_years,
            'current_year' => $academic_year,
            'current_semester' => $semester,
            'system_current_year' => $sys_year
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
