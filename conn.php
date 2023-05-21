<?php
// Database connection details
$host = "localhost";
$username = "root";
$password = "root";
$database = "weatherApp";


//create database

// CREATE DATABASE weatherApp;

//create table using sql

// CREATE TABLE WeatherData (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     date DATE NOT NULL,
//     city VARCHAR(255) NOT NULL,
//     temp DECIMAL(10,2) NOT NULL,
//     humidity DECIMAL(5,2) NOT NULL,
//     wind_speed DECIMAL(10,2) NOT NULL,
//     weather_description VARCHAR(255) NOT NULL,
//     icon_url VARCHAR(255) NOT NULL
// );


// Create a new database connection
$conn = new mysqli($host, $username, $password, $database);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!$conn) {
echo "Error creating table: " . $conn->error;
}

?>