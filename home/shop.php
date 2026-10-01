<?php
session_start();
$user_name = $_SESSION['user_name'] ?? 'Guest';
$profile_img = $_SESSION['profile_img'] ?? 'img/default-user.png'; // default profile image

// Use a more modern and secure approach with MySQLi prepared statements
$conn = new mysqli("localhost", "root", "", "craftconnect");

// Check connection and terminate if there's an error
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Fetch distinct categories using a secure query
$categories = [];
$cat_sql = "SELECT DISTINCT category FROM products ORDER BY category ASC";
$cat_result = $conn->query($cat_sql);
if ($cat_result && $cat_result->num_rows > 0) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Sanitize user input and set up pagination variables
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 9;
$start = ($page - 1) * $limit;

// Fetch products for the current page using a prepared statement
$products = [];
$sql = "SELECT id, name, category, price, image_url FROM products ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);

// Bind the integer parameters "ii" for $start and $limit
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Craft Connect</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <link href="img/favicon.ico" rel="icon">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">

    <style>
        .category-circle {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #ddd;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .category-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category-circle:hover {
            transform: scale(1.05);
        }

        .font-weight-medium {
            font-weight: 500;
            font-size: 14px;
            color: #222;
        }

        .cat-item {
            height: 420px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .cat-item:hover {
            transform: scale(1.01);
        }

        .cat-img {
            height: 270px;
            width: 100%;
            overflow: hidden;
        }

        .cat-img img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            display: block;
        }

        .cat-item p {
            margin-bottom: 0.5rem;
            text-align: right;
            font-size: 14px;
            color: #555;
        }

        .cat-item h5 {
            text-align: center;
            margin-top: 10px;
            font-weight: 600;
        }

        .card.product-item {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-img {
            height: 250px;
            /* Adjust image height */
            overflow: hidden;
        }

        .product-img img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            display: block;
        }

        .card-body {
            flex-grow: 1;
        }
    </style>
</head>

<body>

    <!-- Topbar Start -->
    <div class="container-fluid">
        <div class="row bg-secondary py-2 px-xl-5">
            <div class="col-lg-6 d-none d-lg-block">
                <div class="d-inline-flex align-items-center">
                    <a class="text-dark" href="">FAQs</a>
                    <span class="text-muted px-2">|</span>
                    <a class="text-dark" href="">Help</a>
                    <span class="text-muted px-2">|</span>
                    <a class="text-dark" href="">Support</a>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <a class="text-dark px-2" href="https://www.facebook.com/profile.php?id=61578561308304">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a class="text-dark px-2" href="">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a class="text-dark px-2" href="">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a class="text-dark px-2"
                        href="https://www.instagram.com/cra_ftconnect?igsh=MTk1dHprYjM3OXY1MA%3D%3D&utm_source=qr">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a class="text-dark pl-2" href="http://youtube.com/@tutaart">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="row align-items-center py-3 px-xl-5">
            <div class="col-lg-3 d-none d-lg-block">
                <a href="" class="text-decoration-none">
                    <h1 class="m-0 display-5 font-weight-semi-bold"><span
                            class="text-primary font-weight-bold border px-3 mr-1">Craft</span>Connect</h1>
                </a>
            </div>
            <div class="col-lg-6 col-6 text-left ml-4">
                <form action="">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search for products">
                        <div class="input-group-append">
                            <span class="input-group-text bg-transparent text-primary">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-2 col-2 text-right">
                <!-- <a href="" class="btn border">
                    <i class="fas fa-heart text-primary"></i>
                    <span class="badge">0</span>
                </a> -->
                <a href="cart.php" class="btn border">
                    <i class="fas fa-shopping-cart text-primary"></i>
                    <span class="badge">0</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Topbar End -->

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
                            <a href="shop.php" class="nav-item nav-link active">Shop</a>
                            <a href="tutorial.php" class="nav-item nav-link">Tutorials</a>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages</a>
                                <div class="dropdown-menu rounded-0 m-0">
                                    <a href="cart.php" class="dropdown-item">Shopping Cart</a>
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
    <!-- Navbar End -->


    <!-- Horizontal Category Section Start -->
    <div class="container my-4">
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-4">

            <div class="text-center mx-3">
                <div class="category-circle">
                    <a href="decore.php"><img src="img/resin3.jpg" alt="Home Decor"></a>
                </div>
                <p class="mt-2 font-weight-medium">Home Decor</p>
            </div>

            <div class="text-center mx-3">
                <div class="category-circle">
                    <a href="accessories.php"><img src="img/resinjwl12.jpg" alt="Accessories"></a>
                </div>
                <p class="mt-2 font-weight-medium">Accessories</p>
            </div>

            <div class="text-center mx-3">
                <div class="category-circle">
                    <a href="pottery.php"><img src="img/cup4.jpg" alt="Pottery"></a>
                </div>
                <p class="mt-2 font-weight-medium">Pottery</p>
            </div>

            <div class="text-center mx-3">
                <div class="category-circle">
                    <a href="crochet.php"><img src="img/tou8.jpg" alt="Crochet"></a>
                </div>
                <p class="mt-2 font-weight-medium">Crochet</p>
            </div>

            <div class="text-center mx-3">
                <div class="category-circle">
                    <a href="bags.php"><img src="img/bag10.jpg" alt="Bags"></a>
                </div>
                <p class="mt-2 font-weight-medium">Bags</p>
            </div>

            <div class="text-center mx-3">
                <div class="category-circle">
                    <a href="sandals.php"><img src="img/sandal5.jpg" alt="Sandals"></a>
                </div>
                <p class="mt-2 font-weight-medium">Sandals</p>
            </div>

        </div>
    </div>
    <!-- Horizontal Category Section End -->
    </div>




    </div>
    <div class="container-fluid pt-5">
        <div class="text-center mb-4">
            <h2 class="section-title px-5"><span class="px-2">Trendy Products</span></h2>
        </div>
        <div class="row px-xl-5 pb-3">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="cat-item d-flex flex-column border mb-4">
                            <div class="cat-img position-relative overflow-hidden">
                                <?php
                                // ✅ Always load image from /img/ folder
                                $img = !empty($product['image_url']) ? 'img/' . basename($product['image_url']) : 'img/no-image.png';
                                ?>
                                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    class="product-img img-fluid" style="height: 220px; object-fit: cover; width: 100%;">
                            </div>

                            <h5 class="font-weight-semi-bold m-0 mt-2"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p>₹<?php echo htmlspecialchars($product['price']); ?></p>
                            <div class="d-flex justify-content-between mt-2">
                                <a href="detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye mr-1"></i>View Details
                                </a>
                                <a href="cart.php?id=<?php echo $product['id']; ?>&qty=1"
                                    class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-shopping-cart mr-1"></i>Add To Cart

                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </div>

    <div class="container-fluid bg-secondary text-dark mt-5 pt-5">
        <div class="row px-xl-5 pt-5">
            <div class="col-lg-4 col-md-12 mb-5 pr-3 pr-xl-5">
                <a href="#" class="text-decoration-none">
                    <h1 class="mb-4 display-5 font-weight-semi-bold">
                        <span class="text-primary font-weight-bold border border-white px-3 mr-1">Craft</span>Connect
                    </h1>
                </a>
                <p>Empowering artisans, homemakers, and small-scale creators by providing a platform to sell handmade
                    goods and share their skills through tutorials.</p>
                <p class="mb-2"><i class="fa fa-map-marker-alt text-primary mr-3"></i>123 Craft Street, Kerala, India
                </p>
                <p class="mb-2"><i class="fa fa-envelope text-primary mr-3"></i>support@craftconnect.in</p>
                <p class="mb-0"><i class="fa fa-phone-alt text-primary mr-3"></i>+91 98765 43210</p>
            </div>

            <div class="col-lg-4 col-md-6 mb-5">
                <h5 class="font-weight-bold text-dark mb-4">Quick Links</h5>
                <div class="d-flex flex-column justify-content-start">
                    <a class="text-dark mb-2" href="index.php"><i class="fa fa-angle-right mr-2"></i>Home</a>
                    <a class="text-dark mb-2" href="tutorials.php"><i class="fa fa-angle-right mr-2"></i>Tutorials</a>
                    <a class="text-dark mb-2" href="cart.php"><i class="fa fa-angle-right mr-2"></i>Cart</a>
                    <a class="text-dark mb-2" href="checkout.php"><i class="fa fa-angle-right mr-2"></i>Checkout</a>
                    <a class="text-dark" href="contact.php"><i class="fa fa-angle-right mr-2"></i>Contact Us</a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-5">
                <h5 class="font-weight-bold text-dark mb-4">Newsletter</h5>
                <form>
                    <div class="form-group">
                        <input type="text" class="form-control border-0 py-4" placeholder="Your Name" required />
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control border-0 py-4" placeholder="Your Email" required />
                    </div>
                    <div>
                        <button class="btn btn-primary btn-block border-0 py-3" type="submit">Subscribe Now</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row border-top border-light mx-xl-5 py-4">
            <div class="col-md-6 px-xl-0 text-center text-md-left">
                <p class="mb-md-0 text-dark">
                    &copy; <a class="text-dark font-weight-semi-bold" href="#">Craft Connect</a>. All Rights
                    Reserved.<br>
                    Designed by <a class="text-dark font-weight-semi-bold" href="https://htmlcodex.com"
                        target="_blank">HTML Codex</a>
                    | Customized by <a href="#">Thahina</a>
                </p>
            </div>
            <div class="col-md-6 px-xl-0 text-center text-md-right">
                <img class="img-fluid" src="img/payments.png" alt="Payment Methods">
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
</body>

</html>