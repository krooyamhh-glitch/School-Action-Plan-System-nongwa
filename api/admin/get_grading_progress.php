<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$role = $_SESSION['role'] ?? '';
$is_academic = $_SESSION['is_academic'] ?? false;
$position = $_SESSION['position'] ?? '';
$school_id = $_SESSION['school_id'] ?? null;

// Only admin, academic staff, or director can access
$is_director = (strpos($position, 'ผู้อำนวยการ') !== false);
if ($role !== 'admin' && $role !== 'super_admin' && !$is_academic && !$is_director) {
    die(json_encode(['error' => 'Permission denied']));
}

$academic_year = $_GET['academic_year'] ?? '2567';
$semester = $_GET['semester'] ?? '1';
$level = $_GET['level'] ?? '';
$action = $_GET['action'] ?? '';

try {
    if ($action === 'get_years') {
        $stmt = $pdo->prepare("SELECT year, is_current FROM academic_years WHERE school_id = ? ORDER BY year DESC");
        $stmt->execute([$school_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    $sql = "SELECT 
                ta.id as assignment_id,
                sub.name AS subject_name,
                sub.code AS subject_code,
                sub.level AS subject_level,
                c.room,
                c.id as classroom_id,
                u.name AS teacher_name,
                u.last_name AS teacher_last_name,
                /* Count total students in this classroom for the specific academic year */
                (SELECT COUNT(*) FROM students s WHERE (s.classroom_id = ta.classroom_id OR (c.id IS NOT NULL AND s.level = c.level AND (s.room = c.room OR REPLACE(s.room, 'ห้อง', '') = REPLACE(c.room, 'ห้อง', '')))) AND s.school_id = u.school_id AND s.academic_year = ta.academic_year AND (s.status = 'studying' OR s.status IS NULL OR s.status = '')) as student_count,
                
                /* Learning Units Progress */
                (SELECT COUNT(*) FROM learning_units lu WHERE lu.subject_id = ta.subject_id AND lu.classroom_id = ta.classroom_id AND lu.academic_year = ta.academic_year AND lu.semester = ta.semester) as total_units,
                (SELECT COUNT(DISTINCT us.learning_unit_id) FROM unit_scores us JOIN learning_units lu ON us.learning_unit_id = lu.id WHERE lu.subject_id = ta.subject_id AND lu.classroom_id = ta.classroom_id AND lu.academic_year = ta.academic_year AND lu.semester = ta.semester) as completed_units,
                
                /* Final Progress (Check if any scores were entered per student) */
                (SELECT COUNT(*) FROM grades g WHERE g.subject_id = ta.subject_id AND g.classroom_id = ta.classroom_id AND g.academic_year = ta.academic_year AND g.semester = ta.semester AND g.score_final > 0) as final_count,
                
                /* Characteristics & Analytical Progress */
                (SELECT COUNT(*) FROM characteristics_scores cs WHERE cs.subject_id = ta.subject_id AND cs.classroom_id = ta.classroom_id AND cs.academic_year = ta.academic_year AND cs.semester = ta.semester) as characteristics_count,
                (SELECT COUNT(*) FROM analytical_scores ascr WHERE ascr.subject_id = ta.subject_id AND ascr.classroom_id = ta.classroom_id AND ascr.academic_year = ta.academic_year AND ascr.semester = ta.semester) as analytical_count,
                
                /* Competency & Learner Dev (Classroom level) */
                (SELECT COUNT(*) FROM competency_scores cmps WHERE cmps.classroom_id = ta.classroom_id AND cmps.academic_year = ta.academic_year AND cmps.semester = ta.semester) as competency_count,
                (SELECT COUNT(*) FROM learner_development_results ldr WHERE ldr.classroom_id = ta.classroom_id AND ldr.academic_year = ta.academic_year AND ldr.semester = ta.semester) as learner_dev_count
                
            FROM teacher_assignments ta
            JOIN subjects sub ON ta.subject_id = sub.id
            JOIN users u ON ta.teacher_id = u.id
            LEFT JOIN classrooms c ON ta.classroom_id = c.id
            WHERE ta.academic_year = ? AND ta.semester = ? AND u.school_id = ?";
    
    $params = [$academic_year, $semester, $school_id];
    
    if ($level) {
        $sql .= " AND sub.level = ?";
        $params[] = $level;
    }
    
    $sql .= " ORDER BY sub.level ASC, c.room ASC, sub.name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
