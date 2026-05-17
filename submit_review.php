<?php
include 'db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Validate and sanitize inputs
    $movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;
    $user_id = $_SESSION['user_id'];
    $review = isset($_POST['review']) ? trim(htmlspecialchars($_POST['review'])) : '';
    $comment = isset($_POST['comment']) ? trim(htmlspecialchars($_POST['comment'])) : '';

    // Validate required fields
    if (empty($movie_id) || empty($review) || empty($comment)) {
        $_SESSION['message'] = "❌ Please fill in all required fields.";
        header("Location: index.php");
        exit();
    }

    // Insert review & comment into database
    $query = "INSERT INTO reviews (movie_id, user_id, review, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $movie_id, $user_id, $review, $comment);

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Review and comment submitted successfully!";
    } else {
        $_SESSION['message'] = "❌ Error submitting review: " . $stmt->error;
    }

    header("Location: index.php");
    exit();
}
?>