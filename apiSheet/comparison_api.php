<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set time zone
date_default_timezone_set('UTC');

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

require_once 'connect.php';

try {
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit(0);
    }

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $action = isset($_GET['action']) ? $_GET['action'] : '';

    if ($action === 'get_users') {
        $query = "SELECT id, f_name, l_name FROM users WHERE is_active = 1";
        $result = $conn->query($query);
        
        if ($result === false) {
            throw new Exception('Query failed: ' . $conn->error);
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    } elseif ($action === 'get_total_tasks') {
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

        if (!$type || !$id || !$start_date || !$end_date) {
            throw new Exception('Missing required parameters');
        }

        // Validate dates
        if (!DateTime::createFromFormat('Y-m-d', $start_date)) {
            throw new Exception('Invalid start date format. Use YYYY-MM-DD');
        }
        if (!DateTime::createFromFormat('Y-m-d', $end_date)) {
            throw new Exception('Invalid end date format. Use YYYY-MM-DD');
        }
        if (strtotime($start_date) > strtotime($end_date)) {
            throw new Exception('Start date cannot be after end date');
        }

        $metrics = calculateTaskMetrics($conn, $type, $id, $start_date, $end_date);

        echo json_encode([
            'success' => true,
            'data' => [
                'total_tasks' => $metrics['total_tasks'],
                'total_completed_tasks' => $metrics['total_completed_tasks'],
                'task_completion_efficiency' => $metrics['task_completion_efficiency'],
                'avg_completion_time' => $metrics['avg_completion_time'],
                'projects_completed' => $metrics['projects_completed'],
                'productivity_rate' => $metrics['productivity_rate'],
                'overall_growth' => $metrics['overall_growth']
            ],
            'message' => 'Metrics retrieved successfully'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Basic API endpoint'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();

function calculateTaskMetrics($conn, $type, $id, $start_date, $end_date) {
    // Build date conditions
    $date_conditions = "AND p.approved_date BETWEEN ? AND ?";
    $params = [$id, $start_date, $end_date];
    $types = "iss";

    // Build filter condition based on type
    $filter_condition = ($type === 'individual') ? "t.assigned_to = ?" : "p.dept_id = ?";

    // Query to get total assigned tasks
    $sql_total = "
        SELECT COUNT(*) as total_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE $filter_condition
        $date_conditions
    ";
    
    $stmt_total = $conn->prepare($sql_total);
    if (!$stmt_total) {
        throw new Exception('Prepare failed for total tasks: ' . $conn->error);
    }
    
    $stmt_total->bind_param($types, ...$params);
    $stmt_total->execute();
    $total_result = $stmt_total->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_tasks = $total_row['total_tasks'] ?? 0;
    $stmt_total->close();
    
    // Query to get efficiently completed tasks (weekday-based)
    $sql_efficient = "
        SELECT COUNT(*) as efficient_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE $filter_condition
        AND t.status = 61
        AND (
            SELECT (
                DATEDIFF(t.updated_at, p.approved_date) - 
                (2 * FLOOR(DATEDIFF(t.updated_at, p.approved_date) / 7) + 
                 LEAST(DATEDIFF(t.updated_at, p.approved_date) % 7, 
                       1 + (DAYOFWEEK(p.approved_date) + (DATEDIFF(t.updated_at, p.approved_date) % 7) - 1) % 7 IN (1, 7))
                )
            ) <= (
                DATEDIFF(t.end_date, p.approved_date) - 
                (2 * FLOOR(DATEDIFF(t.end_date, p.approved_date) / 7) + 
                 LEAST(DATEDIFF(t.end_date, p.approved_date) % 7, 
                       1 + (DAYOFWEEK(p.approved_date) + (DATEDIFF(t.end_date, p.approved_date) % 7) - 1) % 7 IN (1, 7))
                )
            )
        )
        $date_conditions
    ";
    
    $stmt_efficient = $conn->prepare($sql_efficient);
    if (!$stmt_efficient) {
        throw new Exception('Prepare failed for efficient tasks: ' . $conn->error);
    }
    
    $stmt_efficient->bind_param($types, ...$params);
    $stmt_efficient->execute();
    $efficient_result = $stmt_efficient->get_result();
    $efficient_row = $efficient_result->fetch_assoc();
    $efficient_tasks = $efficient_row['efficient_tasks'] ?? 0;
    $stmt_efficient->close();

    // Query to get all completed tasks
    $sql_completed = "
        SELECT COUNT(*) as completed_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE $filter_condition
        AND t.status = 61
        AND t.updated_at BETWEEN ? AND ?
        $date_conditions
    ";
    
    $stmt_completed = $conn->prepare($sql_completed);
    if (!$stmt_completed) {
        throw new Exception('Prepare failed for completed tasks: ' . $conn->error);
    }
    
    $stmt_completed->bind_param($types . 'ss', ...array_merge($params, [$start_date, $end_date]));
    $stmt_completed->execute();
    $completed_result = $stmt_completed->get_result();
    $completed_row = $completed_result->fetch_assoc();
    $total_completed_tasks = $completed_row['completed_tasks'] ?? 0;
    $stmt_completed->close();

    // Query to get average task completion time (weekday-based, in hours)
    $sql_avg_time = "
        SELECT AVG(
            (DATEDIFF(t.updated_at, p.approved_date) - 
             (2 * FLOOR(DATEDIFF(t.updated_at, p.approved_date) / 7) + 
              LEAST(DATEDIFF(t.updated_at, p.approved_date) % 7, 
                    1 + (DAYOFWEEK(p.approved_date) + (DATEDIFF(t.updated_at, p.approved_date) % 7) - 1) % 7 IN (1, 7))
             )
            ) * 8
        ) as avg_completion_time
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE $filter_condition
        AND t.status = 61
        AND t.updated_at >= p.approved_date
        AND t.updated_at BETWEEN ? AND ?
        $date_conditions
    ";
    
    $stmt_avg_time = $conn->prepare($sql_avg_time);
    if (!$stmt_avg_time) {
        throw new Exception('Prepare failed for avg completion time: ' . $conn->error);
    }
    
    $stmt_avg_time->bind_param($types . 'ss', ...array_merge($params, [$start_date, $end_date]));
    $stmt_avg_time->execute();
    $avg_time_result = $stmt_avg_time->get_result();
    $avg_time_row = $avg_time_result->fetch_assoc();
    $avg_completion_time = $avg_time_row['avg_completion_time'] !== null ? round($avg_time_row['avg_completion_time'], 2) : 0;
    $stmt_avg_time->close();

    // Query to get number of completed projects
    $sql_projects_completed = "
        SELECT COUNT(DISTINCT p.project_id) as projects_completed
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE $filter_condition
        AND p.completed_date IS NOT NULL
        $date_conditions
    ";
    
    $stmt_projects_completed = $conn->prepare($sql_projects_completed);
    if (!$stmt_projects_completed) {
        throw new Exception('Prepare failed for projects completed: ' . $conn->error);
    }
    
    $stmt_projects_completed->bind_param($types, ...$params);
    $stmt_projects_completed->execute();
    $projects_completed_result = $stmt_projects_completed->get_result();
    $projects_completed_row = $projects_completed_result->fetch_assoc();
    $projects_completed = $projects_completed_row['projects_completed'] ?? 0;
    $stmt_projects_completed->close();

    // Query to get total distinct projects
    $sql_total_projects = "
        SELECT COUNT(DISTINCT p.project_id) as total_projects
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE $filter_condition
        $date_conditions
    ";
    
    $stmt_total_projects = $conn->prepare($sql_total_projects);
    if (!$stmt_total_projects) {
        throw new Exception('Prepare failed for total projects: ' . $conn->error);
    }
    
    $stmt_total_projects->bind_param($types, ...$params);
    $stmt_total_projects->execute();
    $total_projects_result = $stmt_total_projects->get_result();
    $total_projects_row = $total_projects_result->fetch_assoc();
    $total_projects = $total_projects_row['total_projects'] ?? 0;
    $stmt_total_projects->close();

    // Calculate efficiency and completion rate
    $task_completion_efficiency = $total_tasks > 0 ? round(($efficient_tasks / $total_tasks) * 100, 2) : 0;
    $completion_rate = $total_tasks > 0 ? round(($total_completed_tasks / $total_tasks) * 100, 2) : 0;

    // Calculate productivity rate
    $productivity_rate = round($task_completion_efficiency * $completion_rate / 100, 2);

    // Calculate overall growth
    $normalized_projects_completed = $total_projects > 0 ? round(($projects_completed / $total_projects) * 100, 2) : 0;
    $overall_growth = round((0.4 * $task_completion_efficiency) + (0.4 * $completion_rate) + (0.2 * $normalized_projects_completed), 2);

    return [
        'total_tasks' => $total_tasks,
        'total_completed_tasks' => $total_completed_tasks,
        'task_completion_efficiency' => $task_completion_efficiency,
        'avg_completion_time' => $avg_completion_time,
        'projects_completed' => $projects_completed,
        'productivity_rate' => $productivity_rate,
        'overall_growth' => $overall_growth
    ];
}
?>