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
if(
    isset($data) && isset($data->version_no) && isset($data->project_name) && isset($data->user_id) &&
    isset($data->start_date) && isset($data->end_date)  && isset($data->project_id) 
){
    // echo json_encode($data);

    $project_id = mysqli_real_escape_string($conn, $data->project_id);
    $version_no = mysqli_real_escape_string($conn, $data->version_no);
    $name = mysqli_real_escape_string($conn, $data->project_name);
    $description = mysqli_real_escape_string($conn, $data->project_description);
    $dept_id = mysqli_real_escape_string($conn, $data->dept_id);
    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $start_date = mysqli_real_escape_string($conn, $data->start_date);
    $end_date = mysqli_real_escape_string($conn, $data->end_date);
    // $ip_address = mysqli_real_escape_string($conn, $data->ip_address);
    // $location = mysqli_real_escape_string($conn, $data->location); 
    $start_date = date("Y-m-d", strtotime($start_date));
    $end_date = date("Y-m-d", strtotime($end_date));
    
    $ip_address = 'DF45-123E-34E-24';
    $location = 'Accra Ghana';

    // function get_all_approval_users ($conn){
    //     $query = "SELECT email FROM `users` WHERE can_approve = 1";
    //     $result = mysqli_query($conn, $query);

    //     $num = mysqli_num_rows($result);
    //     $email_arr = array();

    //     if ($num > 0) {
    //         while ($row = mysqli_fetch_assoc($result)) {

    //         $email_arr[] = $row['email'];

    //         } 

    //         $to = 'ampahkwabena55@gmail.com';
    //         $subject = "UNION SYSTEMS GLOBAL";
    //         $txt = 'New Project has been created :  http://192.168.1.78/project_tracker/login';
    //         $headers = "From: ampahkwabena55@gmail.com" . "\r\n" . "CC: " .  implode (", ", $email_arr);

           

    //        if( mail($to,$subject,$txt,$headers)){
    //             return true;
    //        }
           
    //     } else {
    //         echo 'no date exit';
    //     }

    // }


    $query = "UPDATE `projects` SET `name` = '$name', `version_no`= '$version_no',`description` = '$description', `dept_id` = '$dept_id', `start_date` = '$start_date', `end_date` = '$end_date'   WHERE `projects`.`project_id` = '$project_id'";

    
    $result = mysqli_query($conn, $query);

    if ($result == 1) {


        $message = json_encode(
            array(
                'message' =>'project created successfully',
                'status' => 'success',
                'data' => [
                    'project_id' => $project_id,
                    'version_no' => $version_no,
                    'project_name' => $name,
                    'department_id' => $dept_id,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ],
                'project_id' => $project_id
            )
        );
                           
            // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to modify a project PROJ-0000' . $project_id. ' | Details: ' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
            
        

         exit($message);

        //  echo get_all_approval_users($conn);

        //  if (get_all_approval_users($conn)) {
        //    exit($message);
        // }
        

    } else {
        
                   
            // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to modify a project PROJ-0000' . $project_id;
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
         
        $message = json_encode(
            array(
                'message' => 'Failed to update project',
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


