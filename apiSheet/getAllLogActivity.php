<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';

$query = "SELECT u.id user_id, u.f_name, u.l_name, u.email, log.id log_id, log.activity activity, log.status activity_status, log.security IP, log.time activity_time FROM log_activities log LEFT JOIN users u ON log.user = u.id ORDER BY log.id DESC";

$result = mysqli_query($conn, $query);

$num = mysqli_num_rows($result);
$log_activities_arr = array();
if ($num > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
       $log_activities_arr[] = $row;
    }
    echo json_encode($log_activities_arr);
} else {
    echo 'no date exit';
}
