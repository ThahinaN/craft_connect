<?php
session_start();
$conn = mysqli_connect("localhost","root","","craftconnect");
if(!$conn){ die("DB connection failed: ".mysqli_connect_error()); }
// Set admin ID from session (or hardcode for testing)
// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../home/login.html");
    exit();
}

$admin_id = $_SESSION['admin_id'];// replace 5 with actual admin ID if session not set

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


$totalSellers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM users WHERE role='seller'"))['c'];
$totalBuyers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM users WHERE role='buyer'"))['c'];
$totalProducts= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM products"))['c'];
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM orders"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Reports | Admin Panel</title>
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
    <h4><?php echo $admin_name; ?></h4>
    <small>Administrator</small>
</div>

           <div class="side-menu">
                <ul>
                    <li><a href="admin-dashboard.php"><span class="las la-home"></span><small>Dashboard</small></a></li>
                    <li><a href="sellers.php"><span class="las la-user-tie"></span><small>Sellers</small></a></li>
                    <li><a href="buyers.php"><span class="las la-users"></span><small>Buyers</small></a></li>
                    <li><a href="products.php"><span class="las la-box"></span><small>Products</small></a></li>
                    <li><a href="orders.php"><span class="las la-shopping-cart"></span><small>Orders</small></a></li>
                    <li><a href="reports.php" class="active"><span class="las la-chart-line"></span><small>Reports</small></a></li>
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
                <h1>Reports</h1>
                <small>Visual overview of platform statistics</small>
            </div>
            
            <div class="page-content">
                <div class="chart-container" style="position: relative; height:400px; width:80%">
                    <canvas id="reportsChart"></canvas>
                </div>
            </div>
        </main>
    </div>
    <script>
        const ctx = document.getElementById('reportsChart').getContext('2d');
        const reportsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Sellers', 'Buyers', 'Products', 'Orders'],
                datasets: [{
                    label: 'Total Counts',
                    data: [<?php echo $totalSellers; ?>, <?php echo $totalBuyers; ?>, <?php echo $totalProducts; ?>, <?php echo $totalOrders; ?>],
                    backgroundColor: [
                        'rgba(0, 123, 255, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderColor: [
                        'rgba(0, 123, 255, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
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