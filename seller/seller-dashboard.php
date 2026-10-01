<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "craftconnect");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// ✅ Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: ../home/login.html");
    exit();
}

$seller_id = $_SESSION['seller_id'];


// --- Fetch seller info (name + profile) ---
$seller_sql = "SELECT name, profile_pic FROM users WHERE id = ?";
$seller_stmt = $conn->prepare($seller_sql);
$seller_stmt->bind_param("i", $seller_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_stmt->close();

// ✅ Safeguards (avoid null values)
$seller_name = $seller['name'] ?? 'Unknown Seller';
$profile_img = (!empty($seller['profile_pic']) && file_exists("../" . $seller['profile_pic']))
    ? "../" . htmlspecialchars($seller['profile_pic'])
    : "../img/seller.jpg";

// --- 1️⃣ Total sales and orders ---
$dashboard_sql = "
    SELECT 
        COALESCE(SUM(o.total_price),0) AS total_sales,
        COUNT(o.id) AS total_orders
    FROM products p
    JOIN orders o ON p.id = o.product_id
    WHERE p.seller_id = ?";
$dashboard_stmt = $conn->prepare($dashboard_sql);
$dashboard_stmt->bind_param("i", $seller_id);
$dashboard_stmt->execute();
$dashboard_result = $dashboard_stmt->get_result();
$dashboard_data = $dashboard_result->fetch_assoc();
$dashboard_stmt->close();

$total_sales = $dashboard_data['total_sales'] ?? 0;
$total_orders = $dashboard_data['total_orders'] ?? 0;

// --- 2️⃣ Total stock ---
$stock_sql = "SELECT COALESCE(SUM(stock),0) AS total_stock FROM products WHERE seller_id = ?";
$stock_stmt = $conn->prepare($stock_sql);
$stock_stmt->bind_param("i", $seller_id);
$stock_stmt->execute();
$stock_result = $stock_stmt->get_result();
$total_stock = $stock_result->fetch_assoc()['total_stock'] ?? 0;
$stock_stmt->close();

// --- 3️⃣ Product performance ---
$products_sql = "
    SELECT 
        p.id,
        p.name,
        p.image_url,
        p.stock AS remaining_stock,
        COALESCE(SUM(o.quantity), 0) AS total_sold
    FROM products p
    LEFT JOIN orders o ON p.id = o.product_id
    WHERE p.seller_id = ?
    GROUP BY p.id, p.name, p.image_url, p.stock
    ORDER BY p.created_at DESC";
$products_stmt = $conn->prepare($products_sql);
$products_stmt->bind_param("i", $seller_id);
$products_stmt->execute();
$products_result = $products_stmt->get_result();
$products_data = $products_result->fetch_all(MYSQLI_ASSOC);
$products_stmt->close();

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Seller Dashboard | Craft Connect</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <style>
        /* FIX: Ensure the sidebar scrolls if the content exceeds the screen height */
        .sidebar {
            height: 100vh; /* Set height to full viewport height */
            overflow-y: auto; /* Enable vertical scrolling */
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            text-align: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        img.product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        tr:hover {
            background-color: #f2f2f2;
        }
        .analytics {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .card {
            flex: 1;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-head h2 {
            margin: 0;
        }
        .card-progress {
            margin-top: 10px;
        }
        .card-indicator {
            height: 5px;
            background-color: #eee;
            border-radius: 5px;
        }
        .indicator {
            height: 5px;
            border-radius: 5px;
        }
        .indicator.one { background-color: #007bff; }
        .indicator.two { background-color: #28a745; }
        .indicator.three { background-color: #ffc107; }
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
                <h4><?php echo htmlspecialchars($seller_name); ?></h4>
                <small>Crafts Seller</small>
            </div>

            <div class="side-menu">
                <ul>
                    <li><a href="../home/index.php"><span class="las la-user"></span><small>Home</small></a></li>
                    <li><a href="seller-dashboard.php" class="active"><span class="las la-home"></span><small>Dashboard</small></a></li>
                    <li><a href="add-product.php"><span class="las la-plus-square"></span><small>Add Product</small></a></li>
                    <li><a href="add-tutorial.php"><span class="las la-video"></span><small>Add Tutorial</small></a></li>
                    <li><a href="my-products.php"><span class="las la-box-open"></span><small>My Products</small></a></li>
                    <li><a href="my-orders.php"><span class="las la-shopping-cart"></span><small>Orders</small></a></li>
                    <li><a href="seller_messages.php"><span class="las la-comments"></span><small>Messages</small></a></li>
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
                <h1>Dashboard</h1>
                <small>Home / Dashboard</small>
            </div>

            <div class="page-content">
                <div class="analytics">
                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $total_orders; ?></h2>
                            <span class="las la-shopping-cart"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Orders</small>
                            <div class="card-indicator"><div class="indicator one" style="width: 100%"></div></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <h2>₹<?php echo number_format($total_sales, 2); ?></h2>
                            <span class="las la-chart-line"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Sales</small>
                            <div class="card-indicator"><div class="indicator two" style="width: 100%"></div></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $total_stock; ?></h2>
                            <span class="las la-box-open"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Stock</small>
                            <div class="card-indicator"><div class="indicator three" style="width: 100%"></div></div>
                        </div>
                    </div>
                </div>

                <div class="records table-responsive">
                    <h4 class="mb-3">Product Performance</h4>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Total Sold</th>
                                <th>Remaining Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products_data)): ?>
                                <?php foreach ($products_data as $product): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $img = !empty($product['image_url']) ? '../' . htmlspecialchars($product['image_url']) : '../images/no-image.png';
                                            ?>
                                            <img src="<?php echo $img; ?>" 
                                                alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                class="product-img">
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo $product['total_sold'] ; ?></td>
                                        <td><?php echo $product['remaining_stock']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No products found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
