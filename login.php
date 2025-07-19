<?php
require_once 'config.php'; // Pastikan ini mengandung koneksi ke database

$error = null;
$username = ''; // Inisialisasi variabel username

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Query untuk mengambil pengguna dari database
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verifikasi password
        if ($user['password'] === $password) {
            $_SESSION['user'] = [
                'username' => $username,
                'role' => $user['role'], // Pastikan kolom 'role' ada di tabel user
            ];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Kata Sandi salah.';
        }
    } else {
        $error = 'Nama Pengguna tidak ditemukan.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sukarobot Academy</title>
    <link rel="icon" href="aset/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background-image: url('aset/BG-LOGIN.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .card-login {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }

        .card-login h4 {
            color: #fff;
        }

        .logo {
            width: 60px;
            margin-bottom: 10px;
        }

        .form-label, .form-control {
            color: #fff;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: black;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
          
        }

        .form-control::placeholder {
            color: #ddd;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.2);
           
        }

        .focus-white:focus {
             color: white;
        }
        .focus-white {
             color: white;
        }

        .error-message {
            color: #ffc9c9;
            background-color: #ff4d4d;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.95rem;
        }

        .footer-text {
            font-size: 0.8rem;
            color: #ccc;
            margin-top: 20px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
        }

        .position-relative {
            position: relative;
        }

        .modal {
            display: none; /* Sembunyikan popup secara default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-dialog {
            margin: 15% auto;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="card-login text-center">
        <img src="aset/logo.png" class="logo" alt="Logo">
        <h4 class="mb-3">Login Sukarobot Tools App</h4>

        <?php if ($error): ?>
            <div class="error-message" id="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="text-start mb-3">
                <label for="username" class="form-label">Nama Pengguna</label>
                <input type="text" class="form-control focus-white" id="username" name="username" required autofocus placeholder="Masukkan Nama Pengguna" value="<?= htmlspecialchars($username) ?>">
            </div>
            <div class="text-start mb-4 position-relative">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control focus-white" id="password" name="password" required placeholder="Masukkan Kata Sandi">
                <span class="password-toggle" onclick="togglePassword()">👁️</span>
            </div>
            <button type="submit" class="btn btn-warning w-100 fw-bold">Masuk</button>
        </form>

        <button type="button" class="btn btn-link" onclick="showForgotPasswordPopup()">Lupa Kata Sandi?</button>

        <div class="footer-text">© 2025 Sukarobot Academy. Created by Dzikri.</div>
    </div>



    <!-- Popup untuk Kode Verifikasi -->
    <div id="forgotPasswordPopup" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lupa Kata Sandi</h5>
                    <button type="button" class="close" onclick="closePopup()">&times;</button>
                </div>
                <div class="modal-body">
                    <label for="resetUsername">Nama Pengguna:</label>
                    <input type="text" id="resetUsername" class="form-control" placeholder="Masukkan Nama Pengguna" required>
                    <label for="verificationCode">Masukkan Kode Verifikasi:</label>
                    <input type="text" id="verificationCode" class="form-control" placeholder="Masukkan Kode Verifikasi" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="verifyCode()">Verifikasi</button>
                    <button type="button" class="btn btn-secondary" onclick="closePopup()">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup untuk Kata Sandi Baru -->
    <div id="newPasswordPopup" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atur Kata Sandi Baru</h5>
                    <button type="button" class="close" onclick="closeNewPasswordPopup()">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Pastikan kata sandi baru Anda memenuhi syarat berikut:</p>
                    <ul>
                        <li>Minimal 8 karakter</li>
                        <li>Harus mengandung setidaknya satu huruf dan satu angka</li>
                    </ul>
                    <div class="text-start mb-3 position-relative">
                        <label for="newPassword">Kata Sandi Baru:</label>
                        <input type="password" id="newPassword" class="form-control" placeholder="Masukkan Kata Sandi Baru" required>
                        <span class="password-toggle" onclick="toggleNewPassword()">👁️</span>
                    </div>
                    <div class="text-start mb-3 position-relative">
                        <label for="confirmPassword">Konfirmasi Kata Sandi:</label>
                        <input type="password" id="confirmPassword" class="form-control" placeholder="Konfirmasi Kata Sandi Baru" required>
                        <span class="password-toggle" onclick="toggleConfirmPassword()">👁️</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="resetPassword()">Atur Kata Sandi</button>
                    <button type="button" class="btn btn-secondary" onclick="closeNewPasswordPopup()">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hilangkan pesan error setelah 3 detik
        setTimeout(() => {
            const msg = document.getElementById("error-msg");
            if (msg) msg.style.display = "none";
        }, 3000);

        // Toggle lihat/sembunyikan password
        function togglePassword() {
            const pw = document.getElementById("password");
            pw.type = pw.type === "password" ? "text" : "password";
        }

        // Popup untuk Lupa Kata Sandi
        function showForgotPasswordPopup() {
            document.getElementById("forgotPasswordPopup").style.display = "block";
        }

        function closePopup() {
            document.getElementById("forgotPasswordPopup").style.display = "none";
        }

        function verifyCode() {
            const username = document.getElementById("resetUsername").value;
            const code = document.getElementById("verificationCode").value;

            // Ganti dengan logika verifikasi yang sesuai
            if (code === "089622029800") {
                closePopup();
                document.getElementById("newPasswordPopup").style.display = "block";
            } else {
                alert("Kode verifikasi salah.");
            }
        }

        function closeNewPasswordPopup() {
            document.getElementById("newPasswordPopup").style.display = "none";
        }

        function resetPassword() {
            const username = document.getElementById("resetUsername").value;
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;

            // Validasi kata sandi
            const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/; // Minimal 8 karakter, setidaknya 1 huruf dan 1 angka
            if (!passwordRegex.test(newPassword)) {
                alert("Kata sandi harus minimal 8 karakter dan mengandung setidaknya satu huruf dan satu angka.");
                return;
            }

            if (newPassword === confirmPassword) {
                // Proses reset kata sandi di server
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "reset_password.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert("Kata sandi berhasil diatur.");
                        closeNewPasswordPopup();
                    }
                };
                xhr.send("username=" + encodeURIComponent(username) + "&newPassword=" + encodeURIComponent(newPassword));
            } else {
                alert("Kata sandi tidak cocok.");
            }
        }

        // Toggle lihat/sembunyikan kata sandi baru
        function toggleNewPassword() {
            const newPw = document.getElementById("newPassword");
            newPw.type = newPw.type === "password" ? "text" : "password";
        }

        // Toggle lihat/sembunyikan konfirmasi kata sandi
        function toggleConfirmPassword() {
            const confirmPw = document.getElementById("confirmPassword");
            confirmPw.type = confirmPw.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
