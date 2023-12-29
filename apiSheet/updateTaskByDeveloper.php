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

$data = json_decode(file_get_contents("php://input"));
if (
    isset($data) && isset($data->user_id) && isset($data->task_id) && isset($data->taskStatus) && isset($data->ready_for_test) &&
    isset($data->percentage_completion)
) {
    // echo json_encode($data);

    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $task_id = mysqli_real_escape_string($conn, $data->task_id);
    $taskStatus = mysqli_real_escape_string($conn, $data->taskStatus);
    $ready_for_test = mysqli_real_escape_string($conn, $data->ready_for_test);
    $percentage_completion = mysqli_real_escape_string($conn, $data->percentage_completion);
    $dateOfCompletion = mysqli_real_escape_string($conn, $data->completionDate);
    $projectId = mysqli_real_escape_string($conn, $data->project_id);
    
    // $ip_address = 'DF45-123E-34E-24';
    // $location = 'Accra Ghana';

    if ($taskStatus == '') {
        $query = "UPDATE `tasks` SET `completion` = '$percentage_completion', `ready_4_test` = '$ready_for_test' WHERE `tasks`.`task_id` = '$task_id'";
    }else{
        $query = "UPDATE `tasks` SET `completion` = '$percentage_completion', `status` = '$taskStatus', `ready_4_test` = '$ready_for_test' WHERE `tasks`.`task_id` = '$task_id'";
    }


    $result = mysqli_query($conn, $query);

    //check and update the project to completed
    $queryP = "UPDATE projects SET status = 88, completed_date='$dateOfCompletion' WHERE projects.project_id = '$projectId' AND (SELECT AVG(t.completion)=100 from tasks t WHERE t.project_id ='$projectId' )";
        // echo($queryP); die();
        $resultP = mysqli_query($conn, $query);

    if ($result == 1) { 
        // $task_id = mysqli_insert_id($conn); 
        $message = json_encode( 
            array(
                'message' => 'task updated successfully',
                'status' => 'success',
                'data' => [
                    'task_id' => $task_id,
                    'taskStatus' => $taskStatus,
                    'ready_for_test' => $ready_for_test,
                    'percentage_completion' => $percentage_completion
                ],
                'task_id' => $task_id
            )
        );
                            // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to changed task status REF-0000' . $task_id. ':  ' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
        exit($message);
    } else {

        
             // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to change task status REF-0000' . $task_id;
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
            
        $message = json_encode(
            array(
                'message' => 'Failed to Update task',
                'status' => 'failed'
            )
        );
        exit($message);
    }
} else {
    $message = json_encode(
        array(
            'message' => 'Invalid Request',
            'status' => 'failed'
        )
    );
    exit($message);
}
