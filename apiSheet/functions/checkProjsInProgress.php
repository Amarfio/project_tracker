<?php

    function checkProjsInProgress($conn){
        // $query = "UPDATE `tasks` SET overdue = 1 WHERE CURRENT_DATE > end_date AND completion <> 100";
        $query = "UPDATE projects SET status = 87 WHERE projects.is_approved = 1 AND (SELECT AVG(t.completion)<>100 from tasks t WHERE t.project_id = projects.project_id)";
        $result = mysqli_query($conn, $query);
        
    } 

    // $query = "CASE WHEN(
    //     (
    //     SELECT
    //         status
    //     FROM
    //         `project_tracker_db`.`projects`
    //     WHERE
    //         (
    //             `project_tracker_db`.`projects` = 119
    //         )
    // ) "