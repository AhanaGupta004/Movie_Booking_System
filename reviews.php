<?php
include 'db.php';
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// Get movie ID from URL
if (!isset($_GET['movie_id']) || !is_numeric($_GET['movie_id'])) {
    die("Invalid movie ID.");
}
$movie_id = intval($_GET['movie_id']);

// Fetch movie details
$movie_query = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($movie_query);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie_result = $stmt->get_result();
$movie = $movie_result->fetch_assoc();

if (!$movie) {
    die("Movie not found.");
}

// Handle new review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review']) && $is_logged_in) {
    $review_text = trim($_POST['review']);

    if (!empty($review_text)) {
        $insert_review = "INSERT INTO reviews (movie_id, user_id, comment) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_review);
        $stmt->bind_param("iis", $movie_id, $user_id, htmlspecialchars($review_text));
        $stmt->execute();
    }
    header("Location: reviews.php?movie_id=$movie_id");
    exit();
}

// Handle review update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_review']) && isset($_POST['review_id']) && $is_logged_in) {
    $review_id = intval($_POST['review_id']);
    $edited_review = trim($_POST['edit_review']);

    if (!empty($edited_review)) {
        $update_review = "UPDATE reviews SET comment = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_review);
        $stmt->bind_param("sii", htmlspecialchars($edited_review), $review_id, $user_id);
        $stmt->execute();
    }
    header("Location: reviews.php?movie_id=$movie_id");
    exit();
}

// Handle review deletion
if (isset($_GET['delete_review']) && $is_logged_in) {
    $review_id = intval($_GET['delete_review']);
    $delete_review = "DELETE FROM reviews WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_review);
    $stmt->bind_param("ii", $review_id, $user_id);
    $stmt->execute();
    header("Location: reviews.php?movie_id=$movie_id");
    exit();
}

// Fetch reviews for this movie
$reviews_query = "SELECT reviews.id, reviews.comment, reviews.created_at, users.name, reviews.user_id 
                  FROM reviews 
                  JOIN users ON reviews.user_id = users.id 
                  WHERE reviews.movie_id = ? ORDER BY reviews.created_at DESC";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - <?php echo htmlspecialchars($movie['title']); ?></title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { margin-top: 20px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        .review-box { border: 2px solid #008000; padding: 10px; margin-bottom: 10px; border-radius: 8px; background-color: #f8f9fa; }
        .username { font-weight: bold; color: #007bff; }
        .timestamp { font-size: 12px; color: #888; }
        textarea.form-control { border: 2px solid #007bff; border-radius: 8px; background-color: #f8f9fa; padding: 10px; font-size: 16px; color: #333; }
        textarea.form-control:focus { border-color: #0056b3; background-color: #ffffff; outline: none; box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5); }
        .btn-edit { margin-right: 10px; }
    </style>
</head>
<body>

    <div class="container">
        <a href="index.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Home</a>
        <h2>Reviews for "<?php echo htmlspecialchars($movie['title']); ?>"</h2>

        <!-- Review Form -->
        <?php if ($is_logged_in): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="review" class="form-label">Your Review:</label>
                    <textarea class="form-control" name="review" id="review" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Login</a> to add a review.</p>
        <?php endif; ?>

        <!-- Display Reviews -->
        <div class="mt-4">
            <?php if ($reviews_result->num_rows > 0): ?>
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <div class="review-box">
                        <p class="username"><?php echo htmlspecialchars($review['name']); ?></p>
                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        <p class="timestamp"><?php echo date("F j, Y, g:i a", strtotime($review['created_at'])); ?></p>
                        
                        <?php if ($is_logged_in && $review['user_id'] == $user_id): ?>
                            <!-- Edit and Delete Buttons -->
                            <button class="btn btn-warning btn-sm btn-edit" onclick="editReview(<?php echo $review['id']; ?>)">Edit</button>
                            <a href="reviews.php?movie_id=<?php echo $movie_id; ?>&delete_review=<?php echo $review['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this review?');">Delete</a>
                            
                            <!-- Edit Form -->
                            <form method="POST" id="edit-form-<?php echo $review['id']; ?>" style="display: none;">
                                <textarea class="form-control mt-2" name="edit_review" required><?php echo htmlspecialchars($review['comment']); ?></textarea>
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <button type="submit" class="btn btn-success btn-sm mt-2">Save</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews yet. Be the first to review!</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function editReview(id) {
            document.getElementById('edit-form-' + id).style.display = 'block';
        }
    </script>

</body>
</html>