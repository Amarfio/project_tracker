<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 'On');
    $host = 'localhost';
    $user = 'root';
    $password = '';
    // $db_name = 'cheat';
    // $db_name = 'union';
    $db_name = 'project_tracker_db';


    $conn = new mysqli($host, $user, $password, $db_name, '3306');
    // try{
        


    // }catch(Exception $e){
    //     echo($e->message());
    // }
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