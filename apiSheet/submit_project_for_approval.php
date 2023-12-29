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
// require_once 'mailer.php';

//mail
$mailName = "no-reply";



function get_department_name($department_id, $conn){

        $query = "SELECT co.desc department_name FROM code_desc co WHERE co.id = '$department_id'";
        $result = mysqli_query($conn, $query);
       $row = mysqli_fetch_array($result);
       return $row['department_name'];
    
  
    }

function get_department_head_email($department_id, $conn){
    $query = "SELECT email FROM users WHERE (is_dpt_head = 1 AND can_approve = 1) AND (dept = '$department_id' AND is_active = 1) ";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    return $row['email'];
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

            $query = "SELECT email FROM `users` WHERE can_approve = 1 AND is_active = 1";
            $result = mysqli_query($conn, $query);

            $num = mysqli_num_rows($result);
            $email_arr = array();
            if ($num > 0) {
                // echo json_encode('am here'); die();
                while ($row = mysqli_fetch_assoc($result)) {

                    $email_arr[] = $row['email'];
                    // echo json_encode($email_arr);
                } 

                $to = 'ampahkwabena55@gmail.com';
                // $subject = "UNION SYSTEMS GLOBAL";
                $txt = "A new Project has been created and is pending approval.<br/> 
                Project Details includes the following: 
                <br/><br/>
                Project ID: PRO-0000 $project_id <br/>
                Project Name:  $name <br/>
                Version: $version_name <br/>
                Department: $department <br/>
                Start Date: $start_date <br/>
                End Date: $end_date <br/> <br/>
                Kindly <a href='http://192.168.1.195:84/project_tracker/login'>click here</a> to login <br/>
                <img  src='http://issues.unionsg.com/images/logo.png' class='img-circle'/>
                ";

                // $txt = "New Project has been created and is pending approval: ".  "http://192.168.1.195:84/project_tracker/login". "\r\n" ;
                // $txt = $txt . 'Project ID: PRO-0000' . $project_id . "\r\n" ;
                // $txt = $txt . 'Description: ' . $description . "\r\n" ;
                // $txt = $txt . 'Version : ' . $version_name . "\r\n" ;
                // $txt = $txt . 'Department: ' . $department . "\r\n" ;
                // $txt = $txt . 'Start Date: ' . $start_date . "\r\n" ;
                // $txt = $txt . 'End Date: ' . $end_date . "\r\n" ;
                
                $headers = "From: USG" . "\r\n" . "CC: " .  implode (", ", $email_arr);
                $mailName= "no-reply";

                $deptHeadEmail = get_department_head_email($department_id, $conn);
                // return $deptHeadEmail; die();

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
                $mail->setFrom($deptHeadEmail, $mailName);
                $mail->addAddress($deptHeadEmail);
                $mail->Subject="Approval Request";
                $mail->Body = $txt;
                $done = $mail->send();

            //revisit and send emails to department heads for approval alert
                // return true;

            // mail($to,$subject,$txt,$headers
           if( $done){
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

            }
            
                else {
                    
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
                    'message' => 'Project has been submitted for Approval',
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