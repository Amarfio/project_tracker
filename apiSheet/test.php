<?php
$to = "kwabena.ampah@stu.ucc.edu.gh";
$subject = "My subject";
$txt = "Hello world!";
$headers = "From: USG" . "\r\n" .
"CC: ampahkwabena5@gmail.com";

if(mail($to,$subject,$txt,$headers)){
    echo 'true';
}else{
    echo 'false';
}
?>