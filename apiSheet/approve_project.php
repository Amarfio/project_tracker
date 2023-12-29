<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
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

if (isset($_GET['project_id']) && isset($_GET['approvedBy']) && isset($_GET['comment']) ) {

        $comment = mysqli_escape_string($conn, $_GET['comment']);
        $project_id = mysqli_escape_string($conn, $_GET['project_id']);
        $approvedBy = mysqli_escape_string($conn, $_GET['approvedBy']);
        $department_id = mysqli_escape_string($conn, $_GET['department_id']);

        approve_project($conn, $project_id, $approvedBy, $comment );

        

        

//     $get_users_approvers = "SELECT email FROM users WHERE dept = '$department_id'";
//     $result_get_users_approvers = mysqli_query($conn, $get_users_approvers);
//     $count_get_users_approvers = mysqli_num_rows($result_get_users_approvers);

//     $users_arr = array();

// if ($count_get_users_approvers > 0) {
//     while ($row = mysqli_fetch_assoc($result_get_users_approvers)) {

//          $email = strtolower($row['email']);

//          $users_arr[] = $email;


//     }
//         $email_list = implode(",", $users_arr);
    
//         $to = "ampahkwabena55@gmail.com" ;
//         $subject = "New Project has been assigned to your department";
//         $txt = 'http://192.168.1.195:84/project_tracker/login/';
//         // $headers = "From: project.tracker@unionsg.com" . "\r\n" .
//         $headers = "From: ampahkwabena55@gmail.com" . "\r\n" .
//         "CC: " . $email_list ;

//           if (mail($to,$subject,$txt,$headers)) {

//                 approve_project($conn, $project_id, $approvedBy, $comment );

//           }


// } else {

//         $to = "ampahkwabena55@gmail.com" ;
//          $subject = "New Project has been assigned to your department";
//         $txt = 'http://192.168.1.195:84/project_tracker/login/';
//         // $headers = "From: project.tracker@unionsg.com" . "\r\n" .
//         $headers = "From: ampahkwabena55@gmail.com" . "\r\n" ;

//           if (mail($to,$subject,$txt,$headers)) {

//                 approve_project($conn, $project_id, $approvedBy, $comment );

//           }
// }




}

function send_email($toEmail, $subject, $txt){
    //mail setup
    $mail = new PHPMailer();
    // return "am here in the email method.";


    // STMP Settings
    $mail->isSMTP();
    $mail->Host = "server.unionsg.com";
    $mail->SMTPAuth=true;
    $mail->Username="hr@unionsg.com";
    $mail->Password="(qLwOdQ3F3cm";
    $mail->Port = 587;
    $mail->SMTPSecure = "tls";
    $mailName = "no-reply";

    // //SMTP Settings
    // $mail->isSMTP();
    // $mail->Host = "smtp.gmail.com";
    // $mail->SMTPAuth= true;
    // $mail->Username="joshuaamarfio1@gmail.com";
    // $mail->Password = "atmoqrmb8N";
    // $mail->Port = 587; //587
    // $mail->SMTPSecure ="tls";//tls

    // Email Settings
    $mail->isHTML(true);
    $mail->setFrom($toEmail, $mailName);
    $mail->addAddress($toEmail);
    $mail->Subject=$subject;
    $mail->Body = $txt;
    $done = $mail->send();
    
    return $done;
}

//get the owner's email for the specified project with its id
function get_project_owner_email($project_id, $conn){
    $query = "SELECT u.email email FROM projects p LEFT JOIN users u ON u.id = p.posted_by WHERE project_id = '$project_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    return $row['email'];
}

//get the project's creator too email for the specified project with its id
function get_project_creator($project_id, $conn){
    // echo json_encode("try again"); die();
    $query = "SELECT u.email email FROM projects p LEFT JOIN users u ON u.id = p.owner WHERE project_id = '$project_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    return $row['email'];
}


function approve_project($conn, $project_id, $approvedBy, $comment ){

    //code to get the current date and save in the database
    // $approvedDate = date("F Y h:i:s A");

    
    $query = "UPDATE `projects` SET `status` = 85, `is_approved` = 1, `comment` = '$comment', `comment_by` = '$approvedBy', `approved_by` = '$approvedBy', `approved_date` = NOW() WHERE `projects`.`project_id` = '$project_id' ";
    // echo ($query);
    // die();
    $result = mysqli_query($conn, $query);
    // echo json_encode("update successful"); die();

    $query_project = "SELECT p.*, p.status status_id, co_sta.desc status_name, co.id version_id, co.init version_init, co.init_desc version_code, co.desc version_name, p.comment , p.comment_by comment_by_id, CONCAT(u.f_name, ' ', u.l_name) comment_by_name FROM projects p LEFT JOIN code_desc co ON co.id = p.version_no LEFT JOIN code_desc co_sta ON co_sta.id = p.status LEFT JOIN users u ON u.id = p.comment_by WHERE project_id = '$project_id'";
        
        $result_project = mysqli_query($conn, $query_project);
        
        // $num_project = mysqli_num_rows($result_project); 
        
        $row_project = mysqli_fetch_assoc($result_project);
        // echo json_encode($row_project); die();
        
        $project_id =  $row_project['project_id'];
        $version_no  =  $row_project['version_no'];
        $version_name  =  $row_project['version_name'];
        $name =  $row_project['name'];
        $description =  $row_project['description'];
        $is_approved =  $row_project['is_approved'];
        $project_status_id =  $row_project['status_id'];
        $project_status =  $row_project['status_name'];
        $department_id =  $row_project['dept_id'];
        // $department =  get_department_name($row_project['dept_id'], $conn); 
        $comment =  $row_project['comment'];
        $comment_by_id =  $row_project['comment_by_id']; 
        $comment_by_name =  $row_project['comment_by_name']; 
        $start_date =  $row_project['start_date'];
        $end_date =  $row_project['end_date'];

                // $to = 'ampahkwabena55@gmail.com';
                // $subject = "UNION SYSTEMS GLOBAL";
                $txt = "The Project that was submitted for approval has been duly approved, below includes the details for the project.<br/> 
                Project Details includes the following: 
                <br/><br/>
                Project ID: PRO-0000 $project_id <br/>
                Description:  $description <br/>
                Version: $version_name <br/>
                Start Date: $start_date <br/>
                End Date: $end_date <br/> <br/>
                Kindly <a href='http://192.168.1.195:84/project_tracker/login'>click here</a> to login <br/>
                <img  src='http://issues.unionsg.com/images/logo.png' class='img-circle'/>
                ";

        //another testing code
        // echo json_encode($txt); die();

        //get the details of the send_email method
        $creator_email =  get_project_creator($project_id, $conn);
        // echo json_encode($creator_email); die();
        $subject = "Approval Status";
        
        //send the email to the creator of the project 
        send_email($creator_email, $subject, $txt);
        // echo json_encode($new_result); die();
        
        //send the email to the owner of the project
        $owner_email = get_project_owner_email($project_id, $conn);
        
        $new_result = send_email($owner_email, $subject, $txt);
        // echo json_encode($new_result); die();
    
        if ($new_result == true) {
            $message = json_encode( 
                array(
                    'message' => 'Project has been sucessfully Approved',
                    'status' => 'success',
                    'project_id' => $project_id
                )
            );
            exit($message);
        } else{
            $message = json_encode(
                array(
                    'message' => 'failed approve project',
                    'status' => 'failed',
                )
            );

            exit($message);
        }


}

