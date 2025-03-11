<?php
session_start();
require_once 'db.php';

// Изчистване на всички сесийни данни
$_SESSION = array();

// Унищожаване на сесийното cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Унищожаване на сесията
session_destroy();

// Пренасочване към началната страница
header("Location: index.php");
exit();
?>