<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//get the database connection
require_once ('connect.php');

function execution($conn, $query){
    
    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);

    $results = array();


    if ($num > 0) { 

        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
         }
        //  $data = ([''])
        //  echo json_encode($results);
        echo json_encode(
            array(
                'messge'=>'data found',
                'status'=>200,
                'data'=> $results
            )
            );

    } else {
        echo json_encode(
            array(
                'messge'=>'no data found',
                'status'=>200,
                'data'=> null
            )
        );
    }

}

//method to get user id by the email provided
function getUserIdByEmail($email)
{
    global $conn;
    $queri = "SELECT Login_Id FROM users WHERE Email= '$email' LIMIT 1";
    $result = mysqli_query($conn, $queri);
    // return the username
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['Login_Id'] ;
    // echo json_encode($developer_email); die();
    return $user_id;

}

if (isset($_GET['email'])) {
    $email = mysqli_escape_string($conn, $_GET['email']); 

    //get the user id with the email provided...
    // $user_id = getUserIdByEmail($email);
    
    //query to get all assigned issues to the user id that are in work in progress and assigned to user
    // $queryByIncidentsAssignedTo = "SELECT * from incident WHERE Status IN(2,3) AND Assigned_To = $user_id";
    //query to get user details with provided email 
    $queryByEmail = "SELECT * from vw_eng_rm_members where email = '$email'";
    // echo json_encode($queryByEmail); die();
    execution($conn, $queryByEmail);

}else if (isset($_GET['first_name'])){
    $firstname= mysqli_escape_string($conn, $_GET['first_name']);

    $queryByFirstName = "SELECT * from vw_eng_rm_members where f_name = '$firstname'";
    execution($conn, $queryByFirstName);
}
else{
    $queryToGetAllUsers = "SELECT * FROM users";
    execution($conn, $queryToGetAllUsers);
}