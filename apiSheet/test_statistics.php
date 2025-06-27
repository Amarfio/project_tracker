<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('UTC');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'connect.php';

// HARDCODED TEST VALUES
$test_department = 'Credits';
$test_start_date = '2025-01-01';
$test_end_date = '2025-06-19';

try {
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // 1. Get the correct department ID
    $dept_stmt = $conn->prepare("SELECT id FROM code_desc WHERE init = 'dpt' AND `desc` = ?");
    $dept_stmt->bind_param('s', $test_department);
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();
    
    if ($dept_result->num_rows === 0) {
        throw new Exception("Department not found");
    }
    
    $dept_row = $dept_result->fetch_assoc();
    $dept_id = $dept_row['id'];
    $dept_stmt->close();

    // 2. CORRECTED New Tasks Query (using UNION instead of UNION ALL to avoid duplicates)
    $sql = "SELECT COUNT(DISTINCT t.task_id) as count
            FROM tasks t
            JOIN projects p ON t.project_id = p.project_id
            LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
            LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
            WHERE (t.department = ? OR (t.department IS NULL AND p.dept_id = ?))
            AND p.approved_date BETWEEN ? AND ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('iiss', $dept_id, $dept_id, $test_start_date, $test_end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();

    // 3. Get the actual tasks for verification
    $debug_sql = "SELECT t.task_id, p.approved_date, t.created_at, 
                  CASE WHEN t.department IS NOT NULL THEN 'Direct' ELSE 'Project' END as assignment_type
                  FROM tasks t
                  JOIN projects p ON t.project_id = p.project_id
                  LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
                  LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
                  WHERE (t.department = ? OR (t.department IS NULL AND p.dept_id = ?))
                  AND p.approved_date BETWEEN ? AND ?
                  ORDER BY p.approved_date";

    $debug_stmt = $conn->prepare($debug_sql);
    $debug_stmt->bind_param('iiss', $dept_id, $dept_id, $test_start_date, $test_end_date);
    $debug_stmt->execute();
    $debug_result = $debug_stmt->get_result();
    
    $tasks = [];
    while ($row = $debug_result->fetch_assoc()) {
        $tasks[] = $row;
    }
    $debug_stmt->close();

    // Response
    echo json_encode([
        'success' => true,
        'department' => $test_department,
        'department_id' => $dept_id,
        'start_date' => $test_start_date,
        'end_date' => $test_end_date,
        'new_tasks_count' => $count,
        'tasks_counted' => $tasks,
        'tasks_count' => count($tasks),
        'query_used' => $sql
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>