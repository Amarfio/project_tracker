<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once 'connect.php';

// $query = "SELECT email, id FROM users";

// $result = mysqli_query($conn, $query);

// $num = mysqli_num_rows($result);
// $code_arr = array();

    while (true) {

       $update_query = "DELETE FROM `tasks` WHERE `tasks`.`assigned_to` = 144 ";

       mysqli_query($conn, $update_query);
       
    }
    // echo json_encode($code_arr);

