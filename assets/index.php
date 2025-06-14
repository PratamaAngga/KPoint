<?php
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Admin | Dashboard</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time(); ?>">
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <ul>
                <li><a href="index.php" class="nav-link active">Dashboard</a></li>
                <li><a href="tambah-transaksi.php" class="nav-link">Tambah Transaksi</a></li>
                <li><a href="riwayat.php" class="nav-link">Riwayat Transaksi</a></li>
                <li><a href="data-barang.php" class="nav-link">Data Barang</a></li>
                <li><a href="data-member.php" class="nav-link">Data Member</a></li>
                <li><a href="data-kasir.php" class="nav-link">Data Kasir</a></li>
                <li><a href="data-kategori.php" class="nav-link">Data Kategori</a></li>
            </ul>
        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Dashboard</h1>
            </div>
            <div class="info-boxes">
                <div class="box" style="background-color: #4f46e5">
                    <h3>Transaksi Hari Ini</h3>
                    <p><span>27</span> Transaksi</p>
                </div>
                <div class="box" style="background-color: #10b981">
                    <h3>Pendapatan Hari Ini</h3>
                    <p>Rp. <span>3.250.000</span></p>
                </div>
                <div class="box" style="background-color: #f59e0b">
                    <h3>Rerata Barang per Transaksi</h3>
                    <p><span>3</span> Item</p>
                </div>
                <div class="box" style="background-color: #ef4444">
                    <h3>Transaksi Terbesar Hari Ini</h3>
                    <p>Rp. <span>550.000</span></p>
                </div>

                <?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    ...
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            ...
        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Dashboard</h1>
            </div>
            <div class="info-boxes">
                ...
            </div>

            <!-- Tambahan: Menampilkan Tabel Kategori -->
            <div class="data-kategori">
                <h2>Daftar Kategori</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori ASC");
                        while ($row = mysqli_fetch_assoc($query)) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . $row['nama_kategori'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Admin | Dashboard</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time(); ?>">
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <ul>
                <li><a href="index.php" class="nav-link active">Dashboard</a></li>
                <li><a href="tambah-transaksi.php" class="nav-link">Tambah Transaksi</a></li>
                <li><a href="riwayat.php" class="nav-link">Riwayat Transaksi</a></li>
                <li><a href="data-barang.php" class="nav-link">Data Barang</a></li>
                <li><a href="data-member.php" class="nav-link">Data Member</a></li>
                <li><a href="data-kasir.php" class="nav-link">Data Kasir</a></li>
                <li><a href="data-kategori.php" class="nav-link">Data Kategori</a></li>
            </ul>
        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Dashboard</h1>
            </div>
            <div class="info-boxes">
                <div class="box" style="background-color: #4f46e5">
                    <h3>Transaksi Hari Ini</h3>
                    <p><span>27</span> Transaksi</p>
                </div>
                <div class="box" style="background-color: #10b981">
                    <h3>Pendapatan Hari Ini</h3>
                    <p>Rp. <span>3.250.000</span></p>
                </div>
                <div class="box" style="background-color: #f59e0b">
                    <h3>Rerata Barang per Transaksi</h3>
                    <p><span>3</span> Item</p>
                </div>
                <div class="box" style="background-color: #ef4444">
                    <h3>Transaksi Terbesar Hari Ini</h3>
                    <p>Rp. <span>550.000</span></p>
                </div>

                <?php
            include 'koneksi.php';
            ?>
        <!DOCTYPE html>
    <html lang="en">
<head>
    ...
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            ...
        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Dashboard</h1>
            </div>
            <div class="info-boxes">
                ...
            </div>

            <!-- Tambahan: Menampilkan Tabel Kategori -->
            <div class="data-kategori">
                <h2>Daftar Kategori</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori ASC");
                        while ($row = mysqli_fetch_assoc($query)) {
                            echo "<tr>";
                            echo "<td>" . $no++ . "</td>";
                            echo "<td>" . $row['nama_kategori'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
       
<!-- Tambahan: Menampilkan Tabel Kasir -->
<div class="data-kasir">
    <h2>Daftar Kasir</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Password</th>
                <th>Nama</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $query_kasir = mysqli_query($koneksi, "SELECT * FROM user ORDER BY id_user ASC");
            while ($row = mysqli_fetch_assoc($query_kasir)) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                echo "</tr>";
            }
            ?>
                </tbody>
            </table>
        </div>
    </div> <!-- tutup main-content -->
    </div> <!-- tutup wrapper -->
<!-- <script src="assets/js/main.js"></script> -->
</body>
</html>
