<?php
require_once '../../config/koneksi.php';
require_once '../../include/auth.php';
require_admin();

$totalProduk = mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM produk'))['total'];
$totalUser = mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM users'))['total'];
$totalKeranjang = mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM keranjang'))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/assets/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../../include/sidebar.php'; ?>
        <main class="flex-grow-1 p-4">
            <h1 class="page-title fw-bold mb-4">Dashboard</h1>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm"><div class="card-body"><p class="text-secondary mb-1">Total Produk</p><h2 class="fw-bold text-success"><?php echo $totalProduk; ?></h2></div></div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm"><div class="card-body"><p class="text-secondary mb-1">Total User</p><h2 class="fw-bold text-success"><?php echo $totalUser; ?></h2></div></div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm"><div class="card-body"><p class="text-secondary mb-1">Item Keranjang</p><h2 class="fw-bold text-success"><?php echo $totalKeranjang; ?></h2></div></div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
