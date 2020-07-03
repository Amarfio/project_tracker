
<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With"); 
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
require_once 'connect.php';

    

    function get_department_name($department_id, $conn){

        $query = "SELECT co.desc department_name FROM code_desc co WHERE co.id = '$department_id'";
        $result = mysqli_query($conn, $query);
       $row = mysqli_fetch_array($result);
       return $row['department_name'];
    
  
    }

    function get_all_approval_users ($conn, $project_id){

   $query_project = "SELECT p.*, p.status status_id, co_sta.desc status_name, co.id version_id, co.init version_init, co.init_desc version_code, co.desc version_name, p.comment , p.comment_by comment_by_id, CONCAT(u.f_name, ' ', u.l_name) comment_by_name FROM projects p LEFT JOIN code_desc co ON co.id = p.version_no LEFT JOIN code_desc co_sta ON co_sta.id = p.status LEFT JOIN users u ON u.id = p.comment_by WHERE project_id = '$project_id'";

    $result_project = mysqli_query($conn, $query_project);
    
    $num_project = mysqli_num_rows($result_project); 
    $row_project = mysqli_fetch_assoc($result_project);

    $project_id =  $row_project['project_id'];
    $version_no  =  $row_project['version_no'];
    $version_name  =  $row_project['version_name'];
    $name =  $row_project['name'];
    $description =  $row_project['description'];
    $is_approved =  $row_project['is_approved'];
    $project_status_id =  $row_project['status_id'];
    $project_status =  $row_project['status_name'];
    $department_id =  $row_project['dept_id'];
    $department =  get_department_name($row_project['dept_id'], $conn); 
    $comment =  $row_project['comment'];
    $comment_by_id =  $row_project['comment_by_id']; 
    $comment_by_name =  $row_project['comment_by_name']; 
    $start_date =  $row_project['start_date'];
    $end_date =  $row_project['end_date'];

        $query = "SELECT email FROM `users` WHERE can_approve = 1";
        $result = mysqli_query($conn, $query);

        $num = mysqli_num_rows($result);
        $email_arr = array();
        if ($num > 0) {
            while ($row = mysqli_fetch_assoc($result)) {

            $email_arr[] = $row['email'];

            } 

            $to = 'ampahkwabena55@gmail.com';
            $subject = "UNION SYSTEMS GLOBAL";
              $txt = "New Project has been created and approved: ".  "http://192.168.1.195:84/project_tracker/login". "\r\n" ;
            $txt = $txt . 'Project ID: PRO-0000' . $project_id . "\r\n" ;
            $txt = $txt . 'Description: ' . $description . "\r\n" ;
            $txt = $txt . 'Version : ' . $version_name . "\r\n" ;
            $txt = $txt . 'Department: ' . $department . "\r\n" ;
            $txt = $txt . 'Start Date: ' . $start_date . "\r\n" ;
            $txt = $txt . 'End Date: ' . $end_date . "\r\n" ;
        $headers = "From: USG" . "\r\n" . "CC: " .  implode (", ", $email_arr);

           

           if( mail($to,$subject,$txt,$headers)){
                return true;

           }else{

                $message = json_encode(
                    array(
                        'message' => 'Failed to send email notication',
                        'status' => 'failed'
                    )
                );
                exit($message);

           }
           
        } else {
            
            $to = 'ampahkwabena55@gmail.com';
            $subject = "UNION SYSTEMS GLOBAL";
              $txt = 'New Project has been created and approved:  http://192.168.1.195/project_tracker/login'. "\r\n" ;
            $txt = $txt . 'Project ID: ' . $project_id . "\r\n" ;
            $txt = $txt . 'Description: ' . $description . "\r\n" ;
            $txt = $txt . 'Version : ' . $version_name . "\r\n" ;
            $txt = $txt . 'Department: ' . $department . "\r\n" ;
            $txt = $txt . 'Start Date: ' . $start_date . "\r\n" ;
            $txt = $txt . 'End Date: ' . $end_date . "\r\n" ;
            $headers = "From: USG" ;

            if( mail($to,$subject,$txt,$headers)){
                return true;
           }
           
        }

    }

function approve ($conn, $project_id){
    
    $query = "UPDATE `projects` SET `status` = 84 WHERE `projects`.`project_id` = '$project_id';";

    $result = mysqli_query($conn, $query);

    if ($result == 1) {
        $message = json_encode(
            array(
                'message' => 'Project has been approved',
                'status' => 'success',
                'project_id' => $project_id
            )
        );
        exit($message);
    } else{
        $message = json_encode(
            array(
                'message' => 'failed to approve',
                'status' => 'failed',
            )
        );

        exit($message);
    }
}




if (isset($_GET['project_id']) ) {

    $project_id = mysqli_escape_string($conn, $_GET['project_id']);
    if(get_all_approval_users($conn, $project_id) ){
        
        approve ($conn, $project_id);


    }else{

        approve ($conn, $project_id);
    }

}