<?php
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Unauthorized access. Please log in as an admin.</div>";
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $movie_id = intval($_GET['id']); // Ensure movie ID is an integer

    // Fetch movie details (to delete poster file)
    $query = "SELECT poster FROM movies WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $movie = $result->fetch_assoc();
        $posterPath = "uploads/movies/" . $movie['poster']; // Corrected path

        // 🔹 Delete associated showtimes first
        $deleteShowtimes = "DELETE FROM showtimes WHERE movie_id = ?";
        $stmt = $conn->prepare($deleteShowtimes);
        $stmt->bind_param("i", $movie_id);
        if (!$stmt->execute()) {
            $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error deleting showtimes: " . $stmt->error . "</div>";
            header("Location: manage_movie.php");
            exit();
        }

        // 🔹 Delete movie from database
        $deleteQuery = "DELETE FROM movies WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $movie_id);

        if ($stmt->execute()) {
            // Delete the poster file if it exists
            if (file_exists($posterPath)) {
                if (!unlink($posterPath)) {
                    $_SESSION['message'] = "<div class='alert alert-warning text-center'>⚠ Movie deleted, but poster file could not be removed.</div>";
                }
            }
            $_SESSION['message'] = "<div class='alert alert-success text-center'>✅ Movie deleted successfully!</div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error deleting movie: " . $stmt->error . "</div>";
        }
    } else {
        $_SESSION['message'] = "<div class='alert alert-warning text-center'>⚠ Movie not found.</div>";
    }
} else {
    $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Invalid request.</div>";
}

// 🔹 Redirect to manage movies page
header("Location: manage_movie.php");
exit();
?>