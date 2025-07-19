<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM data_sekolah WHERE id = :id");
$stmt->execute(['id' => $id]);

header('Location: data_sekolah.php');
exit;
