<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: login.php");
    exit();
}

// Check if we have booking data
if (!isset($_SESSION['booking_data']) || !isset($_POST['passengers'])) {
    header("Location: booking.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Create the booking
    $booking_query = "INSERT INTO bookings (user_id, flight_id, passengers, total_price, fare_type) 
                     VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($booking_query);
    $stmt->bind_param("iiids", 
        $_SESSION['user_id'],
        $_SESSION['booking_data']['flight_id'],
        $_SESSION['booking_data']['passengers'],
        $_SESSION['booking_data']['price'] * $_SESSION['booking_data']['passengers'],
        $_SESSION['booking_data']['fare_type']
    );
    $stmt->execute();
    $booking_id = $conn->insert_id;

    // Add passenger information
    $passenger_query = "INSERT INTO passengers (booking_id, first_name, last_name, gender, special_assistance) 
                       VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($passenger_query);

    foreach ($_POST['passengers'] as $passenger) {
        $stmt->bind_param("issss", 
            $booking_id,
            $passenger['first_name'],
            $passenger['last_name'],
            $passenger['gender'],
            $passenger['special_assistance']
        );
        $stmt->execute();
    }

    // Update available seats
    $update_seats = "UPDATE flights 
                    SET seats_available = seats_available - ? 
                    WHERE flight_id = ?";
    $stmt = $conn->prepare($update_seats);
    $stmt->bind_param("ii", 
        $_SESSION['booking_data']['passengers'],
        $_SESSION['booking_data']['flight_id']
    );
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Clear booking data from session
    unset($_SESSION['booking_data']);

    // Redirect to booking confirmation
    $_SESSION['success_message'] = "Your booking has been confirmed!";
    header("Location: my-bookings.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error_message'] = "An error occurred while processing your booking. Please try again.";
    header("Location: my-bookings.php");
    exit();
}
?> 