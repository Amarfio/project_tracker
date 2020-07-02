<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once 'connect.php';

function autoLoginAfterResetPassword($email, $password, $hash_pass, $conn){
    // echo $email = $email;
    // echo $password = $password;
    // echo $hash_pass = $hash_pass;

    $query = "SELECT u.id as user_id, u.f_name, u.l_name, u.username, u.is_dpt_head, u.email, u.phone, u.country, u.city, u.can_approve, u.postal_addr AS address, u.reset as token, c.id AS department_id, c.desc department, d.id AS role_id, d.desc as role from users u LEFT JOIN code_desc c ON u.dept = c.id left JOIN code_desc d ON u.role = d.id WHERE (email = '$email') AND hash_pass = '$hash_pass' LIMIT 1";
    $result = mysqli_query($conn, $query); 
    $num = mysqli_num_rows($result);
    if ($num > 0) {
        $row = mysqli_fetch_assoc($result);
        $message = json_encode(
            array(
                'message' => 'Login successful',
                'status' => 'success',
                'data' => [ 
                    'user_id' => $row['user_id'],
                    'first_name' => $row['f_name'],
                    'last_name' => $row['l_name'],
                    'username' => $row['username'],
                    'department_id' => $row['department_id'],
                    'department' => $row['department'],
                    'role_id' => $row['role_id'],
                    'role' => $row['role'],
                    'is_dept_head' => $row['is_dpt_head'],
                    'can_approve' => $row['can_approve'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'address' => $row['address'],
                    'country' => $row['country'],
                    'city' => $row['city']

                ],
                'token' => $row['token'],
                'auth' => true
            )
        );
        exit($message);
    } else {
        $message = json_encode(
            array(
                'message' => 'unsuccessful login',
                'status' => 'failed'
            )
        );
        exit($message);
    }
}



function updateResetLink($email, $password, $hash_pass, $conn){
        // update reset number
        $set_password = md5($email . null . time()); 
        $up_query = "UPDATE `users` SET `reset` = '$set_password' WHERE `users`.`email` = '$email'";
        $up_result = mysqli_query($conn, $up_query);
        if ($up_result == 1) {

            autoLoginAfterResetPassword($email, $password, $hash_pass, $conn); 
            
        } else{
            $message = json_encode(
                array(
                    'message' => 'Could not get hash reset ',
                    'status' => 'failed'
                )
            );
            exit($message);
        }
}

$data = json_decode(file_get_contents("php://input"));
if(isset($data)){
    // echo json_encode($data);

   
    if (!isset($data->email) || !isset($data->hash_pass)) {
        $message = json_encode(
            array(
                'data' => 'Invalid Request Parameters',
                'message' => 'failed'
            )
        );
        exit($message);
    }else{

        $email = strtolower(mysqli_real_escape_string($conn, $data->email));
        $password = mysqli_real_escape_string($conn, $data->hash_pass); 
        $hash_pass = md5(null . $password . $email. 'password');
    
        $query = "UPDATE `users` SET `hash_pass` = '$hash_pass' WHERE `users`.`email` = '$email'";
        $result = mysqli_query($conn, $query);
        if ($result == 1) {

            updateResetLink($email, $password, $hash_pass, $conn);

        } else {
            $message = json_encode(
                array(
                    'message' => 'Failed to set password',
                    'data' => 'init already exist',
                    'status' => 'failed'
                )
            );
            exit($message);
        }
    }

    }else{
        $message = json_encode(
            array(
                'message' => 'Please check the parameter sent',
                'data' => 'Invalid Request',
                'status' => 'failed'
            )
        );

        exit($message);
    }


