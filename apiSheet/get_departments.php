<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production

require_once 'connect.php';

try {
    // Check if connection is valid
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $specific_departments = [
        'Credits',
        'Enterprise Solutions',
        'Project Management',
        'Mobile Operations',
        'Mobile Technologies',
        'Operation',
        'Systems',
        'Treasury/Payment'
    ];

    // Use a simpler query to avoid bind_param issues
    $placeholders = implode("','", array_map([$conn, 'real_escape_string'], $specific_departments));
    $sql = "SELECT * FROM code_desc WHERE init = 'dpt' AND `desc` IN ('$placeholders')";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }

    echo json_encode([
        'success' => true,
        'departments' => $departments
    ]);

    $result->free();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>