<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'connect.php';

// Log function for debugging
function debug_log($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, 'api_debug.log');
}

try {
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $user_id = isset($_GET['id']) ? $_GET['id'] : null;
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    debug_log("Request received: user_id=$user_id, start_date=$start_date, end_date=$end_date");

    if (!$user_id) {
        throw new Exception('User ID is required');
    }

    // Validate user_id
    if (!is_numeric($user_id)) {
        throw new Exception('Invalid user ID');
    }

    // Validate dates
    if ($start_date && !DateTime::createFromFormat('Y-m-d', $start_date)) {
        throw new Exception('Invalid start date format. Use YYYY-MM-DD');
    }
    if ($end_date && !DateTime::createFromFormat('Y-m-d', $end_date)) {
        throw new Exception('Invalid end date format. Use YYYY-MM-DD');
    }
    if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
        throw new Exception('Start date cannot be after end date');
    }

    // Query user name
    $sql = "SELECT CONCAT(f_name, ' ', l_name) AS user_name 
            FROM users 
            WHERE id = ? AND is_active = 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User not found or inactive');
    }

    $row = $result->fetch_assoc();
    $user_name = $row['user_name'] ?: 'Unknown User';
    
    // Calculate metrics
    $metrics = calculateTaskMetrics($conn, $user_id, $start_date, $end_date);
    
    $response = [
        'success' => true,
        'user_name' => $user_name,
        'task_completion_efficiency' => $metrics['task_completion_efficiency'],
        'completed_tasks' => $metrics['completed_tasks'],
        'total_tasks' => $metrics['total_tasks'],
        'completion_rate' => $metrics['completion_rate'],
        'avg_completion_time' => $metrics['avg_completion_time']
    ];

    debug_log("User data fetched: user_name=$user_name, efficiency={$metrics['task_completion_efficiency']}, completed_tasks={$metrics['completed_tasks']}, total_tasks={$metrics['total_tasks']}, completion_rate={$metrics['completion_rate']}, avg_completion_time={$metrics['avg_completion_time']} hours");

    echo json_encode($response);

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    $error_message = 'Error: ' . $e->getMessage();
    echo json_encode([
        'success' => false,
        'message' => $error_message
    ]);
    debug_log($error_message);
}

function calculateTaskMetrics($conn, $user_id, $start_date, $end_date) {
    // Build date conditions
    $date_conditions = "";
    $params = [$user_id];
    $types = "i";
    
    if ($start_date && $end_date) {
        $date_conditions = "AND p.approved_date BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    } elseif ($start_date) {
        $date_conditions = "AND p.approved_date >= ?";
        $params[] = $start_date;
        $types .= "s";
    } elseif ($end_date) {
        $date_conditions = "AND p.approved_date <= ?";
        $params[] = $end_date;
        $types .= "s";
    }

    // Query to get total assigned tasks
    $sql_total = "
        SELECT COUNT(*) as total_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE t.assigned_to = ?
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
    
    // Query to get efficiently completed tasks (completed on or before end_date)
    $sql_efficient = "
        SELECT COUNT(*) as efficient_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE t.assigned_to = ?
        AND t.status = 61 /* Completed */
        AND t.updated_at <= t.end_date /* Completed on or before deadline */
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

    // Query to get all completed tasks (regardless of deadline)
    $sql_completed = "
        SELECT COUNT(*) as completed_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE t.assigned_to = ?
        AND t.status = 61 /* Completed */
        $date_conditions
    ";
    
    $stmt_completed = $conn->prepare($sql_completed);
    if (!$stmt_completed) {
        throw new Exception('Prepare failed for completed tasks: ' . $conn->error);
    }
    
    $stmt_completed->bind_param($types, ...$params);
    $stmt_completed->execute();
    $completed_result = $stmt_completed->get_result();
    $completed_row = $completed_result->fetch_assoc();
    $completed_tasks = $completed_row['completed_tasks'] ?? 0;
    $stmt_completed->close();

    // Query to get average task completion time in hours
    $sql_avg_time = "
        SELECT AVG(TIMESTAMPDIFF(HOUR, p.approved_date, t.updated_at)) as avg_completion_time
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE t.assigned_to = ?
        AND t.status = 61 /* Completed */
        AND t.updated_at >= p.approved_date /* Ensure positive duration */
        $date_conditions
    ";
    
    $stmt_avg_time = $conn->prepare($sql_avg_time);
    if (!$stmt_avg_time) {
        throw new Exception('Prepare failed for avg completion time: ' . $conn->error);
    }
    
    $stmt_avg_time->bind_param($types, ...$params);
    $stmt_avg_time->execute();
    $avg_time_result = $stmt_avg_time->get_result();
    $avg_time_row = $avg_time_result->fetch_assoc();
    $avg_completion_time = $avg_time_row['avg_completion_time'] !== null ? round($avg_time_row['avg_completion_time'], 2) : 0;
    $stmt_avg_time->close();

    // Calculate efficiency and completion rate
    $task_completion_efficiency = $total_tasks > 0 ? round(($efficient_tasks / $total_tasks) * 100, 2) : 0;
    $completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100, 2) : 0;

    return [
        'task_completion_efficiency' => $task_completion_efficiency,
        'completed_tasks' => $completed_tasks,
        'total_tasks' => $total_tasks,
        'completion_rate' => $completion_rate,
        'avg_completion_time' => $avg_completion_time
    ];
}

$conn->close();
?>