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
if(isset($data) && isset($data->comment) && isset($data->task_id)  && isset($data->posted_by) ){
    // echo json_encode($data);

    $comment = mysqli_real_escape_string($conn, $data->comment);
    $attach = mysqli_real_escape_string($conn, $data->attach);
    $task_id = mysqli_real_escape_string($conn, $data->task_id);
    $posted_by = mysqli_real_escape_string($conn, $data->posted_by);


    // $query = "INSERT INTO `clients` (`client_id`, `name`) VALUES (NULL, '$client')";
    $query = "INSERT INTO `comments` (`comment_id`, `comment`, `attach`, `task_id`, `posted_by`) VALUES (NULL, '$comment', '$attach', '$task_id', '$posted_by')";
    $result = mysqli_query($conn, $query);

    if ($result == 1) {
        $message = json_encode(
            array(
                'message' => 'Comment created successfully',
                'status' => 'success',
                'data' => [
                    'comment' => $comment,
                    'attach' => $attach,
                    'task_id' => $task_id, 
                    'posted_by' => $posted_by,
                ]
            )
        );
                    // LOG ACTIVITY
                    
            $user =  $posted_by;
            $activity =  ' tried to comment on task REF-0000' . $task_id. ':  ' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY

        exit($message);

    } else {

            
        $message = json_encode(
            array(
                'message' => 'Failed create Comment',
                'status' => 'failed'
            )
        );
                
             // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to Comment ';
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
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

