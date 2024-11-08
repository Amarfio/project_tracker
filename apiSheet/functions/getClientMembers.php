<?php function getAllClientMembersEmails($conn, $client_id){

    // $query = "SELECT email from users WHERE client_id = '$client_id' AND is_active = 1 AND email_notice = 126";
    $query = "SELECT email from users WHERE client_id = '$client_id' AND is_active = 1 AND role = 71";
    // echo($query); die();
    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);
    $clients_arr = array();
    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
        $clients_arr[] = $row['email'];
        }
    }

    return $clients_arr;

}

?>