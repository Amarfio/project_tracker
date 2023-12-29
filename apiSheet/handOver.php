<?php


header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once 'connect.php';

$data = json_decode(file_get_contents("php://input"));


// echo json_encode($data); die();
if(
    isset($data) 
){
    // echo json_encode($data);die(); 

    $dept_head_id = mysqli_real_escape_string($conn, $data->currentDeptHead);
    $hand_over_note = mysqli_real_escape_string($conn, $data->handOverNote);
    $new_dept_head_id= mysqli_real_escape_string($conn, $data->newDeptHead);
    $dept_id = mysqli_real_escape_string($conn, $data->dept_id);

    //code to send email to the user
    $from = "Project Tracker App";
    $name="no-reply";
    $subject = "Handing over process";
    
    $body= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD XHTML 1.0 Transitional //EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml' xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office'>
        
        <head>
          <!--[if gte mso 9]>
        <xml>
          <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
          </o:OfficeDocumentSettings>
        </xml>
        <![endif]-->
          <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
          <meta name='viewport' content='width=device-width, initial-scale=1.0'>
          <meta name='x-apple-disable-message-reformatting'>
          <!--[if !mso]><!-->
          <meta http-equiv='X-UA-Compatible' content='IE=edge'>
          <!--<![endif]-->
          <title></title>
        
          <style type='text/css'>
            @media only screen and (min-width: 620px) {
              .u-row {
                width: 600px !important;
              }
              .u-row .u-col {
                vertical-align: top;
              }
              .u-row .u-col-100 {
                width: 600px !important;
              }
            }
            
            @media (max-width: 620px) {
              .u-row-container {
                max-width: 100% !important;
                padding-left: 0px !important;
                padding-right: 0px !important;
              }
              .u-row .u-col {
                min-width: 320px !important;
                max-width: 100% !important;
                display: block !important;
              }
              .u-row {
                width: 100% !important;
              }
              .u-col {
                width: 100% !important;
              }
              .u-col>div {
                margin: 0 auto;
              }
            }
            
            body {
              margin: 0;
              padding: 0;
            }
            
            table,
            tr,
            td {
              vertical-align: top;
              border-collapse: collapse;
            }
            
            p {
              margin: 0;
            }
            
            .ie-container table,
            .mso-container table {
              table-layout: fixed;
            }
            
            * {
              line-height: inherit;
            }
            
            a[x-apple-data-detectors='true'] {
              color: inherit !important;
              text-decoration: none !important;
            }
            
            table,
            td {
              color: #000000;
            }
            
            #u_body a {
              color: #0000ee;
              text-decoration: underline;
            }
          </style>
        
        
        
          <!--[if !mso]><!-->
          <link href='https://fonts.googleapis.com/css?family=Cabin:400,700' rel='stylesheet' type='text/css'>
          <!--<![endif]-->
        
        </head>
        
        <body class='clean-body u_body' style='margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #f9f9f9;color: #000000'>
          <!--[if IE]><div class='ie-container'><![endif]-->
          <!--[if mso]><div class='mso-container'><![endif]-->
          <table id='u_body' style='border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #f9f9f9;width:100%' cellpadding='0' cellspacing='0'>
            <tbody>
              <tr style='vertical-align: top'>
                <td style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
                  <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td align='center' style='background-color: #f9f9f9;'><![endif]-->
        
        
                  <div class='u-row-container' style='padding: 0px;background-color: transparent'>
                    <div class='u-row' style='Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;'>
                      <div style='border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;'>
                        <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding: 0px;background-color: transparent;' align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px;'><tr style='background-color: transparent;'><![endif]-->
        
                        <!--[if (mso)|(IE)]><td align='center' width='600' style='width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;' valign='top'><![endif]-->
                        <div class='u-col u-col-100' style='max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;'>
                          <div style='height: 100%;width: 100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div style='height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;'>
                              <!--<![endif]-->
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <div style='color: #afb0c7; line-height: 170%; text-align: center; word-wrap: break-word;'>
                                        <p style='font-size: 14px; line-height: 170%;'><span style='font-size: 14px; line-height: 23.8px;'>View Email in Browser</span></p>
                                      </div>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                          </div>
                        </div>
                        <!--[if (mso)|(IE)]></td><![endif]-->
                        <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
                      </div>
                    </div>
                  </div>
        
        
        
                  <div class='u-row-container' style='padding: 0px;background-color: transparent'>
                    <div class='u-row' style='Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;'>
                      <div style='border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;'>
                        <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding: 0px;background-color: transparent;' align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px;'><tr style='background-color: #ffffff;'><![endif]-->
        
                        <!--[if (mso)|(IE)]><td align='center' width='600' style='width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;' valign='top'><![endif]-->
                        <div class='u-col u-col-100' style='max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;'>
                          <div style='height: 100%;width: 100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div style='height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;'>
                              <!--<![endif]-->
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:20px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                        <tr>
                                          <td style='padding-right: 0px;padding-left: 0px;' align='center'>
        
                                            <img align='center' border='0' src='https://issues.unionsg.com/images/logo.png' alt='Image' title='Image' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;width: 100%;max-width: 266px;'
                                              width='266' />
        
                                          </td>
                                        </tr>
                                      </table>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                          </div>
                        </div>
                        <!--[if (mso)|(IE)]></td><![endif]-->
                        <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
                      </div>
                    </div>
                  </div>
        
        
        
                  <div class='u-row-container' style='padding: 0px;background-color: transparent'>
                    <div class='u-row' style='Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #003399;'>
                      <div style='border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;'>
                        <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding: 0px;background-color: transparent;' align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px;'><tr style='background-color: #003399;'><![endif]-->
        
                        <!--[if (mso)|(IE)]><td align='center' width='600' style='width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;' valign='top'><![endif]-->
                        <div class='u-col u-col-100' style='max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;'>
                          <div style='height: 100%;width: 100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div style='height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;'>
                              <!--<![endif]-->
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:40px 10px 10px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                                        <tr>
                                          <td style='padding-right: 0px;padding-left: 0px;' align='center'>
        
                                            <img align='center' border='0' src='https://cdn.templates.unlayer.com/assets/1597218650916-xxxxc.png' alt='Image' title='Image' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;width: 26%;max-width: 150.8px;'
                                              width='150.8' />
        
                                          </td>
                                        </tr>
                                      </table>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <div style='color: #e5eaf5; line-height: 140%; text-align: center; word-wrap: break-word;'>
                                        <p style='font-size: 14px; line-height: 140%;'><strong>ACCOUNT SET UP !</strong></p>
                                      </div>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:0px 10px 31px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <div style='color: #e5eaf5; line-height: 140%; text-align: center; word-wrap: break-word;'>
                                        <p style='font-size: 14px; line-height: 140%;'><span style='font-size: 28px; line-height: 39.2px;'><strong><span style='line-height: 39.2px; font-size: 28px;'>SET UP YOUR PASSWORD <br /></span></strong>
                                          </span>
                                        </p>
                                      </div>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                          </div>
                        </div>
                        <!--[if (mso)|(IE)]></td><![endif]-->
                        <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
                      </div>
                    </div>
                  </div>
        
        
        
                  <div class='u-row-container' style='padding: 0px;background-color: transparent'>
                    <div class='u-row' style='Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;'>
                      <div style='border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;'>
                        <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding: 0px;background-color: transparent;' align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px;'><tr style='background-color: #ffffff;'><![endif]-->
        
                        <!--[if (mso)|(IE)]><td align='center' width='600' style='width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;' valign='top'><![endif]-->
                        <div class='u-col u-col-100' style='max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;'>
                          <div style='height: 100%;width: 100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div style='height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;'>
                              <!--<![endif]-->
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:33px 55px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <div style='line-height: 160%; text-align: center; word-wrap: break-word;'>
                                        <p style='font-size: 14px; line-height: 160%;'><span style='font-size: 22px; line-height: 35.2px;'>Hi $f_name, </span></p>
                                        <p style='font-size: 14px; line-height: 160%;'><span style='font-size: 18px; line-height: 28.8px;'>Your account has been created successfully for the Project Tracker Application. Kindly click the reset passowrd below to reset your password. And also attachment to this email is a manual on how to use the application. <br /></span></p>
                                      </div>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <!--[if mso]><style>.v-button {background: transparent !important;}</style><![endif]-->
                                      <div align='center'>
                                        <!--[if mso]><v:roundrect xmlns:v='urn:schemas-microsoft-com:vml' xmlns:w='urn:schemas-microsoft-com:office:word' href='http://192.168.1.195:84/project_tracker/set_password/$set_password' style='height:46px; v-text-anchor:middle; width:235px;' arcsize='8.5%'  stroke='f' fillcolor='#236fa1'><w:anchorlock/><center style='color:#FFFFFF;font-family:'Cabin',sans-serif;'><![endif]-->
                                        <a href='http://192.168.1.195:84/project_tracker/set_password/$set_password' target='_blank' class='v-button' style='box-sizing: border-box;display: inline-block;font-family:'Cabin',sans-serif;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #FFFFFF; background-color: #236fa1; border-radius: 4px;-webkit-border-radius: 4px; -moz-border-radius: 4px; width:auto; max-width:100%; overflow-wrap: break-word; word-break: break-word; word-wrap:break-word; mso-border-alt: none;'>
                                          <span style='display:block;padding:14px 44px 13px;line-height:120%;'><span style='font-size: 16px; line-height: 19.2px;'><strong><span style='line-height: 19.2px; font-size: 16px;'>RESET PASSWORD</span></strong>
                                          </span>
                                          </span>
                                        </a>
                                        <!--[if mso]></center></v:roundrect><![endif]-->
                                      </div>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:33px 55px 60px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <div style='line-height: 160%; text-align: center; word-wrap: break-word;'>
                                        <p style='line-height: 160%; font-size: 14px;'><span style='font-size: 18px; line-height: 28.8px;'>Thanks,</span></p>
                                        <p style='line-height: 160%; font-size: 14px;'><span style='font-size: 18px; line-height: 28.8px;'>Enterprise Solutions Department.<br /></span></p>
                                      </div>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                          </div>
                        </div>
                        <!--[if (mso)|(IE)]></td><![endif]-->
                        <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
                      </div>
                    </div>
                  </div>
        
        
        
                  <div class='u-row-container' style='padding: 0px;background-color: transparent'>
                    <div class='u-row' style='Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #e5eaf5;'>
                      <div style='border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;'>
                        <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding: 0px;background-color: transparent;' align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px;'><tr style='background-color: #e5eaf5;'><![endif]-->
        
                        <!--[if (mso)|(IE)]><td align='center' width='600' style='width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;' valign='top'><![endif]-->
                        <div class='u-col u-col-100' style='max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;'>
                          <div style='height: 100%;width: 100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div style='height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;'>
                              <!--<![endif]-->
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:10px 10px 33px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <div align='center'>
                                        <div style='display: table; max-width:146px;'>
                                          <!--[if (mso)|(IE)]><table width='146' cellpadding='0' cellspacing='0' border='0'><tr><td style='border-collapse:collapse;' align='center'><table width='100%' cellpadding='0' cellspacing='0' border='0' style='border-collapse:collapse; mso-table-lspace: 0pt;mso-table-rspace: 0pt; width:146px;'><tr><![endif]-->
        
        
                                          <!--[if (mso)|(IE)]><td width='32' style='width:32px; padding-right: 17px;' valign='top'><![endif]-->
                                          <table align='left' border='0' cellspacing='0' cellpadding='0' width='32' height='32' style='width: 32px !important;height: 32px !important;display: inline-block;border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;margin-right: 17px'>
                                            <tbody>
                                              <tr style='vertical-align: top'>
                                                <td align='left' valign='middle' style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
                                                  <a href='https://www.linkedin.com/company/union-systems-global-ltd/mycompany/' title='LinkedIn' target='_blank'>
                                                    <img src='https://cdn.tools.unlayer.com/social/icons/circle/linkedin.png' alt='LinkedIn' title='LinkedIn' width='32' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: none;height: auto;float: none;max-width: 32px !important'>
                                                  </a>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <!--[if (mso)|(IE)]></td><![endif]-->
        
                                          <!--[if (mso)|(IE)]><td width='32' style='width:32px; padding-right: 17px;' valign='top'><![endif]-->
                                          <table align='left' border='0' cellspacing='0' cellpadding='0' width='32' height='32' style='width: 32px !important;height: 32px !important;display: inline-block;border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;margin-right: 17px'>
                                            <tbody>
                                              <tr style='vertical-align: top'>
                                                <td align='left' valign='middle' style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
                                                  <a href='info@unionsg.com' title='Email' target='_blank'>
                                                    <img src='https://cdn.tools.unlayer.com/social/icons/circle/email.png' alt='Email' title='Email' width='32' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: none;height: auto;float: none;max-width: 32px !important'>
                                                  </a>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <!--[if (mso)|(IE)]></td><![endif]-->
        
                                          <!--[if (mso)|(IE)]><td width='32' style='width:32px; padding-right: 0px;' valign='top'><![endif]-->
                                          <table align='left' border='0' cellspacing='0' cellpadding='0' width='32' height='32' style='width: 32px !important;height: 32px !important;display: inline-block;border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;margin-right: 0px'>
                                            <tbody>
                                              <tr style='vertical-align: top'>
                                                <td align='left' valign='middle' style='word-break: break-word;border-collapse: collapse !important;vertical-align: top'>
                                                  <a href='https://twitter.com/Unionsys_Global' title='Twitter' target='_blank'>
                                                    <img src='https://cdn.tools.unlayer.com/social/icons/circle/twitter.png' alt='Twitter' title='Twitter' width='32' style='outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: none;height: auto;float: none;max-width: 32px !important'>
                                                  </a>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <!--[if (mso)|(IE)]></td><![endif]-->
        
        
                                          <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
                                        </div>
                                      </div>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                          </div>
                        </div>
                        <!--[if (mso)|(IE)]></td><![endif]-->
                        <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
                      </div>
                    </div>
                  </div>
        
        
        
                  <div class='u-row-container' style='padding: 0px;background-color: transparent'>
                    <div class='u-row' style='Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #003399;'>
                      <div style='border-collapse: collapse;display: table;width: 100%;height: 100%;background-color: transparent;'>
                        <!--[if (mso)|(IE)]><table width='100%' cellpadding='0' cellspacing='0' border='0'><tr><td style='padding: 0px;background-color: transparent;' align='center'><table cellpadding='0' cellspacing='0' border='0' style='width:600px;'><tr style='background-color: #003399;'><![endif]-->
        
                        <!--[if (mso)|(IE)]><td align='center' width='600' style='width: 600px;padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;' valign='top'><![endif]-->
                        <div class='u-col u-col-100' style='max-width: 320px;min-width: 600px;display: table-cell;vertical-align: top;'>
                          <div style='height: 100%;width: 100% !important;'>
                            <!--[if (!mso)&(!IE)]><!-->
                            <div style='height: 100%; padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;'>
                              <!--<![endif]-->
        
                              <table style='font-family:'Cabin',sans-serif;' role='presentation' cellpadding='0' cellspacing='0' width='100%' border='0'>
                                <tbody>
                                  <tr>
                                    <td style='overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:'Cabin',sans-serif;' align='left'>
        
                                      <div style='color: #fafafa; line-height: 180%; text-align: center; word-wrap: break-word;'>
                                        <p style='font-size: 14px; line-height: 180%;'><span style='font-size: 16px; line-height: 28.8px;'>Copyrights © Union Systems Global All Rights Reserved</span></p>
                                      </div>
        
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
        
                              <!--[if (!mso)&(!IE)]><!-->
                            </div>
                            <!--<![endif]-->
                          </div>
                        </div>
                        <!--[if (mso)|(IE)]></td><![endif]-->
                        <!--[if (mso)|(IE)]></tr></table></td></tr></table><![endif]-->
                      </div>
                    </div>
                  </div>
        
        
                  <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                </td>
              </tr>
            </tbody>
          </table>
          <!--[if mso]></div><![endif]-->
          <!--[if IE]></div><![endif]-->
        </body>
        
        </html>";
        // $headers = "From: project.tracker@unionsg.com" . "\r\n" .
        $headers = "From: USG" ;
           
        // echo json_encode(
        //         array('email'=>$email,
        //               'caption'=>$name,
        //               'subject'=>$subject,
        //               'text'=>$txt)
        //     );die();
        // echo mail($to,$subject,$txt,$headers); die;

        // sending the email to the user
        // require_once "PHPMailer/PHPMailer.php";
        // require_once "PHPMailer/SMTP.php";
        // require_once "PHPMailer/Exception.php";

        // $mail = new PHPMailer();

        // //STMP Settings
        // $mail->isSMTP();
        // $mail->Host = "server.unionsg.com";
        // $mail->SMTPAuth=true;
        // $mail->Username="hr@unionsg.com";
        // $mail->Password="(qLwOdQ3F3cm";
        // $mail->Port = 587;
        // $mail->SMTPSecure = "tls";

        //Email Settings
        $mail->isHTML(true);
        $mail->setFrom($email , $name);
        $mail->addAddress($email);
        $mail->Subject=$subject;
        $mail->addAttachment('./uploads/Project Tracker User Manuel (Autosaved).docx','Project Tracker Manual.docx');
        $mail->Body = $txt;
        $done = $mail->send();

        if($done == 1){

            //insert into the database            
            $query = "INSERT INTO `hand_over` ( `dept_head_id`, `hand_over_note`,`new_dept_head_id`, `dept_id`) VALUES ('$dept_head_id', '$hand_over_note', '$new_dept_head_id', '$dept_id')";

            // echo($query); die();
            $result = mysqli_query($conn, $query);

        }
    


    if ($result == 1) { 
        
        $query = "SELECT u.id as user_id, u.f_name, u.profile_pic, u.bio, u.l_name, u.gender, u.username, u.is_dpt_head, u.can_approve, u.receive_emails, u.email, u.phone, u.country, u.city, u.postal_addr AS address, u.reset as token, c.id AS department_id, c.desc department, d.id AS role_id, d.desc as role, e_desc.desc AS alt_desc from users u LEFT JOIN code_desc c ON u.dept = c.id LEFT JOIN code_desc e_desc ON u.receive_emails = e_desc.id left JOIN code_desc d ON u.role = d.id WHERE u.id = '$user_id'";
        $result = mysqli_query($conn, $query);
        $num = mysqli_num_rows($result);

        if ($num > 0) {
            $row = mysqli_fetch_assoc($result);
            echo json_encode(
                array(
                    'message' => 'User update successfully',
                    'status' => 'success',
                    'data' => [
                        'user_id' => $row['user_id'],
                        'first_name' => $row['f_name'],
                        'last_name' => $row['l_name'],
                        'gender' => $row['gender'],
                        'username' => $row['username'],
                        'department_id' => $row['department_id'],
                        'department' => $row['department'],
                        'role_id' => $row['role_id'],
                        'role' => $row['role'],
                        'is_dept_head' => $row['is_dpt_head'],
                        'can_approve' => $row['can_approve'],
                        'email' => $row['email'],
                        'phone' => $row['phone'],
                        'address' => $row['address'],
                        'country' => $row['country'],
                        'bio' => $row['bio'],
                        'city' => $row['city'],
                        'emailNotice' =>$row['receive_emails'],
                        'desc_alt' =>$row['alt_desc']
                    ],
                    'token' => $row['token'],
                    'auth' => true,
                    'profile_pic' => $row['profile_pic']
                )
            );
        } else {
            echo json_encode(
                array(
                    'message' => 'failed to update user info',
                    'status' => 'failed'
                )
            );
        }

    }else {
            echo json_encode(
                array(
                    'message' => 'failed to update user info',
                    'status' => 'failed'
                )
            );
        }
 
}