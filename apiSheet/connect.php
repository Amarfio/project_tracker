<?php
$host = 'localhost';
$user = 'root';
// $password = 'firefox';
$password = '';
// $db_name = 'cheat';
// $db_name = 'union';
$db_name = 'project_tracker_test_db';
$port = '3306';

$conn = new mysqli($host, $user, $password, $db_name, $port);

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