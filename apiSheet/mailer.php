<?php

//folder to send emails
use PHPMailer\PHPMailer\PHPMailer;
// echo json_encode("here");

// echo "Am here";die();
//sending the email to the user
require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";


$mail = new PHPMailer();


//STMP Settings
$mail->isSMTP();
$mail->Host = "server.unionsg.com";
$mail->SMTPAuth=true;
$mail->Username="hr@unionsg.com";
$mail->Password="(qLwOdQ3F3cm";
$mail->Port = 587;
$mail->SMTPSecure = "tls";
