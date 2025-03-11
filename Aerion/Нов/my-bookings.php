<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: login.php");
    exit();
}

// Get user's bookings with flight details
$booking_query = "SELECT b.*, f.flight_number, f.departure_city, f.arrival_city, 
                        f.departure_time, f.arrival_time, 
                        TIMEDIFF(f.arrival_time, f.departure_time) as flight_duration
                 FROM bookings b
                 JOIN flights f ON b.flight_id = f.id
                 WHERE b.user_id = ?
                 ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($booking_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Aerion Airways</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .booking-header {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .booking-info {
            padding: 2rem;
        }

        .flight-path {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .flight-path i {
            color: #3498db;
            margin: 0 1rem;
        }

        .flight-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-size: 1.1rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .booking-status {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            text-align: center;
            width: fit-content;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .no-bookings {
            text-align: center;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 15px;
            margin-top: 2rem;
        }

        .no-bookings i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
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
                                <li><a class="dropdown-item active" href="my-bookings.php"><i class="fas fa-ticket-alt"></i> My Bookings</a></li>
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

    <!-- Bookings Header -->
    <div class="booking-header">
        <div class="container">
            <h1>My Bookings</h1>
        </div>
    </div>

    <!-- Bookings Content -->
    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if ($bookings->num_rows > 0): ?>
            <?php while ($booking = $bookings->fetch_assoc()): ?>
                <div class="booking-card">
                    <div class="booking-info">
                        <div class="flight-path">
                            <span><?php echo htmlspecialchars($booking['departure_city']); ?></span>
                            <i class="fas fa-plane"></i>
                            <span><?php echo htmlspecialchars($booking['arrival_city']); ?></span>
                        </div>
                        <div class="flight-details">
                            <div class="detail-item">
                                <span class="detail-label">Flight Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($booking['flight_number']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date</span>
                                <span class="detail-value"><?php echo date('d M Y', strtotime($booking['departure_time'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Departure Time</span>
                                <span class="detail-value"><?php echo date('H:i', strtotime($booking['departure_time'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Arrival Time</span>
                                <span class="detail-value"><?php echo date('H:i', strtotime($booking['arrival_time'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Duration</span>
                                <span class="detail-value"><?php echo $booking['flight_duration']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Passengers</span>
                                <span class="detail-value"><?php echo $booking['num_passengers']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total Price</span>
                                <span class="detail-value"><?php echo number_format($booking['total_price'], 2); ?> лв.</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Status</span>
                                <span class="booking-status status-confirmed"><?php echo ucfirst($booking['status']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-bookings">
                <i class="fas fa-ticket-alt"></i>
                <h3>No Bookings Found</h3>
                <p class="text-muted">You haven't made any bookings yet.</p>
                <a href="booking.php" class="btn btn-primary mt-3">Book a Flight</a>
            </div>
        <?php endif; ?>
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