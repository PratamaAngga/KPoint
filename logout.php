<?php
session_start();
session_destroy(); // hapus semua sesi
header("Location: signin.php"); // kembali ke halaman login
exit;
?>