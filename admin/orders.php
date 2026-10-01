<?php
session_start();
// --- Database Connection (using user's provided details) ---
$conn = mysqli_connect("localhost","root","","craftconnect");
if(!$conn){ die("DB connection failed: ".mysqli_connect_error()); }

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

// ✅ CORE DATA FETCH: Updated to select only 'shipped_at' for shipping details
$items = mysqli_query($conn,"SELECT id,buyer_id,product_id,status,created_at,shipped_at FROM orders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Orders | Admin Panel</title>
<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
<link rel="stylesheet" href="style.css">
<style>
/* Order Action Button Styles */
.order-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    align-items: center;
}
.status-badge {
    padding: 5px 10px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    text-align: center;
    text-transform: capitalize; /* Ensure status looks clean */
    min-width: 80px; /* Gives badges a minimum width */
}
.status-pending { background-color: #ffc107; } /* Yellow */
.status-approved { background-color: #28a745; } /* Green */
.status-shipped { background-color: #007bff; } /* Primary Blue */
.status-delivered { background-color: #6f42c0; } /* Purple */
.status-cancelled { background-color: #dc3545; } /* Red */
.status-processing { background-color: #17a2b8; } /* Cyan/Info Blue */

.btn-action {
    padding: 8px 15px;
    border: none;
    color: white;
    font-weight: bold;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease;
    text-decoration: none;
    white-space: nowrap; /* Prevent button text wrapping */
}
.btn-approve { background-color: #28a745; }
.btn-approve:hover { background-color: #218838; }
.btn-ship { background-color: #007bff; }
.btn-ship:hover { background-color: #0069d9; }
.btn-deliver { background-color: #6f42c0; }
.btn-deliver:hover { background-color: #5a359c; }
.btn-cancel { background-color: #dc3545; }
.btn-cancel:hover { background-color: #c82333; }
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
    <h4><?php echo $admin_name; ?></h4>
    <small>Administrator</small>
</div>
           <div class="side-menu">
                <ul>
                    <li><a href="admin-dashboard.php"><span class="las la-home"></span><small>Dashboard</small></a></li>
                    <li><a href="sellers.php"><span class="las la-user-tie"></span><small>Sellers</small></a></li>
                    <li><a href="buyers.php"><span class="las la-users"></span><small>Buyers</small></a></li>
                    <li><a href="products.php"><span class="las la-box"></span><small>Products</small></a></li>
                    <li><a href="orders.php" class="active"><span class="las la-shopping-cart"></span><small>Orders</small></a></li>
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
                <h1>Orders</h1>
                <small>Manage / Orders</small>
            </div>
            
            <div class="page-content">
                <div class="records table-responsive">
                    <div>
                        <table width="100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><span class="las la-sort"></span> Buyer ID</th>
                                    <th><span class="las la-sort"></span> Product ID</th>
                                    <th><span class="las la-sort"></span> Status</th>
                                    <th><span class="las la-sort"></span> Order Date</th>
                                    <!-- NEW HEADERS ADDED BELOW -->
                                    <th><span class="las la-sort"></span> Ship Date</th>
                                    <!-- END NEW HEADERS -->
                                    <th><span class="las la-sort"></span> Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($items && mysqli_num_rows($items)>0){
                                    while($row=mysqli_fetch_assoc($items)){
                                        $status_class = strtolower($row['status']); 

                                        echo "<tr>";
                                        echo "<td>{$row['id']}</td>";
                                        echo "<td>{$row['buyer_id']}</td>";
                                        echo "<td>{$row['product_id']}</td>";
                                        echo "<td><span class='status-badge status-". $status_class ."'>{$row['status']}</span></td>";
                                        echo "<td>{$row['created_at']}</td>";
                                        
                                        // --- SHIP DATE COLUMN ---
                                        // Display formatted shipped date or '-'
                                        $shipped_date = ($row['shipped_at'] && $row['shipped_at'] != '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($row['shipped_at'])) : '-';
                                        echo "<td>" . $shipped_date . "</td>";
                                        // --- END SHIP DATE COLUMN ---

                                        echo "<td>
                                            <div class='order-actions'>";
                                        
                                        // Conditional Buttons based on Order Status
                                        if ($status_class == 'pending') {
                                            echo "<a href='approve_order.php?id={$row['id']}' class='btn-action btn-approve'>Approve</a>";
                                            echo "<a href='cancel_order.php?id={$row['id']}' class='btn-action btn-cancel'>Cancel</a>";
                                        } else if ($status_class == 'approved' || $status_class == 'processing') {
                                            echo "<a href='ship_order.php?id={$row['id']}' class='btn-action btn-ship'>Ship</a>";
                                        } else if ($status_class == 'shipped') {
                                            echo "<a href='deliver_order.php?id={$row['id']}' class='btn-action btn-deliver'>Delivered</a>";
                                        } else {
                                            echo "<span>-</span>";
                                        }
                                        
                                        echo "</div>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else { echo "<tr><td colspan='7'>No orders found</td></tr>"; }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
