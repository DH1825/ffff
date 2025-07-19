<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaSekolah = $_POST['nama_sekolah'];
    $lokasiSekolah = $_POST['lokasi_sekolah'];

    $stmt = $pdo->prepare("INSERT INTO data_sekolah (nama_sekolah, lokasi_sekolah) VALUES (:nama_sekolah, :lokasi_sekolah)");
    $stmt->execute(['nama_sekolah' => $namaSekolah, 'lokasi_sekolah' => $lokasiSekolah]);

    header('Location: data_sekolah.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Tambah Data Sekolah - Sukarobot Academy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        .layout {
            display: flex;
            min-height: 100vh;
            flex-direction: row;
        }

        .sidebar {
            width: 150px;
            background-color: #0d6efd;
            color: white;
            padding: 20px 0;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .menu-item {
            text-align: center;
            margin: 5px 0;
            color: white;
            text-decoration: none;
        }

        .icon-box {
            width: 50px;
            height: 50px;
            background-color: white;
            color: #0d6efd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 5px;
            transition: all 0.3s ease;
        }

        .icon-box:hover {
            background-color: #e0e0e0;
        }

        .menu-text {
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }

        .main-content {
            flex-grow: 1;
            padding: 30px;
            margin-left: 170px; /* Menyesuaikan margin untuk menghindari tumpang tindih */
        }

        @media (max-width: 768px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                position: fixed;
                bottom: 0;
                top: auto;
                left: 0;
                width: 100%;
                height: auto;
                flex-direction: row;
                justify-content: space-around;
                padding: 10px 0;
            }

            .main-content {
                padding-bottom: 120px;
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
  <div class="layout">
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Dashboard -->
      <a href="index.php" class="menu-item">
        <div class="icon-box"><i class="bi bi-house-door"></i></div>
        <div class="menu-text">Dashboard</div>
      </a>

      <!-- Dropdown Data -->
      <div class="menu-item dropdown position-relative">
        <div class="icon-box dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
          <i class="bi bi-folder"></i>
        </div>
        <div class="menu-text">Data</div>
        <ul class="dropdown-menu custom-dropdown-menu shadow fade show-on-hover">
          <li><a class="dropdown-item" href="data_alat.php"><i class="bi bi-cpu me-2"></i> Data Komponen</a></li>
          <li><a class="dropdown-item" href="data_peminjaman.php"><i class="bi bi-arrow-left-right me-2"></i> Data Peminjaman</a></li>
          <li><?php if ($isAdmin): ?><a class="dropdown-item" href="data_trainer.php"><i class="bi bi-person-lines-fill me-2"></i> Data Trainer</a><?php endif; ?></li>
          <li><?php if ($isAdmin): ?><a class="dropdown-item" href="data_sekolah.php"><i class="bi bi-building me-2"></i> Data Sekolah</a><?php endif; ?></li>
        </ul>
      </div>

      <!-- Ambil -->
      <a href="kebutuhan_alat.php" class="menu-item">
        <div class="icon-box"><i class="bi bi-arrow-down-circle"></i></div>
        <div class="menu-text">Ambil</div>
      </a>

      <!-- Cek (admin only) -->
      <?php if ($isAdmin): ?>
      <a href="stok_opname.php" class="menu-item">
        <div class="icon-box"><i class="bi bi-bar-chart-line"></i></div>
        <div class="menu-text">Cek</div>
      </a>
      <?php endif; ?>

      <!-- Logout -->
      <a href="logout.php" class="menu-item">
        <div class="icon-box bg-danger text-white"><i class="bi bi-box-arrow-right"></i></div>
        <div class="menu-text text-danger fw-bold">Logout</div>
      </a>
    </div>

    <div class="main-content">
        <div class="container mt-5">
            <h1>Tambah Data Sekolah</h1>
            <form method="POST">
                <div class="mb-3">
                    <label for="nama_sekolah" class="form-label">Nama Sekolah:</label>
                    <input type="text" name="nama_sekolah" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="lokasi_sekolah" class="form-label">Lokasi Sekolah:</label>
                    <input type="text" name="lokasi_sekolah" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
            <a href="data_sekolah.php" class="btn btn-secondary mt-3">Kembali</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
