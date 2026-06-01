<?php
include 'db.php';
session_start();
$message = ""; // Variable to store success or error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user from database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            // Redirect based on role
            if ($user['role'] == "Admin") {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $message = "<p class='text-danger mt-2'><i class='bi bi-exclamation-circle'></i> Incorrect email or password!</p>";
        }
    } else {
        $message = "<p class='text-danger mt-2'><i class='bi bi-exclamation-circle'></i> No account found with this email!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

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
        .btn-login {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }
        .btn-login:hover {
            background-color: #0056b3;
        }
        .signup-link {
            margin-top: 10px;
        }
        .home-button {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Home Button -->
        <a href="index.php" class="btn btn-light home-button">
            <i class="bi bi-house-door-fill"></i> Home
        </a>

        <h2 class="mb-4">Login</h2>

        <form method="POST">
            <!-- Email Input -->
            <div class="form-group">
                <i class="bi bi-envelope-fill"></i>
                <input type="email" name="email" class="form-control" placeholder="Enter Email" required>
            </div>

            <!-- Password Input -->
            <div class="form-group">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
            </div>

            <!-- Login Button -->
            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>

            <!-- Message -->
            <div class="message"><?php echo $message; ?></div>
        </form>
        <!-- Forgot Password Link -->
        <p class="forgot-password mt-3">
            <i class="bi bi-key-fill"></i> Forgot your password? 
            <a href="forgot_password.php">Reset it here</a>
        </p>
        <!-- Signup Link -->
        <p class="signup-link mt-3">
            <i class="bi bi-person-plus-fill"></i> Don't have an account? 
            <a href="signup.php">Sign up here</a>
        </p>
    </div>
</body>
</html>
