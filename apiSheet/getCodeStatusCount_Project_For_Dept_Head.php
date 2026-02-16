<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once 'connect.php';


/*
|--------------------------------------------------------------------------
| GET TOTAL PROJECT COUNT (NO DUPLICATES)
|--------------------------------------------------------------------------
*/
function get_total_project_count($conn, $department_id)
{
    $query = "
        SELECT COUNT(*) AS total_project_count
        FROM projects p
        WHERE p.is_archive = 0
        AND EXISTS (
            SELECT 1
            FROM tasks t
            WHERE t.project_id = p.project_id
            AND t.department = ?
        )
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return array(
        'description' => 'total',
        'total_projects' => $row['total_project_count']
    );
}


/*
|--------------------------------------------------------------------------
| GET STATUS COUNT (NO DUPLICATES)
|--------------------------------------------------------------------------
*/
function get_status_count($status_id, $department_id, $conn)
{
    // Default query
    $query = "
        SELECT COUNT(*) AS total_project_count
        FROM projects p
        WHERE p.status = ?
        AND p.is_archive = 0
        AND EXISTS (
            SELECT 1
            FROM tasks t
            WHERE t.project_id = p.project_id
            AND t.department = ?
        )
    ";

    // Special cases
    if ($status_id == 85) {
        $query = "
            SELECT COUNT(*) AS total_project_count
            FROM projects p
            WHERE p.is_approved = 1
            AND p.is_archive = 0
            AND EXISTS (
                SELECT 1
                FROM tasks t
                WHERE t.project_id = p.project_id
                AND t.department = ?
            )
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $department_id);
    }
    elseif ($status_id == 131) {
        $query = "
            SELECT COUNT(*) AS total_project_count
            FROM projects p
            WHERE p.is_archive = 1
            AND EXISTS (
                SELECT 1
                FROM tasks t
                WHERE t.project_id = p.project_id
                AND t.department = ?
            )
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $department_id);
    }
    else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $status_id, $department_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['total_project_count'];
}


/*
|--------------------------------------------------------------------------
| MAIN REQUEST
|--------------------------------------------------------------------------
*/
if (isset($_GET['init'])) {

    $init = $_GET['init'];
    $department_id = (int) $_GET['department_id'];

    $query = "SELECT * FROM code_desc WHERE init = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $init);
    $stmt->execute();

    $result = $stmt->get_result();
    $codes = array();

    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            if (!isset($codes[$row['init']])) {

                $codes[$row['init']] = array(
                    "init" => $row['init'],
                    "code_desc" => array(),
                    "total_projects" => get_total_project_count($conn, $department_id)
                );
            }

            $codes[$row['init']]['code_desc'][] = array(
                'status_id' => $row['id'],
                'status_init' => $row['init'],
                "status_init_desc" => $row['init_desc'],
                "status" => $row['desc'],
                "code_color" => $row['color'],
                "status_count" => get_status_count($row['id'], $department_id, $conn)
            );
        }

        echo json_encode(array_values($codes));
    } else {
        echo json_encode([]);
    }
}