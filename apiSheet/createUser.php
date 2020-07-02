<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once 'connect.php';

$data = json_decode(file_get_contents("php://input"));
// if (
//     isset($data) && isset($data->first_name) && isset($data->last_name) && isset($data->username) && isset($data->dept_id) && isset($data->role_id)  && isset($data->can_approve) && isset($data->is_dpt_head) && isset($data->email) && isset($data->phone) && isset($data->country) && isset($data->city) && isset($data->city) && isset($data->postal_addr)
// ) {
if (
    isset($data) && isset($data->first_name) && isset($data->last_name) && isset($data->username)   && isset($data->role_id)  && isset($data->can_approve)  && isset($data->email) && isset($data->city) && isset($data->gender) 
) {
    // echo json_encode($data); jdfjodf 

    $f_name = mysqli_real_escape_string($conn, $data->first_name); 
    $l_name = mysqli_real_escape_string($conn, $data->last_name);
    $username = mysqli_real_escape_string($conn, $data->username);
    $gender = mysqli_real_escape_string($conn, $data->gender);
    $dept = mysqli_real_escape_string($conn, $data->dept_id);

    $role = mysqli_real_escape_string($conn, $data->role_id);
    $can_approve = mysqli_real_escape_string($conn, $data->can_approve);
    // $position = mysqli_real_escape_string($conn, $data->position);
    $is_dpt_head = mysqli_real_escape_string($conn, $data->is_dpt_head);
  
    $email = strtolower( mysqli_real_escape_string($conn, $data->email) );
    $phone = mysqli_real_escape_string($conn, $data->phone);
    $country = mysqli_real_escape_string($conn, $data->country);
  
    $city = mysqli_real_escape_string($conn, $data->city);
    $postal_addr = mysqli_real_escape_string($conn, $data->postal_addr);
    
    // CHECK IF data->each_request exist. 

    $check_user_query = "SELECT dept FROM users where users.dept = '$dept' AND is_dpt_head =1 AND is_dpt_head = $is_dpt_head ";
    $result_check_user_query = mysqli_query($conn, $check_user_query);
    $check_dept_head_count = mysqli_num_rows($result_check_user_query);

    $check_email_query = "SELECT email FROM users WHERE email = '$email'";
    $result_check_email_query = mysqli_query($conn, $check_email_query);
    $check_email_count = mysqli_num_rows($result_check_email_query);
    if ($check_email_count >= 1) {
         $message = json_encode(
            array(
                'data' => null,
                'message' => 'Email already exist',
                'status' => 'failed'
            )
        );
        exit($message);
    }else {
        # code...
    }


    if ($check_dept_head_count >= 1) {
        $message = json_encode(
            array(
                'data' => null,
                'message' => 'Manage already exist',
                'status' => 'failed'
            )
        );
        exit($message);
    }else {
        
        $set_password = md5($email . null . time());
  


        $to = $email;
        $subject = "Change Password Link, USG Project Tracker";
        $txt = 'http://192.168.1.74/project_tracker/set_password/' . $set_password;
        // $headers = "From: project.tracker@unionsg.com" . "\r\n" .
        $headers = "From: USG" ;
           

        // mail($to,$subject,$txt,$headers);
        if (mail($to,$subject,$txt,$headers)) {

            $query = "INSERT INTO `users` (`id`, `f_name`, `l_name`,`username`, `dept`, `role`, `can_approve`, `position`, `is_dpt_head`, `email`, `phone`, `country`, `city`, `postal_addr`, `reset`) VALUES (NULL, '$f_name', '$l_name', '$username', '$dept', '$role', '$can_approve', NULL, '$is_dpt_head', '$email', '$phone', '$country', '$city', '$postal_addr', '$set_password')";
            $result = mysqli_query($conn, $query);
     
            if ($result == 1) {

              $message= json_encode(
                array(
                    'status' => 'success',
                    'message' => "User Successfully create. \n Please check email for change password link" ,
                    'data' => [
                        'first_name' => $f_name,
                        'last_name' => $l_name,
                        'dept_id' => $dept,
                        'role_id' => $role,
                        // 'position' => $position,
                        'is_dpt_head' => $is_dpt_head,
                        'email' => $email,
                        'phone' => $phone,
                        'country' => $country, 
                        'city' => $city,
                        'postal_addr' => $postal_addr
                    ],
                    'set_password' => $set_password
                )
            );
             exit($message);
            
         } else {
                $message =  json_encode(
                     array(
                         'data' => null,
                         'message' => 'Failed to create a user',
                         'status' => 'failed'
                     )
                 );
                  exit($message);
             } 

        }else{

            $message =  json_encode(
                array(
                    'data' => null,
                    'message' => 'Failed to send email notification to user',
                    'status' => 'failed'
                )
            );
             exit($message);
           
        }

        // if (mail($to,$subject,$txt,$headers)) {
       
        //        $message= json_encode(
        //         array(
        //             'status' => 'success',
        //             'message' => 'User successfully added',
        //             'data' => [
        //                 'first_name' => $f_name,
        //                 'last_name' => $l_name,
        //                 'dept_id' => $dept,
        //                 'role_id' => $role,
        //                 // 'position' => $position,
        //                 'is_dpt_head' => $is_dpt_head,
        //                 'email' => $email,
        //                 'phone' => $phone,
        //                 'country' => $country, 
        //                 'city' => $city,
        //                 'postal_addr' => $postal_addr
        //             ],
        //             'set_password' => $set_password
        //         )
        //     );
        //      exit($message);
        // }       

           
         
        } 
    }

 else {
    $message = json_encode(
        array(
            'data' => null,
            'message' => 'Invalid request parameters',
            'status' => 'failed'
        )
    );

     exit($message);
}
