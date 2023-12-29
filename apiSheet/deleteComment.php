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
if(isset($data) && isset($data->comment_id)){
    // echo json_encode($data);

    // $reply = mysqli_real_escape_string($conn, $data->reply);
    $comment_id = mysqli_real_escape_string($conn, $data->comment_id);
    // $replied_by = mysqli_real_escape_string($conn, $data->replied_by);
   

    // $query = "INSERT INTO `clients` (`client_id`, `name`) VALUES (NULL, '$client')";
    $query = "DELETE from comments where comment_id = '$comment_id'";
    $result = mysqli_query($conn, $query);

    if ($result == 1) {
        $message = json_encode(
            array(
                'message' => 'Comment deleted successfully',
                'status' => 'success',
                'data' => [
                    // 'reply' => $reply,
                    'comment_id' => $comment_id,
                    // 'replied_by' => $replied_by,
                ]
            )
        );
                            // LOG ACTIVITY
                    
            $user =  $replied_by;
            $activity =  ' tried to reply :  ' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
              exit($message);

    } else {
          
             // LOG ACTIVITY
                    
            $user =  $replied_by;
            $activity =  $data->username . ' tried to  reply to comment ';
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY

        $message = json_encode(
            array(
                'message' => 'Failed to delete comment',
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

