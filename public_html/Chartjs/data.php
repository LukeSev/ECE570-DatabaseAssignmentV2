<?php
//setting header to json
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Grab secret credentials
require_once dirname(__DIR__) . '/../private/config.php';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

//query to get data from the table
$query = sprintf("SELECT node_name, temperature, humidity, time_received FROM sensor_data");

//execute query
$result = $conn->query($query);

//loop through the returned data
$data = array();
foreach ($result as $row) {
  $data[] = $row;
}

//close connection
$conn->close();

//now print the data
print json_encode($data);
