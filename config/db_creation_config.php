<?php
$servername = "localhost";
$username = "root"; // Replace with your DB username
$password = "root"; // Replace with your DB password
$dbname = "teipon_gadget"; // Keep this to use later

// Connect without selecting a database initially
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
