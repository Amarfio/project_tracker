<?php
 
header('Access-Control-Allow-Origin: *'); 
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';


function get_all_attachment_files ($task_id, $conn){
        
    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != '' ORDER BY c.comment_id DESC";
    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $attach_files = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $attach_files[] = $row;
        } 

        echo json_encode($attach_files);

}




if (isset($_GET['task_id'])) {

    $task_id = mysqli_escape_string($conn, $_GET['task_id']);
    get_all_attachment_files ($task_id, $conn);

    } else {
        echo 'invalid request';
    }
