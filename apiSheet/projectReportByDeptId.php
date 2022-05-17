<?php

require_once 'connect.php';

$_GET['dept_id']= 106;

$output = "";

// echo($data->dept_id); die();
if(isset($_GET['dept_id'])){

  
    //get the department id 
    // $dept_id = mysqli_escape_string($conn, $_GET['dept_id']);
    $dept_id = 106;
    
    // print_r($dept_id); die();
    $query = "SELECT project_id, project_name, start_date, end_date, department, completion, client, owner, status, last_update, last_update_date FROM `vw_proj_update_by_dept` WHERE dept_id = '$dept_id'";

    $result = mysqli_query($conn, $query);

    // echo json_encode($result); die();
    // $i = 1;

    
    $num = mysqli_num_rows($result);
    // echo json_encode($num); die();
    if($num > 0){
      // json_encode(print_r($result));
      $output .='
                <table class="table" bordered = "1">
                <tr>
                  <th>Project ID</th>
                  <th>Project Name</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Department</th>
                  <th>Completion Rate</th>
                  <th>Client</th>
                  <th>Owner</th>
                  <th>Status</th>
                  <th>Last Update</th>
                  <th>Last Update Date</th>
                </tr>
      ';

      while($row = mysqli_fetch_array($result)){

        $output .= '<tr>
                    <td>'.$row["project_id"].'</td>
                    <td>'.$row["project_name"].'</td>
                    <td>'.$row["start_date"].'</td>
                    <td>'.$row["end_date"].'</td>
                    <td>'.$row["department"].'</td>
                    <td>'.$row["completion"].'</td>
                    <td>'.$row["client"].'</td>
                    <td>'.$row["owner"].'</td>
                    <td>'.$row["status"].'</td>
                    <td>'.$row["last_update"].'</td>
                    <td>'.$row["last_update_date"].'</td>
                   </tr>';

        $outupt .='</table>';

        header('Content-Type: application/xls');
        header('Content-Disposition: attachment; filename=projectUpateReport.xls');
        echo $output;
      }
    }
    else{ 

      echo ("no data available");
    }


}

?>