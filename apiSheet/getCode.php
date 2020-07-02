<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';

$query = "SELECT * FROM code";

$result = mysqli_query($conn, $query);

$num = mysqli_num_rows($result);
$code_arr = array();
if ($num > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
       $code_arr[] = $row;
    }
    echo json_encode($code_arr);
} else {
    echo 'no date exit';
}
