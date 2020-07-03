<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';

function check_if_email_exist($email, $conn){
    $query = "SELECT email FROM users WHERE email = '$email' limit 1 ";

    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);
    return $num;
}


if (isset($_GET['email'])) {
    $email = mysqli_escape_string($conn, $_GET['email']);

    // $query = "SELECT email FROM users WHERE email = '$email' limit 1 ";

    // $result = mysqli_query($conn, $query);

    $num = check_if_email_exist($email, $conn);
    
    $reset_email_username = array();
 
    // update reset number
    $set_password = md5($email . null . time());

    if ($num > 0) {
         // update reset number
        $set_password = md5($email . null . time());

        $up_query = "UPDATE `users` SET `reset` = '$set_password' WHERE `users`.`email` = '$email'";
        $up_result = mysqli_query($conn, $up_query);
        if ($up_result == 1) {

            $to = $email;
            $subject = "UNION SYSTEMS GLOBAL";
            $txt = 'http://192.168.1.195:84/project_tracker/set_password/' . $set_password;
            $headers = "From: UNION SYSTEMS GLOBAL" ;
            

        if (mail($to,$subject,$txt,$headers)) { 
            $message = json_encode(
                array(
                    'message' => 'Check email for password reset link',
                    'status' => 'success',
                    'set_password' => 'http://192.168.1.195:84/project_tracker/set_password/' . $set_password
                )
            );
            exit($message);
            
        }else {
            $message = json_encode(
                array(
                    'message' => 'Could not send you the link',
                    'status' => 'failed'
                )
            );
            exit($message);

        }




        }else {
            $message = json_encode(
                array(
                    'message' => 'Could not send you the link',
                    'status' => 'failed'
                )
            );
            exit($message);

        }



        
    } else {
        $message = json_encode(
            array(
                'message' => 'Email does not exit',
                'status' => 'failed'
            )
        );
        exit($message);

    }

}