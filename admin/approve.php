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
    header("Location: " . $redirect_page . "?status=error&message=No user ID provided for {$role_name} approval.");
    exit();
}

// Sanitize and validate the ID
$user_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

if ($user_id === false || $user_id <= 0) {
    header("Location: " . $redirect_page . "?status=error&message=Invalid ID format for {$role_name}.");
    exit();
}

// --- Prepare and Execute UPDATE Statement ---

// Set status to the string 'approved'
$sql = "UPDATE users SET status = 'approved' WHERE id = ?";
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
        // Success: redirect with a success message
        header("Location: " . $redirect_page . "?status=success&message={$role_name} ID {$user_id_safe} has been approved.");
    } else {
        // No row updated (either ID not found or status was already 'approved')
        header("Location: " . $redirect_page . "?status=warning&message={$role_name} ID {$user_id_safe} not found or already approved.");
    }
} else {
    // Execution failure
    header("Location: " . $redirect_page . "?status=error&message={$role_name} approval failed: " . $stmt->error);
}

// --- Cleanup ---
$stmt->close();
$conn->close();
exit();
