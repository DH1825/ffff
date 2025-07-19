<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['username'] !== 'admin') {
    header('Location: data_alat.php?error=Anda tidak memiliki akses.');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: data_trainer.php?error=ID tidak valid');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("DELETE FROM trainer1 WHERE id = ?");
if ($stmt->execute([$id])) {
    header('Location: data_trainer.php?success=Data Trainer Berhasil Dihapus');
    exit;
} else {
    header('Location: data_trainer.php?error=Gagal Menghapus Data');
    exit;
}