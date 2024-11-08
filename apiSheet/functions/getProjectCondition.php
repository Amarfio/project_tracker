<?php 

function getProjectCondition($startDate, $endDate, $completionPercentage) {
    // Convert dates to DateTime objects
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $now = new DateTime();

    // Check if the project has already ended
    if ($now > $end) {
        return $completionPercentage >= 100 ? 'Completed' : 'Missed';
    }

    // Calculate total duration of the project in days
    $totalDuration = $start->diff($end)->days;

    // Calculate elapsed duration of the project in days
    $elapsedDuration = $start->diff($now)->days;

    // Calculate expected completion percentage
    $expectedCompletionPercentage = ($elapsedDuration / $totalDuration) * 100;

    // Determine project condition
    if ($completionPercentage >= $expectedCompletionPercentage) {
        return 'On Target';
    } else {
        return 'Missed';
    }
}

