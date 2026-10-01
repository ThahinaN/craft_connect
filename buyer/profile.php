<?php
session_start();

// Use a secure and modern MySQLi approach
$conn = new mysqli("localhost", "root", "", "craftconnect");

// Check connection and terminate if there's an error
if ($conn->connect_error) {
die("DB connection failed: " . $conn->connect_error);
}
// ✅ Check if admin is logged in
if (!isset($_SESSION['buyer_id'])) {
    header("Location: ../home/login.html");
    exit();
}

$buyer_id = $_SESSION['buyer_id'];

// Fetch buyer information from the 'users' table
$sql = "SELECT name, email, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

$buyer_name = $user['name'] ?? '';
$buyer_email = $user['email'] ?? '';
$buyer_phone = $user['phone'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
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
                    <li><a href="dashboard.php"><span class="las la-home"></span><small>Dashboard</small></a></li>
                   <li><a href="profile.php" class="active"><span class="las la-user"></span><small>Profile</small></a></li>
                 <li><a href="../home/shop.php"><span class="las la-shopping-bag"></span><small>Shop</small></a></li>
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
                <h1>My Profile</h1>
                <small>Home / Profile</small>
            </div>

            <div class="page-content">
                <div class="profile-page">
                    <div class="profile-info">
                        <div class="profile-header">
                            <div class="profile-img" style="background-image: url('../img/placeholder-profile.png');"></div>
                            <h4><?php echo htmlspecialchars($buyer_name); ?></h4>
                            <small><?php echo htmlspecialchars($buyer_email); ?></small>
                        </div>
                        
                        <form action="update-profile.php" method="POST">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($buyer_name); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($buyer_email); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number (Optional)</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($buyer_phone); ?>">
                            </div>
                            <button type="submit" class="btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>