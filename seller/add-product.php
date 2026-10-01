<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "craftconnect");
if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}

// ✅ Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: ../home/login.html");
    exit();
}

$seller_id = $_SESSION['seller_id'];

// --- EDIT MODE LOGIC: Check for Product ID in URL ---
$is_edit_mode = false;
$product_data = [];
$product_id = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit_mode = true;
    $product_id = (int)$_GET['id'];

    // Fetch existing product data
    $fetch_sql = "SELECT id, name, category, price, stock, image_url, description FROM products WHERE id = ? AND seller_id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param("ii", $product_id, $seller_id);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();

    if ($fetch_result->num_rows === 1) {
        $product_data = $fetch_result->fetch_assoc();
    } else {
        // Product not found or unauthorized
        header("Location: my-products.php?message=" . urlencode("Error: Product not found or unauthorized."));
        exit();
    }
    $fetch_stmt->close();
}
// --- END EDIT MODE LOGIC ---

// Fetch seller info including profile picture (Moved here to ensure $seller_id is set)
$seller_sql = "SELECT name, profile_pic FROM users WHERE id = ?";
$seller_stmt = $conn->prepare($seller_sql);
$seller_stmt->bind_param("i", $seller_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_stmt->close();

// Determine profile image path
$profile_img = !empty($seller['profile_pic']) ? '../' . htmlspecialchars($seller['profile_pic']) : '../img/seller.jpg';


// --- FORM SUBMISSION HANDLER (INSERT or UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and validate input (Prepared statements handle escaping, so we just trim/cast)
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    // Cast to correct types
    $price = (float)$_POST['price']; 
    $stock = (int)$_POST['stock'];
    $image_url = trim($_POST['image_url']);
    $category = trim($_POST['category']);
    
    $success_message = "";
    $error_message = "";

    // Check if we are UPDATING an existing product
    if (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
        // --- UPDATE LOGIC ---
        $product_id_to_update = (int)$_POST['product_id'];

        $update_sql = "
            UPDATE products 
            SET name = ?, description = ?, price = ?, stock = ?, image_url = ?, category = ?
            WHERE id = ? AND seller_id = ?
        "; 
        
        $stmt = $conn->prepare($update_sql);

        // Bind parameters: name(s), description(s), price(d), stock(i), image_url(s), category(s), id(i), seller_id(i)
        $stmt->bind_param("ssdissii", $name, $description, $price, $stock, $image_url, $category, $product_id_to_update, $seller_id);

        if ($stmt->execute()) {
            $success_message = "Product updated successfully!";
        } else {
            $error_message = "Error updating product: " . $stmt->error;
        }
        $stmt->close();
        
    } else {
        // --- INSERT LOGIC (Add New Product) ---
        
        $insert_sql = "
            INSERT INTO products (seller_id, name, description, price, stock, image_url, category) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $conn->prepare($insert_sql);
        
        // Bind parameters: seller_id(i), name(s), description(s), price(d), stock(i), image_url(s), category(s)
        $stmt->bind_param("issdsss", $seller_id, $name, $description, $price, $stock, $image_url, $category);

        if ($stmt->execute()) {
            $success_message = "Product added successfully!";
        } else {
            $error_message = "Error adding product: " . $stmt->error;
        }
        $stmt->close();
    }

    // Redirect to my-products.php with appropriate message
    if ($success_message) {
        header("Location: my-products.php?message=" . urlencode($success_message));
        exit();
    } elseif ($error_message) {
        header("Location: my-products.php?message=" . urlencode($error_message));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Add Products | Seller Panel</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
</head>
<body>
<input type="checkbox" id="menu-toggle">
<div class="sidebar">
    <div class="side-header">
        <h1 class="m-0 display-5 font-weight-semi-bold">
            <span class="text-primary font-weight-bold border px-3 mr-1">Craft</span>Connect
        </h1>
    </div>
    <div class="side-content">
       <div class="profile">
    <div class="profile-img bg-img" style="background-image: url('<?php echo $profile_img; ?>')"></div>
    <h4><?php echo htmlspecialchars($seller['name']); ?></h4>
    <small>Crafts Seller</small>
</div>

        <div class="side-menu">
            <ul>
                <li><a href="../home/index.php"><span class="las la-user"></span><small>Home</small></a></li>
                <li><a href="seller-dashboard.php"><span class="las la-home"></span><small>Dashboard</small></a></li>
                <li><a href="add-product.php" class="active"><span class="las la-plus-square"></span><small>Add Product</small></a></li>
                <li><a href="add-tutorial.php"><span class="las la-video"></span><small>Upload Tutorial</small></a></li>
                <li><a href="my-products.php"><span class="las la-box-open"></span><small>My Products</small></a></li>
                <li><a href="my-orders.php"><span class="las la-shopping-cart"></span><small>Orders</small></a></li>
                <li><a href="sales-analytics.php"><span class="las la-chart-line"></span><small>Sales Analytics</small></a></li>
                <li><a href="../home/logout.php"><span class="las la-sign-out-alt"></span><small>Logout</small></a></li>
            </ul>
        </div>
    </div>
</div>

<div class="main-content">
    <header>
        <div class="header-content">
            <label for="menu-toggle">
                <span class="las la-bars"></span>
            </label>
        </div>
        <div class="header-menu">
           <div class="user">
    <div class="bg-img" style="background-image: url('<?php echo $profile_img; ?>')"></div>
    <span class="las la-power-off"></span>
    <span><a href="../home/logout.php">Logout</a></span>
</div>

        </div>
    </header>

    <main>
        <div class="page-header">
            <h1><?php echo $is_edit_mode ? 'Edit Product' : 'Add New Product'; ?></h1>
            <small><?php echo $is_edit_mode ? 'Update your product details.' : 'Share your handmade products with buyers!'; ?></small>
        </div>

        <div class="page-content">
            <div class="form-container">
                
                <form method="POST">
                    <?php if ($is_edit_mode): ?>
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" 
                            value="<?php echo $is_edit_mode ? htmlspecialchars($product_data['name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5"><?php echo $is_edit_mode ? htmlspecialchars($product_data['description']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (₹)</label>
                        <input type="number" id="price" name="price" step="0.01" 
                            value="<?php echo $is_edit_mode ? htmlspecialchars($product_data['price']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" 
                            value="<?php echo $is_edit_mode ? htmlspecialchars($product_data['stock']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="text" id="image_url" name="image_url" placeholder="e.g., img/product1.jpg" 
                            value="<?php echo $is_edit_mode ? htmlspecialchars($product_data['image_url']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <?php 
                            $categories = ["Home Decor", "Accessories", "Pottery", "Crochet", "Bags", "Sandals"];
                            $current_category = $is_edit_mode ? $product_data['category'] : '';
                            foreach ($categories as $cat) {
                                $selected = ($cat === $current_category) ? 'selected="selected"' : '';
                                echo "<option value=\"$cat\" $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary"><?php echo $is_edit_mode ? 'Update Product' : 'Add Product'; ?></button>
                </form>
            </div>
        </div>
    </main>
</div>

<style>
.form-container {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
}
.form-group input, .form-group textarea, .form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}
.form-group input:focus, .form-group textarea:focus, .form-group select:focus {
    outline: none;
    border-color: #007bff;
}
.btn-primary {
    background: #007bff;
    color: #fff;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s;
}
.btn-primary:hover {
    background: #0056b3;
}
.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}
.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border: 1px solid #f5c6cb;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}
</style>
</body>
</html>
