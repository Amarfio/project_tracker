<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php'; 
    function get_name_of_department_head ($department_id, $conn){
        $query = "SELECT CONCAT(u.f_name, ' ', u.l_name) department_head FROM users u, code_desc c  WHERE u.dept = '$department_id', c.id = '$department_id' AND u.is_dpt_head = '1'";

        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $department_head = $row['department_head'];
        return $department_head;

    }

    $query = "SELECT * FROM code_desc co WHERE co.init = 'dpt'";


    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);

    $departments = array();


    if ($num > 0) { 

        while ($row = mysqli_fetch_assoc($result)) {
            $departments[] = array(
                'department_id' => $row['id'],
                'department_init' => $row['init'],
                'department_init_desc' => $row['init_desc'],
                'department_desc' => $row['desc']
                // 'department_head' => get_name_of_department_head($row['id'], $conn)
            );
         }
         echo json_encode($departments);
    } else {
        echo 'no date exit';
    }

