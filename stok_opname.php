<?php
require_once 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['username'] !== 'admin') {
    header('Location: data_alat.php?error=Anda tidak memiliki akses.');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

// Ambil tingkatan alat untuk dropdown
$stmt = $pdo->query("SELECT DISTINCT tingkatan_alat FROM alat ORDER BY tingkatan_alat ASC");
$tingkatanList = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Tambahkan opsi "Semua"
array_unshift($tingkatanList, 'Semua');

// Tingkatan yang dipilih
$selectedTingkatan = $_GET['tingkatan_alat'] ?? $tingkatanList[0];

// Paginasi
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Ambil data alat sesuai tingkatan
if ($selectedTingkatan === 'Semua') {
    $stmt = $pdo->prepare("SELECT * FROM alat ORDER BY nama_alat ASC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM alat WHERE tingkatan_alat = ? ORDER BY nama_alat ASC LIMIT ? OFFSET ?");
    $stmt->execute([$selectedTingkatan, $limit, $offset]);
}
$alatList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total alat untuk paginasi
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM alat" . ($selectedTingkatan !== 'Semua' ? " WHERE tingkatan_alat = ?" : ""));
$totalStmt->execute($selectedTingkatan !== 'Semua' ? [$selectedTingkatan] : []);
$totalAlat = $totalStmt->fetchColumn();
$totalPages = ceil($totalAlat / $limit);

// Handle AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    foreach ($data as $item) {
        $alat_id = $item['id'];
        $jumlah_opname = $item['jumlah'];

        if ($jumlah_opname !== '' && is_numeric($jumlah_opname) && $jumlah_opname >= 0) {
            // Ambil jumlah awal sebelum update
            $stmtSelect = $pdo->prepare("SELECT jumlah_alat FROM alat WHERE id = ?");
            $stmtSelect->execute([$alat_id]);
            $jumlah_awal = $stmtSelect->fetchColumn();

            // Insert ke stok_opname
            $stmtInsert = $pdo->prepare("
                INSERT INTO stok_opname (nama_alat, jumlah_alat_opname, jumlah_alat_sebelumnya, tanggal_pengecekan)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmtInsert->execute([$alat_id, $jumlah_opname, $jumlah_awal]);

            // Update jumlah di tabel alat
            $stmtUpdate = $pdo->prepare("UPDATE alat SET jumlah_alat = ? WHERE id = ?");
            $stmtUpdate->execute([$jumlah_opname, $alat_id]);
        }
    }

    echo json_encode(['status' => 'success']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stok Opname AJAX - Sukarobot Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .sidebar {
            width: 150px; /* Lebar sidebar */
            background-color: #0d6efd; /* Warna biru */
            color: white;
            padding: 20px 0;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: fixed; /* Posisi tetap */
            top: 0; /* Atas */
            left: 0; /* Kiri */
            height: 100vh; /* Tinggi penuh */
            overflow: hidden; /* Nonaktifkan scroll */
            transition: transform 0.3s ease;
        }

        .menu-item {
            text-align: center;
            margin: 5px 0; /* Jarak antar item */
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
            margin-left: 170px; /* Margin untuk konten utama */
            padding: 30px;
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
        <div class="main-content">
            <h2 class="mb-4">Form Input Stok Opname Alat</h2>

            <!-- Pemberitahuan -->
            <div id="notification" class="alert alert-info d-none" role="alert"></div>

            <!-- Filter Tingkatan -->
            <form id="tingkatanForm" method="get" class="mb-4">
                <label for="tingkatanFilter" class="form-label"><strong>Tingkatan Alat:</strong></label>
                <select name="tingkatan_alat" id="tingkatanFilter" class="form-select w-auto d-inline" onchange="changeTingkatan(this)">
                    <?php foreach ($tingkatanList as $tingkatan): ?>
                        <option value="<?= htmlspecialchars($tingkatan) ?>" <?= $tingkatan === $selectedTingkatan ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($tingkatan)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <form id="formOpname">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Komponen</th>
                            <th>Level</th>
                            <th>Jumlah Awal</th>
                            <th>Update Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alatList as $i => $alat): ?>
                            <tr>
                                <td><?= $i + 1 + $offset ?></td>
                                <td><?= htmlspecialchars($alat['nama_alat']) ?></td>
                                <td><?= htmlspecialchars($alat['tingkatan_alat']) ?></td>
                                <td><strong><?= $alat['jumlah_alat'] ?></strong></td>
                                <td>
                                    <input type="text" class="form-control opname-input"
                                           name="jumlah_alat_opname"
                                           data-id="<?= $alat['id'] ?>"
                                           data-tingkatan="<?= $alat['tingkatan_alat'] ?>"
                                           placeholder="Input jumlah">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="rekap_pengecekan.php" class="btn btn-primary">Rekap</a>
                    
                    <!-- Paginasi -->
                    <nav aria-label="Page navigation" class="mx-3">
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === (int)$page ? 'active' : '' ?>">
                                    <a class="page-link" href="?tingkatan_alat=<?= urlencode($selectedTingkatan) ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>

                    <button type="button" onclick="submitOpname()" class="btn btn-success">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const tempData = {}; // Menyimpan inputan berdasarkan tingkatan

        // Muat ulang form berdasarkan tingkatan
        function changeTingkatan(select) {
            saveCurrentInputs();
            const tingkatan = select.value;
            const url = new URL(window.location.href);
            url.searchParams.set('tingkatan_alat', tingkatan);
            url.searchParams.set('page', 1); // Reset ke halaman 1 saat mengganti tingkatan
            window.location.href = url;
        }

        // Simpan input saat ini
        function saveCurrentInputs() {
            const inputs = document.querySelectorAll('.opname-input');
            const currentTingkatan = document.getElementById('tingkatanFilter').value;
            tempData[currentTingkatan] = [];

            inputs.forEach(input => {
                tempData[currentTingkatan].push({
                    id: input.dataset.id,
                    jumlah: input.value
                });
            });
        }

        // Restore input saat load halaman
        window.onload = function() {
            const currentTingkatan = document.getElementById('tingkatanFilter').value;
            if (tempData[currentTingkatan]) {
                const inputs = document.querySelectorAll('.opname-input');
                inputs.forEach(input => {
                    const match = tempData[currentTingkatan].find(item => item.id === input.dataset.id);
                    if (match) input.value = match.jumlah;
                });
            }
        }

        // Validasi input hanya angka
        document.querySelectorAll('.opname-input').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, ''); // Hanya izinkan angka
            });
        });

        // Submit menggunakan AJAX
        function submitOpname() {
            saveCurrentInputs();
            let allData = [];
            let hasData = false; // Flag untuk memeriksa apakah ada data yang diisi

            for (const key in tempData) {
                allData = allData.concat(tempData[key]);
            }

            // Cek apakah ada input yang diisi
            allData.forEach(item => {
                if (item.jumlah !== '') {
                    hasData = true;
                }
            });

            if (!hasData) {
                showNotification("Harap isi minimal satu data sebelum menyimpan.");
                return;
            }

            fetch('stok_opname.php?ajax=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(allData)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    showNotification("Data berhasil disimpan!");
                    location.reload();
                } else {
                    showNotification("Gagal menyimpan data.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                showNotification("Terjadi kesalahan saat menyimpan data.");
            });
        }

        // Fungsi untuk menampilkan pemberitahuan
        function showNotification(message) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.classList.remove('d-none');

            setTimeout(() => {
                notification.classList.add('d-none');
            }, 3000); // Menghilang setelah 3 detik
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
