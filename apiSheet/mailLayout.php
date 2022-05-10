<?php

require_once 'mailer.php';

function sendEmail($email, $name, $subject, $txt){

    //Email Settings
    $mail->isHTML(true);
    $mail->setFrom($email, $name);
    $mail->addAddress($email);
    $mail->Subject=$subject;
    $mail->Body = $txt;
    $done = $mail->send();

    //return result of the sent mail
    return $done;
}
