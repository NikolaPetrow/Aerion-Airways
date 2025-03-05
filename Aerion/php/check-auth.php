<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] === true) {
    echo json_encode([
        'status' => 'success',
        'isLoggedIn' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name']
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'isLoggedIn' => false
    ]);
}
?> 