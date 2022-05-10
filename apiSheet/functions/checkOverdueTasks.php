<?php

    function overdue_status($conn){
        // $query = "UPDATE `tasks` SET overdue = 1 WHERE CURRENT_DATE > end_date AND completion <> 100";
        $query = "UPDATE tasks SET status = 117 WHERE project_id IN (SELECT p.project_id FROM  projects p WHERE (p.is_approved = 1 AND CURRENT_DATE > tasks.end_date) AND tasks.completion<>100)";
        $result = mysqli_query($conn, $query);
        
    } 