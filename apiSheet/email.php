<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8"> <!-- utf-8 works for most cases -->
    <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
    <meta name="x-apple-disable-message-reformatting">  <!-- Disable auto-scale in iOS 10 Mail entirely -->
    <title></title> <!-- The title tag shows in email notifications, like Android 4.4. -->

    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">

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
div[style*="margin: 16px 0"] {
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

<body width="100%" style="margin: 0 !important; padding: 0 !important; mso-line-height-rule: exactly !important; background-color: #f1f1f1 !important;">
	<center style="width: 100% !important; background-color: #f1f1f1 !important;">
    <div style="display: none !important; font-size: 1px !important;max-height: 0px !important; max-width: 0px !important; opacity: 0 !important; overflow: hidden !important; mso-hide: all !important; font-family: sans-serif !important;">
      &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>
    <div style="max-width: 600px !important; margin: 0 auto !important; margin-top: -20px !important;" class="email-container">
    	<table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto !important;">
        <tr>
          <td class="bg_light" style="text-align: center !important;">
          	<img src="logo.png" width="270" style="max-width: 100% !important; margin-top: -35px !important; margin-bottom: -50px !important;"
            />
          </td>
        </tr>
      </table>
    	<!-- BEGIN BODY -->
      <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto !important;">
      	<!-- <tr> -->
          <!-- <td valign="top" class="bg_white" style="padding: 1em 2.5em 0 2.5em !important;"> -->
          	<!-- <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
          		<tr>
          			<td class="logo" style="text-align: center !important;">
			            <h1><a href="#">Issue Assigned</a></h1>
			          </td>
          		</tr>Ω
          	</table> -->
          <!-- </td> -->
	      <!-- </tr>end tr -->
	      <tr>
          <td valign="middle" class="hero bg_white" style="padding: 3em 0 2em 0 !important;">
          	<div style="margin-top: -24px !important;">
            <img src="email.png" alt="" style="width: 100px !important; max-width: 600px !important; height: auto !important; margin: auto !important; display: block !important;">
            </div>
          </td>
	      </tr><!-- end tr -->
		<tr>
          <td valign="middle" class="hero bg_white" style="padding: 2em 0 4em 0 !important;">
            <table>
            	<tr>
            		<td>
            			<div class="text" style="padding: 0 2.5em !important; text-align: center !important;">
            				<h2 style="line-height: 2.2 !important; margin-top: -52px !important; font-size: 21px !important;">Newly Assigned Issue</h2>
            				<h3 style="font-size: 16px !important; line-height: 2.2 !important;">Dear Rapheal Djane Kotei, Incident Number <b style="color: grey !important;">PJ00006545</b> has been assigned to You. Below is a summary of the details:</h3>
            			</div>
            		</td>
            	</tr>
            	<tr>
            		<div class="text" style="padding: 0 2.5em !important; text-align: center !important;">
	            		<table class="table table-stripped" style="line-height: 2.8 !important; margin-bottom: -10% !important; margin-top: -1% !important; color: black !important;">
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
							<td><b style="overflow: wrap;">This is a newly assigned issue</b></td>
						</tr>
						<tr>
							<td>Department Affected : &nbsp;&nbsp;&nbsp;</td>
							<td><b>Enterprise</b></td>
						</tr>
						<tr>
							<td colspan="2">
							<p style="margin-bottom: 15px !important; text-transform: capitalize !important;"><a href="#" style="background: #953581 !important; color: white !important; padding: 7px !important;">Click here to preview the details of the issue</a></p>
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


      <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto !important;">
        <tr>
          <td class="bg_light" style="text-align: center !important;">
          	<p>&#169 !important; <?php echo date('Y') ?> <a href="#" style="color: rgba(0,0,0,.8) !important;">Union Systems Global Limited</a>. All Rights Reserved</p>
          </td>
        </tr>
      </table>

    </div>
  </center>
</body>
</html>