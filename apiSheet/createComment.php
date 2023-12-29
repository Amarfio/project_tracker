<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//folder to send emails
use PHPMailer\PHPMailer\PHPMailer;
// echo json_encode("here");

// echo "Am here";die();
//sending the email to the user
require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";

require_once 'connect.php';
require_once 'functions/get_IP_Location.php';
require_once 'functions/activity_logs.php';


$data = json_decode(file_get_contents("php://input"));

//get department head's email
function get_department_head_email($department_id, $conn){
    $query = "SELECT u.email as email, code_desc.init_desc as receive_emails FROM users u LEFT JOIN code_desc ON u.receive_emails = code_desc.id WHERE (u.is_dpt_head = 1 AND u.can_approve = 1) AND (u.dept = '$department_id' AND u.is_active = 1) ";
        // echo json_encode($query); die();
    $result = mysqli_query($conn, $query);
    // echo json_encode($result); die();
    $row = mysqli_fetch_array($result);
    // echo json_encode($row); die();
    // echo json_encode($row); die();
    return $row;
}

//get task owner or assigned_to email
function get_assigned_to_email($user_id, $conn){
    $query = "SELECT email FROM users WHERE (id='$user_id' AND is_active = 1) ";
    // json_encode($query); die();
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    // echo json_encode($row['email']); die();
    return $row['email'];
}

function send_email_to_taskOwner ($user_id, $comment, $task_id, $conn){
    $txt = "A comment has been given on the task with reference REF000$task_id, with comment as .<b>$comment</b>.
            <br/> 
            <br/>
            Kindly <a href='http://192.168.1.195:84/project_tracker/task_detail/$task_id'>click here</a> to see the task and the comment given.
            <br/>
            <br/>
            <img src='http://issues.unionsg.com/images/logo.png' class='img-circle'/>";
        
    // echo json_encode($department_id); die();
    // $department_id=106;

    //get the department head's email
    $ownerEmail = get_assigned_to_email($user_id, $conn);
    // $deptHeadEmail = "amarfio.joshua@unionsg.com";
    $mailName = "Task Comment Update";

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

    // Email Settings
    $mail->isHTML(true);
    $mail->setFrom($ownerEmail, $mailName);
    $mail->addAddress($ownerEmail);
    $mail->Subject="Task Update Given";
    $mail->Body = $txt;
    $done = $mail->send();
    
    // if($done){
    //     echo json_encode("email sent");
    // }
    // else{
    //     echo json_encode("email sent");
    // }

}

//function to send email
function send_email_to_dept_head($department_id, $comment, $task_id, $conn){
    $txt = "A comment has been given on the task with reference REF000.$task_id, with comment as <b>$comment</b>.
            <br/> 
            <br/>
            Kindly <a href='http://192.168.1.195:84/project_tracker/task_detail/$task_id'>click here</a> to see the task.
            <br/>
            <br/>
            <img src='http://issues.unionsg.com/images/logo.png' class='img-circle'/>";
        
    // echo json_encode($department_id); die();
    // $department_id=106;

    //get the department head's email
    $deptHeadEmailDetails = get_department_head_email($department_id, $conn);


    // echo json_encode($deptHeadEmailDetails); die();
    $deptHeadEmail = $deptHeadEmailDetails['email'];
    // echo json_encode($deptHeadEmail); die();
    $receiveValue = $deptHeadEmailDetails['receive_emails'];
    $mailName = "Task Comment Update";

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

    // Email Settings
    $mail->isHTML(true);
    $mail->setFrom($deptHeadEmail, $mailName);
    $mail->addAddress($deptHeadEmail);
    $mail->Subject="Task Update Given";
    $mail->Body = $txt;

    if($receiveValue =="Y"){
         $mail->send();
    }
    
    
    // if($done){
    //     echo json_encode("email sent");
    // }
    // else{
    //     echo json_encode("email sent");
    // }

}

function getOwnerOfTask($task_id, $conn){
    $query = "SELECT assigned_to FROM tasks WHERE (task_id='$task_id') ";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    // echo json_encode($row['email']); die();
    return $row['assigned_to'];
}

if(isset($data) && isset($data->comment) && isset($data->task_id)  && isset($data->posted_by) ){
    // echo json_encode($data);

    $comment = mysqli_real_escape_string($conn, $data->comment);
    $attach = mysqli_real_escape_string($conn, $data->attach);
    $task_id = mysqli_real_escape_string($conn, $data->task_id);
    $posted_by = mysqli_real_escape_string($conn, $data->posted_by);
    $department_id = mysqli_real_escape_string($conn, $data->department_id);
    $is_dept_head = mysqli_real_escape_string($conn, $data->is_dept_head);
    $taskOwnerId = mysqli_real_escape_string($conn, $data->taskOwnerId);
    // json_encode($taskOwnerId); die();
    // json_encode($department_id); die();

    if($is_dept_head == 0) {
        send_email_to_dept_head($department_id, $comment, $task_id, $conn);
    }

    //send comment to email comment passed is not from task owner
    if($posted_by != $taskOwnerId){
        send_email_to_taskOwner($taskOwnerId, $comment, $task_id, $conn);
    }
    
    // $query = "INSERT INTO `clients` (`client_id`, `name`) VALUES (NULL, '$client')";
    $query = "INSERT INTO `comments` (`comment_id`, `comment`, `attach`, `task_id`, `posted_by`) VALUES (NULL, '$comment', '$attach', '$task_id', '$posted_by')";
    $result = mysqli_query($conn, $query);
    // echo $result; die();

    //get the department head's email using the department id 
    // get_department_head_email($department_id, $conn);

    if ($result == 1) {
        $message = json_encode(
            array(
                'message' => 'Comment created successfully',
                'status' => 'success',
                'data' => [
                    'comment' => $comment,
                    'attach' => $attach,
                    'task_id' => $task_id, 
                    'posted_by' => $posted_by,
                ]
            )
        );
                    // LOG ACTIVITY
                    
            $user =  $posted_by;
            $activity =  ' tried to comment on task REF-0000' . $task_id. ':  ' . $message;
            $status = 'success';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY

        exit($message);

    } else {

            
        $message = json_encode(
            array(
                'message' => 'Failed create Comment',
                'status' => 'failed'
            )
        );
                
             // LOG ACTIVITY
                    
            $user =  $user_id;
            $activity =  ' tried to Comment ';
            $status = 'failed';
            log_activity($conn, $user, $activity, $status, getSecurity());

            // END LOG ACTIVITY
        exit($message);
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

