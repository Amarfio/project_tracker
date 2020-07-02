<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once 'connect.php'; 

$data = json_decode(file_get_contents("php://input"));
if(isset($data) && isset($data->client)){
    // echo json_encode($data);

    $client = mysqli_real_escape_string($conn, $data->client);
   

    $query = "INSERT INTO `clients` (`client_id`, `name`) VALUES (NULL, '$client')";
    $result = mysqli_query($conn, $query);
    if ($result == 1) {
        $message = json_encode(
            array(
                'message' => 'client created successfully',
                'status' => 'success',
                'data' => [
                    'client' => $client
                ]
            )
        );
        exit($message);


    } else {
        $message = json_encode(
            array(
                'message' => 'Failed create Client or Code already exist',
                'status' => 'failed'
            )
        );
        exit($message);

    }

}else{
    $message = json_encode(
        array(
            'message' => 'Invalid Request',
            'status' => 'failed'
        )
    );
    exit($message);
}

