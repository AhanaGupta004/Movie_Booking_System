<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT name, email FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    echo "<script>alert('User not found!'); window.location.href = 'login.php';</script>";
    exit();
}

// Fetch booking history
$booking_query = "SELECT 
    b.id, 
    b.movie_id, 
    b.seat_number, 
    b.show_date, 
    s.show_time,  -- Use show_time from showtimes table
    m.title 
FROM bookings b 
JOIN movies m ON b.movie_id = m.id 
JOIN showtimes s ON b.showtime_id = s.id  -- Join with showtimes table
WHERE b.user_id = ? 
ORDER BY b.show_date DESC, s.show_time DESC";
$booking_stmt = $conn->prepare($booking_query);
$booking_stmt->bind_param("i", $user_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
$bookings = $booking_result->fetch_all(MYSQLI_ASSOC);
date_default_timezone_set('Asia/Kolkata'); // Change to your timezone

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; margin-top: 50px; }
        .card { margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .card-header { background-color: #007bff; color: white; font-weight: bold; }
        .card-body { padding: 20px; }
        .booking-item { margin-bottom: 15px; padding: 10px; border-bottom: 1px solid #eee; }
        .booking-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                User Details
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Booking History
            </div>
            <div class="card-body">
                <?php if (empty($bookings)): ?>
                    <p>No bookings found.</p>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-item">
                            <p><strong>Movie:</strong> <?php echo htmlspecialchars($booking['title']); ?></p>
                            <p><strong>Seat Number:</strong> <?php echo htmlspecialchars($booking['seat_number']); ?></p>
                            <p><strong>Show Date:</strong> <?php echo htmlspecialchars($booking['show_date']); ?></p>
                            <p><strong>Show Time:</strong> <?php echo date("h:i A", strtotime($booking['show_time'])); ?></p>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>