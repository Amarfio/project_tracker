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

// Function to calculate task metrics for a user (for individual_tasks)
function calculateTaskMetrics($conn, $user_id, $start_date, $end_date) {
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
    
    debug_log("Executing sql_total: $sql_total with params: " . json_encode($params));
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
    
    // Query to get efficiently completed tasks
    $sql_efficient = "
        SELECT COUNT(*) as efficient_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE t.assigned_to = ?
        AND t.status = 61
        AND DATE(t.updated_at) <= t.end_date
        $date_conditions
    ";
    
    debug_log("Executing sql_efficient: $sql_efficient with params: " . json_encode($params));
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
        WHERE t.assigned_to = ?
        AND t.status = 61
        $date_conditions
    ";
    
    debug_log("Executing sql_completed: $sql_completed with params: " . json_encode($params));
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

    // Query to get average task completion time (in days)
    $sql_avg_time = "
        SELECT AVG(DATEDIFF(DATE(t.updated_at), p.approved_date)) as avg_completion_days
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE t.assigned_to = ?
        AND t.status = 61
        AND DATE(t.updated_at) >= p.approved_date
        $date_conditions
    ";
    
    debug_log("Executing sql_avg_time: $sql_avg_time with params: " . json_encode($params));
    $stmt_avg_time = $conn->prepare($sql_avg_time);
    if (!$stmt_avg_time) {
        throw new Exception('Prepare failed for avg completion time: ' . $conn->error);
    }
    
    $stmt_avg_time->bind_param($types, ...$params);
    $stmt_avg_time->execute();
    $avg_time_result = $stmt_avg_time->get_result();
    $avg_time_row = $avg_time_result->fetch_assoc();
    $avg_completion_days = $avg_time_row['avg_completion_days'] !== null ? round($avg_time_row['avg_completion_days'], 2) : 0;
    $stmt_avg_time->close();

    // Query to get number of completed projects
    $sql_projects_completed = "
        SELECT COUNT(DISTINCT p.project_id) as projects_completed
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        WHERE t.assigned_to = ?
        AND (SELECT COUNT(*) FROM tasks t2 WHERE t2.project_id = p.project_id) > 0
        AND (SELECT COUNT(*) FROM tasks t2 WHERE t2.project_id = p.project_id AND t2.status != 61) = 0
        $date_conditions
    ";
    
    debug_log("Executing sql_projects_completed: $sql_projects_completed with params: " . json_encode($params));
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
        WHERE t.assigned_to = ?
        $date_conditions
    ";
    
    debug_log("Executing sql_total_projects: $sql_total_projects with params: " . json_encode($params));
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
    $completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100, 2) : 0;

    // Calculate productivity rate
    $productivity_rate = round($task_completion_efficiency * $completion_rate / 100, 2);

    // Calculate overall growth
    $normalized_projects_completed = $total_projects > 0 ? round(($projects_completed / $total_projects) * 100, 2) : 0;
    $overall_growth = round((0.4 * $task_completion_efficiency) + (0.4 * $completion_rate) + (0.2 * $normalized_projects_completed), 2);

    return [
        'task_completion_efficiency' => $task_completion_efficiency,
        'completed_tasks' => $completed_tasks,
        'total_tasks' => $total_tasks,
        'completion_rate' => $completion_rate,
        'avg_completion_days' => $avg_completion_days,
        'projects_completed' => $projects_completed,
        'total_projects' => $total_projects,
        'productivity_rate' => $productivity_rate,
        'overall_growth' => $overall_growth
    ];
}

// Function to calculate task metrics for a department (for department_performance)
function calculateDepartmentMetrics($conn, $dept_id, $start_date, $end_date) {
    $date_conditions = "AND p.approved_date BETWEEN ? AND ?";
    $params = [$dept_id, $start_date, $end_date];
    $types = "iss";

    // Total tasks
    $sql_total = "
        SELECT COUNT(*) as total_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        JOIN users u ON t.assigned_to = u.id
        WHERE u.dept = ?
        $date_conditions
    ";
    $stmt_total = $conn->prepare($sql_total);
    if (!$stmt_total) throw new Exception('Prepare failed for total tasks: ' . $conn->error);
    $stmt_total->bind_param($types, ...$params);
    $stmt_total->execute();
    $total_result = $stmt_total->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_tasks = $total_row['total_tasks'] ?? 0;
    $stmt_total->close();

    // Efficient tasks (completed on or before end_date)
    $sql_efficient = "
        SELECT COUNT(*) as efficient_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        JOIN users u ON t.assigned_to = u.id
        WHERE u.dept = ?
        AND t.status = 61
        AND DATE(COALESCE(t.updated_at, t.end_date)) <= t.end_date
        $date_conditions
    ";
    $stmt_efficient = $conn->prepare($sql_efficient);
    if (!$stmt_efficient) throw new Exception('Prepare failed for efficient tasks: ' . $conn->error);
    $stmt_efficient->bind_param($types, ...$params);
    $stmt_efficient->execute();
    $efficient_result = $stmt_efficient->get_result();
    $efficient_row = $efficient_result->fetch_assoc();
    $efficient_tasks = $efficient_row['efficient_tasks'] ?? 0;
    $stmt_efficient->close();

    // Completed tasks
    $sql_completed = "
        SELECT COUNT(*) as completed_tasks
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        JOIN users u ON t.assigned_to = u.id
        WHERE u.dept = ?
        AND t.status = 61
        $date_conditions
    ";
    $stmt_completed = $conn->prepare($sql_completed);
    if (!$stmt_completed) throw new Exception('Prepare failed for completed tasks: ' . $conn->error);
    $stmt_completed->bind_param($types, ...$params);
    $stmt_completed->execute();
    $completed_result = $stmt_completed->get_result();
    $completed_row = $completed_result->fetch_assoc();
    $completed_tasks = $completed_row['completed_tasks'] ?? 0;
    $stmt_completed->close();

    // Average task cycle time (from project approval to task completion)
    $sql_avg_cycle_time = "
        SELECT AVG(DATEDIFF(COALESCE(t.updated_at, t.end_date), p.approved_date)) as avg_task_cycle_time
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        JOIN users u ON t.assigned_to = u.id
        WHERE u.dept = ?
        AND t.status = 61
        AND COALESCE(t.updated_at, t.end_date) >= p.approved_date
        $date_conditions
    ";
    debug_log("Executing sql_avg_cycle_time: $sql_avg_cycle_time with params: " . json_encode($params));
    $stmt_avg_cycle_time = $conn->prepare($sql_avg_cycle_time);
    if (!$stmt_avg_cycle_time) throw new Exception('Prepare failed for avg task cycle time: ' . $conn->error);
    $stmt_avg_cycle_time->bind_param($types, ...$params);
    $stmt_avg_cycle_time->execute();
    $avg_cycle_time_result = $stmt_avg_cycle_time->get_result();
    $avg_cycle_time_row = $avg_cycle_time_result->fetch_assoc();
    $avg_task_cycle_time = $avg_cycle_time_row['avg_task_cycle_time'] !== null ? round($avg_cycle_time_row['avg_task_cycle_time'], 2) : 0;
    $stmt_avg_cycle_time->close();

    // Calculate efficiency and completion rate
    $task_completion_efficiency = $total_tasks > 0 ? round(($efficient_tasks / $total_tasks) * 100, 2) : 0;
    $completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100, 2) : 0;

    return [
        'total_tasks' => $total_tasks,
        'completed_tasks' => $completed_tasks,
        'avg_task_cycle_time' => $avg_task_cycle_time,
        'task_completion_efficiency' => $task_completion_efficiency,
        'completion_rate' => $completion_rate
    ];
}

try {
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->error);
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

    // Adjust end_date to include the full day
    $end_date_inclusive = date('Y-m-d 23:59:59', strtotime($end_date));

    $data = [];

    if ($type === 'individual_tasks') {
        $sql = "
            SELECT 
                u.id,
                CONCAT(u.f_name, ' ', u.l_name) AS user,
                cd_dept.`desc` AS department,
                cd_role.`desc` AS role,
                u.email,
                COUNT(t.task_id) AS task_count,
                SUM(CASE WHEN t.status = 61 THEN 1 ELSE 0 END) AS completed_task_count
            FROM 
                users u
            LEFT JOIN 
                code_desc cd_dept ON u.dept = cd_dept.id AND cd_dept.init = 'dpt'
            LEFT JOIN 
                code_desc cd_role ON u.role = cd_role.id AND cd_role.init = 'rol'
            INNER JOIN 
                tasks t ON t.assigned_to = u.id 
            INNER JOIN 
                projects p ON t.project_id = p.project_id 
                AND p.approved_date BETWEEN ? AND ?
            WHERE 
                u.is_active = 1
            GROUP BY 
                u.id, u.f_name, u.l_name, cd_dept.`desc`, cd_role.`desc`, u.email";
        
        debug_log("Executing individual_tasks query: $sql with params: [$start_date, $end_date_inclusive]");
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception('Prepare failed for individual_tasks: ' . $conn->error);
        $stmt->bind_param('ss', $start_date, $end_date_inclusive);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $metrics = calculateTaskMetrics($conn, $row['id'], $start_date, $end_date_inclusive);
            $data[] = [
                'id' => (int)$row['id'],
                'user' => $row['user'] ?: 'Unknown User',
                'department' => $row['department'] ?: 'Unknown Department',
                'role' => $row['role'] ?: 'Unknown Role',
                'email' => $row['email'] ?: 'No Email',
                'task_count' => (int)$row['task_count'],
                'completed_task_count' => (int)$row['completed_task_count'],
                'task_completion_efficiency' => (float)$metrics['task_completion_efficiency'],
                'completed_tasks' => (int)$metrics['completed_tasks'],
                'total_tasks' => (int)$metrics['total_tasks'],
                'completion_rate' => (float)$metrics['completion_rate'],
                'avg_completion_days' => (float)$metrics['avg_completion_days'],
                'projects_completed' => (int)$metrics['projects_completed'],
                'total_projects' => (int)$metrics['total_projects'],
                'productivity_rate' => (float)$metrics['productivity_rate'],
                'overall_growth' => (float)$metrics['overall_growth']
            ];
        }

        usort($data, function($a, $b) {
            $eff_a = (float)$a['task_completion_efficiency'];
            $eff_b = (float)$b['task_completion_efficiency'];
            if ($eff_a === $eff_b) return strcmp($a['user'], $b['user']);
            return $eff_a < $eff_b ? 1 : -1;
        });

        debug_log("Individual tasks data fetched: " . count($data) . " rows");
        $stmt->close();
    } elseif ($type === 'department_performance') {
        // Department metrics
        $sql_depts = "
            SELECT 
                cd_dept.id AS dept_id,
                cd_dept.`desc` AS department,
                COUNT(t.task_id) AS total_tasks,
                SUM(CASE WHEN t.status = 61 THEN 1 ELSE 0 END) AS completed_tasks,
                AVG(
                    CASE 
                        WHEN t.status = 61 AND DATE(t.updated_at) >= p.approved_date
                        THEN DATEDIFF(DATE(t.updated_at), p.approved_date)
                        ELSE NULL
                    END
                ) AS avg_task_cycle_time
            FROM 
                users u
            LEFT JOIN 
                code_desc cd_dept ON u.dept = cd_dept.id AND cd_dept.init = 'dpt'
            INNER JOIN 
                tasks t ON t.assigned_to = u.id 
            INNER JOIN 
                projects p ON t.project_id = p.project_id 
                AND p.approved_date BETWEEN ? AND ?
            WHERE 
                u.is_active = 1
            GROUP BY 
                cd_dept.id, cd_dept.`desc`
            HAVING 
                total_tasks > 0";
        
        debug_log("Executing department_performance query: $sql_depts with params: [$start_date, $end_date_inclusive]");
        $stmt_depts = $conn->prepare($sql_depts);
        if (!$stmt_depts) throw new Exception('Prepare failed for department_performance: ' . $conn->error);
        $stmt_depts->bind_param('ss', $start_date, $end_date_inclusive);
        $stmt_depts->execute();
        $result_depts = $stmt_depts->get_result();

        $departments = [];
        while ($row = $result_depts->fetch_assoc()) {
            $metrics = calculateDepartmentMetrics($conn, $row['dept_id'], $start_date, $end_date_inclusive);
            $departments[] = [
                'department' => $row['department'] ?: 'Unknown Department',
                'total_tasks' => (int)$row['total_tasks'],
                'completed_tasks' => (int)$row['completed_tasks'],
                'avg_task_cycle_time' => $row['avg_task_cycle_time'] !== null ? round($row['avg_task_cycle_time'], 2) : 0,
                'task_completion_efficiency' => $metrics['task_completion_efficiency'],
                'completion_rate' => $metrics['completion_rate']
            ];
        }
        debug_log("Departments fetched: " . count($departments));
        $stmt_depts->close();

        // Underperforming individuals (top 1 per department without ROW_NUMBER)
        $sql_underperforming = "
            SELECT 
                u.id,
                CONCAT(u.f_name, ' ', u.l_name) AS user,
                cd_dept.`desc` AS department,
                COUNT(t.task_id) AS total_tasks,
                SUM(CASE WHEN t.status = 61 THEN 1 ELSE 0 END) AS completed_tasks,
                SUM(CASE 
                    WHEN (t.status = 61 AND DATE(t.updated_at) > t.end_date) 
                    OR (t.status != 61 AND t.end_date < CURRENT_DATE) 
                    THEN 1 ELSE 0 END) AS overdue_tasks,
                AVG(
                    CASE 
                        WHEN t.status = 61 AND DATE(t.updated_at) > t.end_date
                        THEN DATEDIFF(DATE(t.updated_at), t.end_date)
                        WHEN t.status != 61 AND t.end_date < CURRENT_DATE
                        THEN DATEDIFF(CURRENT_DATE, t.end_date)
                        ELSE NULL
                    END
                ) AS avg_delay_days
            FROM 
                users u
            LEFT JOIN 
                code_desc cd_dept ON u.dept = cd_dept.id AND cd_dept.init = 'dpt'
            INNER JOIN 
                tasks t ON t.assigned_to = u.id 
            INNER JOIN 
                projects p ON t.project_id = p.project_id 
                AND p.approved_date BETWEEN ? AND ?
            WHERE 
                u.is_active = 1
            GROUP BY 
                u.id, cd_dept.`desc`
            HAVING 
                overdue_tasks > 0
            ORDER BY 
                cd_dept.`desc`, overdue_tasks DESC, avg_delay_days DESC";
        
        debug_log("Executing underperforming query: $sql_underperforming with params: [$start_date, $end_date_inclusive]");
        $stmt_underperforming = $conn->prepare($sql_underperforming);
        if (!$stmt_underperforming) throw new Exception('Prepare failed for underperforming: ' . $conn->error);
        $stmt_underperforming->bind_param('ss', $start_date, $end_date_inclusive);
        $stmt_underperforming->execute();
        $result_underperforming = $stmt_underperforming->get_result();
        $underperforming_individuals = [];
        $seen_departments = [];
        while ($row = $result_underperforming->fetch_assoc()) {
            $dept = $row['department'] ?: 'Unknown Department';
            if (!isset($seen_departments[$dept])) {
                $completion_rate = $row['total_tasks'] > 0 ? round(($row['completed_tasks'] / $row['total_tasks']) * 100, 2) : 0;
                $underperforming_individuals[] = [
                    'user' => $row['user'] ?: 'Unknown User',
                    'department' => $dept,
                    'overdue_tasks' => (int)$row['overdue_tasks'],
                    'avg_delay_days' => $row['avg_delay_days'] !== null ? round($row['avg_delay_days'], 2) : 0,
                    'completion_rate' => $completion_rate
                ];
                $seen_departments[$dept] = true;
            }
            if (count($underperforming_individuals) >= 7) break;
        }
        debug_log("Underperforming individuals fetched: " . count($underperforming_individuals));
        $stmt_underperforming->close();

        // Performance trends (monthly completion rates)
        $sql_trends = "
            SELECT 
                DATE_FORMAT(p.approved_date, '%Y-%m') AS month,
                cd_dept.`desc` AS department,
                COUNT(t.task_id) AS total_tasks,
                SUM(CASE WHEN t.status = 61 THEN 1 ELSE 0 END) AS completed_tasks
            FROM 
                tasks t
            JOIN projects p ON t.project_id = p.project_id
            JOIN users u ON t.assigned_to = u.id
            LEFT JOIN code_desc cd_dept ON u.dept = cd_dept.id AND cd_dept.init = 'dpt'
            WHERE p.approved_date BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(p.approved_date, '%Y-%m'), cd_dept.`desc`
            ORDER BY month, cd_dept.`desc`";
        
        debug_log("Executing trends query: $sql_trends with params: [$start_date, $end_date_inclusive]");
        $stmt_trends = $conn->prepare($sql_trends);
        if (!$stmt_trends) throw new Exception('Prepare failed for trends: ' . $conn->error);
        $stmt_trends->bind_param('ss', $start_date, $end_date_inclusive);
        $stmt_trends->execute();
        $trends_result = $stmt_trends->get_result();
        $trends = ['months' => []];
        while ($row = $trends_result->fetch_assoc()) {
            $month = $row['month'];
            $dept = strtolower($row['department'] ?: 'unknown');
            $completion_rate = $row['total_tasks'] > 0 ? round(($row['completed_tasks'] / $row['total_tasks']) * 100, 2) : 0;
            
            if (!in_array($month, $trends['months'])) {
                $trends['months'][] = $month;
            }
            if (!isset($trends[$dept])) {
                $trends[$dept] = array_fill(0, count($trends['months']), 0);
            }
            $trends[$dept][array_search($month, $trends['months'])] = $completion_rate;
        }
        debug_log("Trends fetched: months=" . count($trends['months']));
        $stmt_trends->close();

        $data = [
            'departments' => $departments,
            'underperforming_individuals' => $underperforming_individuals,
            'trends' => $trends
        ];

        debug_log("Department performance data fetched: depts=" . count($departments) . ", underperforming=" . count($underperforming_individuals) . ", trend_months=" . count($trends['months']));
    } elseif ($type === 'task_timelines') {
        $sql_delivery_status = "
            SELECT 
                SUM(CASE WHEN t.status = 61 AND DATE(t.updated_at) <= t.end_date THEN 1 ELSE 0 END) AS on_time_tasks,
                SUM(CASE WHEN (t.status = 61 AND DATE(t.updated_at) > t.end_date) OR (t.status != 61 AND t.end_date < CURRENT_DATE) THEN 1 ELSE 0 END) AS late_tasks
            FROM tasks t
            JOIN projects p ON t.project_id = p.project_id
            WHERE p.approved_date BETWEEN ? AND ?
        ";
        debug_log("Executing sql_delivery_status: $sql_delivery_status with params: [$start_date, $end_date_inclusive]");
        $stmt_delivery_status = $conn->prepare($sql_delivery_status);
        if (!$stmt_delivery_status) throw new Exception('Prepare failed for delivery status: ' . $conn->error);
        $stmt_delivery_status->bind_param('ss', $start_date, $end_date_inclusive);
        $stmt_delivery_status->execute();
        $delivery_status_result = $stmt_delivery_status->get_result();
        $delivery_status_row = $delivery_status_result->fetch_assoc();
        $on_time_tasks = (int)($delivery_status_row['on_time_tasks'] ?? 0);
        $late_tasks = (int)($delivery_status_row['late_tasks'] ?? 0);
        $stmt_delivery_status->close();

        $sql_avg_delay = "
            SELECT AVG(DATEDIFF(COALESCE(DATE(t.updated_at), CURRENT_DATE), t.end_date)) AS avg_delay_days
            FROM tasks t
            JOIN projects p ON t.project_id = p.project_id
            WHERE ((t.status = 61 AND DATE(t.updated_at) > t.end_date) OR (t.status != 61 AND t.end_date < CURRENT_DATE))
            AND p.approved_date BETWEEN ? AND ?
        ";
        debug_log("Executing sql_avg_delay: $sql_avg_delay with params: [$start_date, $end_date_inclusive]");
        $stmt_avg_delay = $conn->prepare($sql_avg_delay);
        if (!$stmt_avg_delay) throw new Exception('Prepare failed for avg delay: ' . $conn->error);
        $stmt_avg_delay->bind_param('ss', $start_date, $end_date_inclusive);
        $stmt_avg_delay->execute();
        $avg_delay_result = $stmt_avg_delay->get_result();
        $avg_delay_row = $avg_delay_result->fetch_assoc();
        $avg_delay_days = $avg_delay_row['avg_delay_days'] !== null ? round($avg_delay_row['avg_delay_days'], 2) : 0;
        $stmt_avg_delay->close();

        $sql_most_delayed = "SELECT 
        t.description AS task_name,
        cd_dept.`desc` AS department,
        DATEDIFF(COALESCE(t.updated_at, t.end_date), t.end_date) AS delay_days
    FROM tasks t
    JOIN projects p ON t.project_id = p.project_id
    JOIN users u ON t.assigned_to = u.id
    LEFT JOIN code_desc cd_dept ON u.dept = cd_dept.id AND cd_dept.init = 'dpt'
    WHERE ((t.status = 61 AND DATE(t.updated_at) > t.end_date) OR (t.status != 61 AND t.end_date < CURRENT_DATE))
    AND p.approved_date BETWEEN ? AND ?
    ORDER BY delay_days DESC
    LIMIT 10";
        debug_log("Executing sql_most_delayed: $sql_most_delayed with params: [$start_date, $end_date_inclusive]");
        $stmt_most_delayed = $conn->prepare($sql_most_delayed);
        if (!$stmt_most_delayed) throw new Exception('Prepare failed for most delayed tasks: ' . $conn->error);
        $stmt_most_delayed->bind_param('ss', $start_date, $end_date_inclusive);
        $stmt_most_delayed->execute();
        $most_delayed_result = $stmt_most_delayed->get_result();
        $most_delayed_tasks = [];
        while ($row = $most_delayed_result->fetch_assoc()) {
            $most_delayed_tasks[] = [
                'task_name' => $row['task_name'] ?: 'Unknown Task',
                'department' => $row['department'] ?: 'Unknown Department',
                'delay_days' => (int)$row['delay_days']
            ];
        }
        $stmt_most_delayed->close();

        $sql_trends = "
            SELECT 
                DATE_FORMAT(p.approved_date, '%Y-%m') AS month,
                AVG(DATEDIFF(COALESCE(DATE(t.updated_at), CURRENT_DATE), t.end_date)) AS avg_delay
            FROM tasks t
            JOIN projects p ON t.project_id = p.project_id
            WHERE ((t.status = 61 AND DATE(t.updated_at) > t.end_date) OR (t.status != 61 AND t.end_date < CURRENT_DATE))
            AND p.approved_date BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(p.approved_date, '%Y-%m')
            ORDER BY month
        ";
        debug_log("Executing sql_trends: $sql_trends with params: [$start_date, $end_date_inclusive]");
        $stmt_trends = $conn->prepare($sql_trends);
        if (!$stmt_trends) throw new Exception('Prepare failed for delivery trends: ' . $conn->error);
        $stmt_trends->bind_param('ss', $start_date, $end_date_inclusive);
        $stmt_trends->execute();
        $trends_result = $stmt_trends->get_result();
        $trend_months = [];
        $trend_delays = [];
        while ($row = $trends_result->fetch_assoc()) {
            $trend_months[] = $row['month'];
            $trend_delays[] = round($row['avg_delay'], 2);
        }
        $stmt_trends->close();

        $data = [
            'on_time_tasks' => $on_time_tasks,
            'late_tasks' => $late_tasks,
            'avg_delay_days' => $avg_delay_days,
            'most_delayed_tasks' => $most_delayed_tasks,
            'trend_months' => $trend_months,
            'trend_delays' => $trend_delays
        ];

        debug_log("Task timelines data fetched: on_time=$on_time_tasks, late=$late_tasks, avg_delay=$avg_delay_days, delayed_tasks=" . count($most_delayed_tasks) . ", trend_months=" . count($trend_months));
    } elseif ($type === 'completion_metrics') {
        $data = [['message' => 'Completion metrics data not implemented']];
    } else {
        throw new Exception('Invalid analysis type');
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

    debug_log("Response sent: success=true, data_count=" . (is_array($data) ? count($data) : 1));
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