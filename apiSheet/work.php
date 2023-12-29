<?php
$servername = "localhost";
$username = "unionsgc_ticket_test";
$password = "pass1234pass1234";
$dbname = "unionsgc_ticket_test";

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "project_tracker_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// sql to create table
$sql = "CREATE TABLE tb_change (
requestor_id VARCHAR(50) NOT NULL,
ticket_id VARCHAR(50) NOT NULL,
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
customer_name VARCHAR(50) NOT NULL,
description_of_change VARCHAR(50) NOT NULL,
request_by VARCHAR(50) NOT NULL,
schedule_of_change VARCHAR(50) NOT NULL,
budget_of_change VARCHAR(50) NOT NULL,
reason_of_change TEXT NOT NULL,
risk_plan TEXT NOT NULL,
other_impacts TEXT NOT NULL,
reasons_required TEXT NOT NULL,
date_submitted timestamp NOT NULL,
date_required VARCHAR(50) NOT NULL,
submitter_email VARCHAR(50) NOT NULL,
submitter_id VARCHAR(50) NOT NULL,
other_comments TEXT NOT NULL,
change_type INT(11) NOT NULL,
approver1 INT(11) NULL,
date1 VARCHAR(50) NOT NULL,
approver2 INT(11) NULL,
date2 VARCHAR(50) NOT NULL,
approver3 INT(11) NULL,
date3 VARCHAR(50) NOT NULL,
teamleaderapprover VARCHAR(50) NULL,
date VARCHAR(50) NULL,
teamleader_flag VARCHAR(50) NULL,
approver_flag VARCHAR(5) NULL,
reason_for_rejection VARCHAR(200) NULL,
recommendations VARCHAR(100) NULL,
decision VARCHAR(200) NULL,
decision_date VARCHAR(50) NULL,
decision_explanation VARCHAR(200) NULL,
conditions VARCHAR(100) NULL,
temp_app VARCHAR(200) NULL,
CacheData VARCHAR(3000) NULL,
LinkToApprove VARCHAR(200) NULL,
projectManagerFlag VARCHAR(10) NULL,
finalApproverFlag VARCHAR(10) NULL,
status VARCHAR(5) NULL,
raised_by VARCHAR(50) NULL
)";

if ($conn->query($sql) === TRUE) {
  echo "Table tb_change created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

$conn->close();
?>