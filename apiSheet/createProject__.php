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
    isset($data) && isset($data->version_no) && isset($data->project_name) && isset($data->user_id) &&
    isset($data->start_date) && isset($data->end_date) 
){
    // echo json_encode($data);

    $version_no = mysqli_real_escape_string($conn, $data->version_no);
    $name = mysqli_real_escape_string($conn, $data->project_name);
    $description = mysqli_real_escape_string($conn, $data->project_description);
    $dept_id = mysqli_real_escape_string($conn, $data->dept_id);
    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $start_date = mysqli_real_escape_string($conn, $data->start_date);
    $end_date = mysqli_real_escape_string($conn, $data->end_date);
    // $ip_address = mysqli_real_escape_string($conn, $data->ip_address);
    // $location = mysqli_real_escape_string($conn, $data->location); 
    $start_date = date("Y-m-d", strtotime($start_date));
    $end_date = date("Y-m-d", strtotime($end_date));
    
    $ip_address = 'DF45-123E-34E-24'; 
    $location = 'Accra Ghana';

    function get_all_approval_users ($conn){
        $query = "SELECT email FROM `users` WHERE can_approve = 1";
        $result = mysqli_query($conn, $query);

        $num = mysqli_num_rows($result);
        
        if ($num > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
           
            // $to = "elijahashitey002@gmail.com";
            $to = $row['email'];
            $subject = "UNION SYSTEMS GLOBAL";
            $txt = "New Project has been created: visit: http://192.168.1.74/login";
            $headers = "UNION SYSTEMS GLOBAL" . "\r\n" . "CC: usg@gmail.com";

              if (true) { 

                    $message = json_encode(
                        array(
                            'message' =>'Approvers have been notified',
                            'status' => 'success'
                        )
                    );
                    exit($message);

                }   else {
                    $message =  json_encode(
                        array(
                            'data' => null,
                            'message' => 'Failed to send email to approvers',
                            'status' => 'failed'
                        )
                    );
                    exit($message);
               }    

            // echo json_encode($clients_arr);
            } 
        } else {
            echo 'no date exit';
        }

    }

    get_all_approval_users($conn);

    // $query = "INSERT INTO `projects` (`project_id`, `version_no`, `name`, `description`, `dept_id`, `posted_by`, `ip_address`, `location`, `start_date`, `end_date`) VALUES (NULL, '$version_no', '$name', '$description', '$dept_id', '$user_id', '$ip_address', '$location', '$start_date', '$end_date')";
    // $result = mysqli_query($conn, $query);

    // if ($result == 1) {

    

        // $project_id = mysqli_insert_id($conn);  
        // $message = json_encode(
        //     array(
        //         'message' =>'project created successfully',
        //         'status' => 'success',
        //         'data' => [
        //             'project_id' => $project_id,
        //             'version_no' => $version_no,
        //             'project_name' => $name,
        //             'department_id' => $dept_id,
        //             'start_date' => $start_date,
        //             'end_date' => $end_date,
        //         ],
        //         'project_id' => $project_id
        //     )
        // );
        // exit($message);

    // } else {
    //     $message = json_encode(
    //         array(
    //             'message' => 'Failed to create project',
    //             'status' => 'failed'
    //         )
    //     );
    //     exit($message);

    // }

}else{

    $message = json_encode(
        array(
            'message' => 'Invalid Request',
            'status' => 'failed'
        )
    );
    exit($message);

}

