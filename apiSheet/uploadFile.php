<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once 'connect.php';

function upload_file()
{
    /* Getting file name */
    $filename = $_FILES['file']['name'];

    /* Location */
    $location = 'uploads/';

    // Upload file
    move_uploaded_file($_FILES['file']['tmp_name'], $location . $filename);


    $arr = array('name' => $filename);
    echo json_encode($arr);
}

if (isset($_FILES['file']['name'])) {

    upload_file();
}
