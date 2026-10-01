<?php
session_start();

// --- Dynamic Redirect Page and Item Type ---
// Check URL parameters for the redirect page name and append '.php'. Defaults to 'products.php'.
$redirect_page = isset($_GET['page']) && !empty($_GET['page']) ? $_GET['page'] . '.php' : "products.php";

// Check URL parameters for the item type name for messaging. Defaults to 'Product'.
// We use 'item_type' here to distinguish product/item scripts from user/role scripts.
$item_type = isset($_GET['item_type']) && !empty($_GET['item_type']) ? $_GET['item_type'] : "Product";
// ------------------------------------------

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "craftconnect";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header("Location: " . $redirect_page . "?status=error&message=Database Connection failed.");
    die("Database Connection failed: " . $conn->connect_error);
}

// Check if an ID was provided via the GET request
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . $redirect_page . "?status=error&message=No ID provided for {$item_type} removal.");
    exit();
}

// Sanitize and validate the ID
$item_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

if ($item_id === false || $item_id <= 0) {
    header("Location: " . $redirect_page . "?status=error&message=Invalid ID format for {$item_type}.");
    exit();
}

// --- Prepare and Execute UPDATE Statement ---

// TARGETING THE 'products' TABLE
$sql = "UPDATE products SET status = 'removed' WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    header("Location: " . $redirect_page . "?status=error&message=SQL preparation failed: " . $conn->error);
    $conn->close();
    exit();
}

// Bind the product ID (integer type 'i') to the statement
$stmt->bind_param("i", $item_id);

// Execute the statement
if ($stmt->execute()) {
    $rows_affected = $stmt->affected_rows;
    $item_id_safe = htmlspecialchars($item_id);

    if ($rows_affected > 0) {
        // Success: dynamic message
        header("Location: " . $redirect_page . "?status=success&message={$item_type} ID {$item_id_safe} has been removed.");
    } else {
        // Warning: dynamic message
        header("Location: " . $redirect_page . "?status=warning&message={$item_type} ID {$item_id_safe} not found or already removed.");
    }
} else {
    // Execution failure: dynamic message
    header("Location: " . $redirect_page . "?status=error&message={$item_type} removal failed: " . $stmt->error);
}

// --- Cleanup ---
$stmt->close();
$conn->close();
exit();
