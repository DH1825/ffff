<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = $_POST['kode_peminjaman'];
    $jumlah_kembali = $_POST['jumlah_kembali'];

    $stmt = $pdo->prepare("SELECT * FROM data_peminjaman_alat WHERE kode_peminjaman = ?");
    $stmt->execute([$kode]);
    $peminjamanList = $stmt->fetchAll();

    if ($peminjamanList) {
    $lostItems = []; // Array untuk menyimpan alat yang hilang

    foreach ($peminjamanList as $row) {
        $nama_alat = trim($row['alat_yang_dipinjam']);
        $jumlah_pinjam = intval($row['jumlah_alat_yang_dipinjam']);
        $key = htmlspecialchars($nama_alat);
        $jumlah_dikembalikan = isset($jumlah_kembali[$key]) ? intval($jumlah_kembali[$key]) : 0;

        // Update stok alat dengan jumlah yang dikembalikan
        $stmt = $pdo->prepare("UPDATE alat SET jumlah_alat = jumlah_alat + ? WHERE nama_alat = ?");
        $stmt->execute([$jumlah_dikembalikan, $nama_alat]);

        // Cek jika jumlah dikembalikan kurang dari jumlah pinjam
        if ($jumlah_dikembalikan < $jumlah_pinjam) {
            $jumlah_hilang = $jumlah_pinjam - $jumlah_dikembalikan;
            $lostItems[$nama_alat] = $jumlah_hilang; // Simpan alat yang hilang
        }
    }

    // Update status_alat menjadi "Dikembalikan"
    $stmt = $pdo->prepare("UPDATE data_peminjaman_alat SET Status_alat = 'Dikembalikan' WHERE kode_peminjaman = ?");
    $stmt->execute([$kode]);

    // Simpan data kehilangan jika ada
    if (!empty($lostItems)) {
        $keterangan = $_POST['keterangan'];
        foreach ($lostItems as $nama_alat => $jumlah_hilang) {
            $stmt = $pdo->prepare("INSERT INTO data_kehilangan (kode_peminjaman, nama_alat, jumlah_hilang, keterangan) VALUES (?, ?, ?, ?)");
            $stmt->execute([$kode, $nama_alat, $jumlah_hilang, $keterangan]);
        }
        header('Location: data_peminjaman.php?success=Sebagian alat tidak dikembalikan. Kehilangan telah dicatat.');
    } else {
        header('Location: data_peminjaman.php?success=Semua alat berhasil dikembalikan dan status diperbarui.');
    }
} else {
    header('Location: data_peminjaman.php?error=Data peminjaman tidak ditemukan.');
}


    exit;
}
