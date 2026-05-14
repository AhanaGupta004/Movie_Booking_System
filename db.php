
<?php
$host = "127.0.0.1"; // Use 127.0.0.1 instead of 'localhost'
$user = "root";
$password = ""; // Keep empty if no password
$database = "movies"; // Ensure this matches your database name
$port = 3307; // Use the correct MySQL port

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

