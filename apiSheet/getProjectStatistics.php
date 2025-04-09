<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';
require_once 'functions/noOfConflictingTasks.php';
require_once 'functions/usableFunctions.php';



    //method to update project status to completed
    function changeStatusToCompleted($project_id, $conn){
        $query = "UPDATE `projects` SET status = 88 where project_id = '$project_id'";
        // echo($query); die();
        mysqli_query($conn, $query);
        // echo "done updating the status to completed"+$result;
    }

    //method 
    function getTotal($firstValue, $secondValue){
      return (int)$firstValue + (int)$secondValue;
    }

    function project_avg_percentage($project_id, $conn){

        $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE (t.status <> 137) AND t.project_id = '$project_id'";
        // $query = "SELECT AVG(ALL t.completion) project_average_completion FROM tasks t WHERE t.project_id = '$project_id'";
        $result = mysqli_query($conn, $query);
       $row = mysqli_fetch_array($result);
       $per = intval($row ['project_average_completion']);

       if ($per == 100){
            changeStatusToCompleted($project_id, $conn);
       }
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
        
    
        // $query = "SELECT t.task_id, t.description, t.project_id, t.start_date, t.end_date, cl.client_id client_id, cl.name client, CONCAT(u_to.f_name, ' ', u_to.l_name) assigned_to, u_to.id assigned_to_id, CONCAT(u_by.f_name, ' ', u_by.l_name) assigned_by, CONCAT(u_ap.f_name, ' ', u_ap.l_name) approved_by, t.completion, cod_pri.id priority_id, cod_pri.desc priority, cod_sta.id status_id, cod_sta.desc status FROM tasks t LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN users u_by ON u_by.id = t.assigned_by LEFT JOIN users u_ap ON u_ap.id = t.approved_by LEFT JOIN code_desc cod_pri ON cod_pri.id = t.priority LEFT JOIN code_desc cod_sta ON cod_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id WHERE t.project_id = '$project_id' ORDER BY t.task_id DESC";
        
        $query = "select * from vw_tasks_under_project_by_id where project_id = '$project_id' ORDER BY task_id DESC";

        $result = mysqli_query($conn, $query);
    
        $num = mysqli_num_rows($result);
    
        while ($row = mysqli_fetch_assoc($result)) {
           
           $task_id_base64Encode = array(
                'task_id_base64Encode' => base64_encode($row['task_id']) 
            );
            $task_arr[] = $row;

            array_push($task_arr[0], $task_id_base64Encode);
        }

        for($i= 0; $i<count($task_arr); $i++){
            $taskConflictsNo = getNoOfConflictingTasks($task_arr[$i]['task_id'], $conn);
            // echo($taskConflictsNo); 
            $task_arr[$i]['noOfConflictsTask'] = $taskConflictsNo;
        }
        return $task_arr;
    }
    
    function get_projects_brought_forward_last_year($deptId, $conn){
        $query = "SELECT * FROM vw_projects_brot_forward_last_year WHERE dept_id = '$deptId'";
        // echo($query); die();
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
          $row = mysqli_fetch_assoc($result); // Fetch the actual row data
          return $row['projects_brought_forward_last_year'];
      }
  
      return 0;
    }

    function get_projects_brought_forward_last_month($deptId, $conn){
        $query = " SELECT * FROM vw_projects_brot_forward_last_month WHERE dept_id = '$deptId'";
        $result = mysqli_query($conn, $query);
        if($result && mysqli_num_rows($result)> 0){
          $row = mysqli_fetch_assoc($result); // Fetch the actual row data
          return $row['projects_brought_forward_last_month'];
        }
        return 0;        

    }

    function get_new_projets($deptId, $conn){
      $query = " SELECT * FROM vw_new_projects WHERE dept_id = '$deptId'";
      $result = mysqli_query($conn, $query);
      if($result && mysqli_num_rows($result)> 0){
        $row = mysqli_fetch_assoc($result); // Fetch the actual row data
        return $row['new_projects_this_month'];
      }
      return 0;        

  }

    function get_projects_exceeded_target($deptId, $conn){
      $query = " SELECT * FROM vw_projects_exceeded_targets_by_depts WHERE dept_id = '$deptId'";
      $result = mysqli_query($conn, $query);
      if($result && mysqli_num_rows($result)> 0){
        $row = mysqli_fetch_assoc($result); // Fetch the actual row data
        return $row['projects_exceeded_target'];
      }
      return 0; 
    }

    function get_projects_completed($deptId, $conn){
        $query = " SELECT * FROM SELECT * 
FROM projects 
WHERE 
    is_archive = 0 
    AND MONTH(completed_date) = MONTH(CURRENT_DATE()) 
    OR YEAR(completed_date) = YEAR(CURRENT_DATE) AND dept_id = '$deptId'";
        $result = mysqli_query($conn, $query);
        if($result && mysqli_num_rows($result)> 0){
          $row = mysqli_fetch_assoc($result); // Fetch the actual row data
          return $row['projects_exceeded_target'];
        }
        return 0; 
      }

if (isset($_GET['deptId'])) {
    $dept_id = mysqli_escape_string($conn, $_GET['deptId']);

    $query = "SELECT * FROM vw_project_details";
    
    $result = mysqli_query($conn, $query);
    
    $num = mysqli_num_rows($result); 

    // if deprt is set get the values for the statistics
    // else
    // get the all the departments and their corresponding statistics

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
                "client"=> $row['client_name'],
                "hash_tag"=> $row['hash_tag'],
                "attach" => $row['attach'],
                "is_approved" => $row['is_approved'],
                "approved_by_id" => $row['approved_by'],
                "approved_by_name" => $row['approved_by_name'],
                "created_at" => $row['created_at'],
                "project_status_id" => $row['status_id'],
                "project_status" => $row['status_name'],
                "owner_id" => $row['owner_id'],
                "project_owner" => $row['project_owner'],
                "sec_owner_id" => $row['s_owner'],
                "sec_owner" => $row['s_own'],
                "i_pern" => $row['i_pern'],
                "department_id" => $row['dept_id'],
                "department" => get_department_name($row['dept_id'], $conn), 
                "comment" => $row['comment'],
                "comment_by_id" => $row['comment_by_id'], 
                "comment_by_name" => $row['comment_by_name'], 
                "posted_by_name" => $row['posted_by_name'],
                "start_date" => $row['start_date'],
                "end_date" => $row['end_date'],
                "approved_date" => $row['approved_date'],
                "date_of_completion"=> checkForCompletionDate($conn, $row['project_id']),
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
            'message' => 'Great here are your data',
            'status' => 'success',
            'data' => $projects_tasks,
            'project_avg_percentage' => project_avg_percentage($project_id, $conn)
        )
    );
    exit($message);
        
    }
    
}else{
        $query = "SELECT * FROM code_desc WHERE init = 'dpt' AND is_active = 1";
        $result = mysqli_query($conn, $query);
    
        $num = mysqli_num_rows($result); 
        $depts_array = array();

        if ($num > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
              $depts_array[] =  array(
                  'dept_id' => $row['id'],
                  "dept_name"   => $row['desc'],
                  "brought_forward_by_year" => get_projects_brought_forward_last_year($row['id'], $conn),
                  "new_projects" => get_new_projets($row['id'], $conn),
                  "brought_forward_by_month"=>get_projects_brought_forward_last_month($row['id'], $conn),
                  "completed_projects"=>get_projects_completed($row['id'], $conn),
                  "projects_exceeded_target_dates"=>get_projects_exceeded_target($row['id'], $conn),
                  "new_total"=> getTotal(get_projects_brought_forward_last_month($row['id'], $conn), get_new_projets($row['id'], $conn),
                  )

              );
          }
      
          
          $message = json_encode(
              array(
                  'message' => 'Great here are your data',
                  'status' => 'success',
                  'data' => $depts_array,
              )
          );
          exit($message);
      
          // echo json_encode($projects_tasks);
      } else {
              
        $message = json_encode(
          array(
              'message' => 'Great here are your data',
              'status' => 'success',
              'data' => $projects_tasks,
              'project_avg_percentage' => project_avg_percentage($project_id, $conn)
          )
      );
      exit($message);
          
      }
}