<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'connect.php';

// Log function for debugging
function debug_log($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, 'api_debug.log');
}

try {
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $user_id = isset($_GET['id']) ? $_GET['id'] : null;

    debug_log("Request received: user_id=$user_id");

    if (!$user_id) {
        throw new Exception('User ID is required');
    }

    // Validate user_id
    if (!is_numeric($user_id)) {
        throw new Exception('Invalid user ID');
    }

    // Query user name
    $sql = "SELECT CONCAT(f_name, ' ', l_name) AS user_name 
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

    $row = $result->fetch_assoc();
    $response = [
        'success' => true,
        'user_name' => $row['user_name'] ?: 'Unknown User'
    ];

    debug_log("User data fetched: user_name=" . $row['user_name']);

    echo json_encode($response);

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    $error_message = 'Error: ' . $e->getMessage();
    echo json_encode([
        'success' => false,
        'message' => $error_message
    ]);
    debug_log($error_message);
}

$conn->close();
?>