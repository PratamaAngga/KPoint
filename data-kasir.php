<?php
include 'auth.php';
include 'koneksi.php';
$nama = "";
$username = "";
$password = "";
$pesan = "";

if (isset($_POST['simpan'])) {
    $username   = trim($_POST['username']);
    $password = $_POST['password'];
    $nama       = trim($_POST['nama']);

    // Validasi sederhana
    if ($username && $password && $nama) {
        // Cek username
        $cek = mysqli_query($koneksi, "SELECT * FROM kasir WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['pesanGagal'] = "⚠️ Username sudah digunakan!";
            header("Location: data-kasir.php");
            exit;
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO kasir (username, password, nama) 
                VALUES ('$username', '$password_hash', '$nama')";
        $query = mysqli_query($koneksi, $sql);

        if ($query) {
            $_SESSION['pesanSukses'] = "✅ Data berhasil disimpan!";
            header("Location: data-kasir.php");
            exit;
        } else {
            $_SESSION['pesanGagal'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: data-kasir.php");
            exit;
        }
    } else {
        $_SESSION['pesanGagal'] = "⚠️ Semua field wajib diisi!";
    }
}

// tampilkan data barang di edit barang
if (isset($_GET['id']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    header('Content-Type: application/json');

    $id_user = intval($_GET['id']);

    // Ambil data barang
    $kasir = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM kasir WHERE id_user = $id_user"));

    echo json_encode([
        'id_user' => $kasir['id_user'],
        'nama' => $kasir['nama'],
        'username' => $kasir['username']
    ]);

    exit;
}

// edit kasir
if (isset($_POST['simpanEdit'])) {
    $id_user = intval($_POST['edit_id_user']);
    $edit_nama = trim($_POST['edit_nama']);
    $edit_username = trim($_POST['edit_username']);

    // Validasi sederhana
    if ($edit_nama && $edit_username) {
        // Cek username
        $cek = mysqli_query($koneksi, "SELECT * FROM kasir WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            $_SESSION['pesanGagal'] = "⚠️ Username sudah digunakan!";
            header("Location: data-kasir.php");
            exit;
        }

        $sqlEdit = "UPDATE kasir SET nama = '$edit_nama', username = '$edit_username' WHERE id_user = '$id_user'";
        $queryEdit = mysqli_query($koneksi, $sqlEdit);

        if ($queryEdit) {
            $_SESSION['pesanSukses'] = "✅ Data berhasil diubah!";
            header("Location: data-kasir.php");
            exit;
        } else {
            $_SESSION['pesanGagal'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: data-kasir.php");
            exit;
        }
    } else {
        $_SESSION['pesanGagal'] = "⚠️ Semua field wajib diisi!";
    }
}

// Hapus barang
if (isset($_GET['hapus_id'])) {
    $id_user = intval($_GET['hapus_id']);

    // Hapus dari tabel barang
    $hapus = mysqli_query($koneksi, "DELETE FROM kasir WHERE id_user = $id_user");

    if ($hapus) {
        $_SESSION['pesanSukses'] = "🗑️ Kasir berhasil dihapus!";
    } else {
        $_SESSION['pesanGagal'] = "❌ Gagal menghapus Kasir: " . mysqli_error($koneksi);
    }

    header("Location: data-kasir.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Admin | Data Kasir</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time(); ?>">
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <ul>
                <li><a href="index.php" class="logo">K<span>.</span>Point</a></li>
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="tambah-transaksi.php" class="nav-link">Tambah Transaksi</a></li>
                <li><a href="riwayat.php" class="nav-link">Riwayat Transaksi</a></li>
                <li><a href="data-barang.php" class="nav-link">Data Barang</a></li>
                <li><a href="data-member.php" class="nav-link">Data Member</a></li>
                <li><a href="data-kasir.php" class="nav-link active">Data Kasir</a></li>
                <li><a href="data-kategori.php" class="nav-link">Data Kategori</a></li>
            </ul>
        </div>
        
        <!-- POPUP Edit Kasir -->
        <div class="popup-overlay" id="popupFormEdit">
            <div class="popup-box">
                <h2>Ubah Data Kasir</h2>
                <form action="" method="POST">
                    <input type="hidden" name="edit_id_user" id="edit_id_user">
                    <label>Nama Kasir</label>
                    <input type="text" name="edit_nama" id="edit_nama" value="<?= htmlspecialchars($nama) ?>" required />

                    <label>Nama Pengguna</label>
                    <input type="text" name="edit_username" id="edit_username" value="<?= htmlspecialchars($username) ?>" required />

                    <div class="popup-btns">
                        <button type="submit" name="simpanEdit" class="btn-simpan">Simpan</button>
                        <button type="button" class="btn-batal" onclick="tutupPopupEdit()">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Data Kasir</h1>
                <div class="profil">
                    <h4>Hai, <?= $_SESSION['nama']; ?> </h4>
                    <img src="assets/icons/logout/logout.svg" alt="Logout" onclick="konfirmasiLogout()" style="cursor:pointer;">
                </div>
            </div>
            <!-- Tampilkan pesan -->
            <?php
            if (isset($_SESSION['pesanSukses'])) {
                echo "<div class='pesanSukses'>"
                    . $_SESSION['pesanSukses'] . "</div>";
                unset($_SESSION['pesanSukses']); // Hapus setelah ditampilkan
            }
            if (isset($_SESSION['pesanGagal'])) {
                echo "<div class='pesanGagal'>"
                    . $_SESSION['pesanGagal'] . "</div>";
                unset($_SESSION['pesanGagal']); // Hapus setelah ditampilkan
            }
            ?>
            <div class="content">
                <section class="card">
                    <div class="card-header">
                        <h2>Daftar Kasir</h2>
                        <button class="btn-tambah" onclick="tambahKasir()">
                        + Tambah Kasir
                        </button>
                        <!-- POPUP TAMBAH KASIR -->
                        <div class="popup-overlay" id="popupForm">
                        <div class="popup-box">
                            <h2>Tambah Kasir</h2>
                            <form action="" method="POST">
                            <label>Nama Kasir</label>
                            <input type="text" name="nama" id="nama" required />

                            <label>Nama Pengguna</label>
                            <input type="text" name="username" id="username" required />

                            <label>Password</label>
                            <input type="password" name="password" id="password" required />

                            <div class="popup-btns">
                                <button type="submit" name="simpan" class="btn-simpan">Simpan</button>
                                <button type="button" class="btn-batal" onclick="tutupPopup()">Batal</button>
                            </div>
                            </form>
                        </div>
                        </div>
                        <!-- INI MODIFIKASI BARU -->
                    </div>
                    <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nama Kasir</th>
                            <th scope="col">Nama Pengguna</th>
                            <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                                    $sqlbrg1 = "select * from kasir order by id_user";
                                                    $qbrg1   = mysqli_query($koneksi,$sqlbrg1);
                                                    $urut = 1;
                                                    while ($rbrg1 = mysqli_fetch_array($qbrg1)) {
                                                        $id_user = $rbrg1['id_user'];
                                                        $nama= $rbrg1['nama'];
                                                        $username = $rbrg1['username'];
                                                    ?>
                        <tr>
                            <td><?php echo $urut++ ?></td>
                            <td><?php echo $nama ?></td>
                            <td><?php echo $username ?></td>
                            <td>
                                <button
                                type="button"
                                class="editBtn"
                                data-id-user="<?php echo $rbrg1['id_user']; ?>" onclick="editKasir(event)"><img src="assets/icons/edit/Edit_fill.svg" alt="">
                                </button>
                                <button type="button" class="deleteBtn" 
                                data-id-user="<?php echo $rbrg1['id_user']; ?>"><img src="assets/icons/trash/Trash.svg" alt=""></button
                                >
                            </td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        // popup tambah barang
        function tambahKasir() {
        document.getElementById("popupForm").style.display = "flex";
        }
        function tutupPopup() {
            document.getElementById("popupForm").style.display = "none";
        }
        function tutupPopupEdit() {
            document.getElementById("popupFormEdit").style.display = "none";
        }
        // edit kasir
        function editKasir(event) {
            const button = event.currentTarget; // tombol yang diklik
            const id = button.getAttribute('data-id-user');

            fetch(`data-kasir.php?id=${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // penting agar dikenali sebagai AJAX di PHP
                }
            })
                .then(res => res.json())
                .then(data => {
                    // Isi form edit
                    document.getElementById('edit_id_user').value = data.id_user;
                    document.getElementById('edit_nama').value = data.nama;
                    document.getElementById('edit_username').value = data.username;
                    // Tampilkan popup edit
                    document.getElementById('popupFormEdit').style.display = 'flex';
                });
        }

        // hapus kasir
        document.addEventListener("DOMContentLoaded", function () {
            const deleteButtons = document.querySelectorAll(".deleteBtn");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const idUser = this.getAttribute("data-id-user");

                    if (confirm("Apakah kamu yakin ingin menghapus kasir ini?")) {
                        // Redirect ke URL PHP dengan parameter id
                        window.location.href = `data-kasir.php?hapus_id=${idUser}`;
                    }
                });
            });
        });

        function konfirmasiLogout() {
            const yakin = confirm("Apakah Anda yakin ingin logout?");
            if (yakin) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>