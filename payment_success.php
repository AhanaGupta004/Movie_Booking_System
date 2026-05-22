<?php
session_start();

// Include the phpqrcode library
require 'includes/phpqrcode.php';

// Check if booking_id is set in the URL
if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);

    // Generate QR code data (e.g., booking details)
    $qr_data = "Booking ID: $booking_id\n";
    $qr_data .= "Customer Name: John Doe\n"; // Replace with actual customer name
    $qr_data .= "Show Date: 2023-10-15\n"; // Replace with actual show date
    $qr_data .= "Table Number: 5\n"; // Replace with actual table number

    // Define the path to save the QR code image
    $qr_code_path = "uploads/qrcodes/ticket_$booking_id.png";

    // Generate the QR code and save it as an image
    QRcode::png($qr_data, $qr_code_path, QRcode::QR_ECLEVEL_L, 10);

    // Set the QR code image source for display
    $qr_code_src = $qr_code_path;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; text-align: center; }
        .container { max-width: 500px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); margin-top: 50px; }
        h2 { color: green; }
        .qr-code { margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2>🎉 Payment Successful! 🎟️</h2>
    <p>Your booking has been confirmed.</p>

    <?php if (isset($_GET['booking_id'])): ?>
        <h5>Scan the QR Code for Booking Details:</h5>
        <img src="<?php echo $qr_code_src; ?>" class="qr-code" alt="QR Code">
    <?php endif; ?>

    <br><br>
    <a href="index.php" class="btn btn-primary">Go to Home</a>
</div>

</body>
</html>