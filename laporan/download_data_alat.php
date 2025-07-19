<?php
require_once '../config.php';
require_once '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$tingkatan = $_GET['tingkatan'] ?? 'all';

$query = "SELECT * FROM alat";
$params = [];

if ($tingkatan !== 'all') {
    $query .= " WHERE tingkatan_alat = ?";
    $params[] = $tingkatan;
}

// Add ORDER BY clause to sort by nama_alat and tingkatan_alat
$query .= " ORDER BY nama_alat ASC, tingkatan_alat ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$alatList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Array for Indonesian month names
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

// Set the title based on the selected tingkatan
$title = ($tingkatan === 'all') ? 'Data Komponen' : 'Data Komponen - ' . htmlspecialchars($tingkatan);

$html = '<h2>' . $title . '</h2><table border="1" cellpadding="5" cellspacing="0" width="100%">
<tr>
    <th>Nama Komponen</th>
    <th>Kit Komponen</th>
    <th>Jumlah Komponen</th>
    <th>Tanggal Input</th>
</tr>';

foreach ($alatList as $alat) {
    // Format the date to "25 Juni 2025"
    $tanggalInput = new DateTime($alat['tanggal_input']);
    $day = $tanggalInput->format('d'); // Get day
    $month = (int)$tanggalInput->format('m'); // Get month as integer
    $year = $tanggalInput->format('Y'); // Get year

    // Get the Indonesian month name
    $formattedDate = $day . ' ' . $bulanIndo[$month] . ' ' . $year; // Format: 25 Juni 2025

    $html .= '<tr>
        <td>' . htmlspecialchars($alat['nama_alat']) . '</td>
        <td>' . htmlspecialchars($alat['tingkatan_alat']) . '</td>
        <td>' . htmlspecialchars($alat['jumlah_alat']) . '</td>
        <td>' . htmlspecialchars($formattedDate) . '</td>
    </tr>';
}
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Use the tingkatan in the filename
$filename = ($tingkatan === 'all') ? "data_alat.pdf" : "data_alat_" . htmlspecialchars($tingkatan) . ".pdf";
$dompdf->stream($filename, ["Attachment" => false]);
exit;
