
<?php
// $to = "elijahashitey002@gmail.com";
$to = "joshuaamarfio1@gmail.com";
$subject = "My subject";
$txt = "Hello world!";
$headers = "From: USG" . "\r\n" .
"CC: elijahashitey002@gmail.com";

// mail($to,$subject,$txt,$headers);
if (mail($to,$subject,$txt,$headers)) {
    echo 'succes';
}else{
    echo 'failed';
}
?>