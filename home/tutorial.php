<?php
session_start();
$user_name = $_SESSION['user_name'] ?? 'Guest';
$profile_img = $_SESSION['profile_img'] ?? 'img/default-user.png'; // default profile image
// DB connection
$conn = mysqli_connect("localhost", "root", "", "craftconnect");
if (!$conn)
    die("DB connection failed: " . mysqli_connect_error());

// Fetch all tutorial videos
$result = mysqli_query($conn, "SELECT * FROM tutorials ORDER BY id DESC"); // table: tutorials
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Craft Connect - Tutorials</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }

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

        .reel-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .horizontal-reel-container {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 20px;
            scroll-snap-type: x mandatory;
        }

        .reel {
            flex: 0 0 auto;
            width: 300px;
            height: 500px;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            background: #000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            scroll-snap-align: center;
        }

        .reel video,
        .reel iframe {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: none;
        }

        .reel-info {
            position: absolute;
            bottom: 10px;
            left: 10px;
            right: 10px;
            color: #fff;
            background: rgba(0, 0, 0, 0.5);
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 14px;
        }

        .reel-info a {
            color: #ffc107;
            text-decoration: underline;
            display: block;
            margin-top: 5px;
            font-size: 13px;
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
                            <a href="shop.php" class="nav-item nav-link ">Shop</a>
                            <a href="tutorial.php" class="nav-item nav-link active">Tutorials</a>
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

    <!-- Categories Section -->
    <div class="container my-4">
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-4">
            <div class="text-center mx-3">
                <div class="category-circle"><a href="decore.php"><img src="img/resin3.jpg" alt="Home Decor"></a></div>
                <p class="mt-2 font-weight-medium">Home Decore</p>
            </div>
            <div class="text-center mx-3">
                <div class="category-circle"><a href="accessories.php"><img src="img/resinjwl12.jpg"
                            alt="Accessories"></a></div>
                <p class="mt-2 font-weight-medium">Accessories</p>
            </div>
            <div class="text-center mx-3">
                <div class="category-circle"><a href="pottery.php"><img src="img/cup4.jpg" alt="Pottery"></a></div>
                <p class="mt-2 font-weight-medium">Pottery</p>
            </div>
            <div class="text-center mx-3">
                <div class="category-circle"><a href="crochet.php"><img src="img/tou8.jpg" alt="Crochet"></a></div>
                <p class="mt-2 font-weight-medium">Crochet</p>
            </div>
            <div class="text-center mx-3">
                <div class="category-circle"><a href="bags.php"><img src="img/bag10.jpg" alt="Bags"></a></div>
                <p class="mt-2 font-weight-medium">Bags</p>
            </div>
            <div class="text-center mx-3">
                <div class="category-circle"><a href="sandals.php"><img src="img/sandal5.jpg" alt="Sandals"></a></div>
                <p class="mt-2 font-weight-medium">Sandals</p>
            </div>
        </div>
    </div>

    <!-- Tutorial Videos from DB -->
    <div class="container">
        <h2 class="text-center mt-4">Tutorial Videos</h2>
        <div class="reel-container">
            <div class="horizontal-reel-container">
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $video_url = $row['video_url'] ?? '';
                        $caption = $row['caption'] ?? '';

                        if ($video_url) {
                            // Convert YouTube Shorts link to embed URL
                            $embed_url = str_replace(
                                ['https://youtube.com/shorts/', '?si=URepp9JS...'],
                                ['https://www.youtube.com/embed/', ''],
                                $video_url
                            );

                            echo '<div class="reel">';
                            echo '<iframe src="' . htmlspecialchars($embed_url) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                            echo '<div class="reel-info">' . htmlspecialchars($caption) . '</div>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<p>No tutorial videos found.</p>';
                }

                ?>
            </div>
        </div>
    </div>



    <!-- Footer -->



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
    <!-- Footer End -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const reels = document.querySelectorAll(".reel video");

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.play();    // play video in view
                    } else {
                        entry.target.pause();   // pause video out of view
                    }
                });
            }, { threshold: 0.6 }); // 60% of video should be visible to play

            reels.forEach(video => {
                observer.observe(video);
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>

</body>

</html>