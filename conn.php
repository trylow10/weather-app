<?php
// Database connection details
$host = "localhost";
$username = "root";
$password = "root";
$database = "weatherApp";

// Create a new database connection
$conn = new mysqli($host, $username, $password, $database);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the table if it does not exist
// $sql =
//  "CREATE TABLE IF NOT EXISTS WeatherData (
//     Date DATE,
//     City VARCHAR(30),
//     Humidity INT(5,2),
//     Wind_Speed FLOAT,
//     Weather_description VARCHAR(50)
// )"
// ;

if (!$conn) {
echo "Error creating table: " . $conn->error;
}

?>