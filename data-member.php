<?php
include 'auth.php';
include 'koneksi.php';
$nama_pelanggan = "";
$no_telp = "";
$poin = "";
$pesan = "";

if (isset($_POST['simpan'])) {
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $no_telp = $_POST['no_telp'];
    $poin = $_POST['poin'];

    // Validasi sederhana
    if ($nama_pelanggan && $no_telp) {
        $sql = "INSERT INTO pelanggan (nama_pelanggan, no_telp, poin) 
                VALUES ('$nama_pelanggan', '$no_telp', '$poin')";
        $query = mysqli_query($koneksi, $sql);

        if ($query) {
            $_SESSION['pesanSukses'] = "✅ Data berhasil disimpan!";
            header("Location: data-member.php");
            exit;
        } else {
            $_SESSION['pesanGagal'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: data-member.php");
            exit;
        }
    } else {
        $_SESSION['pesanGagal'] = "⚠️ Semua field wajib diisi!";
    }
}

// edit pelanggan
if (isset($_POST['simpanEdit'])) {
    $id_pelanggan = intval($_POST['edit_id_pelanggan']);
    $edit_nama_pelanggan = $_POST['edit_nama_pelanggan'];
    $edit_no_telp = $_POST['edit_no_telp'];
    $edit_poin = $_POST['edit_poin'];

    // Validasi sederhana
    if ($edit_nama_pelanggan && $edit_no_telp) {
        $sqlEdit = "UPDATE pelanggan SET nama_pelanggan = '$edit_nama_pelanggan', no_telp = '$edit_no_telp', poin = '$edit_poin' WHERE id_pelanggan = '$id_pelanggan'";
        $queryEdit = mysqli_query($koneksi, $sqlEdit);

        if ($queryEdit) {
            $_SESSION['pesanSukses'] = "✅ Data berhasil diubah!";
            header("Location: data-member.php");
            exit;
        } else {
            $_SESSION['pesanGagal'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: data-member.php");
            exit;
        }
    } else {
        $_SESSION['pesanGagal'] = "⚠️ Semua field wajib diisi!";
    }
}

// tampilkan data pelanggan di edit pelanggan
if (isset($_GET['id']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    header('Content-Type: application/json');

    $id_pelanggan = intval($_GET['id']);

    // Ambil data pelanggan
    $pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan = $id_pelanggan"));

    echo json_encode([
        'id_pelanggan' => $pelanggan['id_pelanggan'],
        'nama_pelanggan' => $pelanggan['nama_pelanggan'],
        'no_telp' => $pelanggan['no_telp'],
        'poin' => $pelanggan['poin']
    ]);

    exit;
}

// Hapus pelanggan
if (isset($_GET['hapus_id'])) {
    $id_pelanggan = intval($_GET['hapus_id']);

    // Hapus dari tabel pelanggan
    $hapus = mysqli_query($koneksi, "DELETE FROM pelanggan WHERE id_pelanggan = $id_pelanggan");

    if ($hapus) {
        $_SESSION['pesanSukses'] = "🗑️ pelanggan berhasil dihapus!";
    } else {
        $_SESSION['pesanGagal'] = "❌ Gagal menghapus pelanggan: " . mysqli_error($koneksi);
    }

    header("Location: data-member.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Admin | Data pelanggan</title>
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
                <li><a href="data-member.php" class="nav-link active">Data Member</a></li>
                <li><a href="data-kasir.php" class="nav-link">Data Kasir</a></li>
                <li><a href="data-kategori.php" class="nav-link">Data Kategori</a></li>
            </ul>
        </div>
        
        <!-- POPUP Edit pelanggan -->
                        <div class="popup-overlay" id="popupFormEdit">
                        <div class="popup-box">
                            <h2>Ubah Data pelanggan</h2>
                            <form action="" method="POST">
                                <input type="hidden" name="edit_id_pelanggan" id="edit_id_pelanggan">
                            <label>Nama pelanggan</label>
                            <input type="text" name="edit_nama_pelanggan" id="edit_nama_pelanggan" value="<?= htmlspecialchars($nama_pelanggan) ?>" required />

                            <label>No Telp</label>
                            <input type="number" name="edit_no_telp" id="edit_no_telp" value="<?= htmlspecialchars($no_telp) ?>" required />

                            <label>Poin</label>
                            <input type="number" name="edit_poin" id="edit_poin" value="<?= htmlspecialchars($poin) ?>" readonly required />

                            <div class="popup-btns">
                                <button type="submit" name="simpanEdit" class="btn-simpan">Simpan</button>
                                <button type="button" class="btn-batal" onclick="tutupPopupEdit()">Batal</button>
                            </div>
                            </form>
                        </div>
                        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Data pelanggan</h1>
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
                        <h2>Daftar pelanggan</h2>
                        <button class="btn-tambah" onclick="tambahpelanggan()">
                        + Tambah pelanggan
                        </button>
                        <!-- POPUP TAMBAH pelanggan -->
                        <div class="popup-overlay" id="popupForm">
                        <div class="popup-box">
                            <h2>Tambah pelanggan</h2>
                            <form action="" method="POST">
                            <label>Nama pelanggan</label>
                            <input type="text" name="nama_pelanggan" id="nama_pelanggan" required />

                            <label>No Telp</label>
                            <input type="number" name="no_telp" id="no_telp" required />

                            <label>Poin</label>
                            <input type="number" name="poin" id="poin" value="0" readonly required />

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
                            <th scope="col">Nama pelanggan</th>
                            <th scope="col">No Telp</th>
                            <th scope="col">Poin</th>
                            <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                                    $sqlbrg1 = "select * from pelanggan order by id_pelanggan";
                                                    $qbrg1   = mysqli_query($koneksi,$sqlbrg1);
                                                    $urut = 1;
                                                    while ($rbrg1 = mysqli_fetch_array($qbrg1)) {
                                                        $id_pelanggan = $rbrg1['id_pelanggan'];
                                                        $nama_pelanggan = $rbrg1['nama_pelanggan'];
                                                        $no_telp = $rbrg1['no_telp'];
                                                        $poin = $rbrg1['poin'];
                                                    ?>
                        <tr>
                            <td><?php echo $urut++ ?></td>
                            <td><?php echo $nama_pelanggan ?></td>
                            <td><?php echo $no_telp ?></td>
                            <td><?php echo $poin ?></td>
                            <td>
                                <button
                                type="button"
                                class="editBtn"
                                data-id-pelanggan="<?php echo $rbrg1['id_pelanggan']; ?>" onclick="editpelanggan(event)"><img src="assets/icons/edit/Edit_fill.svg" alt="">
                                </button>
                                <button type="button" class="deleteBtn" 
                                data-id-pelanggan="<?php echo $rbrg1['id_pelanggan']; ?>"><img src="assets/icons/trash/Trash.svg" alt=""></button
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
        // popup tambah pelanggan
        function tambahpelanggan() {
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
        // edit pelanggan
        function editpelanggan(event) {
            const button = event.currentTarget; // tombol yang diklik
            const id = button.getAttribute('data-id-pelanggan');

            fetch(`data-member.php?id=${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // penting agar dikenali sebagai AJAX di PHP
                }
            })
                .then(res => res.json())
                .then(data => {
                    // Isi form edit
                    document.getElementById('edit_id_pelanggan').value = data.id_pelanggan;
                    document.getElementById('edit_nama_pelanggan').value = data.nama_pelanggan;
                    document.getElementById('edit_no_telp').value = data.no_telp;
                    document.getElementById('edit_poin').value = data.poin;
                    // Tampilkan popup edit
                    document.getElementById('popupFormEdit').style.display = 'flex';
                });
        }

        // hapus pelanggan
        document.addEventListener("DOMContentLoaded", function () {
            const deleteButtons = document.querySelectorAll(".deleteBtn");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const idpelanggan = this.getAttribute("data-id-pelanggan");

                    if (confirm("Apakah kamu yakin ingin menghapus pelanggan ini?")) {
                        // Redirect ke URL PHP dengan parameter id
                        window.location.href = `data-member.php?hapus_id=${idpelanggan}`;
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