<?php 
// Load the database configuration file 
// include_once 'dbConfig.php'; 
// Headers for download 
header("Content-Type: application/vnd.ms-excel"); 
header("Content-Disposition: attachment; filename=\"$fileName\""); 

$dbHost     = "localhost"; 
$dbUsername = "root"; 
$dbPassword = ""; 
$dbName     = "project_tracker_db"; 
 
// Create database connection 
$db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

if(isset($_GET['status_id'])){

    // $department_id = $_GET['dept_id'];
    // $status_id = $_GET['status_id'];
 
    $department_id = 106;
    $status_id = 83;
 
    
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
} 
 
//status description
$status_name = "";
 
// Column names 
$fields = array('Project ID', 'Version', 'Description', 'Owner', 'Status', 'Start Date', 'End Date', 'Age(days)', 'Completion', 'Department'); 
 
// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n"; 
 
// Fetch records from database 

$query = $db->query("SELECT * FROM `vw_excel_projects_download` WHERE status = '$status_id' AND dept_id = '$department_id'"); 
if($query->num_rows > 0){ 
    // Output each row of the data 
    while($row = $query->fetch_assoc()){ 
        // $status = ($row['status'] == 1)?'Active':'Inactive'; 
        $status_name = $row['status_desc'];
        $lineData = array($row['project_id'], $row['version_no'], $row['description'], $row['m_o'], $row['status_desc'], $row['start_date'], $row['end_date'], $row['no_of_days'], $row['completion'], $row['department']); 
        array_walk($lineData, 'filterData'); 
        $excelData .= implode("\t", array_values($lineData)) . "\n"; 
    } 
}else{ 
    $excelData .= 'No records found...'. "\n"; 
}

// Excel file name for download 
$fileName = "project-".$status_name. date('Y-m-d') . ".xls"; 
 

 
// Render excel data 
echo $excelData; 
 
exit;
}