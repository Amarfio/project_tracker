<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'connect.php';

function getWorkingDays($startDate, $endDate) {
    if (!$startDate || !$endDate) return null;
    
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $end->modify('+1 day');
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);
    
    $workingDays = 0;
    foreach ($period as $date) {
        if ($date->format('N') < 6) $workingDays++;
    }
    return $workingDays;
}

function formatDate($dateString) {
    if (!$dateString) return null;
    return date('d-M-Y', strtotime($dateString));
}

try {
    // Validate parameters
    $department = $_GET['department'] ?? null;
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $metric = $_GET['metric'] ?? null;

    if (!$department || !$start_date || !$end_date || !$metric) {
        throw new Exception('All parameters are required');
    }

    // Validate dates
    if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }

    if (strtotime($start_date) > strtotime($end_date)) {
        throw new Exception('Start date cannot be after end date');
    }

    // Get department ID
    $stmt_dept = $conn->prepare("SELECT id FROM code_desc WHERE init = 'dpt' AND `desc` = ?");
    if (!$stmt_dept) throw new Exception('Prepare failed: ' . $conn->error);
    
    $stmt_dept->bind_param('s', $department);
    $stmt_dept->execute();
    $dept_id = $stmt_dept->get_result()->fetch_assoc()['id'] ?? null;
    $stmt_dept->close();
    
    if (!$dept_id) throw new Exception('Department not found');

    // Build query conditions
    switch ($metric) {
        case 'outstanding_tasks':
            $conditions = "t.status NOT IN (60, 61) AND p.approved_date IS NOT NULL AND p.approved_date BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
            break;
        case 'new_tasks':
            $conditions = "p.approved_date IS NOT NULL AND p.approved_date BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
            break;
        case 'total_completed':
            $conditions = "t.status = 61 AND t.updated_at BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
            break;
        case 'oldest_outstanding':
            $conditions = "t.status NOT IN (60, 61) AND p.approved_date IS NOT NULL AND p.approved_date BETWEEN ? AND ? AND (t.updated_at IS NULL OR t.updated_at > ?)";
            $params = [$start_date, $end_date, $end_date];
            break;
        case 'tasks_exceeded':
            $conditions = "t.end_date IS NOT NULL AND p.approved_date IS NOT NULL AND p.approved_date BETWEEN ? AND ? AND ((t.status NOT IN (60, 61) AND t.end_date < CURRENT_DATE) OR (t.status = 61 AND t.updated_at > t.end_date))";
            $params = [$start_date, $end_date];
            break;
        case 'avg_days_to_complete':
            $conditions = "t.status = 61 AND t.updated_at IS NOT NULL AND p.approved_date IS NOT NULL AND p.approved_date <= t.updated_at AND t.updated_at BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
            break;
        default:
            throw new Exception('Invalid metric');
    }

    // Main query using consistent LEFT JOIN approach
    $sql = "
        SELECT 
            t.task_id, 
            p.approved_date,
            COALESCE(t.department, p.dept_id) as department,
            COALESCE(cd.`desc`, pcd.`desc`) as dept_description,
            t.description as summary,
            t.status,
            t.assigned_to,
            t.updated_at as last_updated,
            CASE 
                WHEN t.status = 58 THEN 'Not Started'
                WHEN t.status = 59 THEN 'In Progress'
                WHEN t.status = 60 THEN 'Suspended'
                WHEN t.status = 61 THEN 'Completed'
                ELSE 'Unknown'
            END as status_desc,
            CASE WHEN t.status = 61 THEN t.updated_at END as completed_date,
            t.end_date as due_date
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
        LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
        WHERE (t.department = ? OR (t.department IS NULL AND p.dept_id = ?))
        AND ($conditions)
        ORDER BY 
            CASE WHEN ? = 'oldest_outstanding' THEN p.approved_date END ASC,
            CASE WHEN ? != 'oldest_outstanding' THEN p.approved_date END DESC
    " . ($metric == 'oldest_outstanding' ? " LIMIT 1" : "");

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    
    // Add metric type twice for the ORDER BY clause
    $full_params = array_merge([$dept_id, $dept_id], $params, [$metric, $metric]);
    $types = 'ii' . str_repeat('s', count($params)) . 'ss';
    $stmt->bind_param($types, ...$full_params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Process results
    $tasks = [];
    $userIds = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['assigned_to']) $userIds[] = $row['assigned_to'];
        $tasks[] = $row;
    }

    // Get user names
    $users = [];
    if (!empty(array_unique($userIds))) {
        $placeholders = implode(',', array_fill(0, count(array_unique($userIds)), '?'));
        $stmt_users = $conn->prepare("SELECT id, CONCAT(f_name, ' ', l_name) as full_name FROM users WHERE id IN ($placeholders)");
        if (!$stmt_users) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt_users->bind_param(str_repeat('i', count(array_unique($userIds))), ...array_unique($userIds));
        $stmt_users->execute();
        foreach ($stmt_users->get_result() as $user) {
            $users[$user['id']] = $user['full_name'];
        }
        $stmt_users->close();
    }

    // Format final output with date formatting
    $formatted_tasks = [];
    foreach ($tasks as $task) {
        $formatted_tasks[] = [
            'task_id' => $task['task_id'],
            'approved_date' => formatDate($task['approved_date']),
            'department' => $task['department'],
            'dept_description' => $task['dept_description'],
            'summary' => $task['summary'],
            'owner' => $task['assigned_to'] ? ($users[$task['assigned_to']] ?? 'Unknown') : null,
            'last_updated' => formatDate($task['last_updated']),
            'status' => $task['status'],
            'status_desc' => $task['status_desc'],
            'completed_date' => $task['completed_date'] ? formatDate($task['completed_date']) : null,
            'due_date' => $task['due_date'] ? formatDate($task['due_date']) : null,
            'days_to_complete' => ($task['status'] == 61 && $task['approved_date'] && $task['completed_date']) 
                ? getWorkingDays($task['approved_date'], $task['completed_date']) 
                : null,
            'days_outstanding' => ($metric == 'oldest_outstanding' && $task['status'] != 61 && $task['approved_date']) 
                ? getWorkingDays($task['approved_date'], $end_date) 
                : null,
            'days_overdue' => ($task['status'] != 61 && $task['due_date'] && $task['due_date'] < $end_date)
                ? getWorkingDays($task['due_date'], $end_date)
                : null
        ];
    }

    echo json_encode([
        'success' => true,
        'tasks' => $formatted_tasks,
        'metric' => $metric,
        'params' => [
            'department' => $department,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

$conn->close();
?>