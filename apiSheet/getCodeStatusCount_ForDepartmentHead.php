
<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';


function get_total_task_count($conn, $department_id)
{

    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.status) total_task_status FROM tasks t WHERE t.is_approved = 1 AND t.status = '$status_id'";
    $query = "SELECT COUNT(t.task_id) total_task_count FROM tasks t LEFT JOIN projects p ON p.project_id = t.project_id LEFT JOIN users u ON u.id =  t.assigned_to  WHERE p.dept_id = '$department_id'  ";
    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

    $row = mysqli_fetch_assoc($result);
    // $count_total_status[] = $row;

    return array(
        'description' => 'total tasks',
        'total_tasks' => $row['total_task_count']
    );
}


function get_status_count($status_id, $conn, $department_id)
{

    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.status) total_task_status FROM tasks t WHERE t.is_approved = 1 AND t.status = '$status_id'";
    $query = "SELECT COUNT(t.status) total_task_status FROM tasks t LEFT JOIN projects p ON p.project_id = t.project_id LEFT JOIN users u ON u.id =  t.assigned_to  WHERE p.is_approved = 1 AND t.status = '$status_id' AND p.dept_id = '$department_id'";
    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

    $row = mysqli_fetch_assoc($result);
    // $count_total_status[] = $row;

    return $row['total_task_status'];
}


function get_approved_count($conn, $department_id)
{

    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.task_id) approved_project FROM tasks t LEFT JOIN projects p ON p.project_id = t.project_id WHERE p.is_approved = 1";
    $query = "SELECT COUNT(t.task_id) approved_project FROM tasks t LEFT JOIN projects p ON p.project_id = t.project_id LEFT JOIN users u ON u.id =  t.assigned_to WHERE p.is_approved = 1 AND p.dept_id = '$department_id'";

    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

    $row = mysqli_fetch_assoc($result);
    $count_total_status = $row['approved_project'];


    return array(
        'description' => 'Approved',
        'approved_count' => $row['approved_project']
    );
}

function get_unapproved_count($conn, $department_id)
{

    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.task_id) unapproved_project FROM tasks t LEFT JOIN projects p ON p.project_id = t.project_id WHERE p.is_approved = 0 ";
    $query = "SELECT COUNT(t.task_id) unapproved_project FROM tasks t LEFT JOIN projects p ON p.project_id = t.project_id LEFT JOIN users u ON u.id =  t.assigned_to WHERE p.is_approved = 0 AND p.dept_id = '$department_id'  ";

    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

    $row = mysqli_fetch_assoc($result);
    $count_total_status[] = $row['unapproved_project'];

    return array(
        'description' => 'unapproved',
        'unapproved_count' => $row['unapproved_project']
    );
    // return $row['unapproved_project'];

}




if (isset($_GET['init']) && isset($_GET['department_id'])) {
    $init = mysqli_escape_string($conn, $_GET['init']);
    $department_id = mysqli_escape_string($conn, $_GET['department_id']);

    $query = "SELECT * FROM code_desc LEFT JOIN code ON code.init = code_desc.init WHERE code_desc.init = '$init'";


    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);

    $codes = array();


    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // extract($row);
            if (!isset($codes[$row['init']])) {
                // If this is the first row for this contact, create an entry in the results
                $codes[$row['init']] = array(
                    "init" => $row['init'],
                    "name" => $row['name'],
                    "code_desc" => array(),
                    "total_tasks" => get_total_task_count($conn, $department_id),
                    "approved_project" => get_approved_count($conn, $department_id),
                    "unapproved_project" => get_unapproved_count($conn, $department_id)
                );
            }
            // Add this phone number to the `PhoneNumbers` array
            $codes[$row['init']]['code_desc'][] = array(
                'status_id' => $row['id'],
                'status_init' => $row['init'],
                "status_init_desc" => $row['init_desc'],
                "status" => $row['desc'],
                "status_count" => get_status_count($row['id'], $conn, $department_id, '1')
            );
        }
        $codes = array_values($codes);

        echo json_encode($codes);
    } else {
        echo 'empty';
    }
}
