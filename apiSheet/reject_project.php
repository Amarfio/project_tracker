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


        reject_project($conn, $project_id, $approvedBy, $comment );

}



function reject_project($conn, $project_id, $approvedBy, $comment ){

    
    $query = "UPDATE `projects` SET `status` = 83, `is_approved` = 1, `comment` = '$comment',`approved_by` = '$approvedBy ' WHERE `projects`.`project_id` = '$project_id' ";

    $result = mysqli_query($conn, $query);

    if ($result == 1) {
        $message = json_encode( 
            array(
                'message' => 'Project has been moved to draft',
                'status' => 'success',
                'project_id' => $project_id
            )
        );
        exit($message);
    } else{
        $message = json_encode(
            array(
                'message' => 'failed reject project',
                'status' => 'failed',
            )
        );

        exit($message);
    }


}

