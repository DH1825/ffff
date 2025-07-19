<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM data_peminjaman_alat");
        $stmt->execute();
        header('Location: data_peminjaman.php?success=Semua data peminjaman telah dihapus.');
        exit;
    } catch (PDOException $e) {
        header('Location: data_peminjaman.php?error=Terjadi kesalahan saat menghapus data: ' . htmlspecialchars($e->getMessage()));
        exit;
    }
}
?>