<?php
$host = "localhost";      // or 127.0.0.1
$username = "root";    // your MySQL username
$password = "student";  // your MySQL password
$database = "isproject"; // your database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(" Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Successfully connected to the database!";
}

$conn->close();
?>
