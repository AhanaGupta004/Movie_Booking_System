<?php
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Check if movie ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$id = intval($_GET['id']); // Sanitize movie ID

// Fetch movie details
$query = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    die("Movie not found.");
}

// Fetch showtimes for this movie
$showtimeQuery = "SELECT show_date, show_time FROM showtimes WHERE movie_id = ? ORDER BY show_date, show_time";
$stmt = $conn->prepare($showtimeQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$showtimeResult = $stmt->get_result();

$showtimes = [];
while ($row = $showtimeResult->fetch_assoc()) {
    $showtimes[$row['show_date']][] = $row['show_time'];
}

// Fetch halls
$hallQuery = "SELECT id, name FROM halls";
$hallResult = $conn->query($hallQuery);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $duration = intval($_POST['duration']);
    $genre = htmlspecialchars(trim($_POST['genre']));
    $show_dates = $_POST['show_dates'] ?? [];
    $hall_id = intval($_POST['hall']);
    $showtimesInput = $_POST['showtimes'] ?? [];
    $price_per_seat = floatval($_POST['price_per_seat']);

    // Server-side validation for past dates
    $currentDate = date('Y-m-d');
    foreach ($show_dates as $date) {
        if ($date < $currentDate) {
            die("Error: You cannot select a date in the past.");
        }
    }

    // Check if a new poster is uploaded
    if ($_FILES['poster']['size'] > 0) {
        $targetDir = "uploads/movies/";
        $fileName = basename($_FILES['poster']['name']);
        $targetFilePath = $targetDir . $fileName;

        // Move uploaded file to destination folder
        if (move_uploaded_file($_FILES['poster']['tmp_name'], $targetFilePath)) {
            // Update with new poster
            $updateQuery = "UPDATE movies SET title=?, description=?, duration=?, genre=?, poster=?, hall_id=?, price_per_seat=? WHERE id=?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssisssdi", $title, $description, $duration, $genre, $fileName, $hall_id, $price_per_seat, $id);
        } else {
            echo "❌ Error uploading the file.";
            exit();
        }
    } else {
        // Update without changing the poster
        $updateQuery = "UPDATE movies SET title=?, description=?, duration=?, genre=?, hall_id=?, price_per_seat=? WHERE id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssisidi", $title, $description, $duration, $genre, $hall_id, $price_per_seat, $id);
    }

    if ($stmt->execute()) {
        // Delete old showtimes
        $deleteQuery = "DELETE FROM showtimes WHERE movie_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Insert new showtimes
        $insertQuery = "INSERT INTO showtimes (movie_id, show_date, show_time) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        foreach ($show_dates as $date) {
            $date = htmlspecialchars(trim($date));
            if (!empty($showtimesInput[$date])) {
                foreach ($showtimesInput[$date] as $time) {
                    $time = htmlspecialchars(trim($time));
                    $stmt->bind_param("iss", $id, $date, $time);
                    $stmt->execute();
                }
            }
        }

        echo "<script>alert('✅ Movie updated successfully!'); window.location='manage_movie.php';</script>";
    } else {
        echo "❌ Database Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Movie</title>
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        .top-right-buttons {
            position: absolute;
            top: 15px;
            right: 20px;
        }

        .container-box {
            max-width: 500px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .btn-back {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!-- Back & Logout Buttons -->
<div class="top-right-buttons">
    <a href="manage_movie.php" class="btn btn-secondary me-2">⬅ Back</a>
    <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
</div>

<!-- Edit Movie Form Container -->
<div class="container mt-5 d-flex justify-content-center">
    <div class="container-box text-center">
        <h2 class="mb-4">🎬 Edit Movie</h2>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control text-center" value="<?= htmlspecialchars($movie['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control text-center" required><?= htmlspecialchars($movie['description']) ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Duration (mins)</label>
                    <input type="number" name="duration" class="form-control text-center" value="<?= $movie['duration'] ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Genre</label>
                    <input type="text" name="genre" class="form-control text-center" value="<?= htmlspecialchars($movie['genre']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Price Per Seat (₹)</label>
                <input type="number" name="price_per_seat" class="form-control text-center" value="<?= $movie['price_per_seat'] ?>" step="0.01" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Hall</label>
                <select name="hall" class="form-control text-center" required>
                    <option value="">Select Hall</option>
                    <?php while ($hall = $hallResult->fetch_assoc()): ?>
                        <option value="<?= $hall['id'] ?>" <?= ($movie['hall_id'] == $hall['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($hall['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Showtimes</label>
                <button type="button" class="btn btn-primary w-100 mb-3" onclick="openDatePicker()">📅 Add Date</button>

                <div id="dates-container">
                    <?php foreach ($showtimes as $date => $times): ?>
                        <div class="mb-3 border p-2" id="date-<?= str_replace('-', '', $date); ?>">
                            <strong><?= $date; ?></strong>
                            <div id="showtimes-<?= str_replace('-', '', $date); ?>">
                                <?php foreach ($times as $time): ?>
                                    <div class="input-group mb-2">
                                        <input type="time" name="showtimes[<?= $date; ?>][]" class="form-control" value="<?= $time; ?>" required>
                                        <button type="button" class="btn btn-danger" onclick="this.parentElement.remove()">❌</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addShowtime('<?= $date; ?>')">➕ Add Showtime</button>
                            <input type="hidden" name="show_dates[]" value="<?= $date; ?>">
                            <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeDate('<?= str_replace('-', '', $date); ?>')">🗑 Remove Date</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Poster (Leave empty to keep current)</label>
                <input type="file" name="poster" class="form-control">
            </div>

            <button type="submit" class="btn btn-success w-100">💾 Update Movie</button>
        </form>
    </div>
</div>

<script>
    function openDatePicker() {
        let dateInput = document.createElement("input");
        dateInput.type = "date";
        dateInput.style.position = "absolute";
        dateInput.style.opacity = "0";
        document.body.appendChild(dateInput);

        dateInput.addEventListener("change", function () {
            addDate(dateInput.value);
            document.body.removeChild(dateInput);
        });

        dateInput.showPicker();
    }

    function addDate(date) {
        let dateId = date.replace(/-/g, '');
        
        // Check if the date already exists
        if (document.getElementById(`date-${dateId}`)) {
            alert("This date is already added!");
            return;
        }

        // Get the current date
        let currentDate = new Date();
        currentDate.setHours(0, 0, 0, 0); // Set time to midnight for accurate comparison

        // Convert the selected date to a Date object
        let selectedDate = new Date(date);

        // Check if the selected date is in the past
        if (selectedDate < currentDate) {
            alert("You cannot select a date in the past!");
            return;
        }

        let dateContainer = document.getElementById("dates-container");

        let newDateDiv = document.createElement("div");
        newDateDiv.classList.add("mb-3", "border", "p-2");
        newDateDiv.id = `date-${dateId}`;

        newDateDiv.innerHTML = `
            <strong>${date}</strong>
            <div id="showtimes-${dateId}"></div>
            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addShowtime('${date}')">➕ Add Showtime</button>
            <input type="hidden" name="show_dates[]" value="${date}">
            <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeDate('${dateId}')">🗑 Remove Date</button>
        `;

        dateContainer.appendChild(newDateDiv);
    }

    function addShowtime(date) {
        let dateId = date.replace(/-/g, '');
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

    function removeDate(dateId) {
        document.getElementById(`date-${dateId}`).remove();
    }
</script>

</body>
</html>