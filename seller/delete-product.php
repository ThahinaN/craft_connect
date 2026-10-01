<?php
// delete-product.php
session_start();
$conn = mysqli_connect("localhost", "root", "", "craftconnect");
if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}

// 1. Authorization check
if (!isset($_SESSION['seller_id'])) {
    header("Location: ../home/login.html");
    exit();
}

// 2. Input validation: THIS IS WHERE YOUR ERROR MESSAGE ORIGINATES
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // If no ID or invalid ID, redirect immediately.
    header("Location: my-products.php?message=" . urlencode("Invalid product ID."));
    exit();
}

$product_id = (int)$_GET['id'];
$seller_id = $_SESSION['seller_id'];
$message = "";

// --- Start Deletion Process ---

// A. Fetch image URL before deleting the product
$fetch_sql = "SELECT image_url FROM products WHERE id = ? AND seller_id = ?";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bind_param("ii", $product_id, $seller_id);
$fetch_stmt->execute();
$fetch_result = $fetch_stmt->get_result();
$product = $fetch_result->fetch_assoc();
$fetch_stmt->close();

if ($product) {
    // B. Delete the database record
    $delete_sql = "DELETE FROM products WHERE id = ? AND seller_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $product_id, $seller_id);
    
    if ($delete_stmt->execute()) {
        // C. Delete the physical image file
        if (!empty($product['image_url'])) {
            $image_path = '../' . $product['image_url'];
            if (file_exists($image_path) && strpos($image_path, 'no-image.png') === false) {
                // Remove the file if it exists and is not a default placeholder
                unlink($image_path);
            }
        }
        $message = "Product deleted successfully.";
    } else {
        $message = "Error deleting product: " . $conn->error;
    }
    $delete_stmt->close();
} else {
    $message = "Product not found or you are unauthorized to delete it.";
}

mysqli_close($conn);

// 3. Redirect back to my-products.php with a status message
header("Location: my-products.php?message=" . urlencode($message));
exit();
?>