<?php
include 'db.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied!");
}

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    die("Invalid user ID");
}
$user_id = $_GET['user_id'];

// Fetch user details
$user_query = "SELECT id, name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    die("User not found");
}

// Fetch user's booking history
$bookings_query = "SELECT bookings.id, movies.title, bookings.seat_number, bookings.total_price, bookings.booking_date 
                   FROM bookings 
                   JOIN movies ON bookings.movie_id = movies.id 
                   WHERE bookings.user_id = ? 
                   ORDER BY bookings.booking_date DESC";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    
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
    </style>
</head>
<body>

<div class="container">
    <a href="manage_users.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Users</a>
    <h2>User Details</h2>
    
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td><?php echo $user['id']; ?></td>
        </tr>
        <tr>
            <th>Name</th>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
        </tr>
    </table>

    <h3 class="mt-4">Booking History</h3>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Booking ID</th>
                <th>Movie Title</th>
                <th>Seats</th>
                <th>Total Price</th>
                <th>Booking Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($bookings_result->num_rows > 0): ?>
                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['title']); ?></td>
                        <td><?php echo htmlspecialchars($booking['seat_number']); ?></td>
                        <td><?php echo htmlspecialchars($booking['total_price']); ?></td>
                        <td><?php echo date("F j, Y, g:i a", strtotime($booking['booking_date'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No bookings found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
