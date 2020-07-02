<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';


if (isset($_GET['init'])) { 
    $init = mysqli_escape_string($conn, $_GET['init']);
    $query = "SELECT * FROM code_desc LEFT JOIN code ON code.init = code_desc.init WHERE code_desc.init = '$init'";

    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);
    $project_status = array();
    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
        $project_status[] = $row;
        }
        echo json_encode($project_status);
    } else {
        echo 'no date exit';
    }
} else {
    echo 'no date exit';
}
