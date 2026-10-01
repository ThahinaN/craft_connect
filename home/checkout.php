<?php
session_start();
$user_name = $_SESSION['user_name'] ?? 'Guest';
$profile_img = $_SESSION['profile_img'] ?? 'img/default-user.png'; // default profile image
// Use MySQLi for consistency
$conn = new mysqli("localhost", "root", "", "craftconnect"); 
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Ensure buyer is logged in
if (!isset($_SESSION['buyer_id'])) {
    // Redirect to login if not logged in
    header("Location: login.html");
    exit;
}
$buyer_id = $_SESSION['buyer_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") { // <-- CORRECTED: Added opening brace {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_method']; // Capture payment method

    if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
        echo "<script>alert('Your cart is empty!'); window.location='cart.php';</script>";
        exit;
    }

    // 1️⃣ Save buyer address
    // Note: Assuming 'buyer_addresses' table exists with buyer_id, full_name, email, phone, address columns
    $stmt = $conn->prepare("INSERT INTO buyer_addresses (buyer_id, full_name, email, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $buyer_id, $name, $email, $phone, $address);
    $stmt->execute();
    $address_id = $stmt->insert_id;
    $stmt->close();
    
    $order_ids = []; // To store IDs of newly created orders

    // Determine initial status based on payment method
    // COD is 'Processing' immediately, Online is 'Awaiting Payment'
    $initial_status = ($payment_method === 'COD') ? 'Processing' : 'Awaiting Payment';
    $redirect_message = ($payment_method === 'COD') ? '✅ Order placed successfully! Your order will be processed shortly.' : '✅ Order placed successfully! Please complete payment.';


    // 2️⃣ Save orders with dynamic status
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $quantity = intval($quantity);

        // Fetch product price from the database
        $price_query = $conn->query("SELECT price FROM products WHERE id = " . intval($product_id));
        if ($price_query->num_rows === 0) continue; 
        
        $product_data = $price_query->fetch_assoc();
        $price = $product_data['price'];
        
        // Calculate total price including shipping
        $subtotal = $price * $quantity;
        $total_price = $subtotal + 50; 
        
        // Insert into orders table with dynamically determined status
        $stmt = $conn->prepare("INSERT INTO orders (buyer_id, product_id, quantity, total_price, status, address_id, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?)");
        // Data types: i, i, i, d (for total_price), s (for initial_status), i (address_id), s (payment_method)
        $stmt->bind_param("iiidsis", $buyer_id, $product_id, $quantity, $total_price, $initial_status, $address_id, $payment_method);
        $stmt->execute();
        $order_ids[] = $stmt->insert_id;
        $stmt->close();
    }

    unset($_SESSION['cart']); // clear cart

    // Redirect to dashboard with the correct success message
    $conn->close();
    $order_list = implode(',', $order_ids);
    echo "<script>alert('{$redirect_message}'); window.location='../buyer/dashboard.php?new_orders={$order_list}';</script>";
    exit;
} // <-- CORRECTED: Added closing brace

// --- Fetch user info for form pre-fill (Optional but good practice) ---
$user_info = ['name' => '', 'email' => '', 'phone' => ''];
$fetch_user_sql = "SELECT name, email, phone FROM users WHERE id = ?";
if ($stmt = $conn->prepare($fetch_user_sql)) {
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_info = $result->fetch_assoc() ?? $user_info;
    $stmt->close();
}

// Re-open connection for order summary loop if closed previously
if (!$conn) {
    $conn = mysqli_connect("localhost", "root", "", "craftconnect");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Checkout | Craft Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Fonts -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body style="font-family: 'Poppins', sans-serif;">

<!-- 🔹 Topbar -->
<div class="container-fluid">
    <div class="row bg-secondary py-2 px-xl-5">
        <div class="col-lg-6 d-none d-lg-block">
            <div class="d-inline-flex align-items-center">
                <a class="text-dark" href="#">FAQs</a><span class="text-muted px-2">|</span>
                <a class="text-dark" href="#">Help</a><span class="text-muted px-2">|</span>
                <a class="text-dark" href="#">Support</a>
            </div>
        </div>
        <div class="col-lg-6 text-center text-lg-right">
            <div class="d-inline-flex align-items-center">
                <a class="text-dark px-2" href="#"><i class="fab fa-facebook-f"></i></a>
                <a class="text-dark px-2" href="#"><i class="fab fa-twitter"></i></a>
                <a class="text-dark px-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                <a class="text-dark px-2" href="#"><i class="fab fa-instagram"></i></a>
                <a class="text-dark pl-2" href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <!-- 🔹 Header -->
    <div class="row align-items-center py-3 px-xl-5">
        <div class="col-lg-3 d-none d-lg-block">
            <a href="index.php" class="text-decoration-none">
                <h1 class="m-0 display-5 font-weight-semi-bold">
                    <span class="text-primary font-weight-bold border px-3 mr-1">Craft</span>Connect
                </h1>
            </a>
        </div>

        <div class="col-lg-6 col-6 text-left ml-4">
            <form action="shop.php" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search for products">
                    <div class="input-group-append">
                        <button class="input-group-text bg-transparent text-primary border-0"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-2 col-2 text-right">
            <a href="#" class="btn border"><i class="fas fa-heart text-primary"></i><span class="badge">0</span></a>

            <?php
            $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
            ?>
            <a href="cart.php" class="btn border">
                <i class="fas fa-shopping-cart text-primary"></i>
                <span class="badge"><?php echo $cart_count; ?></span>
            </a>
        </div>
    </div>
</div>

  
  
    <!-- Navbar Start -->
     <!-- Navbar Start -->
<div class="container-fluid mb-5">
    <div class="row border-top px-xl-5">
        <div class="col-lg-12">
            <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-0">
                <a href="index.php" class="text-decoration-none d-block d-lg-none">
                    <h1 class="m-0 display-5 font-weight-semi-bold">
                        <span class="text-primary font-weight-bold border px-3 mr-1">Craft</span>Connect
                    </h1>
                </a>
                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                    <div class="navbar-nav mr-auto py-0">
                        <a href="index.php" class="nav-item nav-link ">Home</a>
                        <a href="shop.php" class="nav-item nav-link">Shop</a>
                        <a href="tutorial.php" class="nav-item nav-link">Tutorials</a>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages</a>
                            <div class="dropdown-menu rounded-0 m-0">
                                <a href="cart.php" class="dropdown-item">Shopping Cart</a>
                                <a href="checkout.php" class="dropdown-item active">Checkout</a>
                            </div>
                        </div>
                        <a href="contact.php" class="nav-item nav-link">Contact</a>
                    </div>
                    <div class="navbar-nav ml-auto py-0 d-flex align-items-center gap-3">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <!-- Logged in -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="profile-img" style="
                                    width: 35px;
                                    height: 35px;
                                    border-radius: 50%;
                                    background-image: url('<?php echo htmlspecialchars($profile_img); ?>');
                                    background-size: cover;
                                    background-position: center;
                                    border: 1px solid #ccc;
                                "></div>
                                <span class="nav-item nav-link mb-0">
                                    <i class="las la-user"></i>
                                    Hello, <strong><?php echo htmlspecialchars($user_name); ?></strong>
                                </span>
                                <a href="logout.php" class="nav-item nav-link text-danger">
                                    <i class="las la-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Not logged in -->
                            <a href="login.html" class="nav-item nav-link">
                                <i class="las la-sign-in-alt"></i> Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</div>
<!-- Navbar End -->
<!-- 🔹 Checkout Form -->
<<div class="container py-5">
    <h2 class="mb-4 text-center">Checkout</h2>
    <div class="row">
        <div class="col-lg-7">
            <form method="POST" class="p-4 border rounded bg-light">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user_info['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_info['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Shipping Address</label>
                    <textarea name="address" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="Online">Online Payment (GPay/PhonePe)</option>
                        <option value="COD">Cash on Delivery (COD)</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block py-2">Place Order</button>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card border">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Your Order Summary</h5>
                </div>
                <div class="card-body">
                    <?php
                    $grand_total = 0;
                    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                        $ids = implode(',', array_keys($_SESSION['cart']));
                        $result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
                        $cartProducts = [];
                        while ($row = mysqli_fetch_assoc($result)) {
                            $cartProducts[$row['id']] = $row;
                        }

                        foreach ($_SESSION['cart'] as $product_id => $quantity) {
                            if (!isset($cartProducts[$product_id])) continue;
                            $p = $cartProducts[$product_id];
                            $subtotal = $p['price'] * $quantity;
                            // Add shipping fee (₹50) only once per product for display consistency, 
                            // though typically calculated differently for the grand total.
                            $item_total = $subtotal + 50; 
                            $grand_total += $item_total;
                            echo "<p><strong>{$p['name']}</strong> x {$quantity} <span class='float-right'>₹" . number_format($item_total, 2) . "</span></p>";
                        }
                        echo "<hr><h5 class='text-right'>Total: ₹" . number_format($grand_total, 2) . "</h5>";
                    } else {
                        echo "<p>Your cart is empty.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

 <!-- Footer Start -->
<div class="container-fluid bg-secondary text-dark mt-5 pt-5">
    <div class="row px-3 pt-5">
        <div class="col-lg-4 col-md-12 mb-5">
            <h1 class="display-5 font-weight-semi-bold"><span class="text-primary font-weight-bold border border-white px-3 mr-1">Craft</span>Connect</h1>
            <p>Empowering artisans to sell handmade goods and share skills through tutorials.</p>
            <p><i class="fa fa-map-marker-alt text-primary mr-3"></i>123 Craft Street, Kerala, India</p>
            <p><i class="fa fa-envelope text-primary mr-3"></i>support@craftconnect.in</p>
            <p><i class="fa fa-phone-alt text-primary mr-3"></i>+91 98765 43210</p>
        </div>
        <div class="col-lg-4 col-md-6 mb-5">
            <h5 class="font-weight-bold mb-4">Quick Links</h5>
            <a class="text-dark mb-2 d-block" href="index.php">Home</a>
            <a class="text-dark mb-2 d-block" href="tutorials.php">Tutorials</a>
            <a class="text-dark mb-2 d-block" href="cart.php">Cart</a>
            <a class="text-dark mb-2 d-block" href="checkout.php">Checkout</a>
            <a class="text-dark d-block" href="contact.php">Contact Us</a>
        </div>
        <div class="col-lg-4 col-md-6 mb-5">
            <h5 class="font-weight-bold mb-4">Newsletter</h5>
            <form>
                <input type="text" class="form-control mb-2" placeholder="Your Name" required>
                <input type="email" class="form-control mb-2" placeholder="Your Email" required>
                <button class="btn btn-primary btn-block" type="submit">Subscribe</button>
            </form>
        </div>
    </div>
    <div class="row border-top py-4">
        <div class="col-md-6 text-center text-md-left">
            &copy; <strong>Craft Connect</strong>. All Rights Reserved. Customized by Thahina.
        </div>
        <div class="col-md-6 text-center text-md-right">
            <img class="img-fluid" src="img/payments.png" alt="Payment Methods">
        </div>
    </div>
</div>
<!-- JS -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>