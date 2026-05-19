<?php
include 'db.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit();
}

// Get JSON data from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Validate JSON data
if (!$data || !is_array($data)) {
    echo json_encode(["success" => false, "error" => "Invalid snack data"]);
    exit();
}

// Initialize an array to store selected snacks
$selected_snacks = [];

// Process each snack
foreach ($data as $snack) {
    // Validate snack data
    if (!isset($snack['id']) || !isset($snack['quantity'])) {
        echo json_encode(["success" => false, "error" => "Missing snack data"]);
        exit();
    }

    $snack_id = intval($snack['id']);
    $quantity = intval($snack['quantity']);

    // Skip if quantity is invalid
    if ($quantity <= 0) {
        continue;
    }

    // Fetch snack details from the database
    $query = "SELECT name, price, image FROM snacks WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Database error"]);
        exit();
    }

    $stmt->bind_param("i", $snack_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $snack_info = $result->fetch_assoc();
    $stmt->close();

    // Add snack to the session if it exists in the database
    if ($snack_info) {
        $selected_snacks[] = [
            "id" => $snack_id,
            "name" => $snack_info['name'],
            "quantity" => $quantity,
            "price" => $snack_info['price'],
            "image" => $snack_info['image']
        ];
    }
}

// Save selected snacks to the session
$_SESSION['selected_snacks'] = $selected_snacks;

// Return success response
echo json_encode(["success" => true]);
exit();
?>