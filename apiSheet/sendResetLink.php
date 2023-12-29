<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';
require_once 'mailer.php';

//method to check if the email exists in the database
function check_if_email_exist($email, $conn){
    $query = "SELECT email FROM users WHERE email = '$email' limit 1 ";

    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);
    return $num;
}

//method to get the first name of the user in the database
function get_first_name($email, $conn){
    $query = "SELECT f_name FROM users WHERE email = '$email' limit 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $result = $row['f_name'];
    return $result;
}



if (isset($_GET['email'])) {
    $email = mysqli_escape_string($conn, $_GET['email']);

    // $query = "SELECT email FROM users WHERE email = '$email' limit 1 ";

    // $result = mysqli_query($conn, $query);

    $num = check_if_email_exist($email, $conn);
    // echo $num; die();
    $first_name = get_first_name($email, $conn);
    // $reset_email_username = array();
    
 
    // update reset number
    $set_password = md5($email . null . time());

    // return $num; die();

    if ($num > 0) {
         // update reset number
        $set_password = md5($email . null . time());

        $up_query = "UPDATE `users` SET `reset` = '$set_password' WHERE `users`.`email` = '$email'";
        $up_result = mysqli_query($conn, $up_query);
        if ($up_result == 1) {

            $from = "Project Tracker (USG)";
            $name = "no-reply";
            $subject = "UNION SYSTEMS GLOBAL";
            // $txt = 'http://192.168.1.195:84/project_tracker/set_password/' . $set_password;
            $txt = "
            Hello $first_name,  
            <br /><br />
            The password to your <b>Project Tracker</b> account has been reset successfully.
            <br />
            Kindly <a href='http://localhost:8081/project_tracker/set_password/$set_password'>click here</a> to set your password. 
            <br /><br/>
            Kind regards,
            <br/>
            <a href='http://unionsg.com/' target='_blank'>UNION SYSTEMS GLOBAL</a>.
            <br/>
            <img  src='http://issues.unionsg.com/images/logo.png' class='img-circle'/>
            ";
            $headers = "From: UNION SYSTEMS GLOBAL" ;
            
        //Email Settings
        $mail->isHTML(true);
        $mail->setFrom("hr@unionsg.com", $name);
        $mail->addAddress($email);
        $mail->Subject=$subject;
        $mail->Body = $txt;
        $done = $mail->send();

        // mail($to,$subject,$txt,$headers)
        if ($done) { 
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