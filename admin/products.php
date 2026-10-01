<?php
session_start();
// Use the recommended object-oriented connection style for consistency
$conn = new mysqli("localhost", "root", "", "craftconnect");
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

// --- START: ADDED/MODIFIED PHP FOR STATUS MESSAGES AND STATUS FETCH ---

// Optional: Check for status messages from redirect and construct HTML message
$message = '';
if (isset($_GET['message']) && isset($_GET['status'])) {
    $status = htmlspecialchars($_GET['status']);
    $msg = htmlspecialchars($_GET['message']);
    // Simple message display logic
    $message = "<p style='padding:10px; margin-bottom: 15px; border-radius: 5px; background-color: " . 
                ($status == 'success' ? '#d4edda; color:#155724' : 
                 ($status == 'warning' ? '#fff3cd; color:#856404' : '#f8d7da; color:#721c24')) . 
                ";'>{$msg}</p>";
}

// Fetch products along with seller info, INCLUDING THE 'status' COLUMN
$sql_products = "SELECT p.id, p.name, p.price, p.created_at, p.image_url, p.seller_id, p.status, u.name AS seller_name 
                 FROM products p 
                 LEFT JOIN users u ON p.seller_id = u.id
                 ORDER BY p.created_at DESC";

$items = $conn->query($sql_products);
// ----------------- END: ADDED/MODIFIED PHP -------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Products | Admin Panel</title>
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Original Styles */
        .product-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        /* Unified Action Button Styles (Based on sellers.php example for consistency) */
        .action-btn {
            padding: 10px 20px;
            border: none;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            text-align: center;
            min-width: 90px;
        }
        
        .approve-btn {
            background-color: #28a745; /* Green */
        }
        .approve-btn:hover {
            background-color: #218838; /* Darker green on hover */
        }

        .remove-btn {
            background-color: #dc3545; /* Red */
        }
        .remove-btn:hover {
            background-color: #c82333; /* Darker red on hover */
        }
        
        /* Status Display Styles (Based on sellers.php example for visual feedback) */
        .status-cell {
            text-transform: capitalize;
            font-weight: bold;
        }
        .status-approved { 
            color: #28a745; /* Green */
        } 
        .status-pending { 
            color: #ffc107; /* Yellow/Orange */
        } 
        .status-removed { 
            color: #dc3545; /* Red */
        }

        /* Disabled Status Button Styles */
        .status-btn {
            background-color: #ffc107; /* Orange/Yellow for general status display */
            color: #343a40;
            cursor: default;
        }
        .status-removed-btn {
            background-color: #6c757d; /* Gray for removed/disabled */
            color: white;
            cursor: default;
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
                <h4><?php echo $admin_name; ?></h4>
                <small>Administrator</small>
            </div>

            <div class="side-menu">
                <ul>
                    <li><a href="admin-dashboard.php"><span class="las la-home"></span><small>Dashboard</small></a></li>
                    <li><a href="sellers.php"><span class="las la-user-tie"></span><small>Sellers</small></a></li>
                    <li><a href="buyers.php"><span class="las la-users"></span><small>Buyers</small></a></li>
                    <li><a href="products.php" class="active"><span class="las la-box"></span><small>Products</small></a></li>
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
                <h1>Products</h1>
                <small>Manage / Products</small>
            </div>
            
            <div class="page-content">
                <?php echo $message; // Display status message here ?>
                <div class="records table-responsive">
                    <div>
                        <table width="100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><span class="las la-sort"></span> Product</th>
                                    <th><span class="las la-sort"></span> Name</th>
                                    <th><span class="las la-sort"></span> Seller</th>
                                    <th><span class="las la-sort"></span> Price</th>
                                    <th><span class="las la-sort"></span> Created At</th>
                                    <th><span class="las la-sort"></span> Status</th> <!-- NEW COLUMN HEADER -->
                                    <th><span class="las la-sort"></span> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($items && $items->num_rows > 0){
                                    while($row=$items->fetch_assoc()){
                                        $img = !empty($row['image_url']) ? '../' . htmlspecialchars($row['image_url']) : '../img/no-image.png';
                                        $item_id = $row['id'];
                                        $current_status = htmlspecialchars($row['status'] ?? 'pending'); // Read the status column
                                        
                                        // Links including redirection parameters for consistency
                                        $approve_link = "product-approve.php?id={$item_id}&page=products&item_type=Product";
                                        $remove_link = "product-remove.php?id={$item_id}&page=products&item_type=Product";
                                        
                                        echo "<tr>";
                                        echo "<td>{$item_id}</td>";
                                        echo "<td><img src='{$img}' alt='".htmlspecialchars($row['name'])."' class='product-img'></td>";
                                        echo "<td>".htmlspecialchars($row['name'])."</td>";
                                        echo "<td>".htmlspecialchars($row['seller_name'] ?? 'Unknown')."</td>";
                                        echo "<td>₹".number_format($row['price'],2)."</td>";
                                        echo "<td>{$row['created_at']}</td>";
                                        
                                        // --- STATUS CELL ---
                                        echo "<td class='status-cell status-{$current_status}'>" . $current_status . "</td>";

                                        // --- ACTIONS CELL (MODIFIED FOR CONDITIONAL BUTTONS) ---
                                        echo "<td>
                                            <div class='product-actions'>";
                                                
                                        // Show Approve button only if status is NOT 'approved'
                                        if ($current_status !== 'approved') {
                                            // Using action-btn class for consistency
                                            echo "<a href='{$approve_link}' class='action-btn approve-btn'>Approve</a>";
                                        } else {
                                            echo "<button disabled class='action-btn status-btn'>Approved</button>";
                                        }

                                        // Show Remove button only if status is NOT 'removed'
                                        if ($current_status !== 'removed') {
                                            // Using action-btn class for consistency
                                            echo "<a href='{$remove_link}' onclick='return confirm(\"Are you sure you want to remove this product?\")' class='action-btn remove-btn'>Remove</a>";
                                        } else {
                                            echo "<button disabled class='action-btn status-removed-btn'>Removed</button>";
                                        }
                                        
                                        echo "</div>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    // Increased colspan to 8 to account for the new Status column
                                    echo "<tr><td colspan='8'>No products found</td></tr>";
                                }
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
<?php $conn->close(); // Ensure connection is closed ?>
