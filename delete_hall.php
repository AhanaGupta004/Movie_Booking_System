<?php
include 'db.php';
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if hall ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$hall_id = intval($_GET['id']); // Convert ID to integer for security

// Start transaction for atomic operations
$conn->begin_transaction();

try {
    // Check if the `showtimes` table contains `hall_id`
    $checkShowtimesColumn = $conn->query("SHOW COLUMNS FROM showtimes LIKE 'hall_id'");
    $hasHallIdInShowtimes = $checkShowtimesColumn->num_rows > 0;

    // Check if the `bookings` table contains `hall_id`
    $checkBookingsColumn = $conn->query("SHOW COLUMNS FROM bookings LIKE 'hall_id'");
    $hasHallIdInBookings = $checkBookingsColumn->num_rows > 0;

    // If hall_id exists, delete related records
    if ($hasHallIdInShowtimes) {
        $stmt = $conn->prepare("DELETE FROM showtimes WHERE hall_id = ?");
        $stmt->bind_param("i", $hall_id);
        $stmt->execute();
        $stmt->close();
    }

    if ($hasHallIdInBookings) {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE hall_id = ?");
        $stmt->bind_param("i", $hall_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete the hall itself
    $stmt = $conn->prepare("DELETE FROM halls WHERE id = ?");
    $stmt->bind_param("i", $hall_id);
    $stmt->execute();
    $stmt->close();

    // Commit the transaction
    $conn->commit();

    // Success message
    echo "<script>alert('✅ Hall deleted successfully!'); window.location='manage_halls.php';</script>";
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();

    // Error message
    echo "<script>alert('❌ Error deleting hall: " . addslashes($e->getMessage()) . "'); window.location='manage_halls.php';</script>";
} finally {
    $conn->close();
}
?>