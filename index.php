<?php
include 'db.php';
session_start(); // Start session to check login status

$is_logged_in = isset($_SESSION['user_id']); // Check if the user is logged in
$role = $is_logged_in ? $_SESSION['role'] : null;

// Redirect admin users to their dashboard
if ($role === 'Admin') {
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Theater</title>
    
    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

        /* Header Styling */
        .header {
            background-color: #222;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #ff6600; /* Orange bar */
        }

        .navbar-brand {
            font-size: 20px;
            font-weight: bold;
            color: white;
        }

        .navbar-brand i {
            font-size: 24px;
        }

        .navbar-nav .nav-link {
            color: white;
            font-size: 16px;
            padding: 8px 16px;
            border-radius: 5px;
            background-color: #444;
            transition: 0.3s;
            margin-left: 10px;
        }

        .navbar-nav .nav-link:hover {
            background-color: #666;
        }

        .logout-btn {
            background-color: red !important;
        }

        /* Movies Section */
        .movies-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 20px;
            gap: 20px; /* Add gap between cards */
        }

        .movie-card {
            background: white;
            width: 280px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            overflow: hidden;
        }

        .movie-card img {
            width: 100%;
            height: 270px;
            border-radius: 5px;
            object-fit: cover;
        }

        .movie-card h3 {
            margin: 10px 0;
        }

        .movie-card p {
            font-size: 14px;
            color: #555;
        }

        /* Buttons Container */
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }

        /* Button Styles */
        .book-button, .review-button {
            display: block;
            text-align: center;
            padding: 10px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .book-button {
            background-color: #007bff;
        }

        .book-button:hover {
            background-color: #0056b3;
        }

        .review-button {
            background-color: #28a745;
        }

        .review-button:hover {
            background-color: #1e7e34;
        }

        /* No movies message */
        .no-movies {
            text-align: center;
            font-size: 20px;
            color: #888;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        MOVIE THEATER
    </div>

    <!-- Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <!-- Home Button with Icon -->
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-door-fill"></i> Home
            </a>

            <div class="navbar-nav ms-auto">
                <?php if ($is_logged_in): ?>
                    <a href="history.php" class="btn btn-primary me-2">
                        <i class="bi bi-clock-history"></i> Booking History
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                    <a class="nav-link" href="signup.php">
                        <i class="bi bi-person-plus-fill"></i> Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Movies Section -->
    <div class="movies-container">
        <?php
        // Fetch movies from the database
        $query = "SELECT * FROM movies";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { 
                ?>
                <div class="movie-card">
                    <img src="uploads/movies/<?php echo htmlspecialchars($row['poster']); ?>" alt="Movie Poster">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><strong>Genre:</strong> <?php echo htmlspecialchars($row['genre']); ?></p>
                    <p><strong>Duration:</strong> <?php echo htmlspecialchars($row['duration']); ?> mins</p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?> mins</p>

                    <!-- Buttons inside the card -->
                    <div class="button-container">
                        <a href="<?php echo $is_logged_in ? "seat_selection.php?movie_id=" . urlencode($row['id']) : "login.php"; ?>" 
                           class="book-button">
                            <i class="bi bi-ticket-perforated-fill"></i> Book Tickets
                        </a>
                        <a href="reviews.php?movie_id=<?php echo urlencode($row['id']); ?>" class="review-button">
                            <i class="bi bi-chat-left-text-fill"></i> See Reviews
                        </a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p class='no-movies'>No movies available. Please check back later!</p>";
        }
        ?>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
