<?php
require_once 'config.php'; // Pastikan ini mengandung koneksi ke database

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

// Mengambil data dari tabel data_sekolah
$stmt = $pdo->query("SELECT * FROM data_sekolah");
$dataSekolah = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Data Sekolah - Sukarobot Academy</title>
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
            margin-left: 170px;
        }

        /* Dropdown Custom */
        .dropdown-menu.custom-dropdown-menu {
            background-color: #0d6efd;
            border-radius: 10px;
            padding: 5px 0;
            min-width: 140px;
            position: absolute;
            top: 55px;
            left: 0;
            right: 0;
            margin: auto;
            z-index: 9999;
        }

        .dropdown-menu.custom-dropdown-menu .dropdown-item {
            color: white;
            font-size: 0.75rem;
            padding: 6px 15px;
            text-align: left;
        }

        .dropdown-menu.custom-dropdown-menu .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Tabel Styling */
        table {
            width: 100%;
            margin-top: 20px;
        }

        th {
            background-color: #0d6efd;
            color: white;
        }

        td {
            vertical-align: middle;
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

            .menu-text {
                font-size: 0.65rem;
            }

            .dropdown-menu.custom-dropdown-menu {
                top: auto !important;
                bottom: 60px !important;
                width: 90%;
            }

            .dropdown-menu.custom-dropdown-menu .dropdown-item {
                text-align: center;
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
      <li>  <?php if ($isAdmin): ?><a class="dropdown-item" href="data_trainer.php"><i class="bi bi-person-lines-fill me-2"></i> Data Trainer</a>  <?php endif; ?></li>
      <li>  <?php if ($isAdmin): ?><a class="dropdown-item" href="data_sekolah.php"><i class="bi bi-building me-2"></i> Data Sekolah</a>  <?php endif; ?></li>
      <li><?php if ($isAdmin): ?><a class="dropdown-item" href="data_kehilangan.php"><i class="bi bi-building me-2"></i> Data Kehilangan</a><?php endif; ?></li>
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
        <h1>Data Sekolah</h1>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Sekolah</th>
            <th>Lokasi Sekolah</th>
            <th>Aksi</th>
        </tr>
    </thead>
        <tbody>
            <?php if (count($dataSekolah) > 0): ?>
                <?php foreach ($dataSekolah as $index => $sekolah): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($sekolah['nama_sekolah']) ?></td>
                        <td><?= htmlspecialchars($sekolah['lokasi_sekolah']) ?></td>
                        <td>
                            <a href="edit_sekolah.php?id=<?= $sekolah['id'] ?>" class="btn btn-warning">Edit</a>
                            <a href="delete_sekolah.php?id=<?= $sekolah['id'] ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus?');">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data tersedia.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="create_sekolah.php" class="btn btn-success">Tambah Sekolah</a>
        <a href="index.php" class="btn btn-primary">Kembali</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
