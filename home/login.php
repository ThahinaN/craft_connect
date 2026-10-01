<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'dbconn.php';

$message = "";
$alert_class = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect login credentials safely
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $message = "❌ Please enter both email and password.";

    } else {
        // Check if user exists
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // 1. Password Verification
            if (password_verify($password, $user['password'])) {

                // 2. Account Approval Check
                if ($user['status'] !== 'approved') {
                    // Specific message for unapproved users
                    $message = "⚠️ Your account is **pending admin approval**. Please wait for confirmation.";
                } else {
                    // Approved user – proceed to set session variables and redirect
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] == 'seller') {
                        $_SESSION['seller_id'] = $user['id'];
                        header("Location: ../seller/seller-dashboard.php");
                        exit; 
                    } elseif ($user['role'] == 'buyer') {
                        $_SESSION['buyer_id'] = $user['id'];
                        header("Location: ../buyer/dashboard.php");
                        exit; 
                    } elseif ($user['role'] == 'admin') {
                        $_SESSION['admin_id'] = $user['id'];
                        header("Location: ../admin/admin-dashboard.php");
                        exit; 
                    }
                }

            } else {
                // Specific message for wrong password (combined for security)
                $message = "❌ **Invalid email or password.** Please try again.";
            }

        } else {
            // Specific message for user not found (combined for security)
            $message = "❌ **Invalid email or password.** Please try again.";
        }

        $stmt->close();
    }

    // Determine alert class based on message content
    if (!empty($message)) {
        if (strpos($message, '⚠️') !== false) {
            $alert_class = 'alert-warning'; 
        } else {
            $alert_class = 'alert-danger';  
        }
    }
}

// Close connection before including HTML
$conn->close();

// Include the HTML form template to render the page
include 'login.html';
?>
