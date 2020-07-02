<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';

if (isset($_GET['project_id']) && isset($_GET['approvedBy']) && isset($_GET['comment']) ) {

        $comment = mysqli_escape_string($conn, $_GET['comment']);
        $project_id = mysqli_escape_string($conn, $_GET['project_id']);
        $approvedBy = mysqli_escape_string($conn, $_GET['approvedBy']);
        $department_id = mysqli_escape_string($conn, $_GET['department_id']);

        approve_project($conn, $project_id, $approvedBy, $comment );

//     $get_users_approvers = "SELECT email FROM users WHERE dept = '$department_id'";
//     $result_get_users_approvers = mysqli_query($conn, $get_users_approvers);
//     $count_get_users_approvers = mysqli_num_rows($result_get_users_approvers);

//     $users_arr = array();

// if ($count_get_users_approvers > 0) {
//     while ($row = mysqli_fetch_assoc($result_get_users_approvers)) {

//          $email = strtolower($row['email']);

//          $users_arr[] = $email;


//     }
//         $email_list = implode(",", $users_arr);
    
//         $to = "ampahkwabena55@gmail.com" ;
//         $subject = "New Project has been assigned to your department";
//         $txt = 'http://192.168.1.74/project_tracker/login/';
//         // $headers = "From: project.tracker@unionsg.com" . "\r\n" .
//         $headers = "From: ampahkwabena55@gmail.com" . "\r\n" .
//         "CC: " . $email_list ;

//           if (mail($to,$subject,$txt,$headers)) {

//                 approve_project($conn, $project_id, $approvedBy, $comment );

//           }


// } else {

//         $to = "ampahkwabena55@gmail.com" ;
//          $subject = "New Project has been assigned to your department";
//         $txt = 'http://192.168.1.74/project_tracker/login/';
//         // $headers = "From: project.tracker@unionsg.com" . "\r\n" .
//         $headers = "From: ampahkwabena55@gmail.com" . "\r\n" ;

//           if (mail($to,$subject,$txt,$headers)) {

//                 approve_project($conn, $project_id, $approvedBy, $comment );

//           }
// }




}



function approve_project($conn, $project_id, $approvedBy, $comment ){

    
    $query = "UPDATE `projects` SET `status` = 85, `is_approved` = 1, `comment` = '$comment', `comment_by` = '$approvedBy', `approved_by` = '$approvedBy, ' WHERE `projects`.`project_id` = '$project_id' ";

    $result = mysqli_query($conn, $query);

    if ($result == 1) {
        $message = json_encode( 
            array(
                'message' => 'Project has been sucessfully Approved',
                'status' => 'success',
                'project_id' => $project_id
            )
        );
        exit($message);
    } else{
        $message = json_encode(
            array(
                'message' => 'failed approve project',
                'status' => 'failed',
            )
        );

        exit($message);
    }


}

