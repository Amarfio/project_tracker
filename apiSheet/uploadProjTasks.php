<?php
// Include PhpSpreadsheet library
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Function to connect to the database
function connectDB() {
    $host = 'localhost'; // Change this to your host
    $dbname = 'project_tracker_db'; // Change this to your database name
    $username = 'root'; // Change this to your database username
    $password = ''; // Change this to your database password

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Function to load Excel data into database
function loadExcelData($filePath) {
    // Connect to the database
    $conn = connectDB();

    try {
        // Load Excel file
        $spreadsheet = IOFactory::load($filePath);
        
        // Get the first worksheet
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Iterate over rows
        foreach ($worksheet->getRowIterator() as $row) {
            // Skip header row (if needed)
            if ($row->getRowIndex() == 1) {
                continue;
            }
            
            // Extract data from the row
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }
            
            // Insert data into database
            $stmt = $conn->prepare("INSERT INTO tasks_new (column1, column2, column3) VALUES (?, ?, ?)");
            $stmt->execute($rowData); // Assuming your Excel data maps directly to your database columns
        }
        
        echo "Data loaded successfully.";
    } catch (Exception $e) {
        echo "Error loading data: " . $e->getMessage();
    }
}

// Usage example
$excelFilePath = 'path_to_your_excel_file.xlsx'; // Change this to the path of your Excel file
loadExcelData($excelFilePath);
?>