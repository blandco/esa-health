<!DOCTYPE html>
<html lang="en">
<head>
  <title>ESA Health</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>
<body>
<script>
function refresh() {
    location.reload();
}
</script>

<?php
// SET VARIABLES //
// List of ESAs to query
$hostnames   = array(
    "esa1.example.com",
    "esa2.example.com",
    "esa3.example.com"
);

// ESA API port. 6643 is the default port for TLS.
// See "Enabling AsyncOS API" at the following URL
// https://www.cisco.com/c/en/us/td/docs/security/esa/esa_all/esa_api/b_ESA_API_Getting_Started_Guide/b_ESA_API_Getting_Started_Guide_chapter_00.html#con_1092167
$apiPort     = '6443';

// Credentials are stored here. 
// Recommend using an ESA user assigned to Read-Only Operator role.
// Be sure to use TLS (default API port 6443) so that credentials are encrypted when sent over the wire.
$username    = "ESAuser";
$passphrase  = "ESAuser passphrase";
$apiToken    = base64_encode($username . ":" . $passphrase);

// Number of messages in Workqueue before highlighting the cell red.
$limitWorkq  = "2000";
// Percent of RAM in use before highlighting the cell red.
$limitRAM    = "50";
// Percent of Disk I/O in use before highlighting the cell red.
$limitDiskIO = "50";
// Percent of CPU in use before highlighting the cell red.
$limitCPU    = "85";
// END VARIABLES //
echo '<div class="container">
  <h1>IronPort Health Dashboard</h1>
  <p>Select Live or Historical data</p>
<ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#live">Live</a></li>
    <li><a data-toggle="tab" href="#hist">Historical</a></li>
</ul>

  <div class="tab-content">
    <div id="live" class="tab-pane fade in active">
<div class="container">
  <h2>ESA Health Parameters</h2>
  <p>Current health parameters of the appliance</p>
    <div class="panel panel-default">  
    <div class="panel-body">
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Hostname</th>
        <th><span class="glyphicon glyphicon-envelope"></span> Messages in WorkQ</th>
        <th><span class="glyphicon glyphicon-book"></span> % RAM</th>
        <th><span class="glyphicon glyphicon-transfer"></span> % Disk I/O</th>
        <th><span class="glyphicon glyphicon-cog"></span> % CPU</th>
      </tr>
    </thead>
    <tbody>';
//Get Live Health stats from each ESA
foreach ($hostnames as $host) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_PORT => "$apiPort",
        CURLOPT_URL => "https://$host:$apiPort/api/v1.0/health/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Basic $apiToken",
            "cache-control: no-cache"
        )
    ));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($curl);
    $err      = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo '<div class="alert alert-danger">
    <strong>cURL Error!</strong> The error was: ' . $err;
        echo '</div>';
    } else {
        $health_parameters = json_decode($response);
        $health            = array();
        array_push($health, array(
            $health_parameters->data->messages_in_workqueue
        ));
        array_push($health, array(
            $health_parameters->data->percentage_ram_utilization
        ));
        array_push($health, array(
            $health_parameters->data->percentage_diskio
        ));
        array_push($health, array(
            $health_parameters->data->percentage_cpu_load
        ));
        $report_values = $health;
    }
    //  Print Live Health Stats
    echo '<tr><td>' . $host . '</td>';
    // Messages in WorkQ
    if ($health[0][0] >= $limitWorkq) {
        echo '<td class="danger">';
        echo $health[0][0];
        echo '</td>';
    } else {
        echo '<td>';
        echo $health[0][0];
        echo '</td>';
    }
    // % RAM
    if ($health[1][0] >= $limitRAM) {
        echo '<td class="danger">';
        echo $health[1][0];
        echo '</td>';
    } else {
        echo '<td>';
        echo $health[1][0];
        echo '</td>';
    }
    // % Disk I/O
    if ($health[2][0] >= $limitDiskIO) {
        echo '<td class="danger">';
        echo $health[2][0];
        echo '</td>';
    } else {
        echo '<td>';
        echo $health[2][0];
        echo '</td>';
    }
    // % CPU
    if ($health[3][0] >= $limitCPU) {
        echo '<td class="danger">';
        echo $health[3][0];
        echo '</td>';
    } else {
        echo '<td>';
        echo $health[3][0];
        echo '</td>';
    }
}
echo '</tr>      
  </tbody>
  </table>
  </div>
  </div>  
<button type="button" class="btn btn-default" onclick="refresh()">
<span class="glyphicon glyphicon-refresh"></span> Refresh
</button>
</div>
</div>

<div id="hist" class="tab-pane fade">
<div class="container">
  <h2>ESA System Capacity</h2>
  <p>Summary of system capacity for the the last day</p>
    <div class="panel panel-default">  
    <div class="panel-body">  
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Hostname</th>
        <th>WorkQ average (sec)</th>
        <th>WorkQ max messages</th>
        <th>WorkQ average messages</th>
        <th>Overall % CPU</th>
        <th>Mail % CPU</th>
        <th>Messages IN</th>
        <th>Messages OUT</th>
      </tr>
    </thead>
    <tbody>';
// Get Historical Stats (1d) from each ESA
foreach ($hostnames as $host) {
    $curl1s = curl_init();
    curl_setopt_array($curl1s, array(
        CURLOPT_PORT => "$apiPort",
        CURLOPT_URL => "https://$host:$apiPort/api/v1.0/stats/mail_system_capacity?1d",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: Basic $apiToken",
            "cache-control: no-cache"
        )
    ));
    curl_setopt($curl1s, CURLOPT_SSL_VERIFYPEER, false);
    $response1s = curl_exec($curl1s);
    $err1s      = curl_error($curl1s);
    curl_close($curl1s);
    if ($err1s) {
        echo '<div class="alert alert-danger">
    <strong>cURL Error!</strong> The error was: ' . $err1s;
        echo '</div>';
    } else {
        $stats_parameters1s = json_decode($response1s);
        $stats1s            = array();
        array_push($stats1s, array(
            $stats_parameters1s->data->workqueue_average_time_spent
        ));
        array_push($stats1s, array(
            $stats_parameters1s->data->workqueue_messages_max
        ));
        array_push($stats1s, array(
            $stats_parameters1s->data->workqueue_average_messages
        ));
        array_push($stats1s, array(
            $stats_parameters1s->data->overall_percent_cpu_usage
        ));
        array_push($stats1s, array(
            $stats_parameters1s->data->overall_percent_cpu_usage_for_mail_count
        ));
        array_push($stats1s, array(
            $stats_parameters1s->data->incoming_messages
        ));
        array_push($stats1s, array(
            $stats_parameters1s->data->outgoing_messages
        ));
        $report_values1s = $stats1s;
    }
    // Print Historical Stats
    echo '<tr><td>' . $host . '</td>';
    echo '<td>';
    echo $stats1s[0][0];
    echo '</td>
        <td>';
    echo $stats1s[1][0];
    echo '</td>
        <td>';
    echo $stats1s[2][0];
    echo '</td>
        <td>';
    echo $stats1s[3][0];
    echo '</td>
        <td>';
    echo $stats1s[4][0];
    echo '</td>
        <td>';
    echo $stats1s[5][0];
    echo '</td>
        <td>';
    echo $stats1s[6][0];
    echo '</td>';
}
echo '</tr>
    </tbody>
  </table>
  </div>
  </div>
  </div>
</div>';
?> 
</body>
</html>
