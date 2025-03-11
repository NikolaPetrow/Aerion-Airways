<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aerion Airways - Your Reliable Partner in the Sky</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Discover the World with Aerion Airways</h1>
            <p class="lead mb-5">Travel comfortably and safely to your dream destination</p>
            <div class="text-center mt-4">
                <a href="booking.php" class="btn btn-primary">Book Now</a>
            </div>
        </div>
    </section>

    <style>
    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
        height: 80vh;
        display: flex;
        align-items: center;
        color: white;
        position: relative;
    }

    .hero-section h1 {
        font-size: 3.5rem;
        font-weight: 600;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        margin-bottom: 1.5rem;
    }

    .hero-section .lead {
        font-size: 1.5rem;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        margin-bottom: 2rem;
    }

    .hero-section .btn-primary {
        font-size: 1.2rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 1rem 3rem;
        border-radius: 50px;
        background: linear-gradient(45deg, #3498db, #2ecc71);
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .hero-section .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        background: linear-gradient(45deg, #2980b9, #27ae60);
    }

    @media (max-width: 768px) {
        .hero-section h1 {
            font-size: 2.5rem;
        }
        .hero-section .lead {
            font-size: 1.2rem;
        }
        .hero-section .btn-primary {
            font-size: 1rem;
            padding: 0.8rem 2rem;
        }
    }
    </style>

    <!-- Main Features -->
    <section class="services py-5">
        <div class="container">
            <h2 class="text-center mb-5">Our Services</h2>
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="service-card text-center p-4 mb-4 h-100">
                        <div class="service-icon mb-4">
                            <i class="fas fa-ticket-alt fa-3x text-primary"></i>
                        </div>
                        <h3 class="service-title mb-3">Book Flight</h3>
                        <p class="service-description mb-4">Quick and easy online flight booking</p>
                        <a href="booking.php" class="btn btn-outline-primary rounded-pill px-4">Book Now</a>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="service-card text-center p-4 mb-4 h-100">
                        <div class="service-icon mb-4">
                            <i class="fas fa-check-circle fa-3x text-primary"></i>
                        </div>
                        <h3 class="service-title mb-3">Online Check-in</h3>
                        <p class="service-description mb-4">Check-in online and save time at the airport</p>
                        <a href="checkin.php" class="btn btn-outline-primary rounded-pill px-4">Check-in</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .service-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 50%;
        }

        .service-title {
            color: #2c3e50;
            font-weight: 600;
        }

        .service-description {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .btn-outline-primary {
            border-width: 2px;
            font-weight: 500;
            padding: 0.75rem 2rem;
        }

        .btn-outline-primary:hover {
            background-color: #3498db;
            border-color: #3498db;
            color: white;
            transform: translateY(-2px);
        }
    </style>

    <!-- Popular Destinations Section -->
    <section class="popular-destinations py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Popular Destinations</h2>
            <div class="row g-4">
                <!-- London -->
                <div class="col-md-4">
                    <div class="card destination-card h-100">
                        <img src="assets/images/destinations/london.jpg" class="card-img-top" alt="London">
                        <div class="card-body">
                            <h5 class="card-title">London</h5>
                            <p class="card-text">Experience the magic of Britain's capital. Visit Big Ben, Tower Bridge, and enjoy traditional afternoon tea.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="price-tag">
                                    <small>Starting from</small>
                                    <div class="price">$199</div>
                                </div>
                                <a href="booking.php?destination=LGW" class="btn btn-primary">Find Flights</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Paris -->
                <div class="col-md-4">
                    <div class="card destination-card h-100">
                        <img src="assets/images/destinations/paris.jpg" class="card-img-top" alt="Paris">
                        <div class="card-body">
                            <h5 class="card-title">Paris</h5>
                            <p class="card-text">The city of love and lights. Visit the Eiffel Tower, Louvre Museum, and enjoy French cuisine.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="price-tag">
                                    <small>Starting from</small>
                                    <div class="price">$189</div>
                                </div>
                                <a href="booking.php?destination=CDG" class="btn btn-primary">Find Flights</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Dubai -->
                <div class="col-md-4">
                    <div class="card destination-card h-100">
                        <img src="assets/images/destinations/dubai.jpg" class="card-img-top" alt="Dubai">
                        <div class="card-body">
                            <h5 class="card-title">Dubai</h5>
                            <p class="card-text">Experience luxury in the desert. Visit Burj Khalifa, Dubai Mall, and enjoy desert safaris.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="price-tag">
                                    <small>Starting from</small>
                                    <div class="price">$299</div>
                                </div>
                                <a href="booking.php?destination=DXB" class="btn btn-primary">Find Flights</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rome -->
                <div class="col-md-4">
                    <div class="card destination-card h-100">
                        <img src="assets/images/destinations/rome.jpg" class="card-img-top" alt="Rome">
                        <div class="card-body">
                            <h5 class="card-title">Rome</h5>
                            <p class="card-text">Explore the eternal city. Visit the Colosseum, Vatican, and enjoy authentic Italian pizza.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="price-tag">
                                    <small>Starting from</small>
                                    <div class="price">$179</div>
                                </div>
                                <a href="booking.php?destination=FCO" class="btn btn-primary">Find Flights</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Amsterdam -->
                <div class="col-md-4">
                    <div class="card destination-card h-100">
                        <img src="assets/images/destinations/amsterdam.jpg" class="card-img-top" alt="Amsterdam">
                        <div class="card-body">
                            <h5 class="card-title">Amsterdam</h5>
                            <p class="card-text">Discover the Venice of the North. Visit canals, museums, and enjoy Dutch culture.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="price-tag">
                                    <small>Starting from</small>
                                    <div class="price">$159</div>
                                </div>
                                <a href="booking.php?destination=AMS" class="btn btn-primary">Find Flights</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Barcelona -->
                <div class="col-md-4">
                    <div class="card destination-card h-100">
                        <img src="assets/images/destinations/barcelona.jpg" class="card-img-top" alt="Barcelona">
                        <div class="card-body">
                            <h5 class="card-title">Barcelona</h5>
                            <p class="card-text">Experience Catalan culture. Visit Sagrada Familia, Park Güell, and enjoy tapas.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="price-tag">
                                    <small>Starting from</small>
                                    <div class="price">$169</div>
                                </div>
                                <a href="booking.php?destination=BCN" class="btn btn-primary">Find Flights</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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

    <!-- Add this to your existing JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle destination selection
        const destinationLinks = document.querySelectorAll('.destination-card .btn-primary');
        destinationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const destination = this.href.split('=')[1];
                window.location.href = `booking.php?destination=${destination}`;
            });
        });
    });
    </script>
</body>
</html> 