<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set time zone to match database
date_default_timezone_set('UTC');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'connect.php';

try {
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get the action from request
    $action = isset($_GET['action']) ? $_GET['action'] : null;

    switch ($action) {
        case 'get_user_info':
            // Get user information including can_approve status
            $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
            
            if (!$user_id) {
                throw new Exception('User ID is required');
            }
            
            $sql = "SELECT id, f_name, l_name, username, gender, dept_id, profile_pic, can_approve 
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

        case 'get_requests':
            // Get all change requests with optional filtering
            $filter = isset($_GET['filter']) ? $_GET['filter'] : null;
            $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            
            $sql = "SELECT cr.*, 
                    CONCAT(u1.f_name, ' ', u1.l_name) as submitted_by_name,
                    CONCAT(u2.f_name, ' ', u2.l_name) as request_by_name,
                    CONCAT(u3.f_name, ' ', u3.l_name) as implementer_name,
                    cr.client_affected as client_name
                    FROM change_requests cr
                    LEFT JOIN users u1 ON cr.submitted_by = u1.id
                    LEFT JOIN users u2 ON cr.request_by = u2.id
                    LEFT JOIN users u3 ON cr.implementer = u3.id";
            
            $where = [];
            $params = [];
            $types = '';
            
            if ($filter === 'my_requests' && $user_id) {
                $where[] = "cr.submitted_by = ?";
                $params[] = $user_id;
                $types .= 'i';
            } elseif ($filter === 'my_approved' && $user_id) {
                $where[] = "(cr.dept_head_id = ? OR cr.qa_id = ? OR cr.ceo_id = ?)";
                $params[] = $user_id;
                $params[] = $user_id;
                $params[] = $user_id;
                $types .= 'iii';
            } elseif ($status) {
                $where[] = "cr.status = ?";
                $params[] = $status;
                $types .= 's';
            } elseif ($user_id) {
                $where[] = "(cr.submitted_by = ? OR cr.request_by = ? OR cr.implementer = ?)";
                $params[] = $user_id;
                $params[] = $user_id;
                $params[] = $user_id;
                $types .= 'iii';
            }
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " ORDER BY cr.date_submitted DESC";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $requests = [];
            while ($row = $result->fetch_assoc()) {
                // Format dates for display
                $row['date_submitted_formatted'] = date('M d, Y', strtotime($row['date_submitted']));
                $row['date_raised_formatted'] = date('M d, Y', strtotime($row['date_raised']));
                $row['implementation_date_formatted'] = date('M d, Y', strtotime($row['implementation_date']));
                
                // Format approval status
                $row['approval_status'] = [
                    'dept_head' => [
                        'approved' => $row['dept_head_approval'] === 'approved',
                        'name' => $row['dept_head_id'] ? getUserFullName($row['dept_head_id']) : null,
                        'date' => $row['dept_head_date'],
                        'comments' => $row['dept_head_comments']
                    ],
                    'qa' => [
                        'approved' => $row['qa_approval'] === 'approved',
                        'name' => $row['qa_id'] ? getUserFullName($row['qa_id']) : null,
                        'date' => $row['qa_date'],
                        'comments' => $row['qa_comments']
                    ],
                    'ceo' => [
                        'approved' => $row['ceo_approval'] === 'approved',
                        'name' => $row['ceo_id'] ? getUserFullName($row['ceo_id']) : null,
                        'date' => $row['ceo_date'],
                        'comments' => $row['ceo_comments']
                    ]
                ];
                
                $requests[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $requests
            ]);
            break;

        case 'get_request':
            // Get single change request by ID
            $request_id = isset($_GET['id']) ? intval($_GET['id']) : null;
            
            if (!$request_id) {
                throw new Exception('Request ID is required');
            }
            
            $sql = "SELECT cr.*, 
                    CONCAT(u1.f_name, ' ', u1.l_name) as submitted_by_name,
                    CONCAT(u2.f_name, ' ', u2.l_name) as request_by_name,
                    CONCAT(u3.f_name, ' ', u3.l_name) as implementer_name,
                    cr.client_affected as client_name
                    FROM change_requests cr
                    LEFT JOIN users u1 ON cr.submitted_by = u1.id
                    LEFT JOIN users u2 ON cr.request_by = u2.id
                    LEFT JOIN users u3 ON cr.implementer = u3.id
                    WHERE cr.id = ?";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('i', $request_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Change request not found');
            }
            
            $request = $result->fetch_assoc();
            
            // Format dates for display
            $request['date_submitted_formatted'] = date('M d, Y', strtotime($request['date_submitted']));
            $request['date_raised_formatted'] = date('M d, Y', strtotime($request['date_raised']));
            $request['implementation_date_formatted'] = date('M d, Y', strtotime($request['implementation_date']));
            
            // Format approval status
            $request['approval_status'] = [
                'dept_head' => [
                    'approved' => $request['dept_head_approval'] === 'approved',
                    'name' => $request['dept_head_id'] ? getUserFullName($request['dept_head_id']) : null,
                    'date' => $request['dept_head_date'],
                    'comments' => $request['dept_head_comments']
                ],
                'qa' => [
                    'approved' => $request['qa_approval'] === 'approved',
                    'name' => $request['qa_id'] ? getUserFullName($request['qa_id']) : null,
                    'date' => $request['qa_date'],
                    'comments' => $request['qa_comments']
                ],
                'ceo' => [
                    'approved' => $request['ceo_approval'] === 'approved',
                    'name' => $request['ceo_id'] ? getUserFullName($request['ceo_id']) : null,
                    'date' => $request['ceo_date'],
                    'comments' => $request['ceo_comments']
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $request
            ]);
            break;

        case 'create_request':
            // Create a new change request
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Log incoming data for debugging
            error_log('create_request input: ' . json_encode($data));
            
            // Validate required fields
            $required = ['type', 'client_affected', 'country', 'request_by', 'priority', 
                         'title', 'description', 'implementation_date', 'change_reason',
                         'impact_assessment', 'service_application', 'affected_artifacts',
                         'scope', 'implementation_plan', 'backout_plan', 'submitted_by', 'implementer'];
            
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    throw new Exception("Required field missing: $field");
                }
            }
            
            // Validate project_id or ticket_id (one must be provided, not both)
            if (!isset($data['project_id']) && !isset($data['ticket_id'])) {
                throw new Exception('Either Project ID or Ticket ID must be provided');
            }
            if (isset($data['project_id']) && isset($data['ticket_id'])) {
                throw new Exception('Cannot provide both Project ID and Ticket ID');
            }
            
            // Validate numeric fields
            if (isset($data['project_id']) && (!is_numeric($data['project_id']) || $data['project_id'] <= 0)) {
                throw new Exception('Invalid Project ID');
            }
            if (isset($data['ticket_id']) && (!is_numeric($data['ticket_id']) || $data['ticket_id'] <= 0)) {
                throw new Exception('Invalid Ticket ID');
            }
            if (!is_numeric($data['request_by']) || $data['request_by'] <= 0) {
                throw new Exception('Invalid Request By ID');
            }
            if (!is_numeric($data['submitted_by']) || $data['submitted_by'] <= 0) {
                throw new Exception('Invalid Submitted By ID');
            }
            if (!is_numeric($data['implementer']) || $data['implementer'] <= 0) {
                throw new Exception('Invalid Implementer ID');
            }
            
            // Verify project_id if provided
            if (isset($data['project_id'])) {
                $sql = "SELECT project_id FROM projects WHERE project_id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed for project_id: ' . $conn->error);
                }
                $stmt->bind_param('i', $data['project_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception('Invalid Project ID');
                }
            }
            
            // Verify ticket_id if provided
            if (isset($data['ticket_id'])) {
                // Fetch tickets from external API
                $ch = curl_init('https://issues.unionsg.com/js/getTickets.php');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                
                if ($response === false) {
                    curl_close($ch);
                    throw new Exception('Failed to fetch tickets from external API: ' . curl_error($ch));
                }
                
                $tickets = json_decode($response, true);
                curl_close($ch);
                
                if (!is_array($tickets)) {
                    throw new Exception('Invalid ticket data received from external API');
                }
                
                // Check if ticket_id exists in API response
                $formatted_ticket_id = 'IN' . str_pad($data['ticket_id'], 8, '0', STR_PAD_LEFT);
                $ticket_exists = false;
                foreach ($tickets as $ticket) {
                    if ($ticket['Ticket_Id'] === $formatted_ticket_id) {
                        $ticket_exists = true;
                        break;
                    }
                }
                
                if (!$ticket_exists) {
                    error_log('Invalid Ticket ID: ' . $data['ticket_id'] . ' (formatted as ' . $formatted_ticket_id . ')');
                    throw new Exception('Invalid Ticket ID');
                }
                
                // Convert ticket_id to integer for storage
                $data['ticket_id'] = (int)$data['ticket_id'];
            }
            
            // Verify request_by user exists
            $sql = "SELECT id FROM users WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for request_by: ' . $conn->error);
            }
            $stmt->bind_param('i', $data['request_by']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Invalid Request By ID');
            }
            
            // Verify submitted_by user exists
            $sql = "SELECT id FROM users WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for submitted_by: ' . $conn->error);
            }
            $stmt->bind_param('i', $data['submitted_by']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Invalid Submitted By ID');
            }
            
            // Verify implementer user exists
            $sql = "SELECT id FROM users WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for implementer: ' . $conn->error);
            }
            $stmt->bind_param('i', $data['implementer']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Invalid Implementer ID');
            }
            
            // Format time fields
            $start_time = !empty($data['start_time']) ? date('H:i:s', strtotime($data['start_time'])) : null;
            $end_time = !empty($data['end_time']) ? date('H:i:s', strtotime($data['end_time'])) : null;
            
            // Set optional fields to null if empty
            $emergency_reason = !empty($data['emergency_reason']) ? $data['emergency_reason'] : null;
            $budget = !empty($data['budget']) ? $data['budget'] : null;
            $risk = !empty($data['risk']) ? $data['risk'] : null;
            $resources_required = !empty($data['resources_required']) ? $data['resources_required'] : null;
            $comments = !empty($data['comments']) ? $data['comments'] : null;
            $project_id = isset($data['project_id']) ? $data['project_id'] : null;
            $ticket_id = isset($data['ticket_id']) ? $data['ticket_id'] : null;
            
            // Generate change_no
            $year_short = date('y');
            $change_no = 'CH' . $year_short . str_pad(getNextChangeId(), 6, '0', STR_PAD_LEFT);
            
            // Set status as a variable
            $status = 'Pending';
            
            $sql = "INSERT INTO change_requests (
                    project_id, ticket_id, change_no, type, emergency_reason, date_raised, 
                    client_affected, country, request_by, priority, title, 
                    description, implementation_date, start_time, end_time, 
                    change_reason, impact_assessment, service_application, 
                    affected_artifacts, scope, implementation_plan, budget, 
                    risk, backout_plan, resources_required, comments, 
                    submitted_by, implementer, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed for insert: ' . $conn->error);
            }
            
            $stmt->bind_param('iissssssisssssssssssssssssiis',    
                $project_id, 
                $ticket_id,
                $change_no, 
                $data['type'], 
                $emergency_reason, 
                $data['date_raised'],
                $data['client_affected'], 
                $data['country'], 
                $data['request_by'], 
                $data['priority'], 
                $data['title'],
                $data['description'], 
                $data['implementation_date'], 
                $start_time, 
                $end_time,
                $data['change_reason'], 
                $data['impact_assessment'], 
                $data['service_application'],
                $data['affected_artifacts'], 
                $data['scope'], 
                $data['implementation_plan'], 
                $budget,
                $risk,
                $data['backout_plan'], 
                $resources_required, 
                $comments,
                $data['submitted_by'],
                $data['implementer'],
                $status
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            
            $request_id = $stmt->insert_id;
            
            // Call sendApprovalEmails.php to notify approvers
            $email_data = [
                'request_id' => $request_id,
                'request_by' => $data['request_by'],
                'change_no' => $change_no,
                'project_id' => $data['project_id'] ?? null,
                'ticket_id' => $data['ticket_id'] ?? null,
                'title' => $data['title']
            ];
            $ch = curl_init('http://localhost/project_tracker_test/apiSheet/sendApprovalEmails.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($email_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $email_response = curl_exec($ch);
            
            if ($email_response === false) {
                $curl_error = curl_error($ch);
                curl_close($ch);
                error_log('cURL failed to send approval emails: ' . $curl_error);
                echo json_encode([
                    'success' => true,
                    'message' => 'Change request created successfully, but email sending failed: cURL error - ' . $curl_error,
                    'request_id' => $request_id,
                    'project_id' => $project_id,
                    'ticket_id' => $ticket_id,
                    'change_no' => $change_no
                ]);
                break;
            }
            
            $email_response_data = json_decode($email_response, true);
            curl_close($ch);
            
            if (!is_array($email_response_data) || !isset($email_response_data['success'])) {
                error_log('Invalid response from sendApprovalEmails.php: ' . $email_response);
                echo json_encode([
                    'success' => true,
                    'message' => 'Change request created successfully, but email sending failed: Invalid response from email service',
                    'request_id' => $request_id,
                    'project_id' => $project_id,
                    'ticket_id' => $ticket_id,
                    'change_no' => $change_no
                ]);
                break;
            }
            
            if (!$email_response_data['success']) {
                error_log('Failed to send approval emails: ' . ($email_response_data['message'] ?? 'No error message provided'));
                echo json_encode([
                    'success' => true,
                    'message' => 'Change request created successfully, but some emails failed: ' . ($email_response_data['message'] ?? 'Unknown error'),
                    'request_id' => $request_id,
                    'project_id' => $project_id,
                    'ticket_id' => $ticket_id,
                    'change_no' => $change_no
                ]);
                break;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Change request created successfully',
                'request_id' => $request_id,
                'project_id' => $project_id,
                'ticket_id' => $ticket_id,
                'change_no' => $change_no
            ]);
            break;

            case 'update_approval':
                // Update approval status for a change request
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Log incoming data for debugging
                error_log('update_approval input: ' . json_encode($data));
                
                // Validate required fields
                $required = ['request_id', 'approver_id', 'approval_level', 'status'];
                foreach ($required as $field) {
                    if (empty($data[$field])) {
                        throw new Exception("Required field missing: $field");
                    }
                }
                
                $request_id = intval($data['request_id']);
                $approver_id = intval($data['approver_id']);
                $approval_level = $data['approval_level'];
                $status = $data['status'];
                $comments = isset($data['comments']) ? $data['comments'] : null;
                
                // Validate inputs
                if (!in_array($approval_level, ['dept_head', 'qa', 'ceo'])) {
                    throw new Exception('Invalid approval level');
                }
                if (!in_array($status, ['approved', 'rejected'])) {
                    throw new Exception('Status must be either "approved" or "rejected"');
                }
                
                // Check if the user has already approved at another level and fetch current status
                $check_sql = "SELECT dept_head_id, qa_id, ceo_id, dept_head_approval, qa_approval, ceo_approval, status 
                              FROM change_requests WHERE id = ?";
                $check_stmt = $conn->prepare($check_sql);
                if (!$check_stmt) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }
                $check_stmt->bind_param('i', $request_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception('Change request not found');
                }
                
                $row = $result->fetch_assoc();
                
                // Prevent user from approving multiple levels
                if ($approval_level !== 'dept_head' && $row['dept_head_id'] == $approver_id && $row['dept_head_approval'] !== null) {
                    throw new Exception('User has already approved or rejected as Department Head');
                }
                if ($approval_level !== 'qa' && $row['qa_id'] == $approver_id && $row['qa_approval'] !== null) {
                    throw new Exception('User has already approved or rejected as Quality Assurance');
                }
                if ($approval_level !== 'ceo' && $row['ceo_id'] == $approver_id && $row['ceo_approval'] !== null) {
                    throw new Exception('User has already approved or rejected as CEO');
                }
                
                // Check if the current level has already been approved or rejected by another user
                $column_prefix = $approval_level . '_';
                if ($row[$column_prefix . 'id'] !== null && $row[$column_prefix . 'id'] != $approver_id && $row[$column_prefix . 'approval'] !== null) {
                    throw new Exception("The $approval_level level has already been " . $row[$column_prefix . 'approval'] . " by another user");
                }
                
                // Determine the new system status
                $system_status = isset($row['status']) && $row['status'] !== null ? $row['status'] : 'Pending';
                if ($status === 'rejected') {
                    $system_status = 'Rejected';
                } else {
                    // Fetch the latest approval statuses after the update
                    $temp_approvals = [
                        'dept_head' => $row['dept_head_approval'],
                        'qa' => $row['qa_approval'],
                        'ceo' => $row['ceo_approval']
                    ];
                    $temp_approvals[$approval_level] = $status; // Simulate the update for this level
                    
                    if ($temp_approvals['dept_head'] === 'approved' && 
                        $temp_approvals['qa'] === 'approved' && 
                        $temp_approvals['ceo'] === 'approved') {
                        $system_status = 'Approved';
                    } else {
                        $system_status = 'Pending';
                    }
                }
                
                // Build the update query
                $sql = "UPDATE change_requests SET 
                        {$column_prefix}id = ?,
                        {$column_prefix}approval = ?,
                        {$column_prefix}date = NOW(),
                        {$column_prefix}comments = ?,
                        status = ?
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }
                
                $stmt->bind_param('isssi', 
                    $approver_id,
                    $status,
                    $comments,
                    $system_status,
                    $request_id
                );
                
                if (!$stmt->execute()) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }
                
                // Verify the update
                $verify_sql = "SELECT {$column_prefix}id, {$column_prefix}approval, {$column_prefix}date, 
                               {$column_prefix}comments, status 
                               FROM change_requests WHERE id = ?";
                $verify_stmt = $conn->prepare($verify_sql);
                if (!$verify_stmt) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }
                $verify_stmt->bind_param('i', $request_id);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                $updated_data = $verify_result->fetch_assoc();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Approval status updated successfully',
                    'data' => [
                        'approval_level' => $approval_level,
                        'approver_id' => $updated_data[$column_prefix . 'id'],
                        'approval_status' => $updated_data[$column_prefix . 'approval'],
                        'approval_date' => $updated_data[$column_prefix . 'date'],
                        'comments' => $updated_data[$column_prefix . 'comments'],
                        'system_status' => $updated_data['status']
                    ]
                ]);
                break;

        case 'update_implementation':
            // Update implementation status
            $data = json_decode(file_get_contents('php://input'), true);
            
            $required = ['request_id', 'implementation_status'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Required field missing: $field");
                }
            }
            
            $request_id = intval($data['request_id']);
            $implementation_status = $data['implementation_status'];
            $implementation_start = isset($data['implementation_start']) ? $data['implementation_start'] : null;
            $implementation_end = isset($data['implementation_end']) ? $data['implementation_end'] : null;
            $implementation_notes = isset($data['implementation_notes']) ? $data['implementation_notes'] : null;
            
            // Validate implementation status
            if (!in_array($implementation_status, ['Successful', 'Failed', 'Rescheduled'])) {
                throw new Exception('Invalid implementation status');
            }
            
            // Format time fields if provided
            if ($implementation_start && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $implementation_start)) {
                $implementation_start = date('H:i:s', strtotime($implementation_start));
            }
            if ($implementation_end && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $implementation_end)) {
                $implementation_end = date('H:i:s', strtotime($implementation_end));
            }
            
            $sql = "UPDATE change_requests SET 
                    implementation_status = ?,
                    implementation_start = ?,
                    implementation_end = ?,
                    implementation_notes = ?,
                    status = CASE 
                        WHEN ? = 'Successful' THEN 'Implemented'
                        WHEN ? = 'Failed' THEN 'Failed'
                        WHEN ? = 'Rescheduled' THEN 'Rescheduled'
                        ELSE status
                    END
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('sssssssi', 
                $implementation_status, 
                $implementation_start, 
                $implementation_end, 
                $implementation_notes,
                $implementation_status,
                $implementation_status,
                $implementation_status,
                $request_id
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            
            // Fetch the updated fields to return in response
            $verify_sql = "SELECT implementation_status, implementation_start, implementation_end, implementation_notes, status 
                           FROM change_requests 
                           WHERE id = ?";
            $verify_stmt = $conn->prepare($verify_sql);
            if (!$verify_stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $verify_stmt->bind_param('i', $request_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $updated_data = $verify_result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => 'Implementation status updated successfully',
                'data' => [
                    'implementation_status' => $updated_data['implementation_status'],
                    'implementation_start' => $updated_data['implementation_start'],
                    'implementation_end' => $updated_data['implementation_end'],
                    'implementation_notes' => $updated_data['implementation_notes'],
                    'status' => $updated_data['status']
                ]
            ]);
            break;

        case 'get_reference_data':
            // Get reference data for dropdowns
            $reference_data = [
                'types' => ['Emergency', 'Normal'],
                'risk_levels' => ['High', 'Medium', 'Low'],
                'priorities' => ['Low', 'Medium', 'High'],
                'statuses' => ['Pending', 'Approved', 'Rejected', 'Implemented', 'Failed', 'Rescheduled'],
                'implementation_statuses' => ['Successful', 'Failed', 'Rescheduled'],
                'services' => getServices(),
                'artifacts' => getArtifacts(),
                'countries' => getCountries(),
                'approvers' => [
                    'dept_head' => getDepartmentHeads(),
                    'qa' => getQAPersonnel(),
                    'ceo' => getCEOPersonnel()
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $reference_data
            ]);
            break;

        case 'get_countries':
            // Get all active countries
            $countries = getCountries();
            echo json_encode([
                'success' => true,
                'data' => $countries
            ]);
            break;

        case 'get_users':
            // Get all active users
            $users = getUsers();
            echo json_encode([
                'success' => true,
                'data' => $users
            ]);
            break;

        case 'get_clients':
            // Get all active clients
            $clients = getClients();
            echo json_encode([
                'success' => true,
                'data' => $clients
            ]);
            break;

        case 'get_projects':
            // Get all projects
            $projects = getProjects();
            echo json_encode([
                'success' => true,
                'data' => $projects
            ]);
            break;

        case 'get_tickets':
            // Get all tickets from external API
            $ch = curl_init('https://issues.unionsg.com/js/getTickets.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            
            if ($response === false) {
                curl_close($ch);
                throw new Exception('Failed to fetch tickets from external API: ' . curl_error($ch));
            }
            
            $tickets = json_decode($response, true);
            curl_close($ch);
            
            if (!is_array($tickets)) {
                throw new Exception('Invalid ticket data received from external API');
            }
            
            $formatted_tickets = array_map(function($ticket) {
                $numeric_id = (int)preg_replace('/^IN/', '', $ticket['Ticket_Id']);
                return [
                    'id' => $numeric_id,
                    'formatted_id' => $ticket['Ticket_Id']
                ];
            }, $tickets);
            
            echo json_encode([
                'success' => true,
                'data' => $formatted_tickets
            ]);
            break;

        default:
            throw new Exception('Invalid action specified');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();

// Helper function to get the next change ID
function getNextChangeId() {
    global $conn;
    
    $sql = "SELECT MAX(id) as max_id FROM change_requests";
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['max_id'] + 1;
    }
    
    return 1;
}

// Helper function to get user full name
function getUserFullName($user_id) {
    global $conn;
    
    $sql = "SELECT CONCAT(f_name, ' ', l_name) as full_name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['full_name'];
    }
    
    return null;
}

// Helper function to get services
function getServices() {
    global $conn;
    
    $services = [];
    $sql = "SELECT service_name FROM change_request_services WHERE is_active = 1";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row['service_name'];
        }
    }
    
    return $services;
}

// Helper function to get artifacts
function getArtifacts() {
    global $conn;
    
    $artifacts = [];
    $sql = "SELECT artifact_name FROM change_request_artifacts WHERE is_active = 1";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $artifacts[] = $row['artifact_name'];
        }
    }
    
    return $artifacts;
}

// Helper function to get countries
function getCountries() {
    global $conn;
    
    $country_ids = [65, 87, 154, 97]; // Ghana, Kenya, Sierra Leone, Liberia
    
    $placeholders = implode(',', array_fill(0, count($country_ids), '?'));
    $sql = "SELECT id, name FROM countries WHERE id IN ($placeholders) ORDER BY name";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $types = str_repeat('i', count($country_ids));
    $stmt->bind_param($types, ...$country_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $countries = [];
    while ($row = $result->fetch_assoc()) {
        $countries[] = $row['name'];
    }
    
    return $countries;
}

// Helper function to get department heads
function getDepartmentHeads() {
    global $conn;
    
    $heads = [];
    $sql = "SELECT id, CONCAT(f_name, ' ', l_name) as name FROM users WHERE is_dpt_head = 1 AND is_active = 1";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $heads[] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
    }
    
    return $heads;
}

// Helper function to get QA personnel
function getQAPersonnel() {
    global $conn;
    
    $personnel = [];
    $sql = "SELECT id, CONCAT(f_name, ' ', l_name) as name 
            FROM users 
            WHERE dept IN (108, 135, 138) AND is_active = 1 
            ORDER BY name";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $personnel[] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
    }
    
    return $personnel;
}

// Helper function to get CEO personnel
function getCEOPersonnel() {
    global $conn;
    
    $personnel = [];
    $sql = "SELECT id, CONCAT(f_name, ' ', l_name) as name 
            FROM users 
            WHERE id IN (145, 147, 151, 138, 196) AND is_active = 1 
            ORDER BY name";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $personnel[] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
    }
    
    return $personnel;
}

// Helper function to get all active users
function getUsers() {
    global $conn;
    
    $users = [];
    $sql = "SELECT id, CONCAT(f_name, ' ', l_name) as name FROM users WHERE is_active = 1 ORDER BY name";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
    }
    
    return $users;
}

// Helper function to get all active clients
function getClients() {
    global $conn;
    
    $clients = [];
    $sql = "SELECT client_id as id, name FROM clients WHERE is_active = 1 ORDER BY name";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $clients[] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
    }
    
    return $clients;
}

// Helper function to get all projects
function getProjects() {
    global $conn;
    
    $projects = [];
    $sql = "SELECT project_id as id FROM projects ORDER BY project_id";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $projects[] = [
                'id' => $row['id']
            ];
        }
    }
    
    return $projects;
}
?>