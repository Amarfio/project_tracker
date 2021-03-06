<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';

function query_total_tasks(){
    $query_total_tasks = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, t.assigned_to as assigned_to_id, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, t.assigned_by as assigned_by_id, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority,c_sta.id status_id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id 
    ORDER BY t.task_id  DESC";

    return $query_total_tasks;
}

  

  function query_task_by_status ($status_id){

      $query_task_by_status = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, t.assigned_to as assigned_to_id, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, t.assigned_by as assigned_by_id, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE c_sta.id = '$status_id' AND pro.is_approved = 1
    ORDER BY t.task_id  DESC";
    
    //code to check for the overdue tasks
    if($status_id == 117){
    //     $query_task_by_status = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, t.assigned_to as assigned_to_id, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, t.assigned_by as assigned_by_id, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE t.overdue = 1 AND pro.is_approved = 1
    // ORDER BY t.task_id  DESC";
        $query_task_by_status = "SELECT * FROM `vw_overdue_tasks`";
    }
    //code to check for all the tasks that are ready for testing 
    if($status_id == 118){
        $query_task_by_status = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, t.assigned_to as assigned_to_id, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, t.assigned_by as assigned_by_id, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE ready_4_test = 1 AND (t.completion >=80 AND t.completion < 100) AND pro.is_approved = 1
        ORDER BY t.task_id  DESC";
    }

    return $query_task_by_status;
  }

//   $query_task_by_status = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE u_to.id = 1 AND c_sta.id = 58
//     ORDER BY t.task_id  ASC";

  
  function query_task_by_is_approved ($is_approved){

    //   $query_task_by_is_approved = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, t.assigned_to as assigned_to_id, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, t.assigned_by as assigned_by_id, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE  pro.is_approved = '$is_approved' ORDER BY t.task_id  DESC";

    $query_task_by_is_approved = "";

    if($is_approved==1){
        $query_task_by_is_approved = "SELECT * FROM `vw_approved_tasks_last_update` ORDER BY task_id DESC";
    }
    else if ($is_approved==0){
        $query_task_by_is_approved = "SELECT * FROM `vw_unapproved_tasks` ORDER BY task_id DESC";
    }
    
    return $query_task_by_is_approved;
  }


  
//   function query_task_by_approved($user_id, $is_approved){
//         $query_task_by_approved = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE u_to.id = '$user_id' AND pro.is_approved = 1 ORDER BY t.task_id  ASC";

//     return $query_task_by_approved;
//   }



function result_from_query ($conn, $query){
    
    $result = mysqli_query($conn, $query); 

    $num = mysqli_num_rows($result);
    $task_arr = array();
    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
           
            $task_id_base64Encode = array(
                'task_id_base64Encode' => base64_encode($row['task_id']) ,
            );
            $task_arr[] = $row;

            array_push($task_arr[0], $task_id_base64Encode);
           
        }
            
        $message = json_encode(
            array(
                'message' => 'Great here are your data',
                'status' => 'success',
                'data' => $task_arr
            )
        );
        exit($message);

        // echo json_encode($task_arr);
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


// function function_name (query, connection, ..) 

if ( !isset($_GET['status_id']) &&  !isset($_GET['is_approved'])) {
    // echo '<br>'. $user_id; 
    // echo '<br>'. $status_id; 

    result_from_query ($conn, query_total_tasks ());
}
elseif (isset($_GET['status_id']) ) {
    $status_id = mysqli_escape_string($conn, $_GET['status_id']);
    // echo '<br>'. $user_id; 
    // echo '<br>'. $status_id; 

    result_from_query ($conn, query_task_by_status ($status_id));
}
elseif (isset($_GET['is_approved'])) {
    $is_approved = mysqli_escape_string($conn, $_GET['is_approved']);
    // echo '<br>'. $user_id; 
    // echo '<br>'. $status_id; 

    result_from_query ($conn, query_task_by_is_approved ($is_approved));
}

// result_from_query ($conn, $query_total_tasks);


