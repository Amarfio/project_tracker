<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';
require_once 'functions/activity_logs.php';
require_once 'functions/get_IP_Location.php';

if (isset($_GET['user_id'])) {
    $user_id = mysqli_escape_string($conn, $_GET['user_id']);
    // echo ($user_id);
    // die();

    $query = "SELECT u.id as user_id, u.f_name, u.profile_pic, u.signature, u.bio, u.l_name, u.gender, u.username, u.is_dpt_head, u.can_approve, u.email, u.phone, u.country, u.city, u.receive_emails, e_desc.desc AS alt_desc, u.postal_addr AS address, u.reset as token, c.id AS department_id, c.desc department, d.id AS role_id, d.desc as role, u.is_active AS is_active from users u LEFT JOIN code_desc c ON u.dept = c.id LEFT JOIN code_desc e_desc ON u.receive_emails = e_desc.id left JOIN code_desc d ON u.role = d.id WHERE u.id = '$user_id'";

    // echo $result; die();
    $result = mysqli_query($conn, $query);
    $num = mysqli_num_rows($result);
    if ($num > 0) {
        $row = mysqli_fetch_assoc($result); 

        // LOG ACTIVITY 
                
        $user =  $row['user_id'];
        $activity =  'get user details';
        $status = 'success';
        log_activity($conn, $user, $activity, $status, getSecurity());
        
        //check and update projects in progress
        // checkProjsInProgress($conn);

        //check and update project completed
        // checkProjsCompleted($conn);
        
        
        //check rejected projects and delete them
        // deleteRejectedProjects($conn);

        //update task status to completed when completion is 100%
        // updateTaskToCompleted($conn);


        // END LOG ACTIVITY

        // echo json_encode('result', $result);

        $isActive = null;
        if($row['is_active'] == '1'){
                $isActive = "Yes";
        }else{
            $isActive = "No";
        }

        
        echo json_encode(
            array(
                'message' => 'data found !!!',
                'status' => 'success',
                'data' => [
                    'user_id' => $row['user_id'],
                    'first_name' => $row['f_name'],
                    'last_name' => $row['l_name'],
                    'gender' => $row['gender'],
                    'username' => $row['username'],
                    'department_id' => $row['department_id'],
                    'department' => $row['department'],
                    'role_id' => $row['role_id'],
                    'role' => $row['role'],
                    'is_dept_head' => $row['is_dpt_head'],
                    'can_approve' => $row['can_approve'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'address' => $row['address'],
                    'country' => $row['country'],
                    'bio' => $row['bio'],
                    'city' => $row['city'],
                    'emailNotice' =>$row['receive_emails'],
                    'desc_alt' =>$row['alt_desc'],
                    'isActive' =>$isActive
                ],
                'token' => $row['token'], 
                'auth' => true,
                'profile_pic' => $row['profile_pic'],
                'signature_pic' => $row['signature']
            )
        );
    } else {

        echo json_encode(
            array(
                'message' => ' no data found !!!',
                'status' => 'failed',
                'data' => null
            )
        );

         // LOG ACTIVITY
                
        $user =  0;
        $activity = 'tried to get user data for admin';
        $status = 'failed';
        log_activity($conn, $user, $activity, $status, getSecurity());


        
        //code to update the projects that are overdue...
        // overdue_projects($conn);
        // checkProjsInProgress($conn);

        //check projects completed
        // checkProjsCompleted($conn);

        //check rejected projects and delete them
        // deleteRejectedProjects  ($conn);
       
        //update task status to completed when completion is 100%
        // updateTaskToCompleted($conn);

        // END LOG ACTIVITY
        
        // echo json_encode(
        //     array(
        //         'message' => 'Incorrect Credentials',
        //         'status' => 'failed'
        //     )
        // );
    }
}