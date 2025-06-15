<?php
session_start();
include 'koneksi.php';
$username = "";
$password = "";
$nama = "";
$pesan = "";

// Proses simpan kasir
if (isset($_POST['simpan'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $_POST['nama'];

    if ($username != "" && $password != "" && $nama != "") {
        $sql = "INSERT INTO kasir (username, password, nama) VALUES ('$username', '$password', '$nama')";
        $query = mysqli_query($koneksi, $sql);
        if ($query) {
            $_SESSION['pesan'] = "✅ Kasir berhasil ditambahkan!";
            header("Location: data-kasir.php");
            exit;
        } else {
            $_SESSION['pesan'] = "❌ Gagal menambah kasir: " . mysqli_error($koneksi);
        }
    } else {
        $pesan = "⚠️ Semua field wajib diisi!";
    }
}

// Proses hapus kasir
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM kasir WHERE id_kasir = '$id'");
    $_SESSION['pesan'] = "🗑️ Kasir berhasil dihapus!";
    header("Location: data-kasir.php");
    exit;
}

// Ambil data kasir untuk diedit
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = mysqli_query($koneksi, "SELECT * FROM kasir WHERE id_kasir = '$id'");
    $kasir_edit = mysqli_fetch_assoc($result);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Admin | Data Kasir</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time(); ?>">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="tambah-transaksi.php">Tambah Transaksi</a></li>
            <li><a href="riwayat.php">Riwayat Transaksi</a></li>
            <li><a href="data-barang.php">Data Barang</a></li>
            <li><a href="data-member.php">Data Member</a></li>
            <li><a href="data-kasir.php" class="nav-link active">Data Kasir</a></li>
            <li><a href="data-kategori.php">Data Kategori</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="judul">
            <h1>Data Kasir</h1>
        </div>

        <?php if (isset($_SESSION['pesan'])) {
            echo "<div style='padding:10px; background:#f0f0f0; border:1px solid #ccc; margin:10px 0;'>" . $_SESSION['pesan'] . "</div>";
            unset($_SESSION['pesan']);
        } ?>

        <div class="content">
            <section class="card">
                <div class="card-header">
                    <h2>Daftar Kasir</h2>
                    <button class="btn-tambah" onclick="tambahKasir()">+ Tambah Kasir</button>

                    <!-- POPUP TAMBAH -->
                    <div class="popup-overlay" id="popupFormKasir">
                        <div class="popup-box">
                            <h2>Tambah Kasir</h2>
                            <form method="POST">
                                <label>Username</label>
                                <input type="text" name="username" required />
                                <label>Password</label>
                                <input type="password" name="password" required />
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" required />

                                <div class="popup-btns">
                                    <button type="submit" name="simpan" class="btn-simpan">Simpan</button>
                                    <button type="button" class="btn-batal" onclick="tutupPopupKasir()">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- END POPUP -->
                </div>

                <?php if ($kasir_edit) { ?>
                    <!-- POPUP EDIT -->
                    <div class="popup-overlay" id="popupFormEditKasir" style="display:flex;">
                        <div class="popup-box">
                            <h2>Edit Kasir</h2>
                            <form method="POST" action="proses-edit-kasir.php">
                                <input type="hidden" name="id_user" value="<?= $kasir_edit['id_user'] ?>" />
                                <label>Username</label>
                                <input type="text" name="username" value="<?= htmlspecialchars($kasir_edit['username']) ?>" required />
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" value="<?= htmlspecialchars($kasir_edit['nama']) ?>" required />
                                <label>Password Baru (Opsional)</label>
                                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin diubah" />

                                <div class="popup-btns">
                                    <button type="submit" name="update" class="btn-simpan">Update</button>
                                    <a href="data-kasir.php" class="btn-batal">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $kasir = mysqli_query($koneksi, "SELECT * FROM kasir ORDER BY id_kasir ASC");
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($kasir)) {
                            echo "<tr>
                                <td>{$no}</td>
                                <td>" . htmlspecialchars($row['username']) . "</td>
                                <td>" . htmlspecialchars($row['nama']) . "</td>
                                <td>
                                    <a href='?edit={$row['id_user']}' class='btn-edit'>Edit</a>
                                    <a href='?hapus={$row['id_user']}' onclick='return confirm(\"Yakin ingin menghapus kasir?\")' class='btn-hapus'>Hapus</a>
                                </td>
                            </tr>";
                            $no++;
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
function tambahKasir() {
    document.getElementById("popupFormKasir").style.display = "flex";
}
function tutupPopupKasir() {
    document.getElementById("popupFormKasir").style.display = "none";
}
</script>
</body>
</html>
