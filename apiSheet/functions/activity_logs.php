<?php

    function log_activity($conn, $user_id, $activity, $status, $security){
        $query = "INSERT INTO `log_activities` (`id`, `user`, `activity`, `status`, `security`, `time`) VALUES (NULL, '$user_id', '$activity', '$status', '$security', current_timestamp())";
        $result = mysqli_query($conn, $query);

        // if ($result == 1) {
        //     echo 'GOOD';
        // }else{
        //     echo 'BAD';
        // }
        
    } 