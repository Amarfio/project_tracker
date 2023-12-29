<?php  
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//folder to send emails
use PHPMailer\PHPMailer\PHPMailer;

require_once 'connect.php';
require_once 'functions/get_IP_Location.php';
require_once 'functions/activity_logs.php';
require_once 'functions/response.php';
require_once 'functions/isDescriptionAvailable.php';
require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";
// require_once "objects/DateCheck.php";




function getUserEmailById($user_id)
{
    global $conn;
    $queri = "SELECT email FROM users WHERE id= '$user_id' LIMIT 1";
    $result = mysqli_query($conn, $queri);
    // return the username
    $row = mysqli_fetch_assoc($result);
    $developer_email = $row['email'] ;
    // echo json_encode($developer_email); die();
    return $developer_email;



}


//checks for creating a task
//check if task start date is earlier than project start date done
//check if task end date is later than project end date done
//check if task end date is earlier than task start date
//check if task start date is later than task end date

function isTaskBeforeProjectStartDate($taskStartDate, $projectStartDate) {
    // Assuming $taskStartDate and $projectStartDate are in 'Y-m-d' format
    $taskDateTime = strtotime($taskStartDate);
    $projectDateTime = strtotime($projectStartDate);

    return $taskDateTime < $projectDateTime;
}

//code to check for earlier date
function checkForEarlierDate($newStartDate, $oldStartDate){
    $newDateTime = strtotime($newStartDate);
    $oldDateTime = strtotime($oldStartDate);

    return $newDateTime < $oldDateTime;
}

//code to check for later date
function checkForLaterDate($newDate, $oldDate){
    $newDateTime = strtotime($newDate);
    $oldDateTime = strtotime($oldDate);
    
    return $newDateTime > $oldDateTime;
}







$data = json_decode(file_get_contents("php://input")); 
if(
    isset($data) && isset($data->user_id) && isset($data->project_id) && isset($data->task_name) && isset($data->assigned_by) &&
    isset($data->assigned_to) && isset($data->t_start_date) && isset($data->t_end_date) && isset($data->p_start_date) && isset($data->p_end_date) &&  isset($data->priority)  
){
    // echo json_encode($data);

    $user_id = mysqli_real_escape_string($conn, $data->user_id);
    $project_id = mysqli_real_escape_string($conn, $data->project_id);
    $task_name = mysqli_real_escape_string($conn, $data->task_name);
    $assigned_by = mysqli_real_escape_string($conn, $data->assigned_by);
    $assigned_to = mysqli_real_escape_string($conn, $data->assigned_to);
    $client_id = mysqli_real_escape_string($conn, $data->client_id);
    $t_start_date = mysqli_real_escape_string($conn, $data->t_start_date);
    $t_end_date = mysqli_real_escape_string($conn, $data->t_end_date);
    $p_start_date = mysqli_real_escape_string($conn, $data->p_start_date);
    $p_end_date = mysqli_real_escape_string($conn, $data->p_end_date);
    $priority = mysqli_real_escape_string($conn, $data->priority);

    $start_date = date("Y-m-d", strtotime($t_start_date));
    $end_date = date("Y-m-d", strtotime($t_end_date));

    $p_start_date = date("Y-m-d", strtotime($p_start_date));
    $p_end_date = date("Y-m-d", strtotime($p_end_date));

    //create an instance of the date check class
    // $dateCheckTasks = new DateCheck($t_start_date, $p_start_date);
    

    function sendTaskEmail($email, $project_id, $task_name, $task_id, $t_start_date, $t_end_date){
        $txt = "A task has been assigned to you.<br/> 
                Project Details and its task details includes the following: 
                <br/><br/>
                Project ID: PRO-0000$project_id <br/>
                Task Description:  $task_name <br/>
                Task Id: REF0000$task_id <br/>
                Task Start Date: $t_start_date <br/>
                Task End Date: $t_end_date <br/> <br/>
                Kindly <a href='http://localhost:8081/project_tracker/task_detail/$task_id'>click here</a> to see the task <br/>
                <img  src='http://issues.unionsg.com/images/logo.png' class='img-circle'/>
                ";

                //mail setup
                $mail = new PHPMailer();


                // STMP Settings
                $mail->isSMTP();
                $mail->Host = "server.unionsg.com";
                $mail->SMTPAuth=true;
                $mail->Username="hr@unionsg.com";
                $mail->Password="(qLwOdQ3F3cm";
                $mail->Port = 587;
                $mail->SMTPSecure = "tls";

                // echo json_encode($txt); die();
           
                // Email Settings
                $mail->isHTML(true);
                $mail->setFrom("hr@unionsg.com", "no-reply");
                $mail->addAddress($email);
                $mail->Subject="Task Assignment";
                $mail->Body = $txt;
                $done = $mail->send();
                return $done;
    }
    //email content and configuration

    //code to check if task date overlaps another with the same project id
    function checkIfDateOverlaps($conn, $taskStartDate, $taskEndDate, $project_id){
        //get all start dates from the db with the project id
        $sql = "select start_date, end_date from tasks where project_id = '$project_id' AND status = 58 || status = 59";
        //check if each start date is equal new input from user

        $result = mysqli_query($conn, $sql);
        // echo "new date ".$taskStartDate;

        while($row = mysqli_fetch_assoc($result)){
            // echo "old date " .$row['start_date'];

            if ($row['start_date'] == $taskStartDate){
                return true;
            }
        }
        //if it's equal stop return true
        //if not return false


    }

    $overlapResult = checkIfDateOverlaps($conn, $start_date, $end_date, $project_id);

    function checkIfTaskStartDateIsOneWeek($startDate){


        $todayDate = date("Y-m-d");
        // echo "Today's date is: " . $todayDate;


        //get the dates to be checked
        // echo($startDate);
        $dateToCheck = $startDate; // Replace this with the date you want to check
        // echo($todayDate); die();
        $referenceDate = $todayDate; // Replace this with your reference date

        // Create DateTime objects
        $dateTimeToCheck = new DateTime($dateToCheck);
        $dateTimeReference = new DateTime($referenceDate);
        // echo($dateTimeToCheck);
        // echo($dateTimeReference); die();

        // Add one week to the reference date
        $dateTimeToCheck->modify('+1 week');

        // Check if the dates are equal
        if ($dateTimeToCheck == $dateTimeReference) {
            echo "The date is exactly one week from the reference date.";
        } else {
            echo "The date is not one week from the reference date.";
        }
        // die();

    }


    //check if task start date is earlier than project start date
    if(checkForEarlierDate($t_start_date, $p_start_date)){
        $message = response('Task start date cannot be earlier than project start date', 'failed', null);
    }
    //check if task end date is later than project end date
    else if(checkForLaterDate($t_end_date, $p_end_date)){
        $message = response('Task end date cannot be later than project end date', 'failed', null);
    }
    //check if task end date is earlier than task start date
    // else if(checkForEarlierDate($t_end_date, $t_start_date)){
    else if(checkForEarlierDate($t_end_date, $t_start_date)){
        $message = response('Task end date cannot be earlier than task start date', 'failed', null);
    }
    //check if task start date is later than task end date
    else if(checkForLaterDate($t_start_date, $t_end_date)){
        $message = response('Task start date cannot be later than task end date', 'failed', null);
    }
    //check if task desription or name already exists....
    else if(isDescriptionAvailable($conn, "tasks", $task_name) === false){

            $ip_address = 'DF45-123E-34E-24';
            $location = 'Accra Ghana'; 
        

            // $query = "INSERT INTO `projects` (`id`, `version_no`, `name`, `dept_id`, `posted_by`, `ip_address`, `location`, `start_date`, `end_date`) VALUES (NULL, '$version_no', '$name', '$dept_id', '$user_id', '$ip_address', '$location', '$start_date', '$end_date')";
            $query = "INSERT INTO `tasks` (`task_id`, `description`, `start_date`, `end_date`, `client_id`, `assigned_by`, `assigned_to`, `priority`, `project_id`, `ip_address`, `location`, `created_at`) VALUES (NULL, '$task_name', '$start_date', '$end_date', '$client_id', '$assigned_by', '$assigned_to', '$priority', '$project_id', '$ip_address', '$location', NOW())";
            

            $result = mysqli_query($conn, $query);

            $task_id = mysqli_insert_id($conn); 

            if ($result == 1) {

                //get the email of the one it has been assigned to
                $email = getUserEmailById($assigned_to);

                // $queryLastId= "select last_inset_id() ";

                
                
                
                //send the email to 
                $sent = sendTaskEmail($email, $project_id, $task_name, $task_id, $t_start_date, $t_end_date);
                // echo $sent; die();
        
                if($sent){

                    $message = response(
                            'task created successfully',
                            'success',
                            array( [
                                'task_name' => $task_name,
                                'start_date' => $start_date,
                                'end_date' => $end_date,
                                'assigned_by' => $assigned_by,
                                'assigned_to' => $assigned_to,
                                'priority' => $priority,
                                'percentage_completion' => '0%',
                                // 'documentation' => $doc,
                                // 'documentation_file' => $doc_url,
                                'implementation' => 'NO',
                                'is_approved' => 'NO'
                            ],
                            'task_id' => $task_id
                        )
                    );
                        // LOG ACTIVITY
                                
                        $user =  $user_id;
                        $activity =  ' tried to create a task REF-0000' . $task_id.  ' | Details: ' . $message;
                        $status = 'success';
                        log_activity($conn, $user, $activity, $status, getSecurity());
            
                        // END LOG ACTIVITY
                    
                    
            
                }
                        $message=json_encode(
                            array(
                                'message'=>'Task created successfully',
                                'status'=>'success',
                                'data'=>$data
                            )
                            );
                }

                else{
                        $user =  $user_id;
                        $activity =  ' tried to create a task REF-0000' . $task_id;
                        $status = 'failed';
                        log_activity($conn, $user, $activity, $status, getSecurity());

                        $message = json_encode(
                            array(
                                'message' => 'Failed to create task',
                                'status' => 'failed'
                            )
                        );
                }
    }
    exit($message);

    // checkIfTaskStartDateIsOneWeek($start_date);
    // if($overlapResult){
    //    $message=json_encode(
    //                             array(
    //                                 'message' => 'A task has already been taken and not completed',
    //                                 'status' => 'failed',
    //                                 'data'=> null
    //                                 )
    //                         );
    //     exit($message);
    // }
    // else if( ){

    // }
    // else{
    //         $ip_address = 'DF45-123E-34E-24';
    //         $location = 'Accra Ghana'; 
        

    //         // $query = "INSERT INTO `projects` (`id`, `version_no`, `name`, `dept_id`, `posted_by`, `ip_address`, `location`, `start_date`, `end_date`) VALUES (NULL, '$version_no', '$name', '$dept_id', '$user_id', '$ip_address', '$location', '$start_date', '$end_date')";
    //         $query = "INSERT INTO `tasks` (`task_id`, `description`, `start_date`, `end_date`, `client_id`, `assigned_by`, `assigned_to`, `priority`, `project_id`, `ip_address`, `location`, `created_at`) VALUES (NULL, '$task_name', '$start_date', '$end_date', '$client_id', '$assigned_by', '$assigned_to', '$priority', '$project_id', '$ip_address', '$location', NOW())";
            

    //         $result = mysqli_query($conn, $query);

    //         $task_id = mysqli_insert_id($conn); 

    //         if ($result == 1) {

    //             //get the email of the one it has been assigned to
    //             $email = getUserEmailById($assigned_to);

    //             // $queryLastId= "select last_inset_id() ";

                
                
                
    //             //send the email to 
    //             $sent = sendTaskEmail($email, $project_id, $task_name, $task_id, $t_start_date, $t_end_date);
    //             // echo $sent; die();
        
    //             if($sent){

    //                 $message = json_encode(
    //                     array(
    //                         'message' => 'task created successfully',
    //                         'status' => 'success',
    //                         'data' => [
    //                             'task_name' => $task_name,
    //                             'start_date' => $start_date,
    //                             'end_date' => $end_date,
    //                             'assigned_by' => $assigned_by,
    //                             'assigned_to' => $assigned_to,
    //                             'priority' => $priority,
    //                             'percentage_completion' => '0%',
    //                             // 'documentation' => $doc,
    //                             // 'documentation_file' => $doc_url,
    //                             'implementation' => 'NO',
    //                             'is_approved' => 'NO'
    //                         ],
    //                         'task_id' => $task_id
    //                     )
    //                 );
    //                     // LOG ACTIVITY
                                
    //                     $user =  $user_id;
    //                     $activity =  ' tried to create a task REF-0000' . $task_id.  ' | Details: ' . $message;
    //                     $status = 'success';
    //                     log_activity($conn, $user, $activity, $status, getSecurity());
            
    //                     // END LOG ACTIVITY
                    
                    
    //                 exit($message);
            
    //             }
                
            
    //         } else {
    //                 $user =  $user_id;
    //                 $activity =  ' tried to create a task REF-0000' . $task_id;
    //                 $status = 'failed';
    //                 log_activity($conn, $user, $activity, $status, getSecurity());

    //             $message = json_encode(
    //                 array(
    //                     'message' => 'Failed to create task',
    //                     'status' => 'failed'
    //                 )
    //             );
    //             exit($message);
                
    //         }
    // }

    //code to check if task date is 1week
    

    // if ($p_start_date > $t_start_date) {
    //       $message = json_encode(
    //             array(
    //                 'message' => 'Task start date can not be less than project start date',
    //                 'status' => 'failed'
    //             )
    //         );
    //         exit($message);
    
    // }
    // if ($t_end_date > $p_end_date) {
    //       $message = json_encode(
    //             array(
    //                 'message' => 'Task end date can not be greater than project end date',
    //                 'status' => 'failed'
    //             )
    //         );
    //         exit($message);
    
    // }

    // $status = mysqli_real_escape_string($conn, $data->status);
    // $doc = mysqli_real_escape_string($conn, $data->doc);
    // $doc_url = mysqli_real_escape_string($conn, $data->doc_url);
    // $ip_address = mysqli_real_escape_string($conn, $data->ip_address);
    // $location = mysqli_real_escape_string($conn, $data->location);
    



}else{
    $message = json_encode(
        array(
            'message' => 'Invalid Request',
            'status' => 'failed'
        )
    );
    exit($message);
    
}

