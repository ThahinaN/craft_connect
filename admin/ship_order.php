<?php
// Start the session to maintain admin authentication status
session_start();

// Check if the administrator is logged in
if (!isset($_SESSION['admin_id'])) {
    // If not logged in, redirect them to the login page
    header("Location: ../home/login.html");
    exit();
}

// --- Database Connection ---
$conn = mysqli_connect("localhost","root","","craftconnect");
if(!$conn){
    // Log the connection error and stop execution
    error_log("DB connection failed: " . mysqli_connect_error());
    die("Database connection failed. Check your configuration.");
}

// Check if an order ID was passed in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // If no ID is provided, redirect back to the orders list
    header("Location: orders.php");
    exit();
}

// Sanitize and validate the order ID
$order_id = (int)$_GET['id'];
$new_status = 'shipped'; // The target status

// Prepare the SQL statement to update the order status
// We use prepared statements for security to prevent SQL injection
$sql = "UPDATE orders SET status = ? WHERE id = ?";

// Prepare the statement
if ($stmt = $conn->prepare($sql)) {
    // Bind the parameters (s = string, i = integer)
    $stmt->bind_param("si", $new_status, $order_id);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Log successful update (optional, but good practice)
        error_log("Order ID {$order_id} status updated to 'shipped' by Admin ID: {$_SESSION['admin_id']}");
    } else {
        // Log the execution error
        error_log("Error updating order ID {$order_id}: " . $stmt->error);
    }

    // Close the statement
    $stmt->close();
} else {
    // Log the preparation error
    error_log("Error preparing statement: " . $conn->error);
}

// Close the database connection
$conn->close();

// Always redirect back to the orders page after the action is complete
header("Location: orders.php");
exit();

?>
