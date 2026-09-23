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

$assignment_id = $_GET['assignment_id'] ?? '';

if (!$assignment_id) {
    die(json_encode(['error' => 'Assignment ID is required']));
}

try {
    // 1. Get assignment details
    $stmt = $pdo->prepare("
        SELECT ta.*, sub.name as subject_name, sub.code as subject_code, c.level, c.room, u.name as teacher_name, u.last_name as teacher_last_name
        FROM teacher_assignments ta
        JOIN subjects sub ON ta.subject_id = sub.id
        JOIN classrooms c ON ta.classroom_id = c.id
        JOIN users u ON ta.teacher_id = u.id
        WHERE ta.id = ? AND u.school_id = ?
    ");
    $stmt->execute([$assignment_id, $school_id]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        die(json_encode(['error' => 'Assignment not found']));
    }

    // 2. Get learning units
    $stmt = $pdo->prepare("
        SELECT id, unit_name, max_score 
        FROM learning_units 
        WHERE subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester = ?
        ORDER BY id ASC
    ");
    $stmt->execute([
        $assignment['subject_id'], 
        $assignment['classroom_id'], 
        $assignment['academic_year'], 
        $assignment['semester']
    ]);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Get students who were in this assignment's context
    // Scope strictly to students in this specific classroom (level & room) for this academic year
    $clean_room = str_replace('ห้อง', '', $assignment['room'] ?? '');

    $students_sql = "
        SELECT DISTINCT s.id, s.prefix, s.name, s.last_name, s.student_code
        FROM students s
        LEFT JOIN grades g ON s.id = g.student_id AND g.subject_id = ? AND g.classroom_id = ? AND g.academic_year = ? AND g.semester = ?
        LEFT JOIN learning_units lu ON lu.subject_id = ? AND lu.classroom_id = ? AND lu.academic_year = ? AND lu.semester = ?
        LEFT JOIN unit_scores us ON s.id = us.student_id AND us.learning_unit_id = lu.id
        WHERE s.school_id = ?
        AND (
            (
                (s.classroom_id = ? OR (s.level = ? AND (s.room = ? OR s.room = ? OR REPLACE(s.room, 'ห้อง', '') = ?)))
                AND s.academic_year = ? 
                AND (s.status = 'studying' OR s.status IS NULL OR s.status = '')
            )
            OR (g.id IS NOT NULL)
            OR (us.id IS NOT NULL)
        )
    ";
    
    $params = [
        $assignment['subject_id'], $assignment['classroom_id'], $assignment['academic_year'], $assignment['semester'],
        $assignment['subject_id'], $assignment['classroom_id'], $assignment['academic_year'], $assignment['semester'],
        $school_id,
        $assignment['classroom_id'], $assignment['level'], $assignment['room'], $clean_room, $clean_room,
        $assignment['academic_year']
    ];

    $stmt = $pdo->prepare($students_sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // เรียงลำดับนักเรียนตามรหัสประจำตัว
    usort($students, function($a, $b) {
        return strnatcmp($a['student_code'] ?? '', $b['student_code'] ?? '');
    });

    // 4. Get all unit scores for these students
    // We can fetch them all at once
    $unit_scores = [];
    if (count($units) > 0) {
        $unit_ids = array_map(fn($u) => $u['id'], $units);
        $placeholders = implode(',', array_fill(0, count($unit_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT student_id, learning_unit_id, score 
            FROM unit_scores 
            WHERE learning_unit_id IN ($placeholders)
        ");
        $stmt->execute($unit_ids);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $unit_scores[$row['student_id']][$row['learning_unit_id']] = $row['score'];
        }
    }

    // 5. Get final grades/scores
    $grades = [];
    $stmt = $pdo->prepare("
        SELECT student_id, score_final, score_total, grade
        FROM grades
        WHERE subject_id = ? AND classroom_id = ? AND academic_year = ? AND semester = ?
    ");
    $stmt->execute([
        $assignment['subject_id'], 
        $assignment['classroom_id'], 
        $assignment['academic_year'], 
        $assignment['semester']
    ]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $grades[$row['student_id']] = $row;
    }

    // 6. Build the result
    $student_data = [];
    foreach ($students as $s) {
        $scores = [];
        $calculated_units_total = 0;
        $has_all_unit_scores = (count($units) > 0);
        
        foreach ($units as $u) {
            $unit_score = $unit_scores[$s['id']][$u['id']] ?? null;
            $scores[$u['id']] = $unit_score;
            if ($unit_score === null) {
                $has_all_unit_scores = false;
            } else {
                $calculated_units_total += floatval($unit_score);
            }
        }

        $stored_final = $grades[$s['id']]['score_final'] ?? null;
        $calculated_total = $calculated_units_total + floatval($stored_final ?: 0);
        
        // Simple grade calculation if not stored
        $calculated_grade = null;
        if ($stored_final !== null && $has_all_unit_scores) {
            $t = $calculated_total;
            if ($t >= 80) $calculated_grade = '4';
            else if ($t >= 75) $calculated_grade = '3.5';
            else if ($t >= 70) $calculated_grade = '3';
            else if ($t >= 65) $calculated_grade = '2.5';
            else if ($t >= 60) $calculated_grade = '2';
            else if ($t >= 55) $calculated_grade = '1.5';
            else if ($t >= 50) $calculated_grade = '1';
            else $calculated_grade = '0';
        }

        $student_data[] = [
            'id' => $s['id'],
            'student_code' => $s['student_code'],
            'full_name' => ($s['prefix'] ?: '') . $s['name'] . ' ' . ($s['last_name'] ?: ''),
            'unit_scores' => $scores,
            'final_score' => $stored_final,
            'total_score' => $grades[$s['id']]['score_total'] ?? $calculated_total,
            'grade' => $grades[$s['id']]['grade'] ?? $calculated_grade,
            'calculated_total' => $calculated_total,
            'calculated_grade' => $calculated_grade,
            'is_fully_graded' => ($stored_final !== null && $has_all_unit_scores)
        ];
    }

    echo json_encode([
        'assignment' => $assignment,
        'units' => $units,
        'students' => $student_data
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
