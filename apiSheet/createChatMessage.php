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
if(isset($data) && isset($data->sender_id) && isset($data->reciever_id)  && isset($data->message) && isset($data->department_id) ){
    // echo json_encode($data);

    $sender_id = mysqli_real_escape_string($conn, $data->sender_id);
    $reciever_id = mysqli_real_escape_string($conn, $data->reciever_id);
    $message = mysqli_real_escape_string($conn, $data->message);
    $department_id = mysqli_real_escape_string($conn, $data->department_id); 


    // $query = "INSERT INTO `clients` (`client_id`, `name`) VALUES (NULL, '$client')";
    $query = "INSERT INTO `chat` (`chat_id`, `sender_id`, `reciever_id`, `message`, `dept_id`) VALUES (NULL, '$sender_id', '$reciever_id', '$message', '$department_id')";
    $result = mysqli_query($conn, $query);

    if ($result == 1) {
        $message = json_encode(
            array(
                'message' => 'Chat message created successfully',
                'status' => 'success',
                'data' => [
                    'sender_id' => $sender_id,
                    'reciever_id' => $reciever_id,
                    'message' => $message, 
                    'department_id' => $department_id,
                ]
            )
        );
                    // LOG ACTIVITY
                    
            $user =  $sender_id;
            $activity =  ' tried to send a private message' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY

        exit($message);

    } else {
        
           
            
        $message = json_encode(
            array(
                'message' => 'Failed to send private message',
                'status' => 'failed'
            )
        );

          // LOG ACTIVITY
                    
            $user =  $sender_id;
            $activity =  ' tried to send private message ';
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

