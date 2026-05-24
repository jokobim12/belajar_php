<?php
require_once '../../config/koneksi.php';
require_once '../../include/auth.php';
require_admin();

$statusOptions = ['menunggu_pembayaran', 'diproses', 'dikirim', 'selesai', 'batal'];
$paymentOptions = ['belum_bayar', 'menunggu_konfirmasi', 'lunas', 'gagal'];

function admin_payment_proof_src($path)
{
    if ($path === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//', $path)) {
        return $path;
    }

    return '../../' . ltrim(str_replace('../', '', $path), '/');
}

if (isset($_GET['hapus'])) {
    $idPesanan = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM pesanan WHERE id_pesanan = ?');
    mysqli_stmt_bind_param($stmt, 'i', $idPesanan);
    mysqli_stmt_execute($stmt);
    header('Location: ./checkout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPesanan = (int) ($_POST['id_pesanan'] ?? 0);
    $statusPesanan = $_POST['status_pesanan'] ?? 'diproses';
    $statusPembayaran = $_POST['status_pembayaran'] ?? 'menunggu_konfirmasi';
    $catatan = trim($_POST['catatan_admin'] ?? '');

    if (!in_array($statusPesanan, $statusOptions, true)) {
        $statusPesanan = 'diproses';
    }
    if (!in_array($statusPembayaran, $paymentOptions, true)) {
        $statusPembayaran = 'menunggu_konfirmasi';
    }

    $stmt = mysqli_prepare($koneksi, 'UPDATE pesanan SET status_pesanan = ?, status_pembayaran = ?, catatan_admin = ? WHERE id_pesanan = ?');
    mysqli_stmt_bind_param($stmt, 'sssi', $statusPesanan, $statusPembayaran, $catatan, $idPesanan);
    mysqli_stmt_execute($stmt);

    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    header('Location: ./checkout.php');
    exit;
}

$orders = mysqli_query($koneksi, 'SELECT p.*, u.nama, u.email FROM pesanan p JOIN users u ON p.id_user = u.id_user ORDER BY p.id_pesanan DESC');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Admin - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/assets/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../../include/sidebar.php'; ?>
        <main class="flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-success fw-semibold mb-1">Pesanan masuk</p>
                    <h1 class="page-title fw-bold mb-0">Data Checkout</h1>
                </div>
                <a href="./payment.php" class="btn btn-outline-success">Atur QRIS</a>
            </div>
            <div class="d-flex flex-column gap-4">
                <?php if (mysqli_num_rows($orders) === 0) : ?>
                    <div class="alert alert-success">Belum ada pesanan.</div>
                <?php endif; ?>

                <?php while ($order = mysqli_fetch_assoc($orders)) : ?>
                    <?php
                    $detailStmt = mysqli_prepare($koneksi, 'SELECT * FROM pesanan_detail WHERE id_pesanan = ?');
                    mysqli_stmt_bind_param($detailStmt, 'i', $order['id_pesanan']);
                    mysqli_stmt_execute($detailStmt);
                    $details = mysqli_stmt_get_result($detailStmt);
                    ?>
                    <div class="card admin-order-card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <span class="badge text-bg-success mb-2"><?php echo htmlspecialchars($order['status_pesanan']); ?></span>
                                    <h2 class="h4 fw-bold mb-1"><?php echo htmlspecialchars($order['kode_pesanan']); ?></h2>
                                    <p class="text-secondary mb-0"><?php echo htmlspecialchars($order['created_at']); ?></p>
                                </div>
                                <a href="./checkout.php?hapus=<?php echo (int) $order['id_pesanan']; ?>" class="btn btn-outline-danger btn-sm" data-confirm-delete>Hapus</a>
                            </div>
                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <div class="order-customer-box mb-3">
                                        <p class="mb-1"><strong>User:</strong> <?php echo htmlspecialchars($order['nama']); ?> - <?php echo htmlspecialchars($order['email']); ?></p>
                                        <p class="mb-1"><strong>Penerima:</strong> <?php echo htmlspecialchars($order['nama_penerima']); ?> (<?php echo htmlspecialchars($order['telepon']); ?>)</p>
                                        <p class="mb-0"><strong>Alamat:</strong> <?php echo htmlspecialchars($order['alamat']); ?></p>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead class="table-success"><tr><th>Produk</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
                                            <tbody>
                                                <?php while ($detail = mysqli_fetch_assoc($details)) : ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($detail['nama_produk']); ?></td>
                                                        <td><?php echo (int) $detail['jumlah']; ?></td>
                                                        <td><?php echo rupiah($detail['subtotal']); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="fw-bold text-success">Total: <?php echo rupiah($order['total_harga']); ?></p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="bg-green-soft rounded-3 p-3 mb-3">
                                        <div class="d-flex justify-content-between gap-3 mb-2">
                                            <span class="fw-semibold">Bukti Pembayaran</span>
                                            <span class="badge text-bg-success"><?php echo htmlspecialchars($order['status_pembayaran']); ?></span>
                                        </div>
                                        <?php if (!empty($order['bukti_pembayaran'])) : ?>
                                            <?php $proofSrc = admin_payment_proof_src($order['bukti_pembayaran']); ?>
                                            <a href="<?php echo htmlspecialchars($proofSrc); ?>" target="_blank" class="d-block">
                                                <img src="<?php echo htmlspecialchars($proofSrc); ?>" class="payment-proof-preview" alt="Bukti pembayaran <?php echo htmlspecialchars($order['kode_pesanan']); ?>">
                                            </a>
                                            <a href="<?php echo htmlspecialchars($proofSrc); ?>" target="_blank" class="btn btn-outline-success btn-sm w-100 mt-2">Buka Bukti Pembayaran</a>
                                        <?php else : ?>
                                            <p class="text-secondary mb-0">User belum upload bukti pembayaran.</p>
                                        <?php endif; ?>
                                    </div>
                                    <form method="post" data-confirm-save data-ajax-save>
                                        <input type="hidden" name="id_pesanan" value="<?php echo (int) $order['id_pesanan']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Status pesanan</label>
                                            <select name="status_pesanan" class="form-select">
                                                <?php foreach ($statusOptions as $status) : ?>
                                                    <option value="<?php echo $status; ?>" <?php echo $order['status_pesanan'] === $status ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $status)); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status pembayaran</label>
                                            <select name="status_pembayaran" class="form-select">
                                                <?php foreach ($paymentOptions as $status) : ?>
                                                    <option value="<?php echo $status; ?>" <?php echo $order['status_pembayaran'] === $status ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $status)); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Info untuk user</label>
                                            <textarea name="catatan_admin" class="form-control" rows="4" placeholder="Contoh: Pesanan sedang disiapkan kurir."><?php echo htmlspecialchars($order['catatan_admin']); ?></textarea>
                                        </div>
                                        <button class="btn btn-success w-100">Simpan Info Pesanan</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>
    <?php include '../../include/modal/save.php'; ?>
    <?php include '../../include/modal/delete.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
