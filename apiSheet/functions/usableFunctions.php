<?php 
//code to check for the completed date of a project, it checks if the column is empty then picks that of the date task was updated using the % completion of the project
function checkForCompletionDate($conn, $projectId){
    $dateOfCompletion= "";

    if(checkPercentageOfProject($conn, $projectId)==100){
        $query = "SELECT completion_date from projects WHERE  project_id = '$projectId'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_array($result);
        if($row['completion_date']== "" || $row['completion_date']==NULL){
            $query = "SELECT created_at from tasks WHERE project_id = '$projectId' ORDER BY created_at DESC LIMIT 1";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_array($result);
            $dateOfCompletion = $row['created_at'];
        }
        else{
            $dateOfCompletion = $row['completed_date'];
        }
    }
return $dateOfCompletion;
    
}

//code to check for percentage completion of a project where it is either completed, in progress or not started
function checkPercentageOfProject( $conn, $projectId){
    // echo("here adey"); die();
    // $query = "SELECT AVG(t.completion)=100 from tasks t WHERE t.project_id ='$projectId' ";
    $query = "SELECT AVG(t.completion) as completion from tasks t WHERE (t.status=59 OR t.status=61 OR t.status=58) AND t.project_id ='$projectId' ";
    // echo($query); die();
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);

    return intval($row['completion']);
}

?>