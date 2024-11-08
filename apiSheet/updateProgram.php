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
require_once 'functions/response.php';


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

//code to check for earlier date
function checkForEarlierDate($newStartDate, $oldStartDate){
    $newDateTime = strtotime($newStartDate);
    $oldDateTime = strtotime($oldStartDate);

    return $newDateTime < $oldDateTime;
}

//code to check for later date
function checkForLaterDate($newDate, $oldDate){
    $newDateTime = strtotime($newDate);
    $oldDateTime = strtotime($oldDate);
    
    return $newDateTime > $oldDateTime;
}





$data = json_decode(file_get_contents("php://input")); 
if(
    isset($data) && isset($data->program_owner) && isset($data->program_id) && isset($data->program_name) && isset($data->description)  
){
    // echo json_encode($data); die();

     $user_id = mysqli_real_escape_string($conn, $data->posted_by);
     $program_id = mysqli_real_escape_string($conn, $data->program_id);
    //  $task_id = mysqli_real_escape_string($conn, $data->task_id);
     $program_name = mysqli_real_escape_string($conn, $data->program_name);
     $owner = mysqli_real_escape_string($conn, $data->program_owner);
     $description = mysqli_real_escape_string($conn, $data->description);
    //  $client_id = mysqli_real_escape_string($conn, $data->client_id);
    //  $t_start_date = mysqli_real_escape_string($conn, $data->t_start_date);
    //  $t_end_date = mysqli_real_escape_string($conn, $data->t_end_date);
     $p_start_date = mysqli_real_escape_string($conn, $data->start_date);
     $p_end_date = mysqli_real_escape_string($conn, $data->end_date);
    //  $priority = mysqli_real_escape_string($conn, $data->priority);

    //  $start_date = date("Y-m-d", strtotime($t_start_date));
    //  $end_date = date("Y-m-d", strtotime($t_end_date));

     $p_start_date = date("Y-m-d", strtotime($p_start_date));
     $p_end_date = date("Y-m-d", strtotime($p_end_date));

    $ip_address = 'DF45-123E-34E-24';
    $location = 'Accra Ghana'; 
 

    
    //validating the dates entered
    //check if task start date is earlier than project start date
    // if(checkForEarlierDate($t_start_date, $p_start_date)){
    //     $message = response('Task start date cannot be earlier than project start date', 'failed', null);
    // }
    // //check if task end date is later than project end date
    // else if(checkForLaterDate($t_end_date, $p_end_date)){
    //     $message = response('Task end date cannot be later than project end date', 'failed', null);
    // }
    // //check if task end date is earlier than task start date
    // // else if(checkForEarlierDate($t_end_date, $t_start_date)){
    // else 
    if(checkForEarlierDate($p_end_date, $p_start_date)){
        $message = response('Program end date cannot be earlier than program start date', 'failed', null);
    }
    //check if task start date is later than task end date
    else if(checkForLaterDate($p_start_date, $p_end_date)){
        $message = response('Program start date cannot be later than program end date', 'failed', null);
    }else{

                // $query = "INSERT INTO `tasks` (`task_id`, `description`, `start_date`, `end_date`, `client_id`, `assigned_by`, `assigned_to`, `priority`, `project_id`, `ip_address`, `location`) VALUES (NULL, '$task_name', '$start_date', '$end_date', '$client_id', '$assigned_by', '$assigned_to', '$priority', '$project_id', '$ip_address', '$location')";
                $query = "UPDATE `programs` SET `name`='$program_name',`owner`='$owner', `description` = '$description', `start_date` = '$p_start_date', `end_date` = '$p_end_date', `updated_at` = NOW()   WHERE `programs`.`id` = '$program_id'";

                $result = mysqli_query($conn, $query);

                if ($result == 1) {

                            
                    $message = json_encode(
                        array(
                            'message' => 'program updated successfully',
                            'status' => 'success',
                            'data' => [
                                'program_name' => $program_name,
                                'start_date' => $p_start_date,
                                'end_date' => $p_end_date,
                            ],
                            'program_id' => $program_id
                        )
                    );

                                    
                        // LOG ACTIVITY   
                        $user =  $user_id;
                        $activity =  ' tried to modify a program PROG-0000' . $program_id.  ' | Details: ' . $message;
                        $status = 'success';
                        log_activity($conn, $user, $activity, $status, getSecurity());

                        // END LOG ACTIVITY
                        
                    exit($message);

                
                } else {
                                            
                        // LOG ACTIVITY
                                
                        $user =  $user_id;
                        $activity =  ' tried to modify a task REF-0000' . $task_id;
                        $status = 'failed';
                        log_activity($conn, $user, $activity, $status, getSecurity());

                        // END LOG ACTIVITY
                    
                    $message = json_encode(
                        array(
                            'message' => 'Failed to create task',
                            'status' => 'failed'
                        )
                    );
                    exit($message);
                    
                }
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

exit($message);

