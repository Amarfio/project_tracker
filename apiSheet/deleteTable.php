<?php
$servername = "localhost";
$username = "unionsgc_ticket_test";
$password = "pass1234pass1234";
$dbname = "unionsgc_ticket_test";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// sql to create table
$sql = "DROP TABLE tb_change;";

if ($conn->query($sql) === TRUE) {
  echo "Table tb_change dropped successfully";
} else {
  echo "Error dropping table: " . $conn->error;
}

$conn->close();
?>