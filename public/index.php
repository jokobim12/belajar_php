<?php
require_once '../config/koneksi.php';
require_once '../include/auth.php';

$produkTerbaru = mysqli_query($koneksi, 'SELECT * FROM produk ORDER BY id_produk DESC LIMIT 3');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/style.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>

    <main>
        <section class="hero-section py-5">
            <div class="container py-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="badge text-bg-warning mb-3">Belanja hemat setiap hari</span>
                        <h1 class="display-5 fw-bold">Produk segar dan kebutuhan harian dalam satu toko.</h1>
                        <p class="lead mb-4">Temukan pilihan produk terbaik, masukkan ke keranjang, lalu lanjutkan belanja dengan mudah.</p>
                        <a href="../pages/produk.php" class="btn btn-warning btn-lg fw-semibold">Lihat Produk</a>
                    </div>
                    <div class="col-lg-5">
                        <div class="bg-white text-success rounded-3 p-4 shadow">
                            <h2 class="h4 fw-bold">TokoPaedi</h2>
                            <p class="mb-0 text-secondary">Produk yang tampil di bawah ini langsung dibaca dari tabel produk.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="page-title fw-bold mb-0">Produk Terbaru</h2>
                    <a href="../pages/produk.php" class="btn btn-outline-success">Semua Produk</a>
                </div>
                <div class="row g-4">
                    <?php if (mysqli_num_rows($produkTerbaru) === 0) : ?>
                        <div class="col-12"><div class="alert alert-success mb-0">Belum ada produk. Tambahkan dari halaman admin.</div></div>
                    <?php endif; ?>
                    <?php while ($produk = mysqli_fetch_assoc($produkTerbaru)) : ?>
                        <div class="col-md-4">
                            <div class="card product-card h-100 border-0 shadow-sm">
                                <img src="<?php echo htmlspecialchars($produk['gambar'] ?: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80'); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                                <div class="card-body">
                                    <span class="badge bg-green-soft text-success mb-2"><?php echo htmlspecialchars($produk['kategori']); ?></span>
                                    <h3 class="h5"><?php echo htmlspecialchars($produk['nama_produk']); ?></h3>
                                    <p class="fw-bold text-success mb-0"><?php echo rupiah($produk['harga']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
