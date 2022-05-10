<?php
    $host = 'localhost';
    $user = 'root';
    $password = 'firefox';
    // $db_name = 'cheat';
    // $db_name = 'union';
    $db_name = 'project_tracker_db';

$conn = new mysqli($host, $user, $password, $db_name);

// if ($conn) {
//     echo json_encode(
//         array(
//             'message' => 'Database Connected'
//         )
//     );
// }else{
//     echo json_encode(
//         array(
//             'message' => 'Database Not Connected'
//         )
//     );
// }