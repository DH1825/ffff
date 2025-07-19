<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validasi awal
if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$action = $data['action'];
$daftar = $data['daftar'] ?? [];
$trainer = $data['trainer'] ?? null;
$project = $data['project'] ?? '';
$location = $data['location'] ?? '';
$currentTingkatan = $data['currentTingkatan'] ?? '';
$kode_peminjaman = $data['kode_peminjaman'] ?? '';

try {
    $pdo->beginTransaction();

    // === Ambil / kembalikan 1 alat ===
    if ($action === 'ambil' || $action === 'kembalikan') {
        if (!isset($data['alat'], $data['jumlah'])) {
            throw new Exception('Data alat atau jumlah tidak lengkap.');
        }

        $alatName = $data['alat'];
        $jumlah = intval($data['jumlah']);

        $stmt = $pdo->prepare('SELECT id, jumlah_alat FROM alat WHERE nama_alat = ?');
        $stmt->execute([$alatName]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new Exception('Alat tidak ditemukan.');
        }

        $alatId = $row['id'];
        $stokSekarang = (int)$row['jumlah_alat'];

        if ($action === 'ambil') {
            if ($stokSekarang < $jumlah) {
                throw new Exception("Stok alat '$alatName' tidak cukup.");
            }
            $stmt = $pdo->prepare('UPDATE alat SET jumlah_alat = jumlah_alat - ? WHERE id = ?');
            $stmt->execute([$jumlah, $alatId]);
            $message = "Berhasil mengambil $jumlah $alatName.";
        } else {
            $stmt = $pdo->prepare('UPDATE alat SET jumlah_alat = jumlah_alat + ? WHERE id = ?');
            $stmt->execute([$jumlah, $alatId]);
            $message = "Berhasil mengembalikan $jumlah $alatName.";
        }
    }

    // === Ambil banyak alat ===
    elseif ($action === 'ambil_semua') {
        if (!is_array($daftar) || empty($trainer) || empty($currentTingkatan)) {
            throw new Exception('Data daftar alat, trainer, atau tingkatan tidak lengkap.');
        }

        // Generate kode peminjaman jika belum dikirim
        if (empty($kode_peminjaman)) {
            $kode_peminjaman = 'TX' . date('ymdHis') . rand(10, 99);
        }

        $stmtInsert = $pdo->prepare("INSERT INTO data_peminjaman_alat (
            tingkatan_alat,
            nama_project,
            nama_trainer,
            Tempat_mengajar,
            tanggal_ngajar,
            alat_yang_dipinjam,
            jumlah_alat_yang_dipinjam,
            Status_alat,
            kode_peminjaman
        ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?)");

        foreach ($daftar as $alatName => $jumlah) {
            $jumlah = intval($jumlah);

            $stmt = $pdo->prepare('SELECT id, jumlah_alat FROM alat WHERE nama_alat = ?');
            $stmt->execute([$alatName]);
            $row = $stmt->fetch();

            if (!$row) {
                throw new Exception("Alat '$alatName' tidak ditemukan.");
            }

            $alatId = $row['id'];
            $stokSekarang = (int)$row['jumlah_alat'];

            if ($stokSekarang < $jumlah) {
                throw new Exception("Stok alat '$alatName' tidak cukup.");
            }

            $stmt = $pdo->prepare('UPDATE alat SET jumlah_alat = jumlah_alat - ? WHERE id = ?');
            $stmt->execute([$jumlah, $alatId]);

            $stmtInsert->execute([
                $currentTingkatan,
                $project,
                $trainer,
                $location,
                $alatName,
                $jumlah,
                'Mengambil',
                $kode_peminjaman
            ]);
        }

        $message = 'Berhasil mengambil semua alat dan menyimpan data peminjaman.';
    }

    // === Kembalikan banyak alat ===
    elseif ($action === 'kembalikan_semua') {
        if (!is_array($daftar) || empty($trainer) || empty($currentTingkatan)) {
            throw new Exception('Data daftar alat, trainer, atau tingkatan tidak lengkap.');
        }

        $stmtInsert = $pdo->prepare("INSERT INTO data_peminjaman_alat (
            tingkatan_alat,
            nama_project,
            nama_trainer,
            Tempat_mengajar,
            tanggal_ngajar,
            alat_yang_dipinjam,
            jumlah_alat_yang_dipinjam,
            Status_alat,
            kode_peminjaman
        ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?)");

        foreach ($daftar as $alatName => $jumlah) {
            $jumlah = intval($jumlah);

            $stmt = $pdo->prepare('SELECT id, jumlah_alat FROM alat WHERE nama_alat = ?');
            $stmt->execute([$alatName]);
            $row = $stmt->fetch();

            if (!$row) {
                throw new Exception("Alat '$alatName' tidak ditemukan.");
            }

            $alatId = $row['id'];

            $stmt = $pdo->prepare('UPDATE alat SET jumlah_alat = jumlah_alat + ? WHERE id = ?');
            $stmt->execute([$jumlah, $alatId]);

            $stmtInsert->execute([
                $currentTingkatan,
                $project,
                $trainer,
                $location,
                $alatName,
                $jumlah,
                'Mengembalikan',
                $kode_peminjaman
            ]);
        }

        $message = 'Berhasil mengembalikan semua alat.';
    }

    else {
        throw new Exception('Aksi tidak dikenal.');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message, 'kode_peminjaman' => $kode_peminjaman]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}