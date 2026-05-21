<?php
include 'db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Access denied! Please log in.'); window.location.href='login.php';</script>";
    exit();
}

// Check if form data is received
if (!isset($_POST['movie_id'], $_POST['date'], $_POST['showtime'], $_POST['seats'], $_POST['final_price'], $_POST['payment_method'], $_POST['csrf_token'])) {
    echo "<script>alert('Error: Missing booking details.'); window.location.href='index.php';</script>";
    exit();
}

// Validate CSRF token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "<script>alert('Error: Invalid CSRF token.'); window.location.href='index.php';</script>";
    exit();
}

// Retrieve form data
$user_id = $_SESSION['user_id'];
$movie_id = intval($_POST['movie_id']);
$show_date = htmlspecialchars($_POST['date']);
$show_time = htmlspecialchars($_POST['showtime']);
$seat_numbers = htmlspecialchars($_POST['seats']); // Seat IDs (e.g., "A1,A2")
$seats_array = explode(",", $seat_numbers); // Convert comma-separated string to array
$seats_count = count($seats_array); // Count seats booked
$final_price = floatval($_POST['final_price']);
$payment_method = htmlspecialchars($_POST['payment_method']);
$transaction_id = NULL; // Initially NULL, will be updated after payment

// Validate seat numbers
foreach ($seats_array as $seat) {
    if (!preg_match('/^[A-Za-z]\d+$/', $seat)) {
        echo "<script>alert('Invalid seat numbers. Seat numbers must be in the format A1,A2,B3, etc.'); window.history.back();</script>";
        exit();
    }
}

// Debugging: Log the seat numbers and count
error_log("Seat Numbers: " . $seat_numbers);
error_log("Seats Count: " . $seats_count);

// Fetch showtime_id from the database (assuming each show has a unique ID)
$query = "SELECT id FROM showtimes WHERE movie_id = ? AND show_date = ? AND show_time = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $movie_id, $show_date, $show_time);
$stmt->execute();
$result = $stmt->get_result();
$showtime = $result->fetch_assoc();
$stmt->close();

if (!$showtime) {
    echo "<script>alert('Error: Showtime not found.'); window.location.href='index.php';</script>";
    exit();
}

$showtime_id = $showtime['id'];

// Insert booking into bookings table
$query = "INSERT INTO bookings (user_id, movie_id, seat_number, seats, showtime_id, booking_date, total_price, show_date, show_time) 
          VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("iisisdsd", $user_id, $movie_id, $seat_numbers, $seats_count, $showtime_id, $final_price, $show_date, $show_time);

if ($stmt->execute()) {
    // Get the inserted booking ID
    $booking_id = $stmt->insert_id;
    $stmt->close();

    // Redirect to payment gateway (for now, just simulate success)
    echo "<script>
        alert('Booking successful! Proceeding to payment...');
        window.location.href='payment_success.php?booking_id=$booking_id';
    </script>";
    exit();
} else {
    echo "<script>alert('Error: Booking failed. Please try again.'); window.location.href='index.php';</script>";
    exit();
}
?>
