<?php
// Start session
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    $conn = new mysqli('localhost', 'root', '', 'aerion_airways');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = null;
$success = null;
$booking = null;
$available_bookings = null;

// Get user's bookings
try {
    $bookings_query = "SELECT b.id, f.flight_number, f.departure_city, f.arrival_city, f.departure_time 
                       FROM `bookings` b 
                       INNER JOIN `flights` f ON b.flight_id = f.id 
                       WHERE b.user_id = ? 
                       AND f.departure_time > NOW()
                       AND NOT EXISTS (
                           SELECT 1 
                           FROM `check_ins` c 
                           WHERE c.booking_id = b.id
                       )
                       ORDER BY f.departure_time";

    $bookings_stmt = $conn->prepare($bookings_query);
    if ($bookings_stmt === false) {
        throw new Exception("Error preparing query: " . $conn->error);
    }
    
    $bookings_stmt->bind_param("i", $_SESSION['user_id']);
    if (!$bookings_stmt->execute()) {
        throw new Exception("Error executing query: " . $bookings_stmt->error);
    }
    
    $available_bookings = $bookings_stmt->get_result();
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
    $available_bookings = false;
}

// Handle check-in submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search_booking'])) {
        $booking_id = trim($_POST['booking_id']);
        
        // Get booking details
        $query = "SELECT b.*, f.* FROM bookings b 
                 JOIN flights f ON b.flight_id = f.id 
                 WHERE b.id = ? AND b.user_id = ? 
                 AND NOT EXISTS (SELECT 1 FROM check_ins c WHERE c.booking_id = b.id)";
    $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        } else {
            $error = "Booking not found, does not belong to you, or is already checked in.";
        }
    } elseif (isset($_POST['complete_checkin'])) {
        try {
            $booking_id = $_POST['booking_id'];
            $has_paid_seat = !isset($_POST['no_seat_preference']);
            $seat_number = $has_paid_seat ? $_POST['seat_number'] : null;
            
            $conn->begin_transaction();
            
            // Verify booking
            $verify_query = "SELECT b.*, f.flight_number, f.departure_city, f.arrival_city, 
                                   f.departure_time, u.first_name, u.last_name 
                            FROM bookings b 
                            INNER JOIN flights f ON b.flight_id = f.id 
                            INNER JOIN users u ON b.user_id = u.id
                            WHERE b.id = ? AND b.user_id = ? 
                            AND NOT EXISTS (
                                SELECT 1 
                                FROM check_ins c 
                                WHERE c.booking_id = b.id
                            )";
            
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            $verify_stmt->execute();
            $booking_result = $verify_stmt->get_result();
            
            if ($booking_result->num_rows === 0) {
                throw new Exception("Invalid booking or already checked in.");
            }
            
            $booking_details = $booking_result->fetch_assoc();
            
            // Check if seat is taken
            if ($has_paid_seat && $seat_number) {
                $seat_query = "SELECT 1 FROM check_ins WHERE seat_number = ? AND booking_id IN (
                                SELECT id FROM bookings WHERE flight_id = ?
                              )";
                $seat_stmt = $conn->prepare($seat_query);
                $seat_stmt->bind_param("si", $seat_number, $booking_details['flight_id']);
                $seat_stmt->execute();
                
                if ($seat_stmt->get_result()->num_rows > 0) {
                    throw new Exception("Selected seat is already taken.");
                }
            }
            
            // Insert check-in
            $checkin_query = "INSERT INTO check_ins (booking_id, seat_number, has_paid_seat) 
                             VALUES (?, ?, ?)";
            $checkin_stmt = $conn->prepare($checkin_query);
            $checkin_stmt->bind_param("isi", $booking_id, $seat_number, $has_paid_seat);
            
            if (!$checkin_stmt->execute()) {
                throw new Exception("Failed to complete check-in: " . $checkin_stmt->error);
            }
            
            // Update price for paid seat
            if ($has_paid_seat) {
                $price_query = "UPDATE bookings SET total_price = total_price + 20 WHERE id = ?";
                $price_stmt = $conn->prepare($price_query);
                $price_stmt->bind_param("i", $booking_id);
                
                if (!$price_stmt->execute()) {
                    throw new Exception("Failed to update booking price.");
                }
            }
            
            $conn->commit();
            
            // Store boarding pass data
            $_SESSION['boarding_pass'] = [
                'flight_number' => $booking_details['flight_number'],
                'passenger_name' => $booking_details['first_name'] . ' ' . $booking_details['last_name'],
                'from' => $booking_details['departure_city'],
                'to' => $booking_details['arrival_city'],
                'date' => date('d M Y', strtotime($booking_details['departure_time'])),
                'time' => date('H:i', strtotime($booking_details['departure_time'])),
                'seat' => $seat_number ?? 'Not assigned',
                'booking_id' => $booking_id,
                'check_in_time' => date('Y-m-d H:i:s')
            ];
            
            $success = "Check-in completed successfully! Your boarding pass is ready.";
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollback();
            }
            $error = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Check-in - Aerion Airways</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .checkin-header {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .checkin-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 0 auto 2rem;
            max-width: 1000px;
        }

        .seat-selection {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e0e0e0;
        }

        .airplane-layout {
            position: relative;
            max-width: 800px;
            margin: 2rem auto;
            padding: 4rem;
            background: #fff;
            border-radius: 15px;
        }

        .airplane-silhouette {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 0;
        }

        .airplane-silhouette::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            height: 100%;
            background: #f8f9fa;
            border-radius: 150px 150px 0 0;
            z-index: -1;
        }

        .airplane-silhouette::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 30px;
            background: #f8f9fa;
            border-radius: 0 0 50px 50px;
            z-index: -1;
        }

        .seat-map {
            display: grid;
            grid-template-columns: 30px repeat(3, 1fr) 60px repeat(3, 1fr);
            gap: 5px;
            margin: 1rem 0;
            position: relative;
            z-index: 1;
        }

        .row-number {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-weight: 500;
            grid-column: 1;
        }

        .aisle {
            grid-column: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 0.8rem;
            border-left: 1px dashed #ccc;
            border-right: 1px dashed #ccc;
        }

        .seat {
            aspect-ratio: 1;
            border: 2px solid #ddd;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background-color: white;
        }

        .seat.business {
            border-color: #27ae60;
            color: #27ae60;
            background-color: #e8f6e9;
        }

        .seat.economy {
            border-color: #e67e22;
            color: #e67e22;
            background-color: #fef5ea;
        }

        .seat.exit {
            border-color: #8e44ad;
            color: #8e44ad;
            background-color: #f5eef8;
        }

        .seat:hover:not(.occupied) {
            transform: scale(1.1);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .seat.selected {
            background-color: #3498db;
            border-color: #3498db;
            color: white;
        }

        .seat.occupied {
            background-color: #e0e0e0;
            border-color: #bbb;
            cursor: not-allowed;
            color: #999;
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-box {
            width: 20px;
            height: 20px;
            border: 2px solid;
            border-radius: 3px;
        }

        .legend-box.business {
            border-color: #27ae60;
            background-color: #e8f6e9;
        }

        .legend-box.economy {
            border-color: #e67e22;
            background-color: #fef5ea;
        }

        .legend-box.exit {
            border-color: #8e44ad;
            background-color: #f5eef8;
        }

        .legend-box.occupied {
            border-color: #bbb;
            background-color: #e0e0e0;
        }

        .toilet-icon {
            color: #8e44ad;
            font-size: 1.5rem;
            margin: 0.5rem;
        }

        .flight-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .flight-info i {
            color: #3498db;
            margin-right: 0.5rem;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .boarding-pass, .boarding-pass * {
                visibility: visible;
            }
            .boarding-pass {
                position: absolute;
                left: 0;
                top: 0;
            }
            .download-boarding-pass {
                display: none;
            }
        }
        
        .boarding-pass {
            max-width: 800px;
            margin: 2rem auto;
        }
        
        .boarding-pass .card {
            border: 2px solid #3498db;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .boarding-pass .card-body {
            padding: 2rem;
        }
        
        .boarding-pass .card-title {
            color: #3498db;
            font-weight: 600;
        }
        
        .boarding-pass strong {
            color: #2c3e50;
        }

        .upcoming-flights {
            margin-bottom: 2rem;
        }

        .flight-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            max-width: 450px;
            margin: 0 auto 1rem;
        }

        .flight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .flight-card-header {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .flight-number {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .flight-number i {
            margin-right: 0.5rem;
        }

        .flight-card-body {
            padding: 1.5rem;
        }

        .flight-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .departure, .arrival {
            text-align: center;
            flex: 1;
        }

        .city {
            font-weight: 600;
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }

        .time {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .flight-line {
            position: relative;
            flex: 2;
            display: flex;
            align-items: center;
            padding: 0 1rem;
        }

        .flight-line .line {
            height: 2px;
            background: #e0e0e0;
            flex: 1;
        }

        .flight-line i {
            color: #3498db;
            font-size: 1.2rem;
            transform: rotate(90deg);
            margin: 0 0.5rem;
        }

        .flight-date {
            text-align: center;
            color: #7f8c8d;
            font-size: 0.9rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }

        .flight-date i {
            margin-right: 0.5rem;
            color: #3498db;
        }

        .no-flights {
            text-align: center;
            padding: 3rem 1rem;
        }

        .no-flights-image {
            max-width: 200px;
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }

        .no-flights h5 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .no-flights p {
            color: #7f8c8d;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .flight-card {
                margin-bottom: 1rem;
            }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .row-cols-md-2 {
            justify-content: center;
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
                        <a class="nav-link active" href="checkin.php">Online Check-in</a>
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
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Check-in Header -->
    <div class="checkin-header">
    <div class="container">
            <h1>Online Check-in</h1>
        </div>
    </div>
            
    <!-- Check-in Content -->
    <div class="container py-4">
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="max-width: 1000px; margin: 0 auto 1rem;">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="max-width: 1000px; margin: 0 auto 1rem;">
                    <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            
            <?php if ($boarding_pass): ?>
            <div class="boarding-pass">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="card-title mb-4">Boarding Pass</h3>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <strong>Flight:</strong> <?php echo htmlspecialchars($boarding_pass['flight_number']); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Seat:</strong> <?php echo htmlspecialchars($boarding_pass['seat']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <strong>Passenger:</strong> <?php echo htmlspecialchars($boarding_pass['passenger_name']); ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <strong>From:</strong> <?php echo htmlspecialchars($boarding_pass['from']); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>To:</strong> <?php echo htmlspecialchars($boarding_pass['to']); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Date:</strong> <?php echo htmlspecialchars($boarding_pass['date']); ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Time:</strong> <?php echo htmlspecialchars($boarding_pass['time']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode(json_encode($boarding_pass)); ?>" 
                                     alt="Boarding Pass QR Code" 
                                     class="img-fluid mb-3">
                                <a href="#" class="btn btn-primary download-boarding-pass" 
                                   onclick="window.print(); return false;">
                                    <i class="fas fa-download"></i> Download Boarding Pass
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <style>
                @media print {
                    body * {
                        visibility: hidden;
                    }
                    .boarding-pass, .boarding-pass * {
                        visibility: visible;
                    }
                    .boarding-pass {
                        position: absolute;
                        left: 0;
                        top: 0;
                    }
                    .download-boarding-pass {
                        display: none;
                    }
                }
                
                .boarding-pass {
                    max-width: 800px;
                    margin: 2rem auto;
                }
                
                .boarding-pass .card {
                    border: 2px solid #3498db;
                    border-radius: 15px;
                    overflow: hidden;
                }
                
                .boarding-pass .card-body {
                    padding: 2rem;
                }
                
                .boarding-pass .card-title {
                    color: #3498db;
                    font-weight: 600;
                }
                
                .boarding-pass strong {
                    color: #2c3e50;
                }
            </style>
            <?php endif; ?>
        <?php endif; ?>

        <div class="checkin-card">
            <?php if (!$booking && !$success): ?>
                <div class="upcoming-flights">
                    <h4 class="mb-4">Your Upcoming Flights</h4>
                    <?php if ($available_bookings && $available_bookings->num_rows > 0): ?>
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            <?php while ($row = $available_bookings->fetch_assoc()): ?>
                                <div class="col">
                                    <div class="flight-card">
                                        <div class="flight-card-header">
                                            <div class="flight-number">
                                                <i class="fas fa-plane"></i>
                                                Flight <?php echo htmlspecialchars($row['flight_number']); ?>
                                            </div>
                                            <form method="POST" action="checkin.php" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="search_booking" class="btn btn-primary btn-sm">
                                                    Check-in Now
                                                </button>
                                            </form>
                                        </div>
                                        <div class="flight-card-body">
                                            <div class="flight-route">
                                                <div class="departure">
                                                    <div class="city"><?php echo htmlspecialchars($row['departure_city']); ?></div>
                                                    <div class="time"><?php echo date('H:i', strtotime($row['departure_time'])); ?></div>
                                                </div>
                                                <div class="flight-line">
                                                    <div class="line"></div>
                                                    <i class="fas fa-plane"></i>
                                                </div>
                                                <div class="arrival">
                                                    <div class="city"><?php echo htmlspecialchars($row['arrival_city']); ?></div>
                                                    <div class="time"><?php echo date('H:i', strtotime($row['departure_time'] . ' +2 hours')); ?></div>
                                                </div>
                                            </div>
                                            <div class="flight-date">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('d M Y', strtotime($row['departure_time'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                </div>
            <?php else: ?>
                        <div class="no-flights">
                            <img src="assets/images/no-flights.svg" alt="No flights" class="no-flights-image">
                            <h5>No Upcoming Flights</h5>
                            <p>You don't have any upcoming flights available for check-in.</p>
                            <a href="booking.php" class="btn btn-primary">Book a Flight</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif ($booking && !$success): ?>
                <div class="flight-info">
                    <h4 class="mb-3">Flight Details</h4>
                    <p><i class="fas fa-plane"></i> Flight Number: <?php echo htmlspecialchars($booking['flight_number']); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> From: <?php echo htmlspecialchars($booking['departure_city']); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> To: <?php echo htmlspecialchars($booking['arrival_city']); ?></p>
                    <p><i class="fas fa-calendar"></i> Date: <?php echo date('d M Y', strtotime($booking['departure_time'])); ?></p>
                    <p><i class="fas fa-clock"></i> Time: <?php echo date('H:i', strtotime($booking['departure_time'])); ?></p>
                </div>

                <form method="POST" action="checkin.php">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    
                        <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="no_seat_preference" name="no_seat_preference">
                            <label class="form-check-label" for="no_seat_preference">
                                I don't have a seat preference (Save 20 лв.)
                            </label>
                        </div>
                    </div>

                    <div id="seatSelection" class="seat-selection">
                        <h5 class="mb-4">Select Your Seat</h5>
                        <div class="airplane-layout">
                            <div class="airplane-silhouette"></div>
                            <div class="seat-map">
                                <?php
                                // Get occupied seats
                                $occupied_query = "SELECT seat_number FROM check_ins WHERE booking_id != ? AND seat_number IS NOT NULL";
                                $occupied_stmt = $conn->prepare($occupied_query);
                                $occupied_stmt->bind_param("i", $booking['id']);
                                $occupied_stmt->execute();
                                $occupied_result = $occupied_stmt->get_result();
                                $occupied_seats = [];
                                while($row = $occupied_result->fetch_assoc()) {
                                    $occupied_seats[] = $row['seat_number'];
                                }

                                // Generate seat map
                                for ($row = 1; $row <= 16; $row++) {
                                    // Row number
                                    echo "<div class='row-number'>$row</div>";
                                    
                                    // Generate first 3 seats (ABC)
                                    for ($col = 0; $col < 3; $col++) {
                                        $seat = chr(65 + $col) . $row;
                                        $seatClass = 'seat ';
                                        
                                        // Add specific classes based on row
                                        if ($row == 1) {
                                            $seatClass .= 'business';
                                        } elseif ($row >= 2 && $row <= 5) {
                                            $seatClass .= 'economy';
                                        } elseif ($row >= 6 && $row <= 11) {
                                            $seatClass .= 'economy';
                                        } elseif ($row == 12 || $row == 13) {
                                            $seatClass .= 'exit';
                                        } else {
                                            $seatClass .= 'economy';
                                        }
                                        
                                        if (in_array($seat, $occupied_seats)) {
                                            $seatClass .= ' occupied';
                                        }
                                        
                                        echo "<div class='$seatClass' data-seat='$seat'>$seat</div>";
                                    }
                                    
                                    // Add aisle
                                    echo "<div class='aisle'></div>";
                                    
                                    // Generate last 3 seats (DEF)
                                    for ($col = 3; $col < 6; $col++) {
                                        $seat = chr(65 + $col) . $row;
                                        $seatClass = 'seat ';
                                        
                                        if ($row == 1) {
                                            $seatClass .= 'business';
                                        } elseif ($row >= 2 && $row <= 5) {
                                            $seatClass .= 'economy';
                                        } elseif ($row >= 6 && $row <= 11) {
                                            $seatClass .= 'economy';
                                        } elseif ($row == 12 || $row == 13) {
                                            $seatClass .= 'exit';
                                        } else {
                                            $seatClass .= 'economy';
                                        }
                                        
                                        if (in_array($seat, $occupied_seats)) {
                                            $seatClass .= ' occupied';
                                        }
                                        
                                        echo "<div class='$seatClass' data-seat='$seat'>$seat</div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="seat-legend">
                            <div class="legend-item">
                                <div class="legend-box business"></div>
                                <span>Business</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box economy"></div>
                                <span>Economy</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-box exit"></div>
                                <span>Exit Row</span>
                        </div>
                            <div class="legend-item">
                                <div class="legend-box occupied"></div>
                                <span>Occupied</span>
                            </div>
                        </div>
                        <input type="hidden" name="seat_number" id="selected_seat">
                            </div>

                    <div class="text-end mt-4">
                        <button type="submit" name="complete_checkin" class="btn btn-primary">Complete Check-in</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const noSeatPreferenceCheckbox = document.getElementById('no_seat_preference');
        const seatSelection = document.getElementById('seatSelection');
        const seats = document.querySelectorAll('.seat');
        const selectedSeatInput = document.getElementById('selected_seat');

        // Show seat selection by default
        seatSelection.style.display = 'block';

        // Toggle seat selection visibility
        noSeatPreferenceCheckbox.addEventListener('change', function() {
            seatSelection.style.display = this.checked ? 'none' : 'block';
            if (this.checked) {
                selectedSeatInput.value = '';
                seats.forEach(seat => seat.classList.remove('selected'));
            }
        });

        // Handle seat selection
        seats.forEach(seat => {
            seat.addEventListener('click', function() {
                if (noSeatPreferenceCheckbox.checked || this.classList.contains('occupied')) return;
                
                seats.forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                selectedSeatInput.value = this.dataset.seat;
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!noSeatPreferenceCheckbox.checked && !selectedSeatInput.value) {
                e.preventDefault();
                alert('Please select a seat or check "I don\'t have a seat preference".');
            }
        });
    });
    </script>
</body>
</html> 