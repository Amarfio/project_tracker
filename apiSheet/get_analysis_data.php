<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set time zone to match database
date_default_timezone_set('UTC');

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

    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    $type = isset($_GET['type']) ? $_GET['type'] : 'individual_tasks';

    debug_log("Request received: type=$type, start_date=$start_date, end_date=$end_date");

    if (!$start_date || !$end_date) {
        throw new Exception('Start date and end date are required');
    }

    // Validate dates
    if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }

    if (strtotime($start_date) > strtotime($end_date)) {
        throw new Exception('Start date cannot be after end date');
    }

    $data = [];

    if ($type === 'individual_tasks') {
        // Query active users with their id, department, role, email, and task count
        $sql = "SELECT 
                    u.id,
                    CONCAT(u.f_name, ' ', u.l_name) AS user,
                    cd_dept.`desc` AS department,
                    cd_role.`desc` AS role,
                    u.email,
                    COUNT(t.task_id) AS task_count
                FROM 
                    users u
                LEFT JOIN 
                    code_desc cd_dept ON u.dept = cd_dept.id AND cd_dept.init = 'dpt'
                LEFT JOIN 
                    code_desc cd_role ON u.role = cd_role.id AND cd_role.init = 'rol'
                LEFT JOIN 
                    tasks t ON t.assigned_to = u.id 
                    AND t.created_at BETWEEN ? AND ?
                WHERE 
                    u.is_active = 1
                GROUP BY 
                    u.id, u.f_name, u.l_name, cd_dept.`desc`, cd_role.`desc`, u.email
                ORDER BY 
                    task_count DESC, u.f_name, u.l_name";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        // Adjust end_date to include the full day
        $end_date_inclusive = date('Y-m-d 23:59:59', strtotime($end_date));
        $stmt->bind_param('ss', $start_date, $end_date_inclusive);

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => (int)$row['id'],
                'user' => $row['user'] ?: 'Unknown User',
                'department' => $row['department'] ?: 'Unknown Department',
                'role' => $row['role'] ?: 'Unknown Role',
                'email' => $row['email'] ?: 'No Email',
                'task_count' => (int)$row['task_count']
            ];
        }

        debug_log("Individual tasks data fetched: " . count($data) . " rows");

        $stmt->close();
    } else if ($type === 'department_performance') {
        $data = [['message' => 'Department performance data not implemented']];
    } else if ($type === 'task_timelines') {
        $data = [['message' => 'Task timelines data not implemented']];
    } else if ($type === 'completion_metrics') {
        $data = [['message' => 'Completion metrics data not implemented']];
    } else {
        throw new Exception('Invalid analysis type');
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

    debug_log("Response sent: success=true, data_count=" . count($data));

} catch (Exception $e) {
    http_response_code(500);
    $error_message = 'Error: ' . $e->getMessage();
    echo json_encode([
        'success' => false,
        'message' => $error_message
    ]);
    debug_log($error_message);
}

$conn->close();
?>