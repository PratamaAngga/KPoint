<?php
include 'auth.php';
include 'koneksi.php';
$nama_pelanggan = "";
$no_telp = "";
$poin = "";
$pesan = "";

if (isset($_POST['simpan-pelanggan'])) {
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
            header("Location: tambah-transaksi.php");
            exit;
        } else {
            $_SESSION['pesanGagal'] = "❌ Gagal menyimpan data: " . mysqli_error($koneksi);
            header("Location: tambah-transaksi.php");
            exit;
        }
    } else {
        $_SESSION['pesanGagal'] = "⚠️ Semua field wajib diisi!";
    }
}

// Ambil data pelanggan (semua dianggap member)
$member_list = mysqli_query($koneksi, "SELECT id_pelanggan, nama_pelanggan, poin FROM pelanggan ORDER BY nama_pelanggan");

// Ambil data barang
$barang_list = mysqli_query($koneksi, "SELECT id_barang, nama_barang, harga FROM barang ORDER BY nama_barang");

// Tanggal hari ini
$tanggal_hari_ini = date('Y-m-d');

// simpan transaksi
if (isset($_POST['simpan'])) {
    $tanggal       = $_POST['tanggal'];
    $id_pelanggan  = intval($_POST['id_pelanggan']);
    $total         = floatval($_POST['grand_total']);
    $id_user       = $_SESSION['id_user']; // dari session login

    // Masukkan ke tabel transaksi
    $sql = "INSERT INTO transaksi (tanggal_transaksi, total, id_user, id_pelanggan) VALUES ('$tanggal', $total, $id_user, $id_pelanggan)";
    $query = mysqli_query($koneksi, $sql);

    if ($query) {
        $id_transaksi = mysqli_insert_id($koneksi);

        // Simpan ke detail_transaksi
        $id_barang   = $_POST['id_barang'];
        $jumlah      = $_POST['jumlah'];
        $harga_satuan = $_POST['harga_satuan'];
        $subtotal     = $_POST['subtotal'];

        for ($i = 0; $i < count($id_barang); $i++) {
            $id_brg = intval($id_barang[$i]);
            $jml    = intval($jumlah[$i]);
            $harga  = floatval($harga_satuan[$i]);
            $sub    = floatval($subtotal[$i]);

            mysqli_query($koneksi, "INSERT INTO detail_transaksi (id_transaksi, id_barang, jumlah, harga_satuan, subtotal) VALUES ($id_transaksi, $id_brg, $jml, $harga, $sub)");
        }

        // Tambah poin member
        $poin_saat_ini = intval($_POST['poin_saat_ini']);
        $poin_setelah = $poin_saat_ini + 1;

        if ($poin_setelah >= 500) {
            $poin_setelah = 0; // Reset poin
        }

        mysqli_query($koneksi, "UPDATE pelanggan SET poin = $poin_setelah WHERE id_pelanggan = $id_pelanggan");

        $_SESSION['pesanSukses'] = "✅ Transaksi berhasil disimpan!";
        header("Location: tambah-transaksi.php"); // arahkan ke halaman riwayat transaksi misalnya
        exit;
    } else {
        $_SESSION['pesanGagal'] = "❌ Gagal menyimpan transaksi: " . mysqli_error($koneksi);
        header("Location: tambah-transaksi.php");
        exit;
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
                <li><a href="tambah-transaksi.php" class="nav-link active">Tambah Transaksi</a></li>
                <li><a href="riwayat.php" class="nav-link">Riwayat Transaksi</a></li>
                <li><a href="data-barang.php" class="nav-link">Data Barang</a></li>
                <li><a href="data-member.php" class="nav-link">Data Member</a></li>
                <li><a href="data-kasir.php" class="nav-link">Data Kasir</a></li>
                <li><a href="data-kategori.php" class="nav-link">Data Kategori</a></li>
            </ul>
        </div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <h1>Buat Transaksi</h1>
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
                                <button type="submit" name="simpan-pelanggan" class="btn-simpan">Simpan</button>
                                <button type="button" class="btn-batal" onclick="tutupPopup()">Batal</button>
                            </div>
                            </form>
                        </div>
                        </div>
            <div class="content">
                <section class="card">
                    <div class="card-header">
                        <h2>Detail Transaksi</h2>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">

                            <div class="identitas">
                                <div class="kotak">
                                    <label>Tanggal</label>
                                    <input type="date" name="tanggal" value="<?= $tanggal_hari_ini ?>" readonly>
        
                                    <label>Nama Member</label>
                                    <input type="text" id="nama_member" name="nama_member" list="daftar_member" onchange="pilihMember()" placeholder="Ketik nama member..." required>
                                    <datalist id="daftar_member">
                                        <?php 
                                        mysqli_data_seek($member_list, 0); // reset pointer
                                        while ($m = mysqli_fetch_assoc($member_list)) : ?>
                                            <option value="<?= htmlspecialchars($m['nama_pelanggan']) ?>" data-id="<?= $m['id_pelanggan'] ?>" data-poin="<?= $m['poin'] ?>"></option>
                                        <?php endwhile; ?>
                                    </datalist>
                                    <input type="hidden" name="id_pelanggan" id="id_pelanggan">
                                </div>
                                <div class="kotak">
                                    <label>Poin Saat Ini</label>
                                    <input type="number" id="poin_saat_ini" readonly>
        
                                    <label>Poin Setelah Transaksi</label>
                                    <input type="number" id="poin_setelah" readonly>
                                </div>
                                <div class="kotak">
                                    <label>Nama Kasir</label>
                                    <input type="text" name="nama_kasir" value="<?= $_SESSION['nama']; ?>" readonly>
                                </div>
                                <div class="kotak">
                                    <button type="button" class="btn-tambah" onclick="tambahpelanggan()">
                                        + Daftarkan Member Baru
                                    </button>
                                </div>
                            </div>

                            <div class="garis-pemisah-transaksi"></div>

                            <h3>Detail Barang</h3>
                            <table class="detail-table" id="detailTable">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Harga Satuan</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" name="nama_barang[]" list="daftar_barang" oninput="pilihBarang(this)" placeholder="Ketik nama barang...">
                                            <datalist id="daftar_barang">
                                                <?php 
                                                mysqli_data_seek($barang_list, 0);
                                                while ($b = mysqli_fetch_assoc($barang_list)) : ?>
                                                    <option value="<?= htmlspecialchars($b['nama_barang']) ?>" data-id="<?= $b['id_barang'] ?>" data-harga="<?= $b['harga'] ?>"></option>
                                                <?php endwhile; ?>
                                            </datalist>
                                            <input type="hidden" name="id_barang[]">
                                        </td>
                                        <td><input type="number" name="harga_satuan[]" readonly></td>
                                        <td><input type="number" name="jumlah[]" oninput="updateSubtotal(this)"></td>
                                        <td><input type="number" name="subtotal[]" readonly></td>
                                        <td><button type="button" class="deleteBtn" onclick="hapusBaris(this)"><img src="assets/icons/trash/Trash.svg" alt=""></button></td>
                                    </tr>
                                </tbody>
                            </table>

                            <button type="button" class="btn-tambah" onclick="tambahBaris()">+ Tambah Barang</button>

                            <h3 style="margin-top: 20px">Total</h3>
                            <label style="margin-right: 10px;">Grand Total</label>
                            <input type="number" class="grand_total" id="grand_total" name="grand_total" readonly>

                            <div class="popup-btns">
                                <button type="submit" name="simpan" class="btn-simpan">Simpan Transaksi</button>
                                <button type="button" class="btn-batal" onclick="resetForm()">Batal</button>
                            </div>
                        </form>
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
        function pilihMember() {
            const nama = document.getElementById("nama_member").value;
            const options = document.querySelectorAll("#daftar_member option");
            let found = false;

            options.forEach(opt => {
                if (opt.value === nama) {
                    document.getElementById("id_pelanggan").value = opt.getAttribute("data-id");
                    const poin = parseInt(opt.getAttribute("data-poin")) || 0;
                    document.getElementById("poin_saat_ini").value = poin;

                    let poinSetelah = poin + 1;

                    // Jika poin setelah transaksi mencapai atau lebih dari 500, reset ke 0
                    if (poinSetelah >= 500) {
                        alert("🎉 Member ini mencapai 500 poin dan berhak mendapat hadiah! Poin akan direset.");
                        poinSetelah = 0;
                    }

                    document.getElementById("poin_setelah").value = poinSetelah;

                    if (poin + 1 >= 500) {
                        alert("🎉 Member ini mencapai 500 poin dan berhak mendapat hadiah!");
                    }
                    found = true;
                }
            });

            if (!found) {
                // Jika tidak ada yang cocok, reset field terkait
                document.getElementById("id_pelanggan").value = '';
                document.getElementById("poin_saat_ini").value = '';
                document.getElementById("poin_setelah").value = '';
            }
        }

        function pilihBarang(input) {
            const nama = input.value;
            const row = input.closest('tr');
            const options = document.querySelectorAll("#daftar_barang option");
            let found = false;

            options.forEach(opt => {
                if (opt.value === nama) {
                    row.querySelector("[name='id_barang[]']").value = opt.getAttribute("data-id");
                    row.querySelector("[name='harga_satuan[]']").value = opt.getAttribute("data-harga");
                    updateSubtotal(row.querySelector("[name='jumlah[]']"));
                    found = true;
                }
            });

            if (!found) {
                row.querySelector("[name='id_barang[]']").value = '';
                row.querySelector("[name='harga_satuan[]']").value = '';
                row.querySelector("[name='subtotal[]']").value = '';
            }
        }

        function updateSubtotal(inputJumlah) {
            const row = inputJumlah.closest('tr');
            const harga = parseFloat(row.querySelector("[name='harga_satuan[]']").value) || 0;
            const jumlah = parseInt(inputJumlah.value) || 0;
            const subtotal = harga * jumlah;
            row.querySelector("[name='subtotal[]']").value = subtotal;
            hitungGrandTotal();
        }

        function hitungGrandTotal() {
            let total = 0;
            document.querySelectorAll("[name='subtotal[]']").forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('grand_total').value = total;
        }

        function hapusBaris(button) {
            const row = button.closest('tr');
            row.remove();
            hitungGrandTotal();
        }

        function tambahBaris() {
            const tbody = document.querySelector("#detailTable tbody");
            const rowBaru = tbody.rows[0].cloneNode(true);
            rowBaru.querySelectorAll('input').forEach(input => input.value = "");
            tbody.appendChild(rowBaru);
        }

        function konfirmasiLogout() {
            const yakin = confirm("Apakah Anda yakin ingin logout?");
            if (yakin) {
                window.location.href = "logout.php";
            }
        }
        function resetForm() {
            if (confirm("Apakah kamu yakin ingin membatalkan transaksi? Semua data akan dikosongkan.")) {
                const form = document.querySelector("form");
                form.reset();

                // Kosongkan juga field yang tidak ter-reset otomatis
                document.getElementById('id_pelanggan').value = "";
                document.getElementById('poin_saat_ini').value = "";
                document.getElementById('poin_setelah').value = "";
                document.getElementById('grand_total').value = "";

                // Hapus semua baris barang kecuali baris pertama
                const tbody = document.querySelector("#detailTable tbody");
                while (tbody.rows.length > 1) {
                    tbody.deleteRow(1);
                }

                // Kosongkan baris pertama
                tbody.rows[0].querySelectorAll("input").forEach(input => input.value = "");
            }
        }
    </script>
</body>
</html>