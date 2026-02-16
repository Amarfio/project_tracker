<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');

require_once 'connect.php';
require_once 'functions/formatDate.php';
require_once 'functions/noOfTasks.php';
require_once 'functions/usableFunctions.php';


/*
|--------------------------------------------------------------------------
| PROJECT AVERAGE COMPLETION
|--------------------------------------------------------------------------
*/
function project_avg_percentage($project_id, $conn){
    $query = "
        SELECT AVG(t.completion) AS avg_completion
        FROM tasks t
        WHERE t.project_id = ?
        AND t.status <> 60
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();
    return round($result['avg_completion'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| BASE PROJECT QUERY (NO DUPLICATES)
|--------------------------------------------------------------------------
*/
function base_project_query($department_id){

    return "
        SELECT 
            pro.*,
            CONCAT(u.f_name,' ',u.l_name) AS m_o,
            co.id AS version_id,
            co.desc AS version,
            pro.status AS status_id,
            co_p_status.desc AS status_desc,
            co_dept.id AS department_id,
            co_dept.desc AS department,
            cli.name AS client_name,
            DATEDIFF(NOW(), pro.start_date) AS no_of_days
        FROM projects pro
        LEFT JOIN code_desc co ON pro.version_no = co.id
        LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id
        LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status
        LEFT JOIN clients cli ON cli.client_id = pro.client
        LEFT JOIN users u ON u.id = pro.owner
        WHERE EXISTS (
            SELECT 1 
            FROM tasks t
            WHERE t.project_id = pro.project_id
            AND t.department = '$department_id'
        )
    ";
}


/*
|--------------------------------------------------------------------------
| GET PROJECTS BY STATUS (NO DUPLICATES)
|--------------------------------------------------------------------------
*/
function query_project_by_status($status_id, $department_id){

    $base = base_project_query($department_id);

    if ($status_id == 116) {
        return $base . "
            AND CURRENT_DATE > pro.end_date
            AND pro.is_approved = 1
            AND pro.is_archive = 0
            ORDER BY pro.project_id DESC
        ";
    }

    if ($status_id == 85) {
        return $base . "
            AND pro.is_approved = 1
            AND pro.is_archive = 0
            ORDER BY pro.project_id DESC
        ";
    }

    if ($status_id == 131) {
        return $base . "
            AND pro.is_archive = 1
            ORDER BY pro.project_id DESC
        ";
    }

    return $base . "
        AND pro.status = '$status_id'
        AND pro.is_archive = 0
        ORDER BY pro.project_id DESC
    ";
}


/*
|--------------------------------------------------------------------------
| GET ALL PROJECTS (NO DUPLICATES)
|--------------------------------------------------------------------------
*/
function query_total_project($department_id){
    return base_project_query($department_id) . "
        AND pro.is_archive = 0
        ORDER BY pro.project_id DESC
    ";
}


/*
|--------------------------------------------------------------------------
| EXECUTE QUERY & RETURN RESULTS
|--------------------------------------------------------------------------
*/
function result_from_query($conn, $query){

    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode([
            'message' => 'Empty data',
            'status'  => 'success'
        ]);
        exit;
    }

    $projects = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $age = max(0, $row['no_of_days']);
        $completion = project_avg_percentage($row['project_id'], $conn);

        if ($completion == 100) {
            $age = "-";
        }

        $projects[] = [
            'project_id' => $row['project_id'],
            'version_id' => $row['version_id'],
            'client' => $row['client_name'],
            'version' => $row['version'],
            'status_id' => $row['status_id'],
            'status_desc' => $row['status_desc'],
            'department_id' => $row['department_id'],
            'department' => $row['department'],
            'owner' => $row['m_o'],
            'completion' => $completion,
            'name' => $row['name'],
            'hash_tag' => $row['hash_tag'],
            'start_date' => formatDate($row['start_date']),
            'end_date' => formatDate($row['end_date']),
            'date_of_completion' => checkForCompletionDate($conn, $row['project_id']),
            'age' => $age,
            'no_of_tasks' => noOfTasks($row['project_id'], $conn),
            'no_of_overdue_tasks' => noOfTasksOverdue($row['project_id'], $conn)
        ];
    }

    echo json_encode([
        'message' => 'Great here are your data',
        'status'  => 'success',
        'data'    => $projects
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CONTROLLER
|--------------------------------------------------------------------------
*/
if (isset($_GET['status_id']) && isset($_GET['department_id'])) {

    $status_id = intval($_GET['status_id']);
    $department_id = intval($_GET['department_id']);

    result_from_query(
        $conn,
        query_project_by_status($status_id, $department_id)
    );

} elseif (isset($_GET['department_id'])) {

    $department_id = intval($_GET['department_id']);

    result_from_query(
        $conn,
        query_total_project($department_id)
    );

} else {

    echo json_encode([
        'message' => 'Missing parameters',
        'status'  => 'error'
    ]);
}
