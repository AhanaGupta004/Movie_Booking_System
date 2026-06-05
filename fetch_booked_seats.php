<?php
include 'db.php'; // Database connection

if (!isset($_GET['movie_id']) || !isset($_GET['date']) || !isset($_GET['showtime'])) {
    echo json_encode(["success" => false, "error" => "Missing parameters"]);
    exit();
}

$movie_id = intval($_GET['movie_id']);
$date = $_GET['date'];
$showtime = $_GET['showtime'];

$query = "SELECT seat_number FROM bookings WHERE movie_id = ? AND show_date = ? AND show_time = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $movie_id, $date, $showtime);
$stmt->execute();
$result = $stmt->get_result();

$booked_seats = [];
while ($row = $result->fetch_assoc()) {
    $booked_seats[] = $row['seat_number'];
}

echo json_encode(["success" => true, "booked_seats" => $booked_seats]);
?>
