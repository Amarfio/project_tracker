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

if (isset($_GET['project_id']) && isset($_GET['reassignedBy']) && isset($_GET['comment']) ) {

        $comment = mysqli_escape_string($conn, $_GET['comment']);
        $user_id = mysqli_escape_string($conn, $_GET['user_id']);
        $project_id = mysqli_escape_string($conn, $_GET['project_id']);
        $reassignedBy = mysqli_escape_string($conn, $_GET['reassignedBy']);
        $department_id = mysqli_escape_string($conn, $_GET['department_id']);

        reassign_project($conn, $project_id, $reassignedBy, $comment, $department_id, $user_id );
            // get_department_head_send_email ($conn, $project_id, $department_id, $comment) ;

}



function reassign_project($conn, $project_id, $reassignedBy, $comment, $department_id, $user_id ){

    
    $query = "UPDATE `projects` SET `dept_id` = '$department_id' WHERE `projects`.`project_id` = '$project_id'";

    $result = mysqli_query($conn, $query);

    if ($result == 1) { 
        //  get_department_head_send_email();

            // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' treid re-assigned project PROJ-' . $project_id;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
        $message = json_encode( 
           
            array(
                'message' => 'Project has been sucessfully Reassigned ',
                'status' => 'success',
                'project_id' => $project_id
            )
        );
        exit($message);
    } else{
        
            // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried re-assigned project PROJ-' . $project_id;
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
        $message = json_encode(
            array(
                'message' => 'failed approve project',
                'status' => 'failed',
            )
        );

        exit($message);
    }


}

function get_department_head_send_email ($conn, $project_id, $department_id, $comment) {

    $get_dept_head = "SELECT email FROM users WHERE dept = '$department_id' AND is_dpt_head = 1";
    $result_dept_head= mysqli_query($conn, $get_dept_head);
    $count_dept_head = mysqli_num_rows($result_dept_head);

    $dept_head = array();

if ($count_dept_head > 0) {
    while ($row = mysqli_fetch_assoc($result_dept_head)) {

         $email = strtolower($row['email']);

         $det_head[] = $email;

    }
        $email_list = implode(",", $dept_head);
    
        $to = "ampahkwabena55@gmail.com" ;
        $subject = "A project PROJ-0000".$project_id." has been re-assigned to your department ";
        $txt = "<a href='http://192.168.1.74/project_tracker/login' target='_blank'>Visit USG Project Tracker</a>". "\r\n" ;
      
        $txt = $txt . "<strong>Project ID: PROJ-0000". $project_id ."</strong>". "\r\n" ;
        $txt = $txt . "<strong>Comment: </strong>".$comment. "\r\n" ;
        // $headers = "From: project.tracker@unionsg.com" . "\r\n" .
        $headers = "From: USG" . "\r\n" .
        "CC: " . $email_list ;

          if (mail($to,$subject,$txt,$headers)) {

                reassign_project($conn, $project_id, $reassignedBy, $comment, $department_id );

          }else{
              
          }


} else {

        $to = "ampahkwabena55@gmail.com" ;
        $subject = "A project PROJ-0000".$project_id." has been re-assigned to your department ";
        $txt = "<a href='http://192.168.1.74/project_tracker/login' target='_blank'>Visit USG Project Tracker</a>". "\r\n" ;
      
        $txt = $txt . "<strong>Project ID: PROJ-0000". $project_id ."</strong>". "\r\n" ;
        $txt = $txt . "<strong>Comment: </strong>".$comment. "\r\n" ;
        // $headers = "From: project.tracker@unionsg.com" . "\r\n" .
        $headers = "From: USG" . "\r\n" .
        "CC: ";

          if (mail($to,$subject,$txt,$headers)) {

                reassign_project($conn, $project_id, $reassignedBy, $comment, $department_id );

          }
}

}

