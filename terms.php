<?php
include 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if GET parameters are present
if (!isset($_GET['movie_id']) || !isset($_GET['date']) || !isset($_GET['showtime']) || !isset($_GET['seats'])) {
    echo "<script>alert('Missing parameters! Check console.');</script>";
    echo "<pre>";
    print_r($_GET); // Debugging: Show received GET parameters
    echo "</pre>";
    exit();
}

// Validate and sanitize inputs
$movie_id = intval($_GET['movie_id']);
$date = htmlspecialchars($_GET['date']);
$showtime = htmlspecialchars($_GET['showtime']);
$seats = htmlspecialchars($_GET['seats']);

// Debugging: Log parameters to console
echo "<script>console.log('Movie ID: $movie_id, Date: $date, Showtime: $showtime, Seats: $seats');</script>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body { background-color: #f8f9fa; text-align: center; }
        .container { 
            max-width: 600px; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); 
            margin-top: 30px; 
            position: relative; 
        }
        .back-btn { 
            position: absolute; 
            top: 10px; 
            right: 10px; 
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Back Button -->
    <button class="btn btn-outline-secondary back-btn" onclick="window.location.href='seat_selection.php?movie_id=<?php echo $movie_id; ?>&date=<?php echo urlencode($date); ?>&showtime=<?php echo urlencode($showtime); ?>&seats=<?php echo urlencode($seats); ?>'">Back</button>

    <h3 class="mb-3">Terms and Conditions</h3>
    <p class="text-muted">By proceeding, you agree to the following terms:</p>

    <ul class="text-start">
        <li><strong>Non-Refundable:</strong> Tickets once booked cannot be canceled or refunded.</li>
        <li><strong>No Outside Food:</strong> Outside food and beverages are strictly prohibited inside the theater.</li>
        <li><strong>Arrival Time:</strong> Please arrive at least 15 minutes before the showtime. Late entry may not be allowed.</li>
        <li><strong>Seat Selection:</strong> Selected seats cannot be changed after the booking process is completed.</li>
        <li><strong>ID Proof:</strong> You may be asked to present a valid ID for verification purposes.</li>
        <li><strong>Behavior Policy:</strong> Disruptive behavior may lead to removal from the premises without a refund.</li>
        <li><strong>Snacks Purchase:</strong> Additional snacks and beverages can be purchased inside the theater.</li>
    </ul>

    <button class="btn btn-success mt-3 w-100" id="accept">
        <i class="bi bi-check-circle"></i> Accept & Proceed
    </button>
</div>

<script>
    document.getElementById('accept').addEventListener('click', function () {
        window.location.href = `select_snacks.php?movie_id=<?php echo $movie_id; ?>&date=<?php echo urlencode($date); ?>&showtime=<?php echo urlencode($showtime); ?>&seats=<?php echo urlencode($seats); ?>`;
    });
</script>

</body>
</html>