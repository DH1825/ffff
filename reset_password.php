<?php
require_once 'config.php'; // koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $newPassword = trim($_POST['newPassword']);

    // Update di database
    $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE username = ?");
    if ($stmt->execute([$newPassword, $username])) {
        echo "Password updated successfully.";
    } else {
        echo "Failed to update password.";
    }
}
?>
