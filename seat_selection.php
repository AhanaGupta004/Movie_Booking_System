<?php
include 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate movie ID
if (!isset($_GET['movie_id']) || !is_numeric($_GET['movie_id'])) {
    echo "<script>alert('Invalid request!'); window.history.back();</script>";
    exit();
}

$movie_id = intval($_GET['movie_id']);

// Fetch movie & hall details
$query = "SELECT movies.*, halls.id AS hall_id, halls.hall_rows, halls.hall_columns 
          FROM movies 
          JOIN halls ON movies.hall_id = halls.id
          WHERE movies.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    echo "<script>alert('Movie not found!'); window.history.back();</script>";
    exit();
}

$hall_id = $movie['hall_id'];
$rows = $movie['hall_rows'];
$columns = $movie['hall_columns'];

// Update bookings with correct show_time and show_date from showtimes
$update_query = "UPDATE bookings tb
                 JOIN showtimes st ON tb.showtime_id = st.id
                 SET tb.show_time = st.show_time, tb.show_date = st.show_date";
$conn->query($update_query);

// Fetch available dates and showtimes
$showtimes_query = "SELECT DISTINCT show_date FROM showtimes WHERE movie_id = ? ORDER BY show_date";
$showtimes_stmt = $conn->prepare($showtimes_query);
$showtimes_stmt->bind_param("i", $movie_id);
$showtimes_stmt->execute();
$dates_result = $showtimes_stmt->get_result();

$available_dates = [];
$showtimes_map = [];

while ($row = $dates_result->fetch_assoc()) {
    $available_dates[] = $row['show_date'];
    $showtimes_map[$row['show_date']] = [];
}

// Fetch available showtimes for each date
$showtime_query = "SELECT show_date, show_time FROM showtimes WHERE movie_id = ?";
$showtime_stmt = $conn->prepare($showtime_query);
$showtime_stmt->bind_param("i", $movie_id);
$showtime_stmt->execute();
$showtime_result = $showtime_stmt->get_result();

while ($row = $showtime_result->fetch_assoc()) {
    $showtimes_map[$row['show_date']][] = $row['show_time'];
}

// Fetch booked seats
$booked_seats = [];

if (isset($_GET['date']) && isset($_GET['showtime'])) {
    $selected_date = $_GET['date'];
    $selected_showtime = $_GET['showtime'];

    $booked_query = "SELECT seat_number FROM bookings WHERE movie_id = ? AND show_date = ? AND show_time = ?";
    $booked_stmt = $conn->prepare($booked_query);
    $booked_stmt->bind_param("iss", $movie_id, $selected_date, $selected_showtime);
    $booked_stmt->execute();
    $booked_result = $booked_stmt->get_result();

    while ($row = $booked_result->fetch_assoc()) {
        $booked_seats[] = $row['seat_number'];
    }
}

// Ensure `$booked_seats` is always an array
$booked_seats = $booked_seats ?? [];

// Handle form submission for booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_seats'])) {
    $user_id = $_SESSION['user_id'];
    $showtime_id = $_POST['showtime_id'];
    $seat_numbers = $_POST['seat_numbers'];
    $total_price = $_POST['total_price'];

    // Fetch showtime details
    $showtime_query = "SELECT show_time, show_date FROM showtimes WHERE id = ?";
    $showtime_stmt = $conn->prepare($showtime_query);
    $showtime_stmt->bind_param("i", $showtime_id);
    $showtime_stmt->execute();
    $showtime_result = $showtime_stmt->get_result();
    $showtime_data = $showtime_result->fetch_assoc();

    if ($showtime_data) {
        $show_time = $showtime_data['show_time'];
        $show_date = $showtime_data['show_date'];

        // Insert booking into bookings table
        $insert_query = "INSERT INTO bookings (user_id, movie_id, showtime_id, seat_number, seats, booking_date, total_price, show_time, show_date)
                         VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iiisidss", $user_id, $movie_id, $showtime_id, $seat_numbers, count(explode(',', $seat_numbers)), $total_price, $show_time, $show_date);
        $stmt->execute();

        // Execute the update query to sync show_time and show_date
        $update_query = "UPDATE bookings tb
                         JOIN showtimes st ON tb.showtime_id = st.id
                         SET tb.show_time = st.show_time, tb.show_date = st.show_date";
        $conn->query($update_query);

        // Redirect to the same page to reflect the updated data
        header("Location: book_seats.php?movie_id=$movie_id&date=$show_date&showtime=$show_time");
        exit();
    } else {
        echo "<script>alert('Invalid showtime selected!'); window.history.back();</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Seats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #f8f9fa; text-align: center; }
        .container { max-width: 600px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); margin-top: 30px; }
        .screen { background-color: black; color: white; padding: 10px; margin: 20px auto; width: 80%; font-weight: bold; font-size: 18px; border-radius: 10px; }
        .seat { 
            width: 30px; 
            height: 30px; 
            margin: 2px; 
            text-align: center; 
            line-height: 30px; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 12px; 
            display: inline-block; 
        }
        .available { background-color: white; border: 2px solid gray; }
        .selected { background-color: green; color: white; }
        .booked { background-color: gray; pointer-events: none; color: white; cursor: not-allowed; }
        .exit { width: 60px; height: 100px; background-color: red; color: white; font-weight: bold; display: flex; align-items: center; justify-content: center; margin: auto 10px; border-radius: 5px; }
        .loading { display: none; font-weight: bold; color: blue; }
    </style>
</head>
<body>
<br>

<div class="container">
    <!-- Back & Logout Buttons -->
    <div class="d-flex justify-content-end mb-3">
    <button class="btn btn-outline-secondary me-2" onclick="window.location.href='index.php'">Back</button>

        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <h3 class="mb-3">Book Tickets for <strong><?php echo $movie['title']; ?></strong></h3>
    <h4 id="totalPrice">Total Price: ₹0.00</h4>

    <form id="bookingForm">
        <div class="mb-3">
            <label class="form-label">Select Number of Tickets</label>
            <select id="ticketCount" class="form-control" required>
                <option value="">-- Select Tickets --</option>
                <?php for ($i = 1; $i <= 10; $i++) { ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Date</label>
            <select id="selectedDate" class="form-control" required onchange="updateShowtimes()">
                <option value="">-- Select Date --</option>
                <?php foreach ($available_dates as $date) { ?>
                    <option value="<?php echo $date; ?>"><?php echo $date; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Showtime</label>
            <select id="selectedShowtime" class="form-control" required>
                <option value="">-- Select Showtime --</option>
            </select>
        </div>
    </form>

    <div class="d-flex justify-content-center align-items-center mb-3">
        <div class="exit">EXIT</div>
        <div class="screen">SCREEN</div>
        <div class="exit">EXIT</div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex flex-wrap justify-content-center">
                <?php
                for ($r = 1; $r <= $rows; $r++) {
                    for ($c = 1; $c <= min($movie['hall_columns'], 8); $c++) {
                        $seat_number = chr(64 + $r) . $c; // A1, A2, B1, B2...
                        $class = in_array($seat_number, $booked_seats) ? "booked" : "available";
                        echo "<div class='seat $class' data-seat='$seat_number'>$seat_number</div>";
                    }
                    echo '<div class="w-100"></div>'; // Ensure each row starts fresh
                }
                ?>
            </div>
        </div>
    </div>

    <div class="loading" id="loading">Loading...</div>
    <button class="btn btn-primary mt-4" id="proceed">Proceed</button>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const seats = document.querySelectorAll(".seat");
    const ticketCountSelect = document.getElementById("ticketCount");
    const dateSelect = document.getElementById("selectedDate");
    const showtimeSelect = document.getElementById("selectedShowtime");
    const pricePerSeat = <?php echo json_encode($movie['price_per_seat']); ?>;
    let selectedSeats = new Set();

    // Disable all seats initially
    seats.forEach(seat => {
        seat.style.pointerEvents = "none";
        seat.style.opacity = "0.6";
    });

    // Function to enable/disable seats based on form completion
    function updateSeatAvailability() {
        const isFormComplete = ticketCountSelect.value && dateSelect.value && showtimeSelect.value;

        seats.forEach(seat => {
            if (isFormComplete && !seat.classList.contains("booked")) {
                seat.style.pointerEvents = "auto";
                seat.style.opacity = "1";
            } else {
                seat.style.pointerEvents = "none";
                seat.style.opacity = "0.6";
            }
        });
    }

    // Function to update total price
    function updateTotalPrice() {
        let totalPrice = selectedSeats.size * pricePerSeat;
        document.getElementById("totalPrice").innerText = "Total Price: ₹" + totalPrice.toFixed(2);
    }

    // Add click event listeners to seats
    seats.forEach(seat => {
        seat.addEventListener("click", function () {
            const maxSeats = parseInt(ticketCountSelect.value);

            if (selectedSeats.has(this.dataset.seat)) {
                selectedSeats.delete(this.dataset.seat);
                this.classList.remove("selected");
            } else {
                if (selectedSeats.size < maxSeats) {
                    selectedSeats.add(this.dataset.seat);
                    this.classList.add("selected");
                } else {
                    alert(`You can only select up to ${maxSeats} seats.`);
                }
            }
            updateTotalPrice();
        });
    });

    // Enable/disable seats when form fields change
    ticketCountSelect.addEventListener("change", updateSeatAvailability);
    dateSelect.addEventListener("change", updateSeatAvailability);
    showtimeSelect.addEventListener("change", updateSeatAvailability);

    // Fetch booked seats when date or showtime changes
    dateSelect.addEventListener("change", fetchBookedSeats);
    showtimeSelect.addEventListener("change", fetchBookedSeats);

    // Function to fetch booked seats
    function fetchBookedSeats() {
        const movieId = "<?php echo $movie_id; ?>";
        const date = dateSelect.value;
        const showtime = showtimeSelect.value;

        if (!date || !showtime) {
            console.log("Date or showtime not selected.");
            document.querySelectorAll(".seat").forEach(seat => seat.classList.remove("booked"));
            return;
        }

        // Show loading spinner
        document.getElementById("loading").style.display = "block";

        // Fetch booked seats from the server
        fetch(`fetch_booked_seats.php?movie_id=${movieId}&date=${date}&showtime=${showtime}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok.");
                }
                return response.json();
            })
            .then(data => {
                console.log("Fetched Data:", data); // Debugging
                if (data.success) {
                    markBookedSeats(data.booked_seats);
                } else {
                    console.error("Error:", data.message);
                }
            })
            .catch(error => {
                console.error("Error fetching booked seats:", error);
            })
            .finally(() => {
                // Hide loading spinner
                document.getElementById("loading").style.display = "none";
            });
    }

    // Function to mark booked seats
    function markBookedSeats(bookedSeats) {
        console.log("Booked Seats:", bookedSeats); // Debugging

        seats.forEach(seat => {
            const seatNumber = seat.dataset.seat;

            // Reset seat color
            seat.classList.remove("booked");

            // If the seat is booked, mark it as grey
            if (bookedSeats.includes(seatNumber)) {
                seat.classList.add("booked");
                seat.style.pointerEvents = "none"; // Disable interaction with booked seats
                seat.style.opacity = "0.6"; // Dim booked seats
            } else {
                seat.style.pointerEvents = "auto"; // Enable interaction with available seats
                seat.style.opacity = "1"; // Reset opacity for available seats
            }
        });

        // Re-enable seats if form is complete
        updateSeatAvailability();
    }

    // Update showtimes when date changes
    let showtimesMap = <?php echo json_encode($showtimes_map); ?>;
    function updateShowtimes() {
        const selectedDate = dateSelect.value;
        const showtimeSelect = document.getElementById("selectedShowtime");

        // Debugging: Log the selected date and showtimesMap
        console.log("Selected Date:", selectedDate);
        console.log("Showtimes Map:", showtimesMap);

        // Clear existing options
        showtimeSelect.innerHTML = '<option value="">-- Select Showtime --</option>';

        // Populate showtimes for the selected date
        if (showtimesMap[selectedDate]) {
            showtimesMap[selectedDate].forEach(time => {
                const option = document.createElement("option");
                option.value = time;
                option.textContent = time;
                showtimeSelect.appendChild(option);
            });
        } else {
            console.log("No showtimes available for the selected date.");
        }

        // Clear booked seats when the date changes
        document.querySelectorAll(".seat").forEach(seat => seat.classList.remove("booked"));
    }

    // Add event listener to date dropdown
    document.getElementById("selectedDate").addEventListener("change", updateShowtimes);

    // Handle Proceed button click
    document.getElementById("proceed").addEventListener("click", function () {
        const ticketCount = ticketCountSelect.value;
        const selectedDate = dateSelect.value;
        const selectedShowtime = showtimeSelect.value;
        const selectedSeats = Array.from(document.querySelectorAll(".seat.selected")).map(seat => seat.dataset.seat);

        if (!ticketCount || !selectedDate || !selectedShowtime || selectedSeats.length !== parseInt(ticketCount)) {
            alert("Please complete your selection.");
            return;
        }

        // Redirect to terms.php with selected details
        window.location.href = `terms.php?movie_id=<?php echo $movie_id; ?>&date=${selectedDate}&showtime=${selectedShowtime}&seats=${selectedSeats.join(',')}`;
    });
});
</script>

</body>
</html>