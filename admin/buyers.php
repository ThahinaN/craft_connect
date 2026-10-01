<?php
session_start();
// Use the recommended object-oriented connection style for consistency
$conn = new mysqli("localhost", "root", "", "craftconnect");
if ($conn->connect_error) { 
    die("DB connection failed: " . $conn->connect_error); 
}

// 1. Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../home/login.html");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// 2. Fetch admin info
$admin_sql = "SELECT name, profile_pic FROM users WHERE id = ?";
$admin_stmt = $conn->prepare($admin_sql);
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin = $admin_result->fetch_assoc();
$admin_stmt->close();

$profile_img = !empty($admin['profile_pic']) ? '../' . htmlspecialchars($admin['profile_pic']) : '../img/admin.jpg';
$admin_name = htmlspecialchars($admin['name'] ?? 'Administrator');

// 3. Fetch buyer data, including the 'status' column
$sql_buyers = "SELECT id, name, email, created_at, status FROM users WHERE role='buyer' ORDER BY created_at DESC";
// Using object-oriented method for consistency
$items_result = $conn->query($sql_buyers);

// Optional: Check for status messages from redirect
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Buyers | Admin Panel</title>
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles for action buttons and status display */
        .product-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .action-btn {
            padding: 10px 15px;
            border: none;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            text-align: center;
            min-width: 80px;
            font-size: 0.9rem;
        }

        .approve-btn {
            background-color: #28a745; /* Green */
        }
        .approve-btn:hover {
            background-color: #218838;
        }
        
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

        .remove-btn {
            background-color: #dc3545; /* Red */
        }
        .remove-btn:hover {
            background-color: #c82333;
        }

        .status-cell {
            text-transform: capitalize;
            font-weight: bold;
        }
        /* Visual cues for status states */
        .status-approved { color: #28a745; }
        .status-pending { color: #ffc107; }
        .status-removed { color: #dc3545; }
        .profile-img { background-size: cover; background-position: center; border-radius: 50%; width: 50px; height: 50px; }
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
                    <li><a href="buyers.php" class="active"><span class="las la-users"></span><small>Buyers</small></a></li>
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
                <h1>Buyers</h1>
                <small>Manage / Buyers</small>
            </div>
            
            <div class="page-content">
                <?php echo $message; // Display status message ?>
                <div class="records table-responsive">
                    <div>
                        <table width="100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><span class="las la-sort"></span> Name</th>
                                    <th><span class="las la-sort"></span> Email</th>
                                    <th><span class="las la-sort"></span> Created At</th>
                                    <th><span class="las la-sort"></span> Status</th>
                                    <th><span class="las la-sort"></span> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($items_result && $items_result->num_rows > 0){
                                    while($row = $items_result->fetch_assoc()){
                                        $user_id = $row['id'];
                                        $current_status = htmlspecialchars($row['status'] ?? 'pending'); // Default to pending if null
                                        
                                        // --- UPDATED GENERIC LINK PARAMETERS FOR BUYERS ---
                                        $approve_link = "approve.php?id={$user_id}&page=buyers&role_name=Buyer";
                                        $remove_link = "remove.php?id={$user_id}&page=buyers&role_name=Buyer";
                                        // ----------------------------------------------

                                        echo "<tr>";
                                        echo "<td>{$user_id}</td>";
                                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                                        
                                        // Display Status with class
                                        echo "<td class='status-cell status-{$current_status}'>" . $current_status . "</td>";

                                        // Action buttons logic
                                        echo "<td>
                                            <div class='product-actions'>";
                                                
                                        // Show Approve button only if status is NOT 'approved'
                                        if ($current_status !== 'approved') {
                                            echo "<a href='{$approve_link}' class='action-btn approve-btn'>Approve</a>";
                                        } else {
                                            echo "<button disabled class='action-btn status-btn'>Approved</button>";
                                        }

                                        // Show Remove button only if status is NOT 'removed'.
                                        // Removed the blocking JavaScript confirmation.
                                        if ($current_status !== 'removed') {
                                            echo "<a href='{$remove_link}' class='action-btn remove-btn'>Remove</a>";
                                        } else {
                                            echo "<button disabled class='action-btn status-removed-btn'>Removed</button>";
                                        }

                                        echo "</div>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else { 
                                    echo "<tr><td colspan='6'>No buyers found</td></tr>"; 
                                }
                                $conn->close();
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
