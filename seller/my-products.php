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

// --- 1. Fetch Seller Info ---
$seller_sql = "SELECT name, profile_pic FROM users WHERE id = ?";
$seller_stmt = $conn->prepare($seller_sql);
$seller_stmt->bind_param("i", $seller_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_stmt->close();

// Determine profile image path
$profile_img = !empty($seller['profile_pic']) ? '../' . htmlspecialchars($seller['profile_pic']) : '../img/seller.jpg';

// --- 2. Fetch Product List ---
$sql = "SELECT id, name, category, price, stock, image_url, created_at 
        FROM products 
        WHERE seller_id = ? 
        ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// NOTE: We do NOT close the connection or the statement here because $result is still needed for the HTML loop.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>My Products | Seller Panel</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
<style>
    .table-container {
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.08);
        overflow-x: auto;
        background: #fff;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }
    th {
        background: #343a40;
        color: #fff;
    }
    tr:hover {
        background: #f1f1f1;
    }
    .action {
        text-decoration: none;
        font-weight: 600;
        padding: 5px 8px;
        border-radius: 5px;
    }
    .edit {
        color: #007bff;
    }
    .delete {
        color: #dc3545;
    }
    .product-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }
    .status-message {
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
        font-weight: 600;
        text-align: center;
    }
    .status-message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .status-message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
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
                <li><a href="add-product.php"><span class="las la-plus-square"></span><small>Add Product</small></a></li>
                <li><a href="add-tutorial.php"><span class="las la-video"></span><small>Add Tutorial</small></a></li>
                <li><a href="my-products.php" class="active"><span class="las la-box-open"></span><small>My Products</small></a></li>
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
            <h1>My Products</h1>
            <small>View and manage your product listings</small>
        </div>
        
        <?php if (isset($_GET['message'])): 
            $message_text = htmlspecialchars($_GET['message']);
            $class = (strpos($message_text, 'success') !== false) ? 'success' : 'error';
        ?>
        <div class="status-message <?php echo $class; ?>">
            <?php echo $message_text; ?>
        </div>
        <?php endif; ?>

        <div class="page-content">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $img = !empty($row['image_url']) ? '../' . htmlspecialchars($row['image_url']) : '../images/no-image.png';
                                    ?>
                                    <img src="<?php echo $img; ?>" 
                                            alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                            class="product-img">
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td>₹<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['stock']); ?></td>
                                <td>
                                    <a href="edit-product.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="action edit">Edit</a> | 
                                    <a href="delete-product.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="action delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6">No products found. Add your first product now!</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>