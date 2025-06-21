<?php
include 'auth.php';
include 'koneksi.php';
$nama_kategori = "";
$pesan = "";

if (isset($_POST['simpan'])) {
    $nama_kategori = $_POST['nama_kategori'];

    // Validasi sederhana
    if ($nama_kategori) {
        $sql = "INSERT INTO kategori (nama_kategori) 
                VALUES ('$nama_kategori')";
        $query = mysqli_query($koneksi, $sql);

        if ($query) {
            $_SESSION['pesanSukses'] = "✅ Data berhasil disimpan!";
            header("Location: data-kategori.php");
            exit;
        } else {
            $_SESSION['pesanGagal'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: data-kategori.php");
            exit;
        }
    } else {
        $_SESSION['pesanGagal'] = "⚠️ Semua field wajib diisi!";
    }
}

// edit kategori
if (isset($_POST['simpanEdit'])) {
    $id_kategori = intval($_POST['edit_id_kategori']);
    $edit_nama_kategori = $_POST['edit_nama_kategori'];

    // Validasi sederhana
    if ($edit_nama_kategori) {
        $sqlEdit = "UPDATE kategori SET nama_kategori = '$edit_nama_kategori' WHERE id_kategori = '$id_kategori'";
        $queryEdit = mysqli_query($koneksi, $sqlEdit);

        if ($queryEdit) {
            $_SESSION['pesanSukses'] = "✅ Data berhasil diubah!";
            header("Location: data-kategori.php");
            exit;
        } else {
            $_SESSION['pesanGagal'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: data-kategori.php");
            exit;
        }
    } else {
        $_SESSION['pesanGagal'] = "⚠️ Semua field wajib diisi!";
    }
}

// tampilkan data kategori
if (isset($_GET['id']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    header('Content-Type: application/json');

    $id_kategori = intval($_GET['id']);

    // Ambil data kategori
    $kategori = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM kategori WHERE id_kategori = $id_kategori"));

    echo json_encode([
        'id_kategori' => $kategori['id_kategori'],
        'nama_kategori' => $kategori['nama_kategori']
    ]);

    exit;
}

// Hapus kategori
if (isset($_GET['hapus_id'])) {
    $id_kategori = intval($_GET['hapus_id']);

    // Hapus dari tabel kategori
    $hapus = mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = $id_kategori");

    if ($hapus) {
        $_SESSION['pesanSukses'] = "🗑️ kategori berhasil dihapus!";
    } else {
        $_SESSION['pesanGagal'] = "❌ Gagal menghapus kategori: " . mysqli_error($koneksi);
    }

    header("Location: data-kategori.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Admin | Data Kategori</title>
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
                <li><a href="data-barang.php" class="nav-link">Data Barang</a></li>
                <li><a href="data-member.php" class="nav-link">Data Member</a></li>
                <li><a href="data-kasir.php" class="nav-link">Data Kasir</a></li>
                <li><a href="data-kategori.php" class="nav-link active">Data Kategori</a></li>
            </ul>
        </div>
        
        <!-- POPUP Edit kategori -->
                        <div class="popup-overlay" id="popupFormEdit">
                        <div class="popup-box">
                            <h2>Ubah Data kategori</h2>
                            <form action="" method="POST">
                                <input type="hidden" name="edit_id_kategori" id="edit_id_kategori">
                            <label>Nama kategori</label>
                            <input type="text" name="edit_nama_kategori" id="edit_nama_kategori" value="<?= htmlspecialchars($nama_kategori) ?>" required />

                            <div class="popup-btns">
                                <button type="submit" name="simpanEdit" class="btn-simpan">Simpan</button>
                                <button type="button" class="btn-batal" onclick="tutupPopupEdit()">Batal</button>
                            </div>
                            </form>
                        </div>
                        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Data kategori</h1>
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
                        <h2>Daftar kategori</h2>
                        <button class="btn-tambah" onclick="tambahkategori()">
                        + Tambah kategori
                        </button>
                        <!-- POPUP TAMBAH kategori -->
                        <div class="popup-overlay" id="popupForm">
                        <div class="popup-box">
                            <h2>Tambah kategori</h2>
                            <form action="" method="POST">
                            <label>Nama kategori</label>
                            <input type="text" name="nama_kategori" id="nama_kategori" required />

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
                            <th scope="col">Nama kategori</th>
                            <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                                    $sqlktg1 = "select * from kategori order by id_kategori";
                                                    $qktg1   = mysqli_query($koneksi,$sqlktg1);
                                                    $urut = 1;
                                                    while ($rktg1 = mysqli_fetch_array($qktg1)) {
                                                        $id_kategori = $rktg1['id_kategori'];
                                                        $nama_kategori = $rktg1['nama_kategori'];
                                                    ?>
                        <tr>
                            <td><?php echo $urut++ ?></td>
                            <td><?php echo $nama_kategori ?></td>
                            <td>
                                <button
                                type="button"
                                class="editBtn"
                                data-id-kategori="<?php echo $rktg1['id_kategori']; ?>" onclick="editkategori(event)"><img src="assets/icons/edit/Edit_fill.svg" alt="">
                                </button>
                                <button type="button" class="deleteBtn" 
                                data-id-kategori="<?php echo $rktg1['id_kategori']; ?>"><img src="assets/icons/trash/Trash.svg" alt=""></button
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
        // popup tambah kategori
        function tambahkategori() {
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
        // edit kategori
        function editkategori(event) {
            const button = event.currentTarget; // tombol yang diklik
            const id = button.getAttribute('data-id-kategori');

            fetch(`data-kategori.php?id=${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // penting agar dikenali sebagai AJAX di PHP
                }
            })
                .then(res => res.json())
                .then(data => {
                    // Isi form edit
                    document.getElementById('edit_id_kategori').value = data.id_kategori;
                    document.getElementById('edit_nama_kategori').value = data.nama_kategori;

                    // Tampilkan popup edit
                    document.getElementById('popupFormEdit').style.display = 'flex';
                });
        }

        // hapus kategori
        document.addEventListener("DOMContentLoaded", function () {
            const deleteButtons = document.querySelectorAll(".deleteBtn");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const idkategori = this.getAttribute("data-id-kategori");

                    if (confirm("Apakah kamu yakin ingin menghapus kategori ini?")) {
                        // Redirect ke URL PHP dengan parameter id
                        window.location.href = `data-kategori.php?hapus_id=${idkategori}`;
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