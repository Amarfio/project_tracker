<?php
    ////////////////////////////////////////////////////////////////////////////////////
    $conn = new mysqli('localhost', 'root', 'firefox');
    mysqli_select_db($conn, 'project_tracker_db');

    $filename= "updatedProjects".date("d M Y");

  if(isset($_GET['dept_id'])){

    $dept_id = $_GET['dept_id'];

    $setSql = "SELECT project_id, project_name, owner, start_date, end_date, completion, status, last_update, last_update_date FROM `vw_proj_update_by_dept` WHERE dept_id = $dept_id ORDER BY last_update_date DESC";
    $setRec = mysqli_query($conn, $setSql);
    // echo json_encode($setRec); die();


    $columnHeader = "Project ID"."\t"."Project Name"."\t"."Owner"."\t"."Start Date"."\t"."End Date"."\t"."Completion Rate"."\t"."Status"."\t"."Last Update"."\t"."Last Update Date";

    $setData = '';

    while ($rec = mysqli_fetch_row($setRec)) {  
        $rowData = '';
        foreach ($rec as $value) {
            $value = '"' . $value . '"' . "\t";  
            $rowData .= $value;  
        }  
        $setData .= trim($rowData) . "\n";  
    }  



    header("Content-type: application/octet-stream");  
    header("Content-Disposition: attachment; filename=$filename.xls");  
    header("Pragma: no-cache");
    header("Expires: 0");  

    echo ucwords($columnHeader) . "\n" . $setData . "\n";
    // sleep(5);
  }

?>