<?php
session_start();
include 'koneksi.php';

// === HANDLE LOGIN ===
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM kasir WHERE username='$username'");
    $data  = mysqli_fetch_assoc($query);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['login'] = true;
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['pesanGagal'] = "❌ Username atau Password salah!";
        header("Location: signin.php");
        exit;
    }
}

// === HANDLE REGISTER ===
if (isset($_POST['signup'])) {
    $username   = trim($_POST['username']);
    $nama       = trim($_POST['nama']);
    $password   = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($password !== $konfirmasi) {
        $_SESSION['pesanGagal'] = "⚠️ Password dan konfirmasi tidak cocok!";
        header("Location: signin.php");
        exit;
    }

    // Cek username
    $cek = mysqli_query($koneksi, "SELECT * FROM kasir WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['pesanGagal'] = "⚠️ Username sudah digunakan!";
        header("Location: signin.php");
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $simpan = mysqli_query($koneksi, "INSERT INTO kasir (username, password, nama) VALUES ('$username', '$password_hash', '$nama')");

    if ($simpan) {
        $_SESSION['pesanSukses'] = "✅ Pendaftaran berhasil. Silakan login.";
        header("Location: signin.php");
        exit;
    } else {
        $_SESSION['pesanGagal'] = "❌ Gagal daftar: " . mysqli_error($koneksi);
        header("Location: signin.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MASUK - KPoint</title>
    <link rel="stylesheet" href="assets/css/signin.css?v=<?= time(); ?>">
</head>
<body>
    <div class="test"></div>
    <section class="forms-section">
        <h1 class="section-title">Selamat datang di K.Point</h1>
        <?php
        if (isset($_SESSION['pesanGagal'])) {
            echo "<div class='pesanGagal'>" . $_SESSION['pesanGagal'] . "</div>";
            unset($_SESSION['pesanGagal']);
        }
        if (isset($_SESSION['pesanSukses'])) {
            echo "<div class='pesanSukses'>" . $_SESSION['pesanSukses'] . "</div>";
            unset($_SESSION['pesanSukses']);
        }
        ?>
        <div class="forms">
            <div class="form-wrapper is-active">
            <button type="button" class="switcher switcher-login">
                Masuk
                <span class="underline"></span>
            </button>
            <form class="form form-login" method="POST">
                <fieldset>
                <legend>Mohon Masukkan Nama Pengguna dan Password Anda.</legend>
                <div class="input-block">
                    <label for="login-username">Nama Pengguna</label>
                    <input id="login-username" name="username" type="text" required>
                </div>
                <div class="input-block">
                    <label for="login-password">Password</label>
                    <input id="login-password" name="password" type="password" required>
                </div>
                </fieldset>
                <button type="submit" name="login" class="btn-login">Masuk</button>
            </form>
            </div>
            <div class="form-wrapper">
            <button type="button" class="switcher switcher-signup">
                Daftar
                <span class="underline"></span>
            </button>
            <form class="form form-signup" method="POST">
                <fieldset>
                    <legend>Daftar akun baru</legend>
                    <div class="input-block">
                        <label for="signup-username">Nama Pengguna</label>
                        <input id="signup-username" name="username" type="text" required>
                    </div>
                    <div class="input-block">
                        <label for="signup-name">Nama Asli</label>
                        <input id="signup-name" name="nama" type="text" required>
                    </div>
                    <div class="input-block">
                        <label for="signup-password">Password</label>
                        <input id="signup-password" name="password" type="password" required>
                    </div>
                    <div class="input-block">
                        <label for="signup-password-confirm">Ulangi Password</label>
                        <input id="signup-password-confirm" name="konfirmasi" type="password" required>
                    </div>
                </fieldset>
                <button type="submit" name="signup" class="btn-signup">Daftar</button>
            </form>
            </div>
        </div>
    </section>

    <script>
        const switchers = [...document.querySelectorAll('.switcher')]

        switchers.forEach(item => {
            item.addEventListener('click', function() {
                switchers.forEach(item => item.parentElement.classList.remove('is-active'))
                this.parentElement.classList.add('is-active')
            })
        })

    </script>
</body>
</html>