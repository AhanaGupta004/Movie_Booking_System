<?php
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user role
$user_query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if ($user['role'] !== 'Admin') {
    die("Access Denied! Only admins can manage reviews.");
}

// Handle review deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Sanitize input

    // Check if the review exists
    $check_review_query = "SELECT id FROM reviews WHERE id = ?";
    $stmt = $conn->prepare($check_review_query);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $check_review_result = $stmt->get_result();

    if ($check_review_result->num_rows > 0) {
        $delete_query = "DELETE FROM reviews WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $_SESSION['message'] = "✅ Review deleted successfully!";
    } else {
        $_SESSION['message'] = "❌ Review not found.";
    }

    header("Location: manage_reviews.php");
    exit();
}

// Fetch all reviews with movie names
$reviews_query = "SELECT reviews.id, reviews.comment, reviews.created_at, users.name AS username, movies.title AS movie_title
                  FROM reviews
                  JOIN users ON reviews.user_id = users.id
                  JOIN movies ON reviews.movie_id = movies.id
                  ORDER BY reviews.created_at DESC";
$stmt = $conn->prepare($reviews_query);
$stmt->execute();
$reviews_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { margin-top: 20px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        .review-box { border-left: 4px solid #007bff; background: #f8f9fa; padding: 10px; margin-bottom: 10px; border-radius: 5px; position: relative; }
        .username { font-weight: bold; color: #007bff; }
        .timestamp { font-size: 12px; color: #888; }
        .delete-btn { position: absolute; top: 10px; right: 10px; background: red; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        .delete-btn:hover { background: darkred; }
        .back-button { display: inline-block; margin-bottom: 15px; background-color: #6c757d; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; }
        .back-button:hover { background-color: #5a6268; }
    </style>
</head>
<body>

    <div class="container">
        <a href="admin_dashboard.php" class="back-button"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        <h2>Manage Reviews</h2>

        <!-- Display Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="mt-4">
            <?php if ($reviews_result->num_rows > 0): ?>
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <div class="review-box">
                        <p class="username"><?php echo htmlspecialchars($review['username']); ?> - <strong><?php echo htmlspecialchars($review['movie_title']); ?></strong></p>
                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        <p class="timestamp"><?php echo date("F j, Y, g:i a", strtotime($review['created_at'])); ?></p>
                        <a href="?delete_id=<?php echo $review['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this review?');">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews available.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>