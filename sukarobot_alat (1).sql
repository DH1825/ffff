-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Jul 2025 pada 11.50
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sukarobot alat`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `alat`
--

CREATE TABLE `alat` (
  `id` int(11) NOT NULL,
  `nama_alat` text NOT NULL,
  `tingkatan_alat` text NOT NULL,
  `jumlah_alat` int(11) NOT NULL,
  `tanggal_input` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alat`
--

INSERT INTO `alat` (`id`, `nama_alat`, `tingkatan_alat`, `jumlah_alat`, `tanggal_input`) VALUES
(3, 'Breadboard', 'Kit Arduino', 10, '2025-05-25'),
(4, 'ESP 32', 'Kit IoT', 2, '2025-05-30'),
(5, 'ESP 8266', 'Kit IoT', 2, '2025-05-30'),
(6, 'Block 523', 'Huna', 5, '2025-05-30'),
(7, 'Sensor DHT11', 'Kit IoT', 9, '2025-05-30'),
(8, 'Kabel Jumper M-F', 'Kit Arduino', 47, '2025-05-31'),
(9, 'Keypad', 'Kit Arduino', 20, '2025-05-31'),
(10, 'Karet Wedohh', 'Wedo', 9, '2025-06-13'),
(13, 'Arduino Uno', 'Kit Arduino', 14, '2025-06-30'),
(14, 'Block 511', 'Huna', 6, '2025-06-30'),
(15, 'LED', 'Kit Arduino', 90, '2025-06-30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `alat1`
--
-- Kesalahan membaca struktur untuk tabel sukarobot alat.alat1: #1932 - Table &#039;sukarobot alat.alat1&#039; doesn&#039;t exist in engine
-- Kesalahan membaca data untuk tabel sukarobot alat.alat1: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `sukarobot alat`.`alat1`&#039; at line 1

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_kehilangan`
--

CREATE TABLE `data_kehilangan` (
  `id` int(11) NOT NULL,
  `kode_peminjaman` text NOT NULL,
  `nama_alat` text NOT NULL,
  `jumlah_hilang` int(11) NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_peminjaman_alat`
--

CREATE TABLE `data_peminjaman_alat` (
  `id` int(11) NOT NULL,
  `kode_peminjaman` varchar(255) NOT NULL,
  `tingkatan_alat` text NOT NULL,
  `nama_project` text NOT NULL,
  `nama_trainer` text NOT NULL,
  `tempat_mengajar` text NOT NULL,
  `tanggal_ngajar` date NOT NULL DEFAULT current_timestamp(),
  `alat_yang_dipinjam` text NOT NULL,
  `jumlah_alat_yang_dipinjam` text NOT NULL,
  `Status_alat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_peminjaman_alat`
--

INSERT INTO `data_peminjaman_alat` (`id`, `kode_peminjaman`, `tingkatan_alat`, `nama_project`, `nama_trainer`, `tempat_mengajar`, `tanggal_ngajar`, `alat_yang_dipinjam`, `jumlah_alat_yang_dipinjam`, `Status_alat`) VALUES
(167, 'TX250719744', 'Intermediate', 'Huna', 'Aji Ramdani', 'SDIT Fathiya', '2025-07-19', 'Sensor DHT11', '3', 'Dikembalikan'),
(168, 'TX250719744', 'Intermediate', 'Huna', 'Aji Ramdani', 'SDIT Fathiya', '2025-07-19', 'ESP 8266', '1', 'Dikembalikan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_sekolah`
--

CREATE TABLE `data_sekolah` (
  `id` varchar(255) NOT NULL,
  `nama_sekolah` text NOT NULL,
  `lokasi_sekolah` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_sekolah`
--

INSERT INTO `data_sekolah` (`id`, `nama_sekolah`, `lokasi_sekolah`) VALUES
('1', 'SDIT Fathiya', 'Ciaul Sukabumi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stok_opname`
--

CREATE TABLE `stok_opname` (
  `id` int(11) NOT NULL,
  `nama_alato` text NOT NULL,
  `tingkatan_alat` text NOT NULL,
  `jumlah_alat` int(11) NOT NULL,
  `jumlah_alat_sebelumnya` text NOT NULL,
  `jumlah_alat_opname` text NOT NULL,
  `tanggal_pengecekan` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stok_opname`
--

INSERT INTO `stok_opname` (`id`, `nama_alato`, `tingkatan_alat`, `jumlah_alat`, `jumlah_alat_sebelumnya`, `jumlah_alat_opname`, `tanggal_pengecekan`) VALUES
(95, '14', '', 0, '10', '15', '2025-07-01'),
(96, '14', '', 0, '15', '20', '2025-07-01'),
(97, '14', '', 0, '20', '5', '2025-07-01'),
(98, '6', '', 0, '4', '5', '2025-07-01'),
(99, '13', '', 0, '18', '17', '2025-07-01'),
(100, '13', '', 0, '17', '15', '2025-07-01'),
(101, '3', '', 0, '15', '8', '2025-07-01'),
(102, '15', '', 0, '170', '90', '2025-07-01'),
(103, '14', '', 0, '5', '6', '2025-07-02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `trainer`
--
-- Kesalahan membaca struktur untuk tabel sukarobot alat.trainer: #1932 - Table &#039;sukarobot alat.trainer&#039; doesn&#039;t exist in engine
-- Kesalahan membaca data untuk tabel sukarobot alat.trainer: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `sukarobot alat`.`trainer`&#039; at line 1

-- --------------------------------------------------------

--
-- Struktur dari tabel `trainer1`
--

CREATE TABLE `trainer1` (
  `id` int(11) NOT NULL,
  `NIK` text NOT NULL,
  `nama_lengkap` text NOT NULL,
  `no_HP` text NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `trainer1`
--

INSERT INTO `trainer1` (`id`, `NIK`, `nama_lengkap`, `no_HP`, `created_at`) VALUES
(1, '', 'Dzikri Hibatullah M', '089622029800999', '2025-06-03'),
(8, '', 'Aji Ramdani', '089737158357', '2025-06-30'),
(9, '', 'Reihan Z', '085765123456', '2025-07-01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `username` text NOT NULL,
  `password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`username`, `password`) VALUES
('admin', 'admin123456'),
('admin', 'admin123456'),
('admin', 'admin123456'),
('trainer', 'trainer');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alat`
--
ALTER TABLE `alat`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `data_kehilangan`
--
ALTER TABLE `data_kehilangan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `data_peminjaman_alat`
--
ALTER TABLE `data_peminjaman_alat`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `data_sekolah`
--
ALTER TABLE `data_sekolah`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `stok_opname`
--
ALTER TABLE `stok_opname`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `trainer1`
--
ALTER TABLE `trainer1`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alat`
--
ALTER TABLE `alat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `data_kehilangan`
--
ALTER TABLE `data_kehilangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `data_peminjaman_alat`
--
ALTER TABLE `data_peminjaman_alat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT untuk tabel `stok_opname`
--
ALTER TABLE `stok_opname`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT untuk tabel `trainer1`
--
ALTER TABLE `trainer1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
