<?php
session_start();

// Use a secure and modern MySQLi approach
$conn = new mysqli("localhost", "root", "", "craftconnect");

// Check connection and terminate if there's an error
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['buyer_id'])) {
    header("Location: ../home/login.html");
    exit();
}

$buyer_id = $_SESSION['buyer_id'];

// --- 1. HANDLE PAYMENT SUBMISSION (Simulated) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_order'])) {
    $order_id_to_pay = intval($_POST['order_id']);
    $payment_amount = floatval($_POST['total_price']);

    // Check if the order is actually awaiting payment for the current buyer
    $check_sql = "SELECT total_price FROM orders WHERE id = ? AND buyer_id = ? AND status = 'Awaiting Payment'";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $order_id_to_pay, $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Here you would integrate with a real payment gateway. 
        // For simulation, we check if the amount matches and update status.
        if (abs($row['total_price'] - $payment_amount) < 0.01) { 
            // Update order status and add payment details
            $update_sql = "UPDATE orders SET status = 'Processing', payment_ref = CONCAT('PAY-', UUID()), payment_date = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $order_id_to_pay);
            $update_stmt->execute();
            $update_stmt->close();
            
            $payment_message = "Payment successful for Order #{$order_id_to_pay}! Order is now being processed.";
        } else {
            $payment_message = "Error: Amount mismatch or payment failed.";
        }
    } else {
        $payment_message = "Error: Order not found or not ready for payment.";
    }

    // Redirect to self to prevent form resubmission
    header("Location: dashboard.php?msg=" . urlencode($payment_message));
    exit();
}

// --- 2. FETCH DATA FOR DISPLAY ---

// A. Fetch Buyer Profile Info for Sidebar (including profile_pic)
$profile_sql = "SELECT name, role, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($profile_sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

$buyer_name = $user_info['name'] ?? 'Buyer';
$buyer_role = $user_info['role'] ?? 'buyer';
$profile_pic_path = !empty($user_info['profile_pic']) ? htmlspecialchars($user_info['profile_pic']) : '../img/buyer.jpg';

// B. Fetch the most recent *active* order for "Track Order"
$most_recent_order_sql = "
    SELECT o.id, p.name, o.total_price, o.status, o.created_at, o.payment_method, o.payment_ref
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.buyer_id = ? AND o.status NOT IN ('Delivered', 'Cancelled')
    ORDER BY o.created_at DESC LIMIT 1";
$stmt = $conn->prepare($most_recent_order_sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$recent_order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// C. Fetch all orders for the "Order History" table
$order_history_sql = "
    SELECT o.id, p.name, o.quantity, o.total_price, o.status, o.created_at, o.payment_method, o.payment_ref
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.buyer_id = ? 
    ORDER BY o.created_at DESC";
$stmt = $conn->prepare($order_history_sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

$conn->close();
// Check for messages after POST/Redirect/Get (Handling Payment Success and New Order Redirects)
$message = '';
if (isset($_GET['msg'])) {
    // Message from payment processing
    $message = htmlspecialchars(urldecode($_GET['msg']));
} elseif (isset($payment_message)) {
    // Message from in-page payment submission
    $message = $payment_message;
} elseif (isset($_GET['new_orders'])) {
    // Message from successful checkout page redirect
    $order_list = htmlspecialchars($_GET['new_orders']);
    $message = "✅ Order(s) placed successfully! Your order ID(s): {$order_list}. Track your most recent order above.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        /* Status Badges */
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status-Awaiting { background-color: #ffc107; color: #343a40; } /* Yellow */
        .status-Processing { background-color: #007bff; color: white; } /* Blue */
        .status-Shipped { background-color: #17a2b8; color: white; } /* Cyan */
        .status-Delivered { background-color: #28a745; color: white; } /* Green */
        .status-Cancelled { background-color: #dc3545; color: white; } /* Red */
        /* Message Alert */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        /* Payment details style */
        .payment-info { background: #f8f9fa; border: 1px solid #e9ecef; padding: 15px; border-radius: 5px; margin-top: 10px; }
        .payment-info p { margin-bottom: 5px; font-size: 14px; }
        .btn-pay { background-color: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
    </style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard</title>
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
                <div class="profile-img bg-img" style="background-image: url('../img/buyer.jpg')"></div>
                <h4><?php echo htmlspecialchars($_SESSION['name'] ?? 'Buyer'); ?></h4>
                <small>Crafts Buyer</small>
            </div>
            <div class="side-menu">
                <ul>
                    <li><a href="dashboard.php" class="active"><span class="las la-home"></span><small>Dashboard</small></a></li>
                     <li><a href="profile.php"><span class="las la-user"></span><small>Profile</small></a></li>
                   <li><a href="../home/shop.php"><span class="las la-shopping-cart"></span><small>Shop</small></a></li>
                     <li><a href="../home/cart.php"><span class="las la-shopping-cart"></span><small>Cart</small></a></li>
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
                    <div class="bg-img" style="background-image: url('../img/buyer.jpg')"></div>
                    
                        <span class="las la-power-off"></span>
                        <span><a href="../home/logout.php">Logout</a></span>
                    </a>
                </div>
            </div>
        </header>

        <main>
            <div class="page-header">
                <h1>Dashboard</h1>
                <small>Home / Dashboard</small>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo (strpos($message, 'Error') !== false || strpos($message, 'failed') !== false) ? 'alert-error' : 'alert-success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="page-content">
                <div class="records">
                    <h4>Track Your Recent Order</h4>
                    <?php if ($recent_order): ?>
                        <div class="card">
                            <div class="card-head">
                                <h2>Order #<?php echo htmlspecialchars($recent_order['id']); ?></h2>
                                <span class="status-badge status-<?php echo str_replace(' ', '', htmlspecialchars($recent_order['status'])); ?>">
                                    <?php echo htmlspecialchars(ucfirst($recent_order['status'])); ?>
                                </span>
                            </div>
                            <div class="card-progress">
                                <small>Product: <?php echo htmlspecialchars($recent_order['name']); ?></small>
                                <small>Total: ₹<?php echo number_format($recent_order['total_price'], 2); ?></small>
                                <small>Order Date: <?php echo htmlspecialchars($recent_order['created_at']); ?></small>
                            </div>
                            
                            <?php if ($recent_order['status'] === 'Awaiting Payment'): ?>
                                <div class="payment-info">
                                    <p class="text-danger">⚠️ Complete your payment.</p>
                                    <p>Method: Online Payment (GPay/PhonePe)</p>
                                    <p>Amount Due: **₹<?php echo number_format($recent_order['total_price'], 2); ?>**</p>
                                    <form method="POST" action="dashboard.php" style="display:inline-block;">
                                        <input type="hidden" name="order_id" value="<?php echo $recent_order['id']; ?>">
                                        <input type="hidden" name="total_price" value="<?php echo $recent_order['total_price']; ?>">
                                        <button type="submit" name="pay_order" class="btn-pay">
                                            <span class="las la-credit-card"></span> Pay Now (Simulated)
                                        </button>
                                    </form>
                                </div>
                            <?php elseif ($recent_order['payment_ref']): ?>
                                <div class="payment-info">
                                    <p class="text-success">✅ Payment Complete</p>
                                    <p>Reference: <?php echo htmlspecialchars($recent_order['payment_ref']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    <?php else: ?>
                        <p>You have no recent orders to track.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="page-content">
                <div class="records">
                    <h4>Order History</h4>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders->num_rows > 0): ?>
                                    <?php while ($row = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                            <td>₹<?php echo number_format($row['total_price'], 2); ?></td>
                                            <td><?php echo !empty($row['payment_ref']) ? '<span class="las la-check-circle text-success"></span> Paid' : htmlspecialchars($row['payment_method']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo str_replace(' ', '', htmlspecialchars($row['status'])); ?>">
                                                    <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">No orders found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>