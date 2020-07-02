<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';



if (isset($_GET['task_id'])) {
    $task_id = mysqli_escape_string($conn, $_GET['task_id']);

    $query = "SELECT * FROM comments LEFT JOIN replies ON comments.comment_id = replies.c_id WHERE comments.task_id = '$task_id' ORDER BY comments.comment_id DESC";


    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);


    function getUsernameById($user_id)
    {
        global $conn;
        $queri = "SELECT username FROM users WHERE id= '$user_id' LIMIT 1";
        $result = mysqli_query($conn, $queri);
        // return the username
        $row = mysqli_fetch_assoc($result);
        $user_name = $row['username'] ;
        return $user_name;
    }
 

    $comments= array();
    // $task_arr = array(
        
    // );
    // $projects_wit_tasks = array();

    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // extract($row);
        if (!isset($comments[$row['comment_id']])) {

            $comments[$row['comment_id']] = array(
                    'comment_id' => $row['comment_id'],
                    'comment' => $row['comment'],
                    "attach"   => $row['attach'],
                    "posted_by"   => $row['posted_by'],
                    "posted_by_name"   => getUsernameById($row['posted_by']),
                    "comment_date" => $row['c_date'],
                    "reply" => array()
            );
        }
        // Add this phone number to the `PhoneNumbers` array
        $comments[$row['comment_id']]['reply'][] = array(
                'reply_id' => $row['reply_id'],
                "reply" => $row['reply'],
                "replied_by" => $row['replied_by'],
                "replied_by_name" => getUsernameById($row['replied_by']),
                "reply_date" => $row['r_date']
        );

        }
        $comments = array_values($comments);

        echo json_encode($comments);
    } else {
        echo 'no date exit';
    }
}
