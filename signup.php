<?php
include 'db.php';
$message = ""; // Variable to store success or error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role']; // Admin or Customer

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $message = "<p class='text-danger mt-2'><i class='bi bi-exclamation-circle'></i> You already have an account!</p>";
    } else {
        // Insert new user using prepared statements (prevents SQL injection)
        $query = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $query->bind_param("ssss", $name, $email, $password, $role);

        if ($query->execute()) {
            $message = "<p class='text-success mt-2'><i class='bi bi-check-circle'></i> Signup successful!</p>";
        } else {
            $message = "<p class='text-danger mt-2'><i class='bi bi-x-circle'></i> Error: " . $query->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>

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
        .btn-signup {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }
        .btn-signup:hover {
            background-color: #218838;
        }
        .login-link {
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
        <!-- Home Button (Moved to the top-right) -->
        <a href="index.php" class="btn btn-light home-button">
            <i class="bi bi-house-door-fill"></i> Home
        </a>

        <h2 class="mb-4">Sign Up</h2>

        <form method="POST">
            <!-- Name Input -->
            <div class="form-group">
                <i class="bi bi-person-fill"></i>
                <input type="text" name="name" class="form-control" placeholder="Enter Name" required>
            </div>

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

            <!-- Role Selection -->
            <div class="form-group">
                <i class="bi bi-person-badge-fill"></i>
                <select name="role" class="form-control" required>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- Sign Up Button -->
            <button type="submit" class="btn btn-signup">
                <i class="bi bi-person-plus-fill"></i> Sign Up
            </button>

            <!-- Message -->
            <div class="message"><?php echo $message; ?></div>
        </form>

        <!-- Login Link -->
        <p class="login-link mt-3">
            <i class="bi bi-box-arrow-in-right"></i> Already have an account? 
            <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>
