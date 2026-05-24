<?php
require_once '../config/koneksi.php';
require_once '../include/auth.php';
require_login();

$idUser = current_user()['id_user'];
$error = '';

$userStmt = mysqli_prepare($koneksi, 'SELECT * FROM users WHERE id_user = ? LIMIT 1');
mysqli_stmt_bind_param($userStmt, 'i', $idUser);
mysqli_stmt_execute($userStmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));

$cartStmt = mysqli_prepare($koneksi, 'SELECT k.id_produk, k.jumlah, p.nama_produk, p.harga FROM keranjang k JOIN produk p ON k.id_produk = p.id_produk WHERE k.id_user = ?');
mysqli_stmt_bind_param($cartStmt, 'i', $idUser);
mysqli_stmt_execute($cartResult = $cartStmt);
$items = mysqli_stmt_get_result($cartStmt);
$cartRows = [];
$totalHarga = 0;

while ($item = mysqli_fetch_assoc($items)) {
    $item['subtotal'] = $item['harga'] * $item['jumlah'];
    $totalHarga += $item['subtotal'];
    $cartRows[] = $item;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (count($cartRows) === 0) {
        $error = 'Keranjang masih kosong.';
    } else {
        $kode = 'TP' . date('YmdHis') . random_int(10, 99);
        $nama = trim($_POST['nama_penerima'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $metode = 'qris';

        $stmt = mysqli_prepare($koneksi, 'INSERT INTO pesanan (id_user, kode_pesanan, nama_penerima, telepon, alamat, metode_pembayaran, total_harga) VALUES (?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'isssssi', $idUser, $kode, $nama, $telepon, $alamat, $metode, $totalHarga);
        mysqli_stmt_execute($stmt);
        $idPesanan = mysqli_insert_id($koneksi);

        foreach ($cartRows as $item) {
            $detailStmt = mysqli_prepare($koneksi, 'INSERT INTO pesanan_detail (id_pesanan, id_produk, nama_produk, harga, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($detailStmt, 'iisiii', $idPesanan, $item['id_produk'], $item['nama_produk'], $item['harga'], $item['jumlah'], $item['subtotal']);
            mysqli_stmt_execute($detailStmt);
        }

        $clearStmt = mysqli_prepare($koneksi, 'DELETE FROM keranjang WHERE id_user = ?');
        mysqli_stmt_bind_param($clearStmt, 'i', $idUser);
        mysqli_stmt_execute($clearStmt);

        header('Location: ./payment.php?id=' . $idPesanan);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/assets/style.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>
    <main class="py-5">
        <div class="container">
            <h1 class="page-title fw-bold mb-4">Checkout</h1>
            <?php if ($error) : ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">Data Pengiriman</h2>
                            <form method="post" id="checkoutForm">
                                <div class="mb-3">
                                    <label class="form-label">Nama penerima</label>
                                    <input type="text" name="nama_penerima" class="form-control" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="telepon" class="form-control" value="<?php echo htmlspecialchars($user['telepon']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control" rows="4" required><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Metode pembayaran</label>
                                    <div class="payment-method-static">
                                        <span class="badge text-bg-success">Aktif</span>
                                        <strong>QRIS</strong>
                                        <small>Metode pembayaran diatur oleh admin.</small>
                                    </div>
                                </div>
                                <button class="btn btn-success w-100" <?php echo count($cartRows) === 0 ? 'disabled' : ''; ?>>Buat Pesanan</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card cart-summary border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold page-title">Ringkasan</h2>
                            <?php foreach ($cartRows as $item) : ?>
                                <div class="summary-row">
                                    <span><?php echo htmlspecialchars($item['nama_produk']); ?> x <?php echo (int) $item['jumlah']; ?></span>
                                    <strong><?php echo rupiah($item['subtotal']); ?></strong>
                                </div>
                            <?php endforeach; ?>
                            <div class="summary-total"><span>Total</span><strong><?php echo rupiah($totalHarga); ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
