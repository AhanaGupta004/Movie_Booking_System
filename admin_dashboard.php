<?php
include 'db.php';
session_start();


// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php"); // Redirect to login if not an admin
    exit();
}

$admin_name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        /* Header */
        .admin-header {
            background-color: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            position: relative;
        }

        /* Logout Button in Header */
        .logout-header-btn {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background-color: red;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-header-btn:hover {
            background-color: darkred;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #222;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            transition: 0.3s;
            font-weight: normal; /* Default weight */
        }

        .sidebar a.active {
            font-weight: bold;  /* Make the active link bold */
            background-color: #444; /* Optional: highlight background */
        }

        .sidebar a:hover {
            background-color: #444;
        }

        /* Content */
        .content {
            margin-left: 260px;
            padding: 20px;
        }

        .dashboard-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }

        .dashboard-card i {
            font-size: 30px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <!-- Admin Header -->
    <div class="admin-header">
        Welcome, Admin <?php echo $admin_name; ?>
        <a href="logout.php" class="logout-header-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="admin_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="manage_movie.php"><i class="bi bi-film"></i> Manage Movies</a>
        <a href="manage_bookings.php"><i class="bi bi-ticket-detailed"></i> View Bookings</a>
        <a href="manage_users.php"><i class="bi bi-people"></i> Manage Users</a>
        <a href="manage_reviews.php"><i class="bi bi-chat-square-text"></i> Manage Reviews</a>
        <a href="manage_snacks.php"><i class="bi bi-basket"></i> Manage Snacks</a>
        <a href="manage_halls.php"><i class="bi bi-film"></i> Manage Halls</a>
    </div>

    <!-- Admin Content -->
    <div class="content">
        <h2>Admin Dashboard</h2>

        <div class="row">
            <!-- Movies Card -->
            <div class="col-md-4">
                <div class="dashboard-card">
                    <i class="bi bi-film"></i>
                    <h3>Manage Movies</h3>
                    <p>Add, edit, and delete movies</p>
                    <a href="manage_movie.php" class="btn btn-primary">Go</a>
                </div>
            </div>

            <!-- Bookings Card -->
            <div class="col-md-4">
                <div class="dashboard-card">
                    <i class="bi bi-ticket-perforated"></i>
                    <h3>View Bookings</h3>
                    <p>Check movie bookings</p>
                    <a href="manage_bookings.php" class="btn btn-primary">Go</a>
                </div>
            </div>

            <!-- Users Card -->
            <div class="col-md-4">
                <div class="dashboard-card">
                    <i class="bi bi-people"></i>
                    <h3>Manage Users</h3>
                    <p>View and manage users</p>
                    <a href="manage_users.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>