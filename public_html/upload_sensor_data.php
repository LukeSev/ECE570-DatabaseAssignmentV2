<?php
    declare(strict_types=1);
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // Grab secret credentials
    require_once dirname(__DIR__) . '/private/config.php';
    
    $REQ_TEXT = base64_decode($_SERVER['QUERY_STRING'] ?? '');
    $REQ_JSON = [];
    parse_str($REQ_TEXT, $REQ_JSON);

    // Process incoming GET request (BASE64 ENCODED)
    if(isset($REQ_JSON['node_name']) && isset($REQ_JSON['temperature']) && isset($REQ_JSON['humidity'])) {
        
        // Check for properly-formatted date, otherwise generate your own
        if(isset($REQ_JSON['time_received']) && ($temp = DateTime::createFromFormat('Y-m-d H:i:s', $REQ_JSON['time_received']))) {
            // print("IN IF");
            if($temp->format('Y-m-d H:i:s') === $REQ_JSON['time_received']) {
                $time_received = $REQ_JSON['time_received'];
            } else {
                $timestamp = new DateTimeImmutable();
                $time_received = $timestamp->format('Y-m-d H:i:s');   
            }
        } else {
            // print("IN ELSE");
            $timestamp = new DateTimeImmutable();
            $time_received = $timestamp->format('Y-m-d H:i:s');
        }
        
        // Set other fields
        $node_name = $REQ_JSON['node_name'];
        $temperature = $REQ_JSON['temperature'];
        $humidity = $REQ_JSON['humidity'];
        
    }
    
    /*
    // Process incoming GET request (NO ENCODING)
    if(isset($_GET['node_name']) && isset($_GET['temperature']) && isset($_GET['humidity'])) {
        
        // Check for properly-formatted date, otherwise generate your own
        if(isset($_GET['time_received']) && ($temp = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['time_received']))) {
            // print("IN IF");
            if($temp->format('Y-m-d H:i:s') === $_GET['time_received']) {
                $time_received = $_GET['time_received'];
            } else {
                $timestamp = new DateTimeImmutable();
                $time_received = $timestamp->format('Y-m-d H:i:s');   
            }
        } else {
            // print("IN ELSE");
            $timestamp = new DateTimeImmutable();
            $time_received = $timestamp->format('Y-m-d H:i:s');
        }
        
        // Set other fields
        $node_name = $_GET['node_name'];
        $temperature = $_GET['temperature'];
        $humidity = $_GET['humidity'];
    }
    */
    
    // TEST REQUEST: curl -X GET "https://lucasloughner.site/upload_sensor_data.php%2Fnode_name=node_1&time_received=2025-10-07%2012:00:32&temperature=69&humidity=68"
    // B64-encoded test request: curl -X GET "https://lucasloughner.site/upload_sensor_data%2Fbm9kZV9uYW1lPW5vZGVfMSZ0aW1lX3JlY2VpdmVkPTIwMjUtMTAtMDclMjAxMjowMDozMiZ0ZW1wZXJhdHVyZT02OSZodW1pZGl0eT02OA=="
    
    
    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if entry already exists for that node at that time_received
    $sql = "SELECT * FROM sensor_data WHERE node_name = '$node_name' AND time_received = '$time_received'";
    $result = $conn->query($sql);
    // print($result->num_rows);
    if($result->num_rows > 0) {
        print("TIME RECEIVED ALREADY EXISTS.");
    } else {
        // Perform the query
        $sql = "INSERT INTO sensor_data (node_name, time_received, temperature, humidity) VALUES ('$node_name', '$time_received', '$temperature', '$humidity')";
        $result = $conn->query($sql);
        if($result) {
            print("DATA UPLOAD SUCCESSFUL");
        } else {
            print("DATA UPLOAD FAILED");
        } 
    }
    
    
    
    
?>