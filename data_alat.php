<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

// Tentukan jumlah baris per halaman
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil tingkatan alat untuk dropdown
$stmt = $pdo->query("SELECT DISTINCT tingkatan_alat FROM alat ORDER BY tingkatan_alat ASC");
$tingkatanList = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Ambil semua alat dengan paginasi
$stmt = $pdo->prepare("SELECT id, nama_alat, tingkatan_alat, jumlah_alat, tanggal_input FROM alat ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$alatList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total alat untuk paginasi
$totalStmt = $pdo->query("SELECT COUNT(*) FROM alat");
$totalAlat = $totalStmt->fetchColumn();
$totalPages = ceil($totalAlat / $limit);

// Array untuk nama bulan dalam Bahasa Indonesia
$bulanIndo = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

// Cek jika ada pesan sukses
$successMessage = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Data Alat - Sukarobot Academy</title>
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

        .table-responsive {
            overflow-x: auto; /* Menambahkan scroll horizontal */
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
                    <li><?php if ($isAdmin): ?><a class="dropdown-item" href="data_trainer.php"><i class="bi bi-person-lines-fill me-2"></i> Data Trainer</a><?php endif; ?></li>
                    <li><?php if ($isAdmin): ?><a class="dropdown-item" href="data_sekolah.php"><i class="bi bi-building me-2"></i> Data Sekolah</a><?php endif; ?></li>
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

        <div class="container main-content">
            <h2 class="mb-4">Data Komponen</h2>

            <?php if ($successMessage): ?>
                <div class="alert alert-success" id="successMessage">
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label for="tingkatanFilter" class="form-label"><strong>Pilih Kit Komponen:</strong></label>
                <select id="tingkatanFilter" class="form-select" onchange="filterAlat()">
                    <option value="all">Semua</option>
                    <?php foreach ($tingkatanList as $tingkatan): ?>
                        <option value="<?= htmlspecialchars($tingkatan) ?>"><?= htmlspecialchars(ucfirst($tingkatan)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <?php if ($isAdmin): ?>
                    <a href="create.php" class="btn btn-primary">Tambah Data</a>
                    <button onclick="downloadFilteredPDF()" class="btn btn-success">Download PDF</button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle" id="alatTable">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Nama Komponen</th>
                            <th>Kit Komponen</th>
                            <th>Jumlah Komponen</th>
                            <th>Tanggal Input</th>
                            <?php if ($isAdmin): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($alatList) > 0): ?>
                            <?php foreach ($alatList as $index => $alat): ?>
                                <tr data-tingkatan="<?= htmlspecialchars($alat['tingkatan_alat']) ?>">
                                    <td><?= $index + 1 + $offset ?></td> <!-- Menambahkan nomor urut -->
                                    <td><?= htmlspecialchars($alat['nama_alat']) ?></td>
                                    <td><?= htmlspecialchars($alat['tingkatan_alat']) ?></td>
                                    <td><?= htmlspecialchars($alat['jumlah_alat']) ?></td>
                                    <td><?= date('d', strtotime($alat['tanggal_input'])) . ' ' . $bulanIndo[date('n', strtotime($alat['tanggal_input']))] . ' ' . date('Y', strtotime($alat['tanggal_input'])) ?></td>
                                    <?php if ($isAdmin): ?>
                                        <td>
                                            <a href="edit.php?id=<?= $alat['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="delete.php?id=<?= $alat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data alat ini?');">Hapus</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $isAdmin ? 5 : 4 ?>" class="text-center">Tidak ada data tersedia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginasi -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <script>
        document.getElementById('tingkatanFilter').addEventListener('change', filterAlat);

        function filterAlat() {
            const selectedTingkatan = document.getElementById('tingkatanFilter').value;
            const rows = document.querySelectorAll('#alatTable tbody tr');

            rows.forEach(row => {
                if (selectedTingkatan === 'all' || row.getAttribute('data-tingkatan') === selectedTingkatan) {
                    row.style.display = ''; // Tampilkan baris
                } else {
                    row.style.display = 'none'; // Sembunyikan baris
                }
            });
        }

        function downloadFilteredPDF() {
            const selectedTingkatan = document.getElementById('tingkatanFilter').value;
            window.open('laporan/download_data_alat.php?tingkatan=' + selectedTingkatan, '_blank');
        }

        // Menghilangkan pesan sukses setelah 3 detik
        setTimeout(() => {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 3000);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
