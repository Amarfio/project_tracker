
<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';


function get_total_project_count( $conn, $department_id ){


        
    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.status) total_task_status FROM tasks t WHERE t.is_approved = 1 AND t.status = '$status_id'";
    $query = "SELECT COUNT(p.project_id) total_project_count FROM projects p WHERE  p.dept_id = '$department_id' ";
    $result = mysqli_query($conn, $query); 
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

        $row = mysqli_fetch_assoc($result);
            // $count_total_status[] = $row;
              
        return array(
            'description' => 'total',
            'total_projects' => $row['total_project_count']
        );


}


function get_status_count($status_id, $department_id, $conn){
        
    // $query = "SELECT attach FROM comments c WHERE c.task_id = '$task_id' AND c.attach != ''";
    // $query = "SELECT COUNT(t.status) total_task_status FROM tasks t WHERE t.is_approved = 1 AND t.status = '$status_id'";
    
    // $query = "";
    $query = "SELECT COUNT(p.status) total_project_count FROM projects p WHERE  p.status  = '$status_id' AND p.dept_id = '$department_id'";

    if($status_id == 116 ){
        // $query = "SELECT COUNT(project_id) total_overdue FROM projects WHERE NOW()>end_date AND dept_id = '$department_id'";
        $query = "SELECT COUNT(p.status) total_project_count FROM projects p WHERE (CURRENT_DATE > end_date AND p.is_approved=1) AND (SELECT AVG(t.completion)<100 from tasks t WHERE t.project_id = p.project_id) AND p.dept_id = '$department_id'";
    }
    elseif($status_id == 85 ){
        // $query = "SELECT COUNT(project_id) total_overdue FROM projects WHERE NOW()>end_date AND dept_id = '$department_id'";
        $query = "SELECT COUNT(p.status) total_project_count FROM projects p WHERE ( p.is_approved=1) AND p.dept_id = '$department_id'";
    }
    // elseif($status_id == 88){
    //     $query = "SELECT COUNT(p.status) total_project_count FROM projects p WHERE (p.is_approved=1) AND (SELECT AVG(t.completion)=100 from tasks t WHERE t.project_id = p.project_id) AND p.dept_id = '$department_id'";
      
    // }
    // else{
    //     $query = "SELECT COUNT(p.status) total_project_count FROM projects p WHERE  p.status  = '$status_id' AND p.dept_id = '$department_id'";
    // }
    $result = mysqli_query($conn, $query);
    // $num = mysqli_num_rows($result);
    $count_total_status = array();

        $row = mysqli_fetch_assoc($result);
            // $count_total_status[] = $row;
        
        return $row['total_project_count'];

}




if (isset($_GET['init'])  ) {
    $init = mysqli_escape_string($conn, $_GET['init']);
    $department_id = mysqli_escape_string($conn, $_GET['department_id']);

    $query = "SELECT * FROM code_desc co_psta WHERE co_psta.init = '$init' ";


    $result = mysqli_query($conn, $query); 

    $num = mysqli_num_rows($result);

    $codes = array();


    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // extract($row);
            if (!isset($codes[$row['init']])) {
                // If this is the first row for this contact, create an entry in the results
                $codes[$row['init']] = array(
                    "init" => $row['init'],
                    "code_desc" => array(),
                    "total_projects" => get_total_project_count( $conn, $department_id )
                );
            }
            // Add this phone number to the `PhoneNumbers` array
            $codes[$row['init']]['code_desc'][] = array(
                'status_id' => $row['id'],
                'status_init' => $row['init'],
                "status_init_desc" => $row['init_desc'],
                "status" => $row['desc'],
                "status_count" =>get_status_count($row['id'],$department_id, $conn)
            );
        }
        $codes = array_values($codes);

        echo json_encode($codes);
    } else {
        echo 'empty';
    }
}

