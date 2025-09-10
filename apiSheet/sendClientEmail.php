<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once 'mailer.php';
require_once 'connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// Function to get client name from external API
function getClientNameFromExternalAPI($client_id) {
    $api_url = 'https://issues.unionsg.com/js/getClients.php';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $clients = json_decode($response, true);
    
    if (is_array($clients)) {
        foreach ($clients as $client) {
            if (isset($client['Company_Id']) && $client['Company_Id'] == $client_id) {
                return $client['Company_Name'];
            }
        }
    }
    
    return null;
}

// Function to get user full name
function getUserFullName($user_id) {
    global $conn;
    
    if (!$user_id) return 'N/A';
    
    $sql = "SELECT CONCAT(f_name, ' ', l_name) as full_name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return 'N/A';
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['full_name'];
    }
    
    return 'N/A';
}

// Function to get change requests for a client
function getChangeRequestsForClient($client_id = null) {
    global $conn;
    
    try {
        // Calculate the start and end of the current week (Monday to Sunday)
        $start_of_week = (new DateTime())->modify('monday this week')->format('Y-m-d 00:00:00');
        $end_of_week = (new DateTime())->modify('sunday this week')->format('Y-m-d 23:59:59');
        
        $sql = "
            SELECT cr.*, 
                   cr.ticket_id,
                   CONCAT(u1.f_name, ' ', u1.l_name) as submitted_by_name,
                   CONCAT(u2.f_name, ' ', u2.l_name) as request_by_name,
                   CONCAT(u3.f_name, ' ', u3.l_name) as implementer_name,
                   cr.client_affected as client_name,
                   cr.qa_id, cr.dept_head_id
            FROM change_requests cr
            LEFT JOIN users u1 ON cr.submitted_by = u1.id
            LEFT JOIN users u2 ON cr.request_by = u2.id
            LEFT JOIN users u3 ON cr.implementer = u3.id
            WHERE cr.status = 'Implemented' AND cr.implementation_date BETWEEN ? AND ?
        ";
        
        $params = [$start_of_week, $end_of_week];
        $types = 'ss';
        
        if ($client_id !== null && $client_id !== '') {
            $client_name = getClientNameFromExternalAPI($client_id);
            if ($client_name) {
                $sql .= " AND cr.client_affected = ?";
                $params[] = $client_name;
                $types .= 's';
            }
        }
        
        $sql .= " ORDER BY cr.date_raised DESC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $requests = array();
        while ($row = $result->fetch_assoc()) {
            $row['date_raised_formatted'] = $row['date_raised'] ? date('M d, Y', strtotime($row['date_raised'])) : 'N/A';
            $row['implementation_date_formatted'] = $row['implementation_date'] ? date('M d, Y', strtotime($row['implementation_date'])) : 'N/A';
            $row['qa_name'] = $row['qa_id'] ? getUserFullName($row['qa_id']) : 'N/A';
            $row['dept_head_name'] = $row['dept_head_id'] ? getUserFullName($row['dept_head_id']) : 'N/A';
            $requests[] = $row;
        }
        
        return $requests;
    } catch (Exception $e) {
        throw new Exception("Database error: " . $e->getMessage());
    }
}

function generatePDF($requests, $client_name) {
    $pdf = new TCPDF('L', PDF_UNIT, 'A3', true, 'UTF-8', false);
    
    $pdf->SetCreator('Project Tracker');
    $pdf->SetAuthor('Project Tracker');
    $pdf->SetTitle('Change Requests for ' . $client_name);
    $pdf->SetSubject('Change Requests Report');
    
    $pdf->SetHeaderData('', 0, 'Change Requests for ' . $client_name, 'Generated on ' . date('Y-m-d H:i:s'));
    
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 7);
    
    $html = '<style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; word-wrap: break-word; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-col { width: 15%; }
        .short-col { width: 5%; }
        .impact-col { width: 10%; }
    </style>';
    
    $html .= '<table border="1" cellpadding="4">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th class="short-col">Change No.</th>';
    $html .= '<th class="short-col">Request No.</th>';
    $html .= '<th class="short-col">Type</th>';
    $html .= '<th class="short-col">Client Affected</th>';
    $html .= '<th class="short-col">Country</th>';
    $html .= '<th class="text-col">Description</th>';
    $html .= '<th class="short-col">Implementation Date</th>';
    $html .= '<th class="short-col">Service/Application</th>';
    $html .= '<th class="short-col">Risk</th>';
    $html .= '<th class="text-col">Implementation Plan</th>';
    $html .= '<th class="impact-col">Impact Assessment</th>';
    $html .= '<th class="short-col">Implementer</th>';
    $html .= '<th class="short-col">Tested By</th>';
    $html .= '<th class="short-col">Department Head</th>';
    $html .= '<th class="short-col">Status</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    
    foreach ($requests as $request) {
        $html .= '<tr>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['change_no']) . '</td>';
        $html .= '<td class="short-col">' . ($request['project_id'] ? 'PROJ-' . str_pad($request['project_id'], 8, '0', STR_PAD_LEFT) : ($request['ticket_id'] ? 'IN' . str_pad($request['ticket_id'], 8, '0', STR_PAD_LEFT) : 'N/A')) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['type']) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['client_name']) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['country']) . '</td>';
        $html .= '<td class="text-col">' . htmlspecialchars($request['description']) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['implementation_date_formatted']) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['service_application']) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['risk'] ? $request['risk'] : 'N/A') . '</td>';
        $html .= '<td class="text-col">' . htmlspecialchars($request['implementation_plan']) . '</td>';
        $html .= '<td class="impact-col">' . htmlspecialchars($request['impact_assessment']) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['implementer_name']) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['qa_name']) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['dept_head_name']) . '</td>';
        $html .= '<td class="short-col">' . htmlspecialchars($request['status']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    return $pdf->Output('', 'S');
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $required = ['emails', 'client_name'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Required field missing: $field");
        }
    }

    $emails = $data['emails'];
    $client_id = isset($data['client_id']) ? intval($data['client_id']) : null;
    $client_name = $data['client_name'];

    $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    foreach ($emails as $email) {
        if (!preg_match($emailRegex, $email)) {
            throw new Exception("Invalid email address: $email");
        }
    }

    $requests = getChangeRequestsForClient($client_id);

    $pdfContent = generatePDF($requests, $client_name);

    $mail->isHTML(true);
    $mail->setFrom('support24x7@unionsg.com', 'Project Tracker (USG)');
    $mail->Subject = "Change Requests for $client_name";
    $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #5e72e4; color: white; padding: 10px; text-align: center; }
        .content { padding: 20px; background-color: #f9fbfe; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Change Requests Report</h2>
        </div>
        <div class="content">
            <p>Dear Recipient,</p>
            <p>Please find attached the change requests report for $client_name, covering implemented requests for the current week (Monday to Sunday).</p>
            <p>Thank you,</p>
            <p>Project Tracker Team</p>
        </div>
    </div>
</body>
</html>
HTML;

    $mail->addStringAttachment($pdfContent, "Change_Requests_$client_name.pdf");

    $failed_emails = [];
    foreach ($emails as $email) {
        try {
            $mail->clearAddresses();
            $mail->addAddress($email);
            if (!$mail->send()) {
                $failed_emails[] = $email . ': ' . $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            $failed_emails[] = $email . ': ' . $e->getMessage();
        }
    }

    if (!empty($failed_emails)) {
        throw new Exception('Some emails failed to send: ' . implode(', ', $failed_emails));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Email sent successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>