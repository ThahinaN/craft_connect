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
    : "../img/seller.jpg"; // Default image

// --- CORE DATA FETCH: Retrieve only relevant contact messages ---
/*
* Logic:
* 1. If message HAS an order_id: The seller must own the product related to that order.
* - This requires joining contact_messages -> orders -> products (where products.seller_id = current_seller_id).
* 2. If message DOES NOT have an order_id (General Inquiry): Show the message to ALL sellers
* - This is a common requirement for general contact forms. If this is NOT desired, remove the OR clause.
*
* Note: The orders table only has product_id, so we must join with products to filter by seller_id.
*/

$messages_data = [];

// Prepare the complex query
$messages_sql = "
    SELECT
        cm.id, cm.name, cm.email, cm.subject, cm.order_id, cm.message, cm.created_at, o.total_price
    FROM
        contact_messages cm
    LEFT JOIN
        orders o ON cm.order_id = o.id
    LEFT JOIN
        products p ON o.product_id = p.id
    WHERE
        -- Filter 1: Message is related to a specific order that BELONGS to this seller
        (cm.order_id IS NOT NULL AND p.seller_id = ?)
        OR
        -- Filter 2: Message is a general inquiry (no order_id) - visible to all sellers
        (cm.order_id IS NULL)
    ORDER BY
        cm.created_at DESC";

$messages_stmt = $conn->prepare($messages_sql);

// Bind the seller_id for the first part of the WHERE clause (p.seller_id = ?)
$messages_stmt->bind_param("i", $seller_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();


if ($messages_result) {
    $message_count = 0;
    while ($row = $messages_result->fetch_assoc()) {
        // --- Mock Logic for 'NEW' Status ---
        $row['is_new'] = ($message_count < 3);

        // --- Prepare Order Details structure ---
        $row['order_details'] = null;
        if (!empty($row['order_id'])) {
            $row['order_details'] = [
                // total_price is now fetched directly in the main query's SELECT
                'total_price' => $row['total_price']
            ];
        }

        $messages_data[] = $row;
        $message_count++;
    }
    $messages_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Messages | Craft Connect</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <style>
        /* Base styles for message cards */
        .message-card {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s, border-left-color 0.2s;
        }
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        /* Style for visually marking new messages */
        .message-card.new {
            border-left: 5px solid #ff7f50; /* Coral border for new/unread messages */
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .message-subject-container {
            display: flex;
            align-items: center; /* Align badge with subject text */
        }
        .message-subject {
            font-weight: 700;
            font-size: 1.2em;
            color: #3b5998;
        }
        .new-badge {
            background-color: #ff7f50; /* Coral for emphasis */
            color: white;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 0.75em;
            font-weight: bold;
            margin-left: 10px;
        }
        .message-date {
            font-size: 0.9em;
            color: #999;
            white-space: nowrap;
        }
        .message-sender {
            font-style: italic;
            color: #555;
            margin-bottom: 10px;
            font-size: 0.95em;
        }
        .message-content {
            line-height: 1.6;
            color: #333;
        }
        /* Style for order context */
        .order-context-info {
            background-color: #e9f5ff; /* Light blue background */
            border-left: 3px solid #007bff; /* Blue line */
            padding: 8px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 0.9em;
            color: #007bff;
            display: flex;
            align-items: center;
        }
        .order-context-info strong {
            font-weight: 600;
            margin-right: 5px;
        }
        .order-context-info .las {
            margin-right: 8px;
            font-size: 1.1em;
        }
        .order-context-info a {
            color: #1e81b0;
            margin-left: auto; /* Push link to the right */
            text-decoration: none;
            font-weight: 500;
        }
        .order-context-info a:hover {
            text-decoration: underline;
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
                <h4><?php echo htmlspecialchars($seller_name); ?></h4>
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
                    <!-- Set 'Messages' as active -->
                    <li><a href="seller_messages.php" class="active"><span class="las la-comments"></span><small>Messages</small></a></li>
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
                <h1>Buyer Inquiries</h1>
                <small>Home / Messages</small>
            </div>

            <div class="page-content">
                <?php
                if (!empty($messages_data)) {
                    foreach ($messages_data as $row) {
                        // Apply 'new' class based on mock logic
                        $card_class = $row['is_new'] ? 'message-card new' : 'message-card';

                        echo "<div class='$card_class'>";
                        echo "  <div class='message-header'>";
                        echo "      <div class='message-subject-container'>";
                        echo "          <div class='message-subject'>" . htmlspecialchars($row['subject']) . "</div>";
                        // Display the 'NEW' badge if mock status is true
                        if ($row['is_new']) {
                            echo "<span class='new-badge'>NEW</span>";
                        }
                        echo "      </div>";
                        // Improved date formatting
                        echo "      <div class='message-date'>" . date("F j, Y H:i", strtotime($row['created_at'])) . "</div>";
                        echo "  </div>";
                        
                        // New section for Order Context
                        if (!empty($row['order_id'])) {
                            $order_details = $row['order_details'];
                            // Assume my-orders.php can handle a query parameter for filtering
                            $order_link = "my-orders.php?order_id=" . htmlspecialchars($row['order_id']); 
                            
                            echo "  <div class='order-context-info'>";
                            echo "      <span class='las la-shopping-cart'></span> ";
                            echo "      <strong>Inquiry about Order #" . htmlspecialchars($row['order_id']) . "</strong>";
                            if ($order_details && isset($order_details['total_price'])) {
                                echo " (Total: $" . number_format($order_details['total_price'], 2) . ")";
                            } else {
                                echo " (Order details loaded)"; // Changed message since total_price is in main query
                            }
                            echo "      <a href='{$order_link}'>View Order</a>";
                            echo "  </div>";
                        }
                        
                        echo "  <div class='message-sender'>From: " . htmlspecialchars($row['name']) . " &lt;" . htmlspecialchars($row['email']) . "&gt;</div>";
                        // nl2br preserves line breaks from user input
                        echo "  <p class='message-content'>" . nl2br(htmlspecialchars($row['message'])) . "</p>"; 
                        echo "</div>";
                    }
                } else {
                    echo "<div class='message-card'><p>No buyer inquiries found.</p></div>";
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>
