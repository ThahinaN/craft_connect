<?php
session_start();
$user_name = $_SESSION['user_name'] ?? 'Guest';
$profile_img = $_SESSION['profile_img'] ?? 'img/default-user.png'; // default profile image
// Set database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "craftconnect";
$status_message = "";
$status_class = "";

// ----------------------------------------------------
// MODIFIED: Check for order ID in the URL to pre-fill the form
// ----------------------------------------------------
$initial_order_id = $_GET['order_id'] ?? '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize and validate input
    $name = trim(htmlspecialchars($_POST['name'] ?? ''));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = trim(htmlspecialchars($_POST['subject'] ?? ''));
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));

    // ----------------------------------------------------
    // NEW: Handle optional order_id
    // ----------------------------------------------------
    $order_id_input = trim($_POST['order_id'] ?? '');
    // Convert empty string to NULL, or cast to integer
    $order_id = !empty($order_id_input) ? (int) $order_id_input : null;

    if (empty($name) || empty($email) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $status_message = "Please fill in all required fields (Name, Email, Subject, Message) correctly.";
        $status_class = "alert-danger";
    } else {
        // 2. Connect to the database
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            $status_message = "Database connection failed: " . $conn->connect_error;
            $status_class = "alert-danger";
        } else {
            // 3. Prepare and execute the INSERT query
            // MODIFIED: Use 'order_id' column for the new functionality
            // IMPORTANT: Ensure your 'contact_messages' table has an 'order_id' column that accepts NULL.
            $sql = "INSERT INTO contact_messages (name, email, subject, message, order_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                // MODIFIED: Bind parameter 'i' for integer type for order_id
                $stmt->bind_param("ssssi", $name, $email, $subject, $message, $order_id);

                if ($stmt->execute()) {
                    $status_message = "Your message has been sent successfully. We will get back to you shortly!";
                    $status_class = "alert-success";

                    // Reset fields on success (except for the initial order ID if available)
                    $name = $email = $subject = $message = "";
                    // Update the initial variable to ensure the form field is cleared or pre-filled correctly
                    $initial_order_id = $order_id_input;
                } else {
                    $status_message = "Error sending message: " . $stmt->error;
                    $status_class = "alert-danger";
                }
                $stmt->close();
            } else {
                $status_message = "Database query preparation failed: " . $conn->error;
                $status_class = "alert-danger";
            }
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Contact | Craft Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="img/favicon.ico" rel="icon">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff;
        }

        .section-title {
            font-weight: 600;
            color: #222;
            text-align: center;
        }

        .contact-form .form-control {
            border-radius: 0.5rem;
        }

        .btn-primary {
            background-color: #d19c97;
            border-color: #d19c97;
        }

        .btn-primary:hover {
            background-color: #c58a85;
            border-color: #c58a85;
        }
    </style>
</head>

<body>

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
                            <span class="input-group-text bg-transparent text-primary"><i
                                    class="fa fa-search"></i></span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-2 col-2 text-right">
                <!-- <a href="#" class="btn border">
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

    <div class="container-fluid mb-3">
        <div class="row px-xl-5">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg bg-light navbar-light py-3 px-0">
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
                            <a href="contact.php" class="nav-item nav-link active">Contact</a>
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

    <div class="container-fluid bg-secondary mb-5">
        <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 250px;">
            <h1 class="font-weight-semi-bold text-uppercase mb-3">Contact Us</h1>
            <div class="d-inline-flex">
                <p class="m-0"><a href="index.php">Home</a></p>
                <p class="m-0 px-2">-</p>
                <p class="m-0">Contact</p>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row px-xl-5">
            <div class="col-lg-6 mb-5">
                <h2 class="section-title px-5" style="text-decoration: none;"><span class="px-2">Send a Message</span>
                </h2>

                <?php if (!empty($status_message)): ?>
                    <div class="alert <?php echo $status_class; ?> alert-dismissible fade show" role="alert">
                        <?php echo $status_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST">
                    <input type="text" class="form-control mb-3" name="name" placeholder="Your Name" required
                        value="<?php echo htmlspecialchars($_POST['name'] ?? $name ?? ''); ?>">
                    <input type="email" class="form-control mb-3" name="email" placeholder="Your Email" required
                        value="<?php echo htmlspecialchars($_POST['email'] ?? $email ?? ''); ?>">
                    <input type="text" class="form-control mb-3" name="subject" placeholder="Subject" required
                        value="<?php echo htmlspecialchars($_POST['subject'] ?? $subject ?? ''); ?>">

                    <!-- MODIFIED: Order ID field -->
                    <input type="number" class="form-control mb-3" name="order_id"
                        placeholder="Order ID (Optional - If related to a specific purchase)"
                        value="<?php echo htmlspecialchars($initial_order_id); ?>" min="1">

                    <textarea class="form-control mb-3" rows="6" name="message" placeholder="Message"
                        required><?php echo htmlspecialchars($_POST['message'] ?? $message ?? ''); ?></textarea>
                    <button class="btn btn-primary btn-block py-2" type="submit">Send Message</button>
                </form>
            </div>
            <div class="col-lg-6 mb-5">
                <h5 class="font-weight-semi-bold mb-3" style="text-decoration: none;">Get In Touch</h5>
                <p>Have questions about our platform or your account? Reach out to us directly!</p>
                <p><i class="fa fa-map-marker-alt text-primary mr-3"></i>123 Craft Street, Kerala, India</p>
                <p><i class="fa fa-envelope text-primary mr-3"></i>support@craftconnect.in</p>
                <p><i class="fa fa-phone-alt text-primary mr-3"></i>+91 98765 43210</p>
            </div>
        </div>
    </div>

    <div class="container-fluid bg-secondary text-dark pt-5">
        <div class="row px-xl-5 pt-5">
            <div class="col-lg-4 col-md-12 mb-5 pr-3 pr-xl-5">
                <a href="#" class="text-decoration-none">
                    <h1 class="mb-4 display-5 font-weight-semi-bold">
                        <span class="text-primary font-weight-bold border border-white px-3 mr-1">Craft</span>Connect
                    </h1>
                </a>
                <p>Empowering artisans, homemakers, and creators through e-commerce and skills-sharing.</p>
                <p><i class="fa fa-map-marker-alt text-primary mr-3"></i>123 Craft Street, Kerala, India</p>
                <p><i class="fa fa-envelope text-primary mr-3"></i>support@craftconnect.in</p>
                <p><i class="fa fa-phone-alt text-primary mr-3"></i>+91 98765 43210</p>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="row">
                    <div class="col-md-4 mb-5">
                        <h5 class="font-weight-bold text-dark mb-4">Quick Links</h5>
                        <div class="d-flex flex-column">
                            <a class="text-dark mb-2" href="index.php">Home</a>
                            <a class="text-dark mb-2" href="shop.php">Shop</a>
                            <a class="text-dark mb-2" href="cart.php">Cart</a>
                            <a class="text-dark mb-2" href="checkout.php">Checkout</a>
                            <a class="text-dark" href="contact.php">Contact Us</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <h5 class="font-weight-bold text-dark mb-4">Newsletter</h5>
                        <form>
                            <input type="text" class="form-control mb-2" placeholder="Your Name">
                            <input type="email" class="form-control mb-2" placeholder="Your Email">
                            <button class="btn btn-primary btn-block" type="submit">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>

</body>

</html>