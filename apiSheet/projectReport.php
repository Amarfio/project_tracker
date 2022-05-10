<?php

// header('Access-Control-Allow-Origin: *');
// header("Content-Type: application/json; charset=UTF-8");
// header('Access-Control-Allow-Methods: GET');
// header("Access-Control-Allow-Headers: X-Requested-With");
// header("Access-Control-Max-Age: 3600");
// header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); 
require_once 'connect.php';

// include_once 'dbConfig.php';

function filterData(&$str)
{

  $str = preg_replace("/\t/", "\\t", $str);
  $str = preg_replace("/\r?\n/", "\\n", $str);
  if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}

// Excel file name for download 
$fileName = "projectUpdatesReport" . date('Y-m-d') . ".xls";

// Column names 
$fields = array(
  'Project ID',
  'Project Name',
  'Owner',
  'Start Date',
  'End Date',
  'Completion Rate',
  'Status',
  'Last Update',
  'Last Update Date'
);

// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n";

// Fetch records from database 
$query = $db->query("SELECT * FROM `vw_projs_last_update` ORDER BY PID ASC;");
if ($query->num_rows > 0) {
  // Output each row of the data 
  while ($row = $query->fetch_assoc()) {
    // $status = ($row['status'] == 1)?'Active':'Inactive'; 
    $lineData = array(
      $row['PID'],
      $row['P_Name'],
      $row['Owner'],
      $row['start_date'],
      $row['end_date'],
      $row['department'],
      $row['percent_complete'],
      $row['status'],
      $row['last_update'],
      $row['last_update_date']
    );
    array_walk($lineData, 'filterData');
    $excelData .= implode("\t", array_values($lineData)) . "\n";
  }
} else {
  $excelData .= 'No records found...' . "\n";
}

// Headers for download 
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$fileName\"");

// Render excel data 
echo $excelData;

exit;