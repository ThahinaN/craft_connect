<?php
// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
$conn = mysqli_connect("localhost", "root", "", "craftconnect");

if (!$conn) {
    die("❌ DB connection failed: " . mysqli_connect_error());
}

// 🔐 Encryption Functions (only if needed here)
$secretKey = "craftconnect@123"; // Keep this secret!

function encryptPassword($password, $secretKey) {
    $cipher = "AES-128-CTR";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
    $encrypted = openssl_encrypt($password, $cipher, $secretKey, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptPassword($encryptedData, $secretKey) {
    $cipher = "AES-128-CTR";
    $decoded = base64_decode($encryptedData);
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = substr($decoded, 0, $ivLength);
    $encrypted = substr($decoded, $ivLength);
    return openssl_decrypt($encrypted, $cipher, $secretKey, 0, $iv);
}
?>

