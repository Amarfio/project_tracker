<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
require_once 'connect.php';
require_once 'getWorkingDays.php';


function project_avg_percentage($project_id, $conn){

    // $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE t.project_id = '$project_id'";
    $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE t.project_id = '$project_id' AND t.status <> 60";
    $result = mysqli_query($conn, $query);
   $row = mysqli_fetch_array($result);
   return $row['project_average_completion'];

}

//function to get the count of all projects overdue
function count_overdue_projects($conn, $department_id, $user_id){
    $query_total_overdue = "SELECT COUNT(project_id) total_overdue FROM projects WHERE NOW()>end_date AND dept_id = '$user_id'";
    $result = mysqli_query($conn, $query_total_overdue);
    $num = mysqli_num_rows($result);
    return $num;
}

//function to get the query of all projects overdue
function query_overdue_projects($conn, $user_id){
    // $query_overdue = "SELECT * FROM projects WHERE NOW()>end_date AND dept_id = '$user_id'";
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

//method to get the number of tasks for project
function noOfTasks($project_id, $conn){

    $query = "SELECT COUNT(task_id) totalTasks FROM tasks where project_id = '$project_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);

    return $row['totalTasks'];
}

function query_total_project($conn, $user_id){ 
    // $query_total_project = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id  LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE  pro.dept_id = '$user_id' ORDER BY pro.project_id DESC";
    $query_total_project = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department,DATEDIFF(NOW(), pro.start_date) AS no_of_days, DATEDIFF(pro.end_date, NOW()) AS daysToEnd, DATEDIFF(NOW(), pro.end_date) AS daysOverdue FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE  pro.owner = '$user_id' AND pro.is_archive = 0 ORDER BY pro.project_id DESC";

    $result = mysqli_query($conn, $query_total_project);
    $num = mysqli_num_rows($result);
    $projects_tasks= array();
    // echo $num;

    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            //today's date is the end date
            $endDate = date("Y-m-d");
            $startDate = $row['start_date'];
            $numberOfDays = ceil(getWorkingDays($startDate, $endDate));
            // json_encode($numberOfDays);
            // die();
            // $daysProper = $row['no_of_days'];
            $daysProper = $numberOfDays;

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

            //code to get the number of tasks for a project
            $noOfTasks = noOfTasks($row['project_id'], $conn);

            //code to get the number of days for a project to end
            $daysToEndProject = $row['daysToEnd'];

            //variable to store number
            $overdueDays = 0;

            $days="";

            if($daysToEndProject < 0 && $row['status_id'] != 88){
                $overdueDays = abs($daysToEndProject);
                $daysToEndProject = "overdue";
                
            }
            else if($row['status_id']==88){
                $days = '-';
            }
            else{
                $daysToEndProject = $daysToEndProject . " days";
            }
            // echo json_encode($noOfTasks);

            //code to get the number of days a project has been overdue
            $daysOverdue = $row['daysOverdue'];

            // if($daysOverdue < 0){
            //     $daysOverdue = "-";
            // }else{
            //     $daysOverdue = $daysOverdue . " days";
            // }

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
                "no_of_tasks"=>$noOfTasks,
                "attach" => $row['attach'], 
                "days_to_end" => $daysToEndProject,
                "days_overdue" => $daysOverdue,
                "overdue_days"=>$overdueDays,
                "days" => $days
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

  

  function query_project_by_status ($status_id, $user_id){

    //   $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE pro.status = '$status_id' AND pro.dept_id = '$user_id' ORDER BY pro.project_id DESC";
        // $query_project_by_status = "";
        $query_project_by_status = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE pro.status = '$status_id' AND pro.owner = '$user_id' AND pro.is_archive = 0 ORDER BY pro.project_id DESC";


      // check if the status is overdue the codesc value for overdue is 116
      if($status_id == 116){
        // $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (CURRENT_DATE > end_date AND pro.is_approved=1) AND (SELECT AVG(t.completion)<100 from tasks t WHERE t.project_id = pro.project_id) AND pro.owner = '$user_id' ORDER BY pro.project_id DESC";
        $query_project_by_status = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, 'overdue' status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE (CURRENT_DATE > end_date AND pro.is_approved=1) AND (SELECT AVG(t.completion)<100 AND pro.is_archive = 0 from tasks t WHERE t.project_id = pro.project_id) AND pro.owner = '$user_id' ORDER BY pro.project_id DESC";
 
      }
      //check if the status is 
      else if($status_id ==85){
        $query_project_by_status = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE (pro.is_approved=1) AND pro.owner = '$user_id' AND pro.is_archive = 0 ORDER BY pro.project_id DESC";
     
      }
      //check if the status is priority the codesc value for priority is 126
      else if($status_id == 126){
        // $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (CURRENT_DATE > end_date AND pro.is_approved=1) AND (SELECT AVG(t.completion)<100 from tasks t WHERE t.project_id = pro.project_id) AND pro.owner = '$user_id' ORDER BY pro.project_id DESC";
        $query_project_by_status = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE (pro.priority=73) AND pro.owner = '$user_id' AND pro.is_archive = 0 ORDER BY pro.project_id DESC";

      }
    //   elseif($status_id == 88) {
    //     $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE (CURRENT_DATE > end_date AND pro.is_approved=1) AND (SELECT AVG(t.completion)=100 from tasks t WHERE t.project_id = pro.project_id) AND pro.dept_id = '$user_id' ORDER BY pro.project_id DESC";
    //   }
    //   else{
          
    //     $query_project_by_status = "SELECT pro.*, co.id version_id, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status WHERE pro.status = '$status_id' AND pro.dept_id = '$user_id' ORDER BY pro.project_id DESC";
    //   }

    return $query_project_by_status;
  }

  //method to get projects within the specified date range and status as well...
  function query_by_status_and_date($user_id, $status_id, $start_date, $end_date){
    // echo json_encode($user_id . " " . $status_id . " " . $start_date . " " . $end_date);
    // die();
    // $query_by_status_and_date = "SELECT * FROM projects WHERE (start_date >= '2022-02-02' AND end_date <= '2022-03-02') AND status = 88";
    // $query_by_status_and_date = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE pro.status = '$status_id' AND pro.owner = '$user_id' AND pro.is_archive = 0 AND (start_date >= '2022-02-02' AND end_date <= '2022-03-02') ORDER BY pro.project_id DESC";
    // $query_by_status_and_date = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE pro.status = '$status_id' AND pro.owner = '$user_id' AND pro.is_archive = 0 AND (pro.start_date >= '$start_date' AND pro.end_date <= '$end_date') ORDER BY pro.project_id DESC";
    $query_by_status_and_date = "";

    if($user_id == 'undefined' && $status_id =='undefined'){
        // echo (json_encode("adey here"));
        // die();
        $query_by_status_and_date = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE pro.is_archive = 0 AND (pro.start_date >= '$start_date' AND pro.end_date <= '$end_date') ORDER BY pro.project_id DESC";    
    }
    else if($status_id == 'undefined'){
        $query_by_status_and_date = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE pro.is_archive = 0 AND (pro.start_date >= '$start_date' AND pro.end_date <= '$end_date') ORDER BY pro.project_id DESC";
    }
    else if($user_id == 'undefined'){
        $query_by_status_and_date = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE pro.is_archive = 0 AND pro.status= '$status_id' AND (pro.start_date >= '$start_date' AND pro.end_date <= '$end_date') ORDER BY pro.project_id DESC";
    }
    else {

        //code to bring all projects that are overdue....
        if ($status_id == 116) {
            $query_by_status_and_date = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, 'overdue' status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE (CURRENT_DATE > end_date AND pro.is_approved=1) AND (SELECT AVG(t.completion)<100 AND pro.is_archive = 0 from tasks t WHERE t.project_id = pro.project_id) AND pro.owner = '$user_id' AND (pro.start_date >= '$start_date' AND pro.end_date <= '$end_date') ORDER BY pro.project_id DESC";
        }
        //code to check for approved projects...
        else if($status_id == 85 ){
            $query_by_status_and_date = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, co_p_status.desc status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE (pro.is_approved=1) AND pro.owner = '$user_id' AND pro.is_archive = 0 AND (pro.start_date >= '$start_date' AND pro.end_date <= '$end_date') ORDER BY pro.project_id DESC";
        }
        else{
            $query_by_status_and_date = "SELECT pro.*, co.id version_id, CONCAT(proj_owner.f_name,' ', proj_owner.l_name) as m_o, co.desc version, pro.status status_id, 'overdue' status_desc, co_dept.id department_id, co_dept.desc department, DATEDIFF(NOW(), pro.start_date) AS no_of_days FROM projects pro LEFT JOIN code_desc co ON pro.version_no = co.id LEFT JOIN code_desc co_dept ON pro.dept_id = co_dept.id LEFT JOIN code_desc co_p_status ON co_p_status.id = pro.status LEFT JOIN users proj_owner ON proj_owner.id = pro.owner WHERE (CURRENT_DATE > end_date AND pro.is_approved=1) AND (SELECT AVG(t.completion)<100 AND pro.is_archive = 0 from tasks t WHERE t.project_id = pro.project_id) AND pro.owner = '$user_id' AND pro.status='$status_id' AND (pro.start_date >= '$start_date' AND pro.end_date <= '$end_date') ORDER BY pro.project_id DESC";
        }
    }
    
    // echo $query_by_status_and_date;
    // die;

    return $query_by_status_and_date;
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

            $startDate = strtotime($row['start_date']);
            $newStartDate = date("d-M-Y", $startDate);

            $endDate = strtotime($row['end_date']);
            $newEndDate = date("d-M-Y", $endDate);

            $noOfTasks = noOfTasks($row['project_id'], $conn);
                $projects_tasks[] =  array(
                'project_id' => $row['project_id'],
                "version_id"   => $row['version_id'],
                "status_id"   => $row['status_id'],
                "status_desc"   => $row['status_desc'],
                "version"   => $row['version'],
                "department_id"   => $row['department_id'],
                "department" => $row['department'],
                "owner" => $row['m_o'],
                "completion"   => project_avg_percentage($row['project_id'], $conn),
                "name" => $row['name'],
                "start_date" => $newStartDate,
                "end_date" => $newEndDate,
                "age"=>$daysProper,
                "no_of_tasks"=>$noOfTasks,
                "attach"=>$row['attach']
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

//method to get user id by the email provided
function getUserIdByEmail($email)
{
    global $conn;
    $queri = "SELECT id FROM users WHERE email= '$email' LIMIT 1";
    $result = mysqli_query($conn, $queri);
    // return the username
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['id'] ;
    // echo json_encode($developer_email); die();
    return $user_id;

}


// function function_name (query, connection, ..) 


if ( isset($_GET['status_id']) && isset($_GET['user_id'])) {
     $status_id = mysqli_escape_string($conn, $_GET['status_id']);
     $user_id = mysqli_escape_string($conn, $_GET['user_id']);
     $start_date = mysqli_escape_string($conn, $_GET['start_date']);
     $end_date = mysqli_escape_string($conn, $_GET['end_date']);

     $request = array('status_id'=>$status_id, 'user_id'=> $user_id, 'start_date'=> $start_date, 'end_date'=> $end_date);
    // echo json_encode($request);
    // die();
    
     if(isset($_GET['start_date']) && isset($_GET['end_date'])){

            // $status_id = mysqli_escape_string($conn, $_GET['status_id']);
            // $user_id = mysqli_escape_string($conn, $_GET['user_id']);


        // echo json_encode($status_id . " " . $user_id . " " . $start_date . " " . $end_date);
        // die();
        result_from_query($conn, query_by_status_and_date($user_id, $status_id, $start_date, $end_date));
     }
     else{
        result_from_query ($conn, query_project_by_status ($status_id, $user_id));
     }
     

     //if statement to check if an overdue status has been passed...


} 
// elseif ((isset($_GET['status_id']) && isset($_GET['user_id'])) && (isset($_GET['start_date']) && isset($_GET['end_date']))) {


//     die();

    
// }

else if(isset($_GET['email'])){
    $email = mysqli_escape_string($conn, $_GET['email']);

    //get user id using the email from the users table
    $user_id = getUserIdByEmail($email);
    
    // echo($user_id); die();
    //use the user id to get all projects for that developer
    query_total_project($conn, $user_id);

}

else {

 $user_id = mysqli_escape_string($conn, $_GET['user_id']);
    query_total_project ($conn, $user_id);
    // count_overdue_projects($conn, $user_id);
}
