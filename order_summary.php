<?php
include 'db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Access denied! Please log in.'); window.location.href='login.php';</script>";
    exit();
}

// Check if required parameters are present
if (!isset($_GET['movie_id'], $_GET['date'], $_GET['showtime'], $_GET['seats'])) {
    echo "<script>alert('Error: Missing booking details.'); window.location.href='index.php';</script>";
    exit();
}

// Handle snack removal (if delete request is made)
if (isset($_GET['delete_snack'])) {
    $delete_id = $_GET['delete_snack'];
    $_SESSION['selected_snacks'] = array_filter($_SESSION['selected_snacks'], function ($snack) use ($delete_id) {
        return $snack['id'] != $delete_id;
    });
    $_SESSION['selected_snacks'] = array_values($_SESSION['selected_snacks']); // Reindex array
    header("Location: order_summary.php?movie_id={$_GET['movie_id']}&date={$_GET['date']}&showtime={$_GET['showtime']}&seats={$_GET['seats']}");
    exit();
}

// Fetch snacks from session
$snacks = $_SESSION['selected_snacks'] ?? [];

$movie_id = intval($_GET['movie_id']);
$date = htmlspecialchars($_GET['date']);
$showtime = htmlspecialchars($_GET['showtime']);
$seats = explode(",", htmlspecialchars($_GET['seats'])); // Convert seats from string to array

// Fetch movie details from the database
$query = "SELECT title, poster, price_per_seat FROM movies WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo "<script>alert('Database error.'); window.location.href='index.php';</script>";
    exit();
}
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();
$stmt->close();

// Ensure movie exists
if (!$movie) {
    echo "<script>alert('Error: Movie not found.'); window.location.href='index.php';</script>";
    exit();
}

// Calculate ticket price
$ticket_price = count($seats) * $movie['price_per_seat'];

// Calculate snacks price
$snacks_total = 0;
foreach ($snacks as $snack) {
    $snacks_total += $snack['price'] * $snack['quantity'];
}

// Calculate total price (Tickets + Snacks)
$total_price = $ticket_price + $snacks_total;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 700px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); margin-top: 30px; }
        h2, h3 { text-align: center; }
        .movie-poster {
            width: 100%; /* Make it responsive */
            max-height: 300px; /* Restrict the height */
            object-fit: contain; /* Ensure the whole poster fits without cropping */
            border-radius: 8px;
        }
        .snack-item { display: flex; align-items: center; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ddd; }
        .snack-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
        .snack-info { flex-grow: 1; text-align: left; margin-left: 10px; }
        .total-price { font-size: 1.2em; font-weight: bold; text-align: right; }
    </style>
</head>
<body>

<div class="container mt-4">
    <!-- Back Button Aligned to Right -->
    <div class="text-end mb-3">
        <a href="select_snacks.php?movie_id=<?php echo $movie_id; ?>&date=<?php echo $date; ?>&showtime=<?php echo $showtime; ?>&seats=<?php echo htmlspecialchars($_GET['seats']); ?>" class="btn btn-secondary">⬅ Back</a>
    </div>

    <h2>Order Summary</h2>

    <!-- Movie Details -->
    <?php
    $poster_path = 'uploads/movies/' . htmlspecialchars($movie['poster']);
    ?>
    <img src="<?php echo file_exists($poster_path) ? $poster_path : 'uploads/movies/default.jpg'; ?>" 
         class="movie-poster" 
         alt="Movie Poster">

    <table class="table table-bordered mt-3">
        <tr><th>Movie:</th><td><?php echo htmlspecialchars($movie['title']); ?></td></tr>
        <tr><th>Show Date:</th><td><?php echo htmlspecialchars($date); ?></td></tr>
        <tr><th>Show Time:</th><td><?php echo htmlspecialchars($showtime); ?></td></tr>
        <tr><th>Seats:</th><td><?php echo htmlspecialchars(implode(", ", $seats)); ?></td></tr>
        <tr><th>Tickets Price:</th><td><strong>₹<?php echo $ticket_price; ?></strong></td></tr>
    </table>

    <!-- Snacks Details -->
    <h3>Selected Snacks</h3>
    <?php if (!empty($snacks)): ?>
        <ul class="list-group">
            <?php foreach ($snacks as $snack): ?>
                <li class="list-group-item snack-item">
                    <img src="uploads/snacks/<?php echo htmlspecialchars($snack['image']); ?>" alt="<?php echo htmlspecialchars($snack['name']); ?>">
                    <div class="snack-info">
                        <strong><?php echo htmlspecialchars($snack['name']); ?></strong> (Qty: <?php echo htmlspecialchars($snack['quantity']); ?>)
                    </div>
                    <span><strong>₹<?php echo $snack['price'] * $snack['quantity']; ?></strong></span>
                    <button class="delete-btn" onclick="deleteSnack(<?php echo $snack['id']; ?>)">❌</button>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted text-center">No snacks selected.</p>
    <?php endif; ?>

    <!-- Total Price -->
    <div class="total-price mt-3">
        <p>Total Price: <strong>₹<?php echo $total_price; ?></strong></p>
    </div>

    <!-- Proceed Button -->
    <form action="calculate_total.php" method="post" class="text-center mt-3">
        <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
        <input type="hidden" name="date" value="<?php echo $date; ?>">
        <input type="hidden" name="showtime" value="<?php echo $showtime; ?>">
        <input type="hidden" name="seats" value="<?php echo htmlspecialchars($_GET['seats']); ?>">
        
        <!-- Preserve Snacks -->
        <?php foreach ($snacks as $snack): ?>
            <input type="hidden" name="snacks[]" value="<?php echo htmlspecialchars(json_encode($snack)); ?>">
        <?php endforeach; ?>
        
        <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
        <button type="submit" class="btn btn-success w-100">Proceed to Final Price</button>
    </form>
</div>

<script>
function deleteSnack(snackId) {
    if (confirm("Are you sure you want to remove this snack?")) {
        window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?movie_id=<?php echo $movie_id; ?>&date=<?php echo $date; ?>&showtime=<?php echo $showtime; ?>&seats=<?php echo $_GET['seats']; ?>&delete_snack=" + snackId;
    }
}
</script>

</body>
</html>