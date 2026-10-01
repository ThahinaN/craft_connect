<?php
session_start();

$role = $_SESSION['role'] ?? '';  // get role before destroying session

session_unset();    // remove all session variables
session_destroy();  // destroy session

// Role-based redirect
if ($role === 'admin') {
    header("Location: login.html?role=admin");
} elseif ($role === 'seller') {
    header("Location: login.html?role=seller");
} elseif ($role === 'buyer') {
    header("Location: login.html?role=buyer");
} else {
    header("Location: login.html");
}
exit();
?>



