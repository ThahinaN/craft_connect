<?php
require_once 'dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form values
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $phone    = trim($_POST['phone']);
    $profile_img = $_POST['profile_pic']; // default
    $role     = $_POST['role'];

    // 1️⃣ Validate password match
    if ($password !== $confirm) {
        die("<script>alert('❌ Passwords do not match.'); window.history.back();</script>");
    }

    // 2️⃣ Validate phone number (10 digits)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        die("<script>alert('❌ Phone number must be exactly 10 digits.'); window.history.back();</script>");
    }

    // 3️⃣ Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        die("<script>alert('❌ Email already registered.'); window.history.back();</script>");
    }
    $stmt->close();

    // 4️⃣ Hash the password
    $hashed = password_hash($password, PASSWORD_DEFAULT);
// 5️⃣ Handle profile picture upload
$profile_img = null;
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR; // full absolute path

    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // full permission for Windows
    }

    $filename = time() . "_" . basename($_FILES['profile_pic']['name']);
    $targetFile = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
        // Save relative path for database use
        $profile_img = "home/img/" . $filename;
    } else {
        echo "<script>alert('❌ Failed to move uploaded file.');</script>";
    }
}

    // 6️⃣ Insert into DB
    $sql = "INSERT INTO users (name, email, password, role, phone, profile_pic) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $hashed, $role, $phone, $profile_img);

    if (mysqli_stmt_execute($stmt)) {
        echo '<script>alert("✅ Successfully Registered"); location.replace("login.html");</script>';
    } else {
        echo "❌ Error inserting user: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}
?>
