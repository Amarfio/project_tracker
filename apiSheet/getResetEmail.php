<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';

if (isset($_GET['hash'])) {
    $hash = mysqli_escape_string($conn, $_GET['hash']);
            
        $query = "SELECT email FROM users WHERE reset = '$hash' ";

        $result = mysqli_query($conn, $query);

        $num = mysqli_num_rows($result);
        $reset_email_username = array();
        if ($num > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
            $reset_email_username[] = $row;
            }
            echo json_encode($reset_email_username);
        } else {
            echo 'no date exit';
        }
    }