<?php
include 'db.php';
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a secure token
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Access denied! Please log in.'); window.location.href='login.php';</script>";
    exit();
}

// Check if form data is received
if (!isset($_POST['movie_id'], $_POST['date'], $_POST['showtime'], $_POST['seats'], $_POST['total_price'])) {
    echo "<script>alert('Error: Missing booking details.'); window.location.href='index.php';</script>";
    exit();
}

// Validate and sanitize inputs
$movie_id = intval($_POST['movie_id']);
$date = htmlspecialchars($_POST['date']);
$showtime = htmlspecialchars($_POST['showtime']);
$seats = explode(",", htmlspecialchars($_POST['seats'])); // Convert to array
$total_price = floatval($_POST['total_price']);

// Fetch movie details from the database
$query = "SELECT title, poster FROM movies WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();
$stmt->close();

if (!$movie) {
    echo "<script>alert('Error: Movie not found.'); window.location.href='index.php';</script>";
    exit();
}

// Fetch selected snacks
$snacks = isset($_POST['snacks']) ? array_map(function($snack) {
    return json_decode($snack, true); // Decode JSON into associative arrays
}, $_POST['snacks']) : [];

// Ensure all snack data is UTF-8 encoded
foreach ($snacks as &$snack) {
    foreach ($snack as $key => $value) {
        if (is_string($value)) {
            $snack[$key] = utf8_encode($value);
        }
    }
}

// Calculate Tax (e.g., 10% GST)
$tax_rate = 0.10;
$tax_amount = $total_price * $tax_rate;
$final_price = $total_price + $tax_amount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Price</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 700px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); margin-top: 30px; }
        h2, h3 { text-align: center; }
        .movie-poster { width: 100%; max-height: 300px; object-fit: contain; border-radius: 8px; }
        .total-price { font-size: 1.2em; font-weight: bold; text-align: right; }
        .btn-container { display: flex; gap: 10px; justify-content: center; }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2>Final Price with Tax</h2>

    <!-- Movie Details -->
    <?php $poster_path = 'uploads/movies/' . htmlspecialchars($movie['poster']); ?>
    <img src="<?php echo file_exists($poster_path) ? $poster_path : 'uploads/movies/default.jpg'; ?>" 
         class="movie-poster" alt="Movie Poster">

    <table class="table table-bordered mt-3">
        <tr><th>Movie:</th><td><?php echo htmlspecialchars($movie['title']); ?></td></tr>
        <tr><th>Show Date:</th><td><?php echo htmlspecialchars($date); ?></td></tr>
        <tr><th>Show Time:</th><td><?php echo htmlspecialchars($showtime); ?></td></tr>
        <tr><th>Seats:</th><td><?php echo htmlspecialchars(implode(", ", $seats)); ?></td></tr>
        <tr><th>Tickets Price:</th><td><strong>₹<?php echo number_format($total_price, 2); ?></strong></td></tr>
        <tr><th>Tax (10% GST):</th><td><strong>₹<?php echo number_format($tax_amount, 2); ?></strong></td></tr>
        <tr class="table-success"><th>Final Price:</th><td><strong>₹<?php echo number_format($final_price, 2); ?></strong></td></tr>
    </table>

    <!-- Proceed to Payment -->
    <form action="payment.php" method="post" class="text-center mt-3">
        <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
        <input type="hidden" name="date" value="<?php echo $date; ?>">
        <input type="hidden" name="showtime" value="<?php echo $showtime; ?>">
        <input type="hidden" name="seats" value="<?php echo htmlspecialchars($_POST['seats']); ?>">
        <input type="hidden" name="final_price" value="<?php echo $final_price; ?>">

        <!-- Preserve Snacks -->
        <?php foreach ($snacks as $snack): ?>
            <?php
            $snack_json = json_encode($snack);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo '<input type="hidden" name="snacks[]" value="' . htmlspecialchars($snack_json) . '">';
            } else {
                echo "<!-- Error encoding snack data: " . json_last_error_msg() . " -->";
            }
            ?>
        <?php endforeach; ?>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="btn-container">
            <button type="submit" class="btn btn-primary">Proceed to Payment</button>
            <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

</body>
</html>