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

function check_init_desc_exist($user_id, $init_desc, $conn){
    $query = "SELECT init_desc FROM code_desc WHERE init_desc = '$init_desc'";
    $result = mysqli_query($conn, $query);
    $check_init_desc_count = mysqli_num_rows($result);
    if ($check_init_desc_count >= 1) {
         $message = json_encode(
            array(
                'data' => null,
                'message' => $init_desc . ' already exist',
                'status' => 'failed'
            )
        );
           // LOG ACTIVITY
                    
            $user =  $user_id;
             $activity =  ' tried to add code_desc->'. $init_desc;
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
        exit($message);
    }else {
        # code... 
    }
}

$data = json_decode(file_get_contents("php://input"));
if (isset($data) && isset($data->user_id) && isset($data->init) && isset($data->description) ) {
    // echo json_encode($data);

    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $init = mysqli_real_escape_string($conn, $data->init);
    $init_desc = mysqli_real_escape_string($conn, $data->init_desc);
    $description = mysqli_real_escape_string($conn, $data->description);

    check_init_desc_exist($user_id, $init_desc, $conn);

    $query = "INSERT INTO `code_desc` (`init`, `init_desc`, `desc`) VALUES ('$init', '$init_desc', '$description')";
    $result = mysqli_query($conn, $query);
    if ($result == 1) {

        $message = json_encode(
            array(
                'message' => $description . '( ' . $init_desc . ' )' . ' created successfully',
                'status' => 'success',
                'data' => [
                    'init' => $init,
                    'init_desc' => $init_desc,
                    'description' => $description
                ]
            )
        );
                 
                       
            // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to add code->' . $init_desc . ' and code_desc->'.$description. '| Details: ' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY 
      
          
        exit($message);

    } else {
        $message = json_encode(
            array(
                'message' => 'Failed create Code Description or Code Description already exist',
                'status' => 'failed'
            )
        );
           // LOG ACTIVITY
                    
            $user =  $user_id;
             $activity =  ' tried to add code->' . $init_desc . ' and code_desc->'. $description;
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
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
