<?php
$conn = mysqli_connect("localhost", "root", "", "craftconnect");
if (!$conn) die("DB connection failed: " . mysqli_connect_error());

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Product ID is missing.");
}

$product_id = intval($_GET['id']); // sanitize input

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Product not found.");
}

$product = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Contact | Craft Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .quantity-input { width: 60px; text-align: center; }
        .review-box { border-top: 1px solid #ccc; margin-top: 40px; padding-top: 20px; }
        .review-item { border-bottom: 1px solid #eee; margin-bottom: 15px; padding-bottom: 15px; }
    </style>
</head>
<body>
    <!-- Topbar -->
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
                <a class="text-dark px-2" href="#"><i class="fab fa-facebook-f"></i></a>
                <a class="text-dark px-2" href="#"><i class="fab fa-twitter"></i></a>
                <a class="text-dark px-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                <a class="text-dark px-2" href="#"><i class="fab fa-instagram"></i></a>
                <a class="text-dark pl-2" href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="row align-items-center py-3 px-xl-5">
        <div class="col-lg-3 d-none d-lg-block">
            <a href="index.php" class="text-decoration-none">
                <h1 class="m-0 display-5 font-weight-semi-bold">
                    <span class="text-primary font-weight-bold border px-3 mr-1">Craft</span>Connect
                </h1>
            </a>
        </div>
        <div class="col-lg-6 col-6 text-left ml-4">
            <form>
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search for products">
                    <div class="input-group-append">
                        <span class="input-group-text bg-transparent text-primary"><i class="fa fa-search"></i></span>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-2 col-2 text-right">
            <a href="#" class="btn border">
                <i class="fas fa-heart text-primary"></i>
                <span class="badge">0</span>
            </a>
            <a href="cart.php" class="btn border">
                <i class="fas fa-shopping-cart text-primary"></i>
                <span class="badge">0</span>
            </a>
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
                        <div class="navbar-nav ml-auto py-0">
                            <a href="logout.php" class="nav-item nav-link">Logout</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
<div class="container py-5">
    <div class="row">
        <div class="col-md-5">
            <?php 
                // ✅ Always load image from /img/ folder
                $img = !empty($product['image_url']) ? 'img/' . basename($product['image_url']) : 'img/no-image.png';
            ?>
            <img src="<?php echo $img; ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="img-fluid" 
                 style="width:100%; height:400px; object-fit:cover;">
        </div>
        <div class="col-md-7">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <p class="text-primary h4">₹<?php echo htmlspecialchars($product['price']); ?></p>
            <div class="d-flex align-items-center mb-3">
                <label class="mr-2">Quantity:</label>
                <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(-1)">-</button>
                <input type="number" class="form-control quantity-input mx-2" id="quantity" value="1" min="1">
                <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(1)">+</button>
            </div>
            <a href="cart.php?id=<?php echo $product['id']; ?>&qty=1" class="btn btn-primary">
                <i class="fa fa-shopping-cart mr-1"></i> Add to Cart
            </a>
            <button class="btn btn-outline-dark ml-2" data-toggle="modal" data-target="#reviewModal">Add Review</button>
        </div>
    </div>
</div>


    <div class="row mt-5">
        <div class="col-md-12">
            <h4>Description</h4>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
    </div>

    <div class="row review-box">
        <div class="col-md-12">
            <h5>Customer Reviews</h5>
            <div id="review-list"></div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add a Review</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Your Review</label>
                    <textarea id="reviewText" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitReview()">Submit</button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
<script>
function changeQty(delta) {
    const qtyInput = document.getElementById('quantity');
    let qty = parseInt(qtyInput.value);
    if (!isNaN(qty)) qtyInput.value = Math.max(1, qty + delta);
}

function submitReview() {
    const reviewText = document.getElementById('reviewText').value.trim();
    if (reviewText) {
        const reviewList = document.getElementById('review-list');
        const div = document.createElement('div');
        div.className = 'review-item';
        div.innerHTML = `<p>${reviewText}</p><small>Just now</small>`;
        reviewList.prepend(div);
        document.getElementById('reviewText').value = '';
        $('#reviewModal').modal('hide');
    } else alert('Please enter a review.');
}
</script>
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

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>


  </body>
</html>
