<?php
    declare(strict_types=1);
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);


    // Grab secret credentials
    require_once dirname(__DIR__) . '/private/config.php';

    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    
    // Perform the queries
    $sql_sensorRegister = "SELECT node_name, manufacturer, latitude, longitude FROM sensor_register";
    $result_sensorRegister = $conn->query($sql_sensorRegister);
    
    $sql_sensorData = "SELECT node_name, time_received, temperature, humidity FROM sensor_data";
    $result_sensorData = $conn->query($sql_sensorData);
    
    // Generate array of rows and sort by node name, then by time_received
    $register_rows = [];
    while($row = $result_sensorRegister->fetch_assoc()) {
        $register_rows[] = $row;    // Append row
    }
    
    $data_rows = [];
    while($row = $result_sensorData->fetch_assoc()) {
        $data_rows[] = $row;        // Append row
    }
        
    // Sort each table using uasort 
    uasort($register_rows, function ($a, $b) {
        return ($a['node_name'] <=> $b['node_name']) ?: ($a['time_received'] <=> $b['time_received']);
    });
    
    uasort($data_rows, function ($a, $b) {
        return ($a['node_name'] <=> $b['node_name']) ?: ($a['time_received'] <=> $b['time_received']);
    });
    
    function get_node_rows(string $nodename) {
        global $data_rows;
        $node_rows = [];
        foreach($data_rows as $row) {
            if($row['node_name'] == $nodename) {
                $node_rows[] = $row;
            }
        }
        return $node_rows;
    }
    
    function get_node_colVals(string $nodename, string $column) {
        $vals = [];
        $node_rows = get_node_rows($nodename);
        foreach($node_rows as $row) {
            $vals[] = $row[$column];
        }
        return $vals;
    }
    
    // Function to calculate Average Temp & Humidity for a given sensor
    function average_tempHumidity(string $node_name): array {
        global $conn;
        
        $sql_nodeData = "SELECT node_name, time_received, temperature, humidity FROM sensor_data WHERE node_name = '$node_name'";
        $result_tempHumid = $conn->query($sql_nodeData);
        $tempTot = 0.00;
        $humTot = 0.00;
        $nRows = 0;
        $node_rows = get_node_rows($node_name);
        foreach($node_rows as $row) {
            $tempTot += $row['temperature'];
            $humTot += $row['humidity'];
            $nRows++;
        }
        if($nRows < 1) {
            return [0, 0];
        } else {
            return [$tempTot/$nRows, $humTot/$nRows];
        }
    }
    $avgs = average_tempHumidity('node_1');
    
    // Calculate various statistics for each node
    function calculate_nodeStats(string $node_name) {
        global $conn;
        
        $sql_nodeData = "SELECT node_name, time_received, temperature, humidity FROM sensor_data WHERE node_name = '$node_name'";
        $result = $conn->query($sql_nodeData);
        $maxTemp = -1.0;
        $minHumid = 9999;
        $tempTot = 0.00;
        $humTot = 0.00;
        $nRows = 0;
        while($row = $result->fetch_assoc()) {
            $temp = $row['temperature'];
            $hum = $row['humidity'];
            if($temp > $maxTemp) {
                $maxTemp = $temp;
            }
            if($hum < $minHumid) {
                $minHumid = $hum;
            }
            $tempTot += $temp;
            $humTot += $hum;
            $nRows++;
        }

        if($nRows < 1) {
            $stats = [
                "maxTemp" => 0,
                "minHumid" => 0,
                "avgTemp" => 0,
                "avgHum" => 0
            ];
        } else {
            $stats = [
                "maxTemp" => $maxTemp/1,
                "minHumid" => $minHumid/1,
                "avgTemp" => $tempTot/$nRows,
                "avgHum" => $humTot/$nRows
            ];
        }
        return $stats;
    }
    $node1_stats = calculate_nodeStats('node_1');
    
    // Calculate stats for all Nodes
    $node_stats = [];
    foreach($register_rows as $row) {
        $node_stat = calculate_nodeStats($row['node_name']);
        $node_stats[$row['node_name']] = $node_stat;
    }
    
    function display_node_JSON(string $node_name) {
        global $conn;
        
        $sql_nodeData = "SELECT node_name, time_received, temperature, humidity FROM sensor_data WHERE node_name = '$node_name'";
        $result = $conn->query($sql_nodeData);
        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT);
        
        $result->free();
    }
    // display_node_JSON('node_2');
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <h1>Welcome to SSU IoT Lab!</h1>
    
    <!--Display Registered Sensors-->
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr><th colspan="4">Registered Sensor Nodes</th></tr>
            <tr>
                <th>node_name</th>
                <th>manufacturer</th>
                <th>latitude</th>
                <th>longitude</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($register_rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['node_name']) ?></td>
                    <td><?= htmlspecialchars($row['manufacturer']) ?></td>
                    <td><?= htmlspecialchars($row['latitude']) ?></td>
                    <td><?= htmlspecialchars($row['longitude']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <br><br>
    
    <!--Display Sensor Data-->
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr><th colspan="4">Sensor Data</th></tr>

            <tr>
                <th>node_name</th>
                <th>time_received</th>
                <th>temperature</th>
                <th>humidity</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['node_name']) ?></td>
                    <td><?= htmlspecialchars($row['time_received']) ?></td>
                    <td><?= htmlspecialchars($row['temperature']) ?></td>
                    <td><?= htmlspecialchars($row['humidity']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <caption><br>The average Temperature for node 1 has been <?= round($node1_stats['avgTemp'],2)?><br></caption>
    <caption>The average Humidity for node 1 has been <?= round($node1_stats['avgHum'],2)?>%<br></caption>
    
    <br><br>

    <!-- Select which Node to show data for -->
    <div class="selectBox">
        <label for="nodeSelect">Choose Sensor Node:</label>
        <select id="nodeSelect">
            <?php foreach ($register_rows as $row): ?>
            <option value="<?= htmlspecialchars($row['node_name']) ?>"><?= htmlspecialchars($row['node_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- Insert Chart -->
    <div id="chart-container" style="width: 600px;">
      <canvas id="myChart"></canvas>
    </div>

    <!-- javascript -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    <!-- This is the location of app.js file - we are assuming it is in the same folder as this file-->
    <script type="text/javascript" src="/Chartjs/app.js?v=<?php echo time(); ?>"></script>

    <div id='avgTemp'>Average Temperature: <?= round($node1_stats['avgTemp'],2)?></div>
    <div id='avgHum'>Average Humidity: <?= round($node1_stats['avgHum'],2)?>%</div>
    <div id='maxTemp'>Maximum Temperature: <?= round($node1_stats['maxTemp'],2)?></div>
    <div id='minHum'>Minimum Humidity: <?= round($node1_stats['minHumid'],2)?>%</div>
    
</body>
</html>


