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


function query_status_projects($status_id, $conn){
    $query_status_projects = "SELECT pro.*, co.id version_id, co.desc version, co_dept.id department_id, co_dept.desc department FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id WHERE pro.status = '$status_id' ORDER BY pro.project_id DESC";

    $result = mysqli_query($conn, $query_status_projects);
    $num = mysqli_num_rows($result);
    $projects_tasks= array();
    // echo $num;

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
                "end_date" => $row['end_date']
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
        }

}

  

//   function query_task_by_status ($status_id){

//       $query_task_by_status = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE c_sta.id = '$status_id' AND pro.is_approved = 1
//     ORDER BY t.task_id  ASC";

//     return $query_task_by_status;
//   }

//   $query_task_by_status = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE u_to.id = 1 AND c_sta.id = 58
//     ORDER BY t.task_id  ASC";

  
  function query_projects_by_is_approved ($is_approved){

      $query_projects_by_is_approved = "SELECT pro.*, co.id version_id, co.desc version, co_dept.id department_id, co_dept.desc department FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id WHERE pro.is_approved = '$is_approved' ORDER BY pro.project_id DESC";

    return $query_projects_by_is_approved;
  }


  
//   function query_task_by_approved($user_id, $is_approved){
//         $query_task_by_approved = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE u_to.id = '$user_id' AND pro.is_approved = 1 ORDER BY t.task_id  ASC";

//     return $query_task_by_approved;
//   }



function result_from_query ($conn, $query){
    
    $result = mysqli_query($conn, $query); 

    $num = mysqli_num_rows($result);
    $tast_arr = array();
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
                "end_date" => $row['end_date']
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
        

        // echo json_encode($tast_arr);
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

if ( isset($_GET['status_id']) ) {
    $status_id = mysqli_escape_string($conn, $_GET['status_id']);
    // echo '<br>'. $user_id; 
    echo '<br>'. $status_id; 
  

    // query_status_projects ($conn);
}
