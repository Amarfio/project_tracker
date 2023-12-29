<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once 'connect.php';
require_once 'functions/get_IP_Location.php';
require_once 'functions/activity_logs.php';
require_once 'functions/isDescriptionAvailable.php';
require_once 'functions/dateChecks.php';
require_once 'functions/response.php';


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
    $file_name = mysqli_real_escape_string($conn, $data->fileName);
    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $start_date = mysqli_real_escape_string($conn, $data->start_date);
    $end_date = mysqli_real_escape_string($conn, $data->end_date);
    $owner = mysqli_real_escape_string($conn, $data->owner);
    $owner_2 = mysqli_real_escape_string($conn, $data->owner_2);
    // $ip_address = mysqli_real_escape_string($conn, $data->ip_address);
    // $location = mysqli_real_escape_string($conn, $data->location); 
    $start_date = date("Y-m-d", strtotime($start_date));
    $end_date = date("Y-m-d", strtotime($end_date));
    $priority = mysqli_real_escape_string($conn, $data->priority);
    $ip_address = 'DF45-123E-34E-24';
    $location = 'Accra Ghana';

    // echo json_encode($priority); die();
    // function get_all_approval_users ($conn){
    //     $query = "SELECT email FROM `users` WHERE can_approve = 1";
    //     $result = mysqli_query($conn, $query);

    //     $num = mysqli_num_rows($result);
    //     $email_arr = array();

    //     if ($num > 0) {
    //         while ($row = mysqli_fetch_assoc($result)) {

    //         $email_arr[] = $row['email'];

    //         } 

    //         $to = 'ampahkwabena55@gmail.com';
    //         $subject = "UNION SYSTEMS GLOBAL";
    //         $txt = 'New Project has been created :  http://192.168.1.78/project_tracker/login';
    //         $headers = "From: ampahkwabena55@gmail.com" . "\r\n" . "CC: " .  implode (", ", $email_arr);

           

    //        if( mail($to,$subject,$txt,$headers)){
    //             return true;
    //        }
           
    //     } else {
    //         echo 'no date exit';
    //     }

    // }

    //check the date
    $limitResult = checkDatesWithSameStartDatesAsNewProjectStartDate($conn,$owner, $start_date);
    // echo $limitResult; die();
    // die();

    if($limitResult === 1){
        $message = json_encode(
            array(
                'message' => 'Project start date limit for the week exceeded !!!',
                'status' => 'failed'
            )
        );
        exit($message);
        
    }
    //check if project end date is earlier than its start date...
    else if(checkForEarlierDate($end_date, $start_date)){
        $message = response("project end date cannot be earlier than its start date", "failed", array("start date"=>$data->start_date, "end date"=>$data->end_date));
        exit($message);
    }
    //check if description already exists in the records...
    else if(isDescriptionAvailable($conn, "projects", $description) === false){
        $query = "INSERT INTO `projects` (`project_id`, `version_no`, `name`, `description`, `attach`, `dept_id`, `posted_by`, `ip_address`, `location`, `start_date`, `end_date`, `created_at`, `owner`, `s_owner`,`priority`) VALUES (NULL, '$version_no', '$name', '$description', '$file_name', '$dept_id', '$user_id', '$ip_address', '$location', '$start_date', '$end_date', NOW(),'$owner', '$owner_2', '$priority' )";
        // echo ($query);
        // die();
        $result = mysqli_query($conn, $query);
    
        if ($result == 1) {
            $project_id = mysqli_insert_id($conn);   
              
           
            $message = json_encode(
                array(
                    'message' =>'project created successfully',
                    'status' => 'success',
                    'data' => [
                        'project_id' => $project_id,
                        'version_no' => $version_no,
                        'project_name' => $name,
                        'department_id' => $dept_id,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                    ],
                    'project_id' => $project_id,
                    'start_date' => $start_date,
                    'end_date' => $end_date, 
                )
            ); 
                      
                // LOG ACTIVITY
                        
                $user =  $user_id;
                $activity =  ' tried to create a project PROJ-0000' . $project_id . ' | Details: ' . $message;
                $status = 'success';
                log_activity($conn, $user, $activity, $status, getSecurity());
    
                // END LOG ACTIVITY
                
    
             exit($message);
    
            //  echo get_all_approval_users($conn);
    
            //  if (get_all_approval_users($conn)) {
            //    exit($message);
            // }
            
    
        } else {
    
                       
                // LOG ACTIVITY
                        
                $user =  $user_id;
                $activity =  ' tried to create a project PROJ-0000 ' . $project_id;
                $status = 'failed';
                log_activity($conn, $user, $activity, $status, getSecurity());
    
                // END LOG ACTIVITY
                
            $message = json_encode(
                array(
                    'message' => 'Failed to create project',
                    'status' => 'failed'
                )
            );
            exit($message);
    
        }
    }


   

}else{

    $message = json_encode(
        array(
            'message' => 'Invalid Request',
            'status' => 'failed'
        )
    );
    exit($message);

}

//method to check the number of project with the same start dates as the one entered.
function checkDatesWithSameStartDatesAsNewProjectStartDate($conn,$user_id, $project_start_date){
    // Example array of project start dates (replace this with your data)
    // $projectStartDates = [
    //     '2023-12-10',
    //     '2023-12-12',
    //     '2023-12-18',
    //     '2023-12-19',
    //     '2023-12-20',
    //     // Add more dates as needed
    // ];


    $projectStartDates = getAllProjectsForUser($conn, $user_id);
    // print_r($projectStartDates);
    // die();

    $projectLimit = getProjectLimit($conn);



    $todayDate = new DateTime($project_start_date); // Get project's date
    $numberOfProjects = 0;

    foreach ($projectStartDates as $startDate) {
        $projectStartDate = new DateTime($startDate);

        // Check if the project start date is in the same week as project's date
        if ($projectStartDate->format('oW') === $todayDate->format('oW')) {
            $numberOfProjects+=1;
            // echo($numberOfProjects);
        }
    }
    // echo $projectLimit;
    // echo $numberOfProjects; die();

    if ($numberOfProjects === $projectLimit || $numberOfProjects > $projectLimit){
        return 1;
    }
    else{
        return 0;
    }
    

}

//method to get project limit 
function getProjectLimit($conn){
    $sql = "SELECT number FROM limits where id = 1";
    $number = 0;
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()){
        $number = $row['number'];
    }
    return $number;
}

function getAllProjectsForUser($conn, $user_id){
    $sql = "SELECT start_date FROM projects WHERE owner = '$user_id'";

$result = $conn->query($sql);
$projectStartDates = array();

    if ($result && $result->num_rows > 0) {
        // Fetching start dates and storing them in an array
        while ($row = $result->fetch_assoc()) {
            $projectStartDates[] = $row['start_date'];
        }
    } else {
        echo "No projects found for this user.";
    }

    return $projectStartDates;
}