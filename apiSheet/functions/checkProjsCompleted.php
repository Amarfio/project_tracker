<?php

    function checkProjsCompleted($conn){
        // $query = "UPDATE `tasks` SET overdue = 1 WHERE CURRENT_DATE > end_date AND completion <> 100";
        $query = "UPDATE projects SET status = 88 WHERE projects.is_approved = 1 AND (SELECT AVG(t.completion)=100 from tasks t WHERE t.project_id = projects.project_id)";
        $result = mysqli_query($conn, $query);
        
    } 

    
    function updateTaskToCompleted($conn){
        $query = "UPDATE tasks SET status = 61 WHERE completion = 100";
        $result = mysqli_query($conn, $query);
    }