<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

try {
    $stmt = $pdo->query("
        SELECT 
            id,
            kode_peminjaman,
            nama_trainer,
            nama_project,
            tingkatan_alat,
            Tempat_mengajar,
            tanggal_ngajar,
            Status_alat,
            alat_yang_dipinjam,
            jumlah_alat_yang_dipinjam,
            GROUP_CONCAT(CONCAT(alat_yang_dipinjam, ' (', jumlah_alat_yang_dipinjam, ')') SEPARATOR ', ') AS daftar_alat
        FROM data_peminjaman_alat
        GROUP BY 
            nama_trainer,
            nama_project,
            tingkatan_alat,
            Tempat_mengajar,
            tanggal_ngajar
        ORDER BY tanggal_ngajar DESC
    ");
    $peminjamanList = $stmt->fetchAll();
} catch (PDOException $e) {
    $peminjamanList = [];
    $error = "Terjadi kesalahan saat mengambil data: " . htmlspecialchars($e->getMessage());
}

$successMsg = $_GET['success'] ?? null;
$errorMsg = $_GET['error'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Data Peminjaman - Sukarobot Academy</title>
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
      <a href="index.php" class="menu-item">
        <div class="icon-box"><i class="bi bi-house-door"></i></div>
        <div class="menu-text">Dashboard</div>
      </a>

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

      <a href="kebutuhan_alat.php" class="menu-item">
        <div class="icon-box"><i class="bi bi-arrow-down-circle"></i></div>
        <div class="menu-text">Ambil</div>
      </a>

      <?php if ($isAdmin): ?>
      <a href="stok_opname.php" class="menu-item">
        <div class="icon-box"><i class="bi bi-bar-chart-line"></i></div>
        <div class="menu-text">Cek</div>
      </a>
      <?php endif; ?>

      <a href="logout.php" class="menu-item">
        <div class="icon-box bg-danger text-white"><i class="bi bi-box-arrow-right"></i></div>
        <div class="menu-text text-danger fw-bold">Logout</div>
      </a>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Data Peminjaman Komponen</h1>
                <?php if ($isAdmin): ?>
                    <form action="delete_all_peminjaman.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus semua data peminjaman?');">
                        <button type="submit" class="btn btn-danger">Hapus Semua Data</button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if ($successMsg): ?>
                <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if (!empty($peminjamanList)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Level</th>
                                <th>Nama Project</th>
                                <th>Nama Trainer</th>
                                <th>Tempat Mengajar</th>
                                <th>Tanggal Ngajar</th>
                                <th>Komponen yang Dipinjam</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($peminjamanList as $index => $peminjaman): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($peminjaman['tingkatan_alat']) ?></td>
                                    <td><?= htmlspecialchars($peminjaman['nama_project']) ?></td>
                                    <td><?= htmlspecialchars($peminjaman['nama_trainer']) ?></td>
                                    <td><?= htmlspecialchars($peminjaman['Tempat_mengajar']) ?></td>
                                    <td><?= htmlspecialchars($peminjaman['tanggal_ngajar']) ?></td>
                                    <td>
                                        <ul class="mb-0">
                                            <?php 
                                            $alatList = explode(', ', $peminjaman['daftar_alat']);
                                            foreach ($alatList as $alat): ?>
                                                <li><?= htmlspecialchars($alat) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td><?= htmlspecialchars($peminjaman['Status_alat']) ?></td>
                                    <td>
    <?php if ($peminjaman['Status_alat'] === 'Dikembalikan'): ?>
        <span class="badge bg-success">Sudah Dikembalikan</span>
    <?php else: ?>
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#returnModal<?= $peminjaman['id'] ?>">Kembalikan</button>
    <?php endif; ?>
</td>

                                </tr>

                                <!-- Modal for Return Quantity -->
                                <div class="modal fade" id="returnModal<?= $peminjaman['id'] ?>" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="returnModalLabel">Kembalikan Alat</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="return_item.php" method="POST">
                                                    <input type="hidden" name="kode_peminjaman" value="<?= htmlspecialchars($peminjaman['kode_peminjaman']) ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Alat yang Dipinjam</label>
                                                        <ul class="list-group mb-3">
                                                            <?php 
                                                            $alatList = explode(', ', $peminjaman['daftar_alat']); 
                                                            foreach ($alatList as $alat): 
                                                                list($nama_alat, $jumlah) = explode(' (', rtrim($alat, ')'));
                                                            ?>
                                                                <li class="list-group-item">
                                                                    <?= htmlspecialchars($nama_alat) ?> 
                                                                    <span class="badge bg-secondary"><?= intval($jumlah) ?></span>
                                                                    <input type="number"
                                                                        name="jumlah_kembali[<?= htmlspecialchars($nama_alat) ?>]"
                                                                        class="form-control mt-2"
                                                                        min="0"
                                                                        max="<?= intval($jumlah) ?>"
                                                                        placeholder="Jumlah yang dikembalikan"
                                                                        required>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                        <small class="text-danger">* Semua alat harus dikembalikan sesuai jumlah pinjam.</small>
                                                    </div>
                                                    <div class="mb-3" id="keteranganContainer" >
                                                        <label class="form-label">Keterangan Kehilangan (jika ada)</label>
                                                        <textarea name="keterangan" class="form-control" placeholder="Keterangan"></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Kembalikan</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Tidak ada data peminjaman alat.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script untuk menampilkan form keterangan jika jumlah yang dikembalikan kurang
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', function () {
            const form = modal.querySelector('form');
            const inputs = form.querySelectorAll('input[name^="jumlah_kembali"]');
            const keteranganContainer = form.querySelector('#keteranganContainer');

            inputs.forEach(input => {
                input.addEventListener('input', function () {
                    let totalKembali = 0;
                    let totalPinjam = 0;

                    inputs.forEach(input => {
                        const jumlahPinjam = parseInt(input.max);
                        const jumlahKembali = parseInt(input.value) || 0;

                        totalKembali += jumlahKembali;
                        totalPinjam += jumlahPinjam;
                    });

                    if (totalKembali < totalPinjam) {
                        keteranganContainer.style.display = 'block';
                    } else {
                        keteranganContainer.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
</body>
</html>
