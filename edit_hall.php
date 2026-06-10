<?php
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if hall ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_halls.php");
    exit();
}

$hallId = intval($_GET['id']);

// Fetch hall details
$query = "SELECT * FROM halls WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $hallId);
$stmt->execute();
$result = $stmt->get_result();
$hall = $result->fetch_assoc();

if (!$hall) {
    header("Location: manage_halls.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $rows = isset($_POST['hall_rows']) ? intval($_POST['hall_rows']) : 0;
    $columns = isset($_POST['hall_columns']) ? intval($_POST['hall_columns']) : 0;
    $total_seats = $rows * $columns;

    // Update hall details
    $updateQuery = "UPDATE halls SET name = ?, hall_rows = ?, hall_columns = ?, total_seats = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("siiii", $name, $rows, $columns, $total_seats, $hallId);

    if ($stmt->execute()) {
        // Delete old seats
        $deleteSeatsQuery = "DELETE FROM seats WHERE hall_id = ?";
        $stmt = $conn->prepare($deleteSeatsQuery);
        $stmt->bind_param("i", $hallId);
        $stmt->execute();

        // Insert new seats
        $insertSeatQuery = $conn->prepare("INSERT INTO seats (hall_id, seat_number) VALUES (?, ?)");
        for ($r = 1; $r <= $rows; $r++) {
            for ($c = 1; $c <= $columns; $c++) {
                $seat_number = chr(64 + $r) . $c; // Generates seat labels (A1, A2, B1, B2...)
                $insertSeatQuery->bind_param("is", $hallId, $seat_number);
                $insertSeatQuery->execute();
            }
        }
        $insertSeatQuery->close();

        $_SESSION['message'] = "✅ Hall updated successfully!";
        header("Location: manage_halls.php");
        exit();
    } else {
        $_SESSION['message'] = "❌ Error updating hall: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hall</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container-box {
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
        }
        .top-right-buttons {
            position: absolute;
            top: 15px;
            right: 20px;
        }
    </style>
</head>
<body>

<!-- Back & Logout Buttons -->
<div class="top-right-buttons">
    <a href="manage_halls.php" class="btn btn-secondary me-2">⬅ Back</a>
    <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
</div>

<div class="container-box text-center">
    <h2 class="mb-4">✏ Edit Hall</h2>

    <!-- Display Message -->
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-info text-center">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']); // Clear message after displaying
    }
    ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Hall Name</label>
            <input type="text" name="name" class="form-control text-center" value="<?= htmlspecialchars($hall['name']) ?>" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Rows</label>
                <input type="number" name="hall_rows" id="hall_rows" class="form-control text-center" 
                       value="<?= htmlspecialchars($hall['hall_rows']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Columns</label>
                <input type="number" name="hall_columns" id="hall_columns" class="form-control text-center" 
                       value="<?= htmlspecialchars($hall['hall_columns']) ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Total Seats</label>
            <input type="text" id="total_seats" class="form-control text-center" value="<?= $hall['total_seats'] ?>" readonly>
        </div>

        <button type="submit" class="btn btn-success w-100">💾 Update Hall</button>
    </form>
</div>

<script>
    // Auto-update total seats when admin inputs rows & columns
    document.getElementById('hall_rows').addEventListener('input', updateTotalSeats);
    document.getElementById('hall_columns').addEventListener('input', updateTotalSeats);

    function updateTotalSeats() {
        let rows = document.getElementById('hall_rows').value;
        let columns = document.getElementById('hall_columns').value;
        document.getElementById('total_seats').value = rows * columns;
    }
</script>

</body>
</html>