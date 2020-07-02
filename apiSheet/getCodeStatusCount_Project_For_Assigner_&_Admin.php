
<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';


function get_total_task_count( $conn){


        
    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.status) total_task_status FROM tasks t WHERE t.is_approved = 1 AND t.status = '$status_id'";
    $query = "SELECT COUNT(p.project_id) total_projects FROM projects p";
    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

        $row = mysqli_fetch_assoc($result);
            // $count_total_status[] = $row;
              
        return array(
            'description' => 'total',
            'total_projects' => $row['total_projects']
        );


}


function get_status_count($status_id, $conn){
        
    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.status) total_task_status FROM tasks t WHERE t.is_approved = 1 AND t.status = '$status_id'";
    $query = "SELECT COUNT(t.status) total_task_status FROM tasks t LEFT JOIN projects p ON p.project_id = t.project_id LEFT JOIN users u ON u.id =  t.assigned_to  WHERE p.is_approved = 1 AND t.status = '$status_id' ";
    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

        $row = mysqli_fetch_assoc($result);
            // $count_total_status[] = $row;
        
        return $row['total_task_status'];

}


function get_approved_count($conn){
        
    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.task_id) approved_project FROM tasks t LEFT JOIN projects p ON p.project_id = t.project_id WHERE p.is_approved = 1";
    $query = "SELECT COUNT(p.project_id) approved_projects FROM projects p WHERE p.is_approved = 1 ";

    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

        $row = mysqli_fetch_assoc($result);
        $count_total_status = $row['approved_projects'];
        

        return array(
            'description' => 'Approved',
            'approved_count' => $row['approved_projects']
        );
        

}

function get_unapproved_count($conn){
        
    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.task_id) unapproved_project FROM tasks t LEFT JOIN projects p ON p.project_id = t.project_id WHERE p.is_approved = 0 ";
    $query = "SELECT COUNT(p.project_id) unapproved_project FROM projects p WHERE p.is_approved = 0 ";

    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

        $row = mysqli_fetch_assoc($result);
        $count_total_status[] = $row['unapproved_project'];
        
        return array(
            'description' => 'unapproved',
            'unapproved_count' => $row['unapproved_project']
        );
        // return $row['unapproved_project'];

}





        $project_statistics = array(
            
            "total_projects" => get_total_task_count( $conn ),
            "approved_project" => get_approved_count($conn),
            "unapproved_project" => get_unapproved_count($conn)
        );
     
        echo json_encode($project_statistics);

