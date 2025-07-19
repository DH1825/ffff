<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

$stmt = $pdo->query("SELECT tingkatan_alat, SUM(jumlah_alat) as total FROM alat GROUP BY tingkatan_alat");
$dataChart = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalAlat = $pdo->query("SELECT COUNT(*) FROM alat")->fetchColumn();
$stmt = $pdo->query("SELECT kode_peminjaman FROM data_peminjaman_alat");
$kodePeminjamanList = $stmt->fetchAll(PDO::FETCH_COLUMN);
// Menghitung jumlah unik kode_peminjaman
$totalPeminjaman = $pdo->query("SELECT COUNT(DISTINCT kode_peminjaman) FROM data_peminjaman_alat")->fetchColumn();
$totalTrainer = $pdo->query("SELECT COUNT(*) FROM trainer1")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Sukarobot Academy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <style>
    /* Custom Dropdown */
.custom-dropdown-menu {
  background-color: white;
  border: none;
  border-radius: 10px;
  padding: 8px 0;
  min-width: 200px;
  transform: translateY(5px);
}

.custom-dropdown-menu .dropdown-item {
  color: #0d6efd;
  font-size: 0.85rem;
  padding: 8px 15px;
  transition: 0.2s;
}

.custom-dropdown-menu .dropdown-item:hover {
  background-color: #e8f0fe;
  color: #0a58ca;
}

/* Hover show fix (for sidebar dropdown) */
.show-on-hover {
  display: none;
  position: absolute;
  top: 65px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1050;
}

.dropdown:hover .show-on-hover {
  display: block;
}

/* Responsive adjustment */
@media (max-width: 768px) {
  .show-on-hover {
    top: auto;
    bottom: 60px;
    left: 10%;
    right: 10%;
    transform: none;
    width: 80%;
  }
}

    html, body {
      overflow-x: hidden;
      width: 100%;
      box-sizing: border-box;
    }
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }

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
      z-index: 1000;
    }

    .menu-item {
      text-align: center;
      margin: 5px 0;
      color: white;
      text-decoration: none;
      position: relative;
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

    .welcome-text {
      font-size: 2rem;
      font-weight: bold;
      color: #0d6efd;
      text-align: center;
      margin-bottom: 30px;
      font-family: 'Poppins', sans-serif;
    }

    .card-stat {
      text-align: center;
      padding: 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .chart-container {
      margin-top: 40px;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
    <!-- SIDEBAR -->
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


    <!-- Main Content -->
    <div class="main-content">
      <div class="welcome-text">Selamat Datang di Dashboard Tools App</div>

      <!-- Statistik -->
      <div class="row text-center mb-4">
    <div class="col-12 mb-3 <?= $isAdmin ? 'col-md-4' : 'col-md-6' ?>">
        <div class="card-stat">
            <h5>Total Komponen</h5>
            <h2><?= $totalAlat ?></h2>
        </div>
    </div>

    <div class="col-12 mb-3 <?= $isAdmin ? 'col-md-4' : 'col-md-6' ?>">
        <div class="card-stat">
            <h5>Total Peminjaman Komponen</h5>
            <h2><?= $totalPeminjaman ?></h2>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    <div class="col-12 mb-3 col-md-4">
        <div class="card-stat">
            <h5>Total Trainer</h5>
            <h2><?= $totalTrainer ?></h2>
        </div>
    </div>
    <?php endif; ?>
</div>


      <!-- Grafik -->
      <div class="chart-container">
        <h5 class="mb-3 text-center">Grafik Jumlah Komponen Berdasarkan Level</h5>
        <canvas id="alatChart" width="400" height="200"></canvas>
      </div>
    </div>
  </div>

  <!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
  const dataChart = <?= json_encode($dataChart) ?>;
  const labels = dataChart.map(item => item.tingkatan_alat);
  const dataValues = dataChart.map(item => parseInt(item.total));

  const ctx = document.getElementById('alatChart').getContext('2d');
  const alatChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Jumlah Alat',
        data: dataValues,
        backgroundColor: [
    '#0d6efd',  // biru
    '#198754',  // hijau
    '#dc3545',  // merah
    '#fd7e14',  // oranye
    '#6f42c1',  // ungu
    '#20c997',  // teal
    '#ffc107',  // kuning
    '#6610f2',  // indigo
  ],
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false // Sembunyikan legend "asa"
        },
        datalabels: {
          color: '#000',
          anchor: 'end',
          align: 'start',
          font: {
            weight: 'bold',
            size: 12
          },
          formatter: function(value, context) {
            const label = context.chart.data.labels[context.dataIndex];
            return `${label} (${value})`;
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          suggestedMax: Math.max(...dataValues) + 10
        }
      }
    },
    plugins: [ChartDataLabels]
  });
</script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

