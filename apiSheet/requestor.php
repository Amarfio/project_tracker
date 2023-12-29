<?php
// include("dbconn.php");
// include("dbconn_.php");


error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once('../Connections/ticket.php');
require_once('../includes/usedfunctions.php');
require_once('../js/PHPMailer/PHPMailer.php');
require_once('../js/PHPMailer/SMTP.php');
require_once('../js/PHPMailer/Exception.php');
use PHPMailer\PHPMailer\PHPMailer;

// require_once('../Connections/ticket');

error_reporting(E_ALL);
ini_set('display_errors', 'On');

@session_start();

$get_submitter_name="";
 $actual_time= date("F j, Y,");
if(isset($_SESSION['SUBMITTER'])){
  $get_submitter_name=$_SESSION['SUBMITTER'];
}

$last4chars = substr(time(), -4);
$SEQUENCE="CH".date('Y')."".$last4chars;

$message="";
if(isset($_POST['submit'])){

	
	// $targetDir = "../uploads/";
	// // $fileName = basename($_FILES["file"]["name"]);
	// $fileName = basename($_FILES["fileUpload"]["name"]);
	// // echo $fileName; die();
	// $targetFilePath = $targetDir . $fileName;
	// $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);

	//intiating post variables

    //echo 1;die;
    $customerName = $_POST["customerName"];
    $requestNumber = $_POST["requestNumber"];
    $requestBy = $_POST["requestBy"];

    $descriptionOfChange = $_POST["descriptionOfChange"];
	$changeImpactAssessment =$_POST["changeImpactAssessment"];
    $reasonForChange = $_POST["reasonForChange"];
	$TICKET_ID = $_POST["ticket_id_no"];
	$scopeOfChange = $_POST["scopeOfChange"];
	$scheduleOfChange = $_POST["scheduleOfChange"];
	$budgetOfChange = $_POST["budgetOfChange"];
	$riskPlan = $_POST["riskPlan"];
	$otherImpacts = $_POST["otherImpacts"];
	$resourcesRequired = $_POST["resourcesRequired"];
    $otherComments = $_POST["otherComments"];
    $dateRequired = $_POST["dateRequired"];
	$loggedBy = $_SESSION['FULL_NAME'];
	$time = date("h:i:sa");
	$dateIssued = date("Y/m/d");
	$submitterEmail = $_SESSION['EMAIL'];
	$raisedBy = $_POST['raisedBy'];
	$changeType = $_POST['changeType'];


	//    $req_email=$dev_name;//$_SESSION['EMAIL'];
	$insert_query = "insert into tb_change (requestor_id, ticket_Id, customer_name, description_of_change, request_by, scope_of_change, schedule_of_change, budget_of_change, risk_plan,
	other_impacts, resources_required, date_required, reason_for_change, submitter_email, other_comments, raised_by, change_type) values
	('$SEQUENCE', '$TICKET_ID', '$customerName', '$descriptionOfChange', '$requestBy', '$scopeOfChange', '$scheduleOfChange', '$budgetOfChange','$riskPlan','$otherImpacts','$resourcesRequired','$dateRequired', '$reasonForChange', '$submitterEmail', '$otherComments', '$raisedBy', '$changeType')";

	// echo($insert_query);
	// echo("here we dey"); die();
	// echo($conn->query($insert_query)); die();
	// echo("here we dey"); die();
	// echo()
	$stmt = $conn->prepare($insert_query);
	$result = $stmt->execute();
	if($result){
		//echo "<script>alert('Send Successfuly')</script>";
		$subject="CHANGE MANAGEMENT ONLINE REQUEST";
		$message=
		"
		<!DOCTYPE html>
<html lang='en' xmlns='http://www.w3.org/1999/xhtml' xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office'>
<head>
    <meta charset='utf-8'> <!-- utf-8 works for most cases -->
    <meta name='viewport' content='width=device-width'> <!-- Forcing initial-scale shouldn't be necessary -->
    <meta http-equiv='X-UA-Compatible' content='IE=edge'> <!-- Use the latest (edge) version of IE rendering engine -->
    <meta name='x-apple-disable-message-reformatting'>  <!-- Disable auto-scale in iOS 10 Mail entirely -->
    <title></title> <!-- The title tag shows in email notifications, like Android 4.4. -->

    <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700' rel='stylesheet'>

    <!-- CSS Reset : BEGIN -->
    <style>

        /* What it does: Remove spaces around the email design added by some email clients. */
        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
        html,
body {
    margin: 0 auto !important;
    padding: 0 !important;
    height: 100% !important;
    width: 100% !important;
    background: #f1f1f1 !important;
}

/* What it does: Stops email clients resizing small text. */
* {
    -ms-text-size-adjust: 100% !important;
    -webkit-text-size-adjust: 100% !important;
}

/* What it does: Centers email on Android 4.4 */
div[style*='margin: 16px 0'] {
    margin: 0 !important;
}

/* What it does: Stops Outlook from adding extra spacing to tables. */
table,
td {
    mso-table-lspace: 0pt !important;
    mso-table-rspace: 0pt !important;
}

/* What it does: Fixes webkit padding issue. */
table {
    border-spacing: 0 !important;
    border-collapse: collapse !important;
    table-layout: fixed !important;
    margin: 0 auto !important;
}

/* What it does: Uses a better rendering method when resizing images in IE. */
img {
    -ms-interpolation-mode:bicubic !important;
}

/* What it does: Prevents Windows 10 Mail from underlining links despite inline CSS. Styles for underlined links should be inline. */
a {
    text-decoration: none !important;
}

/* What it does: A work-around for email clients meddling in triggered links. */
*[x-apple-data-detectors],  /* iOS */
.unstyle-auto-detected-links *,
.aBn {
    border-bottom: 0 !important;
    cursor: default !important;
    color: inherit !important;
    text-decoration: none !important;
    font-size: inherit !important;
    font-family: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
}

/* What it does: Prevents Gmail from displaying a download button on large, non-linked images. */
.a6S {
    display: none !important;
    opacity: 0.01 !important;
}

/* What it does: Prevents Gmail from changing the text color in conversation threads. */
.im {
    color: inherit !important;
}

/* If the above doesn't work, add a .g-img class to any image in question. */
img.g-img + div {
    display: none !important;
}

/* What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89  */
/* Create one of these media queries for each additional viewport size you'd like to fix */

/* iPhone 4, 4S, 5, 5S, 5C, and 5SE */
@media only screen and (min-device-width: 320px) and (max-device-width: 374px) {
    u ~ div .email-container {
        min-width: 320px !important;
    }
}
/* iPhone 6, 6S, 7, 8, and X */
@media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
    u ~ div .email-container {
        min-width: 375px !important;
    }
}
/* iPhone 6+, 7+, and 8+ */
@media only screen and (min-device-width: 414px) {
    u ~ div .email-container {
        min-width: 414px !important;
    }
}

    </style>

    <!-- CSS Reset : END -->

    <!-- Progressive Enhancements : BEGIN -->
    <style>

	    .primary{
	background: #30e3ca;
}
.bg_white{
	background: #ffffff !important;
}
.bg_light{
	background: #fafafa !important;
}
.bg_black{
	background: #000000 !important;
}
.bg_dark{
	background: rgba(0,0,0,.8) !important;
}
.email-section{
	padding:2.5em !important;
}

/*BUTTON*/
.btn{
	padding: 10px 15px !important;
	display: inline-block !important;
}
.btn.btn-primary{
	border-radius: 5px !important;
	background: #30e3ca !important;
	color: #ffffff !important;
}
.btn.btn-white{
	border-radius: 5px !important;
	background: #ffffff !important;
	color: #000000 !important;
}
.btn.btn-white-outline{
	border-radius: 5px !important;
	background: transparent !important;
	border: 1px solid #fff !important;
	color: #fff !important;
}
.btn.btn-black-outline{
	border-radius: 0px !important;
	background: transparent !important;
	border: 2px solid #000 !important;
	color: #000 !important;
	font-weight: 700 !important;
}

h1,h2,h3,h4,h5,h6{
	font-family: 'Lato', sans-serif;
	color: #000000 !important;
	margin-top: 0 !important;
	font-weight: 400 !important;
}

body{
	font-family: 'Lato', sans-serif;
	font-weight: 400 !important;
	font-size: 15px !important;
	line-height: 1.8 !important;
	color: rgba(0,0,0,.4) !important;
}

a{
	color: #30e3ca !important;
}

table{
}
/*LOGO*/

.logo h1{
	margin: 0;
}
.logo h1 a{
	color: #30e3ca !important;
	font-size: 24px !important;
	font-weight: 700 !important;
	font-family: 'Lato', sans-serif !important;
}

/*HERO*/
.hero{
	position: relative !important;
	z-index: 0 !important;
}

.hero .text{
	color: rgba(0,0,0,.3) !important;
}
.hero .text h2{
	color: #000 !important;
	font-size: 40px !important;
	margin-bottom: 0 !important;
	font-weight: 400 !important;
	line-height: 1.4 !important;
}
.hero .text h3{
	font-size: 24px !important;
	font-weight: 300 !important;
}
.hero .text h2 span{
	font-weight: 600 !important;
	color: #30e3ca !important;
}


/*HEADING SECTION*/
.heading-section{
}
.heading-section h2{
	color: #000000 !important;
	font-size: 28px !important;
	margin-top: 0 !important;
	line-height: 1.4 !important;
	font-weight: 400 !important;
}
.heading-section .subheading{
	margin-bottom: 20px !important;
	display: inline-block !important;
	font-size: 13px !important;
	text-transform: uppercase !important;
	letter-spacing: 2px !important;
	color: rgba(0,0,0,.4) !important;
	position: relative !important;
}
.heading-section .subheading::after{
	position: absolute !important;
	left: 0 !important;
	right: 0 !important;
	bottom: -10px !important;
	content: '' !important;
	width: 100% !important;
	height: 2px !important;
	background: #30e3ca !important;
	margin: 0 auto !important;
}

.heading-section-white{
	color: rgba(255,255,255,.8) !important;
}
.heading-section-white h2{
	font-family: 
	line-height: 1 !important;
	padding-bottom: 0 !important;
}
.heading-section-white h2{
	color: #ffffff !important;
}
.heading-section-white .subheading{
	margin-bottom: 0;
	display: inline-block !important;
	font-size: 13px !important;
	text-transform: uppercase !important;
	letter-spacing: 2px !important;
	color: rgba(255,255,255,.4) !important;
}


ul.social{
	padding: 0 !important;
}
ul.social li{
	display: inline-block !important;
	margin-right: 10px !important;
}

/*FOOTER*/

.footer{
	border-top: 1px solid rgba(0,0,0,.05) !important;
	color: rgba(0,0,0,.5) !important;
}
.footer .heading{
	color: #000 !important;
	font-size: 20px !important;
}
.footer ul{
	margin: 0 !important;
	padding: 0 !important;
}
.footer ul li{
	list-style: none !important;
	margin-bottom: 10px !important;
}
.footer ul li a{
	color: rgba(0,0,0,1) !important;
}


@media screen and (max-width: 500px) {


}


    </style>


</head>

<body width='100%' style='margin: 0 !important; padding: 0 !important; mso-line-height-rule: exactly !important; background-color: #f1f1f1 !important;'>
	<center style='width: 100% !important; background-color: #f1f1f1 !important;'>
    <div style='display: none !important; font-size: 1px !important;max-height: 0px !important; max-width: 0px !important; opacity: 0 !important; overflow: hidden !important; mso-hide: all !important; font-family: sans-serif !important;'>
      &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>
    <div style='max-width: 600px !important; margin: 0 auto !important; margin-top: -20px !important;' class='email-container'>
    	<table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='margin: auto !important;'>
        <tr>
          <td class='bg_light' style='text-align: center !important;'>
          	<img src='logo.png' width='270' style='max-width: 100% !important; margin-top: -35px !important; margin-bottom: -50px !important;'
            />
          </td>
        </tr>
      </table>
    	<!-- BEGIN BODY -->
      <table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='margin: auto !important;'>
      	<!-- <tr> -->
          <!-- <td valign='top' class='bg_white' style='padding: 1em 2.5em 0 2.5em !important;'> -->
          	<!-- <table role='presentation' border='0' cellpadding='0' cellspacing='0' width='100%'>
          		<tr>
          			<td class='logo' style='text-align: center !important;'>
			            <h1><a href='#'>Issue Assigned</a></h1>
			          </td>
          		</tr>Ω
          	</table> -->
          <!-- </td> -->
	      <!-- </tr>end tr -->
	      <tr>
          <td valign='middle' class='hero bg_white' style='padding: 3em 0 2em 0 !important;'>
          	<div style='margin-top: -24px !important;'>
            <img src='email.png' alt='' style='width: 100px !important; max-width: 600px !important; height: auto !important; margin: auto !important; display: block !important;'>
            </div>
          </td>
	      </tr><!-- end tr -->
		<tr>
          <td valign='middle' class='hero bg_white' style='padding: 2em 0 4em 0 !important;'>
            <table>
            	<tr>
            		<td>
            			<div class='text' style='padding: 0 2.5em !important; text-align: center !important;'>
            				<h2 style='line-height: 2.2 !important; margin-top: -52px !important; font-size: 21px !important;'>New Change Raised</h2>
            				<h3 style='font-size: 16px !important; line-height: 2.2 !important;'>Dear Rapheal Djane Kotei, Incident Number <b style='color: grey !important;'>PJ00006545</b> has been assigned to You. Below is a summary of the details:</h3>
            			</div>
            		</td>
            	</tr>
            	<tr>
            		<div class='text' style='padding: 0 2.5em !important; text-align: center !important;'>
	            		<table class='table table-stripped' style='line-height: 2.8 !important; margin-bottom: -10% !important; margin-top: -1% !important; color: black !important;'>
						<tr>
							<td>Issue Status : &nbsp;&nbsp;&nbsp;</td>
							<td> <b>New</b> </td>
						</tr>
						<tr>
							<td>Issue Number : &nbsp;&nbsp;&nbsp;</td>
							<td><b>PJ00006545</b></td>
						</tr>
						<tr>
							<td>Date Issued : &nbsp;&nbsp;&nbsp;</td>
							<td><b>4th October 2022</b></td>
						</tr>
						<tr>
							<td>Logged By : &nbsp;&nbsp;&nbsp;</td>
							<td><b>Joshua Amarfio</b></td>
						</tr>
						<tr>
							<td>Time : &nbsp;&nbsp;&nbsp;</td>
							<td><b>06:25:01 pm</b></td>
						</tr>
						<tr>
							<td>Issue Summary : &nbsp;&nbsp;&nbsp;</td>
							<td><b style='overflow: wrap;'>This is a newly assigned issue</b></td>
						</tr>
						<tr>
							<td>Department Affected : &nbsp;&nbsp;&nbsp;</td>
							<td><b>Enterprise</b></td>
						</tr>
						<tr>
							<td colspan='2'>
							<p style='margin-bottom: 15px !important; text-transform: capitalize !important;'><a href='#' style='background: #953581 !important; color: white !important; padding: 7px !important;'>Click here to preview the details of the issue</a></p>
							</td>
						</tr>

					</table>
				  </div>
            	</tr>
            </table>
          </td>
	      </tr><!-- end tr -->
      <!-- 1 Column Text + Button : END -->
      </table>


      <table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='margin: auto !important;'>
        <tr>
          <td class='bg_light' style='text-align: center !important;'>
          	<p>&#169 !important; <?php echo date('Y') ?> <a href='#' style='color: rgba(0,0,0,.8) !important;'>Union Systems Global Limited</a>. All Rights Reserved</p>
          </td>
        </tr>
      </table>

    </div>
  </center>
</body>
</html>
		
		
		";
	
		// $linkToApprove=" <b><font color='blue'> <a href='http://192.168.1.195:84/issues/chain/approve.php?id=$SEQUENCE&user=teamlead'>Click here to Approve</a></font></b>
		// <br/><br/><b><font color='green'> <a href='http://issues.unionsg.com/chain/approve.php?id=$SEQUENCE&user=teamlead&code=RJT'>Click here to Reject</a></font></b>";
	
	
		$sql="UPDATE tb_change set CacheData='".base64_encode($message)."' WHERE requestor_id='$SEQUENCE'";
		// mysqli_query($conn,$sql);
		$stmt = $conn->prepare($sql);
		$result = $stmt->execute();
	
		// $message.=$linkToApprove;
	
	
	
	// 	// $deptHeadId = $_SESSION['dept_head'];
	
	//  $hostname_ticket = "localhost";
	//  $database_ticket = "unionsgc_ticket";
	//  $username_ticket = "root";
	//  $password_ticket = "firefox";
	//  $DEFAULT_PASS="PASS1234";
	//  $ADMIN_EMAIL="SUPPORT24X7@UNIONSG.COM";
	
	// try {
	//     $connT = new PDO("mysql:host=$hostname_ticket;dbname=$database_ticket", $username_ticket, $password_ticket);
	//     // set the PDO error mode to exception
	//     $connT->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	// //    echo "Connected successfully"; 
	//     }
	// catch(PDOException $e)
	//     {
	//     echo "Connection failed: " . $e->getMessage(); die;
	//     }
		
	// 	$sqlEmail = "SELECT Email FROM users WHERE Login_Id = '$deptHeadId' ";
	
	// 	$stmtEmail = $connT->prepare($sqlEmail);
	// 	$stmtEmail ->execute();
	
	// 	$resDept= $stmtEmail->fetchAll(PDO::FETCH_ASSOC);
	// 	$headEmail = $resDept[0]['Email'];
	// 	echo($headEmail); die();
		// echo($message);die();
		// notify_request($to,$subject,$message);
		//setTimeout(function(){window.open('fetch.php?cle=true','_self')},2000)
	
		
		$mailName = "Change Management";
		// $to =$_SESSION['deptHdEmail'];
		$to = "amarfio.joshua@unionsg.com";
		// $newEmail = "amarfio.joshua@unionsg.com";
		$recipients = $_SESSION['CHG_MAILS'];
	
		// print_r($recipients); die();
		
		//mail setup
		$mail = new PHPMailer(true);
		$done = "";
		try{
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
		$mail->setFrom("hr@unionsg.com", $mailName);
		$mail->addAddress($to);
		// $mail->addCC($submitterEmail);
		// foreach($recipients as $email){
		// 	$mail->addCC($email);	
		// }
	
		// $mail->addAddress("daniel.eyeson@unionsg.com");
		// $mail->addAddress("chris.armarfio@unionsg.com");
	
			$mail->Subject=$subject;
			$mail->Body = $message;
			$done = $mail->send();
	
		}catch(Exception $e){
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
		
		if($done){
	
			$message= "
			<script>
			echo('Request Sent Successfully','success');
			echo('Email sent to change management team','success');
			localStorage.clear();
			document.getElementById('request_form').reset();
			setTimeout(function(){window.location.assign('fetch.php')},1000);
			</script>
		";
		}
		else{
			$message= "<script>	echo('An Error occurred while sending emails.','danger');</script>";
		}
		
		}
		else
		{
		   $message= "<script>	echo('An Error occurred while sending request.','danger');</script>";
	
		}

	// if(!empty($_FILES["file"]["name"])){
	// 	if(in_array($fileType, $allowTypes)){
	// 		// Upload file to server
		
	// 		if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){

	// 			// echo $targetFilePath;die();
	// 			// Insert image file name into database
	// 			// $insert = $db->query("INSERT into images (file_name, uploaded_on) VALUES ('".$fileName."', NOW())");
				
				
	// 		}else{
	// 			$statusMsg = "Sorry, there was an error uploading your file.";
	// 			$message="<script> echo ($statusMsg) </script>"; 
	// 			die();
	// 		}
	// 	}else{
	// 		$statusMsg = 'Sorry, only JPG, JPEG, PNG, GIF, & PDF files are allowed to upload.';
	// 		$message = "<script> echo ($statusMsg) </script>";
	// 		die();
	// 	}
	// }

   
	// echo($customerName." ".$requestNumber." ".$descriptionOfChange." ".$changeImpactAssessment." ".$reasonForChange." ".$scopeOfChange." ".$budgetOfChange." ".$riskPlan." ".$otherImpacts." ".$resourcesRequired." ".$dateSubmitted." ".$dateRequired);
	// echo("this is done");die();




		// echo($result); die();


	
}

// if(isset($_POST['save'])){
	
// }

// $exploadedData="";
// if(isset($_GET['from']))
// {
//   //change coming from incident
//   if($_GET['from']=='incident')
//   {
//     $exploadedData=explode("~",$_SESSION['DATA_TO_PASS_TO_CHANGE']);
//   }

// }
?>
<!doctype html>
<html lang="en">
<head>
	   <?php include '../views/header_script1.php'; ?>
	   <link href='../views/assets/css/jquery.dataTables.min.css' rel='stylesheet' type='text/css'>
	     <link rel="stylesheet" href="css/pikaday.css">
</head>
<body>
<div class="wrapper">

	   <?php include '../views/nav1.php'; ?>
	        <div class="content">
	            <div class="container-fluid">
	                <div class="row">
	                    <div class="col-md-12">
	                        <div class="card">
	                            <div class="card-header" data-background-color="purple">
	                                <h4 class="title">
										<div class="row">
											<div class="col-md-4">
												Your Change No is : <font color='yellow'><?php echo $SEQUENCE;?>  </font>
											</div>
											
											<div class="col-md-4"></div>

											<div class="col-md-4">
												Ticket ID : <font color=''><?php 
													if(isset($exploadedData[6]))
													{
														echo $exploadedData[6]; 
													}
													else{
														echo "No ticket id";
													}
												?></font>
											</div>	
										</div>
										
									</h4>
								</div>
	                            	<div class="card-content" >

												<div class="row">
													<div class="col-md-12">
														<div class="card-content table-responsive">


											<!----------START HERE--------------->
										<form method="post" id='request_form'>
											
											<div class="row">
													<div class="col-md-4">
														<div class="form-group label-floating">
															<label class="control-label" style="color: red;">Customer Name * </label>
															<select class="form-control" name="customerName" id="focusedInput" required>
															<?php echo $_SESSION["CHG_CLIENTS"];?>
														    </select>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group label-floating">
															<label class="control-label">Request Number</label>
															<input class="form-control" name="requestNumber" type="text" value= <?php echo $SEQUENCE;?> />
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group label-floating">
															<label class="control-label" style="color: red;">Request by * </label>
															<input class="form-control" name="requestBy" type="text" required/>
														</div>
													</div>

	                                    	</div>



											<div class="row">
												<div class="col-md-12">
													<div class="form-group label-floating">
														<label class="control-label" style="color: red;">Description Of Change * </label>
														<textarea class="form-control" id="descriptionOfChange" rows="4" cols="50" name="descriptionOfChange" required>  </textarea>                
													</div>
												</div>
											</div>



											<div class="row">
												<div class="col-md-4">
													<div class="form-group label-floating">
															<label class="control-label">Other comments  </label>
															<input class="form-control" name="otherComments" type="text" />
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label" style="color: red;">Reason for change * </label>
														<!-- <textarea class="form-control" id="reasonForChange" name="reasonForChange">  </textarea> -->
														<input class="form-control" name="reasonForChange" type="text" required/>

													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label">Change impact assessment  </label>
														<input class="form-control" name="changeImpactAssessment" type="text" required/>
													</div>
												</div>

											</div>



											<div class="row">
												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label" style ="color: red">Scope  *</label>
														<input class="form-control" name="scopeOfChange" type="text" required/>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label" style="color: red;">Schedule * </label>
														<!-- <textarea class="form-control" id="scheduleOfChange" name="scheduleOfChange">  </textarea> -->
														<input class="form-control" name="scheduleOfChange" type="text" required/>

													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label">Budget    </label>
														<input class="form-control" name="budgetOfChange" type="text"/>
													</div>
												</div>
											</div>




										   <div class="row">
												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label" style="color: red">Risk Plan * </label>
														<input class="form-control" name="riskPlan" type="text" required/>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label">Other impacts </label>
														<input class="form-control" name="otherImpacts" type="text"/>
														<!-- <textarea class="form-control" id="otherImpacts" name="otherImpacts">  </textarea> -->
													</div>
												</div>


												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label" style="color: red;">Resources Required * </label>
														<input class="form-control" name="resourcesRequired" type="text" required/>
													</div>
												</div>


											</div>

											<div class="row">
												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label" style="color: red;">Change Type * </label>
															<select class="form-control" name="changeType" id="focusedInput" required>
																<option>--select change type--</option>
																<option value="1">Normal</option>
																<option value="2">Emergency</option>
															</select>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label">Raised by </label>
														<input class="form-control" name="raisedBy" value="<?php echo $_SESSION['FULL_NAME']?>" type="text" readonly/>
													</div>
												</div>


												<div class="col-md-4">
														<div class="form-group label-floating">
																<label class="control-label">Implementation Date  </label>
																<input class="form-control"  id="datepicker-theme"  name="dateRequired" required value="<?php echo $actual_time?>" readonly/>

														</div>
												</div>


											</div>

											<!-- <div class="row">	
												<div class="col-md-4">
													<div class="form-group label-floating">
														<label class="control-label" style="color: green;">Click here to upload file</label>
													
														<input class="form-control" type="file" name="fileUpload">
													</div>
												</div>
											
												
											
													
											</div> -->

											<!-- <div class="row">
												<div class="col-md-4">
													<div class="form-group label-floating">
													<iframe src="../up.php" style='width:100%' scrolling='yes'></iframe>
														<div id='attachment' style='background:#ccc;width:200px;height:200px;display:none'  ></div>
													</div>
												</div>
											</div> -->


												<input type='hidden' name='sequence' value='<?php echo $SEQUENCE;?>'>
												<input type='hidden' name='ticket_id_no' value='<?php if(isset($exploadedData[6])){echo $exploadedData[6];}?>'>

												<div class="row">
													<div class="col-md-4"></div>
													<div class="col-md-4">
														<!-- <input type="button"  onclick="" class="btn btn-primary" value="Save" name="save" > &nbsp; &nbsp; &nbsp; -->
														<input type="button"  onclick="localStorage.clear();alert('Data cleared successfully ');window.location.assign('requestor.php');" class="btn btn-info" value="Clear" n > &nbsp; &nbsp; &nbsp;
														<input type="submit" value="Submit" name="submit" class="btn btn-success ">
													</div>
													<div class="col-md-4"></div>
												</div>
													
        								</form>

											<!-----------END HERE---------------->
											</div>


	                                        </div>
	                                    </div>


										<div class="clearfix"></div>

	                            </div>
	                        </div>
	                    </div>

	                </div>
	            </div>
	        </div>

	       	<?php include '../views/footer.php'?>
		</div>
	</div>

<!-------MODAL START----->
<!-- Modal -->
</body>

	<?php include '../views/footer_script.php'?>
	<script src="../views/assets/js/jquery.dataTables.min.js" type="text/javascript"></script>

	<!--<script src="../js/add_company.js" type="text/javascript"></script>----->
	<script>
	$('#page99').addClass("active");
	$(document).ready(function(){
    $('#form_content').DataTable();
});

</script>
	<script src="jdate/pikaday.js"></script>
     <script>

     // You can use different themes with the `theme` option
    var pickerDefault = new Pikaday(
    {
        field: document.getElementById('datepicker'),
    });

    var pickerTheme = new Pikaday(
    {
        field: document.getElementById('datepicker-theme'),
        theme: 'dark-theme'
    });

    var pickerTriangle = new Pikaday(
    {
        field: document.getElementById('datepicker-triangle'),
        theme: 'triangle-theme'
    });






	function saveinstance_state(){

		if (typeof(Storage) !== "undefined") {
    // Code for localStorage/sessionStorage.
		localStorage.setItem("focusedInput", $('#focusedInput').val());
		localStorage.setItem("project_bau_initiative", $('#project_bau_initiative').val());
		localStorage.setItem("submittername", $('#submittername').val());
		localStorage.setItem("brief_description_of_request", $('#brief_description_of_request').val());
		localStorage.setItem("DateSubmitted", $('#DateSubmitted').val());
		localStorage.setItem("datepicker-theme", $('#datepicker-theme').val());
		localStorage.setItem("priority", $('#priority').val());
		localStorage.setItem("ReasonforChange", $('#ReasonforChange').val());
		localStorage.setItem("focusedInput", $('#focusedInput').val());
		localStorage.setItem("NamesOfArtifactsImpacted", $('#NamesOfArtifactsImpacted').val());
		localStorage.setItem("comments", $('#comments').val());
		localStorage.setItem("client_affected", $('#client_affected').val());
		localStorage.setItem("Country", $('#Country').val());
		localStorage.setItem("version_of_x100_requring_change", $('#version_of_x100_requring_change').val());
		localStorage.setItem("other_artifacts_impacted", $('#other_artifacts_impacted').val());
		localStorage.setItem("leader_email", $('#leader_email').val());
		localStorage.setItem("developer", $('#developer').val());
		alert("Data Saved Successfully");

		} else {
			// Sorry! No Web Storage support..
			alert("Your Browser does not support saving mode at this time");
		}


	}



	if (typeof(Storage) !== "undefined") {
    // Code for localStorage/sessionStorage.
			if (localStorage.getItem("focusedInput") !== null) {
		  //...
			$('#focusedInput').val(localStorage.getItem("focusedInput"));
			$('#project_bau_initiative').val(localStorage.getItem("project_bau_initiative"));
			$('#submittername').val("<?php echo $get_submitter_name."".$_SESSION['FULL_NAME'];?>");
			$('#brief_description_of_request').val(localStorage.getItem("brief_description_of_request"));
			$('#DateSubmitted').val(localStorage.getItem("DateSubmitted"));
			$('#datepicker-theme').val(localStorage.getItem("datepicker-theme"));
			$('#priority').val(localStorage.getItem("priority"));
			$('#ReasonforChange').val(localStorage.getItem("ReasonforChange"));
			$('#focusedInput').val(localStorage.getItem("focusedInput"));
			$('#NamesOfArtifactsImpacted').val(localStorage.getItem("NamesOfArtifactsImpacted"));
			$('#other_artifacts_impacted').val(localStorage.getItem("other_artifacts_impacted"));
			$('#comments').val(localStorage.getItem("comments"));
			$('#client_affected').val(localStorage.getItem("client_affected"));
			$('#Country').val(localStorage.getItem("Country"));
			$('#version_of_x100_requring_change').val(localStorage.getItem("version_of_x100_requring_change"));
			$('#leader_email').val(localStorage.getItem("leader_email"));
			$('#developer').val(localStorage.getItem("developer"));

		}



		}
</script>
<?php echo $message;?>
</html>
