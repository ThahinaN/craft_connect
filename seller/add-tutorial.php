<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "craftconnect");
if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}

// Replace with actual logged-in seller ID from session

// ✅ Check if seller is logged in
if (!isset($_SESSION['seller_id'])) {
    header("Location: ../home/login.html");
    exit();
}

$seller_id = $_SESSION['seller_id'];

// Fetch seller info including profile picture
$seller_sql = "SELECT name, profile_pic FROM users WHERE id = ?";
$seller_stmt = $conn->prepare($seller_sql);
$seller_stmt->bind_param("i", $seller_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_stmt->close();

// Determine profile image path
$profile_img = !empty($seller['profile_pic']) ? '../' . htmlspecialchars($seller['profile_pic']) : '../img/seller.jpg';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caption = mysqli_real_escape_string($conn, $_POST['caption']);
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $video_path = "";

    // --- Handle file upload ---
    if (!empty($_FILES['video']['name'])) {
        $video = $_FILES['video'];
        $target_dir = "videos/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $target_file = $target_dir . basename($video["name"]);
        $videoFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowedTypes = ['mp4', 'webm', 'ogg', 'mov'];

        if (in_array($videoFileType, $allowedTypes)) {
            if (move_uploaded_file($video["tmp_name"], $target_file)) {
                $video_path = $target_file;
            } else {
                $message = "<div class='error-message'>Failed to upload video file.</div>";
            }
        } else {
            $message = "<div class='error-message'>Invalid file type! Allowed: mp4, webm, ogg, mov.</div>";
        }
    }

    // --- Handle video URL if no file uploaded ---
    if (empty($video_path) && !empty($_POST['video_url'])) {
        $video_url = trim($_POST['video_url']);
        if (filter_var($video_url, FILTER_VALIDATE_URL)) {
            $video_path = mysqli_real_escape_string($conn, $video_url);
        } else {
            $message = "<div class='error-message'>Invalid video URL.</div>";
        }
    }

    // --- Insert into database ---
    if (!empty($video_path) && empty($message)) {
        $query = "INSERT INTO tutorials (seller_id, title, video_url, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $seller_id, $caption, $video_path, $description);

        if ($stmt->execute()) {
            $message = "<div class='success-message'>🎉 Tutorial saved successfully!</div>";
        } else {
            $message = "<div class='error-message'>Database error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } elseif (empty($video_path)) {
        $message = "<div class='error-message'>Please upload a video file or enter a video URL.</div>";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Upload Tutorial | Seller Panel</title>
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
    <div class="profile-img bg-img" style="background-image: url('<?php echo $profile_img; ?>')"></div>
    <h4><?php echo htmlspecialchars($seller['name']); ?></h4>
    <small>Crafts Seller</small>
</div>

        <div class="side-menu">
            <ul>
                <li><a href="../home/index.php"><span class="las la-user"></span><small>Home</small></a></li>
                <li><a href="seller-dashboard.php"><span class="las la-home"></span><small>Dashboard</small></a></li>
                <li><a href="add-product.php"><span class="las la-plus-square"></span><small>Add Product</small></a></li>
                <li><a href="add-tutorial.php" class="active"><span class="las la-video"></span><small>Upload Tutorial</small></a></li>
                <li><a href="my-products.php"><span class="las la-box-open"></span><small>My Products</small></a></li>
                <li><a href="my-orders.php"><span class="las la-shopping-cart"></span><small>Orders</small></a></li>
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
            <h1>Upload Tutorial Video</h1>
            <small>Share your crafting skills with buyers!</small>
        </div>

    
        <div class="page-content">
            <div class="form-container">
                
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="caption">Title / Caption</label>
            <input type="text" id="caption" name="caption" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        <div class="form-group">
            <label for="video">Upload Video File</label>
            <input type="file" id="video" name="video" accept="video/*">
            <small>Optional if using a video URL.</small>
        </div>
        <div class="form-group">
            <label for="video_url">Or Enter Video URL</label>
            <input type="url" id="video_url" name="video_url" placeholder="https://">
            <small>Optional if uploading a file.</small>
        </div>
        <button type="submit" class="btn-primary">Save Tutorial</button>
    </form>
</div>
    </main>
</div>

<style>
.form-container {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
}
.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}
.form-group input:focus {
    outline: none;
    border-color: #007bff;
}
.btn-primary {
    background: #007bff;
    color: #fff;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s;
}
.btn-primary:hover {
    background: #0056b3;
}
.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}
.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border: 1px solid #f5c6cb;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}
</style>
</body>
</html>
