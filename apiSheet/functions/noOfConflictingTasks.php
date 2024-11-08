<?php

function getNoOfConflictingTasks($task_id, $conn)
{
    $query = "select * from vw_clashing_tasks where task_id = '$task_id'";
        $result = mysqli_query($conn, $query);
        $num = mysqli_num_rows($result);
    return $num;
}

?>