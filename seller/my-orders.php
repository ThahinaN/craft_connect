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
// Fetch seller info including profile picture
$seller_sql = "SELECT name, profile_pic FROM users WHERE id = ?";
$seller_stmt = $conn->prepare($seller_sql);
$seller_stmt->bind_param("i", $seller_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_stmt->close();

// Determine profile image path
$profile_img = !empty($seller['profile_pic']) ? '../' . htmlspecialchars($seller['profile_pic']) : '../img/seller.jpg';

$sql = "
    SELECT 
        o.id AS order_id, 
        o.created_at AS order_date, 
        o.quantity, 
        o.total_price AS price,
        p.image_url AS product_image,
        o.status, 
        o.payment_method,
        p.name AS product_name,
        a.address
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN buyer_addresses a ON o.address_id = a.id
    WHERE p.seller_id = ?
    AND o.status NOT IN ('Awaiting Payment', 'Cancelled')
    ORDER BY o.created_at DESC
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>My Orders | Seller Panel</title>
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
                    <li><a href="add-product.php"><span class="las la-plus-square"></span><small>Add Product</small></a></li>
                    <li><a href="add-tutorial.php"><span class="las la-video"></span><small>Add Tutorial</small></a></li>
                    <li><a href="my-products.php"><span class="las la-box-open"></span><small>My Products</small></a></li>
                    <li><a href="my-orders.php" class="active"><span class="las la-shopping-cart"></span><small>Orders</small></a></li>
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
                <h1>My Orders</h1>
                <small>View orders for your products</small>
            </div>
            <div class="page-content">
                <div class="table-container">
                    <table>
                     <thead>
                             <tr>
                                 <th>Order ID</th>
                                 <th>Image</th>
                                 <th>Product Name</th>
                                 <th>Quantity</th>
                                  <th>Total Price</th>
                                 <th>Payment</th>
                                 <th>Status</th>
                                 <th>Shipping Address</th> 
                                 <th>Order Date</th>
                             </tr>
                            </thead>
                      <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                    <td>
                                        <img src="../<?php echo htmlspecialchars($row['product_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($row['product_name']); ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                    <td>₹<?php echo number_format($row['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                                    <td>
                                        <?php 
                                            echo htmlspecialchars($row['address']);
                                        ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($row['order_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9">No paid/confirmed orders found for your products.</td>
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