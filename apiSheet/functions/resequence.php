<?php

function resequence_all_projects($conn)
{
    $query = "
        SELECT DISTINCT project_id
        FROM vw_tasks_under_project_by_id
        WHERE archived = 0
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        return false;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $project_id = $row['project_id'];
        task_id_sequence_by_project_id($project_id, $conn);
    }

    return true;
}

function task_id_sequence_by_project_id($project_id, $conn)
{
    $query = "
        SELECT task_id
        FROM vw_tasks_under_project_by_id
        WHERE project_id = '$project_id'
          AND archived = 0
        ORDER BY task_id ASC
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        return false;
    }

    $x = 1;

    while ($row = mysqli_fetch_assoc($result)) {

        $task_id = $row['task_id'];

        $updateQuery = "
            UPDATE tasks
            SET p_task_id = '$x'
            WHERE task_id = '$task_id'
        ";

        mysqli_query($conn, $updateQuery);

        $x++;
    }

    return true;
}

