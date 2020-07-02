<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';


    function project_avg_percentage($project_id, $conn){

        $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE t.project_id = '$project_id'";
        $result = mysqli_query($conn, $query);
       $row = mysqli_fetch_array($result);
       return $row['project_average_completion'];
    
  
    }
    

    function get_department_name($department_id, $conn){

        $query = "SELECT co.desc department_name FROM code_desc co WHERE co.id = '$department_id'";
        $result = mysqli_query($conn, $query);
       $row = mysqli_fetch_array($result);
       return $row['department_name'];
    
  
    }
    
         
    
    
    function get_all_tasks ($project_id, $conn){
        $task_arr = array();
        
    
        $query = "SELECT t.task_id, t.description, t.project_id, t.start_date, t.end_date, cl.client_id client_id, cl.name client, CONCAT(u_to.f_name, ' ', u_to.l_name) assigned_to, u_to.id assigned_to_id, CONCAT(u_by.f_name, ' ', u_by.l_name) assigned_by, CONCAT(u_ap.f_name, ' ', u_ap.l_name) approved_by, t.completion, cod_pri.id priority_id, cod_pri.desc priority, cod_sta.id status_id, cod_sta.desc status FROM tasks t LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN users u_by ON u_by.id = t.assigned_by LEFT JOIN users u_ap ON u_ap.id = t.approved_by LEFT JOIN code_desc cod_pri ON cod_pri.id = t.priority LEFT JOIN code_desc cod_sta ON cod_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id WHERE t.project_id = '$project_id' ORDER BY t.task_id DESC";
    
        $result = mysqli_query($conn, $query);
    
        $num = mysqli_num_rows($result);
    
        while ($row = mysqli_fetch_assoc($result)) {
           
           $task_id_base64Encode = array(
                'task_id_base64Encode' => base64_encode($row['task_id']) ,
            );
            $task_arr[] = $row;

            array_push($task_arr[0], $task_id_base64Encode);
        }
        return $task_arr;
    }
    

if (isset($_GET['project_id'])) {
    $project_id = mysqli_escape_string($conn, $_GET['project_id']);

    // $query = "SELECT p.*, co.id version_id, co.init version_init, co.init_desc version_code, co.desc version_name FROM projects p LEFT JOIN code_desc co ON co.id = p.version_no WHERE project_id = '$project_id '";


    $query = "SELECT p.*, p.status status_id, co_sta.desc status_name, co.id version_id, co.init version_init, co.init_desc version_code, co.desc version_name, p.comment , p.comment_by comment_by_id, CONCAT(u.f_name, ' ', u.l_name) comment_by_name, CONCAT(u_a.f_name, ' ', u_a.l_name) approved_by_name FROM projects p LEFT JOIN code_desc co ON co.id = p.version_no LEFT JOIN code_desc co_sta ON co_sta.id = p.status LEFT JOIN users u ON u.id = p.comment_by LEFT JOIN users u_a ON u_a.id = p.approved_by  WHERE project_id = '$project_id'";

    $result = mysqli_query($conn, $query);
    
    $num = mysqli_num_rows($result); 
    

    $projects_tasks= array();
    // $task_arr = array(
        
    // );
    // $projects_wit_tasks = array();
    
    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $projects_tasks[] =  array(
                'project_id' => $row['project_id'],
                "version_no"   => $row['version_no'],
                "version_name"   => $row['version_name'],
                "name" => $row['name'],
                "description" => $row['description'],
                "is_approved" => $row['is_approved'],
                "approved_by_id" => $row['approved_by'],
                "approved_by_name" => $row['approved_by_name'],
                "project_status_id" => $row['status_id'],
                "project_status" => $row['status_name'],
                "department_id" => $row['dept_id'],
                "department" => get_department_name($row['dept_id'], $conn), 
                "comment" => $row['comment'],
                "comment_by_id" => $row['comment_by_id'], 
                "comment_by_name" => $row['comment_by_name'], 
                "start_date" => $row['start_date'],
                "end_date" => $row['end_date'],
                "tasks" => get_all_tasks($row['project_id'], $conn)
            );
        }
    
        
        $message = json_encode(
            array(
                'message' => 'Great here are your data',
                'status' => 'success',
                'data' => $projects_tasks,
                'project_avg_percentage' => project_avg_percentage($project_id, $conn)
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
    
}