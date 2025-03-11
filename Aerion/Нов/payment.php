<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: login.php");
    exit();
}

// Calculate total price
$base_price = isset($_SESSION['booking_data']['price']) ? $_SESSION['booking_data']['price'] : 0;
$total_price = $base_price;

// Add baggage costs if selected
if (isset($_SESSION['passenger_data']['passengers'])) {
    foreach ($_SESSION['passenger_data']['passengers'] as $passenger) {
        if (isset($passenger['baggage'])) {
            switch ($passenger['baggage']) {
                case '10':
                    $total_price += 20;
                    break;
                case '20':
                    $total_price += 40;
                    break;
                case '26':
                    $total_price += 60;
                    break;
                case '32':
                    $total_price += 80;
                    break;
            }
        }
    }
}

// Store passenger details in session if form was submitted from booking-steps.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['payment_submitted'])) {
    $_SESSION['passenger_data'] = $_POST;
}

// Process payment and save booking when payment form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_submitted'])) {
    if (isset($_SESSION['booking_data']) && isset($_SESSION['passenger_data'])) {
        $flight_id = $_SESSION['booking_data']['flight_id'];
        $user_id = $_SESSION['user_id'];
        $booking_date = date('Y-m-d H:i:s');
        $status = 'confirmed';
        $num_passengers = count($_SESSION['passenger_data']['passengers']);

        // Insert booking
        $booking_query = "INSERT INTO bookings (user_id, flight_id, booking_date, total_price, status, num_passengers) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($booking_query);
        $stmt->bind_param("iisdsi", $user_id, $flight_id, $booking_date, $total_price, $status, $num_passengers);
        
        if ($stmt->execute()) {
            // Clear booking session data
            unset($_SESSION['booking_data']);
            unset($_SESSION['passenger_data']);
            
            // Set success message and redirect to my-bookings
            $_SESSION['success_message'] = "Your booking has been confirmed successfully!";
            $_SESSION['booking_success'] = true;
            header("Location: my-bookings.php");
            exit();
        }
    }
}

// Get flight details from session
if (isset($_SESSION['booking_data']['flight_id'])) {
    $flight_query = "SELECT f.*, 
                    TIMEDIFF(f.arrival_time, f.departure_time) as flight_duration
                    FROM flights f 
                    WHERE f.id = ?";
    $stmt = $conn->prepare($flight_query);
    $stmt->bind_param("i", $_SESSION['booking_data']['flight_id']);
    $stmt->execute();
    $flight = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Aerion Airways</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* Remove navigation and footer styles as they are in style.css */
        
        /* Booking Header and Progress Styles */
        .booking-header {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .booking-progress {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: relative;
            margin-top: -50px;
            z-index: 1;
        }

        .booking-progress::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 15%;
            right: 15%;
            height: 2px;
            background: #e0e0e0;
            transform: translateY(-50%);
            z-index: -1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step-icon i {
            font-size: 1.2rem;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .step-label {
            color: #6c757d;
            font-weight: 500;
            font-size: 0.9rem;
            text-align: center;
        }

        .step.completed .step-icon {
            background: #2ecc71;
            border-color: #2ecc71;
        }

        .step.completed .step-icon i {
            color: white;
        }

        .step.completed .step-label {
            color: #2ecc71;
        }

        .step.active .step-icon {
            background: #3498db;
            border-color: #3498db;
            transform: scale(1.1);
        }

        .step.active .step-icon i {
            color: white;
        }

        .step.active .step-label {
            color: #3498db;
            font-weight: 600;
        }

        .payment-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .payment-summary {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .payment-summary .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.2rem;
            padding-bottom: 1.2rem;
            border-bottom: 2px dashed #e0e0e0;
            font-size: 1.1rem;
        }

        .payment-summary .total {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1.4rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e0e0e0;
            color: #2c3e50;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .payment-method {
            position: relative;
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .payment-method label {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .payment-method input[type="radio"]:checked + label {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            border-color: transparent;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .payment-method i {
            font-size: 1.8rem;
            margin-right: 1rem;
        }

        .card-details {
            margin-top: 2rem;
        }

        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-control:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
            color: #6c757d;
        }

        .alert-info {
            background: linear-gradient(135deg, #e8f4fd, #e3f8ef);
            border: none;
            border-radius: 10px;
            color: #2c3e50;
        }

        .pay-button {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 1.2rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            margin-top: 2rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .pay-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
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
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                                    <li><a class="dropdown-item" href="admin.php"><i class="fas fa-cog"></i> Admin Panel</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="my-bookings.php"><i class="fas fa-ticket-alt"></i> My Bookings</a></li>
                                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                <?php endif; ?>
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

    <!-- Header -->
    <div class="booking-header">
        <div class="container">
            <h1>Payment</h1>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="container mb-4">
        <div class="booking-progress">
            <div class="step completed">
                <div class="step-icon">
                    <i class="fas fa-plane"></i>
                </div>
                <div class="step-label">Select Flight</div>
            </div>
            <div class="step completed">
                <div class="step-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="step-label">Passenger Details</div>
            </div>
            <div class="step active">
                <div class="step-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="step-label">Payment</div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($flight)): ?>
        <div class="row">
            <div class="col-md-8">
                <div class="payment-section">
                    <h3 class="mb-4">Payment Method</h3>
                    <form action="payment.php" method="POST">
                        <input type="hidden" name="payment_submitted" value="1">
                        <input type="hidden" name="flight_id" value="<?php echo $_SESSION['booking_data']['flight_id']; ?>">
                        <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
                        
                        <div class="payment-methods mb-4">
                            <div class="payment-method">
                                <input type="radio" id="credit-card" name="payment_method" value="credit-card" checked>
                                <label for="credit-card">
                                    <i class="fas fa-credit-card"></i>
                                    Credit Card
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="paypal" name="payment_method" value="paypal">
                                <label for="paypal">
                                    <i class="fab fa-paypal"></i>
                                    PayPal
                                </label>
                            </div>
                        </div>

                        <div class="card-details">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <input type="text" class="form-control" placeholder="4242 4242 4242 4242" disabled>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="text" class="form-control" placeholder="MM/YY" disabled>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="text" class="form-control" placeholder="CVV" disabled>
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control" placeholder="Cardholder Name" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            This is a prototype version. No actual payment will be processed.
                        </div>

                        <button type="submit" class="pay-button">
                            <i class="fas fa-check-circle me-2"></i>Complete Booking (<?php echo number_format($total_price, 2); ?> лв.)
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="payment-section">
                    <h3 class="mb-4">Payment Summary</h3>
                    <div class="payment-summary">
                        <div class="item">
                            <span>Flight Ticket</span>
                            <span><?php echo number_format($base_price, 2); ?> лв.</span>
                        </div>
                        <?php if (isset($_SESSION['passenger_data']['passengers'])): ?>
                            <?php foreach ($_SESSION['passenger_data']['passengers'] as $index => $passenger): ?>
                                <?php if (isset($passenger['baggage'])): ?>
                                    <div class="item">
                                        <span>Baggage (<?php echo $passenger['baggage']; ?> kg)</span>
                                        <span>
                                            <?php
                                            switch ($passenger['baggage']) {
                                                case '10': echo '+20.00 лв.'; break;
                                                case '20': echo '+40.00 лв.'; break;
                                                case '26': echo '+60.00 лв.'; break;
                                                case '32': echo '+80.00 лв.'; break;
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div class="total">
                            <span>Total</span>
                            <span><?php echo number_format($total_price, 2); ?> лв.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">
                No flight selected. Please <a href="booking.php">select a flight</a> first.
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p class="mb-2"><i class="fas fa-phone me-2"></i>+359 2 123 4567</p>
                    <p class="mb-2"><i class="fas fa-envelope me-2"></i>info@aerion-airways.com</p>
                    <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Sofia, Bulgaria</p>
                </div>
                <div class="col-md-4">
                    <h5>Useful Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="about-us.php" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="terms.php" class="text-white text-decoration-none">Terms & Conditions</a></li>
                        <li class="mb-2"><a href="privacy.php" class="text-white text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="faq.php" class="text-white text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Follow Us</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Aerion Airways. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 