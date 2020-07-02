<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

function project_avg_percentage($project_id, $conn){

    $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE t.project_id = '$project_id'";
    $result = mysqli_query($conn, $query);
   $row = mysqli_fetch_array($result);
   return $row['project_average_completion'];


}

require_once 'connect.php';

$query = "SELECT pro.*, co.id version_id, co.desc version, co_dept.id department_id, co_dept.desc department FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id ORDER BY pro.project_id DESC";

$result = mysqli_query($conn, $query);

$num = mysqli_num_rows($result);




function get_all_tasks ($project_id, $conn){
    $task_arr = array();
    

    $query = "SELECT * FROM tasks WHERE tasks.project_id = '$project_id'";

    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);

    while ($row = mysqli_fetch_assoc($result)) {
        
       $task_arr[] = array(
            'task_id' => $row['task_id'],
            "description" => $row['description'],
            "completion" => $row['completion'],
            "assigned_by" => $row['assigned_by'],
            "assigned_to" => $row['assigned_to'],
            "start_date" => $row['start_date'],
            "end_date" => $row['end_date']
        );
    }
    return $task_arr;
}

$projects_tasks= array();
// $task_arr = array(
    
// );
// $projects_wit_tasks = array();

if ($num > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $projects_tasks[] =  array(
            'project_id' => $row['project_id'],
            "version_id"   => $row['version_id'],
            "version"   => $row['version'],
            "department_id"   => $row['department_id'],
            "department"   => $row['department'],
            "completion"   => project_avg_percentage($row['project_id'], $conn),
            "name" => $row['name'],
            "start_date" => $row['start_date'],
            "end_date" => $row['end_date'],
            "tasks" => get_all_tasks($row['project_id'], $conn)
        );
    }

    
    $message = json_encode(
        array(
            'message' => 'Great here are your data',
            'status' => 'success',
            'data' => $projects_tasks
        )
    );
    exit($message);

    // echo json_encode($projects_tasks);
} else {
        
    $message = json_encode(
        array(
            'message' => 'Empty data',
            'status' => 'success'
        ) 
    );
    exit($message);
}
