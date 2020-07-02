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
if(isset($data) && isset($data->user_id) && isset($data->init) && isset($data->name)){
    // echo json_encode($data);

    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $init = mysqli_real_escape_string($conn, $data->init);
    $name = mysqli_real_escape_string($conn, $data->name);

    $query = "INSERT INTO `code` (`init`, `name`) VALUES ('$init', '$name')";
    $result = mysqli_query($conn, $query);
    if ($result == 1) { 
        $message = json_encode(
            array(
                'message' => 'Code created succesfully',
                'status' => 'success',
                'data' => [
                    'init' => $init,
                    'name' => $name
                ]
            )
        );
                        
            // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to add code->' . $init . ' and code_desc->'.$name. '| Details: ' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
            

       exit($message);

    } else {
                  
            // LOG ACTIVITY
                    
            $user =  $user_id;
             $activity =  ' tried to add code->' . $init . ' and code_desc->'. $name;
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
          
        $message = json_encode(
            array(
                'message' => 'Failed create Code or Code already exist',
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

