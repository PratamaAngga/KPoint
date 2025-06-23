<?php
include 'auth.php';
include 'koneksi.php';

// Transaksi hari ini
$tanggal_hari_ini = date('Y-m-d');

// ✅ Jumlah transaksi hari ini
$query1 = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jumlah_transaksi 
    FROM transaksi 
    WHERE DATE(tanggal_transaksi) = '$tanggal_hari_ini'
");
$data1 = mysqli_fetch_assoc($query1);
$jumlah_transaksi = (int)$data1['jumlah_transaksi'];

// ✅ Pendapatan hari ini (hitung ulang dari detail_transaksi)
$query2 = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(d.subtotal), 0) AS pendapatan 
    FROM transaksi t 
    LEFT JOIN detail_transaksi d ON t.id_transaksi = d.id_transaksi 
    WHERE DATE(t.tanggal_transaksi) = '$tanggal_hari_ini'
");
$data2 = mysqli_fetch_assoc($query2);
$pendapatan = (int)$data2['pendapatan'];

// ✅ Rerata jumlah jenis barang per transaksi hari ini
$query3 = mysqli_query($koneksi, "
    SELECT AVG(jumlah_jenis) AS rerata_jenis
    FROM (
        SELECT COUNT(DISTINCT dt.id_barang) AS jumlah_jenis
        FROM transaksi t
        LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
        WHERE DATE(t.tanggal_transaksi) = '$tanggal_hari_ini'
        GROUP BY t.id_transaksi
    ) AS sub
");
$data3 = mysqli_fetch_assoc($query3);
$rerata_barang = round($data3['rerata_jenis'] ?? 0, 1);

// ✅ Transaksi terbesar (hitung ulang dari subtotal)
$query4 = mysqli_query($koneksi, "
    SELECT MAX(total_hitung) AS transaksi_terbesar
    FROM (
        SELECT COALESCE(SUM(d.subtotal), 0) AS total_hitung
        FROM transaksi t
        LEFT JOIN detail_transaksi d ON t.id_transaksi = d.id_transaksi
        WHERE DATE(t.tanggal_transaksi) = '$tanggal_hari_ini'
        GROUP BY t.id_transaksi
    ) AS sub
");
$data4 = mysqli_fetch_assoc($query4);
$transaksi_terbesar = (int)$data4['transaksi_terbesar'];

// laporan-transaksi.php

// Ambil tanggal unik dari database untuk pilihan
$tanggal_opsi = [];
$queryTanggal = mysqli_query($koneksi, "SELECT DISTINCT DATE(tanggal_transaksi) AS tanggal FROM transaksi ORDER BY tanggal DESC");
while ($row = mysqli_fetch_assoc($queryTanggal)) {
    $tanggal_opsi[] = $row['tanggal'];
}

// Jika form disubmit
if (isset($_POST['cetak_laporan'])) {
    $jenis = $_POST['jenis_laporan'];
    $periode = $_POST['periode'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];

    // Validasi dan filter data transaksi sesuai periode
    $where = "WHERE DATE(tanggal_transaksi) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'";

    if ($jenis === 'transaksi') {
        require('assets/fpdf/fpdf.php');

        $query = mysqli_query($koneksi, "
            SELECT 
                t.id_transaksi,
                t.tanggal_transaksi,
                m.nama_pelanggan,
                u.nama AS nama_kasir,
                COALESCE(SUM(d.subtotal), 0) AS total
            FROM transaksi t
            LEFT JOIN pelanggan m ON t.id_pelanggan = m.id_pelanggan
            LEFT JOIN kasir u ON t.id_user = u.id_user
            LEFT JOIN detail_transaksi d ON d.id_transaksi = t.id_transaksi
            $where
            GROUP BY t.id_transaksi
            ORDER BY t.tanggal_transaksi ASC
        ");

        $transaksi_data = [];
        $grand_total = 0;
        while ($r = mysqli_fetch_assoc($query)) {
            $transaksi_data[] = $r;
            $grand_total += $r['total'];
        }

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(190,10,'LAPORAN TRANSAKSI',0,1,'C');

        $pdf->SetFont('Arial','',12);
        $pdf->Cell(190,10,"Periode: ".date('d M Y', strtotime($tanggal_mulai))." - ".date('d M Y', strtotime($tanggal_selesai)),0,1);
        $pdf->Cell(190,10,'Grand Total: Rp ' . number_format($grand_total, 0, ',', '.'),0,1);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(10,10,'#',1);
        $pdf->Cell(40,10,'Tanggal',1);
        $pdf->Cell(50,10,'Nama Member',1);
        $pdf->Cell(50,10,'Kasir',1);
        $pdf->Cell(40,10,'Total',1);
        $pdf->Ln();

        $pdf->SetFont('Arial','',11);
        $no = 1;
        foreach ($transaksi_data as $tr) {
            $pdf->Cell(10,10,$no++,1);
            $pdf->Cell(40,10,date('d/m/Y', strtotime($tr['tanggal_transaksi'])),1);
            $pdf->Cell(50,10,$tr['nama_pelanggan'] ?? '-',1);
            $pdf->Cell(50,10,$tr['nama_kasir'] ?? '-',1);
            $pdf->Cell(40,10,'Rp ' . number_format($tr['total'], 0, ',', '.'),1);
            $pdf->Ln();
        }

        $filename = "laporan_transaksi_" . date('Ymd_His') . ".pdf";
        $pdf->Output('D', $filename);
        exit;
    } elseif (($jenis === 'barang_terlaris')) {
        require('assets/fpdf/fpdf.php');
        // Ambil data barang terlaris
        $sql = "
            SELECT b.nama_barang, SUM(dt.jumlah) AS total_terjual, SUM(dt.subtotal) AS total_pendapatan
            FROM detail_transaksi dt
            JOIN barang b ON dt.id_barang = b.id_barang
            JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
            $where
            GROUP BY dt.id_barang
            ORDER BY total_terjual DESC
        ";

        $result = mysqli_query($koneksi, $sql);
        $data_barang = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Total semua
        $grand_total = array_sum(array_column($data_barang, 'total_pendapatan'));

        // Buat PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(190,10,'LAPORAN BARANG TERLARIS',0,1,'C');

        $pdf->SetFont('Arial','',12);
        $pdf->Cell(190,10,"Periode: ".date('d M Y', strtotime($tanggal_mulai))." - ".date('d M Y', strtotime($tanggal_selesai)),0,1);
        $pdf->Cell(190,10,'Total Penjualan: Rp ' . number_format($grand_total, 0, ',', '.'),0,1);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(10,10,'#',1);
        $pdf->Cell(80,10,'Nama Barang',1);
        $pdf->Cell(30,10,'Jumlah',1);
        $pdf->Cell(50,10,'Subtotal',1);
        $pdf->Ln();

        $pdf->SetFont('Arial','',11);
        $no = 1;
        foreach ($data_barang as $item) {
            $pdf->Cell(10,10,$no++,1);
            $pdf->Cell(80,10,$item['nama_barang'],1);
            $pdf->Cell(30,10,$item['total_terjual'],1);
            $pdf->Cell(50,10,'Rp ' . number_format($item['total_pendapatan'], 0, ',', '.'),1);
            $pdf->Ln();
        }

        $filename = "laporan_barang_terlaris_" . date('Ymd_His') . ".pdf";
        $pdf->Output('D', $filename);
        exit;
    } elseif ($jenis === 'penjualan_kategori') {
        require('assets/fpdf/fpdf.php');

        $sql = "
            SELECT k.nama_kategori, 
                SUM(dt.jumlah) AS total_jumlah, 
                SUM(dt.subtotal) AS total_pendapatan
            FROM detail_transaksi dt
            JOIN barang b ON dt.id_barang = b.id_barang
            JOIN kategori_barang kb ON b.id_barang = kb.id_barang
            JOIN kategori k ON kb.id_kategori = k.id_kategori
            JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
            $where
            GROUP BY k.id_kategori
            ORDER BY total_jumlah DESC
        ";

        $result = mysqli_query($koneksi, $sql);
        $kategori_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // Hitung total keseluruhan pendapatan dari semua kategori
        $grand_total = array_sum(array_column($kategori_data, 'total_pendapatan'));

        // Buat PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(190,10,'LAPORAN PENJUALAN PER KATEGORI',0,1,'C');

        $pdf->SetFont('Arial','',12);
        $pdf->Cell(190,10,"Periode: ".date('d M Y', strtotime($tanggal_mulai))." - ".date('d M Y', strtotime($tanggal_selesai)),0,1);
        $pdf->Cell(190,10,'Total Pendapatan: Rp ' . number_format($grand_total, 0, ',', '.'),0,1);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(10,10,'#',1);
        $pdf->Cell(90,10,'Kategori',1);
        $pdf->Cell(30,10,'Jumlah',1);
        $pdf->Cell(60,10,'Pendapatan',1);
        $pdf->Ln();

        $pdf->SetFont('Arial','',11);
        $no = 1;
        foreach ($kategori_data as $row) {
            $pdf->Cell(10,10,$no++,1);
            $pdf->Cell(90,10,$row['nama_kategori'],1);
            $pdf->Cell(30,10,$row['total_jumlah'],1);
            $pdf->Cell(60,10,'Rp ' . number_format($row['total_pendapatan'], 0, ',', '.'),1);
            $pdf->Ln();
        }

        $filename = "laporan_kategori_" . date('Ymd_His') . ".pdf";
        $pdf->Output('D', $filename);
        exit;
    } elseif ($jenis === 'kasir') {
        require('assets/fpdf/fpdf.php');

        $sql = "
            SELECT 
                k.nama AS nama_kasir,
                COUNT(DISTINCT t.id_transaksi) AS jumlah_transaksi,
                COALESCE(SUM(d.subtotal), 0) AS total_pendapatan
            FROM transaksi t
            JOIN kasir k ON t.id_user = k.id_user
            LEFT JOIN detail_transaksi d ON t.id_transaksi = d.id_transaksi
            $where
            GROUP BY k.id_user
            ORDER BY total_pendapatan DESC
        ";
        $result = mysqli_query($koneksi, $sql);
        $data_kasir = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $total_semua = array_sum(array_column($data_kasir, 'total_pendapatan'));

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(190,10,'LAPORAN PENJUALAN PER KASIR',0,1,'C');

        $pdf->SetFont('Arial','',12);
        $pdf->Cell(190,10,"Periode: ".date('d M Y', strtotime($tanggal_mulai))." - ".date('d M Y', strtotime($tanggal_selesai)),0,1);
        $pdf->Cell(190,10,'Total Pendapatan: Rp ' . number_format($total_semua, 0, ',', '.'),0,1);
        $pdf->Ln(5);

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(10,10,'#',1);
        $pdf->Cell(80,10,'Nama Kasir',1);
        $pdf->Cell(30,10,'Transaksi',1);
        $pdf->Cell(60,10,'Total Pendapatan',1);
        $pdf->Ln();

        $pdf->SetFont('Arial','',11);
        $no = 1;
        foreach ($data_kasir as $kasir) {
            $pdf->Cell(10,10,$no++,1);
            $pdf->Cell(80,10,$kasir['nama_kasir'],1);
            $pdf->Cell(30,10,$kasir['jumlah_transaksi'],1);
            $pdf->Cell(60,10,'Rp ' . number_format($kasir['total_pendapatan'], 0, ',', '.'),1);
            $pdf->Ln();
        }

        $filename = "laporan_kasir_" . date('Ymd_His') . ".pdf";
        $pdf->Output('D', $filename);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Admin | Dashboard</title>
    <!-- <link rel="stylesheet" href="assets/css/style.css"> -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time(); ?>">
    <script src="assets/js/chart.umd.js"></script>
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <ul>
                <li><a href="index.php" class="logo">K<span>.</span>Point</a></li>
                <li><a href="index.php" class="nav-link active">Dashboard</a></li>
                <li><a href="tambah-transaksi.php" class="nav-link">Tambah Transaksi</a></li>
                <li><a href="riwayat.php" class="nav-link">Riwayat Transaksi</a></li>
                <li><a href="data-barang.php" class="nav-link">Data Barang</a></li>
                <li><a href="data-member.php" class="nav-link">Data Member</a></li>
                <li><a href="data-kasir.php" class="nav-link">Data Kasir</a></li>
                <li><a href="data-kategori.php" class="nav-link">Data Kategori</a></li>
            </ul>
        </div>

        <div class="sidebar-overlay" id="sidebarOverlay" onclick="tutupSidebar()"></div>

        <div class="main-content" id="main-content">
            <div class="judul">
                <button class="btn-menu-toggle" onclick="toggleSidebar()">☰</button>
                <h1>Dashboard</h1>
                <div class="profil">
                    <h4>Hai, <?= $_SESSION['nama']; ?> </h4>
                    <img src="assets/icons/logout/logout.svg" alt="Logout" onclick="konfirmasiLogout()" style="cursor:pointer;">
                </div>
            </div>
            <div class="info-boxes">
                <div class="box" style="background-color: #4f46e5">
                    <h3>Transaksi Hari Ini</h3>
                    <p><span><?= $jumlah_transaksi ?></span> Transaksi</p>
                </div>
                <div class="box" style="background-color: #10b981">
                    <h3>Pendapatan Hari Ini</h3>
                    <p>Rp. <span><?= number_format($pendapatan, 0, ',', '.') ?></span></p>
                </div>
                <div class="box" style="background-color: #f59e0b">
                    <h3>Rerata Barang per Transaksi</h3>
                    <p><span><?= $rerata_barang ?></span> Item</p>
                </div>
                <div class="box" style="background-color: #ef4444">
                    <h3>Transaksi Terbesar Hari Ini</h3>
                    <p>Rp. <span><?= number_format($transaksi_terbesar, 0, ',', '.') ?></span></p>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-box">
                    <h3 class="chart-title">Transaksi 7 Hari Terakhir</h3>
                    <canvas id="chart-transaksi"></canvas>
                </div>
                <div class="chart-box">
                    <h3 class="chart-title">Pendapatan Harian</h3>
                    <canvas id="chart-pendapatan"></canvas>
                </div>
                <div class="chart-box">
                    <h3 class="chart-title">Penjualan per Kategori</h3>
                    <canvas id="chart-kategori"></canvas>
                </div>
                <div class="chart-box">
                    <h3 class="chart-title">Top 5 Barang Terlaris</h3>
                    <canvas id="chart-terlaris"></canvas>
                </div>
            </div>
            <button onclick="bukaPopupLaporan()" class="btn-tambah" style="margin-top: 30px;"><img src="assets/icons/print/Print.png" alt=""> Cetak Laporan</button>
        </div>
        <!-- Popup Pilihan -->
        <div class="popup-overlay" id="popupLaporan">
            <div class="popup-box">
                <h2>Cetak Laporan</h2>
                <form method="POST" action="" onsubmit="return validasiPeriode()">
                <label>Jenis Laporan:</label>
                <select name="jenis_laporan" required>
                    <option value="transaksi">Laporan Transaksi</option>
                    <option value="barang_terlaris">Barang Terlaris</option>
                    <option value="penjualan_kategori">Penjualan per Kategori</option>
                    <option value="kasir">Laporan Kasir</option>
                </select>

                <label>Periode:</label>
                <select name="periode" id="periode" required onchange="toggleTanggalFields()">
                    <option value="harian">Harian</option>
                    <option value="mingguan">Mingguan</option>
                    <option value="bulanan">Bulanan</option>
                </select>

                <label>Tanggal Mulai:</label>
                <input type="date" name="tanggal_mulai" id="tanggal_mulai" required>
                <label>Tanggal Selesai:</label>
                <input type="date" name="tanggal_selesai" id="tanggal_selesai" required>

                <div class="popup-btns">
                    <button type="submit" name="cetak_laporan" class="btn-simpan">Cetak</button>
                    <button type="button" onclick="tutupPopupLaporan()" class="btn-batal">Batal</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    // Transaksi per 7 hari terakhir (jumlah transaksi)
    $transaksi_harian = [];
    $tanggal_label = [];

    for ($i = 6; $i >= 0; $i--) {
        $tanggal = date('Y-m-d', strtotime("-$i days"));
        $label = date('d M', strtotime($tanggal));

        $result = mysqli_query($koneksi, "
            SELECT COUNT(*) AS total 
            FROM transaksi 
            WHERE DATE(tanggal_transaksi) = '$tanggal'
        ");
        $data = mysqli_fetch_assoc($result);
        
        $tanggal_label[] = $label;
        $transaksi_harian[] = (int)$data['total'];
    }

    // Pendapatan per 7 hari terakhir (hitung ulang dari detail)
    $pendapatan_harian = [];
    for ($i = 6; $i >= 0; $i--) {
        $tanggal = date('Y-m-d', strtotime("-$i days"));

        $result = mysqli_query($koneksi, "
            SELECT COALESCE(SUM(d.subtotal), 0) AS total 
            FROM transaksi t
            LEFT JOIN detail_transaksi d ON t.id_transaksi = d.id_transaksi
            WHERE DATE(t.tanggal_transaksi) = '$tanggal'
        ");
        $data = mysqli_fetch_assoc($result);
        $pendapatan_harian[] = (int)$data['total'];
    }

    // Penjualan per kategori
    $kategori_nama = [];
    $kategori_jumlah = [];
    $kategori_result = mysqli_query($koneksi, "
        SELECT k.nama_kategori, COALESCE(SUM(dt.jumlah), 0) AS jumlah
        FROM kategori k
        JOIN kategori_barang kb ON k.id_kategori = kb.id_kategori
        JOIN barang b ON kb.id_barang = b.id_barang
        LEFT JOIN detail_transaksi dt ON dt.id_barang = b.id_barang
        GROUP BY k.id_kategori
    ");
    while ($row = mysqli_fetch_assoc($kategori_result)) {
        $kategori_nama[] = $row['nama_kategori'];
        $kategori_jumlah[] = (int)$row['jumlah'];
    }

    // Top 5 barang terlaris
    $top_barang = [];
    $top_jumlah = [];
    $barang_result = mysqli_query($koneksi, "
        SELECT b.nama_barang, COALESCE(SUM(dt.jumlah), 0) AS total_terjual
        FROM barang b
        LEFT JOIN detail_transaksi dt ON dt.id_barang = b.id_barang
        GROUP BY b.id_barang
        ORDER BY total_terjual DESC
        LIMIT 5
    ");
    while ($row = mysqli_fetch_assoc($barang_result)) {
        $top_barang[] = $row['nama_barang'];
        $top_jumlah[] = (int)$row['total_terjual'];
    }
    ?>

    <script>
        // Ambil data dari PHP
        const labelHari = <?= json_encode($tanggal_label) ?>;
        const dataTransaksi = <?= json_encode($transaksi_harian) ?>;
        const dataPendapatan = <?= json_encode($pendapatan_harian) ?>;
        const labelKategori = <?= json_encode($kategori_nama) ?>;
        const dataKategori = <?= json_encode($kategori_jumlah) ?>;
        const labelBarang = <?= json_encode($top_barang) ?>;
        const dataBarang = <?= json_encode($top_jumlah) ?>;

        // Transaksi Harian (Bar Chart)
        new Chart(document.getElementById('chart-transaksi'), {
            type: 'bar',
            data: {
                labels: labelHari,
                datasets: [{
                    label: 'Transaksi',
                    data: dataTransaksi,
                    backgroundColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    tooltip: {
                        enabled: true
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Pendapatan Harian (Line Chart)
        new Chart(document.getElementById('chart-pendapatan'), {
            type: 'line',
            data: {
                labels: labelHari,
                datasets: [{
                    label: 'Pendapatan',
                    data: dataPendapatan,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                plugins: {
                    tooltip: {
                        enabled: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Penjualan per Kategori (Pie Chart)
        new Chart(document.getElementById('chart-kategori'), {
            type: 'pie',
            data: {
                labels: labelKategori,
                datasets: [{
                    label: 'Penjualan',
                    data: dataKategori,
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#6366f1']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Barang Terlaris (Horizontal Bar)
        new Chart(document.getElementById('chart-terlaris'), {
            type: 'bar',
            data: {
                labels: labelBarang,
                datasets: [{
                    label: 'Jumlah Terjual',
                    data: dataBarang,
                    backgroundColor: '#f59e0b'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'nearest',
                    intersect: true
                },
                plugins: {
                    tooltip: {
                        enabled: true
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    
    <script>
        function konfirmasiLogout() {
            const yakin = confirm("Apakah Anda yakin ingin logout?");
            if (yakin) {
                window.location.href = "logout.php";
            }
        }

        function bukaPopupLaporan() {
            document.getElementById("popupLaporan").style.display = "flex";
        }

        function tutupPopupLaporan() {
            document.getElementById("popupLaporan").style.display = "none";
        }

        function validasiPeriode() {
            const periode = document.getElementById('periode').value;
            const tglMulai = new Date(document.getElementById('tanggal_mulai').value);
            const tglSelesai = new Date(document.getElementById('tanggal_selesai').value);

            const selisihHari = Math.ceil((tglSelesai - tglMulai) / (1000 * 60 * 60 * 24)) + 1;

            if (periode === 'harian' && selisihHari !== 1) {
                alert("Untuk periode harian, pilih hanya 1 tanggal.");
                return false;
            }
            if (periode === 'mingguan' && selisihHari > 7) {
                alert("Untuk periode mingguan, pilih maksimal 7 hari.");
                return false;
            }
            if (periode === 'bulanan' && selisihHari > 31) {
                alert("Untuk periode bulanan, pilih maksimal 31 hari.");
                return false;
            }
            return true;
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }

        function tutupSidebar() {
            document.querySelector('.sidebar').classList.remove('active');
        }

        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                document.querySelector('.sidebar').classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>