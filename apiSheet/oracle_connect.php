<?php
    $host = '//192.168.1.60:7788/unsgpdb';
    $user = 'BANKOWNER';
    $password = 'PASS1234';
    // $db_name = 'cheat';
    // $db_name = 'union';

$conn = oci_connect($user, $password, $host);

if ($conn) {
    echo json_encode(
        array(
            'message' => 'Database Connected'
        )
    );
}else{
    echo json_encode(
        array(
            'message' => 'Database Not Connected'
        )
    );
}

// Close the Oracle connection
oci_close($conn);