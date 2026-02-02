<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once 'connect.php';
require_once 'mailer.php';
require_once 'functions/passwordResetTemplate.php'; // Optional: for HTML email template, or create a new one

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set time zone to match database
date_default_timezone_set('UTC');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['request_id', 'request_by', 'change_no', 'project_id', 'title'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Required field missing: $field");
        }
    }

    $request_id = intval($data['request_id']);
    $request_by = intval($data['request_by']);
    $change_no = $data['change_no'];
    $project_id = intval($data['project_id']);
    $title = $data['title'];

    // Get requester's department
    $sql = "SELECT dept, CONCAT(f_name, ' ', l_name) AS full_name FROM users WHERE id = ? AND is_active = 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('i', $request_by);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Requester not found or inactive');
    }
    $requester = $result->fetch_assoc();
    $dept = $requester['dept'];
    $requester_name = $requester['full_name'];

    // Initialize arrays for approvers
    $dept_head_emails = [];
    $qa_emails = [];
    $ceo_emails = [];

    // Get Department Head emails (can_approve = 1, same dept)
    $sql = "SELECT email, CONCAT(f_name, ' ', l_name) AS full_name 
            FROM users 
            WHERE dept = ? AND can_approve = 1 AND is_active = 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('i', $dept);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $dept_head_emails[] = [
            'email' => $row['email'],
            'name' => $row['full_name']
        ];
    }

    // Get QA emails (can_approve = 1, dept = 138)
    $qa_dept = 138;
    $sql = "SELECT email, CONCAT(f_name, ' ', l_name) AS full_name 
            FROM users 
            WHERE dept = ? AND can_approve = 1 AND is_active = 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param('i', $qa_dept);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $qa_emails[] = [
            'email' => $row['email'],
            'name' => $row['full_name']
        ];
    }

    // Get CEO/Senior Staff emails (specific IDs)
    $ceo_ids = [196, 138, 144, 145, 147, 151];
    $placeholders = implode(',', array_fill(0, count($ceo_ids), '?'));
    $sql = "SELECT email, CONCAT(f_name, ' ', l_name) AS full_name 
            FROM users 
            WHERE id IN ($placeholders) AND can_approve = 1 AND is_active = 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param(str_repeat('i', count($ceo_ids)), ...$ceo_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ceo_emails[] = [
            'email' => $row['email'],
            'name' => $row['full_name']
        ];
    }

    // Prepare email content
    $approval_link = "http://10.203.14.97/project_tracker/change_request?requestId=$request_id";
    $subject = "Pending Approval: Change Request $change_no";
    
    // Function to generate HTML email body
    function getApprovalEmailBody($recipient_name, $change_no, $project_id, $title, $requester_name, $approval_link) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #5e72e4; color: white; padding: 10px; text-align: center; }
        .content { padding: 20px; background-color: #f9fbfe; }
        .button { display: inline-block; padding: 10px 20px; background-color: #5e72e4; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Pending Approval Notification</h2>
        </div>
        <div class="content">
            <p>Dear $recipient_name,</p>
            <p>A new change request requires your approval:</p>
            <ul>
                <li><strong>Change Number:</strong> $change_no</li>
                <li><strong>Project ID:</strong> PROJ-" . str_pad($project_id, 8, '0', STR_PAD_LEFT) . "</li>
                <li><strong>Title:</strong> $title</li>
                <li><strong>Requested By:</strong> $requester_name</li>
            </ul>
            <p>Please review and approve/reject the request at the following link:</p>
            <p><a href="$approval_link" class="button">Review Change Request</a></p>
            <p>Thank you,</p>
            <p>Project Tracker Team</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    // Send emails to each group
    $mail->isHTML(true);
    $mail->setFrom('support24x7@unionsg.com', 'Project Tracker (USG)');
    $failed_emails = [];

    // Send to Department Heads
    foreach ($dept_head_emails as $recipient) {
        try {
            $mail->clearAddresses();
            $mail->addAddress($recipient['email'], $recipient['name']);
            $mail->Subject = $subject;
            $mail->Body = getApprovalEmailBody($recipient['name'], $change_no, $project_id, $title, $requester_name, $approval_link);
            if (!$mail->send()) {
                $failed_emails[] = $recipient['email'] . ': ' . $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            $failed_emails[] = $recipient['email'] . ': ' . $e->getMessage();
        }
    }

    // Send to QA Personnel
    foreach ($qa_emails as $recipient) {
        try {
            $mail->clearAddresses();
            $mail->addAddress($recipient['email'], $recipient['name']);
            $mail->Subject = $subject;
            $mail->Body = getApprovalEmailBody($recipient['name'], $change_no, $project_id, $title, $requester_name, $approval_link);
            if (!$mail->send()) {
                $failed_emails[] = $recipient['email'] . ': ' . $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            $failed_emails[] = $recipient['email'] . ': ' . $e->getMessage();
        }
    }

    // Send to CEO/Senior Staff
    foreach ($ceo_emails as $recipient) {
        try {
            $mail->clearAddresses();
            $mail->addAddress($recipient['email'], $recipient['name']);
            $mail->Subject = $subject;
            $mail->Body = getApprovalEmailBody($recipient['name'], $change_no, $project_id, $title, $requester_name, $approval_link);
            if (!$mail->send()) {
                $failed_emails[] = $recipient['email'] . ': ' . $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            $failed_emails[] = $recipient['email'] . ': ' . $e->getMessage();
        }
    }

    // Log any failed emails (optional: write to a log file)
    if (!empty($failed_emails)) {
        error_log("Failed to send emails: " . implode(', ', $failed_emails));
        echo json_encode([
            'success' => false,
            'message' => 'Some emails failed to send: ' . implode(', ', $failed_emails)
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Approval emails sent successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>