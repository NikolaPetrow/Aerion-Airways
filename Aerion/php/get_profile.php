<?php
include 'db.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Debug информация
    error_log("User ID from session: " . $user_id);
    
    $sql = "SELECT first_name, last_name, email, created_at FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        error_log("Found user data: " . print_r($row, true));
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => [
                'firstName' => $row['first_name'],
                'lastName' => $row['last_name'],
                'email' => $row['email'],
                'memberSince' => $row['created_at']
            ]
        ]);
    } else {
        error_log("No user found for ID: " . $user_id);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
    
    $stmt->close();
} else {
    error_log("No user_id in session");
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
}

$conn->close();
?> 