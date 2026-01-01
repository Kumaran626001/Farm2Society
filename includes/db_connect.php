<?php
// Database configuration

$whitelist = array(
    '127.0.0.1',
    '::1',
    'localhost:8080'
);

if (in_array($_SERVER['HTTP_HOST'], $whitelist)) {
    // Localhost Configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "farm2society_db";
} else {
    // InfinityFree / Live Configuration
    $servername = "sql307.infinityfree.com";
    $username = "if0_40752988";
    $password = "Cellcare9";
    $dbname = "if0_40752988_farm2society";
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['response' => "DB Connection failed: " . $conn->connect_error]));
}
