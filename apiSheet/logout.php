<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
require_once 'connect.php';
require_once 'functions/get_IP_Location.php';
require_once 'functions/activity_logs.php';
 



if ( isset($_GET['user_id'])) {

    $user_id = mysqli_escape_string($conn, $_GET['user_id']);

    // LOG ACTIVITY
            
    $user =  $user_id;
    $activity =  'tried to logged out ';
    $status = 'success';
    log_activity($conn, $user, $activity, $status, getSecurity());

    // END LOG ACTIVITY
    echo json_encode(
        array(
            'message' => 'Logout Successful',
            'status' => 'success'
        )
    );
    

}else {
    echo json_encode(
        array(
            'data' => 'Invalid Request',
            'status' => 'failed'
        )
    );
}
