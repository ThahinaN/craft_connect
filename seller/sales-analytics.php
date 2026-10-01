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

// Fetch sales data grouped by date
$sales_data = [];
$sql = "
    SELECT 
        DATE_FORMAT(o.created_at, '%Y-%m') AS sale_month,
        SUM(o.total_price) AS total_sales
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE p.seller_id = ?
    GROUP BY sale_month
    ORDER BY sale_month ASC
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sales_data[] = $row;
    }
}

$labels = json_encode(array_column($sales_data, 'sale_month'));
$sales = json_encode(array_column($sales_data, 'total_sales'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Sales Analytics | Seller Panel</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li><a href="my-orders.php"><span class="las la-shopping-cart"></span><small>Orders</small></a></li>
                    <li><a href="sales-analytics.php" class="active"><span class="las la-chart-line"></span><small>Sales Analytics</small></a></li>
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
                <h1>Sales Analytics</h1>
                <small>View your monthly sales performance</small>
            </div>
            
            <div class="page-content">
                <div class="chart-container" style="position: relative; height:400px; width:100%;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </main>
    </div>
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesData = {
            labels: <?php echo $labels; ?>,
            datasets: [{
                label: 'Total Sales (₹)',
                data: <?php echo $sales; ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.7)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        };

        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: salesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>