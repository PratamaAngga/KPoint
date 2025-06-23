<?php
include 'auth.php';
include 'koneksi.php';

// Ambil semua transaksi
$sql = "SELECT t.*, p.nama_pelanggan, u.nama AS nama_kasir
        FROM transaksi t
        LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
        LEFT JOIN kasir u ON t.id_user = u.id_user
        ORDER BY t.tanggal_transaksi DESC";
$query = mysqli_query($koneksi, $sql);

// Jika permintaan AJAX untuk detail transaksi
if (isset($_GET['id_transaksi']) && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $id_transaksi = intval($_GET['id_transaksi']);

    // Ambil detail transaksi
    $detail = [];
    $grand_total = 0;
    $result = mysqli_query($koneksi, "SELECT d.*, b.nama_barang 
        FROM detail_transaksi d
        LEFT JOIN barang b ON d.id_barang = b.id_barang
        WHERE d.id_transaksi = $id_transaksi");

    while ($row = mysqli_fetch_assoc($result)) {
        $row['subtotal'] = $row['jumlah'] * $row['harga_satuan'];
        $grand_total += $row['subtotal'];
        $detail[] = $row;
    }

    echo json_encode([
        'detail' => $detail,
        'grand_total' => $grand_total
    ]);
    exit;
}

// Ambil daftar tanggal transaksi unik untuk dropdown
$tanggal_list = mysqli_query($koneksi, "SELECT DISTINCT tanggal_transaksi FROM transaksi ORDER BY tanggal_transaksi DESC");

// Jika tombol cetak ditekan
if (isset($_POST['cetak_pdf'])) {
    $tanggal_terpilih = $_POST['tanggal_transaksi'];

    // Ambil semua transaksi di tanggal tersebut
    $transaksi_result = mysqli_query($koneksi, "
        SELECT t.*, p.nama_pelanggan, u.nama 
        FROM transaksi t 
        LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
        LEFT JOIN kasir u ON t.id_user = u.id_user 
        WHERE t.tanggal_transaksi = '$tanggal_terpilih'
        ORDER BY t.id_transaksi ASC
    ");

    // Hitung grand total
    $grand_total = 0;
    $transaksi_data = [];
    while ($row = mysqli_fetch_assoc($transaksi_result)) {
        $grand_total += $row['total'];
        $transaksi_data[] = $row;
    }

    // Buat PDF menggunakan FPDF
    require('assets/fpdf/fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(190,10,'LAPORAN TRANSAKSI',0,1,'C');

    $pdf->SetFont('Arial','',12);
    $pdf->Cell(190,10,'Tanggal: ' . date('d M Y', strtotime($tanggal_terpilih)),0,1);
    $pdf->Cell(190,10,'Grand Total: Rp ' . number_format($grand_total, 0, ',', '.'),0,1);
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(10,10,'#',1);
    $pdf->Cell(70,10,'Nama Member',1);
    $pdf->Cell(60,10,'Kasir',1);
    $pdf->Cell(50,10,'Total',1);
    $pdf->Ln();

    $no = 1;
    $pdf->SetFont('Arial','',11);
    foreach ($transaksi_data as $tr) {
        $pdf->Cell(10,10,$no++,1);
        $pdf->Cell(70,10,$tr['nama_pelanggan'],1);
        $pdf->Cell(60,10,$tr['nama'],1);
        $pdf->Cell(50,10,'Rp ' . number_format($tr['total'], 0, ',', '.'),1);
        $pdf->Ln();
    }

    $filename = "laporan_transaksi_" . date('d M Y', strtotime($tanggal_terpilih)) . ".pdf";
    $pdf->Output('D', $filename);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Transaksi</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time(); ?>">
</head>
<body>
<div class="wrapper">
  <div class="sidebar">
    <ul>
      <li><a href="index.php" class="logo">K<span>.</span>Point</a></li>
      <li><a href="index.php">Dashboard</a></li>
      <li><a href="tambah-transaksi.php">Tambah Transaksi</a></li>
      <li><a href="riwayat.php" class="nav-link active">Riwayat Transaksi</a></li>
      <li><a href="data-barang.php">Data Barang</a></li>
      <li><a href="data-member.php">Data Member</a></li>
      <li><a href="data-kasir.php">Data Kasir</a></li>
      <li><a href="data-kategori.php">Data Kategori</a></li>
    </ul>
  </div>
  <div class="main-content">
    <div class="judul">
      <h1>Riwayat Transaksi</h1>
      <div class="profil">
        <h4>Hai, <?= $_SESSION['nama']; ?> </h4>
        <img src="assets/icons/logout/logout.svg" alt="Logout" onclick="konfirmasiLogout()" style="cursor:pointer;">
      </div>
    </div>
    <div class="content">
      <section class="card">
        <div class="card-header">
          <h2>Daftar Transaksi</h2>
        </div>
        <div class="laporan-cetak">
            <form action="" method="POST">
                <label for="tanggal_transaksi">Cetak Laporan Transaksi per Tanggal:</label>
                <select name="tanggal_transaksi" required>
                    <option value="">-- Pilih Tanggal --</option>
                    <?php while ($tgl = mysqli_fetch_assoc($tanggal_list)) : ?>
                        <option value="<?= $tgl['tanggal_transaksi'] ?>"><?= date('d M Y', strtotime($tgl['tanggal_transaksi'])) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn-tambah" name="cetak_pdf">Cetak</button>
            </form>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Nama Member</th>
                <th>Kasir</th>
                <th>Total</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; while ($row = mysqli_fetch_assoc($query)) : ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><?= date('Y-m-d', strtotime($row['tanggal_transaksi'])) ?></td>
                <td><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                <td><?= htmlspecialchars($row['nama_kasir']); ?></td>
                <td><?= number_format($row['total'], 0, ',', '.'); ?></td>
                <td><button class="showBtn" onclick="tampilkanDetail(<?= $row['id_transaksi']; ?>)"><img src="assets/icons/view/View.svg" alt=""></button></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</div>

<!-- POPUP DETAIL TRANSAKSI -->
<div class="popup-overlay" id="popupDetail">
  <div class="popup-box">
    <h2>Detail Transaksi</h2>
    <table class="detail-table">
      <thead>
        <tr>
          <th>Nama Barang</th>
          <th>Jumlah</th>
          <th>Harga Satuan</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody id="detail-body"></tbody>
    </table>
    <h3 style="text-align: right; margin-top: 10px;">Grand Total: <span id="grandTotal"></span></h3>
    <div class="popup-btns">
      <button class="btn-batal" onclick="tutupPopupDetail()">Tutup</button>
    </div>
  </div>
</div>

<script>
function tampilkanDetail(id) {
  fetch('riwayat.php?id_transaksi=' + id, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('detail-body');
      tbody.innerHTML = '';
      data.detail.forEach(row => {
        tbody.innerHTML += `
          <tr>
            <td>${row.nama_barang}</td>
            <td>${row.jumlah}</td>
            <td>${parseInt(row.harga_satuan).toLocaleString()}</td>
            <td>${parseInt(row.subtotal).toLocaleString()}</td>
          </tr>`;
      });
      document.getElementById('grandTotal').textContent = data.grand_total.toLocaleString();
      document.getElementById('popupDetail').style.display = 'flex';
    });
}

function tutupPopupDetail() {
  document.getElementById('popupDetail').style.display = 'none';
}

function konfirmasiLogout() {
  if (confirm("Apakah Anda yakin ingin logout?")) {
    window.location.href = 'logout.php';
  }
}
</script>
</body>
</html>
