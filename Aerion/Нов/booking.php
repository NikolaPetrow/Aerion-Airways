<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Get destination from URL if provided
$selected_destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get all flights for the selected date
$flights_query = "SELECT f.*, 
                 f.departure_city, f.arrival_city,
                 TIMEDIFF(f.arrival_time, f.departure_time) as flight_duration
                 FROM flights f
                 WHERE DATE(f.departure_time) = ?";

if (!empty($selected_destination)) {
    $flights_query .= " AND f.arrival_city = ?";
}

$flights_query .= " ORDER BY f.departure_time";

$stmt = $conn->prepare($flights_query);
if (!empty($selected_destination)) {
    $stmt->bind_param("ss", $selected_date, $selected_destination);
} else {
    $stmt->bind_param("s", $selected_date);
}
$stmt->execute();
$flights_result = $stmt->get_result();
$flights = [];
while($row = $flights_result->fetch_assoc()) {
    $flights[] = $row;
}

// Get all airports for the form
$airports_query = "SELECT * FROM airports ORDER BY city";
$airports_result = $conn->query($airports_query);
$airports = [];
while($row = $airports_result->fetch_assoc()) {
    $airports[] = $row;
}

// Process booking form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $flight_id = (int)$_POST['flight_id'];
    $user_id = $_SESSION['user_id'];
        $passengers = (int)$_POST['passengers'];
        
    // Check if flight exists and has enough seats
    $flight_query = "SELECT * FROM flights WHERE id = ? AND available_seats >= ?";
        $stmt = $conn->prepare($flight_query);
    $stmt->bind_param("ii", $flight_id, $passengers);
        $stmt->execute();
        $result = $stmt->get_result();
        
    if ($result->num_rows === 1) {
            $flight = $result->fetch_assoc();
        $total_price = $flight['price'] * $passengers;
            
        // Start transaction
        $conn->begin_transaction();
            
        try {
            // Create booking
            $booking_query = "INSERT INTO bookings (user_id, flight_id, num_passengers, total_price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($booking_query);
            $stmt->bind_param("iiid", $user_id, $flight_id, $passengers, $total_price);
            $stmt->execute();
            
            // Update available seats
                $update_seats = "UPDATE flights SET available_seats = available_seats - ? WHERE id = ?";
                $stmt = $conn->prepare($update_seats);
            $stmt->bind_param("ii", $passengers, $flight_id);
                $stmt->execute();
                
            $conn->commit();
            $success = "Резервацията е успешна! Обща цена: " . number_format($total_price, 2) . " лв.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Възникна грешка при обработката на резервацията. Моля, опитайте отново.";
        }
    } else {
        $error = "Избраният полет не е наличен или няма достатъчно свободни места.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Flight - Aerion Airways</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .flight-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .flight-card:hover {
            transform: translateY(-5px);
        }
        .flight-time {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
        }
        .flight-duration {
            color: #666;
            font-size: 0.9rem;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 20px;
            display: inline-block;
            position: relative;
            margin: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .flight-duration::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 50%;
            width: 20px;
            height: 2px;
            background: #3498db;
        }
        .flight-duration::after {
            content: '';
            position: absolute;
            right: -20px;
            top: 50%;
            width: 20px;
            height: 2px;
            background: #3498db;
        }
        .flight-price {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
        }
        .date-selector {
            display: flex;
            overflow-x: auto;
            padding: 10px 0;
            margin-bottom: 30px;
            gap: 10px;
        }
        .date-item {
            min-width: 100px;
            padding: 10px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        .date-item.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        .date-item:hover {
            border-color: #3498db;
        }
        .date-day {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .date-date {
            font-size: 0.9rem;
            color: inherit;
        }
        .airport-code {
            font-size: 0.8rem;
            color: #666;
        }
        .flight-path {
            position: relative;
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .flight-path::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #3498db;
            font-size: 1.5rem;
        }
        .flight-path::after {
            content: '•';
            position: absolute;
            right: 0;
            color: #3498db;
            font-size: 1.5rem;
        }
        .search-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            margin-top: 20px;
        }
        .date-navigation {
            margin-top: 20px;
        }
        .date-box {
            padding: 10px 15px;
            border-radius: 8px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 80px;
        }
        .date-box:hover {
            background: #e9ecef;
        }
        .date-box.active {
            background: #0d6efd;
            color: white;
        }
        .day-name {
            font-weight: bold;
            font-size: 0.9rem;
        }
        .date {
            font-size: 0.8rem;
        }
        @media (max-width: 768px) {
            .date-box {
                min-width: 60px;
                padding: 8px 10px;
            }
            .day-name {
                font-size: 0.8rem;
            }
            .date {
                font-size: 0.7rem;
            }
        }
        .fare-card {
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .fare-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .fare-options {
            display: none;
        }
        
        .flight-card.expanded .fare-options {
            display: block;
        }
        
        .fare-card .card-title {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .fare-card .price-tag h4 {
            color: #3498db;
        }
        
        .fare-card ul li {
            font-size: 0.9rem;
            padding: 5px 0;
        }
    </style>
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
                        <li class="nav-item">
                        <a class="nav-link" href="booking.php">Book Flight</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="checkin.php">Online Check-in</a>
                    </li>
                    <?php if (isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn']): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="my-bookings.php"><i class="fas fa-ticket-alt"></i> My Bookings</a></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="search-section py-5">
            <div class="search-container bg-white p-4 rounded-lg shadow-sm">
                <h2 class="text-center mb-4">Find Your Flight</h2>
                <form method="GET" action="" id="searchForm">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="departure" name="departure" required>
                                    <option value="">Select departure</option>
                                    <?php foreach($airports as $airport): ?>
                                        <option value="<?php echo $airport['city']; ?>" <?php echo (isset($_GET['departure']) && $_GET['departure'] == $airport['city']) ? 'selected' : ''; ?>>
                                            <?php echo $airport['city']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="departure">From</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" id="destination" name="destination" required>
                                    <option value="">Select destination</option>
                            <?php foreach($airports as $airport): ?>
                                        <option value="<?php echo $airport['city']; ?>" <?php echo (isset($_GET['destination']) && $_GET['destination'] == $airport['city']) ? 'selected' : ''; ?>>
                                            <?php echo $airport['city']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                                <label for="destination">To</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="date" name="date" value="<?php echo $selected_date; ?>" required>
                                <label for="date">Date</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-3 mb-4">
                        <i class="fas fa-search me-2"></i> Search Flights
                    </button>
                </form>

                <!-- Centered Date Navigation -->
                <div class="date-navigation">
                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        <?php
                        for($i = -3; $i <= 3; $i++) {
                            $date = date('Y-m-d', strtotime($selected_date . " {$i} days"));
                            $day = date('D', strtotime($date));
                            $display_date = date('d M', strtotime($date));
                            $active = $date === $selected_date ? 'active' : '';
                            $current_departure = isset($_GET['departure']) ? "&departure=" . $_GET['departure'] : "";
                            $current_destination = isset($_GET['destination']) ? "&destination=" . $_GET['destination'] : "";
                            echo "
                            <a href='?date={$date}{$current_departure}{$current_destination}' 
                               class='date-box text-decoration-none {$active}'>
                                <div class='day-name'>{$day}</div>
                                <div class='date'>{$display_date}</div>
                            </a>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .search-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            margin-top: 2rem;
        }
        
        .search-container {
            max-width: 900px;
            margin: 0 auto;
            border-radius: 15px;
            background: white;
        }
        
        .form-floating > .form-select,
        .form-floating > .form-control {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }
        
        .form-floating > .form-select:focus,
        .form-floating > .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #3498db, #2ecc71);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .date-box {
            background: white;
            padding: 12px 20px;
            border-radius: 10px;
            min-width: 90px;
            text-align: center;
            color: #2c3e50;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .date-box:hover {
            border-color: #3498db;
            transform: translateY(-2px);
        }
        
        .date-box.active {
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: white;
            border: none;
        }
        
        .day-name {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 2px;
        }
        
        .date {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .search-container {
                padding: 1.5rem !important;
            }
            
            .date-box {
                min-width: 70px;
                padding: 8px 12px;
            }
        }
        </style>

        <div class="booking-container mt-4">
            <!-- Search Results Message -->
            <?php if (isset($_GET['departure']) || isset($_GET['destination'])): ?>
                <div class="alert alert-info">
                    Showing flights
                    <?php if (isset($_GET['departure'])): ?>
                        from <?php echo htmlspecialchars($_GET['departure']); ?>
                    <?php endif; ?>
                    <?php if (isset($_GET['destination'])): ?>
                        to <?php echo htmlspecialchars($_GET['destination']); ?>
                    <?php endif; ?>
                    for <?php echo date('d M Y', strtotime($selected_date)); ?>
                </div>
            <?php endif; ?>

            <!-- Flights List -->
            <?php if ($flights_result && $flights_result->num_rows > 0): ?>
                <?php foreach($flights as $flight): ?>
                    <div class="flight-card card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="text-center">
                                            <div class="flight-time">
                                                <?php echo date('H:i', strtotime($flight['departure_time'])); ?>
                                            </div>
                                            <div class="airport-code">
                                                <?php echo $flight['departure_city']; ?>
                                            </div>
                                        </div>
                                        <div class="flight-path flex-grow-1 text-center">
                                            <div class="flight-duration">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php 
                                                $duration = new DateTime($flight['flight_duration']);
                                                echo $duration->format('G\h i\m');
                                                ?>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="flight-time">
                                                <?php echo date('H:i', strtotime($flight['arrival_time'])); ?>
                                            </div>
                                            <div class="airport-code">
                                                <?php echo $flight['arrival_city']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="flight-number">
                                        <?php echo $flight['flight_number']; ?>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo $flight['available_seats']; ?> seats left
                                    </small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="flight-price">
                                        <?php echo number_format($flight['price'], 2); ?> лв.
                                    </div>
                                    <small class="text-muted">per person</small>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100 select-flight" data-flight-id="<?php echo $flight['id']; ?>">
                                        Select
                                    </button>
                                </div>
                            </div>

                            <!-- Fare Options (Initially Hidden) -->
                            <div class="fare-options mt-4" style="display: none;">
                                <hr>
                                <div class="row g-4">
                                    <!-- Basic Fare -->
                                    <div class="col-md-4">
                                        <div class="card h-100 fare-card">
                                            <div class="card-body">
                                                <h5 class="card-title text-center mb-4">Basic</h5>
                                                <div class="price-tag text-center mb-3">
                                                    <h4 class="mb-0"><?php echo number_format($flight['price'], 2); ?> лв.</h4>
                                                    <small class="text-muted">per passenger</small>
                                                </div>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Cabin bag (40x30x20cm)</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Online check-in</li>
                                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i> Checked baggage</li>
                                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i> Seat selection</li>
                                                </ul>
                                                <form method="POST" action="booking-steps.php" class="mt-3">
                                                    <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                                    <input type="hidden" name="fare_type" value="basic">
                                                    <input type="hidden" name="price" value="<?php echo $flight['price']; ?>">
                                                    <select name="passengers" class="form-select mb-2">
                                                        <?php for($i = 1; $i <= min(9, $flight['available_seats']); $i++): ?>
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> passenger<?php echo $i > 1 ? 's' : ''; ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary w-100">Continue for <?php echo number_format($flight['price'], 2); ?> лв.</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Smart Fare -->
                                    <div class="col-md-4">
                                        <div class="card h-100 fare-card">
                                            <div class="card-body">
                                                <h5 class="card-title text-center mb-4">Smart</h5>
                                                <div class="price-tag text-center mb-3">
                                                    <h4 class="mb-0"><?php echo number_format($flight['price'] * 1.3, 2); ?> лв.</h4>
                                                    <small class="text-muted">per passenger</small>
                                                </div>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Cabin bag (40x30x20cm)</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Online check-in</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 20kg checked baggage</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Standard seat selection</li>
                                                </ul>
                                                <form method="POST" action="booking-steps.php" class="mt-3">
                                                    <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                                    <input type="hidden" name="fare_type" value="smart">
                                                    <input type="hidden" name="price" value="<?php echo number_format($flight['price'] * 1.3, 2); ?>">
                                                    <select name="passengers" class="form-select mb-2">
                                                        <?php for($i = 1; $i <= min(9, $flight['available_seats']); $i++): ?>
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> passenger<?php echo $i > 1 ? 's' : ''; ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary w-100">Continue for <?php echo number_format($flight['price'] * 1.3, 2); ?> лв.</button>
                                                </form>
                                            </div>
                                        </div>
                    </div>
                    
                                    <!-- Plus Fare -->
                                    <div class="col-md-4">
                                        <div class="card h-100 fare-card">
                                            <div class="card-body">
                                                <h5 class="card-title text-center mb-4">Plus</h5>
                                                <div class="price-tag text-center mb-3">
                                                    <h4 class="mb-0"><?php echo number_format($flight['price'] * 1.6, 2); ?> лв.</h4>
                                                    <small class="text-muted">per passenger</small>
                                                </div>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Cabin bag (40x30x20cm)</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Priority check-in</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 32kg checked baggage</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Premium seat selection</li>
                                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Flexible ticket</li>
                                                </ul>
                                                <form method="POST" action="booking-steps.php" class="mt-3">
                                                    <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                                    <input type="hidden" name="fare_type" value="plus">
                                                    <input type="hidden" name="price" value="<?php echo number_format($flight['price'] * 1.6, 2); ?>">
                                                    <select name="passengers" class="form-select mb-2">
                                                        <?php for($i = 1; $i <= min(9, $flight['available_seats']); $i++): ?>
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> passenger<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                                                    <button type="submit" class="btn btn-primary w-100">Continue for <?php echo number_format($flight['price'] * 1.6, 2); ?> лв.</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    No flights available for the selected date. Please try another date.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-phone"></i> +359 2 123 4567</p>
                    <p><i class="fas fa-envelope"></i> info@aerion-airways.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Sofia, Bulgaria</p>
                </div>
                <div class="col-md-4">
                    <h5>Useful Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php" class="text-light text-decoration-none">About Us</a></li>
                        <li><a href="terms.php" class="text-light text-decoration-none">Terms & Conditions</a></li>
                        <li><a href="privacy.php" class="text-light text-decoration-none">Privacy Policy</a></li>
                        <li><a href="faq.php" class="text-light text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Follow Us</h5>
                    <div class="social-links">
                        <a href="#" class="text-light text-decoration-none me-3">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                        <a href="#" class="text-light text-decoration-none me-3">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <a href="#" class="text-light text-decoration-none me-3">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="#" class="text-light text-decoration-none">
                            <i class="fab fa-linkedin-in fa-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-3">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 Aerion Airways. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <style>
    footer {
        margin-top: auto;
    }
    
    footer .social-links a {
        transition: all 0.3s ease;
    }
    
    footer .social-links a:hover {
        opacity: 0.8;
        transform: translateY(-2px);
    }
    
    footer ul li {
        margin-bottom: 0.5rem;
    }
    
    footer ul li a {
        transition: all 0.3s ease;
    }
    
    footer ul li a:hover {
        opacity: 0.8;
        padding-left: 5px;
    }
    
    footer .input-group {
        max-width: 300px;
    }
    
    footer hr {
        border-color: rgba(255,255,255,0.1);
    }
    
    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle flight selection
        const selectButtons = document.querySelectorAll('.select-flight');
        selectButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove expanded class from all cards
                document.querySelectorAll('.flight-card').forEach(card => {
                    card.classList.remove('expanded');
                    card.querySelector('.fare-options').style.display = 'none';
                });
                
                // Add expanded class to selected card
                const card = this.closest('.flight-card');
                card.classList.add('expanded');
                card.querySelector('.fare-options').style.display = 'block';
                
                // Scroll to the expanded section
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
        });
    </script>
</body>
</html> 