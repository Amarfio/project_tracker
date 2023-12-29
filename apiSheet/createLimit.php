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
if(isset($data) && isset($data->user_id) && isset($data->nameOfLimit) && isset($data->descriptionOfLimit)){
    // echo json_encode($data);

    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $nameOfLimit = mysqli_real_escape_string($conn, $data->nameOfLimit);
    $descriptionOfLimit = mysqli_real_escape_string($conn, $data->descriptionOfLimit);
    $noOfLimit = mysqli_real_escape_string($conn, $data->noOfLimit);

    $query = "INSERT INTO `limits` (`name`, `description`, `number`, `created_at`) VALUES ('$nameOfLimit', '$descriptionOfLimit', '$noOfLimit', NOW())";
    $result = mysqli_query($conn, $query);
    if ($result == 1) { 
        $message = json_encode(
            array(
                'message' => 'Limit created succesfully',
                'status' => 'success',
                'data'=>null
            )
        );
                        
            // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to add limit->' . $nameOfLimit . ' and description->'.$descriptionOfLimit. '| Details: ' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
            

       exit($message);

    } else {
                  
            // LOG ACTIVITY
                    
            $user =  $user_id;
             $activity =  ' tried to add limit->' . $nameOfLimit . ' and description->'. $descriptionOfLimit;
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

