<?php
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { // Ensure role check is case-sensitive
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input
    $hall_name = trim($_POST['hall_name']);
    $rows = intval($_POST['rows']);
    $columns = intval($_POST['columns']);
    $total_seats = $rows * $columns; // Auto-calculated

    // Check if fields are empty
    if (empty($hall_name) || $rows <= 0 || $columns <= 0) {
        $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Please fill all fields correctly!</div>";
        header("Location: admin_add_hall.php");
        exit();
    }

    // Check if hall already exists (Prevent Duplicate Entry)
    $checkStmt = $conn->prepare("SELECT id FROM halls WHERE name = ?");
    $checkStmt->bind_param("s", $hall_name);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        $_SESSION['message'] = "<div class='alert alert-warning text-center'>⚠ Hall already exists!</div>";
        header("Location: admin_add_hall.php");
        exit();
    }
    $checkStmt->close();

    // Insert hall into database
    $stmt = $conn->prepare("INSERT INTO halls (name, hall_rows, hall_columns, total_seats) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siii", $hall_name, $rows, $columns, $total_seats);

    if ($stmt->execute()) {
        $hall_id = $stmt->insert_id; // Get last inserted hall ID

        // Insert seats into the 'seats' table
        $seatInsertQuery = $conn->prepare("INSERT INTO seats (hall_id, seat_number) VALUES (?, ?)");

        for ($r = 1; $r <= $rows; $r++) {
            for ($c = 1; $c <= $columns; $c++) {
                $seat_number = chr(64 + $r) . $c; // Generates seat names like A1, A2, B1, B2...
                $seatInsertQuery->bind_param("is", $hall_id, $seat_number);
                $seatInsertQuery->execute();
            }
        }

        $seatInsertQuery->close();
        $stmt->close();
        $conn->close();

        $_SESSION['message'] = <div class='alert alert-success text-center'>"✅ Hall added successfully with $total_seats seats!</div>";
        header("Location: manage_halls.php"); // Redirect to Manage Halls page after success
        exit();
    } else {
        $_SESSION['message'] = <div class='alert alert-danger text-center'>"❌ Database Error: " . $conn->error . "</div>";
        header("Location: admin_add_hall.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Hall</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        .top-right-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>

    <script>
        function calculateTotalSeats() {
            let rows = document.getElementById("rows").value;
            let columns = document.getElementById("columns").value;

            if (rows && columns) {
                document.getElementById("total_seats").value = rows * columns;
            } else {
                document.getElementById("total_seats").value = "";
            }
        }

        // Hide message after 3 seconds
        setTimeout(() => {
            let messageBox = document.getElementById("message-box");
            if (messageBox) {
                messageBox.style.transition = "opacity 0.5s";
                messageBox.style.opacity = "0";
                setTimeout(() => messageBox.remove(), 500);
            }
        }, 3000);
    </script>
</head>
<body>

<!-- Dashboard & Logout Buttons -->
<div class="top-right-buttons">
    <a href="manage_halls.php" class="btn btn-secondary me-2">⬅ Back</a>
    <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
</div>

<div class="container">
    <!-- Display Success/Error Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div id="message-box">
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <h3 class="text-center mb-4">🏛️ Add New Hall</h3>

    <form method="POST">
        <!-- Hall Name -->
        <div class="mb-3">
            <label class="form-label">Hall Name</label>
            <input type="text" name="hall_name" class="form-control" required>
        </div>

        <!-- Rows -->
        <div class="mb-3">
            <label class="form-label">Number of Rows</label>
            <input type="number" id="rows" name="rows" class="form-control" min="1" oninput="calculateTotalSeats()" required>
        </div>

        <!-- Columns -->
        <div class="mb-3">
            <label class="form-label">Number of Columns</label>
            <input type="number" id="columns" name="columns" class="form-control" min="1" oninput="calculateTotalSeats()" required>
        </div>

        <!-- Total Seats (Auto-Calculated) -->
        <div class="mb-3">
            <label class="form-label">Total Seats</label>
            <input type="number" id="total_seats" name="total_seats" class="form-control" readonly required>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-success w-100">➕ Add Hall</button>
    </form>
</div>

</body>
</html>