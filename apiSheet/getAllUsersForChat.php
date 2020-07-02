<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';


    function get_is_read_count ($conn, $sender_id, $reciever_id){
        $query = "SELECT COUNT(ch.chat_id) message_count_is_read FROM chat ch WHERE ch.sender_id = '$reciever_id' AND ch.reciever_id = '$sender_id'  AND ch.is_read = 0";
        
        $result = mysqli_query($conn, $query);
        $unread_message_count = array();
        $row = mysqli_fetch_assoc($result);
        $unread_message_count = $row['message_count_is_read'];
        
        return array(
            'is_read' => 'unread',
            'is_read_count' => $row['message_count_is_read']
        );
        

         
    }



function execution($conn, $query, $sender_id){
    
    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);

  
    $users_for_chat = array();

    if ($num > 0) { 

        while ($row = mysqli_fetch_assoc($result)) { 
            $users_for_chat[] = array(
                'user_id' => $row['user_id'],
                'f_name' => $row['f_name'],
                'l_name' => $row['l_name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'gender' => $row['gender'],
                'profile_pic' => $row['profile_pic'],
                'bio' => $row['bio'],
                'country' => $row['country'],
                'city' => $row['city'],
                'postal_addr' => $row['postal_addr'], 
                'role' => $row['role'],
                'is_read_user_messages' => get_is_read_count ($conn, $sender_id, $row['user_id'])
            );
         }
         $message = json_encode($users_for_chat);
            exit($message);

    } else {
        echo 'no date exit';
    }

}

if ( isset($_GET['sender_id'])) {
    $sender_id = mysqli_escape_string($conn, $_GET['sender_id']); 

    
$query_users_for_chat = "SELECT us.id user_id, us.f_name, us.l_name, us.email, us.phone, us.gender, us.profile_pic, us.bio, us.country, us.city, us.postal_addr, co_r.desc role FROM users us  LEFT JOIN code_desc co_r ON co_r.id = us.role ";

    execution($conn, $query_users_for_chat, $sender_id);

} else{

}