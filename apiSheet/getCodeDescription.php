<?php

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'connect.php';
if (isset($_GET['init'])) {
    $init = mysqli_escape_string($conn, $_GET['init']);

    $query = "SELECT * FROM code_desc LEFT JOIN code ON code.init = code_desc.init WHERE code_desc.init = '$init'";


    $result = mysqli_query($conn, $query);

    $num = mysqli_num_rows($result);

    $codes = array();


    if ($num > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // extract($row);
            if (!isset($codes[$row['init']])) {
                // If this is the first row for this contact, create an entry in the results
                $codes[$row['init']] = array(
                    "init" => $row['init'],
                    "name" => $row['name'],
                    "code_desc" => array()
                );
            }
            // Add this phone number to the `PhoneNumbers` array
            $codes[$row['init']]['code_desc'][] = array(
                'code_desc_id' => $row['id'],
                'code_desc_init' => $row['init'],
                "init_desc" => $row['init_desc'],
                "code_desc" => $row['desc']
            );
        }
        $codes = array_values($codes);

        echo json_encode($codes);
    } else {
        echo 'empty';
    }
}
