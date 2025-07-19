<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$isAdmin = ($user['username'] === 'admin');

$stmt = $pdo->query("SELECT nama_sekolah FROM data_sekolah");
$lokasiMengajarList = $stmt->fetchAll(PDO::FETCH_COLUMN);

$user = $_SESSION['user'];

// Define pertemuan counts for each tingkatan
$kebutuhanPertemuan = [
    'Beginner 1' => 16,
    'Beginner 2' => 16,
    'Basic 1 Junior' => 16,
    'Basic 1 Senior' => 16,
    'Basic 2' => 24,
    'Basic 3' => 24,
    'Intermediate' => 24,
    'Advance' => 24,
];




function getAlat($pdo) {
    $sqlBeginner1 = "SELECT nama_alat FROM alat WHERE tingkatan_alat = 'Wedo'";
    $stmtBeginner1 = $pdo->query($sqlBeginner1);
    $resultsBeginner1 = $stmtBeginner1->fetchAll(PDO::FETCH_COLUMN);

    $sqlBeginner2 = "SELECT nama_alat FROM alat WHERE tingkatan_alat = 'Wedo'";
    $stmtBeginner2 = $pdo->query($sqlBeginner2);
    $resultsBeginner2 = $stmtBeginner2->fetchAll(PDO::FETCH_COLUMN);

    $sqlBasic1Junior = "SELECT nama_alat FROM alat WHERE tingkatan_alat = 'Huna'";
    $stmtBasic1Junior = $pdo->query($sqlBasic1Junior);
    $resultsBasic1Junior = $stmtBasic1Junior->fetchAll(PDO::FETCH_COLUMN);

    $sqlBasic1Senior = "SELECT nama_alat FROM alat WHERE tingkatan_alat = 'Huna'";
    $stmtBasic1Senior = $pdo->query($sqlBasic1Senior);
    $resultsBasic1Senior = $stmtBasic1Senior->fetchAll(PDO::FETCH_COLUMN);

    $sqlBasic2 = "SELECT nama_alat FROM alat WHERE tingkatan_alat = 'Huna'";
    $stmtBasic2 = $pdo->query($sqlBasic2);
    $resultsBasic2 = $stmtBasic2->fetchAll(PDO::FETCH_COLUMN);

    $sqlBasic3 = "SELECT nama_alat FROM alat WHERE tingkatan_alat = 'Kit Arduino'";
    $stmtBasic3 = $pdo->query($sqlBasic3);
    $resultsBasic3 = $stmtBasic3->fetchAll(PDO::FETCH_COLUMN);

    $sqlIntermediate = "SELECT nama_alat FROM alat WHERE tingkatan_alat IN ('Kit Arduino', 'Kit IoT')";
    $stmtIntermediate = $pdo->query($sqlIntermediate);
    $resultsIntermediate = $stmtIntermediate->fetchAll(PDO::FETCH_COLUMN);

    $sqlAdvance = "SELECT nama_alat FROM alat WHERE tingkatan_alat IN ('Kit Arduino', 'Kit IoT')";
    $stmtAdvance = $pdo->query($sqlAdvance);
    $resultsAdvance = $stmtAdvance->fetchAll(PDO::FETCH_COLUMN);

    $result = [
        'Beginner1' => [],
        'Beginner2' => [],
        'Basic1Junior' => [],
        'Basic1Senior' => [],
        'Basic2' => [],
        'Basic3' => [],
        'Intermediate' => [],
        'Advance' => []
    ];

    for ($i = 1; $i <= 24; $i++) {
      $result['Beginner2'][$i] = $resultsBeginner2;
    }

    for ($i = 1; $i <= 24; $i++) {
        $result['Beginner1'][$i] = $resultsBeginner1;
    }

    for ($i = 1; $i <= 16; $i++) {
        $result['Basic1Senior'][$i] = $resultsBasic1Junior;
    }

    for ($i = 1; $i <= 16; $i++) {
        $result['Basic1Senior'][$i] = $resultsBasic1Senior;
    }

    for ($i = 1; $i <= 24; $i++) {
        $result['Basic2'][$i] = $resultsBasic2;
    }
    for ($i = 1; $i <= 24; $i++) {
        $result['Basic3'][$i] = $resultsBasic3;
    }

    for ($i = 1; $i <= 24; $i++) {
        $result['Intermediate'][$i] = $resultsIntermediate;
    }

    for ($i = 1; $i <= 24; $i++) {
          $result['Advance'][$i] = $resultsAdvance;
    }
    return $result;
}

$alatRequirements = getAlat($pdo);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Kebutuhan Alat - Sukarobot Academy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body, html {
      height: 100%;
      margin: 0;
    }
    nav {
      margin-left: 150px; /* Geser navbar agar tidak menutupi sidebar */
      z-index: 1050; /* Pastikan navbar di atas sidebar */
    }
    .header-top-right {
      position: fixed;
      top: 10px;
      right: 20px;
      font-weight: 700;
      font-size: 1.2rem;
      color: #0d6efd;
      z-index: 1050;
    }
    .main-content {
      flex-grow: 1;
      padding: 30px;
      margin-left: 150px; /* Jarak dari sidebar */
      margin-top: 20px; /* Jarak dari navbar, dikurangi untuk mendekat */
    }
    .pertemuan-list {
      list-style: none;
      padding-left: 0;
      max-width: 600px;
    }
    .pertemuan-item {
      cursor: pointer;
      padding: 10px 15px;
      margin-bottom: 8px;
      background-color: #e9ecef;
      border-radius: 8px;
      display: flex;
      align-items: center;
      font-weight: 600;
      color: #000;
      user-select: none;
      transition: background-color 0.2s ease;
      text-decoration: none;
    }
    .pertemuan-item:hover {
      background-color: #0d6efd; /* Warna biru saat hover */
      color: white;
    }
    .dropdown-item {
      transition: background-color 0.2s ease;
      cursor: pointer; /* Mengubah kursor menjadi pointer saat hover */
    }
    .dropdown-item:hover {
      background-color: #0d6efd; /* Warna biru saat hover */
      color: white; /* Mengubah warna teks menjadi putih saat hover */
    }

    .custom-dropdown-menu {
      background-color: white;
      border: none;
      border-radius: 10px;
      padding: 8px 0;
      min-width: 200px;
      transform: translateY(5px);
    }

    .custom-dropdown-menu .dropdown-item {
      color: #0d6efd;
      font-size: 0.85rem;
      padding: 8px 15px;
      transition: 0.2s;
    }

    .custom-dropdown-menu .dropdown-item:hover {
      background-color: #e8f0fe;
      color: #0a58ca;
    }

    /* Hover show fix (for sidebar dropdown) */
    .show-on-hover {
      display: none;
      position: absolute;
      top: 65px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1050;
    }

    .dropdown:hover .show-on-hover {
      display: block;
    }

    /* Responsive adjustment */
    @media (max-width: 768px) {
      .show-on-hover {
        top: auto;
        bottom: 60px;
        left: 10%;
        right: 10%;
        transform: none;
        width: 80%;
      }
      nav {
      margin-left: 0px; /* Geser navbar agar tidak menutupi sidebar */
      z-index: 1050; /* Pastikan navbar di atas sidebar */
    }
    }
    

    html, body {
      overflow-x: hidden;
      width: 100%;
      box-sizing: border-box;
    }
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }

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
      z-index: 1000;
    }

    .menu-item {
      text-align: center;
      margin: 5px 0;
      color: white;
      text-decoration: none;
      position: relative;
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

    .welcome-text {
      font-size: 2rem;
      font-weight: bold;
      color: #0d6efd;
      text-align: center;
      margin-bottom: 30px;
      font-family: 'Poppins', sans-serif;
    }

    .card-stat {
      text-align: center;
      padding: 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .chart-container {
      margin-top: 40px;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        margin-top: 0; /* Hapus margin atas untuk tampilan mobile */
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

<nav class="navbar navbar-dark bg-primary mb-4">
  <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between w-100 py-2 px-3">
    <a class="navbar-brand d-flex align-items-center mb-2 mb-lg-0" href="index.php">
      Sukarobot Academy
    </a>

    <!-- Dropdown tingkatan untuk desktop -->
    <div class="dropdown me-2 mb-2">
      <button class="btn btn-light btn-sm dropdown-toggle" type="button" id="dropdownTingkatan" data-bs-toggle="dropdown" aria-expanded="false" onclick="toggleWelcomeText()">
        Pilih Tingkatan
      </button>
      <ul class="dropdown-menu" aria-labelledby="dropdownTingkatan">
        <?php foreach ($kebutuhanPertemuan as $tingkatan => $count): ?>
          <div class="tingkatan-link" data-tingkatan="<?= htmlspecialchars($tingkatan) ?>">
          <span><?= htmlspecialchars($tingkatan) ?></span>
          </div>
        <?php endforeach; ?>
      </ul>
    </div>

    <button class="btn btn-light btn-sm me-2 mb-2" id="btnAddTingkatan" data-bs-toggle="modal" data-bs-target="#addTingkatanModal">Tambah Tingkatan</button>
  </div>
</nav>

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
  <h3 id="selectedTingkatan"></h3>
  <ul id="pertemuanList" class="pertemuan-list"></ul>
  <div id="welcomeText" class="welcome-text">Silahkan Ambil Alat</div>
</div>

<!-- Modal for alat kebutuhan -->
<div class="modal fade" id="alatModal" tabindex="-1" aria-labelledby="alatModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="alatModalLabel">Kebutuhan Alat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body d-flex flex-column">
        <div class="input-group mb-3">
          <input type="text" id="searchAlat" class="form-control" placeholder="Cari alat..." />
          <button class="btn btn-primary" id="btnSearchAlat" type="button">Cari</button>
        </div>
        <div class="mb-3">
          <label for="trainerSelect" class="form-label">Pilih Trainer</label>
          <select class="form-select" id="trainerSelect" required>
            <option value="">Pilih Trainer</option>
            <!-- Daftar trainer akan diisi di sini -->
          </select>
        </div>
        <div class="mb-3">
          <label for="projectName" class="form-label">Nama Project</label>
          <input type="text" class="form-control" id="projectName" placeholder="Masukkan nama proyek" required>
        </div>
        <div class="mb-3">
          <label for="teachingLocation" class="form-label">Lokasi Ngajar</label>
          <select class="form-select" id="teachingLocation" required>
            <option value="">Pilih Lokasi</option>
            <?php foreach ($lokasiMengajarList as $lokasi): ?>
              <option value="<?= htmlspecialchars($lokasi) ?>"><?= htmlspecialchars($lokasi) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="modalAlatBody"></div>
      </div>
      <div class="modal-footer">
        <div class="btn-group-bottom">
          <a href="data_peminjaman.php" class="btn btn-success" id="btnAmbilSemua">Ambil</a>
        </div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const kebutuhanPertemuan = <?php echo json_encode($kebutuhanPertemuan); ?>;
const alatRequirements = <?php echo json_encode($alatRequirements); ?>;

const tingkatanLinks = document.querySelectorAll('.tingkatan-link');
const tingkatanTitle = document.getElementById('selectedTingkatan');
const pertemuanList = document.getElementById('pertemuanList');
const alatModal = new bootstrap.Modal(document.getElementById('alatModal'));
const modalAlatLabel = document.getElementById('alatModalLabel');
const modalAlatBody = document.getElementById('modalAlatBody');
const btnAmbilSemua = document.getElementById('btnAmbilSemua');
const btnKembalikanSemua = document.getElementById('btnKembalikanSemua');
const searchInput = document.getElementById('searchAlat');
const btnSearchAlat = document.getElementById('btnSearchAlat');

let currentTingkatan = null;
let currentAlatInputs = [];
let currentAlatList = [];
let originalAlatList = [];

const borrowListKey = 'borrowedAlat';

function toggleWelcomeText() {
  const welcomeText = document.getElementById('welcomeText');
  const dropdownMenu = document.getElementById('dropdownTingkatan');
      // Cek apakah dropdown terbuka
  if (dropdownMenu.getAttribute('aria-expanded') === 'true') {
    welcomeText.style.display = 'none'; // Sembunyikan teks saat dropdown terbuka
  } else {
    welcomeText.style.display = 'block'; // Tampilkan teks saat dropdown tertutup
  }
}

 function loadPertemuan(tingkatan) {
    tingkatanTitle.textContent = 'Pertemuan untuk tingkatan: ' + tingkatan;
    pertemuanList.innerHTML = '';
    const jumlah = kebutuhanPertemuan[tingkatan] || 0;
    for (let i = 1; i <= jumlah; i++) {
      const li = document.createElement('li');
      li.className = 'pertemuan-item';
      li.textContent = 'Pertemuan ' + i;
      li.dataset.pertemuan = i; // Menambahkan data pertemuan
      li.addEventListener('click', () => openAlatModal(i)); // Menambahkan event listener
      pertemuanList.appendChild(li);
    }
  }

  document.querySelectorAll('.tingkatan-link, .tingkatan-dropdown-item').forEach(el => {
    el.addEventListener('click', function(e) {
      e.preventDefault();
      const tingkatan = this.getAttribute('data-tingkatan');

      // Atur highlight aktif di sidebar
      document.querySelectorAll('.tingkatan-link').forEach(link => link.classList.remove('active'));
      const sidebarLink = document.querySelector('.tingkatan-link[data-tingkatan="' + tingkatan + '"]');
      if (sidebarLink) sidebarLink.classList.add('active');

      loadPertemuan(tingkatan);
    });
  });

function loadBorrowList() {
  let data = localStorage.getItem(borrowListKey);
  return data ? JSON.parse(data) : {};
}

function saveBorrowList(borrowList) {
  localStorage.setItem(borrowListKey, JSON.stringify(borrowList));
}

function addToBorrowList(alat, qty) {
  let borrowList = loadBorrowList();
  if (borrowList[alat]) {
    borrowList[alat] += qty;
  } else {
    borrowList[alat] = qty;
  }
  saveBorrowList(borrowList);
}

function removeFromBorrowList(alat, qty) {
  let borrowList = loadBorrowList();
  if (borrowList[alat]) {
    borrowList[alat] -= qty;
    if (borrowList[alat] <= 0) {
      delete borrowList[alat];
    }
    saveBorrowList(borrowList);
  }
}

function renderBorrowList() {
  const borrowListContainer = document.getElementById('borrowListContainer');
  if (!borrowListContainer) return;

  let borrowList = loadBorrowList();
  borrowListContainer.innerHTML = '';
  if (Object.keys(borrowList).length === 0) {
    borrowListContainer.innerHTML = '<p>Belum ada alat di daftar pinjaman.</p>';
    return;
  }
  let ul = document.createElement('ul');
  ul.classList.add('list-group');
  for (const [alat, qty] of Object.entries(borrowList)) {
    let li = document.createElement('li');
    li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
    li.textContent = alat;
    let span = document.createElement('span');
    span.classList.add('badge', 'bg-primary', 'rounded-pill');
    span.textContent = qty;
    li.appendChild(span);
    ul.appendChild(li);
  }
  borrowListContainer.appendChild(ul);
}

tingkatanLinks.forEach(link => {
  link.addEventListener('click', () => {
    tingkatanLinks.forEach(l => l.classList.remove('active'));
    link.classList.add('active');

    currentTingkatan = link.dataset.tingkatan;
    tingkatanTitle.textContent = currentTingkatan;

    const count = kebutuhanPertemuan[currentTingkatan] || 0;
    pertemuanList.innerHTML = '';

    for (let i = 1; i <= count; i++) {
      const li = document.createElement('li');
      li.classList.add('pertemuan-item');
      li.textContent = 'Pertemuan ke-' + i;
      li.dataset.pertemuan = i;
      li.style.userSelect = 'none';
      li.addEventListener('click', () => openAlatModal(i));
      pertemuanList.appendChild(li);
    }
  });
});

function renderAlatList(list) {
  modalAlatBody.innerHTML = '';

  if (list.length === 0) {
    modalAlatBody.innerHTML = "<p>Tidak ada data alat tersedia untuk pertemuan ini.</p>";
    return;
  }

  const ul = document.createElement('ul');
  ul.classList.add('list-group');

  list.forEach(alat => {
    const li = document.createElement('li');
    li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

    const spanName = document.createElement('span');
    spanName.textContent = alat;

    const inputJumlah = document.createElement('input');
    inputJumlah.type = 'number';
    inputJumlah.min = '1';
    inputJumlah.placeholder = 'Jumlah';
    inputJumlah.style.width = '70px';

    const btnAdd = document.createElement('button');
    btnAdd.type = 'button';
    btnAdd.textContent = 'Tambah ke Daftar';
    btnAdd.classList.add('btn', 'btn-success', 'btn-sm', 'ms-2');
    btnAdd.addEventListener('click', () => {
      const qty = parseInt(inputJumlah.value);
      if (!qty || qty <= 0) {
        alert('Masukkan jumlah valid!');
        return;
      }
      addToBorrowList(alat, qty);
      renderBorrowList();
    });

    const btnRemove = document.createElement('button');
    btnRemove.type = 'button';
    btnRemove.textContent = 'Kurangi';
    btnRemove.classList.add('btn', 'btn-danger', 'btn-sm', 'ms-2');
    btnRemove.addEventListener('click', () => {
      const qty = parseInt(inputJumlah.value);
      if (!qty || qty <= 0) {
        alert('Masukkan jumlah valid!');
        return;
      }
      removeFromBorrowList(alat, qty);
      renderBorrowList();
    });

    const controlsDiv = document.createElement('div');
    controlsDiv.appendChild(inputJumlah);
    controlsDiv.appendChild(btnAdd);
    controlsDiv.appendChild(btnRemove);

    li.appendChild(spanName);
    li.appendChild(controlsDiv);
    ul.appendChild(li);
  });

  const borrowListContainer = document.createElement('div');
  borrowListContainer.id = 'borrowListContainer';
  borrowListContainer.classList.add('mt-4');

  modalAlatBody.appendChild(ul);
  modalAlatBody.appendChild(borrowListContainer);

  renderBorrowList();
}

function openAlatModal(pertemuan) {
  modalAlatLabel.textContent = `Alat yang diperlukan ${currentTingkatan} pertemuan ${pertemuan} yaitu:`;
  searchInput.value = '';

  const tKey = currentTingkatan.replace(/\s+/g, '');
  currentAlatList = alatRequirements[tKey]?.[pertemuan] || [];

  // Simpan daftar alat asli supaya search selalu filter dari daftar lengkap
  originalAlatList = [...currentAlatList];

  renderAlatList(currentAlatList);

  // Ambil daftar trainer
  fetch('data_trainer.php')
    .then(response => response.text())
    .then(data => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(data, 'text/html');
      const trainerNames = Array.from(doc.querySelectorAll('tr td:nth-child(1)')).map(td => td.textContent.trim());
      window.trainerNames = trainerNames; // Simpan daftar trainer di global

      const trainerSelect = document.getElementById('trainerSelect');
      trainerSelect.innerHTML = '<option value="">Pilih Trainer</option>'; // Reset dropdown
      trainerNames.forEach(name => {
        const option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        trainerSelect.appendChild(option);
      });
    });

  alatModal.show();
}

// Fitur pencarian dengan tombol "Cari"
btnSearchAlat.addEventListener('click', () => {
  const keyword = searchInput.value.toLowerCase().trim();
  const filtered = originalAlatList.filter(alat => alat.toLowerCase().includes(keyword));
  renderAlatList(filtered);
});

function generateKodePeminjaman() {
  const prefix = 'TX';
  const now = new Date();

  const year = now.getFullYear().toString().slice(-2); // '25'
  const month = String(now.getMonth() + 1).padStart(2, '0'); // '06'
  const day = String(now.getDate()).padStart(2, '0'); // '30'
  const random = Math.floor(100 + Math.random() * 900); // 3-digit acak

  return `${prefix}${year}${month}${day}${random}`; // e.g. TX250630123
}


// Ambil semua alat
btnAmbilSemua.addEventListener('click', () => {
  const borrowList = loadBorrowList();
  const trainerName = document.getElementById('trainerSelect').value.trim();
  const projectName = document.getElementById('projectName').value.trim();
  const teachingLocation = document.getElementById('teachingLocation').value.trim();
  const kodePeminjaman = generateKodePeminjaman();

  if (!trainerName) {
    alert('Nama trainer harus diisi.');
    return;
  }

  if (!projectName) {
    alert('Nama proyek harus diisi.');
    return;
  }

  if (!teachingLocation) {
    alert('Lokasi pengajaran harus diisi.');
    return;
  }

  if (Object.keys(borrowList).length === 0) {
    alert('Daftar pinjaman masih kosong.');
    return;
  }

  // Kirim semua data borrowList ke server
  fetch('update_alat.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      action: 'ambil_semua',
      daftar: borrowList,
      trainer: trainerName,
      project: projectName,
      location: teachingLocation,
      currentTingkatan: currentTingkatan,
      kode_peminjaman: kodePeminjaman
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Berhasil meminjam semua alat.');
      localStorage.removeItem(borrowListKey);
      renderBorrowList();
      if (typeof alatModal !== 'undefined') {
        alatModal.hide();
      }
    } else {
      alert('Gagal meminjam alat: ' + (data.message || 'Terjadi kesalahan.'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan komunikasi dengan server.');
  });
});

// Kembalikan semua alat
btnKembalikanSemua.addEventListener('click', () => {
  const borrowList = loadBorrowList();
  const trainerName = document.getElementById('trainerSelect').value.trim();
  const projectName = document.getElementById('projectName')?.value.trim() || '';
  const teachingLocation = document.getElementById('teachingLocation')?.value.trim() || '';

  if (!trainerName) {
    alert('Nama trainer harus diisi.');
    return;
  }

  if (!window.trainerNames.includes(trainerName)) {
    alert('Nama trainer tidak valid. Silakan masukkan nama trainer yang terdaftar.');
    return;
  }

  if (Object.keys(borrowList).length === 0) {
    alert('Daftar pengembalian masih kosong.');
    return;
  }

  // Kirim semua data borrowList ke server
  fetch('update_alat.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: 'kembalikan_semua',
      daftar: borrowList,
      trainer: trainerName,
      project: projectName,
      location: teachingLocation,
      currentTingkatan: currentTingkatan // Pastikan ini ada
    })
  })
  .then(async res => {
    const text = await res.text();
    try {
      const data = JSON.parse(text);
      if (data.success) {
        alert('Berhasil mengembalikan semua alat.');
        localStorage.removeItem(borrowListKey);
        renderBorrowList();
        if (typeof alatModal !== 'undefined') {
          alatModal.hide();
        }
      } else {
        alert('Gagal mengembalikan alat: ' + (data.message || 'Terjadi kesalahan.'));
      }
    } catch (err) {
      console.error('Respon server bukan JSON:', text);
      alert('Terjadi kesalahan komunikasi (respon tidak valid).');
    }
  })
  .catch(error => {
    console.error('Fetch error:', error);
    alert('Terjadi kesalahan komunikasi.');
  });
});
</script>
</body>
</html>




