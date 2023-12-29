<?php 

//code to check for earlier date
function checkForEarlierDate($newStartDate, $oldStartDate){
    $newDateTime = strtotime($newStartDate);
    $oldDateTime = strtotime($oldStartDate);

    return $newDateTime < $oldDateTime;
}

//code to check for later date
function checkForLaterDate($newDate, $oldDate){
    $newDateTime = strtotime($newDate);
    $oldDateTime = strtotime($oldDate);
    
    return $newDateTime > $oldDateTime;
}

?>