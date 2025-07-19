<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['username'] !== 'admin') {
    header('Location: data_alat.php?error=Anda tidak memiliki akses.');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?error=ID tidak valid.');
    exit;
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM alat WHERE id = ?");
    $stmt->execute([$id]);
    $alat = $stmt->fetch();
    if (!$alat) {
        header('Location: data_alat.php?error=Data alat tidak ditemukan.');
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM alat WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: data_alat.php?success=Data alat berhasil dihapus.');
    exit;
} catch (PDOException $e) {
    header('Location: data_alat.php?error=Kesalahan saat menghapus data.');
    exit;
}
?>
