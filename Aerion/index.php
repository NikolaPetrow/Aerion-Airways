<?php
session_start();
$isLoggedIn = isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] === true;
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aerion Airways ® | Flying everywhere</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="hero">
        <nav class="navbar">
            <div class="logo">
                <img src="images/logo.png" alt="Aerion Airways Logo">
                <span class="company-name">Aerion Airways</span>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="checkin.html">Check-in</a></li>
                <li><a href="status.html">Flight Status</a></li>
                <li><a href="manage.html">Manage Booking</a></li>
                <li><a href="login.html" class="login-btn">Login</a></li>
            </ul>
        </nav>
        <div class="hero-text">
            <h1>Start planning your next trip</h1>
            <p>Discover your dream destination</p>
        </div>
        
        <div class="booking-container" id="booking-form">
            <form id="bookingForm" class="search-form">
                <div class="trip-selector">
                    <button type="button" class="trip-type-btn active" data-type="round">
                        <i class="fas fa-exchange-alt"></i> Round Trip
                    </button>
                    <button type="button" class="trip-type-btn" data-type="one-way">
                        <i class="fas fa-plane"></i> One way
                    </button>
                    <div class="passenger-select">
                        <button type="button" class="passenger-btn">
                            <i class="fas fa-user"></i>
                            <span class="passenger-summary">1 Passenger, Economy</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="passenger-dropdown">
                            <div class="passenger-category">
                                <div class="passenger-type">
                                    <h4>Adults</h4>
                                    <span>Age 12+</span>
                                </div>
                                <div class="passenger-count">
                                    <button type="button" class="count-btn" data-action="decrease" disabled>-</button>
                                    <span>1</span>
                                    <button type="button" class="count-btn" data-action="increase">+</button>
                                </div>
                            </div>
                            <div class="passenger-category">
                                <div class="passenger-type">
                                    <h4>Children</h4>
                                    <span>Age 2-11</span>
                                </div>
                                <div class="passenger-count">
                                    <button type="button" class="count-btn" data-action="decrease" disabled>-</button>
                                    <span>0</span>
                                    <button type="button" class="count-btn" data-action="increase">+</button>
                                </div>
                            </div>
                            <div class="passenger-category">
                                <div class="passenger-type">
                                    <h4>Infants</h4>
                                    <span>Under 2</span>
                                </div>
                                <div class="passenger-count">
                                    <button type="button" class="count-btn" data-action="decrease" disabled>-</button>
                                    <span>0</span>
                                    <button type="button" class="count-btn" data-action="increase">+</button>
                                </div>
                            </div>
                            <div class="cabin-class">
                                <h4>Cabin Class</h4>
                                <div class="class-options">
                                    <label class="class-option">
                                        <input type="radio" name="cabin" value="economy" checked>
                                        <span>Economy</span>
                                    </label>
                                    <label class="class-option">
                                        <input type="radio" name="cabin" value="premium">
                                        <span>Premium Economy</span>
                                    </label>
                                    <label class="class-option">
                                        <input type="radio" name="cabin" value="business">
                                        <span>Business</span>
                                    </label>
                                    <label class="class-option">
                                        <input type="radio" name="cabin" value="first">
                                        <span>First Class</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="search-grid">
                    <div class="search-row">
                        <div class="search-col">
                            <label>From</label>
                            <input type="text" placeholder="Origin" id="origin">
                        </div>
                        <button type="button" class="swap-btn">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        <div class="search-col">
                            <label>To</label>
                            <input type="text" placeholder="Destination" id="destination">
                        </div>
                    </div>

                    <div class="search-row dates">
                        <div class="search-col">
                            <label>Departure</label>
                            <div class="date-input">
                                <input type="text" placeholder="MM/DD/YYYY" id="departure-date">
                                <i class="far fa-calendar"></i>
                            </div>
                        </div>
                        <div class="search-col">
                            <label>Return</label>
                            <div class="date-input">
                                <input type="text" placeholder="MM/DD/YYYY" id="return-date">
                                <i class="far fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="search-flights-btn">Search flights</button>
            </form>
        </div>
    </header>

    <main>
        <section class="features">
            <div class="feature-card">
                <i class="fas fa-plane-departure"></i>
                <h3>Online Check-in</h3>
                <p>Check in online and save time at the airport</p>
                <a href="checkin.html" class="feature-btn">Check-in Now</a>
            </div>

            <div class="feature-card">
                <i class="fas fa-map-marked-alt"></i>
                <h3>Flight Status</h3>
                <p>Track your flight status in real-time</p>
                <a href="status.html" class="feature-btn">Track Flight</a>
            </div>

            <div class="feature-card">
                <i class="fas fa-suitcase-rolling"></i>
                <h3>Manage Booking</h3>
                <p>View or modify your booking details</p>
                <a href="manage.html" class="feature-btn">Manage Now</a>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Aerion Airways. All rights reserved.</p>
    </footer>

    <script src="js/airport-search.js"></script>
    <script src="js/booking.js"></script>
    <script src="js/auth-status.js"></script>
    <script src="js/buttons.js"></script>

    <!-- Добавете това след навигацията -->
    <div id="userWelcome" class="user-welcome" style="display: none;">
        <h2>Welcome back, <span id="userName"></span>!</h2>
    </div>

    <script src="js/index.js"></script>
</body>
</html> 