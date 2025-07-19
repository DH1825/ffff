<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

// Handle delete per ID
if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    // Ambil data kehilangan untuk mengembalikan alat dan ambil kode_peminjaman
    $stmt = $pdo->prepare("SELECT nama_alat, jumlah_hilang, kode_peminjaman FROM data_kehilangan WHERE id = ?");
    $stmt->execute([$id]);
    $kehilangan = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($kehilangan) {
        $kode = $kehilangan['kode_peminjaman']; // ambil kode_peminjaman dari hasil fetch

        try {
            // Mulai transaksi
            $pdo->beginTransaction();

            // Kembalikan jumlah alat ke tabel 'alat'
            $stmt = $pdo->prepare("UPDATE alat SET jumlah_alat = jumlah_alat + :jumlah WHERE nama_alat = :nama");
            $stmt->execute([
                ':jumlah' => $kehilangan['jumlah_hilang'],
                ':nama' => $kehilangan['nama_alat']
            ]);

            // Hapus data kehilangan
            $stmt = $pdo->prepare("DELETE FROM data_kehilangan WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Hapus data peminjaman alat berdasarkan kode_peminjaman
            $stmt = $pdo->prepare("DELETE FROM data_peminjaman_alat WHERE kode_peminjaman = :kode");
            $stmt->execute([':kode' => $kode]);

            // Commit transaksi
            $pdo->commit();

            header('Location: data_kehilangan.php?success=Data berhasil dihapus dan alat dikembalikan.');
            exit;
        } catch (PDOException $e) {
            // Rollback jika ada kesalahan
            $pdo->rollBack();
            header('Location: data_kehilangan.php?error=Gagal memproses data: ' . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header('Location: data_kehilangan.php?error=Data tidak ditemukan.');
        exit;
    }
}




// Handle delete semua
if (isset($_POST['delete_all'])) {
    // Hapus semua data kehilangan
    $stmt = $pdo->prepare("DELETE FROM data_kehilangan");
    $stmt->execute();
    
    
    header('Location: data_kehilangan.php?success=Semua data kehilangan berhasil dihapus.');
    exit;
}

// Filter berdasarkan bulan
$filterMonth = $_GET['month'] ?? null;
$filterQuery = '';
$params = [];

if ($filterMonth) {
    $filterQuery = " WHERE MONTH(tanggal_ngajar) = ?";
    $params[] = $filterMonth;
}

// Ambil data kehilangan dari database dengan paginasi
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Siapkan query dasar
    $query = "
        SELECT 
            k.id,
            p.nama_trainer AS nama_trainer,
            p.tingkatan_alat AS tingkatan_alat,
            p.Tempat_mengajar AS Tempat_mengajar,
            p.tanggal_ngajar AS tanggal_ngajar,
            k.kode_peminjaman,
            k.nama_alat,
            k.jumlah_hilang,
            k.keterangan
        FROM data_kehilangan k
        LEFT JOIN data_peminjaman_alat p ON k.kode_peminjaman = p.kode_peminjaman
    ";

    // Tambahkan filter bulan jika ada
    if ($filterMonth) {
        $query .= " WHERE MONTH(tanggal_ngajar) = :month";
    }

    $query .= " GROUP BY k.id
                ORDER BY tanggal_ngajar DESC
                LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);

    // Bind parameter
    if ($filterMonth) {
        $stmt->bindValue(':month', $filterMonth, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    $kehilanganList = $stmt->fetchAll();

    // Ambil total data untuk paginasi
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM data_kehilangan k
        LEFT JOIN data_peminjaman_alat p ON k.kode_peminjaman = p.kode_peminjaman
        " . ($filterMonth ? " WHERE MONTH(tanggal_ngajar) = :month" : "")
    );

    if ($filterMonth) {
        $stmt->bindValue(':month', $filterMonth, PDO::PARAM_INT);
    }
    $stmt->execute();
    $total = $stmt->fetchColumn();
    $totalPages = ceil($total / $limit);
} catch (PDOException $e) {
    $kehilanganList = [];
    $error = "Terjadi kesalahan saat mengambil data kehilangan: " . htmlspecialchars($e->getMessage());
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
            <h1>Data Kehilangan Alat</h1>

            <?php if ($successMsg): ?>
                <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="GET" class="mb-3">
                <label for="month" class="form-label">Filter Berdasarkan Bulan:</label>
                <select name="month" id="month" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Bulan</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= ($filterMonth == $i) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $i, 1)) ?></option>
                    <?php endfor; ?>
                </select>
            </form>

            <?php if (!empty($kehilanganList)): ?>
                <div class="table-responsive">
                   <table class="table table-bordered table-hover table-striped align-middle">
                    <thead class="table-primary">
        <tr>
            <th>No</th>
            <th>Nama Trainer</th>
            <th>Level</th>
            <th>Tempat Mengajar</th>
            <th>Tanggal Mengajar</th>
            <th>Nama Alat Hilang</th>
            <th>Jumlah Alat Hilang</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
<tbody>
    <?php if (!empty($kehilanganList)) : ?>
        <?php foreach ($kehilanganList as $index => $kehilangan): ?>
            <tr>
                <td><?= $index + 1 + $offset ?></td>
                <td><?= htmlspecialchars($kehilangan['nama_trainer']) ?></td>
                <td><?= htmlspecialchars($kehilangan['tingkatan_alat']) ?></td>
                <td><?= htmlspecialchars($kehilangan['Tempat_mengajar']) ?></td>
                <td><?= date('d F Y', strtotime($kehilangan['tanggal_ngajar'])) ?></td>
                <td><?= htmlspecialchars($kehilangan['nama_alat']) ?></td>
                <td><?= htmlspecialchars($kehilangan['jumlah_hilang']) ?></td>
                <td><?= htmlspecialchars($kehilangan['keterangan']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($kehilangan['id']) ?>">
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td colspan="9" class="text-center">Tidak ada data kehilangan.</td>
        </tr>
    <?php endif; ?>
</tbody>


</table>

                </div>
                <form method="POST" class="mb-3">
                    <button type="submit" name="delete_all" class="btn btn-danger">Hapus Semua Data Kehilangan</button>
                </form>
                <nav>
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&month=<?= $filterMonth ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php else: ?>
                <p>Tidak ada data kehilangan alat.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
