<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://192.168.1.60:8181/ords/xapi/basetables/hrtbbranch',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);
// echo $response;
// die;
$mysqli = mysqli_connect("localhost", "root", "", "hrmdata");
if (mysqli_connect_errno($mysqli)) {
    // echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
$sql_ = 'DELETE FROM branches';
$result_ = mysqli_query($mysqli, $sql_);
// $row = mysqli_fetch_assoc($result_);

$data_from_api = json_decode($response);

$branches = $data_from_api->items;

foreach($branches as  $branch){

$br_code = $branch->br_code;
$br_description = $branch -> br_description;
$address1 = $branch -> address1;
$address2 = $branch -> address2;
$address3 = $branch -> address3;
$telephone = $branch -> telephone;
$email = $branch -> email;
$date_opened = $branch -> date_opened;
$posting_date = $branch -> posting_date;
$posted_by = $branch -> posted_by;
$posting_ip = $branch -> posting_ip;

// echo '<br>';
// echo '..........';
// echo $posting_ip;

$sql = 'INSERT INTO branches (br_code, br_description, address1, address2, address3, telephone, email, date_opened, posting_date, posted_by, posting_ip)
VALUES ("'.$br_code.'", "'.$br_description.'", "'.$address1.'", "'.$address2.'", "'.$address3.'", "'.$telephone.'", "'.$email.'", "'.$date_opened.'", "'.$posting_date.'", "'.$posted_by.'", "'.$posting_ip.'" )';
    $result = mysqli_query($mysqli, $sql);

    
    // $row = mysqli_fetch_assoc($result);
    

}
// die();