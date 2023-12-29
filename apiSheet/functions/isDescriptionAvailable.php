<?php
require_once 'response.php';

function isDescriptionAvailable($conn, $tableName, $description){
    

    //query for the result
    $query = "SELECT description FROM $tableName WHERE description = '$description'";
    
    // echo($query); die();
    //hit the db with the table name and description of the table's description with regards to a particular role
    $result = mysqli_query($conn, $query);

    //count if its there, prompt user with the result
    $num = mysqli_num_rows($result);

    if($num >0 ){
        $message = response("Similar description already exists in the records!", "failed", null);
        exit($message);
    }else{
        return false;
    }

}
?>