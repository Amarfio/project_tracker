<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
require_once 'connect.php';


function project_avg_percentage($project_id, $conn){

    // $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE t.project_id = '$project_id'";
    $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE t.project_id = '$project_id' AND t.status <> 60";
    $result = mysqli_query($conn, $query);
   $row = mysqli_fetch_array($result);
   return $row['project_average_completion'];

}


//method to get the number of tasks for project
function noOfTasks($project_id, $conn){

    $query = "SELECT COUNT(task_id) totalTasks FROM tasks where project_id = '$project_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);

    return $row['totalTasks'];
}

//function to get the count of all projects overdue
function count_overdue_projects($conn, $department_id){
    $query_total_overdue = "SELECT COUNT(project_id) total_overdue FROM projects WHERE NOW()>end_date AND dept_id = '$department_id'";
    $result = mysqli_query($conn, $query_total_overdue);
    $num = mysqli_num_rows($result);
    return $num;
}

//function to get the query of all projects overdue
function query_overdue_projects($conn, $department_id){
    // $query_overdue = "SELECT * FROM projects WHERE NOW()>end_date AND dept_id = '$department_id'";
    $query_overdue = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department,DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id  LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (status = 85 AND NOW()>end_date) AND pro.dept_id = 106 ORDER BY pro.project_id DESC";
    $result = mysqli_query($conn, $query_overdue);
    $num = mysqli_num_rows($result);
    $projects_tasks = array();

    if ($num > 0 ){

        while ($row = mysqli_fetch_assoc($result)){
            
            $projects_tasks[] = array(
                'project_id' => $row['project_id'],
                "version_id" => $row['version_id'],
                "version" => $row['status_id'],
                "department_id" => $row['department_id'],
                "completion" => project_avg_percentage($row['project_id'], $conn),
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

function query_total_project($conn, $department_id){ 
    // $query_total_project = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id  LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE  pro.dept_id = '$department_id' ORDER BY pro.project_id DESC";
    $query_total_project = "SELECT pro.*, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department,DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id  LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE  pro.dept_id = '$department_id' AND pro.is_archive = 0 ORDER BY pro.project_id DESC";

    $result = mysqli_query($conn, $query_total_project);
    $num = mysqli_num_rows($result);
    $projects_tasks= array();
    // echo $num;

    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {

            $daysProper = $row['no_of_days'];
            if ($daysProper < 0){
                $daysProper = 0 . " days";
            }
            else{
                $daysProper = $daysProper . " days";
            }

            //code to get the completed projects then make the days dash
            $completionRate = project_avg_percentage($row['project_id'], $conn);

            if( $completionRate == 100){
                $daysProper = "-";
            }

            
            $startDate = strtotime($row['start_date']);
            $newStartDate = date("d-M-Y", $startDate);

            $endDate = strtotime($row['end_date']);
            $newEndDate = date("d-M-Y", $endDate);

            $projects_tasks[] =  array(
                'project_id' => $row['project_id'],
                "version_id"   => $row['version_id'],
                "version"   => $row['version'],
                "status_id"   => $row['status_id'],
                "status_desc"   => $row['status_desc'],
                "department_id"   => $row['department_id'],
                "department"   => $row['department'],
                "owner" => $row['m_o'],
                "completion"   => project_avg_percentage($row['project_id'], $conn),
                "name" => $row['name'],
                "start_date" => $newStartDate,
                "end_date" => $newEndDate,
                "age"=>$daysProper,
                "no_of_tasks" => noOfTasks($row['project_id'], $conn)
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

  

  function query_project_by_status ($status_id, $department_id){

    //   $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE pro.status = '$status_id' AND pro.dept_id = '$department_id' ORDER BY pro.project_id DESC";
        // $query_project_by_status = "";
        $query_project_by_status = "SELECT pro.*, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE pro.status = '$status_id' AND pro.dept_id = '$department_id' AND pro.is_archive = 0 ORDER BY pro.project_id DESC";


      // check if the status is overdue the codesc value for overdue is 116
      if($status_id == 116){
        $query_project_by_status = "SELECT pro.*, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE (CURRENT_DATE > end_date AND pro.is_approved=1) AND (SELECT AVG(t.completion)<100 from tasks t WHERE t.project_id = pro.project_id) AND pro.dept_id = '$department_id' AND pro.is_archive = 0 ORDER BY pro.project_id DESC";
 
      }
      elseif($status_id ==85){
        $query_project_by_status = "SELECT pro.*, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE (pro.is_approved=1) AND (pro.dept_id = '$department_id' AND pro.is_archive = 0) ORDER BY pro.project_id DESC";
     
      }
    //   elseif($status_id == 88) {
    //     $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (CURRENT_DATE > end_date AND pro.is_approved=1) AND (SELECT AVG(t.completion)=100 from tasks t WHERE t.project_id = pro.project_id) AND pro.dept_id = '$department_id' ORDER BY pro.project_id DESC";
    //   }
    //   else{
          
    //     $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE pro.status = '$status_id' AND pro.dept_id = '$department_id' ORDER BY pro.project_id DESC";
    //   }

    return $query_project_by_status;
  }



  


function result_from_query ($conn, $query){
    
    $result = mysqli_query($conn, $query); 

    $num = mysqli_num_rows($result);
    $tast_arr = array();
    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {

            $daysProper = $row['no_of_days'];
            if ($daysProper < 0){
                $daysProper = 0;
            }
                $projects_tasks[] =  array(
                'project_id' => $row['project_id'],
                "version_id"   => $row['version_id'],
                "status_id"   => $row['status_id'],
                "status_desc"   => $row['status_desc'],
                "version"   => $row['version'],
                "department_id"   => $row['department_id'],
                "department"   => $row['department'],
                "owner" => $row['m_o'],
                "completion"   => project_avg_percentage($row['project_id'], $conn),
                "name" => $row['name'],
                "start_date" => $row['start_date'],
                "end_date" => $row['end_date'],
                "age"=>$daysProper
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


if ( isset($_GET['status_id']) && isset($_GET['department_id'])) {
     $status_id = mysqli_escape_string($conn, $_GET['status_id']);
     $department_id = mysqli_escape_string($conn, $_GET['department_id']);
    
     result_from_query ($conn, query_project_by_status ($status_id, $department_id));

     //if statement to check if an overdue status has been passed...


}else {

 $department_id = mysqli_escape_string($conn, $_GET['department_id']);
    query_total_project ($conn, $department_id);
    // count_overdue_projects($conn, $department_id);
}
