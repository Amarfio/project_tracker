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

    $programs = array();


    if ($num > 0) { 

        while ($row = mysqli_fetch_assoc($result)) {
            $programs[] = $row;
         }
         echo json_encode($programs);

    } else {
        echo 'no data exist';
    }

}

if (isset($_GET['program_id'])) {
    $program_id = mysqli_escape_string($conn, $_GET['program_id']); 

    
// $query_programs_by_id = "SELECT us.id user_id, us.f_name, us.l_name, co_d.desc, us.email, us.phone, us.gender, us.profile_pic, us.bio, us.country, us.city, us.postal_addr, co_d.id department_id, co_d.desc department, co_r.desc role FROM programs us LEFT JOIN code_desc co_d ON co_d.id = us.dept LEFT JOIN code_desc co_r ON co_r.id = us.role WHERE co_d.id = '$department_id' AND (us.role= 70 AND us.is_active = 1)";
$query_programs_by_id = "SELECT * FROM programs WHERE id='$program_id'";

    execution($conn, $query_programs_by_id);

} else{
    
    //get all developers from the database....
    $query_all_programs = "SELECT * FROM programs prog ORDER BY prog.created_at DESC";

    execution($conn, $query_all_programs);
}