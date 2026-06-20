<?php
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { // Ensure role check is case-sensitive
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $image = "";

    // Handle file upload
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
            header("Location: add_snacks.php");
            exit();
        }

        if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error: File size must be less than 5MB.</div>";
            header("Location: add_snacks.php");
            exit();
        }

        // Generate unique file name
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = time() . "_" . uniqid() . "." . $fileExtension;
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image = $imageName;
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error uploading image!</div>";
            header("Location: add_snacks.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error: No file uploaded or file upload failed.</div>";
        header("Location: add_snacks.php");
        exit();
    }

    // Insert into database
    $query = "INSERT INTO snacks (name, price, image) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sds", $name, $price, $image);

    if ($stmt->execute()) {
        $_SESSION['message'] = "<div class='alert alert-success text-center'>✅ Snack added successfully! <a href='manage_snacks.php'>Manage Snacks</a></div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error adding snack: " . $stmt->error . "</div>";
    }

    header("Location: add_snacks.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Snack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
        }
        .container {
            max-width: 600px;
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
</head>
<body>

<!-- Dashboard & Logout Buttons -->
<div class="top-right-buttons">
    <a href="manage_snacks.php" class="btn btn-secondary me-2">⬅ Back</a>
    <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
</div>

<div class="container">
    <!-- Display Success/Error Message -->
    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    ?>

    <h3 class="text-center mb-4">🍿 Add New Snack</h3>

    <form method="POST" enctype="multipart/form-data">
        <!-- Snack Name -->
        <div class="mb-3">
            <label class="form-label">Snack Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <!-- Price -->
        <div class="mb-3">
            <label class="form-label">Price (₹)</label>
            <input type="number" name="price" step="0.01" class="form-control" required>
        </div>

        <!-- Image Upload -->
        <div class="mb-3">
            <label class="form-label">Snack Image</label>
            <input type="file" name="image" class="form-control" accept="image/*" required>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-success w-100">➕ Add Snack</button>
    </form>
</div>

</body>
</html>