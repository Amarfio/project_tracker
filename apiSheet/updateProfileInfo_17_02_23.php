<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once 'connect.php';

$data = json_decode(file_get_contents("php://input"));

if(
    isset($data) && isset($data->user_id) && isset($data->username) && isset($data->first_name) && isset($data->last_name) &&
    isset($data->address) && isset($data->city) && isset($data->country) && isset($data->bio) 
){
    // echo json_encode($data); 

    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $username = mysqli_real_escape_string($conn, $data->username);
    $first_name = mysqli_real_escape_string($conn, $data->first_name);
    $last_name = mysqli_real_escape_string($conn, $data->last_name);
    $address = mysqli_real_escape_string($conn, $data->address);
    $city = mysqli_real_escape_string($conn, $data->city);
    $country = mysqli_real_escape_string($conn, $data->country);
    $bio = mysqli_real_escape_string($conn, $data->bio);

    $query = "UPDATE `users` SET `username` = '$username', `f_name` = '$first_name', `l_name` = '$last_name', `postal_addr` = '$address', `city` = '$city', `country` = '$country', `bio` = '$bio'  WHERE `users`.`id` = '$user_id'";

    
    $result = mysqli_query($conn, $query);

    if ($result == 1) { 
        
        $query = "SELECT u.id as user_id, u.f_name, u.profile_pic, u.bio, u.l_name, u.gender, u.username, u.is_dpt_head, u.can_approve, u.email, u.phone, u.country, u.city, u.postal_addr AS address, u.reset as token, c.id AS department_id, c.desc department, d.id AS role_id, d.desc as role from users u LEFT JOIN code_desc c ON u.dept = c.id left JOIN code_desc d ON u.role = d.id WHERE u.id = '$user_id'";
        $result = mysqli_query($conn, $query);
        $num = mysqli_num_rows($result);

        if ($num > 0) {
            $row = mysqli_fetch_assoc($result);
            echo json_encode(
                array(
                    'message' => 'User update successfully',
                    'status' => 'success',
                    'data' => [
                        'user_id' => $row['user_id'],
                        'first_name' => $row['f_name'],
                        'last_name' => $row['l_name'],
                        'gender' => $row['gender'],
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
                        'bio' => $row['bio'],
                        'city' => $row['city']
                    ],
                    'token' => $row['token'],
                    'auth' => true,
                    'profile_pic' => $row['profile_pic']
                )
            );
        } else {
            echo json_encode(
                array(
                    'message' => 'failed to update user info',
                    'status' => 'failed'
                )
            );
        }

    }else {
            echo json_encode(
                array(
                    'message' => 'failed to update user info',
                    'status' => 'failed'
                )
            );
        }
 
}