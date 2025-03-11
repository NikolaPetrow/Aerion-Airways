<?php
session_start();
require_once 'db.php';

// Check for admin rights
if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Function to generate random flight number
function generateFlightNumber() {
    $airline_code = "AA"; // Aerion Airways code
    $number = str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    return $airline_code . $number;
}

// Processing add/edit flight form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_flight':
                $flight_number = isset($_POST['flight_number']) && !empty($_POST['flight_number']) 
                    ? mysqli_real_escape_string($conn, $_POST['flight_number'])
                    : generateFlightNumber();
                $departure_city = mysqli_real_escape_string($conn, $_POST['departure_city']);
                $arrival_city = mysqli_real_escape_string($conn, $_POST['arrival_city']);
                $departure_time = mysqli_real_escape_string($conn, $_POST['departure_time']);
                $arrival_time = mysqli_real_escape_string($conn, $_POST['arrival_time']);
                $price = (float)$_POST['price'];
                $seats = (int)$_POST['seats'];

                $query = "INSERT INTO flights (flight_number, departure_city, arrival_city, departure_time, arrival_time, price, available_seats) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssdi", $flight_number, $departure_city, $arrival_city, $departure_time, $arrival_time, $price, $seats);
                
                if ($stmt->execute()) {
                    $success = "Flight added successfully!";
                } else {
                    $error = "Error adding flight: " . $conn->error;
                }
                break;

            case 'delete_flight':
                $flight_id = (int)$_POST['flight_id'];
                
                // Check for existing bookings
                $check_query = "SELECT COUNT(*) as count FROM bookings WHERE flight_id = ?";
                $stmt = $conn->prepare($check_query);
                $stmt->bind_param("i", $flight_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $count = $result->fetch_assoc()['count'];

                if ($count > 0) {
                    $error = "Cannot delete this flight because there are existing bookings.";
                } else {
                    $query = "DELETE FROM flights WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $flight_id);
                    
                    if ($stmt->execute()) {
                        $success = "Flight deleted successfully!";
                    } else {
                        $error = "Error deleting flight: " . $conn->error;
                    }
                }
                break;
        }
    }
}

// Get all flights
$flights_query = "SELECT * FROM flights ORDER BY departure_time";
$flights_result = $conn->query($flights_query);

// Get all airports for the form
$airports_query = "SELECT DISTINCT city FROM airports ORDER BY city";
$airports_result = $conn->query($airports_query);
$airports = [];
while($row = $airports_result->fetch_assoc()) {
    $airports[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Aerion Airways</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="has-fixed-nav">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/images/logo.png" alt="Aerion Airways Logo" class="nav-logo">
                <span class="brand-text ms-2">Aerion Airways</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <?php if (isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn']): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-4">
        <h2 class="mb-4">Admin Panel</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add New Flight -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add New Flight</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="row g-3">
                    <input type="hidden" name="action" value="add_flight">
                    
                    <div class="col-md-6">
                        <label class="form-label">Flight Number (leave empty for auto-generation)</label>
                        <div class="input-group">
                            <input type="text" name="flight_number" class="form-control" placeholder="AA1234">
                            <button class="btn btn-outline-secondary" type="button" onclick="generateRandomFlightNumber()">
                                <i class="fas fa-random"></i> Generate
                            </button>
                        </div>
                        <small class="text-muted">Format: AA1234 (AA = Airline Code, 1234 = Flight Number)</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Departure City</label>
                        <select name="departure_city" class="form-select" required>
                            <option value="">Select departure city</option>
                            <?php foreach ($airports as $airport): ?>
                                <option value="<?php echo htmlspecialchars($airport['city']); ?>">
                                    <?php echo htmlspecialchars($airport['city']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Arrival City</label>
                        <select name="arrival_city" class="form-select" required>
                            <option value="">Select arrival city</option>
                            <?php foreach ($airports as $airport): ?>
                                <option value="<?php echo htmlspecialchars($airport['city']); ?>">
                                    <?php echo htmlspecialchars($airport['city']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Departure Time</label>
                        <input type="datetime-local" name="departure_time" class="form-control" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Arrival Time</label>
                        <input type="datetime-local" name="arrival_time" class="form-control" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" name="price" class="form-control" step="0.01" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Available Seats</label>
                        <input type="number" name="seats" class="form-control" required>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Add Flight</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Flights List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Manage Flights</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Flight Number</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Departure</th>
                                <th>Arrival</th>
                                <th>Price</th>
                                <th>Available Seats</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($flight = $flights_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($flight['flight_number']); ?></td>
                                    <td><?php echo htmlspecialchars($flight['departure_city']); ?></td>
                                    <td><?php echo htmlspecialchars($flight['arrival_city']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($flight['departure_time'])); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($flight['arrival_time'])); ?></td>
                                    <td>$<?php echo number_format($flight['price'], 2); ?></td>
                                    <td><?php echo $flight['available_seats']; ?></td>
                                    <td>
                                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this flight?');">
                                            <input type="hidden" name="action" value="delete_flight">
                                            <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function generateRandomFlightNumber() {
        const airlineCode = "AA";
        const randomNum = Math.floor(1000 + Math.random() * 9000);
        document.querySelector('input[name="flight_number"]').value = airlineCode + randomNum;
    }
    </script>
</body>
</html> 