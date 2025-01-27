<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle-proj";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optionally, you can set the character set for the connection
$conn->set_charset("utf8");
?>