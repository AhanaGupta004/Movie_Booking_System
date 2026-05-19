<?php
include 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate GET parameters
if (!isset($_GET['movie_id']) || !isset($_GET['date']) || !isset($_GET['showtime']) || !isset($_GET['seats'])) {
    echo "<script>alert('Missing parameters!'); window.history.back();</script>";
    exit();
}

$movie_id = intval($_GET['movie_id']);
$date = htmlspecialchars($_GET['date']);
$showtime = htmlspecialchars($_GET['showtime']);
$seats = htmlspecialchars($_GET['seats']);
$_SESSION['came_from_order_summary'] = true;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if "Proceed" or "Skip" was clicked
    if (isset($_POST['proceed'])) {
        // Save selected snacks to session
        $selected_snacks = [];
        foreach ($_POST['snacks'] as $snack_id => $quantity) {
            if ($quantity > 0) {
                $selected_snacks[] = [
                    'id' => $snack_id,
                    'quantity' => $quantity
                ];
            }
        }
        $_SESSION['selected_snacks'] = $selected_snacks;
    } else {
        // If "Skip" was clicked, clear any previously selected snacks
        $_SESSION['selected_snacks'] = [];
    }

    // Redirect to order_summary.php
    header("Location: order_summary.php?movie_id=$movie_id&date=$date&showtime=$showtime&seats=$seats");
    exit();
}

// Fetch snacks from database
$snacks_query = "SELECT * FROM snacks ORDER BY name ASC";
$snacks_result = $conn->query($snacks_query);
$has_snacks = ($snacks_result->num_rows > 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Snacks</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body { background-color: #f8f9fa; text-align: center; }
        .container { max-width: 700px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); margin-top: 30px; }
        .snack-item { display: flex; align-items: center; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ddd; }
        .snack-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
        .snack-info { flex-grow: 1; text-align: left; margin-left: 10px; }
        .top-buttons { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <!-- Back & Dashboard Buttons -->
    <div class="top-buttons">
        <a href="terms.php?movie_id=<?php echo $movie_id; ?>&date=<?php echo $date; ?>&showtime=<?php echo $showtime; ?>&seats=<?php echo $seats; ?>" class="btn btn-secondary">⬅ Back</a>
        <a href="index.php" class="btn btn-primary" onclick="<?php unset($_SESSION['selected_snacks']); ?>">Dashboard</a>
        </div>

    <h3 class="mb-3">Select Your Snacks</h3>

    <form id="snacksForm">
        <?php if ($has_snacks) { ?>
            <div class="list-group">
                <?php 
                $selected_snacks = $_SESSION['selected_snacks'] ?? []; // Retrieve stored snacks
                while ($snack = $snacks_result->fetch_assoc()) { 
                    $selected_qty = 0;
                    foreach ($selected_snacks as $selected_snack) {
                        if ($selected_snack['id'] == $snack['id']) {
                            $selected_qty = $selected_snack['quantity']; // Load previously selected quantity
                            break;
                        }
                    }
                ?>
                    <div class="snack-item">
                        <img src="uploads/snacks/<?php echo $snack['image']; ?>" alt="<?php echo $snack['name']; ?>">
                        <div class="snack-info">
                            <strong><?php echo $snack['name']; ?></strong> - ₹<?php echo $snack['price']; ?>
                        </div>
                        <input type="number" class="form-control snack-input" style="width: 60px;" min="0" 
                               value="<?php echo $selected_qty; ?>" data-id="<?php echo $snack['id']; ?>">
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="text-muted">No snacks available at the moment.</p>
        <?php } ?>

        <!-- Proceed or Skip Buttons -->
        <div id="button-container">
            <button type="button" class="btn btn-secondary mt-3 w-100" id="skip">
                <i class="bi bi-skip-forward-circle"></i> Skip
            </button>
            <button type="button" class="btn btn-success mt-3 w-100" id="proceed" style="display: none;">
                <i class="bi bi-check-circle"></i> Proceed
            </button>
        </div>
    </form>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const inputs = document.querySelectorAll(".snack-input");
        const proceedBtn = document.getElementById("proceed");
        const skipBtn = document.getElementById("skip");

        function updateButtons() {
            let hasSelected = false;

            inputs.forEach(input => {
                if (parseInt(input.value) > 0) {
                    hasSelected = true;
                }
            });

            // Toggle buttons based on selection
            proceedBtn.style.display = hasSelected ? "block" : "none";
            skipBtn.style.display = hasSelected ? "none" : "block";
        }

        // Listen for input changes on snack selections
        inputs.forEach(input => {
            input.addEventListener("input", updateButtons);
        });

        // Handle Skip button click
        skipBtn.addEventListener("click", function () {
            window.location.href = `order_summary.php?movie_id=<?php echo $movie_id; ?>&date=<?php echo $date; ?>&showtime=<?php echo $showtime; ?>&seats=<?php echo $seats; ?>`;
        });

        // Handle Proceed button click
        proceedBtn.addEventListener("click", function () {
            let selectedSnacks = [];
            inputs.forEach(input => {
                let quantity = parseInt(input.value);
                if (quantity > 0) {
                    selectedSnacks.push({ id: input.dataset.id, quantity: quantity });
                }
            });

            fetch("save_snacks.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(selectedSnacks)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `order_summary.php?movie_id=<?php echo $movie_id; ?>&date=<?php echo $date; ?>&showtime=<?php echo $showtime; ?>&seats=<?php echo $seats; ?>`;
                } else {
                    alert("Failed to save snacks: " + data.error);
                }
            })
            .catch(error => console.error("Error:", error));
        });

        // Initialize button visibility
        updateButtons();
    });
</script>

</body>
</html>