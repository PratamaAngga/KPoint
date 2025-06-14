<?php
session_start();
include 'koneksi.php';
$nama_kategori = "";
$pesan = "";

// Proses simpan kategori
if (isset($_POST['simpan'])) {
    $nama_kategori = $_POST['nama_kategori'];
    if ($nama_kategori != "") {
        $sql = "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')";
        $query = mysqli_query($koneksi, $sql);
        if ($query) {
            $_SESSION['pesan'] = "✅ Kategori berhasil ditambahkan!";
            header("Location: data-kategori.php");
            exit;
        } else {
            $_SESSION['pesan'] = "❌ Gagal menambah kategori: " . mysqli_error($koneksi);
        }
    } else {
        $pesan = "⚠️ Nama kategori wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Admin | Data Kategori</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time(); ?>">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <ul>
            <li><a href="index.php" class="nav-link">Dashboard</a></li>
            <li><a href="tambah-transaksi.php" class="nav-link">Tambah Transaksi</a></li>
            <li><a href="riwayat.php" class="nav-link">Riwayat Transaksi</a></li>
            <li><a href="data-barang.php" class="nav-link">Data Barang</a></li>
            <li><a href="data-member.php" class="nav-link">Data Member</a></li>
            <li><a href="data-kasir.php" class="nav-link">Data Kasir</a></li>
            <li><a href="data-kategori.php" class="nav-link active">Data Kategori</a></li>
        </ul>
    </div>

    <div class="main-content" id="main-content">
        <div class="judul">
            <h1>Data Kategori</h1>
        </div>

        <?php
        if (isset($_SESSION['pesan'])) {
            echo "<div style='padding:10px; background:#f0f0f0; border:1px solid #ccc; margin:10px 0;'>"
                . $_SESSION['pesan'] . "</div>";
            unset($_SESSION['pesan']);
        }
        ?>

        <div class="content">
            <section class="card">
                <div class="card-header">
                    <h2>Daftar Kategori</h2>
                    <button class="btn-tambah" onclick="tambahKategori()">+ Tambah Kategori</button>

                    <!-- POPUP TAMBAH KATEGORI -->
                    <div class="popup-overlay" id="popupFormKategori">
                        <div class="popup-box">
                            <h2>Tambah Kategori</h2>
                            <form action="" method="POST">
                                <label>Nama Kategori</label>
                                <input type="text" name="nama_kategori" required />

                                <div class="popup-btns">
                                    <button type="submit" name="simpan" class="btn-simpan">Simpan</button>
                                    <button type="button" class="btn-batal" onclick="tutupPopupKategori()">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- END POPUP -->
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Kategori</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori ASC");
                        $no = 1;
                        while ($row = mysqli_fetch_array($kategori)) {
                            echo "<tr>
                                <td>{$no}</td>
                                <td>" . htmlspecialchars($row['nama_kategori']) . "</td>
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
    function tambahKategori() {
        document.getElementById("popupFormKategori").style.display = "flex";
    }

    function tutupPopupKategori() {
        document.getElementById("popupFormKategori").style.display = "none";
    }
</script>
</body>
</html>
