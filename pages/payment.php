<?php
require_once '../config/koneksi.php';
require_once '../include/auth.php';
require_login();

$idUser = current_user()['id_user'];
$idPesanan = (int) ($_GET['id'] ?? 0);
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPesanan = (int) ($_POST['id_pesanan'] ?? 0);
    $bukti = '';

    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['bukti_pembayaran']['tmp_name']);

        if (isset($allowed[$mime])) {
            $dir = '../public/uploads/payment/';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $file = 'payment_' . time() . '_' . random_int(1000, 9999) . '.' . $allowed[$mime];
            if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $dir . $file)) {
                $bukti = '../public/uploads/payment/' . $file;
            }
        }
    }

    $stmt = mysqli_prepare($koneksi, 'UPDATE pesanan SET bukti_pembayaran = ?, status_pembayaran = "menunggu_konfirmasi", status_pesanan = "diproses" WHERE id_pesanan = ? AND id_user = ?');
    mysqli_stmt_bind_param($stmt, 'sii', $bukti, $idPesanan, $idUser);
    mysqli_stmt_execute($stmt);
    header('Location: ./info.php?id=' . $idPesanan);
    exit;
}

$stmt = mysqli_prepare($koneksi, 'SELECT * FROM pesanan WHERE id_pesanan = ? AND id_user = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'ii', $idPesanan, $idUser);
mysqli_stmt_execute($stmt);
$pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$qris = mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT * FROM pengaturan_pembayaran WHERE metode = "qris" AND aktif = 1 LIMIT 1'));

if (!$pesanan) {
    header('Location: ./info.php');
    exit;
}

function user_asset_src($path)
{
    if ($path === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//', $path)) {
        return $path;
    }

    return '../' . ltrim(str_replace('../', '', $path), '/');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/assets/style.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>
    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h1 class="h3 page-title fw-bold">Pembayaran</h1>
                            <p class="text-secondary">Kode pesanan: <strong><?php echo htmlspecialchars($pesanan['kode_pesanan']); ?></strong></p>
                            <div class="bg-green-soft rounded-3 p-3 mb-4">
                                <p class="mb-1">Total pembayaran</p>
                                <h2 class="h4 fw-bold text-success mb-0"><?php echo rupiah($pesanan['total_harga']); ?></h2>
                            </div>
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id_pesanan" value="<?php echo (int) $pesanan['id_pesanan']; ?>">
                                <label class="form-label">Upload bukti pembayaran</label>
                                <input type="file" name="bukti_pembayaran" class="form-control mb-3" accept="image/jpeg,image/png,image/webp" required>
                                <button class="btn btn-success w-100">Kirim Bukti Pembayaran</button>
                            </form>
                            <a href="./info.php?id=<?php echo (int) $pesanan['id_pesanan']; ?>" class="btn btn-outline-success w-100 mt-2">Lihat Info Pesanan</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h4 fw-bold page-title mb-3">Scan QRIS</h2>
                            <?php if (!empty($qris['gambar'])) : ?>
                                <img src="<?php echo htmlspecialchars(user_asset_src($qris['gambar'])); ?>" class="qris-preview" alt="QRIS pembayaran">
                                <p class="text-secondary mt-3 mb-0">Scan QRIS ini, lalu upload bukti pembayaran di form sebelah.</p>
                            <?php else : ?>
                                <div class="empty-payment-preview">QRIS belum tersedia. Hubungi admin.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
