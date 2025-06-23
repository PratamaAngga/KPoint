<?php
include 'auth.php';
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
            $_SESSION['pesanSukses'] = "✅ Data berhasil disimpan!";
            header("Location: data-barang.php");
            exit;
        } else {
            $_SESSION['pesanGagal'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: data-barang.php");
            exit;
        }
    } else {
        $_SESSION['pesanGagal'] = "⚠️ Semua field wajib diisi!";
    }
}

// edit barang
if (isset($_POST['simpanEdit'])) {
    $id_barang = intval($_POST['edit_id_barang']);
    $edit_nama_barang = $_POST['edit_nama_barang'];
    $edit_harga = $_POST['edit_harga'];
    $edit_stok_barang = $_POST['edit_stok_barang'];

    // Validasi sederhana
    if ($edit_nama_barang && $edit_harga && $edit_stok_barang) {
        $sqlEdit = "UPDATE barang SET nama_barang = '$edit_nama_barang', harga = '$edit_harga', stok_barang = '$edit_stok_barang' WHERE id_barang = '$id_barang'";
        $queryEdit = mysqli_query($koneksi, $sqlEdit);

        if ($queryEdit) {
            // Jika user memilih kategori baru di form edit
            if (isset($_POST['edit_kategori']) && is_array($_POST['edit_kategori'])) {
                // Hapus kategori lama
                mysqli_query($koneksi, "DELETE FROM kategori_barang WHERE id_barang = $id_barang");

                // Masukkan kategori baru
                foreach ($_POST['edit_kategori'] as $edit_id_kategori) {
                    $edit_id_kategori = intval($edit_id_kategori);
                    $koneksi->query("INSERT INTO kategori_barang (id_barang, id_kategori) 
                                     VALUES ($id_barang, $edit_id_kategori)");
                }
            }
            // Kalau tidak ada input kategori, kategori lama tidak diubah

            $_SESSION['pesanSukses'] = "✅ Data berhasil diubah!";
            header("Location: data-barang.php");
            exit;
        } else {
            $_SESSION['pesanGagal'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: data-barang.php");
            exit;
        }
    } else {
        $_SESSION['pesanGagal'] = "⚠️ Semua field wajib diisi!";
    }
}

// tampilkan data barang di edit barang
if (isset($_GET['id']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    header('Content-Type: application/json');

    $id_barang = intval($_GET['id']);

    // Ambil data barang
    $barang = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang = $id_barang"));

    // Ambil kategori terkait
    $kategori_terkait = [];
    $kategori_nama = [];

    $result = mysqli_query($koneksi, "
        SELECT k.id_kategori, k.nama_kategori 
        FROM kategori_barang kb
        INNER JOIN kategori k ON k.id_kategori = kb.id_kategori
        WHERE kb.id_barang = $id_barang
    ");

    while ($row = mysqli_fetch_assoc($result)) {
        $kategori_terkait[] = $row['id_kategori'];
        $kategori_nama[] = $row['nama_kategori'];
    }

    echo json_encode([
        'id_barang' => $barang['id_barang'],
        'nama_barang' => $barang['nama_barang'],
        'harga' => $barang['harga'],
        'stok_barang' => $barang['stok_barang'],
        'kategori_id' => $kategori_terkait,
        'kategori_terkait' => implode(', ', $kategori_nama)
    ]);

    exit;
}

// Hapus barang
if (isset($_GET['hapus_id'])) {
    $id_barang = intval($_GET['hapus_id']);

    // Hapus dulu dari relasi kategori_barang
    mysqli_query($koneksi, "DELETE FROM kategori_barang WHERE id_barang = $id_barang");

    // Hapus dari tabel barang
    $hapus = mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang = $id_barang");

    if ($hapus) {
        $_SESSION['pesanSukses'] = "🗑️ Barang berhasil dihapus!";
    } else {
        $_SESSION['pesanGagal'] = "❌ Gagal menghapus barang: " . mysqli_error($koneksi);
    }

    header("Location: data-barang.php");
    exit;
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
                <li><a href="index.php" class="logo">K<span>.</span>Point</a></li>
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="tambah-transaksi.php" class="nav-link">Tambah Transaksi</a></li>
                <li><a href="riwayat.php" class="nav-link">Riwayat Transaksi</a></li>
                <li><a href="data-barang.php" class="nav-link active">Data Barang</a></li>
                <li><a href="data-member.php" class="nav-link">Data Member</a></li>
                <li><a href="data-kasir.php" class="nav-link">Data Kasir</a></li>
                <li><a href="data-kategori.php" class="nav-link">Data Kategori</a></li>
            </ul>
        </div>
        
        <!-- POPUP Edit BARANG -->
                        <div class="popup-overlay" id="popupFormEdit">
                        <div class="popup-box">
                            <h2>Ubah Data Barang</h2>
                            <form action="" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="edit_id_barang" id="edit_id_barang">
                            <label>Nama Barang</label>
                            <input type="text" name="edit_nama_barang" id="edit_nama_barang" value="<?= htmlspecialchars($nama_barang) ?>" required />

                            <label>Harga</label>
                            <input type="number" name="edit_harga" id="edit_harga" value="<?= htmlspecialchars($harga) ?>" required />

                            <label>Stok</label>
                            <input type="number" name="edit_stok_barang" id="edit_stok_barang" value="<?= htmlspecialchars($stok_barang) ?>" required />

                            <label>Kategori Saat Ini</label>
                            <div id="kategori_terkait"></div>

                            <label>Kategori</label>
                            <select name="edit_kategori[]" id="edit_kategori" multiple><?php
                                $kategori_query_edit = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori");
                                while ($kat_edit = mysqli_fetch_assoc($kategori_query_edit)) {
                                echo "<option value='{$kat_edit['id_kategori']}'>{$kat_edit['nama_kategori']}</option>";
                                }
                                ?>
                            </select>

                            <div class="popup-btns">
                                <button type="submit" name="simpanEdit" class="btn-simpan">Simpan</button>
                                <button type="button" class="btn-batal" onclick="tutupPopupEdit()">Batal</button>
                            </div>
                            </form>
                        </div>
                        </div>

        <!-- POPUP DETAIL -->
         <div class="popup-overlay" id="popupFormShow">
            <div class="popup-box">
                <h2>Detail Barang</h2>
                <form action="">
                    <input type="hidden" name="show_id_barang" id="show_id_barang">
                    <label>Nama Barang</label>
                    <input type="text" name="show_nama_barang" id="show_nama_barang" value="<?= htmlspecialchars($nama_barang) ?>" readonly />

                    <label>Harga</label>
                    <input type="number" name="show_harga" id="show_harga" value="<?= htmlspecialchars($harga) ?>" readonly />

                    <label>Stok</label>
                    <input type="number" name="show_stok_barang" id="show_stok_barang" value="<?= htmlspecialchars($stok_barang) ?>" readonly />

                    <label>Kategori Saat Ini</label>
                    <div id="kategori_sekarang"></div>

                    <div class="popup-btns">
                        <button type="button" class="btn-batal" onclick="tutupPopupShow()">Tutup</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Data Barang</h1>
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
                                class="editBtn"
                                data-id-barang="<?php echo $rbrg1['id_barang']; ?>" onclick="editBarang(event)"><img src="assets/icons/edit/Edit_fill.svg" alt="">
                                </button>
                                <button
                                type="button"
                                class="showBtn"
                                data-id-barang="<?php echo $rbrg1['id_barang']; ?>" onclick="showBarang(event)"><img src="assets/icons/view/View.svg" alt="">
                                </button>
                                <button type="button" class="deleteBtn" 
                                data-id-barang="<?php echo $rbrg1['id_barang']; ?>"><img src="assets/icons/trash/Trash.svg" alt=""></button
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
        function tambahBarang() {
        document.getElementById("popupForm").style.display = "flex";
        }
        function tutupPopup() {
            document.getElementById("popupForm").style.display = "none";
        }
        function tutupPopupEdit() {
            document.getElementById("popupFormEdit").style.display = "none";
        }
        function tutupPopupShow() {
            document.getElementById("popupFormShow").style.display = "none";
        }
        // edit barang
        function editBarang(event) {
            const button = event.currentTarget; // tombol yang diklik
            const id = button.getAttribute('data-id-barang');

            fetch(`data-barang.php?id=${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // penting agar dikenali sebagai AJAX di PHP
                }
            })
                .then(res => res.json())
                .then(data => {
                    // Isi form edit
                    document.getElementById('edit_id_barang').value = data.id_barang;
                    document.getElementById('edit_nama_barang').value = data.nama_barang;
                    document.getElementById('edit_harga').value = data.harga;
                    document.getElementById('edit_stok_barang').value = data.stok_barang;

                    // Tampilkan kategori terkait (readonly)
                    document.getElementById('kategori_terkait').innerText = data.kategori_terkait;

                    // Select kategori yang sudah dipilih
                    const select = document.getElementById('edit_kategori');
                    for (const option of select.options) {
                        option.selected = data.kategori_id.includes(parseInt(option.value));
                    }

                    // Tampilkan popup edit
                    document.getElementById('popupFormEdit').style.display = 'flex';
                });
        }
        // show barang
        function showBarang(event) {
            const button = event.currentTarget; // tombol yang diklik
            const id = button.getAttribute('data-id-barang');

            fetch(`data-barang.php?id=${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // penting agar dikenali sebagai AJAX di PHP
                }
            })
                .then(res => res.json())
                .then(data => {
                    // Isi form edit
                    document.getElementById('show_id_barang').value = data.id_barang;
                    document.getElementById('show_nama_barang').value = data.nama_barang;
                    document.getElementById('show_harga').value = data.harga;
                    document.getElementById('show_stok_barang').value = data.stok_barang;

                    // Tampilkan kategori terkait (readonly)
                    document.getElementById('kategori_sekarang').innerText = data.kategori_terkait;

                    // Tampilkan popup edit
                    document.getElementById('popupFormShow').style.display = 'flex';
                });
        }

        // hapus barang
        document.addEventListener("DOMContentLoaded", function () {
            const deleteButtons = document.querySelectorAll(".deleteBtn");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const idBarang = this.getAttribute("data-id-barang");

                    if (confirm("Apakah kamu yakin ingin menghapus barang ini?")) {
                        // Redirect ke URL PHP dengan parameter id
                        window.location.href = `data-barang.php?hapus_id=${idBarang}`;
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