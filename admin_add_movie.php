<?php
include 'db.php';
session_start();

// Fetch halls for the dropdown
$hallsQuery = "SELECT id, name FROM halls";
$hallsResult = $conn->query($hallsQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if required fields are set to prevent undefined array key errors
    $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : '';
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0;
    $genre = isset($_POST['genre']) ? $conn->real_escape_string($_POST['genre']) : '';
    $hall_id = isset($_POST['hall_id']) ? intval($_POST['hall_id']) : 0;
    $show_dates = isset($_POST['show_dates']) ? $_POST['show_dates'] : [];
    $showtimes = isset($_POST['showtimes']) ? $_POST['showtimes'] : [];
    $price_per_seat = isset($_POST['price_per_seat']) ? floatval($_POST['price_per_seat']) : 0;

    // Validate hall selection to prevent foreign key constraint errors
    if ($hall_id === 0) {
        $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error: Please select a hall.</div>";
        header("Location: admin_add_movie.php");
        exit();
    }

    // Handle file upload
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $uploadDir = 'uploads/movies/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
        $fileName = time() . "_" . uniqid() . "." . $fileExtension;
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['poster']['tmp_name'], $targetFilePath)) {
            // Insert movie into database
            $query = "INSERT INTO movies (title, description, duration, genre, poster, hall_id, price_per_seat) 
                      VALUES ('$title', '$description', '$duration', '$genre', '$fileName', '$hall_id', '$price_per_seat')";

            if ($conn->query($query)) {
                $movieId = $conn->insert_id; // Get last inserted movie ID

                // Insert selected show dates and times if they are not empty
                if (!empty($show_dates)) {
                    foreach ($show_dates as $date) {
                        $date = $conn->real_escape_string($date);
                        if (!empty($showtimes[$date])) {
                            foreach ($showtimes[$date] as $time) {
                                $time = $conn->real_escape_string($time);
                                $conn->query("INSERT INTO showtimes (movie_id, show_date, show_time) VALUES ('$movieId', '$date', '$time')");
                            }
                        }
                    }
                }

                $_SESSION['message'] = "<div class='alert alert-success text-center'>✅ Movie added successfully! <a href='admin_dashboard.php'>Go to Dashboard</a></div>";
            } else {
                $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Database Error: " . $conn->error . "</div>";
            }
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger text-center'>❌ Error: Unable to move uploaded file.</div>";
        }
    } else {
        $_SESSION['message'] = "<div class='alert alert-warning text-center'>❌ Error: No file uploaded or file upload failed.</div>";
    }

    header("Location: admin_add_movie.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
    <a href="manage_movie.php" class="btn btn-secondary me-2">⬅ Back</a>
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

    <h3 class="text-center mb-4">🎬 Add New Movie</h3>

    <form method="POST" enctype="multipart/form-data" onsubmit="return validateShowtimes()">
        <!-- Title -->
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required></textarea>
        </div>

        <!-- Duration -->
        <div class="mb-3">
            <label class="form-label">Duration (mins)</label>
            <input type="number" name="duration" class="form-control" required>
        </div>

        <!-- Genre -->
        <div class="mb-3">
            <label class="form-label">Genre</label>
            <input type="text" name="genre" class="form-control" required>
        </div>

        <!-- Price Per Seat -->
        <div class="mb-3">
            <label class="form-label">Price Per Seat (₹)</label>
            <input type="number" name="price_per_seat" class="form-control" step="0.01" required>
        </div>

        <!-- Hall Selection -->
        <div class="mb-3">
            <label class="form-label">Select Hall</label>
            <select name="hall_id" class="form-control" required>
                <option value="">-- Select Hall --</option>
                <?php while ($row = $hallsResult->fetch_assoc()) : ?>
                    <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Select Days and Showtimes -->
        <div class="mb-3">
            <button type="button" class="btn btn-primary w-100" onclick="openDatePicker()">📅 Add Date</button>
        </div>

        <div id="dates-container"></div>

        <!-- Poster Upload -->
        <div class="mb-3">
            <label class="form-label">Movie Poster</label>
            <input type="file" name="poster" class="form-control" accept="image/*" required>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-success w-100">➕ Add Movie</button>
    </form>
</div>

<script>
    // Function to open the date picker
    function openDatePicker() {
        let dateInput = document.createElement("input");
        dateInput.type = "date";
        dateInput.min = new Date().toISOString().split("T")[0]; // Disable past dates
        dateInput.style.position = "absolute";
        dateInput.style.opacity = "0";
        document.body.appendChild(dateInput);

        dateInput.addEventListener("change", function () {
            addDate(dateInput.value);
            document.body.removeChild(dateInput);
        });

        dateInput.showPicker(); // Opens the calendar immediately
    }

    // Function to add a date section
    function addDate(selectedDate) {
        if (!selectedDate) return;

        let datesContainer = document.getElementById("dates-container");
        let dateId = selectedDate.replace(/-/g, ''); // Unique ID for each date section

        // Prevent duplicate dates
        if (document.getElementById(`date-${dateId}`)) {
            alert("This date is already added.");
            return;
        }

        let newDateDiv = document.createElement("div");
        newDateDiv.classList.add("mb-3", "border", "p-2", "position-relative");
        newDateDiv.id = `date-${dateId}`; // Assign unique ID

        newDateDiv.innerHTML = `
            <strong>${selectedDate}</strong>
            <div id="showtimes-${dateId}">
                <div class="input-group mb-2">
                    <input type="time" name="showtimes[${selectedDate}][]" class="form-control" required>
                    <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">❌</button>
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addShowtime('${selectedDate}')">➕ Add Showtime</button>
            <input type="hidden" name="show_dates[]" value="${selectedDate}">
            
            <!-- Remove Date Button -->
            <button type="button" class="btn btn-danger btn-sm mt-2 float-end" onclick="removeDate('${dateId}')">🗑 Remove Date</button>
        `;

        datesContainer.appendChild(newDateDiv);
    }

    // Function to remove a date section
    function removeDate(dateId) {
        document.getElementById(`date-${dateId}`).remove();
    }

    // Function to add a showtime
    function addShowtime(date) {
        let dateId = date.replace(/-/g, ''); // Unique ID for each date section
        let container = document.getElementById(`showtimes-${dateId}`);

        if (!container) {
            alert("Error: Date section not found!");
            return;
        }

        let newShowtimeDiv = document.createElement("div");
        newShowtimeDiv.classList.add("input-group", "mb-2");

        newShowtimeDiv.innerHTML = `
            <input type="time" name="showtimes[${date}][]" class="form-control" required>
            <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">❌</button>
        `;

        container.appendChild(newShowtimeDiv);
    }

    // Function to validate showtimes before form submission
    function validateShowtimes() {
        let today = new Date().toISOString().split("T")[0]; // Get today's date in YYYY-MM-DD format
        let now = new Date();
        let showtimesValid = true;

        document.querySelectorAll("[name^='showtimes[']").forEach(input => {
            let date = input.name.match(/\[(.*?)\]/)[1]; // Extract date from input name
            let time = input.value;

            if (date === today) {
                let selectedDateTime = new Date(`${date}T${time}`);
                if (selectedDateTime <= now) {
                    alert("Showtime must be in the future for today's date.");
                    showtimesValid = false;
                }
            }
        });

        return showtimesValid;
    }
</script>

</body>
</html>