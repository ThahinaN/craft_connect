<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "craftconnect");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Set the category
$category = 'Pottery';
$priceFilter = isset($_GET['price']) ? $_GET['price'] : [];

// Prepare base SQL
$product_sql = "SELECT id, name, image_url, price FROM products WHERE category = ?";

// Add price filter dynamically
if (!empty($priceFilter)) {
    $priceConditions = [];
    foreach ($priceFilter as $range) {
        list($min, $max) = explode('-', $range);
        $priceConditions[] = "(price BETWEEN $min AND $max)";
    }
    $product_sql .= " AND (" . implode(" OR ", $priceConditions) . ")";
}

$product_sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($product_sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Craft Connect </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
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
                    <a class="text-dark px-2" href="">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a class="text-dark px-2" href="">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a class="text-dark px-2" href="">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a class="text-dark px-2" href="">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a class="text-dark pl-2" href="">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="row align-items-center py-3 px-xl-5">
            <div class="col-lg-3 d-none d-lg-block">
                <a href="" class="text-decoration-none">
                    <h1 class="m-0 display-5 font-weight-semi-bold"><span class="text-primary font-weight-bold border px-3 mr-1">Craft</span>Connect</h1>
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
                <a href="" class="btn border">
                    <i class="fas fa-shopping-cart text-primary"></i>
                    <span class="badge">0</span>
                </a>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

<!-- Navbar Start -->
    <div class="container-fluid mb-5">
        <div class="row border-top px-xl-80">
            
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

              <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages</a>
                <div class="dropdown-menu rounded-0 m-0">
                     <a href="cart.php" class="dropdown-item">Shopping Cart</a>
                    <a href="checkout.php" class="dropdown-item">Checkout</a>
                </div>
            </div>
            <a href="contact.php" class="nav-item nav-link">Contact</a>
        </div>
        <div class="navbar-nav ml-auto py-0">
 <a href="logout.php" class="nav-item nav-link">Log out</a>
        </div>
    </div>
</nav>
                <div id="header-carousel" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active" style="height: 410px;">
            <img class="img-fluid" src="img/pottery3.jpg" alt="Image" style="height: 100%; width: 100%; object-fit: cover;">
            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                <div class="p-3" style="max-width: 700px;">
                    <h4 class="text-light text-uppercase font-weight-medium mb-3">10% Off Your First Order</h4>
                    <h3 class="display-4 text-white font-weight-semi-bold mb-4">Fashionable Pottery</h3>
                    <a href="shop.php" class="btn btn-light py-2 px-3">Shop Now</a>
                </div>
            </div>
        </div>
        <div class="carousel-item" style="height: 410px;">
            <img src="img/Pottery5.jpg" alt="Image" style="width: 100%; height: 100%; object-fit: cover;">
            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                <div class="p-3" style="max-width: 700px;">
                    <h4 class="text-light text-uppercase font-weight-medium mb-3">10% Off Your First Order</h4>
                    <h3 class="display-4 text-white font-weight-semi-bold mb-4">Reasonable Price</h3>
                    <a href="shop.php" class="btn btn-light py-2 px-3">Shop Now</a>
                </div>
            </div>
        </div>
        <div class="carousel-item" style="height: 410px;">
            <img src="img/Pottery7.jpg" alt="Image" style="width: 100%; height: 100%; object-fit: cover;">
            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                <div class="p-3" style="max-width: 700px;">
                    <h4 class="text-light text-uppercase font-weight-medium mb-3">100% Handmade Products</h4>
                    <h3 class="display-4 text-white font-weight-semi-bold mb-4">High Quality</h3>
                    <a href="shop.php" class="btn btn-light py-2 px-3">Shop Now</a>
                </div>
            </div>
        </div>
    </div>
    <a class="carousel-control-prev" href="#header-carousel" data-slide="prev">
        <div class="btn btn-dark" style="width: 45px; height: 45px;">
            <span class="carousel-control-prev-icon mb-n2"></span>
        </div>
    </a>
    <a class="carousel-control-next" href="#header-carousel" data-slide="next">
        <div class="btn btn-dark" style="width: 45px; height: 45px;">
            <span class="carousel-control-next-icon mb-n2"></span>
        </div>
    </a>
         </div>
         </div>
        </div>
    </div>
    <!-- Navbar End -->


  


   
     <!-- Shop Start -->
    <div class="container-fluid pt-5">
        <div class="row px-xl-5">
            <div class="col-lg-3 col-md-12">
                <!-- Price Filter -->
                <div class="border-bottom mb-4 pb-4">
                    <h5 class="font-weight-semi-bold mb-4">Filter by price</h5>
                    <form method="GET">
                        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
                            <input type="checkbox" class="custom-control-input" id="price1" name="price[]" value="0-500" <?php echo in_array('0-500', $priceFilter) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="price1">₹0 - ₹500</label>
                        </div>
                        <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between mb-3">
                            <input type="checkbox" class="custom-control-input" id="price2" name="price[]" value="501-1000" <?php echo in_array('501-1000', $priceFilter) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="price2">₹501 - ₹1000</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </form>
                </div>
            </div>
<!-- Shop Products Section -->
<div class="col-lg-9 col-md-12">
    <div class="row pb-3">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                <div class="cat-item d-flex flex-column border mb-4">
                    <div class="cat-img position-relative overflow-hidden">
                        <?php 
                        // ✅ Always load image from /img/ folder
                        $img = !empty($product['image_url']) ? 'img/' . basename($product['image_url']) : 'img/no-image.png';
                        ?>
                        <img src="<?php echo $img; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-img img-fluid" 
                             style="height: 220px; object-fit: cover; width: 100%;">
                    </div>

                    <h5 class="font-weight-semi-bold m-0 mt-2"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p>₹<?php echo htmlspecialchars($product['price']); ?></p>
                    <div class="d-flex justify-content-between mt-2">
                        <a href="detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye mr-1"></i>View Details
                        </a>
                        <a href="cart.php?id=<?php echo $product['id']; ?>&qty=1" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-shopping-cart mr-1"></i>Add To Cart
                            
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">No products found in this category.</div>
        <?php endif; ?>
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


    <!-- Back to Top -->
    <a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Contact Javascript File -->
    <script src="mail/jqBootstrapValidation.min.js"></script>
    <script src="mail/contact.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>