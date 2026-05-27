<?php
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { // Ensure role check is case-sensitive
    header("Location: login.php"); // Redirect to login if not an admin
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .showtime-list {
            max-height: 100px;
            overflow-y: auto;
        }
    </style>
</head>

<body class="p-4">
    <div class="container">
        <h2 class="mb-4">🎥 Manage Movies</h2>

        <!-- Dashboard & Logout Buttons -->
        <div class="d-flex justify-content-end mb-3">
            <a href="admin_dashboard.php" class="btn btn-secondary me-2">🏠 Dashboard</a>
            <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>

        <!-- Add Movie Button -->
        <a href="admin_add_movie.php" class="btn btn-primary mb-3">➕ Add Movie</a>

        <!-- Show Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div id="message-box" class="alert alert-info text-center">
                <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Fetch Movies -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Poster</th>
                    <th>Title</th>
                    <th>Genre</th>       
                    <th>Seat Price</th>
                    <th>Showtimes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch movies from the database (optimized query)
                $query = "SELECT id, title, genre, poster, price_per_seat FROM movies ORDER BY id DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $movieId = $row['id'];

                        // Fetch showtimes for the movie (optimized query)
                        $showtimeQuery = "SELECT show_date, show_time FROM showtimes WHERE movie_id = ? ORDER BY show_date, show_time LIMIT 5";
                        $stmt = $conn->prepare($showtimeQuery);
                        $stmt->bind_param("i", $movieId);
                        $stmt->execute();
                        $showtimeResult = $stmt->get_result();

                        echo "<tr>
                            <td><img src='uploads/movies/{$row['poster']}' width='50' class='rounded'></td>
                            <td>{$row['title']}</td>
                            <td>{$row['genre']}</td>
                            <td>{$row['price_per_seat']}</td>
                            <td class='showtime-list'>";

                        if ($showtimeResult->num_rows > 0) {
                            while ($showtime = $showtimeResult->fetch_assoc()) {
                                $formattedDate = date("d M Y", strtotime($showtime['show_date']));
                                $formattedTime = date("h:i A", strtotime($showtime['show_time']));
                                echo "<span class='badge bg-primary p-2 mb-1'>{$formattedDate} - {$formattedTime}</span><br>";
                            }
                        } else {
                            echo "<span class='text-muted'>Not Set</span>";
                        }
                        echo "</td>";

                        echo "<td>
                                <a href='edit_movie.php?id={$movieId}' class='btn btn-warning btn-sm'>✏ Edit</a>
                                <button class='btn btn-danger btn-sm' onclick='showDeleteModal({$movieId})'>🗑 Delete</button>
                              </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No movies found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this movie?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to open the delete modal and set the delete link
        function showDeleteModal(movieId) {
            let deleteBtn = document.getElementById("confirmDeleteBtn");
            deleteBtn.href = "delete_movie.php?id=" + movieId;
            let deleteModal = new bootstrap.Modal(document.getElementById("deleteModal"));
            deleteModal.show();
        }

        // Hide message after 3-4 seconds
        setTimeout(() => {
            let messageBox = document.getElementById("message-box");
            if (messageBox) {
                messageBox.style.transition = "opacity 0.5s";
                messageBox.style.opacity = "0";
                setTimeout(() => messageBox.remove(), 500);
            }
        }, 3000);
    </script>
</body>
</html>