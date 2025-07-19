<?php
require_once '../config.php';
require_once '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Ambil parameter
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';
$tingkatan = $_GET['tingkatan'] ?? '';

$where = [];
$params = [];

if (!empty($bulan)) {
    $where[] = "MONTH(so.tanggal_pengecekan) = ?";
    $params[] = $bulan;
}
if (!empty($tahun)) {
    $where[] = "YEAR(so.tanggal_pengecekan) = ?";
    $params[] = $tahun;
}
if (!empty($tingkatan)) {
    $where[] = "a.tingkatan_alat = ?";
    $params[] = $tingkatan;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Query data klasifikasi
$stmt = $pdo->prepare("
    SELECT a.nama_alat, a.tingkatan_alat, DATE(so.tanggal_pengecekan) AS tanggal, so.jumlah_alat_opname
    FROM stok_opname so
    JOIN alat a ON so.nama_alato = a.id
    $whereSQL
    ORDER BY a.nama_alat, so.tanggal_pengecekan
");
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

// Judul dinamis
$judul = 'Rekap Klasifikasi Pengecekan Stok';
if ($bulan) $judul .= ' Bulan ' . date('F', mktime(0, 0, 0, $bulan, 10));
if ($tahun) $judul .= ' Tahun ' . $tahun;
if ($tingkatan) $judul .= ' - Tingkatan ' . htmlspecialchars($tingkatan);

// HTML PDF
$html = "<h2 style='text-align:center;'>$judul</h2>
<table border='1' cellpadding='5' cellspacing='0' width='100%'>
    <thead>
        <tr>
            <th>Nama Alat</th>
            <th>Tingkatan</th>
            <th>Detail Pengecekan</th>
        </tr>
    </thead>
    <tbody>";

if (!$data) {
    $html .= "<tr><td colspan='3' style='text-align:center;'>Tidak ada data</td></tr>";
} else {
    foreach ($data as $nama => $records) {
        $html .= "<tr>
            <td>" . htmlspecialchars($nama) . "</td>
            <td>" . htmlspecialchars($records[0]['tingkatan_alat']) . "</td>
            <td><ul>";
        foreach ($records as $r) {
            $html .= "<li>" . date('d-m-Y', strtotime($r['tanggal'])) . ": " . $r['jumlah_alat_opname'] . " unit</li>";
        }
        $html .= "</ul></td></tr>";
    }
}

$html .= "</tbody></table>";

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("Rekap_Klasifikasi_Pengecekan.pdf", ["Attachment" => false]);
exit;
