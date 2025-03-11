<?php
session_start();
require_once 'db.php';

// Check if user is logged in and has a booking reference
if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['booking_id'])) {
    header("Location: index.php");
    exit();
}

// Get booking details
$booking_query = "SELECT b.*, f.*,
                 dep.city as departure_city, dep.airport_code as departure_code,
                 arr.city as arrival_city, arr.airport_code as arrival_code
                 FROM bookings b
                 JOIN flights f ON b.flight_id = f.id
                 JOIN airports dep ON f.departure_airport_id = dep.id
                 JOIN airports arr ON f.arrival_airport_id = arr.id
                 WHERE b.id = ?";
$stmt = $conn->prepare($booking_query);
$stmt->bind_param("i", $_SESSION['booking_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Aerion Airways</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .confirmation-header {
            background: linear-gradient(135deg, #2ecc71, #3498db);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .confirmation-box {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .booking-reference {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .booking-reference .number {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            letter-spacing: 2px;
        }
        .flight-details {
            margin-top: 30px;
        }
        .flight-details .row {
            margin-bottom: 15px;
        }
        .success-icon {
            font-size: 4rem;
            color: #2ecc71;
            margin-bottom: 20px;
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
                        <a class="nav-link" href="check-booking.php">Check Booking</a>
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

    <!-- Confirmation Header -->
    <div class="confirmation-header">
        <div class="container">
            <h1>Booking Confirmed!</h1>
        </div>
    </div>

    <div class="container">
        <div class="confirmation-box">
            <div class="text-center">
                <i class="fas fa-check-circle success-icon"></i>
                <h2 class="mb-4">Thank you for booking with Aerion Airways</h2>
                <p class="text-muted">Your booking has been confirmed and your tickets have been sent to your email.</p>
            </div>

            <div class="booking-reference">
                <div class="text-muted mb-2">Booking Reference</div>
                <div class="number"><?php echo $_SESSION['booking_id']; ?></div>
                <div class="text-muted mt-2">Please save this number for future reference</div>
            </div>

            <div class="flight-details">
                <h4 class="mb-4">Flight Details</h4>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Flight Number:</strong> <?php echo $booking['flight_number']; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Date:</strong> <?php echo date('d M Y', strtotime($booking['departure_time'])); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <strong>Route:</strong>
                        <?php echo $booking['departure_city']; ?> (<?php echo $booking['departure_code']; ?>) →
                        <?php echo $booking['arrival_city']; ?> (<?php echo $booking['arrival_code']; ?>)
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Departure:</strong> <?php echo date('H:i', strtotime($booking['departure_time'])); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Arrival:</strong> <?php echo date('H:i', strtotime($booking['arrival_time'])); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Total Price:</strong> <?php echo number_format($booking['total_price'], 2); ?> лв.
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong> <span class="text-success">Confirmed</span>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="my-bookings.php" class="btn btn-primary">View My Bookings</a>
                <a href="index.php" class="btn btn-outline-primary ms-2">Return to Homepage</a>
            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 