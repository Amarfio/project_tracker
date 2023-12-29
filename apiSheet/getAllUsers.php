<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';



function execution($conn, $query){
    
    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);

    $users = array();


    if ($num > 0) { 

        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
         }
         echo json_encode($users);

    } else {
        echo 'no date exit';
    }

}

if (isset($_GET['department_id'])) {
    $department_id = mysqli_escape_string($conn, $_GET['department_id']); 

    
$query_users_by_department_id = "SELECT us.id user_id, us.f_name, us.l_name, co_d.desc, us.email, us.phone, us.gender, us.profile_pic, us.bio, us.country, us.city, us.postal_addr, us.is_active, co_d.id department_id, co_d.desc department, co_r.desc role FROM users us LEFT JOIN code_desc co_d ON co_d.id = us.dept LEFT JOIN code_desc co_r ON co_r.id = us.role WHERE co_d.id = '$department_id'";

    execution($conn, $query_users_by_department_id);

} else{
    
$query_all_users = "SELECT us.id user_id, us.f_name, us.l_name, co_d.desc, us.email, us.phone, us.gender, us.profile_pic, us.bio, us.country, us.city, us.postal_addr, us.is_active, co_d.id department_id, co_d.desc department, co_r.desc role FROM users us LEFT JOIN code_desc co_d ON co_d.id = us.dept LEFT JOIN code_desc co_r ON co_r.id = us.role";

    execution($conn, $query_all_users);
}