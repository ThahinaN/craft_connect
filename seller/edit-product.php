<?php
// edit-product.php
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Redirect to add-product.php, using the ID to signal 'Edit Mode'
    header("Location: add-product.php?id=" . urlencode($_GET['id']));
    exit();
} else {
    // If no ID is provided, go back to the product list
    header("Location: my-products.php");
    exit();
}
?>