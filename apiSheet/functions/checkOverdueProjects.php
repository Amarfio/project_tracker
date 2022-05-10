<?php

    function overdue_projects($conn){
        $query = "UPDATE `projects` SET overdue = 1 WHERE CURRENT_DATE > end_date AND(SELECT AVG(t.completion) from tasks t WHERE t.project_id = projects.project_id)";
        $result = mysqli_query($conn, $query);
        
    } 