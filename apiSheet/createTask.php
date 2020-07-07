<?php

  
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



require_once 'connect.php';
require_once 'functions/get_IP_Location.php';
require_once 'functions/activity_logs.php';


function getUsernameById($user_id)
{
    global $conn;
    $queri = "SELECT email FROM users WHERE id= '$user_id' LIMIT 1";
    $result = mysqli_query($conn, $queri);
    // return the username
    $row = mysqli_fetch_assoc($result);
    $developer_email = $row['email'] ;
    return $developer_email;



}




$data = json_decode(file_get_contents("php://input")); 
if(
    isset($data) && isset($data->user_id) && isset($data->project_id) && isset($data->task_name) && isset($data->assigned_by) &&
    isset($data->assigned_to) && isset($data->t_start_date) && isset($data->t_end_date) && isset($data->p_start_date) && isset($data->p_end_date) &&  isset($data->priority)  
){
    // echo json_encode($data);

    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $project_id = mysqli_real_escape_string($conn, $data->project_id);
    $task_name = mysqli_real_escape_string($conn, $data->task_name);
    $assigned_by = mysqli_real_escape_string($conn, $data->assigned_by);
    $assigned_to = mysqli_real_escape_string($conn, $data->assigned_to);
    $client_id = mysqli_real_escape_string($conn, $data->client_id);
    $t_start_date = mysqli_real_escape_string($conn, $data->t_start_date);
    $t_end_date = mysqli_real_escape_string($conn, $data->t_end_date);
    $p_start_date = mysqli_real_escape_string($conn, $data->p_start_date);
    $p_end_date = mysqli_real_escape_string($conn, $data->p_end_date);
    $priority = mysqli_real_escape_string($conn, $data->priority);

    $start_date = date("Y-m-d", strtotime($t_start_date));
    $end_date = date("Y-m-d", strtotime($t_end_date));

    $p_start_date = date("Y-m-d", strtotime($p_start_date));
    $p_end_date = date("Y-m-d", strtotime($p_end_date));

    // if ($p_start_date > $t_start_date) {
    //       $message = json_encode(
    //             array(
    //                 'message' => 'Task start date can not be less than project start date',
    //                 'status' => 'failed'
    //             )
    //         );
    //         exit($message);
    
    // }
    // if ($t_end_date > $p_end_date) {
    //       $message = json_encode(
    //             array(
    //                 'message' => 'Task end date can not be greater than project end date',
    //                 'status' => 'failed'
    //             )
    //         );
    //         exit($message);
    
    // }

    // $status = mysqli_real_escape_string($conn, $data->status);
    // $doc = mysqli_real_escape_string($conn, $data->doc);
    // $doc_url = mysqli_real_escape_string($conn, $data->doc_url);
    // $ip_address = mysqli_real_escape_string($conn, $data->ip_address);
    // $location = mysqli_real_escape_string($conn, $data->location);
    $ip_address = 'DF45-123E-34E-24';
    $location = 'Accra Ghana'; 
 

    // $query = "INSERT INTO `projects` (`id`, `version_no`, `name`, `dept_id`, `posted_by`, `ip_address`, `location`, `start_date`, `end_date`) VALUES (NULL, '$version_no', '$name', '$dept_id', '$user_id', '$ip_address', '$location', '$start_date', '$end_date')";
    $query = "INSERT INTO `tasks` (`task_id`, `description`, `start_date`, `end_date`, `client_id`, `assigned_by`, `assigned_to`, `priority`, `project_id`, `ip_address`, `location`, `created_at`) VALUES (NULL, '$task_name', '$start_date', '$end_date', '$client_id', '$assigned_by', '$assigned_to', '$priority', '$project_id', '$ip_address', '$location', NOW())";

    $result = mysqli_query($conn, $query);

    if ($result == 1) {

        // $to =  getUsernameById($assigned_to);
        // $subject = "UNION SYSTEMS GLOBAL";
        // $txt = 'One new task has been assigned to you'; 
        // $headers = "UNION SYSTEMS GLOBAL" . "\r\n" .
        // "CC: usg@gmail.com";
    
        // mail($to,$subject,$txt,$headers);


        $task_id = mysqli_insert_id($conn); 
                            
   
        $message = json_encode(
            array(
                'message' => 'task created successfully',
                'status' => 'success',
                'data' => [
                    'task_name' => $task_name,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'assigned_by' => $assigned_by,
                    'assigned_to' => $assigned_to,
                    'priority' => $priority,
                    'percentage_completion' => '0%',
                    // 'documentation' => $doc,
                    // 'documentation_file' => $doc_url,
                    'implementation' => 'NO',
                    'is_approved' => 'NO'
                ],
                'task_id' => $task_id
            )
        );
            // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to create a task REF-0000' . $task_id.  ' | Details: ' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
          
        
        exit($message);

       
    } else {
            $user =  $user_id;
            $activity =  ' tried to create a task REF-0000' . $task_id;
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

        $message = json_encode(
             array(
                'message' => 'Failed to create task',
                'status' => 'failed'
            )
        );
        exit($message);
        
    }

}else{
    $message = json_encode(
        array(
            'message' => 'Invalid Request',
            'status' => 'failed'
        )
    );
    exit($message);
    
}

