<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';
require_once 'mailer.php';
require_once 'functions/passwordResetTemplate.php';

// $mail->isSMTP();
// $mail->Host = "mail.unionsg.com";
// $mail->SMTPAuth = true;
// $mail->Username = "support24x7@unionsg.com";
// $mail->Password = "xz1i8Hmnoj!D";
// $mail->Port = 587;
// $mail->SMTPSecure = "tls";

$mail->isSMTP();
$mail->Host       = "mail.unionsg.com";
$mail->SMTPAuth   = true;
$mail->Username   = "support24x7@unionsg.com";
$mail->Password   = "xz1i8Hmnoj!D"; // change immediately
// $mail->SMTPSecure = "tls";
$mail_secure = true;
$mail->Port       = 465;

$mail->Timeout = 10;
$mail->SMTPKeepAlive = false;
$mail->SMTPAutoTLS = true;


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

// echo $num; die();

    if ($num > 0) {
         // update reset number
        $set_password = md5($email . null . time());

        $up_query = "UPDATE `users` SET `reset` = '$set_password' WHERE `users`.`email` = '$email'";
        $up_result = mysqli_query($conn, $up_query);
        // echo($up_result); die();
        if ($up_result == 1) {

            $setPasswordLink = 'http://10.203.14.97/project_tracker/set_password/' . $set_password;
        
            $subject = "UNION SYSTEMS GLOBAL";
            $message = emailForPasswordReset($first_name, $email, $set_password);
        
            // API endpoint (replace with your actual endpoint)
            $url = "https://10.203.14.97:3001/send-email";
        
            // JSON payload
            $data = array(
                "to" => $email,
                "subject" => $subject,
                "message" => $message,
                "name" => $first_name
            );
        
            $payload = json_encode($data);
        
            // Initialize cURL
            $ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        "status" => "failed",
        "error" => curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
        
            if ($httpCode == 200) {
        
                $message = json_encode(array(
                    'message' => 'Check email for password reset link',
                    'status' => 'success',
                    'set_password' => $setPasswordLink
                ));
        
                exit($message);
        
            } else {
        
                $message = json_encode(array(
                    'message' => 'Could not send you the link',
                    'status' => 'failed'
                ));
        
                exit($message);
            }
        
        } else {
        
            $message = json_encode(array(
                'message' => 'Could not send you the link',
                'status' => 'failed'
            ));
        
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