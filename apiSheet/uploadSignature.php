<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once 'connect.php';
 
function upload_file($conn, $user_id)
{
    /* Getting file name */
    $filename = $_FILES['file']['name'];

    /* Location */
    $location = 'uploads/'; 

    // Upload file
    // move_uploaded_file($_FILES['file']['tmp_name'], $location . $filename);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $location . $filename)) {
        $query = "UPDATE `users` SET `signature` = '$filename' WHERE `users`.`id` = '$user_id'";
         $result = mysqli_query($conn, $query);
 
        if ($result == 1) {
             $message = json_encode(
                array(
                    'message' => 'uploaded signature pic successfully',
                    'data' => array('signature' => $filename),
                    'status' => 'success'
                )
            );
            exit($message);

            // $arr = array('name' => $filename);
            // echo json_encode($arr);
        }
    }else{
         $message = json_encode(
                array(
                    'message' => 'Failed to upload photo',
                    'data' => NULL,
                    'status' => 'failed'
                )
            );
            exit($message);
    }


    
}

if (isset($_FILES['file']['name']) ) {
    
    upload_file($conn, $_GET['user_id']);
}else{
    echo 'error';
}
