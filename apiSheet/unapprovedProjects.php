<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once('connect.php');
// require_once('../includes/usedfunctions.php');
require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";



function sendUnapprovedEmail($conn, $deptId){

    // echo(json_encode($deptId)); die();
$unapprovedQuery= "SELECT * FROM `projects` WHERE is_approved != 1 and dept_id = '$deptId'";

// echo($unapprovedQuery); die();
$result_project = mysqli_query($conn, $unapprovedQuery);
        
$num_project = mysqli_num_rows($result_project); 

$row_project = mysqli_fetch_assoc($result_project);
// echo($row_project); die();
print_r($row_project); die();
  
  $to = "amarfio.joshua@unionsg.com";
  $subject = "Dreams pakainfo Notification";
  
  $message ="Dear Relation managers,";
  $message .= "
  <html>
  <head>
  <meta charset='UTF-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Issues unassigned</title>
  <style>
          body{
            margin: 0;
            padding: 20px;
            font-family: sans-serif;
        }

        *{
            box-sizing: border-box;
        }

        .table{
            width: 100%;
            border-collapse: collapse;
        }

        .table td, .table th{
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 16px;
        }

        .table th{
            background-color: darkblue;
            color: #ffffff;
        }

        .table tbody tr:nth-child(even){
            background-color: #f5f5f5;
        }

        /*responsive*/

        @media(max-width: 500px){
            .table thead{
                display: none;
            }
            .table, .table tbody, .table tr, .table td{
                display: block;
                width: 100%;
            }
            .table tr{
                margin-bottom: 15px;
            }
            .table td{
                text-align: right;
                padding-left: 50%;
                text-align: right;
                position: relative;
            }
            .table td::before{
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 50%;
                padding-left: 15px;
                font-size: 15px;
                font-weight: bold;
                text-align: left;
                
            }
        }
  </style>
  </head>
  <body>
  <h4>The table below contains details of issues that are yet to be assigned to departments.</h4>
  <table border='1' class='table'>
  <tr>
    <th>Project ID</th>
    <th>Project Name</th>
    <th>Start Date</th>
    <th>End Date</th>
    <th>Date logged</th>
  </tr>";
  
  if($num_project >0){
    $results = array();
    $i = 1;
    // echo($num_project); die();
    for ($x = 0; $x < $num_project; $x++ ) 
        {
        
            
            $results[] = $row_project;
            $message .="<tr>";
            $message .="<td data-label='Project ID'>".$row_project[$x]['project_id']."</td>";
            $message .="<td data-label='Project Name'>".$row_project[$x]['name']."</td>";
            $message .="<td data-label='Start Date'>".$row_project[$x]["start_date"]."</td>";
            $message .="<td data-label='End Date'>".$row_project[$x]["end_date"]."</td>";
            $message .="<td data-label='Date Logged'>".$row_project[$x]["created_at"]."</td>";
            $message .="</tr>";
            $i++;
        
        }
  }
  
  $message .="</table>
  <br/><br/>
    kind Regards<br/>
    Support 24x7,<br/>
    From the Office of USG support.<br/>
  </body>
  </html>
  ";


//send the email with this code
$mailName = "Ticket Details";
// $to ="joshuaamarfio1@gmail.com";
// $newEmail = "amarfio.joshua@unionsg.com";

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
$mail->setFrom($to, $mailName);
// $mail->addAddress($to);
// $recipients = $_SESSION['Client_Emails'];
// $recipients = $_SESSION['C_RECEIVERS'];

// $sql= "SELECT Email from users where R_M = 1";
// $stmt= $conn->prepare($sql);
// $stmt->execute();
// $row= $stmt->fetchAll(PDO::FETCH_ASSOC);

// for ($i = 0; $i < count($row); $i++){

//   $mail->addAddress($row[$i]['Email']);

// }

$email = get_department_head_email($deptId, $conn);
echo($message); die();
// echo($email); die();
$mail->addAddress($email);
// foreach($recipients as $email){
//   $mail->addCC($email);	
// }
$mail->Subject="Ticket Details";
$mail->Body = $message;
$done = $mail->send();

echo ("mail sent");

}

function getAllDepartmentIds($conn){
    $query= "SELECT cod.id as department_id from code_desc cod where init = 'dpt' AND is_active = 1";
    $result = mysqli_query($conn, $query);
    $num = mysqli_num_rows($result);

    $departments = array();

    if($num > 0){
        while ($row = mysqli_fetch_assoc($result)){
            $departments[] = $row['department_id'] ;
        }
    }
    // $row = mysqli_fetch_array($result);
    
    // return $departments;

    for($i = 0; $i <count($departments); $i++){
        sendUnapprovedEmail($conn, $departments[$i]);
    }
}

 getAllDepartmentIds($conn);
// echo(json_encode($result));

function get_department_head_email($department_id, $conn){
    $query = "SELECT email FROM users WHERE (is_dpt_head = 1 AND can_approve = 1) AND (dept = '$department_id' AND is_active = 1) ";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);
    return $row['email'];
}

?>
