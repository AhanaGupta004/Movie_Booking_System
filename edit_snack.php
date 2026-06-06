<?php
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { // Ensure role check is case-sensitive
    header("Location: login.php");
    exit();
}

// Check if snack ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_snacks.php?t=" . time());
    exit();
}

$snackId = intval($_GET['id']); // Ensure snack ID is an integer

// Fetch snack details
$query = "SELECT * FROM snacks WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $snackId);
$stmt->execute();
$result = $stmt->get_result();
$snack = $result->fetch_assoc();

if (!$snack) {
    header("Location: manage_snacks.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $image = $snack['image']; // Keep existing image by default

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/snacks/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];
        $fileSize = $_FILES['image']['size']; // in bytes

        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error: Only JPEG, PNG, and GIF images are allowed.</div>";
            header("Location: edit_snack.php?id=$snackId");
            exit();
        }

        if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error: File size must be less than 5MB.</div>";
            header("Location: edit_snack.php?id=$snackId");
            exit();
        }

        // Generate unique file name
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = time() . "_" . uniqid() . "." . $fileExtension;
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            // Delete old image if a new one is uploaded
            if (!empty($snack['image']) && file_exists($targetDir . $snack['image'])) {
                unlink($targetDir . $snack['image']);
            }
            $image = $imageName;
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error uploading image!</div>";
            header("Location: edit_snack.php?id=$snackId");
            exit();
        }
    }

    // Update snack details
    $updateQuery = "UPDATE snacks SET name = ?, price = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sdsi", $name, $price, $image, $snackId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "<div class='alert alert-success text-center'>✅ Snack updated successfully!</div>";
        header("Location: manage_snacks.php?t=" . time());
        exit();
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error updating snack: " . $stmt->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Snack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
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
    <a href="manage_snacks.php" class="btn btn-secondary me-2">⬅ Back</a>
    <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
</div>

<div class="container-box text-center">
    <h2 class="mb-4">✏ Edit Snack</h2>

    <!-- Display Message -->
    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']); // Clear message after displaying
    }
    ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Snack Name</label>
            <input type="text" name="name" class="form-control text-center" value="<?= htmlspecialchars($snack['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Price (₹)</label>
            <input type="number" name="price" class="form-control text-center" value="<?= $snack['price'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Current Image</label><br>
            <img src="uploads/snacks/<?= $snack['image'] ?>" width="100" class="rounded mb-2"><br>
            <label class="form-label">Upload New Image (optional)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success w-100">💾 Update Snack</button>
    </form>
</div>

</body>
</html>