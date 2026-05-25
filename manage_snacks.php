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
    <title>Manage Snacks</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="p-4">
    <div class="container">
        <h2 class="mb-4">🍿 Manage Snacks</h2>

        <!-- Dashboard & Logout Buttons -->
        <div class="d-flex justify-content-end mb-3">
            <a href="admin_dashboard.php" class="btn btn-secondary me-2">🏠 Dashboard</a>
            <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>

        <!-- Add Snack Button -->
        <a href="add_snacks.php" class="btn btn-primary mb-3">➕ Add Snack</a>

        <!-- Show Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div id="message-box" class="alert alert-info text-center">
                <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Fetch Snacks -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price (₹)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch snacks from the database
                $query = "SELECT * FROM snacks ORDER BY id DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $snackId = $row['id'];
                        echo "<tr>
                            <td><img src='uploads/snacks/{$row['image']}' width='50' class='rounded'></td>
                            <td>{$row['name']}</td>
                            <td>₹{$row['price']}</td>
                            <td>
                                <a href='edit_snack.php?id={$snackId}' class='btn btn-warning btn-sm'>✏ Edit</a>
                                <button class='btn btn-danger btn-sm' onclick='showDeleteModal({$snackId})'>🗑 Delete</button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No snacks found.</td></tr>";
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
                    Are you sure you want to delete this snack?
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
        function showDeleteModal(snackId) {
            let deleteBtn = document.getElementById("confirmDeleteBtn");
            deleteBtn.href = "delete_snack.php?id=" + snackId;
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