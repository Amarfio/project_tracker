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

// Function to calculate working days between two dates (excluding weekends)
function getWorkingDays($startDate, $endDate) {
    if (!$startDate || !$endDate) {
        return null;
    }
    
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $end->modify('+1 day'); // Include end date in calculation
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);
    
    $workingDays = 0;
    foreach ($period as $date) {
        $dayOfWeek = $date->format('N'); // 1 (Mon) - 7 (Sun)
        if ($dayOfWeek < 6) { // Skip Saturday (6) and Sunday (7)
            $workingDays++;
        }
    }
    
    return $workingDays;
}

try {
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

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

    // Get departments
    $specific_departments = [
        'Credits',
        'Enterprise Solutions',
        'Project Management',
        'Mobile Operations',
        'Mobile Technologies',
        'Operation',
        'Systems',
        'Treasury/Payment'
    ];
    $placeholders = implode("','", array_map([$conn, 'real_escape_string'], $specific_departments));
    $sql_depts = "SELECT id, `desc` FROM code_desc WHERE init = 'dpt' AND `desc` IN ('$placeholders')";
    $result_depts = $conn->query($sql_depts);
    if ($result_depts === false) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    $departments = [];
    while ($row = $result_depts->fetch_assoc()) {
        $departments[$row['id']] = $row['desc'];
    }
    $result_depts->free();

    // Initialize table data
    $table_data = array_map(function($dept) {
        return [
            'department' => $dept,
            'outstanding_tasks' => 0,
            'new_tasks' => 0,
            'total_completed' => 0,
            'oldest_outstanding' => 0,
            'tasks_exceeded' => 0,
            'avg_days_to_complete' => 0
        ];
    }, $specific_departments);

    // Query for table data
    foreach ($departments as $dept_id => $dept_name) {
        $index = array_search($dept_name, $specific_departments);

        // Outstanding Tasks (as of end date)
        $sql = "SELECT COUNT(DISTINCT t.task_id) as count
                FROM tasks t
                JOIN projects p ON t.project_id = p.project_id
                LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
                LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
                WHERE (t.department = ? OR (t.department IS NULL AND p.dept_id = ?))
                AND t.status NOT IN (60, 61)
                AND p.approved_date IS NOT NULL
                AND p.approved_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('iiss', $dept_id, $dept_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $table_data[$index]['outstanding_tasks'] = $result->fetch_assoc()['count'];
        $stmt->close();

        // New Tasks
        $sql = "SELECT COUNT(DISTINCT t.task_id) as count
                FROM tasks t
                JOIN projects p ON t.project_id = p.project_id
                LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
                LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
                WHERE (t.department = ? OR (t.department IS NULL AND p.dept_id = ?))
                AND p.approved_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('iiss', $dept_id, $dept_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $table_data[$index]['new_tasks'] = $result->fetch_assoc()['count'];
        $stmt->close();

        // Total Completed
        $sql = "SELECT COUNT(DISTINCT t.task_id) as count
                FROM tasks t
                JOIN projects p ON t.project_id = p.project_id
                LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
                LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
                WHERE (t.department = ? OR (t.department IS NULL AND p.dept_id = ?))
                AND t.status = 61
                AND t.updated_at BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('iiss', $dept_id, $dept_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $table_data[$index]['total_completed'] = $result->fetch_assoc()['count'];
        $stmt->close();

        // Oldest Outstanding In Days
        $sql = "SELECT MIN(p.approved_date) as earliest_approved_date
                FROM tasks t
                JOIN projects p ON t.project_id = p.project_id
                LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
                LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
                WHERE (t.department = ? OR (t.department IS NULL AND p.dept_id = ?))
                AND t.status NOT IN (60, 61)
                AND p.approved_date IS NOT NULL
                AND p.approved_date BETWEEN ? AND ?
                AND (t.updated_at IS NULL OR t.updated_at > ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('iisss', $dept_id, $dept_id, $start_date, $end_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $earliest_approved_date = $row['earliest_approved_date'];
        $table_data[$index]['oldest_outstanding'] = $earliest_approved_date ? getWorkingDays($earliest_approved_date, $end_date) : 0;
        $stmt->close();

        // Tasks Exceeded
        $sql = "SELECT COUNT(DISTINCT t.task_id) as count
                FROM tasks t
                JOIN projects p ON t.project_id = p.project_id
                LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
                LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
                WHERE (t.department = ? OR (t.department IS NULL AND p.dept_id = ?))
                AND t.end_date IS NOT NULL
                AND p.approved_date IS NOT NULL
                AND p.approved_date BETWEEN ? AND ?
                AND (
                    (t.status NOT IN (60, 61) AND t.end_date < CURRENT_DATE)
                    OR
                    (t.status = 61 AND t.updated_at > t.end_date)
                )";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('iiss', $dept_id, $dept_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $table_data[$index]['tasks_exceeded'] = $result->fetch_assoc()['count'];
        $stmt->close();

        // Average Days to Complete
        $sql = "SELECT p.approved_date, t.updated_at
                FROM tasks t
                JOIN projects p ON t.project_id = p.project_id
                LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
                LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
                WHERE (t.department = ? OR (t.department IS NULL AND p.dept_id = ?))
                AND t.status = 61
                AND t.updated_at IS NOT NULL
                AND p.approved_date IS NOT NULL
                AND p.approved_date <= t.updated_at
                AND t.updated_at BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('iiss', $dept_id, $dept_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $totalDays = 0;
        $count = 0;
        while ($row = $result->fetch_assoc()) {
            $days = getWorkingDays($row['approved_date'], $row['updated_at']);
            if ($days !== null) {
                $totalDays += $days;
                $count++;
            }
        }
        $table_data[$index]['avg_days_to_complete'] = $count > 0 ? round($totalDays / $count, 1) : 0;
        $stmt->close();
    }

    // Query for Total Projects
    $sql_total_projects = "SELECT COUNT(*) as total_projects
                           FROM projects
                           WHERE created_at >= ? AND created_at <= ?
                           AND approved_date IS NOT NULL
                           AND end_date IS NOT NULL";
    $stmt = $conn->prepare($sql_total_projects);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_projects = $result->fetch_assoc()['total_projects'];
    $stmt->close();

    // Query for Completed Projects
    $sql_completed_projects = "SELECT COUNT(*) as completed_projects
                              FROM projects
                              WHERE status = 88
                              AND updated_at IS NOT NULL
                              AND updated_at >= ? AND updated_at <= ?";
    $stmt = $conn->prepare($sql_completed_projects);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $completed_projects = $result->fetch_assoc()['completed_projects'];
    $stmt->close();

    // Query for Total Tasks
    $sql_total_tasks = "SELECT COUNT(DISTINCT t.task_id) as count
                        FROM tasks t
                        JOIN projects p ON t.project_id = p.project_id
                        LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
                        LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
                        WHERE (cd.id IS NOT NULL OR pcd.id IS NOT NULL)
                        AND p.approved_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql_total_tasks);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_tasks = $result->fetch_assoc()['count'];
    $stmt->close();

    // Query for Completed Tasks
    $sql_completed_tasks = "SELECT COUNT(DISTINCT t.task_id) as count
                            FROM tasks t
                            JOIN projects p ON t.project_id = p.project_id
                            LEFT JOIN code_desc cd ON t.department = cd.id AND cd.init = 'dpt'
                            LEFT JOIN code_desc pcd ON p.dept_id = pcd.id AND pcd.init = 'dpt'
                            WHERE (cd.id IS NOT NULL OR pcd.id IS NOT NULL)
                            AND t.status = 61
                            AND t.updated_at BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql_completed_tasks);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $completed_tasks = $result->fetch_assoc()['count'];
    $stmt->close();

    // Card data (only the 4 values we need)
    $card_data = [
        ['value' => $total_projects],
        ['value' => $completed_projects],
        ['value' => $total_tasks],
        ['value' => $completed_tasks]
    ];

    echo json_encode([
        'success' => true,
        'table_data' => $table_data,
        'card_data' => $card_data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>