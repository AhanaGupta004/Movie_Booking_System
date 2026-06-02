<?php
include 'db.php';
session_start();
$message = ""; // Variable to store success or error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the required fields are set
    if (isset($_POST['email'], $_POST['new_password'], $_POST['confirm_password'])) {
        $email = $_POST['email'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Check if passwords match
        if ($newPassword !== $confirmPassword) {
            $message = "<p class='text-danger mt-2'><i class='bi bi-exclamation-circle'></i> Passwords do not match!</p>";
        } else {
            // Check if the email exists in the database
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                die("Prepare failed: " . $conn->error); // Debugging: Check if prepare failed
            }
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                // Hash the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update the password in the database
                $updateQuery = "UPDATE users SET password = ? WHERE email = ?";
                $updateStmt = $conn->prepare($updateQuery);
                if (!$updateStmt) {
                    die("Prepare failed: " . $conn->error); // Debugging: Check if prepare failed
                }
                $updateStmt->bind_param("ss", $hashedPassword, $email);

                if ($updateStmt->execute()) {
                    $message = "<p class='text-success mt-2'><i class='bi bi-check-circle'></i> Your password has been reset successfully. </p>";
                } else {
                    $message = "<p class='text-danger mt-2'><i class='bi bi-exclamation-circle'></i> Failed to update the password. Please try again.</p>";
                }
            } else {
                $message = "<p class='text-danger mt-2'><i class='bi bi-exclamation-circle'></i> No account found with this email!</p>";
            }
        }
    } else {
        $message = "<p class='text-danger mt-2'><i class='bi bi-exclamation-circle'></i> Please fill out all fields!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            position: relative;
        }
        .form-group {
            position: relative;
            margin-bottom: 15px;
        }
        .form-control {
            padding-left: 40px;
        }
        .form-group i {
            position: absolute;
            left: 10px;
            top: 12px;
            color: gray;
        }
        .btn-reset {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }
        .btn-reset:hover {
            background-color: #0056b3;
        }
        .login-link {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Reset Password</h2>

        <form method="POST">
            <!-- Email Input -->
            <div class="form-group">
                <i class="bi bi-envelope-fill"></i>
                <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
            </div>

            <!-- New Password Input -->
            <div class="form-group">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
            </div>

            <!-- Confirm Password Input -->
            <div class="form-group">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>

            <!-- Reset Button -->
            <button type="submit" class="btn btn-reset">
                <i class="bi bi-arrow-repeat"></i> Reset Password
            </button>

            <!-- Message -->
            <div class="message"><?php echo $message; ?></div>
        </form>

        <!-- Login Link -->
        <p class="login-link mt-3">
            <i class="bi bi-box-arrow-in-right"></i> Remember your password? 
            <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>