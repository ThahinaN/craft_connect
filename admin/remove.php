<?php
session_start();

// --- Dynamic Redirect Page and Role Name ---
// Check URL parameters for the redirect page name and append '.php'. Defaults to 'sellers.php'.
$redirect_page = isset($_GET['page']) && !empty($_GET['page']) ? $_GET['page'] . '.php' : "sellers.php";

// Check URL parameters for the user role name for messaging. Defaults to 'Seller'.
$role_name = isset($_GET['role_name']) && !empty($_GET['role_name']) ? $_GET['role_name'] : "Seller";
// ------------------------------------------

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "craftconnect";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Redirect to the determined page with an error
    header("Location: " . $redirect_page . "?status=error&message=Database Connection failed.");
    die("Database Connection failed: " . $conn->connect_error);
}

// Check if an ID was provided via the GET request
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Message now uses dynamic $role_name
    header("Location: " . $redirect_page . "?status=error&message=No user ID provided for {$role_name} removal.");
    exit();
}

// Sanitize and validate the ID
$user_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

if ($user_id === false || $user_id <= 0) {
    // Message now uses dynamic $role_name
    header("Location: " . $redirect_page . "?status=error&message=Invalid ID format for {$role_name}.");
    exit();
}

// --- Prepare and Execute UPDATE Statement ---

// CORRECT: Update the 'users' table and set status to the string 'removed'
$sql = "UPDATE users SET status = 'removed' WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header("Location: " . $redirect_page . "?status=error&message=SQL preparation failed: " . $conn->error);
    $conn->close();
    exit();
}

// Bind the user ID (integer type 'i') to the statement
$stmt->bind_param("i", $user_id);

// Execute the statement
if ($stmt->execute()) {
    $rows_affected = $stmt->affected_rows;
    $user_id_safe = htmlspecialchars($user_id);

    if ($rows_affected > 0) {
        // Success: message now uses dynamic $role_name
        header("Location: " . $redirect_page . "?status=success&message={$role_name} ID {$user_id_safe} has been removed.");
    } else {
        // Warning: message now uses dynamic $role_name
        header("Location: " . $redirect_page . "?status=warning&message={$role_name} ID {$user_id_safe} not found or already removed.");
    }
} else {
    // Execution failure: message now uses dynamic $role_name
    header("Location: " . $redirect_page . "?status=error&message={$role_name} removal failed: " . $stmt->error);
}

// --- Cleanup ---
$stmt->close();
$conn->close();
exit();
