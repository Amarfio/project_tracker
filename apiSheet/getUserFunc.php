<?php


function get_user_info ($conn, $user_id){
    
        $query = "SELECT u.id as user_id, u.f_name, u.profile_pic, u.l_name, u.username, u.is_dpt_head, u.can_approve, u.email, u.phone, u.country, u.city, u.postal_addr AS address, u.reset as token, c.id AS department_id, c.desc department, d.id AS role_id, d.desc as role from users u LEFT JOIN code_desc c ON u.dept = c.id left JOIN code_desc d ON u.role = d.id WHERE `u`.`id` = '$user_id'";
        $result = mysqli_query($conn, $query);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            $row = mysqli_fetch_assoc($result);
             $message =  json_encode(
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
                        'city' => $row['city'],
                        'profile_pic' => $row['profile_pic']
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