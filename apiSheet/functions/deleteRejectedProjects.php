<?php

    function deleteRejectedProjects($conn){
        // $query = "UPDATE `tasks` SET overdue = 1 WHERE CURRENT_DATE > end_date AND completion <> 100";
        $query = "DELETE from projects where status = 83 and is_approved = 1 AND DATEDIFF(NOW(), created_at)>=30";
        $result = mysqli_query($conn, $query);
        
    } 