<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';



if (isset($_GET['user_id']) && isset($_GET['member_id'])) {
    $member_id = mysqli_escape_string($conn, $_GET['member_id']);
    $user_id = mysqli_escape_string($conn, $_GET['user_id']);

    $query = "SELECT ch.chat_id, ch.message message, us.id sender_id, CONCAT(us.f_name, ' ', us.l_name) sender_name, ur.id reciever_id, CONCAT(ur.f_name, ' ', ur.l_name) reciever_name, ch.time timestamp FROM chat ch LEFT JOIN users us ON us.id = ch.sender_id LEFT JOIN users ur ON ur.id = ch.reciever_id LEFT JOIN code_desc co ON co.id = ch.dept_id WHERE (us.id = '$user_id' AND ur.id = '$member_id') OR (us.id = '$member_id' AND ur.id = '$user_id') ORDER BY ch.chat_id DESC";


    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);
    $chat_message = array();


    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
             $chat_message[] = array(
                 'chat_id' => $row['chat_id'],
                 'message' => $row['message'],
                 'sender_id' => $row['sender_id'],
                 'sender_name' => $row['sender_name'],
                 'reciever_id' => $row['reciever_id'],
                 'reciever_name' => $row['reciever_name'],
                 'timestamp' => $row['timestamp']
             );
        }
                     
        $message = json_encode(
            array(
                'message' => 'Great here are your data',
                'status' => 'success',
                'data' => $chat_message
            )
        );
        exit($message);

    } else { 
       
        $message = json_encode(
            array(
                'message' => 'Empty data',
                'status' => 'success'
            ) 
        );
        exit($message); 
    }
}else {
       
        $message = json_encode(
            array(
                'message' => 'Invalid Parameters',
                'status' => 'failed'
            ) 
        );
        exit($message); 
    }

//     function get_is_read_count ($conn, $user_id, $reciever_id){
//         $query = "SELECT us.id user_id, us.f_name, us.l_name, us.email, us.phone, us.gender, us.profile_pic, us.bio, us.country, us.city, us.postal_addr, co_r.desc role FROM users us  LEFT JOIN code_desc co_r ON co_r.id = us.role  
// ORDER BY `user_id`  DESC";
        
//         $result = mysqli_query($conn, $query);
//         return $result;
//     }