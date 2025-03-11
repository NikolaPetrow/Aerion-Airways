<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: login.php");
    exit();
}

// Check if we have flight booking data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flight_id'])) {
    $_SESSION['booking_data'] = $_POST;
}

// Get flight details
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

// Get user's bookings
$booking_query = "SELECT b.*, f.*
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
        .booking-header {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .flight-info {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }
        .flight-path {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .flight-details {
            font-size: 1rem;
            color: #2c3e50;
        }
        .flight-details > div {
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
        }
        .flight-details > div:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .no-bookings {
            text-align: center;
            padding: 50px 20px;
            background: #f8f9fa;
            border-radius: 15px;
            margin-top: 20px;
        }
        .no-bookings i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .passenger-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .passenger-section h3 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            background-color: white;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .gender-select {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
        }

        .gender-option {
            flex: 1;
            position: relative;
        }

        .gender-option input[type="radio"] {
            display: none;
        }

        .gender-option label {
            display: block;
            padding: 1rem;
            text-align: center;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .gender-option input[type="radio"]:checked + label {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .special-assistance {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            margin: 1rem 0;
        }

        .baggage-section {
            margin-top: 2rem;
        }

        .baggage-section h4 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .baggage-options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .baggage-option input[type="radio"] {
            display: none;
        }

        .baggage-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
        }

        .baggage-option input[type="radio"]:checked + label {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .baggage-option .price {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }

        .baggage-option input[type="radio"]:checked + label .price {
            color: white;
        }

        .cabin-baggage-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .cabin-option label {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cabin-option input[type="radio"]:checked + label {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .cabin-option .icon {
            font-size: 2rem;
            margin-right: 1rem;
        }

        .cabin-option .details {
            flex: 1;
        }

        .cabin-option .title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .cabin-option .description {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .baggage-options {
                grid-template-columns: repeat(2, 1fr);
            }

            .cabin-baggage-options {
                grid-template-columns: 1fr;
            }
        }

        .continue-btn {
            background: linear-gradient(to right, #3498db, #2ecc71);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 1rem 2rem;
            font-weight: 600;
            width: 100%;
            margin-top: 2rem;
            transition: all 0.3s ease;
        }

        .continue-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
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
            position: relative;
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
            transition: all 0.3s ease;
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

        @media (max-width: 768px) {
            .booking-progress {
                padding: 1.5rem 1rem;
                margin-top: -30px;
            }

            .step-icon {
                width: 40px;
                height: 40px;
            }

            .step-icon i {
                font-size: 1rem;
            }

            .step-label {
                font-size: 0.8rem;
            }

            .booking-progress::before {
                left: 20%;
                right: 20%;
            }
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
            <h1>Passenger Details</h1>
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
            <div class="step active">
                <div class="step-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="step-label">Passenger Details</div>
            </div>
            <div class="step">
                <div class="step-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="step-label">Payment</div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Flight Summary -->
        <?php if (isset($flight)): ?>
        <div class="flight-info mb-4">
            <div class="flight-path">
                <?php echo $flight['departure_city']; ?> → 
                <?php echo $flight['arrival_city']; ?>
            </div>
            <div class="flight-details">
                <div>
                    <span>Flight Number:</span>
                    <span><?php echo $flight['flight_number']; ?></span>
                </div>
                <div>
                    <span>Date:</span>
                    <span><?php echo date('d M Y', strtotime($flight['departure_time'])); ?></span>
                </div>
                <div>
                    <span>Time:</span>
                    <span><?php echo date('H:i', strtotime($flight['departure_time'])); ?> - <?php echo date('H:i', strtotime($flight['arrival_time'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Passenger Form -->
        <form method="POST" action="payment.php" class="passenger-form">
            <?php 
            $num_passengers = isset($_SESSION['booking_data']['passengers']) ? $_SESSION['booking_data']['passengers'] : 1;
            for($i = 1; $i <= $num_passengers; $i++): 
            ?>
            <div class="passenger-section">
                <h3>Passenger <?php echo $i; ?> (ADULT)</h3>
                
                <!-- Name Fields -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" placeholder="First name" name="passengers[<?php echo $i; ?>][first_name]" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" placeholder="Last name" name="passengers[<?php echo $i; ?>][last_name]" required>
                    </div>
                </div>

                <!-- Gender Selection -->
                <div class="gender-select">
                    <div class="gender-option">
                        <input type="radio" id="female_<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][gender]" value="female" required>
                        <label for="female_<?php echo $i; ?>">Female</label>
                    </div>
                    <div class="gender-option">
                        <input type="radio" id="male_<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][gender]" value="male" required>
                        <label for="male_<?php echo $i; ?>">Male</label>
                    </div>
                </div>

                <!-- Special Assistance -->
                <div class="special-assistance">
                    <label class="d-flex align-items-center gap-2">
                        <input type="checkbox" name="passengers[<?php echo $i; ?>][special_assistance]">
                        <span>Do you need Special assistance at the airport?</span>
                    </label>
                </div>

                <!-- Checked Baggage -->
                <div class="baggage-section">
                    <h4>Checked-in baggage</h4>
                    <div class="baggage-options">
                        <div class="baggage-option">
                            <input type="radio" id="bag_10_<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][baggage]" value="10">
                            <label for="bag_10_<?php echo $i; ?>">
                                <i class="fas fa-suitcase fa-2x"></i>
                                <span class="mt-2">10 kg</span>
                                <span class="price">+20.00 лв.</span>
                            </label>
                        </div>
                        <div class="baggage-option">
                            <input type="radio" id="bag_20_<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][baggage]" value="20">
                            <label for="bag_20_<?php echo $i; ?>">
                                <i class="fas fa-suitcase fa-2x"></i>
                                <span class="mt-2">20 kg</span>
                                <span class="price">+40.00 лв.</span>
                            </label>
                        </div>
                        <div class="baggage-option">
                            <input type="radio" id="bag_26_<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][baggage]" value="26">
                            <label for="bag_26_<?php echo $i; ?>">
                                <i class="fas fa-suitcase fa-2x"></i>
                                <span class="mt-2">26 kg</span>
                                <span class="price">+60.00 лв.</span>
                            </label>
                        </div>
                        <div class="baggage-option">
                            <input type="radio" id="bag_32_<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][baggage]" value="32">
                            <label for="bag_32_<?php echo $i; ?>">
                                <i class="fas fa-suitcase fa-2x"></i>
                                <span class="mt-2">32 kg</span>
                                <span class="price">+80.00 лв.</span>
                            </label>
                        </div>
                    </div>
                    <div class="special-assistance">
                        <label class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="passengers[<?php echo $i; ?>][no_baggage]">
                            <span>I don't want a checked-in bag</span>
                        </label>
                    </div>
                </div>

                <!-- Cabin Baggage -->
                <div class="baggage-section">
                    <h4>Cabin baggage</h4>
                    <div class="cabin-baggage-options">
                        <div class="cabin-option">
                            <input type="radio" id="cabin_2_<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][cabin_baggage]" value="2" checked>
                            <label for="cabin_2_<?php echo $i; ?>">
                                <i class="fas fa-briefcase icon"></i>
                                <div class="details">
                                    <div class="title">I need 2 cabin bags</div>
                                    <div class="description">Free carry-on bag (40 x 30 x 20 cm) + extra cabin bag (55 x 40 x 23 cm)</div>
                                </div>
                            </label>
                        </div>
                        <div class="cabin-option">
                            <input type="radio" id="cabin_1_<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][cabin_baggage]" value="1">
                            <label for="cabin_1_<?php echo $i; ?>">
                                <i class="fas fa-briefcase icon"></i>
                                <div class="details">
                                    <div class="title">No, free carry-on bag is enough</div>
                                    <div class="description">One small bag (40 x 30 x 20 cm)</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php endfor; ?>

            <button type="submit" class="continue-btn">
                Continue to Payment
            </button>
        </form>
        <?php else: ?>
            <div class="alert alert-warning">
                No flight selected. Please <a href="booking.php">select a flight</a> first.
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