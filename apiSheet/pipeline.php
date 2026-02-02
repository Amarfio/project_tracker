<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set time zone to match database
date_default_timezone_set('UTC');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connect.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';
require_once 'functions/activity_logs.php';
require_once 'functions/get_IP_Location.php';

use PHPMailer\PHPMailer\PHPMailer;

try {
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get the action from request
    $action = isset($_GET['action']) ? $_GET['action'] : null;

    // Function to send pipeline action email
    function sendPipelineActionEmail($action, $pipeline_id, $action_details = []) {
        global $conn;

        try {
            // Get pipeline details
            $sql = "SELECT p.*, 
                    CONCAT(u.f_name, ' ', u.l_name) as lead_name,
                    CONCAT(cu.f_name, ' ', cu.l_name) as created_by_name
                    FROM pipelines p
                    LEFT JOIN users u ON p.lead_id = u.id
                    LEFT JOIN users cu ON p.created_by = cu.id
                    WHERE p.id = ?";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for pipeline details: ' . $conn->error);
            }
            
            $stmt->bind_param('i', $pipeline_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Pipeline not found');
            }
            
            $pipeline = $result->fetch_assoc();
            
            // Get participants emails and names
            $participant_ids = array_filter(explode(',', $pipeline['participants']));
            $participant_emails = [];
            $participant_names = [];
            
            foreach ($participant_ids as $participant_id) {
                $sql = "SELECT id, CONCAT(f_name, ' ', l_name) AS name, email FROM users WHERE id = ? AND is_active = 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $participant_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $participant = $result->fetch_assoc();
                    if ($participant['email']) {
                        $participant_emails[] = $participant['email'];
                        $participant_names[] = $participant['name'];
                    }
                }
            }
            
            // Add lead to recipients if not already included
            $sql = "SELECT id, CONCAT(f_name, ' ', l_name) AS name, email FROM users WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $pipeline['lead_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $lead = $result->fetch_assoc();
                if ($lead['email'] && !in_array($lead['email'], $participant_emails)) {
                    $participant_emails[] = $lead['email'];
                    $participant_names[] = $lead['name'];
                }
            }
            
            // Add creator to recipients if not already included
            $sql = "SELECT id, CONCAT(f_name, ' ', l_name) AS name, email FROM users WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $pipeline['created_by']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $creator = $result->fetch_assoc();
                if ($creator['email'] && !in_array($creator['email'], $participant_emails)) {
                    $participant_emails[] = $creator['email'];
                    $participant_names[] = $creator['name'];
                }
            }
            
            // Prepare action-specific content
            $action_title = '';
            $action_icon = '';
            $action_color = '';
            $action_description = '';
            
            switch ($action) {
                case 'update':
                    $action_title = 'Pipeline Updated';
                    $action_icon = '&#x1F504;'; // 🔄
                    $action_color = '#3498DB';
                    $action_description = isset($action_details['changes']) ? 
                        "The following changes were made to the pipeline:\n" . implode("\n", $action_details['changes']) : 
                        "Pipeline details have been updated.";
                    break;
                
                case 'close':
                    $action_title = 'Pipeline Closed';
                    $action_icon = '&#x2705;'; // ✅
                    $action_color = '#27AE60';
                    $action_description = "This pipeline has been marked as completed and closed.";
                    break;
                
                case 'suspend':
                    $action_title = 'Pipeline Suspended';
                    $action_icon = '&#x23F8;&#xFE0F;'; // ⏸️
                    $action_color = '#E67E22';
                    $action_description = "This pipeline has been temporarily suspended.";
                    break;
                
                    case 'discussion':
                        $action_title = 'New Discussion Added';
                        $action_icon = '&#x1F4DC;'; // 📜
                        $action_color = '#9B59B6';
                        $action_description = isset($action_details['content']) ? 
                            "New discussion:\n" . $action_details['content'] : 
                            "A new discussion has been added to the pipeline.";
                        break;
                
                default:
                    $action_title = 'Pipeline Action';
                    $action_icon = '&#x1F4CB;'; // 📋
                    $action_color = '#34495E';
                    $action_description = "An action has been performed on the pipeline.";
            }
            
            // Format dates
            $discussion_date = date('F j, Y', strtotime($pipeline['discussion_date']));
            $next_date = date('F j, Y', strtotime($pipeline['next_date']));
            
            // Build email template with action section
            $emailBody = '
            <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <style type="text/css">
                    body {
                        font-family: "Helvetica Neue", Arial, sans-serif;
                        background-color: #f5f5f5;
                        margin: 0;
                        padding: 0;
                        line-height: 1.6;
                        color: #333333;
                    }
                    .action-section {
                        background-color: ' . $action_color . ';
                        color: white;
                        padding: 20px;
                        border-radius: 8px;
                        margin-bottom: 20px;
                    }
                </style>
            </head>
            <body style="font-family: \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; line-height: 1.6; color: #333333;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5;">
                    <tr>
                        <td align="center" style="padding: 20px 0;">
                            <table width="800" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; max-width: 800px;">
                                <!-- Header Section -->
                                <tr>
                                    <td style="background-color: #00475B; padding: 40px 40px 30px 40px;">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td>
                                                    <!-- Logo -->
                                                    <table border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td style="width: 50px; height: 50px; background-color: #ffffff; border-radius: 8px; text-align: center; vertical-align: middle; font-weight: bold; font-size: 24px; color: #00475B;">
                                                                P
                                                            </td>
                                                            <td style="padding-left: 15px; font-size: 22px; color: #ffffff; font-weight: 300; letter-spacing: 0.5px;">
                                                                Project Pipeline Tracker
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 25px;">
                                                    <h1 style="color: #ffffff; font-size: 28px; font-weight: 600; margin: 0 0 10px 0; line-height: 1.3;">
                                                         ' . $action_title . ': ' . htmlspecialchars($pipeline['title']) . '
                                                    </h1>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #B8D4D9; font-size: 14px; font-weight: 400; padding-top: 5px;">
                                                    Pipeline ID: ' . htmlspecialchars($pipeline['pipeline_id']) . ' | ' . $action_title . ' on ' . date('F j, Y') . '
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <!-- Main Content with Two Columns -->
                                <tr>
                                    <td style="padding: 40px; background-color: #ffffff;">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <!-- Left Column - Pipeline Details -->
                                                <td width="65%" valign="top" style="padding-right: 30px;">
                                                    <!-- Action Section -->
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 30px;">
                                                        <tr>
                                                            <td class="action-section">
                                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                    <tr>
                                                                        <td width="40" style="font-size: 24px; vertical-align: top;">
                                                                            ' . $action_icon . '
                                                                        </td>
                                                                        <td>
                                                                            <h2 style="color: #ffffff; font-size: 20px; font-weight: 600; margin: 0 0 10px 0;">
                                                                                ' . $action_title . '
                                                                            </h2>
                                                                            <div style="font-size: 14px; color: rgba(255,255,255,0.9); line-height: 1.6; white-space: pre-wrap;">
                                                                                ' . htmlspecialchars($action_description) . '
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <!-- Pipeline Title -->
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                        <tr>
                                                            <td style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                    Pipeline Title
                                                                </div>
                                                                <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                    ' . htmlspecialchars($pipeline['title']) . '
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <!-- Pipeline Description -->
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                        <tr>
                                                            <td style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                    Pipeline Description
                                                                </div>
                                                                <div style="font-size: 15px; color: #2C3E50; line-height: 1.6; white-space: pre-wrap;">
                                                                    ' . htmlspecialchars($pipeline['description'] ?: '') . '
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <!-- Two Column Layout -->
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                        <tr>
                                                            <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                    Created By
                                                                </div>
                                                                <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                    ' . htmlspecialchars($pipeline['created_by_name']) . '
                                                                </div>
                                                            </td>
                                                            <td width="4%"></td>
                                                            <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                    Discussion Lead
                                                                </div>
                                                                <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                    ' . htmlspecialchars($pipeline['lead_name']) . '
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                        <tr>
                                                            <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                    Discussion Date
                                                                </div>
                                                                <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                    ' . htmlspecialchars($discussion_date) . '
                                                                </div>
                                                            </td>
                                                            <td width="4%"></td>
                                                            <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                    Source
                                                                </div>
                                                                <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                    ' . htmlspecialchars($pipeline['source_name'] ?: '') . '
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                        <tr>
                                                            <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                    Source Owner
                                                                </div>
                                                                <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                    ' . htmlspecialchars($pipeline['source_owner'] ?: '') . '
                                                                </div>
                                                            </td>
                                                            <td width="4%"></td>
                                                            <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                    Next Discussion Date & Time
                                                                </div>
                                                                <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                        <tr>
                                                                            <td style="background-color: #ffffff; border: 1px solid #D1D5DB; border-radius: 4px; padding: 10px 12px; font-size: 14px; color: #2C3E50;">
                                                                                ' . htmlspecialchars($next_date) . '
                                                                            </td>
                                                                            <td width="10"></td>
                                                                            <td style="background-color: #ffffff; border: 1px solid #D1D5DB; border-radius: 4px; padding: 10px 12px; font-size: 14px; color: #2C3E50;">
                                                                                ' . htmlspecialchars($pipeline['next_time'] ?: '') . '
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <!-- Participants -->
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                        <tr>
                                                            <td style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                                <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                    Participants
                                                                </div>
                                                                <div style="margin-top: 8px;">
                                                                    ' . implode(' ', array_map(function($name) {
                                                                        return '<span style="display: inline-block; background-color: #00475B; color: #ffffff; font-size: 13px; font-weight: 500; padding: 6px 14px; border-radius: 20px; margin: 4px 4px 4px 0;">' . htmlspecialchars($name) . '</span>';
                                                                    }, $participant_names)) . '
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <!-- Right Column - Action Details -->
                                                <td width="35%" valign="top" style="background-color: #F8F9FA; border-radius: 8px; padding: 25px;">
                                                    <div style="text-align: center; margin-bottom: 25px;">
                                                        <div style="font-size: 48px; margin-bottom: 10px;">
                                                            ' . $action_icon . '
                                                        </div>
                                                        <h3 style="color: #00475B; font-size: 18px; font-weight: 600; margin: 0 0 15px 0;">
                                                            ' . $action_title . '
                                                        </h3>
                                                        <div style="width: 40px; height: 3px; background-color: ' . $action_color . '; margin: 0 auto 15px auto;"></div>
                                                    </div>
                                                    <div style="background-color: #ffffff; border-radius: 6px; padding: 20px; border-left: 4px solid ' . $action_color . ';">
                                                        <div style="font-size: 14px; color: #2C3E50; line-height: 1.6; white-space: pre-wrap;">
                                                            ' . htmlspecialchars($action_description) . '
                                                        </div>
                                                    </div>
                                                    <div style="margin-top: 20px; padding: 15px; background-color: #E6F2FF; border-radius: 6px;">
                                                        <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                            Action Details
                                                        </div>
                                                        <div style="font-size: 13px; color: #2C3E50;">
                                                            <strong>Pipeline:</strong> ' . htmlspecialchars($pipeline['title']) . '<br>
                                                            <strong>Pipeline ID:</strong> ' . htmlspecialchars($pipeline['pipeline_id']) . '<br>
                                                            <strong>Action Date:</strong> ' . date('F j, Y g:i A') . '<br>
                                                            <strong>Status:</strong> ' . htmlspecialchars(ucfirst($pipeline['status'])) . '
                                                        </div>
                                                    </div>
                                                    <!-- Action Button -->
                                                    <div style="text-align: center; margin-top: 25px;">
                                                        <a href="http://10.203.14.97/project_tracker_test/pipeline_details?id=' . $pipeline_id . '" style="display: inline-block; background-color: ' . $action_color . '; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;">
                                                            View Pipeline Details
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Footer Section -->
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 40px; border-top: 1px solid #E1E4E8; padding-top: 30px;">
                                            <tr>
                                                <td style="text-align: center;">
                                                    <p style="margin: 8px 0; font-size: 13px; color: #2C3E50; font-weight: 600;">
                                                        Project Tracker System
                                                    </p>
                                                    <p style="margin: 8px 0; font-size: 12px; color: #6B7280; line-height: 1.6;">
                                                        This is an automated notification. Please do not reply directly to this email.
                                                    </p>
                                                    <p style="margin: 8px 0; font-size: 12px; color: #6B7280; line-height: 1.6;">
                                                        For support, contact your system administrator.
                                                    </p>
                                                    <p style="margin: 8px 0; font-size: 12px; color: #6B7280;">
                                                        &copy; ' . date('Y') . ' Project Pipeline Tracker. All rights reserved.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';
            
            // Send email
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = "mail.unionsg.com";
            $mail->SMTPAuth = true;
            $mail->Username = "support24x7@unionsg.com";
            $mail->Password = "xz1i8Hmnoj!D";
            $mail->Port = 587;
            $mail->SMTPSecure = "tls";
            
            $mail->setFrom('support24x7@unionsg.com', 'Project Tracker');
            foreach ($participant_emails as $email) {
                $mail->addAddress($email);
            }
            $mail->isHTML(true);
            $mail->Subject = 'Pipeline ' . $action_title . ': ' . $pipeline['title'];
            $mail->Body = $emailBody;
            
            if (!$mail->send()) {
                error_log('Failed to send pipeline action email: ' . $mail->ErrorInfo);
                return false;
            } else {
                error_log('Pipeline action email sent successfully to: ' . implode(', ', $participant_emails));
                return true;
            }
            
        } catch (Exception $e) {
            error_log('Error sending pipeline action email: ' . $e->getMessage());
            return false;
        }
    }

    switch ($action) {
        case 'get_user_info':
            $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
            
            if (!$user_id) {
                throw new Exception('User ID is required');
            }
            
            $sql = "SELECT id, f_name, l_name, username, gender, dept_id, profile_pic, email 
                    FROM users 
                    WHERE id = ? AND is_active = 1";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('User not found or inactive');
            }
            
            $user = $result->fetch_assoc();
            $user['full_name'] = $user['f_name'] . ' ' . $user['l_name'];
            
            echo json_encode([
                'success' => true,
                'data' => $user
            ]);
            break;

        case 'get_pipelines':
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
            
            $sql = "SELECT p.*, 
                    CONCAT(u.f_name, ' ', u.l_name) as lead_name,
                    CONCAT(cu.f_name, ' ', cu.l_name) as created_by_name
                    FROM pipelines p
                    LEFT JOIN users u ON p.lead_id = u.id
                    LEFT JOIN users cu ON p.created_by = cu.id";
            
            $where = [];
            $params = [];
            $types = '';
            
            if ($status && $status !== 'myPipelines') {
                $where[] = "p.status = ?";
                $params[] = $status;
                $types .= 's';
            } elseif ($status === 'myPipelines' && $user_id) {
                $where[] = "(p.lead_id = ? OR FIND_IN_SET(?, p.participants))";
                $params[] = $user_id;
                $params[] = $user_id;
                $types .= 'ii';
            }
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " ORDER BY p.discussion_date DESC";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $pipelines = [];
            while ($row = $result->fetch_assoc()) {
                $participant_ids = array_filter(explode(',', $row['participants']));
                $row['participants'] = $participant_ids;
                $row['discussion_date_formatted'] = date('Y-m-d', strtotime($row['discussion_date']));
                $row['next_date_formatted'] = date('Y-m-d', strtotime($row['next_date']));
                $row['lead'] = $row['lead_name'];
                $row['created_by'] = $row['created_by_name'];
                $pipelines[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $pipelines
            ]);
            break;

        case 'get_pipeline':
            $pipeline_id = isset($_GET['id']) ? intval($_GET['id']) : null;
            // echo($pipeline_id);die();
            
            if (!$pipeline_id) {
                throw new Exception('Pipeline ID is required');
            }
            
            $sql = "SELECT p.*, 
                    CONCAT(u.f_name, ' ', u.l_name) as lead_name,
                    CONCAT(cu.f_name, ' ', cu.l_name) as created_by_name
                    FROM pipelines p
                    LEFT JOIN users u ON p.lead_id = u.id
                    LEFT JOIN users cu ON p.created_by = cu.id
                    WHERE p.id = ?";
                    // echo($sql); die();
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('i', $pipeline_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Pipeline not found');
            }
            
            $pipeline = $result->fetch_assoc();
            $participant_ids = array_filter(explode(',', $pipeline['participants']));
            $pipeline['participants'] = $participant_ids;
            $pipeline['discussion_date_formatted'] = date('Y-m-d', strtotime($pipeline['discussion_date']));
            $pipeline['next_date_formatted'] = date('Y-m-d', strtotime($pipeline['next_date']));
            $pipeline['lead'] = $pipeline['lead_name'];
            $pipeline['created_by'] = $pipeline['created_by_name'];
            echo json_encode([
                'success' => true,
                'data' => $pipeline
            ]);
            break;

        case 'create_pipeline':
            $data = json_decode(file_get_contents('php://input'), true);

            error_log('create_pipeline input: ' . json_encode($data));
            
            $required = ['title', 'description', 'lead_id', 'source_name', 'source_owner', 'participants', 'discussion_date', 'next_date', 'next_time', 'created_by'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || ($field !== 'description' && empty($data[$field]))) {
                    throw new Exception("Required field missing: $field");
                }
            }
            
            if (!is_numeric($data['lead_id']) || $data['lead_id'] <= 0) {
                throw new Exception('Invalid Lead ID');
            }
            
            $sql = "SELECT id, CONCAT(f_name, ' ', l_name) AS name, email FROM users WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for lead_id check: ' . $conn->error);
            }
            $stmt->bind_param('i', $data['lead_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Invalid Lead ID: ' . $data['lead_id']);
            }
            
            $lead = $result->fetch_assoc();
            
            if (!is_numeric($data['created_by']) || $data['created_by'] <= 0) {
                throw new Exception('Invalid Created By ID');
            }
            
            $sql = "SELECT id, CONCAT(f_name, ' ', l_name) AS name, email FROM users WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for created_by check: ' . $conn->error);
            }
            $stmt->bind_param('i', $data['created_by']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Invalid Created By ID: ' . $data['created_by']);
            }
            
            $creator = $result->fetch_assoc();
            
            if (!is_array($data['participants']) || empty($data['participants'])) {
                throw new Exception('At least one participant is required');
            }
            
            $participant_emails = [];
            $participant_names = [];
            foreach ($data['participants'] as $participant_id) {
                if (!is_numeric($participant_id) || $participant_id <= 0) {
                    throw new Exception('Invalid Participant ID: ' . $participant_id);
                }
                $sql = "SELECT id, CONCAT(f_name, ' ', l_name) AS name, email FROM users WHERE id = ? AND is_active = 1";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed for participant check: ' . $conn->error);
                }
                $stmt->bind_param('i', $participant_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception('Invalid Participant ID: ' . $participant_id);
                }
                
                $participant = $result->fetch_assoc();
                if ($participant['email']) {
                    $participant_emails[] = $participant['email'];
                    $participant_names[] = $participant['name'];
                }
            }
            
            if ($lead['email'] && !in_array($lead['email'], $participant_emails)) {
                $participant_emails[] = $lead['email'];
                $participant_names[] = $lead['name'];
            }
            
            if ($creator['email'] && !in_array($creator['email'], $participant_emails)) {
                $participant_emails[] = $creator['email'];
                $participant_names[] = $creator['name'];
            }
            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['discussion_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['next_date'])) {
                error_log('Invalid date format - discussion_date: ' . $data['discussion_date'] . ', next_date: ' . $data['next_date']);
                throw new Exception('Invalid date format for discussion_date or next_date. Expected YYYY-MM-DD.');
            }
            
            $discussion_timestamp = strtotime($data['discussion_date']);
            $next_timestamp = strtotime($data['next_date']);
            if ($discussion_timestamp === false || $next_timestamp === false) {
                error_log('Date parsing failed - discussion_date: ' . $data['discussion_date'] . ', next_date: ' . $data['next_date']);
                throw new Exception('Invalid date values for discussion_date or next_date.');
            }
            
            if ($next_timestamp <= $discussion_timestamp) {
                throw new Exception('Next date must be after discussion date.');
            }
            
            $discussion_date = date('Y-m-d', $discussion_timestamp);
            $next_date = date('Y-m-d', $next_timestamp);
            $next_time = $data['next_time'];
            $description = $data['description'] ?: '';
            $source_name = $data['source_name'];
            $source_owner = $data['source_owner'];
            $status = 'active';
            $participants = implode(',', array_map('intval', $data['participants']));
            $pipeline_id = 'PL' . date('y') . str_pad(getNextPipelineId(), 6, '0', STR_PAD_LEFT);
            $activity = "Creating new pipeline";
            $lead_id = (int)$data['lead_id'];
            $created_by = (int)$data['created_by'];
            
            $conn->begin_transaction();
            
            try {
                $sql = "INSERT INTO pipelines (
                    pipeline_id, title, description, lead_id, source_name, source_owner, 
                    discussion_date, next_date, status, participants, created_by, next_time
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed for insert: ' . $conn->error);
                }
                
                error_log('Bind variables: ' . json_encode([
                    'pipeline_id' => $pipeline_id,
                    'title' => $data['title'],
                    'description' => $description,
                    'lead_id' => $lead_id,
                    'source_name' => $source_name,
                    'source_owner' => $source_owner,
                    'discussion_date' => $discussion_date,
                    'next_date' => $next_date,
                    'status' => $status,
                    'participants' => $participants,
                    'created_by' => $created_by,
                    'next_time' => $next_time
                ]));
                
                $stmt->bind_param('sssissssssis', 
                    $pipeline_id,
                    $data['title'],
                    $description,
                    $lead_id,
                    $source_name,
                    $source_owner,
                    $discussion_date,
                    $next_date,
                    $status,
                    $participants,
                    $created_by,
                    $next_time
                );
                
                if (!$stmt->execute()) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }
                
                $new_pipeline_id = $conn->insert_id;
                
                $sql = "SELECT pipeline_id, title, description, lead_id, source_name, source_owner, discussion_date, next_date, status, participants, created_by, next_time 
                        FROM pipelines WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed for verification: ' . $conn->error);
                }
                $stmt->bind_param('i', $new_pipeline_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $inserted_row = $result->fetch_assoc();
                error_log('Inserted pipeline data: ' . json_encode($inserted_row));
                
                if ($inserted_row['status'] !== 'active') {
                    throw new Exception('Status not saved correctly, found: ' . ($inserted_row['status'] ?? 'NULL'));
                }
                
                $conn->query("SET @disable_triggers = NULL");
                
                log_activity($conn, $created_by, $activity, 'success', getSecurity());
                
                $conn->commit();
                
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = "mail.unionsg.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = "support24x7@unionsg.com";
                    $mail->Password = "xz1i8Hmnoj!D";
                    $mail->Port = 587;
                    $mail->SMTPSecure = "tls";
                    
                    $mail->setFrom('support24x7@unionsg.com', 'Project Tracker');
                    foreach ($participant_emails as $email) {
                        $mail->addAddress($email);
                    }
                    $mail->isHTML(true);
                    $mail->Subject = 'New Pipeline: ' . $data['title'];
                    
                    $emailBody = '
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                        <style type="text/css">
                            body {
                                font-family: "Helvetica Neue", Arial, sans-serif;
                                background-color: #f5f5f5;
                                margin: 0;
                                padding: 0;
                                line-height: 1.6;
                                color: #333333;
                            }
                        </style>
                    </head>
                    <body style="font-family: \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; line-height: 1.6; color: #333333;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5;">
                            <tr>
                                <td align="center" style="padding: 20px 0;">
                                    <table width="800" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; max-width: 800px;">
                                        <!-- Header Section -->
                                        <tr>
                                            <td style="background-color: #00475B; padding: 40px 40px 30px 40px;">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td>
                                                            <!-- Logo -->
                                                            <table border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td style="width: 50px; height: 50px; background-color: #ffffff; border-radius: 8px; text-align: center; vertical-align: middle; font-weight: bold; font-size: 24px; color: #00475B;">
                                                                        P
                                                                    </td>
                                                                    <td style="padding-left: 15px; font-size: 22px; color: #ffffff; font-weight: 300; letter-spacing: 0.5px;">
                                                                        Project Pipeline Tracker
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-top: 25px;">
                                                            <h1 style="color: #ffffff; font-size: 28px; font-weight: 600; margin: 0 0 10px 0; line-height: 1.3;">
                                                                New Pipeline Created: ' . htmlspecialchars($data['title']) . '
                                                            </h1>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="color: #B8D4D9; font-size: 14px; font-weight: 400; padding-top: 5px;">
                                                            Pipeline ID: ' . htmlspecialchars($pipeline_id) . ' | Created on ' . date('F j, Y') . '
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- Hero Section with Overlay -->
                                        <tr>
                                            <td style="background-color: #D4C4A8; height: 280px; position: relative;">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="height: 280px;">
                                                    <tr>
                                                        <td valign="bottom" style="background-color: rgba(0, 71, 91, 0.9); padding: 25px 40px;">
                                                            <h2 style="font-size: 24px; font-weight: 600; margin: 0 0 15px 0; color: #ffffff;">
                                                                Pipeline Discussion Details
                                                            </h2>
                                                            <div style="width: 60px; height: 3px; background-color: #FF9F40; margin-bottom: 15px;"></div>
                                                            <div style="font-size: 16px; font-weight: 500; margin-bottom: 8px; color: #ffffff;">
                                                                ' . date('F Y') . '
                                                            </div>
                                                            <div style="font-size: 13px; line-height: 1.8; color: #E0EDF0;">
                                                                Created by: ' . htmlspecialchars($creator['name']) . '<br>
                                                                Discussion Lead: ' . htmlspecialchars($lead['name']) . '<br>
                                                                Source: ' . htmlspecialchars($source_name) . '
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- Main Content -->
                                        <tr>
                                            <td style="padding: 40px; background-color: #ffffff;">
                                                <div style="font-size: 15px; color: #555555; margin-bottom: 30px; line-height: 1.7;">
                                                    <strong>Dear Team,</strong><br>
                                                    A new pipeline has been created in the Project Tracker system. Please review the comprehensive details below and take appropriate action.
                                                </div>
                                                <!-- Pipeline Title -->
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                    <tr>
                                                        <td style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                            <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                Pipeline Title <span style="color: #E74C3C;">*</span>
                                                            </div>
                                                            <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                ' . htmlspecialchars($data['title']) . '
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- Pipeline Description -->
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                    <tr>
                                                        <td style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                            <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                Pipeline Description <span style="color: #E74C3C;">*</span>
                                                            </div>
                                                            <div style="font-size: 15px; color: #2C3E50; line-height: 1.6; white-space: pre-wrap;">
                                                                ' . htmlspecialchars($description) . '
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- Two Column Layout -->
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                    <tr>
                                                        <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                            <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                Created By
                                                            </div>
                                                            <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                ' . htmlspecialchars($creator['name']) . '
                                                            </div>
                                                        </td>
                                                        <td width="4%"></td>
                                                        <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                            <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                Discussion Lead <span style="color: #E74C3C;">*</span>
                                                            </div>
                                                            <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                ' . htmlspecialchars($lead['name']) . '
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                    <tr>
                                                        <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                            <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                Discussion Date <span style="color: #E74C3C;">*</span>
                                                            </div>
                                                            <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                ' . htmlspecialchars($discussion_date) . '
                                                            </div>
                                                        </td>
                                                        <td width="4%"></td>
                                                        <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                            <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                Source <span style="color: #E74C3C;">*</span>
                                                            </div>
                                                            <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                ' . htmlspecialchars($source_name) . '
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                    <tr>
                                                        <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                            <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                Source Owner <span style="color: #E74C3C;">*</span>
                                                            </div>
                                                            <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                ' . htmlspecialchars($source_owner) . '
                                                            </div>
                                                        </td>
                                                        <td width="4%"></td>
                                                        <td width="48%" valign="top" style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                            <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                Next Discussion Date & Time <span style="color: #E74C3C;">*</span>
                                                            </div>
                                                            <div style="font-size: 15px; color: #2C3E50; line-height: 1.6;">
                                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                    <tr>
                                                                        <td style="background-color: #ffffff; border: 1px solid #D1D5DB; border-radius: 4px; padding: 10px 12px; font-size: 14px; color: #2C3E50;">
                                                                            ' . htmlspecialchars($next_date) . '
                                                                        </td>
                                                                        <td width="10"></td>
                                                                        <td style="background-color: #ffffff; border: 1px solid #D1D5DB; border-radius: 4px; padding: 10px 12px; font-size: 14px; color: #2C3E50;">
                                                                            ' . htmlspecialchars($next_time) . '
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- Participants -->
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                                    <tr>
                                                        <td style="background-color: #F8F9FA; border: 1px solid #E1E4E8; border-radius: 6px; padding: 16px;">
                                                            <div style="font-size: 12px; font-weight: 600; color: #00475B; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                                                                Participants <span style="color: #E74C3C;">*</span>
                                                            </div>
                                                            <div style="margin-top: 8px;">
                                                                ' . implode(' ', array_map(function($name) {
                                                                    return '<span style="display: inline-block; background-color: #00475B; color: #ffffff; font-size: 13px; font-weight: 500; padding: 6px 14px; border-radius: 20px; margin: 4px 4px 4px 0;">' . htmlspecialchars($name) . '</span>';
                                                                }, $participant_names)) . '
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- Action Section -->
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 30px 0;">
                                                    <tr>
                                                        <td style="background-color: #E6F2FF; border-left: 4px solid #00475B; padding: 20px; border-radius: 4px;">
                                                            <p style="margin: 0 0 12px 0; font-size: 15px; color: #2C3E50;">
                                                                <strong>Next Steps:</strong> Please access the Project Tracker system to view complete pipeline details, add comments, and manage timeline milestones.
                                                            </p>
                                                            <a href="http://10.203.14.97/project_tracker_test/pipeline_details?id=' . $new_pipeline_id . '" style="display: inline-block; background-color: #00475B; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; margin-top: 8px;">
                                                                View Pipeline Details
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- Footer -->
                                        <tr>
                                            <td style="background-color: #F8F9FA; padding: 30px 40px; text-align: center; border-top: 1px solid #E1E4E8;">
                                                <p style="margin: 8px 0; font-size: 13px; color: #2C3E50; font-weight: 600;">
                                                    Project Tracker System
                                                </p>
                                                <p style="margin: 8px 0; font-size: 12px; color: #6B7280; line-height: 1.6;">
                                                    This is an automated notification. Please do not reply directly to this email.
                                                </p>
                                                <p style="margin: 8px 0; font-size: 12px; color: #6B7280; line-height: 1.6;">
                                                    For support, contact your system administrator.
                                                </p>
                                                <p style="margin: 8px 0; font-size: 12px; color: #6B7280;">
                                                    &copy; ' . date('Y') . ' Project Pipeline Tracker. All rights reserved.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </body>
                    </html>';
                    
                    $mail->Body = $emailBody;
                    
                    if (!$mail->send()) {
                        error_log('Failed to send pipeline creation email: ' . $mail->ErrorInfo);
                    } else {
                        error_log('Pipeline creation email sent successfully to: ' . implode(', ', $participant_emails));
                    }
                } catch (Exception $e) {
                    error_log('Failed to send pipeline creation email: ' . $e->getMessage());
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pipeline: ' . $pipeline_id,
                    'pipeline_id' => $pipeline_id,
                    'id' => $new_pipeline_id
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                $conn->query("SET @disable_triggers = NULL");
                throw $e;
            }
            break;

        case 'update_pipeline':
            $data = json_decode(file_get_contents('php://input'), true);
            
            error_log('update_pipeline input: ' . json_encode($data));
            
            $required = ['id', 'title', 'lead_id', 'source_name', 'source_owner', 'participants', 'discussion_date', 'next_date'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || ($field !== 'description' && empty($data[$field]))) {
                    throw new Exception("Required field missing: $field");
                }
            }
            
            if (!is_numeric($data['id']) || $data['id'] <= 0) {
                throw new Exception('Invalid Pipeline ID');
            }
            
            $sql = "SELECT id, created_by FROM pipelines WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for pipeline check: ' . $conn->error);
            }
            $stmt->bind_param('i', $data['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Pipeline not found');
            }
            
            $pipeline = $result->fetch_assoc();
            $created_by = $pipeline['created_by'];
            
            if (!is_numeric($data['lead_id']) || $data['lead_id'] <= 0) {
                throw new Exception('Invalid Lead ID');
            }
            
            $sql = "SELECT id FROM users WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for lead_id: ' . $conn->error);
            }
            $stmt->bind_param('i', $data['lead_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Invalid Lead ID');
            }
            
            if (!is_array($data['participants']) || empty($data['participants'])) {
                throw new Exception('At least one participant is required');
            }
            
            foreach ($data['participants'] as $participant_id) {
                if (!is_numeric($participant_id) || $participant_id <= 0) {
                    throw new Exception('Invalid Participant ID: ' . $participant_id);
                }
                $sql = "SELECT id FROM users WHERE id = ? AND is_active = 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $participant_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception('Invalid Participant ID: ' . $participant_id);
                }
            }
            
            if (!strtotime($data['discussion_date']) || !strtotime($data['next_date'])) {
                throw new Exception('Invalid date format');
            }
            
            $next_time = isset($data['next_time']) && $data['next_time'] !== '' ? $data['next_time'] : null;
            if ($next_time !== null && !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $next_time)) {
                throw new Exception('Invalid next_time format. Expected HH:mm');
            }
            
            $description = $data['description'] ?: '';
            $source_name = $data['source_name'];
            $source_owner = $data['source_owner'];
            $participants = implode(',', array_map('intval', $data['participants']));
            $lead_id = (int)$data['lead_id'];
            $pipeline_id = (int)$data['id'];
            
            $activity = "Update pipeline details";
            
            $conn->begin_transaction();
            
            try {
                $sql = "UPDATE pipelines SET 
                        title = ?,
                        description = ?,
                        lead_id = ?,
                        source_name = ?,
                        source_owner = ?,
                        discussion_date = ?,
                        next_date = ?,
                        next_time = ?,
                        participants = ?
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed for update: ' . $conn->error);
                }
                
                error_log('update_pipeline bind variables: ' . json_encode([
                    'title' => $data['title'],
                    'description' => $description,
                    'lead_id' => $lead_id,
                    'source_name' => $source_name,
                    'source_owner' => $source_owner,
                    'discussion_date' => $data['discussion_date'],
                    'next_date' => $data['next_date'],
                    'next_time' => $next_time,
                    'participants' => $participants,
                    'id' => $pipeline_id
                ]));
                
                $stmt->bind_param('ssissssssi', 
                    $data['title'],
                    $description,
                    $lead_id,
                    $source_name,
                    $source_owner,
                    $data['discussion_date'],
                    $data['next_date'],
                    $next_time,
                    $participants,
                    $pipeline_id
                );
                
                if (!$stmt->execute()) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }
                
                log_activity($conn, $created_by, $activity, 'success', getSecurity());
                
                $conn->commit();

                // Send update email with changes
                $changes = isset($data['changes']) ? $data['changes'] : [];
                sendPipelineActionEmail('update', $pipeline_id, ['changes' => $changes]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pipeline updated successfully',
                    'id' => $data['id']
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        case 'suspend_pipeline':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $pipeline_id = isset($data['id']) ? intval($data['id']) : null;
            if (!$pipeline_id) {
                throw new Exception('Pipeline ID is required');
            }
            
            $sql = "SELECT id, created_by FROM pipelines WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for pipeline check: ' . $conn->error);
            }
            $stmt->bind_param('i', $pipeline_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Pipeline not found');
            }
            
            $pipeline = $result->fetch_assoc();
            $created_by = $pipeline['created_by'];
            
            $activity = "Suspend pipeline details";
            
            $conn->begin_transaction();
            
            try {
                $sql = "UPDATE pipelines SET status = 'suspended' WHERE id = ? AND status = 'active'";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }
                
                $stmt->bind_param('i', $pipeline_id);
                if (!$stmt->execute()) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }
                
                if ($stmt->affected_rows === 0) {
                    throw new Exception('Pipeline not found or already closed/suspended');
                }
                
                log_activity($conn, $created_by, $activity, 'success', getSecurity());
                
                $conn->commit();

                // Send suspend email
                sendPipelineActionEmail('suspend', $pipeline_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pipeline suspended successfully'
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        case 'close_pipeline':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $pipeline_id = isset($data['id']) ? intval($data['id']) : null;
            if (!$pipeline_id) {
                throw new Exception('Pipeline ID is required');
            }
            
            $sql = "SELECT id, created_by FROM pipelines WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for pipeline check: ' . $conn->error);
            }
            $stmt->bind_param('i', $pipeline_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Pipeline not found');
            }
            
            $pipeline = $result->fetch_assoc();
            $created_by = $pipeline['created_by'];
            
            $activity = "Close pipeline details";
            
            $conn->begin_transaction();
            
            try {
                $sql = "UPDATE pipelines SET status = 'closed' WHERE id = ? AND status = 'active'";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }
                
                $stmt->bind_param('i', $pipeline_id);
                if (!$stmt->execute()) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }
                
                if ($stmt->affected_rows === 0) {
                    throw new Exception('Pipeline not found or already closed/suspended');
                }
                
                log_activity($conn, $created_by, $activity, 'success', getSecurity());
                
                $conn->commit();

                // Send close email
                sendPipelineActionEmail('close', $pipeline_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pipeline closed successfully'
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

            case 'get_discussions':
                $pipeline_id = isset($_GET['pipeline_id']) ? intval($_GET['pipeline_id']) : null;
                
                if (!$pipeline_id) {
                    throw new Exception('Pipeline ID is required');
                }
                
                $sql = "SELECT id, title, date, content, time 
                        FROM discussions 
                        WHERE pipeline_id = ? 
                        ORDER BY date DESC";  // This will now sort by datetime
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }
                
                $stmt->bind_param('i', $pipeline_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $discussions = [];
                while ($row = $result->fetch_assoc()) {
                    // Return the full datetime, don't strip the time
                    $discussions[] = $row;
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $discussions
                ]);
                break;

                case 'add_discussion':
                    $data = json_decode(file_get_contents('php://input'), true);
                    
                    error_log('add_discussion input: ' . json_encode($data));
                    
                    $required = ['pipeline_id', 'date', 'content'];
                    foreach ($required as $field) {
                        if (!isset($data[$field]) || empty($data[$field])) {
                            throw new Exception("Required field missing: $field");
                        }
                    }
                    
                    if (!is_numeric($data['pipeline_id']) || $data['pipeline_id'] <= 0) {
                        throw new Exception('Invalid Pipeline ID');
                    }
                    
                    // Check if this is a system log (to prevent duplicate emails)
                    $is_system_log = isset($data['is_system_log']) && $data['is_system_log'] === true;
                    
                    // Accept both date (YYYY-MM-DD) and datetime (YYYY-MM-DD HH:MM:SS) formats
                    $date_value = $data['date'];
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_value)) {
                        // It's just a date, add default time
                        $date_value .= ' 00:00:00';
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date_value)) {
                        // It's already datetime format, use as is
                    } else {
                        throw new Exception('Invalid date format. Expected YYYY-MM-DD or YYYY-MM-DD HH:MM:SS.');
                    }
                    
                    // Validate the datetime
                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $date_value);
                    if (!$datetime) {
                        throw new Exception('Invalid datetime value.');
                    }
                    
                    $sql = "SELECT id FROM pipelines WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception('Prepare failed for pipeline check: ' . $conn->error);
                    }
                    $stmt->bind_param('i', $data['pipeline_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        throw new Exception('Pipeline not found');
                    }
                    
                    $conn->begin_transaction();
                    
                    try {
                        $sql = "INSERT INTO discussions (pipeline_id, title, date, content, time) 
                                VALUES (?, ?, ?, ?, ?)";

                        // echo($data['time']); die();
                        
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            throw new Exception('Prepare failed for insert: ' . $conn->error);
                        }
                        
                        $title = isset($data['title']) && !empty($data['title']) ? $data['title'] : 'Discussion';
                        
                        $stmt->bind_param('issss', 
                            $data['pipeline_id'],
                            $title,
                            $date_value,  // Now using datetime value
                            $data['content'],
                            $data['time']
                        );
                        
                        if (!$stmt->execute()) {
                            throw new Exception('Execute failed: ' . $stmt->error);
                        }
                        
                        $new_discussion_id = $conn->insert_id;
                        
                        $conn->commit();
                
                        // ONLY send email if this is NOT a system log
                        // System logs are created when actions like update/close/suspend happen
                        // Those actions already send their own emails
                        if (!$is_system_log) {
                            error_log('Sending discussion email for user-created discussion');
                            // Send discussion email
                            sendPipelineActionEmail('discussion', $data['pipeline_id'], [
                                'content' => $data['content']
                            ]);
                        } else {
                            error_log('Skipping email for system log discussion');
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Discussion added successfully',
                            'data' => [
                                'id' => $new_discussion_id,
                                'pipeline_id' => $data['pipeline_id'],
                                'title' => $title,
                                'date' => $date_value,  // Return datetime
                                'content' => $data['content'],
                                'time'=> $data['time']
                            ]
                        ]);
                    } catch (Exception $e) {
                        $conn->rollback();
                        throw $e;
                    }
                    break;

        case 'get_users':
            $users = getUsers();
            echo json_encode([
                'success' => true,
                'data' => $users
            ]);
            break;

        default:
            throw new Exception('Invalid action specified');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error_code' => $e->getCode(),
        'error_line' => $e->getLine()
    ]);
}

$conn->close();

function getNextPipelineId() {
    global $conn;
    
    $sql = "SHOW TABLES LIKE 'pipelines'";
    $result = $conn->query($sql);
    if ($result === false || $result->num_rows === 0) {
        throw new Exception('Pipelines table does not exist');
    }
    
    $sql = "SELECT MAX(id) as max_id FROM pipelines";
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception('Failed to fetch max pipeline ID: ' . $conn->error);
    }
    
    $row = $result->fetch_assoc();
    return ($row['max_id'] ?? 0) + 1;
}

function getUsers() {
    global $conn;
    
    $users = [];
    $sql = "SELECT id, CONCAT(f_name, ' ', l_name) as name FROM users WHERE is_active = 1 ORDER BY name";
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception('Failed to fetch users: ' . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    return $users;
}
?>