<?php
session_start();

// Use a secure and modern MySQLi approach
$conn = new mysqli("localhost", "root", "", "craftconnect");

// Check connection and terminate if there's an error
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}
// Set admin ID from session (or hardcode for testing)

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../home/login.html");
    exit();
}

$admin_id = $_SESSION['admin_id'];
// Fetch admin info including profile picture
$admin_sql = "SELECT name, profile_pic FROM users WHERE id = ?";
$admin_stmt = $conn->prepare($admin_sql);
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin = $admin_result->fetch_assoc();
$admin_stmt->close();

// Determine profile image path (same logic as seller)
$profile_img = !empty($admin['profile_pic']) ? '../' . htmlspecialchars($admin['profile_pic']) : '../img/admin.jpg';
$admin_name = htmlspecialchars($admin['name'] ?? 'Administrator');


// --- Dashboard Data Queries using Prepared Statements ---

// 1. Get total sellers
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM users WHERE role = 'seller'");
$stmt->execute();
$totalSellers = $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

// 2. Get total buyers
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM users WHERE role = 'buyer'");
$stmt->execute();
$totalBuyers = $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

// 3. Get total products
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM products");
$stmt->execute();
$totalProducts = $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

// 4. Get total orders
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM orders");
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

// Close the main connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Admin Dashboard | Craft Connect</title>
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
    <h4><?php echo $admin_name; ?></h4>
    <small>Administrator</small>
</div>

            <div class="side-menu">
                <ul>
                    <li><a href="admin-dashboard.php" class="active"><span class="las la-home"></span><small>Dashboard</small></a></li>
                    <li><a href="../home/index.php"><span class="las la-user-shield"></span><small>Home</small></a></li>
                    <li><a href="sellers.php"><span class="las la-user-tie"></span><small>Sellers</small></a></li>
                    <li><a href="buyers.php"><span class="las la-users"></span><small>Buyers</small></a></li>
                    <li><a href="products.php"><span class="las la-box"></span><small>Products</small></a></li>
                    <li><a href="orders.php"><span class="las la-shopping-cart"></span><small>Orders</small></a></li>
                    <li><a href="reports.php"><span class="las la-chart-line"></span><small>Reports</small></a></li>
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
                            <h2><?php echo $totalSellers; ?></h2>
                            <span class="las la-user-tie"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Sellers</small>
                            <div class="card-indicator">
                                <div class="indicator one" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $totalBuyers; ?></h2>
                            <span class="las la-users"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Buyers</small>
                            <div class="card-indicator">
                                <div class="indicator two" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $totalProducts; ?></h2>
                            <span class="las la-box-open"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Products</small>
                            <div class="card-indicator">
                                <div class="indicator three" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $totalOrders; ?></h2>
                            <span class="las la-shopping-cart"></span>
                        </div>
                        <div class="card-progress">
                            <small>Total Orders</small>
                            <div class="card-indicator">
                                <div class="indicator four" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>