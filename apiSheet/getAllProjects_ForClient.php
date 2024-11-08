<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
require_once 'connect.php';
require_once 'functions/formatDate.php';
require_once 'functions/noOfTasks.php';

// echo("here adey"); die();

function project_avg_percentage($project_id, $conn){

    // $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE t.project_id = '$project_id'";
    $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE t.project_id = '$project_id' AND t.status <> 60";
    $result = mysqli_query($conn, $query);
   $row = mysqli_fetch_array($result);
   return $row['project_average_completion'];

}


function query_total_project($conn, $client_id){ 
    // echo $client_id; die();
    // $query_total_project = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id  LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status ORDER BY pro.project_id DESC";
    $query_total_project = "SELECT pro.*, co.id version_id, CONCAT(pro_owner.f_name,' ',pro_owner.l_name) AS project_owner, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department,cli_p.name client_name, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN clients cli_p ON cli_p.client_id = pro.client LEFT JOIN users pro_owner ON pro.owner = pro_owner.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id  LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status where pro.is_archive = 0 AND pro.client = '$client_id' ORDER BY pro.project_id DESC";

    // echo($query_total_project); die();

    $result = mysqli_query($conn, $query_total_project);
    $num = mysqli_num_rows($result);
    $projects_tasks= array();
    // echo $num;

    //code to get the age of the project based on the project start date
    //if the days is in negative make it zero.
    
    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {

            $daysProper = $row['no_of_days'];
            if ($daysProper < 0){
                $daysProper = 0 ." days";
            } 
            else{
                $daysProper = $daysProper ." days";
            }
            //code to get the completed then make the days dash
            $endDate = $row['end_date'];
            $completionRate = project_avg_percentage($row['project_id'], $conn);
            $todaysDate = date("M d, Y");

            $startDate = strtotime($row['start_date']);
            $newStartDate = date("d-M-Y", $startDate);

            $endDate = strtotime($row['end_date']);
            $newEndDate = date("d-M-Y", $endDate);

            if( $completionRate == 100){
                $daysProper = "-";
            }
            
           
            $projects_tasks[] =  array(
                'project_id' => $row['project_id'],
                "version_id"   => $row['version_id'],
                "client" => $row['client_name'],
                "version"   => $row['version'],
                "status_id"   => $row['status_id'],
                "status_desc"   => $row['status_desc'],
                "attach" => $row['attach'],
                "department_id"   => $row['department_id'],
                "department"   => $row['department'],
                "completion"   => project_avg_percentage($row['project_id'], $conn),
                "name" => $row['name'],
                "hash_tag" => $row['hash_tag'],
                "owner" => $row['project_owner'],
                "start_date" => $newStartDate,
                "end_date" => $newEndDate,
                "age"=>$daysProper,
                "no_of_tasks"=>noOfTasks($row['project_id'], $conn),
                "no_of_overdue_tasks"=>noOfTasksOverdue($row['project_id'], $conn)

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

  

  function query_project_by_status ($status_id, $client_id){

    //   $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE pro.status = '$status_id' ORDER BY pro.project_id DESC";
      $query_project_by_status = "SELECT pro.*, co.id version_id, CONCAT(pro_owner.f_name,' ',pro_owner.l_name) AS project_owner, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, cli_p.name client_name, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN users pro_owner ON pro.owner = pro_owner.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN clients cli_p ON cli_p.client_id = pro.client LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE pro.status = '$status_id' AND pro.is_archive = 0 AND pro.client = '$client_id' ORDER BY pro.project_id DESC";

      if($status_id == 116){
        //code for overdue projects thus id 116 in the codesc table
        // $query_project_by_status = "SELECT pro.*, co.id version_id, CONCAT(pro_owner.f_name,' ',pro_owner.l_name) AS project_owner, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN users pro_owner ON pro.owner = pro_owner.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (CURRENT_DATE > end_date AND pro.is_approved=1) AND(SELECT AVG(t.completion)<100 from tasks t WHERE t.project_id = pro.project_id) ORDER BY pro.project_id DESC";
        $query_project_by_status = "SELECT pro.*, co.id version_id, CONCAT(pro_owner.f_name,' ',pro_owner.l_name) AS project_owner, co.desc version, pro.status status_id, 'overdue' status_desc, co_dept.id department_id, co_dept.desc department, cli_p.name client_name, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN users pro_owner ON pro.owner = pro_owner.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN clients cli_p ON cli_p.client_id = pro.client LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (CURRENT_DATE > end_date AND pro.is_approved=1 AND pro.is_archive = 0) AND(SELECT AVG(t.completion)<100 AND pro.client = '$client_id' from tasks t WHERE t.project_id = pro.project_id) ORDER BY pro.project_id DESC";
      }
    //   elseif($status_id == 88){
    //     //code for completed projects thats in id 88 in the codesc table 
    //     $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (pro.is_approved=1) AND (SELECT AVG(t.completion)=100 from tasks t WHERE t.project_id = pro.project_id) ORDER BY pro.project_id DESC";
    //   }
        elseif($status_id ==85){
        //code for scheduled projects thus id 86 in the codesc table 
        $query_project_by_status = "SELECT pro.*, co.id version_id, CONCAT(pro_owner.f_name,' ',pro_owner.l_name) AS project_owner, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, cli_p.name client_name, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN users pro_owner ON pro.owner = pro_owner.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN clients cli_p ON cli_p.client_id = pro.client LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (pro.is_approved=1 AND pro.is_archive = 0) AND pro.client = '$client_id' ORDER BY pro.project_id DESC";
     }
     elseif($status_id ==131){
        //code for scheduled projects thus id 86 in the codesc table 
        $query_project_by_status = "SELECT pro.*, co.id version_id, CONCAT(pro_owner.f_name,' ',pro_owner.l_name) AS project_owner, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, cli_p.name client_name, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN users pro_owner ON pro.owner = pro_owner.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN clients cli_p ON cli_p.client_id = pro.client LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (pro.is_archive = 1) AND pro.client = '$client_id' ORDER BY pro.project_id DESC";
     }

    return $query_project_by_status;
  }

//   $query_task_by_status = "SELECT t.task_id as task_id, pro.project_id project_id , pro.name project_name, t.description as task_description,t.start_date, t.end_date, pro.dept_id department_id, c_dpt.desc department, t.completion, cl.name as client, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, c_pri.desc as priority, c_sta.id status_id, c_sta.id, c_sta.desc as status  FROM tasks t LEFT JOIN users u_by ON u_by.id = t.assigned_by  LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id LEFT JOIN projects pro ON pro.project_id = t.project_id LEFT JOIN code_desc c_dpt ON c_dpt.id = pro.dept_id WHERE u_to.id = 1 AND c_sta.id = 58
//     ORDER BY t.task_id  ASC";

  


function result_from_query ($conn, $query){
    
    $result = mysqli_query($conn, $query); 

    $num = mysqli_num_rows($result);
    $tast_arr = array();
    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {

            $daysProper = $row['no_of_days'];
            if ($daysProper < 0){
                $daysProper = 0 ." days";
            } 
            else{
                $daysProper = $daysProper ." days";
            }
            //code to get the completed then make the days dash
            $endDate = $row['end_date'];
            $completionRate = project_avg_percentage($row['project_id'], $conn);
            $todaysDate = date("M d, Y");

            $startDate = strtotime($row['start_date']);
            $newStartDate = date("d-M-Y", $startDate);

            $endDate = strtotime($row['end_date']);
            $newEndDate = date("d-M-Y", $endDate);

            if( $completionRate == 100){
                $daysProper = "-";
            }
                $projects_tasks[] =  array(
                'project_id' => $row['project_id'],
                "version_id"   => $row['version_id'],
                "client" => $row['client_name'],
                "status_id"   => $row['status_id'],
                "status_desc"   => $row['status_desc'],
                "attach"=> $row['attach'],
                "version"   => $row['version'],
                "department_id"   => $row['department_id'],
                "department"   => $row['department'],
                "completion"   => project_avg_percentage($row['project_id'], $conn),
                "name" => $row['name'],
                "hash_tag" => $row['hash_tag'],
                "owner" => $row['project_owner'],
                "start_date" => $newStartDate,
                "end_date" => $newEndDate,
                "age"=>$daysProper,
                "no_of_tasks"=>noOfTasks($row['project_id'], $conn),
                "no_of_overdue_tasks"=>noOfTasksOverdue($row['project_id'], $conn)
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


if ( isset($_GET['status_id'])) {
     $status_id = mysqli_escape_string($conn, $_GET['status_id']);
     $client_id = mysqli_escape_string($conn, $_GET['client_id']);
    // echo '<br>'. $user_id; 
    // echo '<br>'. $status_id; 
     result_from_query ($conn, query_project_by_status ($status_id, $client_id));

}else {

    $client_id = mysqli_escape_string($conn, $_GET['client_id']);

    query_total_project ($conn, $client_id);

    result_from_query($conn, query_total_project($conn, $client_id));
}
