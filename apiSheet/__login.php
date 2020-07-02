<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



$data = json_decode(file_get_contents("php://input"));
if (isset($data)) {
    // echo json_encode($data);


    if (!isset($data->username) || !isset($data->password)) {

        $message =  json_encode(
            array(
                'message' => 'Invalid Request Parameters',
                'status' => 'failed'
            )
        );
        exit($message);

    } else {

            $username = $data->username;
            $password = $data->password;
          
            if ($username == 'ampahkwabena@gmail.com' && $password == 'ampah') {

                $message = json_encode(
                    array(
                        'message' => 'Login successful',
                        'status' => 'success',
                        'data' => $username
                    )
                );
            
                exit($message);

            }else{

                $message = json_encode(
                    array(
                        'message' => 'Credential do not match',
                        'status' => 'failed'
                    )
                );
            
                exit($message);

            }
            
    }
}
 else {

    $message = json_encode(
        array(
            'message' => 'Invalid Request',
            'status' => 'failed'
        )
    );

    exit($message);
    
}
