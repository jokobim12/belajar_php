<?php
require_once '../config/koneksi.php';
require_once '../include/auth.php';
require_login();

$idUser = current_user()['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idKeranjang = (int) ($_POST['id_keranjang'] ?? 0);

    if (isset($_POST['hapus'])) {
        $stmt = mysqli_prepare($koneksi, 'DELETE FROM keranjang WHERE id_keranjang = ? AND id_user = ?');
        mysqli_stmt_bind_param($stmt, 'ii', $idKeranjang, $idUser);
        mysqli_stmt_execute($stmt);
    }

    if (isset($_POST['update'])) {
        $jumlah = max(1, (int) ($_POST['jumlah'] ?? 1));
        $stmt = mysqli_prepare($koneksi, 'UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ? AND id_user = ?');
        mysqli_stmt_bind_param($stmt, 'iii', $jumlah, $idKeranjang, $idUser);
        mysqli_stmt_execute($stmt);
    }

    header('Location: ./keranjang.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, 'SELECT k.id_keranjang, k.jumlah, p.nama_produk, p.kategori, p.harga, p.gambar FROM keranjang k JOIN produk p ON k.id_produk = p.id_produk WHERE k.id_user = ? ORDER BY k.id_keranjang DESC');
mysqli_stmt_bind_param($stmt, 'i', $idUser);
mysqli_stmt_execute($stmt);
$items = mysqli_stmt_get_result($stmt);
$cartRows = [];
$totalItem = 0;
$totalHarga = 0;

while ($row = mysqli_fetch_assoc($items)) {
    $row['subtotal'] = $row['harga'] * $row['jumlah'];
    $totalItem += $row['jumlah'];
    $totalHarga += $row['subtotal'];
    $cartRows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/assets/style.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>

    <main class="cart-page py-5">
        <div class="container">
            <div class="cart-hero mb-4">
                <div>
                    <p class="fw-semibold mb-1">Belanjaan saya</p>
                    <h1 class="fw-bold mb-2">Keranjang</h1>
                    <p class="mb-0">Atur jumlah produk sebelum lanjut checkout.</p>
                </div>
                <a href="./produk.php" class="btn btn-warning fw-semibold">Tambah Produk</a>
            </div>

            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <?php if (count($cartRows) === 0) : ?>
                        <div class="empty-cart text-center bg-white rounded-3 shadow-sm p-5">
                            <div class="empty-cart-icon mx-auto mb-3">0</div>
                            <h2 class="h4 page-title fw-bold">Keranjang masih kosong</h2>
                            <p class="text-secondary">Pilih produk dulu supaya bisa lanjut checkout.</p>
                            <a href="./produk.php" class="btn btn-success">Lihat Produk</a>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($cartRows as $item) : ?>
                            <div class="cart-item bg-white rounded-3 shadow-sm p-3">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-2">
                                        <img src="<?php echo htmlspecialchars($item['gambar'] ?: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80'); ?>" class="cart-item-image" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <span class="badge bg-green-soft text-success mb-2"><?php echo htmlspecialchars($item['kategori']); ?></span>
                                        <h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($item['nama_produk']); ?></h2>
                                        <p class="text-secondary mb-0"><?php echo rupiah($item['harga']); ?> per item</p>
                                    </div>
                                    <div class="col-md-3">
                                        <form method="post" class="cart-qty-form">
                                            <input type="hidden" name="id_keranjang" value="<?php echo (int) $item['id_keranjang']; ?>">
                                            <label class="form-label small text-secondary mb-1">Jumlah</label>
                                            <div class="input-group">
                                                <input type="number" name="jumlah" class="form-control" value="<?php echo (int) $item['jumlah']; ?>" min="1">
                                                <button class="btn btn-outline-success" name="update" type="submit">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-2">
                                        <p class="small text-secondary mb-1">Subtotal</p>
                                        <p class="fw-bold text-success mb-0"><?php echo rupiah($item['subtotal']); ?></p>
                                    </div>
                                    <div class="col-md-1 text-md-end">
                                        <form method="post">
                                            <input type="hidden" name="id_keranjang" value="<?php echo (int) $item['id_keranjang']; ?>">
                                            <button class="btn btn-light text-danger border" name="hapus" type="submit" title="Hapus item" data-confirm-delete-submit>X</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card cart-summary border-0 shadow-sm sticky-lg-top">
                        <div class="card-body p-4">
                            <h2 class="h4 fw-bold page-title mb-3">Ringkasan Pesanan</h2>
                            <div class="summary-row">
                                <span>Total item</span>
                                <strong><?php echo $totalItem; ?></strong>
                            </div>
                            <div class="summary-row">
                                <span>Subtotal produk</span>
                                <strong><?php echo rupiah($totalHarga); ?></strong>
                            </div>
                            <div class="summary-row">
                                <span>Ongkir</span>
                                <strong class="text-success">Dihitung nanti</strong>
                            </div>
                            <div class="summary-total">
                                <span>Total sementara</span>
                                <strong><?php echo rupiah($totalHarga); ?></strong>
                            </div>
                            <a href="./checkout.php" class="btn btn-success w-100 mt-4 py-2 <?php echo $totalItem === 0 ? 'disabled' : ''; ?>">Checkout</a>
                            <a href="./produk.php" class="btn btn-outline-success w-100 mt-2 py-2">Lanjut Belanja</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../include/modal/delete.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
