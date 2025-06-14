<?php
session_start();
include 'koneksi.php';
$nama_barang = "";
$harga = "";
$stok_barang = "";
$pesan = "";

if (isset($_POST['simpan'])) {
    $nama_barang = $_POST['nama_barang'];
    $harga = $_POST['harga'];
    $stok_barang = $_POST['stok_barang'];

    // Validasi sederhana
    if ($nama_barang && $harga && $stok_barang) {
        $sql = "INSERT INTO barang (nama_barang, harga, stok_barang) 
                VALUES ('$nama_barang', '$harga', '$stok_barang')";
        $query = mysqli_query($koneksi, $sql);

        if ($query) {
            $id_barang = mysqli_insert_id($koneksi); // Ambil ID barang baru

            // Simpan ke kategori_barang (jika ada kategori dipilih)
            if (isset($_POST['kategori']) && is_array($_POST['kategori'])) {
                foreach ($_POST['kategori'] as $id_kategori) {
                    $id_kategori = intval($id_kategori); // untuk keamanan
                    $koneksi->query("INSERT INTO kategori_barang (id_barang, id_kategori) 
                                     VALUES ($id_barang, $id_kategori)");
                }
            }
            $_SESSION['pesan'] = "✅ Data berhasil disimpan!";
            header("Location: data-barang.php");
            exit;
        } else {
            $_SESSION['pesan'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: data-barang.php");
            exit;
        }
    } else {
        $pesan = "⚠️ Semua field wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Admin | Data Barang</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time(); ?>">
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <ul>
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="tambah-transaksi.php" class="nav-link">Tambah Transaksi</a></li>
                <li><a href="riwayat.php" class="nav-link">Riwayat Transaksi</a></li>
                <li><a href="data-barang.php" class="nav-link active">Data Barang</a></li>
                <li><a href="data-member.php" class="nav-link">Data Member</a></li>
                <li><a href="data-kasir.php" class="nav-link">Data Kasir</a></li>
                <li><a href="data-kategori.php" class="nav-link">Data Kategori</a></li>
            </ul>
        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Data Barang</h1>
            </div>
            <!-- Tampilkan pesan -->
            <?php
            if (isset($_SESSION['pesan'])) {
                echo "<div style='padding:10px; background:#f0f0f0; border:1px solid #ccc; margin:10px 0;'>"
                    . $_SESSION['pesan'] . "</div>";
                unset($_SESSION['pesan']); // Hapus setelah ditampilkan
            }
            ?>
            <div class="content">
                <section class="card">
                    <div class="card-header">
                        <h2>Daftar Barang</h2>
                        <button class="btn-tambah" onclick="tambahBarang()">
                        + Tambah Barang
                        </button>
                        <!-- POPUP TAMBAH BARANG -->
                        <div class="popup-overlay" id="popupForm">
                        <div class="popup-box">
                            <h2>Tambah Barang</h2>
                            <form action="" method="POST" enctype="multipart/form-data">
                            <label>Nama Barang</label>
                            <input type="text" name="nama_barang" id="nama_barang" required />

                            <label>Harga</label>
                            <input type="number" name="harga" id="harga" required />

                            <label>Stok</label>
                            <input type="number" name="stok_barang" id="stok_barang" required />

                            <label>Kategori</label>
                            <select name="kategori[]" id="kategori" multiple required>
                                <?php
                                $kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori");
                                while ($kat = mysqli_fetch_assoc($kategori_query)) {
                                echo "<option value='{$kat['id_kategori']}'>{$kat['nama_kategori']}</option>";
                                }
                                ?>
                            </select>

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
                            <th scope="col">Nama Barang</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Stok Barang</th>
                            <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                                    $sqlbrg1 = "select * from barang order by id_barang";
                                                    $qbrg1   = mysqli_query($koneksi,$sqlbrg1);
                                                    $urut = 1;
                                                    while ($rbrg1 = mysqli_fetch_array($qbrg1)) {
                                                        $id_barang = $rbrg1['id_barang'];
                                                        $nama_barang = $rbrg1['nama_barang'];
                                                        $harga = $rbrg1['harga'];
                                                        $stok_barang = $rbrg1['stok_barang'];
                                                    ?>
                        <tr>
                            <td><?php echo $urut++ ?></td>
                            <td><?php echo $nama_barang ?></td>
                            <td><?php echo $harga ?></td>
                            <td><?php echo $stok_barang ?></td>
                            <td>
                                <button
                                type="button"
                                class="btn btn-warning btn-sm editBtn"
                                data-id_buku="<?php echo $rbrg1['id_barang']; ?>"
                                data-toggle="modal"
                                data-target="#modalEditBuku"
                                >
                                <i class="bi bi-pencil"></i>
                                </button>
                                <button
                                type="button"
                                class="btn btn-primary btn-sm showBtn"
                                data-id_buku="<?php echo $rbrg1['id_barang']; ?>"
                                data-toggle="modal"
                                data-target="#modalShowBuku"
                                >
                                <i class="bi bi-eye"></i>
                                </button>
                                <a
                                href="KPoint.php?op=delete&id_barang=<?php echo $id_barang ?>"
                                onclick="return confirm('Yakin mau delete data?')"
                                ><button type="button" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash3"></i></button
                                ></a>
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
        function tambahBarang() {
        document.getElementById("popupForm").style.display = "flex";
        }
        function tutupPopup() {
        document.getElementById("popupForm").style.display = "none";
        }
    </script>
</body>
</html>