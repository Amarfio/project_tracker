<?php 
// Load the database configuration file 
require_once 'connect.php'; 
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
} 

//get the department id
// if(isset($_GET['dept_id'])){

//     //get the department id 
//     $dept_id = $_GET['dept_id'];
// }
// Excel file name for download 
$fileName = "project-last-update_" . date('Y-m-d') . ".xls"; 
 
// Column names 
$fields = array('Project ID', 'Project Name', 'Owner', 'Start Date', 'End Date', 'Completion Rate', 'Status', 'Last Update', 'Last Update Date'); 
 
// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n"; 
 
// Fetch records from database 
$query = $db->query("SELECT project_id, project_name, start_date, end_date, department, completion, client, owner, status, last_update, last_update_date FROM `vw_proj_update_by_dept` WHERE dept_id = 104"); 
if($query->num_rows > 0){ 
    // Output each row of the data 
    while($row = $query->fetch_assoc()){ 
        // $status = ($row['status'] == 1)?'Active':'Inactive';
        $lineData = array($row['project_id'], $row['project_name'], $row['start_date'], $row['end_date'], $row['department'], $row['completion'], $row['client'], $row['owner'], $row['status'], $row['last_update'], $row['last_update_date']); 
        array_walk($lineData, 'filterData'); 
        $excelData .= implode("\t", array_values($lineData)) . "\n"; 
    } 
}else{ 
    $excelData .= 'No records found...'. "\n"; 
} 
 
// Headers for download 
header("Content-Type: application/vnd.ms-excel"); 
header("Content-Disposition: attachment; filename=projectUpateReport.xls"); 
 
// Render excel data 
echo $excelData; 
 
exit;