<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['username'] !== 'admin') {
    header('Location: login.php?error=Anda tidak memiliki akses.');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

// Ambil tingkatan alat untuk dropdown
$stmt = $pdo->query("SELECT DISTINCT tingkatan_alat FROM alat ORDER BY tingkatan_alat ASC");
$tingkatanList = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Ambil data rekap berdasarkan filter
$where = [];
$params = [];

if (!empty($_GET['bulan'])) {
    $where[] = "MONTH(so.tanggal_pengecekan) = ?";
    $params[] = $_GET['bulan'];
}
if (!empty($_GET['tahun'])) {
    $where[] = "YEAR(so.tanggal_pengecekan) = ?";
    $params[] = $_GET['tahun'];
}
if (!empty($_GET['tingkatan'])) {
    $where[] = "a.tingkatan_alat = ?";
    $params[] = $_GET['tingkatan'];
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Query untuk mengambil data
$stmt = $pdo->prepare("
    SELECT a.nama_alat, a.tingkatan_alat, DATE(so.tanggal_pengecekan) AS tanggal, 
           so.jumlah_alat_opname AS total_opname, jumlah_alat_sebelumnya as jumlah_alat,
           (SELECT so2.jumlah_alat_opname FROM stok_opname so2 
            WHERE so2.nama_alato = so.nama_alato 
            AND so2.tanggal_pengecekan < so.tanggal_pengecekan 
            ORDER BY so2.tanggal_pengecekan DESC 
            LIMIT 1) AS jumlah_alat_sebelumnya
    FROM stok_opname so
    JOIN alat a ON so.nama_alato = a.id
    $whereSQL
    ORDER BY a.nama_alat, so.tanggal_pengecekan
");
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatTanggalIndo($tanggal) {
    $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    return $formatter->format(new DateTime($tanggal));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Rekap Pengecekan - Sukarobot Academy</title>
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
        <h2 class="mb-4">Rekap Pengecekan Stok</h2>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="bulan">Pilih Bulan:</label>
                <select name="bulan" id="bulan" onchange="filterData()">
                    <option value="">-- Semua --</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>"><?= date('F', mktime(0, 0, 0, $i, 10)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="tahun">Pilih Tahun:</label>
                <select name="tahun" id="tahun" onchange="filterData()">
                    <option value="">-- Semua --</option>
                    <?php
                    $stmt = $pdo->query("SELECT DISTINCT YEAR(tanggal_pengecekan) as tahun FROM stok_opname ORDER BY tahun DESC");
                    while ($row = $stmt->fetch()) {
                        echo '<option value="' . $row['tahun'] . '">' . $row['tahun'] . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="tingkatan">Pilih Tingkatan:</label>
                <select name="tingkatan" id="tingkatan" onchange="filterData()">
                    <option value="">-- Semua --</option>
                    <?php
                    $stmt = $pdo->query("SELECT DISTINCT tingkatan_alat FROM alat ORDER BY tingkatan_alat ASC");
                    while ($row = $stmt->fetch()) {
                        echo '<option value="' . $row['tingkatan_alat'] . '">' . $row['tingkatan_alat'] . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <button onclick="downloadFilteredPDF()" class="btn btn-success m-2">Download PDF Klasifikasi</button>

        <div class="table-responsive mt-4">
            <table class="table table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>Nama Komponen</th>
                        <th>Level</th>
                        <th>Pengecekan</th>
                        <th>Total Alat Sebelumnya</th>
                        <th>Total Alat</th>
                    </tr>
                </thead>
                <tbody id="dataTableBody">
                    <?php if (!$data): ?>
                        <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
                    <?php else: foreach ($data as $record): ?>
                        <tr data-tingkatan="<?= htmlspecialchars($record['tingkatan_alat']) ?>" data-tanggal="<?= htmlspecialchars($record['tanggal']) ?>">
                            <td><?= htmlspecialchars($record['nama_alat']) ?></td>
                            <td><?= htmlspecialchars($record['tingkatan_alat']) ?></td>
                            <td><?= formatTanggalIndo($record['tanggal']) ?></td>
                            <td><?= htmlspecialchars($record['jumlah_alat']) ?></td> <!-- Menampilkan jumlah alat sebelumnya -->
                            <td><?= htmlspecialchars($record['total_opname']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filterData() {
        const bulan = document.getElementById('bulan').value;
        const tahun = document.getElementById('tahun').value;
        const tingkatan = document.getElementById('tingkatan').value;
        const rows = document.querySelectorAll('#dataTableBody tr');

        rows.forEach(row => {
            const rowTingkatan = row.getAttribute('data-tingkatan');
            const rowTanggal = new Date(row.getAttribute('data-tanggal'));
            const rowBulan = rowTanggal.getMonth() + 1; // getMonth() returns 0-11
            const rowTahun = rowTanggal.getFullYear();

            const showRow = (tingkatan === '' || rowTingkatan === tingkatan) &&
                            (bulan === '' || rowBulan === parseInt(bulan)) &&
                            (tahun === '' || rowTahun === parseInt(tahun));
            row.style.display = showRow ? '' : 'none';
        });
    }

    function downloadFilteredPDF() {
        const bulan = document.getElementById('bulan').value;
        const tahun = document.getElementById('tahun').value;
        const tingkatan = document.getElementById('tingkatan').value;
        const url = 'laporan/download_pdf_rekap.php?bulan=' + bulan + '&tahun=' + tahun + '&tingkatan=' + tingkatan;
        window.open(url, '_blank');
    }
</script>
</body>
</html>
