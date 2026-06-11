<?php
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { // Ensure role check is case-sensitive
    $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Unauthorized access. Please log in as an admin.</div>";
    header("Location: login.php");
    exit();
}

// Check if snack ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Invalid snack ID!</div>";
    header("Location: manage_snacks.php");
    exit();
}

$snackId = intval($_GET['id']); // Ensure snack ID is an integer

// Fetch the snack details (to delete image)
$query = "SELECT image FROM snacks WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $snackId);
$stmt->execute();
$result = $stmt->get_result();
$snack = $result->fetch_assoc();

if ($snack) {
    // Delete the image file if it exists
    $imagePath = "uploads/snacks/" . $snack['image'];
    if (!empty($snack['image']) && file_exists($imagePath)) {
        if (!unlink($imagePath)) {
            $_SESSION['message'] = "<div class='alert alert-warning text-center'>⚠ Snack deleted, but image file could not be removed.</div>";
        }
    }

    // Delete the snack from the database
    $deleteQuery = "DELETE FROM snacks WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $snackId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "<div class='alert alert-success text-center'>✅ Snack deleted successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error deleting snack: " . $stmt->error . "</div>";
    }
} else {
    $_SESSION['message'] = "<div class='alert alert-warning text-center'>⚠ Snack not found!</div>";
}

// Redirect to manage_snacks.php
header("Location: manage_snacks.php");
exit();
?>