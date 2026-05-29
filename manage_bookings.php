<?php
include 'db.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied!");
}

// Fetch all bookings with user and movie details, grouping seats together
$bookings_query = "
SELECT 
    users.name AS user_name, 
    movies.title AS movie, 
    bookings.show_date, 
    showtimes.show_time,  -- Use show_time from showtimes table
    GROUP_CONCAT(bookings.seat_number ORDER BY bookings.seat_number SEPARATOR ', ') AS seats,  
    SUM(bookings.total_price) AS total_payment, 
    DATE_FORMAT(bookings.booking_date, '%M %d, %Y, %l:%i %p') AS booking_date 
FROM bookings
JOIN users ON bookings.user_id = users.id
JOIN movies ON bookings.movie_id = movies.id
JOIN showtimes ON bookings.showtime_id = showtimes.id  -- Join with showtimes table
GROUP BY bookings.user_id, bookings.movie_id, bookings.show_date, showtimes.show_time, bookings.booking_date
ORDER BY bookings.booking_date DESC;
"; // ✅ Removed LIMIT 1 to show all bookings

$result = $conn->query($bookings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    
    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="admin_dashboard.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    <h2>Manage Bookings</h2>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>User Name</th>
                <th>Movie</th>
                <th>Show Date</th>
                <th>Show Time</th>
                <th>Seats</th>
                <th>Total Payment</th>
                <th>Booking Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($booking = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['movie']); ?></td>
                    <td><?php echo htmlspecialchars($booking['show_date']); ?></td>
                    <td><?php echo date("h:i A", strtotime($booking['show_time'])); ?></td>
                    <td><?php echo htmlspecialchars($booking['seats']); ?></td> <!-- ✅ Grouped Seats -->
                    <td>₹<?php echo number_format($booking['total_payment'], 2); ?></td> <!-- ✅ Formatted Price -->
                    <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
