<?php

//method to get the number of tasks for project
function noOfTasks($project_id, $conn){

    $query = "SELECT COUNT(task_id) totalTasks FROM tasks where project_id = '$project_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);

    return $row['totalTasks'];
}

function noOfTasksOverdue($project_id, $conn){
    $query = "SELECT COUNT(task_id) totalTasks FROM tasks where project_id = '$project_id' AND NOW() > tasks.end_date AND tasks.completion <> 100";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_array($result);

    return $row['totalTasks'];
}

?>