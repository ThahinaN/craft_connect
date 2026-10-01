<?php
session_start();
$user_name = $_SESSION['user_name'] ?? 'Guest';
$profile_img = $_SESSION['profile_img'] ?? 'img/default-user.png'; // default profile image
$conn = mysqli_connect("localhost", "root", "", "craftconnect");
if (!$conn)
    die("Database connection failed: " . mysqli_connect_error());

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- Handle Remove Item ---
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit;
}

// --- Handle Quantity Update (from POST form submit) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $qty = max(1, intval($qty)); // Ensure minimum quantity 1
        $_SESSION['cart'][$id] = $qty;
    }
    header("Location: cart.php");
    exit;
}

// ⭐ NEW LOGIC START ⭐
// --- Handle Add Item (from shop.php or detail.php via GET) ---
if (isset($_GET['id']) && isset($_GET['qty'])) {
    // Note: Using mysqli_prepare for the optional check, so we need to use the $conn object.
    $id = intval($_GET['id']);
    $qty = max(1, intval($_GET['qty']));

    // Optional: Check if product exists in database to prevent adding invalid IDs
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if (isset($_SESSION['cart'][$id])) {
            // Product already in cart, just increase quantity
            $_SESSION['cart'][$id] += $qty;
        } else {
            // Add new product to cart
            $_SESSION['cart'][$id] = $qty;
        }
        // Redirect to clean the URL and prevent re-adding on refresh
        header("Location: cart.php");
        exit;
    }
    $stmt->close();
}
// ⭐ NEW LOGIC END ⭐
// --- Handle AJAX In-Place Quantity Update ---
if (isset($_POST['id']) && isset($_POST['action']) && !isset($_POST['update_cart'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    if (isset($_SESSION['cart'][$id])) {
        if ($action === 'plus') {
            $_SESSION['cart'][$id]++;
        } elseif ($action === 'minus') {
            // Ensure quantity doesn't drop below 1
            if ($_SESSION['cart'][$id] > 1) {
                $_SESSION['cart'][$id]--;
            }
        }
    }
    // Respond to the AJAX call and stop execution
    echo 'ok';
    exit;
}

// --- Fetch Cart Products ---
$cart = $_SESSION['cart'];
$cartProducts = [];
$subtotal = 0;

// ... rest of your cart.php code ...

if (!empty($cart)) {
    $ids = implode(",", array_keys($cart));
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");

    while ($row = mysqli_fetch_assoc($result)) {
        $cartProducts[$row['id']] = $row;
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Shopping Cart | Craft Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="img/favicon.ico" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* Optional: make quantity buttons smaller */
        .input-group .btn {
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>

<body>
    <!-- Topbar -->
    <div class="container-fluid">
        <div class="row bg-secondary py-2 px-xl-5">
            <div class="col-lg-6 d-none d-lg-block">
                <div class="d-inline-flex align-items-center">
                    <a class="text-dark" href="">FAQs</a><span class="text-muted px-2">|</span>
                    <a class="text-dark" href="">Help</a><span class="text-muted px-2">|</span>
                    <a class="text-dark" href="">Support</a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <a class="text-dark px-2" href=""><i class="fab fa-facebook-f"></i></a>
                    <a class="text-dark px-2" href=""><i class="fab fa-twitter"></i></a>
                    <a class="text-dark px-2" href=""><i class="fab fa-linkedin-in"></i></a>
                    <a class="text-dark px-2" href=""><i class="fab fa-instagram"></i></a>
                    <a class="text-dark pl-2" href=""><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="row align-items-center py-3 px-xl-5">
            <div class="col-lg-3 d-none d-lg-block">
                <a href="index.php" class="text-decoration-none">
                    <h1 class="m-0 display-5 font-weight-semi-bold">
                        <span class="text-primary font-weight-bold border px-3 mr-1">Craft</span>Connect
                    </h1>
                </a>
            </div>
            <div class="col-lg-6 col-6 text-left ml-4">
                <form action="">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search for products">
                        <div class="input-group-append">
                            <span class="input-group-text bg-transparent text-primary"><i
                                    class="fa fa-search"></i></span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-2 col-2 text-right">
                <a href="" class="btn border"><i class="fas fa-heart text-primary"></i><span class="badge">0</span></a>
                <a href="" class="btn border"><i class="fas fa-shopping-cart text-primary"></i><span
                        class="badge"><?php echo array_sum($cart); ?></span></a>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <div class="container-fluid">
        <div class="row border-top px-xl-5">
            <div class="col-lg-3 d-none d-lg-block"></div>
            <div class="col-lg-9">
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
                            <a href="index.php" class="nav-item nav-link">Home</a>
                            <a href="shop.php" class="nav-item nav-link">Shop</a>
                            <a href="tutorial.php" class="nav-item nav-link">Tutorials</a>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages</a>
                                <div class="dropdown-menu rounded-0 m-0">
                                    <a href="cart.php" class="dropdown-item active">Shopping Cart</a>
                                    <a href="checkout.php" class="dropdown-item">Checkout</a>
                                </div>
                            </div>
                            <a href="contact.php" class="nav-item nav-link">Contact</a>
                        </div>
                        <div class="navbar-nav ml-auto py-0 d-flex align-items-center gap-3">
                            <?php if (isset($_SESSION['user_id'])): ?>
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

    <!-- Page Header -->
    <div class="container-fluid bg-secondary mb-5">
        <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 300px">
            <h1 class="font-weight-semi-bold text-uppercase mb-3">Shopping Cart</h1>
            <div class="d-inline-flex">
                <p class="m-0"><a href="index.php">Home</a></p>
                <p class="m-0 px-2">-</p>
                <p class="m-0">Shopping Cart</p>
            </div>
        </div>
    </div>

    <!-- Cart -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <div class="col-lg-8 table-responsive mb-5">
                <table class="table table-bordered text-center mb-0">
                    <thead class="bg-secondary text-dark">
                        <tr>
                            <th>Products</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        <?php
                        if (!empty($cartProducts)) {
                            foreach ($cart as $id => $qty) {
                                if (!isset($cartProducts[$id]))
                                    continue;
                                $p = $cartProducts[$id];
                                $total = $p['price'] * $qty;
                                $subtotal += $total;

                                // 1. Use the correct column name 'image_url'
                                $imgFile = !empty($p['image_url']) ? basename($p['image_url']) : 'no-image.png';

                                // 2. 🎯 FIX APPLIED: Use 'img/' since it's in the same directory as cart.php
                                $imgPath = "img/{$imgFile}";

                                // ... inside the foreach loop ...
                                echo "
        <tr>
            <td class='align-middle'>
                <img src='{$imgPath}' alt='{$p['name']}' style='width:50px;'> {$p['name']}
            </td>
            
            <td class='align-middle'>₹" . number_format($p['price'], 2) . "</td>
            
            <td class='align-middle'>
                <div class='input-group quantity mx-auto' style='width:100px;'>
                    <button class='btn btn-sm btn-primary btn-minus' data-id='{$id}' data-action='minus'>-</button>
                    <input type='text' class='form-control form-control-sm bg-secondary text-center product-qty-input' id='qty-{$id}' value='{$qty}' readonly>
                    <button class='btn btn-sm btn-primary btn-plus' data-id='{$id}' data-action='plus'>+</button>
                </div>
            </td>
            
            <td class='align-middle'>₹" . number_format($total, 2) . "</td>
            
            <td class='align-middle'>
                <a href='cart.php?remove={$id}' class='btn btn-sm btn-danger'>×</a>
            </td>
        </tr>";

                            }
                        } else {
                            echo "<tr><td colspan='5'>Your cart is empty</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="col-lg-4">
                <div class="card border-secondary mb-5">
                    <div class="card-header bg-secondary border-0">
                        <h4 class="font-weight-semi-bold m-0">Cart Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 pt-1">
                            <h6 class="font-weight-medium">Subtotal</h6>
                            <h6 class="font-weight-medium">₹<?php echo number_format($subtotal, 2); ?></h6>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h6 class="font-weight-medium">Shipping</h6>
                            <h6 class="font-weight-medium">₹<?php echo ($subtotal > 0 ? 50 : 0); ?></h6>
                        </div>
                    </div>
                    <div class="card-footer border-secondary bg-transparent">
                        <div class="d-flex justify-content-between mt-2">
                            <h5 class="font-weight-bold">Total</h5>
                            <h5 class="font-weight-bold">₹<?php echo ($subtotal > 0 ? $subtotal + 50 : 0); ?></h5>
                        </div>
                        <a href="checkout.php" class="btn btn-block btn-primary my-3 py-3">Proceed To Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cart End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-secondary text-dark mt-5 pt-5">
        <div class="row px-3 pt-5">
            <div class="col-lg-4 col-md-12 mb-5">
                <h1 class="display-5 font-weight-semi-bold"><span
                        class="text-primary font-weight-bold border border-white px-3 mr-1">Craft</span>Connect</h1>
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
    <script>
        $(document).ready(function () {
            // Function to handle the AJAX update
            function updateCartQuantity(id, action) {
                $.ajax({
                    url: 'cart.php', // Send request to the current page
                    type: 'POST',
                    data: {
                        id: id,
                        action: action
                    },
                    success: function (response) {
                        if (response.trim() === 'ok') {
                            // On success, reload the page to update the displayed quantity, 
                            // total price, and cart summary immediately.
                            window.location.reload();
                        } else {
                            alert('Error: Could not update cart session.');
                        }
                    },
                    error: function () {
                        alert('Connection error. Could not reach the server.');
                    }
                });
            }

            // Attach click listeners to the + and - buttons
            $('.btn-plus').on('click', function () {
                const id = $(this).data('id');
                updateCartQuantity(id, 'plus');
            });

            $('.btn-minus').on('click', function () {
                const id = $(this).data('id');
                updateCartQuantity(id, 'minus');
            });
        });
    </script>



</body>

</html>