<?php
require_once '../../config/koneksi.php';
require_once '../../include/auth.php';
require_admin();

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gambar = trim($_POST['gambar_lama'] ?? '');

    if (isset($_FILES['gambar_qris']) && $_FILES['gambar_qris']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['gambar_qris']['tmp_name']);

        if (isset($allowed[$mime])) {
            $dir = '../../public/uploads/qris/';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $file = 'qris_' . time() . '_' . random_int(1000, 9999) . '.' . $allowed[$mime];
            if (move_uploaded_file($_FILES['gambar_qris']['tmp_name'], $dir . $file)) {
                $gambar = '../public/uploads/qris/' . $file;
            }
        }
    }

    $stmt = mysqli_prepare($koneksi, 'UPDATE pengaturan_pembayaran SET nama_metode = "QRIS", gambar = ?, aktif = 1 WHERE metode = "qris"');
    mysqli_stmt_bind_param($stmt, 's', $gambar);
    mysqli_stmt_execute($stmt);
    $success = 'QRIS berhasil disimpan.';
}

$setting = mysqli_fetch_assoc(mysqli_query($koneksi, 'SELECT * FROM pengaturan_pembayaran WHERE metode = "qris" LIMIT 1'));

function admin_asset_src($path)
{
    if ($path === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//', $path)) {
        return $path;
    }

    return '../../' . ltrim(str_replace('../', '', $path), '/');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Admin - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/assets/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../../include/sidebar.php'; ?>
        <main class="flex-grow-1 p-4">
            <h1 class="page-title fw-bold mb-4">Pengaturan Payment</h1>
            <?php if ($success) : ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">Metode Pembayaran Aktif</h2>
                            <div class="bg-green-soft rounded-3 p-3 mb-4">
                                <span class="badge text-bg-success mb-2">Aktif</span>
                                <h3 class="h4 fw-bold mb-0">QRIS</h3>
                            </div>

                            <form method="post" enctype="multipart/form-data" data-confirm-save>
                                <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($setting['gambar'] ?? ''); ?>">
                                <label class="form-label">Upload gambar QRIS</label>
                                <input type="file" name="gambar_qris" class="form-control mb-3" accept="image/jpeg,image/png,image/webp" required>
                                <button class="btn btn-success w-100">Simpan QRIS</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h5 fw-bold mb-3">Preview QRIS</h2>
                            <?php if (!empty($setting['gambar'])) : ?>
                                <img src="<?php echo htmlspecialchars(admin_asset_src($setting['gambar'])); ?>" class="qris-preview" alt="QRIS toko">
                            <?php else : ?>
                                <div class="empty-payment-preview">QRIS belum diupload.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include '../../include/modal/save.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
