<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';
if (isset($_GET['task_id'])) {
    $task_id = mysqli_escape_string($conn, $_GET['task_id']);

    $query = "SELECT p.project_id, p.name project_name, p.description project_description, p.version_no, p.start_date project_start_date, p.end_date project_end_date, c_ver.desc version_name, c_dept.id department_id, c_dept.desc department_name, t.task_id as task_id, t.description as task_description,p.is_approved is_approved, t.ready_4_test, t.start_date task_start_date, t.end_date task_end_date, t.completion, cl.name as client, CONCAT(u_by.f_name, ' ' , u_by.l_name) as assigned_by, CONCAT(u_to.f_name, ' ' , u_to.l_name) as assigned_to, u_to.id as assigned_to_id, c_pri.id priority_id, c_pri.desc as priority, c_sta.id status_id, c_sta.desc as status  FROM tasks t LEFT JOIN projects p ON t.project_id = p.project_id LEFT JOIN users u_by ON u_by.id = t.assigned_by LEFT JOIN users u_to ON u_to.id = t.assigned_to LEFT JOIN code_desc c_pri ON c_pri.id = t.priority LEFT JOIN code_desc c_ver ON c_ver.id = p.version_no LEFT JOIN code_desc c_dept ON c_dept.id = p.dept_id LEFT JOIN code_desc c_sta ON c_sta.id = t.status LEFT JOIN clients cl ON cl.client_id = t.client_id   WHERE t.task_id = '$task_id' ORDER BY `task_id`  DESC";

    
    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);
    $tast_arr = array();
    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tast_arr[] = $row;
        } 
            
        $message = json_encode(
            array(
                'message' => 'Great here are your data',
                'status' => 'success',
                'data' => $tast_arr
            )
        );
        exit($message);

        // echo json_encode($tast_arr);
    } else {
        
        $message = json_encode(
            array(
                'message' => 'Empty data',
                'status' => 'success'
            ) 
        );
        exit($message); 
    }
}