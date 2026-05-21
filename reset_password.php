<?php
include 'db.php';
session_start();
$message = ""; // Variable to store success or error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_GET['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if passwords match
    if ($newPassword !== $confirmPassword) {
        $message = "<p class='text-danger mt-2'><i class='bi bi-exclamation-circle'></i> Passwords do not match!</p>";
    } else {
        // Check if the token is valid and not expired
        $query = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password and clear the reset token
            $updateQuery = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $hashedPassword, $user['id']);
            $updateStmt->execute();

            $message = "<p class='text-success mt-2'><i class='bi bi-check-circle'></i> Your password has been reset successfully. <a href='login.php'>Login here</a></p>";
        } else {
            $message = "<p class='text-danger mt-2'><i class='bi bi-exclamation-circle'></i> Invalid or expired token!</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

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
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Reset Password</h2>

        <form method="POST">
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
    </div>
</body>
</html>