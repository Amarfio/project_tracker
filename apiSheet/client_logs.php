<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_client_requests':
        getClientRequests();
        break;
    default:
        echo json_encode(array("success" => false, "message" => "Invalid action"));
        break;
}

function getClientRequests() {
    global $conn;
    
    // Get pagination and client ID parameters
    $client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : null;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;
    
    try {
        // Calculate the start and end of the current week (Monday to Sunday)
        $today = new DateTime();
        $start_of_week = (new DateTime())->modify('monday this week')->format('Y-m-d 00:00:00');
        $end_of_week = (new DateTime())->modify('sunday this week')->format('Y-m-d 23:59:59');
        
        // Build the base query, including ticket_id
        $sql = "
            SELECT cr.*, 
                   cr.ticket_id,
                   CONCAT(u1.f_name, ' ', u1.l_name) as submitted_by_name,
                   CONCAT(u2.f_name, ' ', u2.l_name) as request_by_name,
                   CONCAT(u3.f_name, ' ', u3.l_name) as implementer_name,
                   cr.client_affected as client_name
            FROM change_requests cr
            LEFT JOIN users u1 ON cr.submitted_by = u1.id
            LEFT JOIN users u2 ON cr.request_by = u2.id
            LEFT JOIN users u3 ON cr.implementer = u3.id
        ";
        
        // Build WHERE clause
        $where = "WHERE cr.status = 'Implemented' AND cr.implementation_date BETWEEN ? AND ?";
        $params = [$start_of_week, $end_of_week];
        $types = 'ss';
        
        if ($client_id !== null && $client_id !== '') {
            // Get the client name from the external API
            $client_name = getClientNameFromExternalAPI($client_id);
            if ($client_name) {
                $where .= " AND cr.client_affected = ?";
                $params[] = $client_name;
                $types .= 's';
            } else {
                // If client not found, return empty results
                echo json_encode(array(
                    "success" => true,
                    "data" => array(
                        "requests" => [],
                        "total" => 0,
                        "total_pages" => 0,
                        "current_page" => $page
                    )
                ));
                return;
            }
        }
        
        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM change_requests cr $where";
        
        $stmt = $conn->prepare($count_sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];
        $total_pages = ceil($total / $limit);
        
        // Get paginated results
        $sql .= $where;
        $sql .= " ORDER BY cr.date_raised DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
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
            // Format dates for display
            $row['date_raised_formatted'] = $row['date_raised'] ? date('M d, Y', strtotime($row['date_raised'])) : 'N/A';
            $row['implementation_date_formatted'] = $row['implementation_date'] ? date('M d, Y', strtotime($row['implementation_date'])) : 'N/A';
            
            // Format approval status
            $row['approval_status'] = array(
                'dept_head' => array(
                    'approved' => $row['dept_head_approval'] === 'approved',
                    'name' => $row['dept_head_id'] ? getUserFullName($row['dept_head_id']) : null,
                    'date' => $row['dept_head_date'],
                    'comments' => $row['dept_head_comments']
                ),
                'qa' => array(
                    'approved' => $row['qa_approval'] === 'approved',
                    'name' => $row['qa_id'] ? getUserFullName($row['qa_id']) : null,
                    'date' => $row['qa_date'],
                    'comments' => $row['qa_comments']
                ),
                'ceo' => array(
                    'approved' => $row['ceo_approval'] === 'approved',
                    'name' => $row['ceo_id'] ? getUserFullName($row['ceo_id']) : null,
                    'date' => $row['ceo_date'],
                    'comments' => $row['ceo_comments']
                )
            );
            
            $requests[] = $row;
        }
        
        echo json_encode(array(
            "success" => true,
            "data" => array(
                "requests" => $requests,
                "total" => $total,
                "total_pages" => $total_pages,
                "current_page" => $page
            )
        ));
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(array("success" => false, "message" => "Database error: " . $e->getMessage()));
    }
}

// Get client name from external API
function getClientNameFromExternalAPI($client_id) {
    // Call the external API to get the client name
    $api_url = 'https://issues.unionsg.com/js/getClients.php';
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Execute the request
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Parse the response
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

// Helper function to get user full name
function getUserFullName($user_id) {
    global $conn;
    
    if (!$user_id) return null;
    
    $sql = "SELECT CONCAT(f_name, ' ', l_name) as full_name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['full_name'];
    }
    
    return null;
}
?>