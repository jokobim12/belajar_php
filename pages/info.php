<?php
require_once '../config/koneksi.php';
require_once '../include/auth.php';
require_login();

$idUser = current_user()['id_user'];
$idPesanan = (int) ($_GET['id'] ?? 0);

if (isset($_GET['hapus'])) {
    $idHapus = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM pesanan WHERE id_pesanan = ? AND id_user = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $idHapus, $idUser);
    mysqli_stmt_execute($stmt);
    header('Location: ./info.php');
    exit;
}

if (($_GET['ajax'] ?? '') === 'status') {
    $stmt = mysqli_prepare($koneksi, 'SELECT id_pesanan, kode_pesanan, status_pesanan, status_pembayaran, catatan_admin, updated_at FROM pesanan WHERE id_user = ? ORDER BY updated_at DESC LIMIT 5');
    mysqli_stmt_bind_param($stmt, 'i', $idUser);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($rows);
    exit;
}

if ($idPesanan > 0) {
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM pesanan WHERE id_pesanan = ? AND id_user = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'ii', $idPesanan, $idUser);
} else {
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM pesanan WHERE id_user = ? ORDER BY id_pesanan DESC');
    mysqli_stmt_bind_param($stmt, 'i', $idUser);
}
mysqli_stmt_execute($stmt);
$pesananResult = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Pesanan - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/assets/style.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>
    <main class="py-5">
        <div class="container">
            <div class="cart-hero mb-4">
                <div>
                    <p class="fw-semibold mb-1">Status belanja</p>
                    <h1 class="fw-bold mb-2">Info Pesanan</h1>
                    <p class="mb-0">Pantau status pesanan dan pembayaran dari admin secara realtime.</p>
                </div>
                <span class="badge text-bg-warning">Realtime aktif</span>
            </div>
            <div id="orderNotification" class="alert alert-success d-none"></div>
            <div class="row g-4">
                <?php if (mysqli_num_rows($pesananResult) === 0) : ?>
                    <div class="col-12"><div class="alert alert-success">Belum ada pesanan.</div></div>
                <?php endif; ?>
                <?php while ($pesanan = mysqli_fetch_assoc($pesananResult)) : ?>
                    <div class="col-lg-6">
                        <div class="card order-info-card border-0 shadow-sm" data-order-id="<?php echo (int) $pesanan['id_pesanan']; ?>" data-order-updated="<?php echo htmlspecialchars($pesanan['updated_at']); ?>">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h2 class="h5 fw-bold"><?php echo htmlspecialchars($pesanan['kode_pesanan']); ?></h2>
                                        <p class="text-secondary mb-0"><?php echo htmlspecialchars($pesanan['created_at']); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge text-bg-success order-status d-block mb-2"><?php echo htmlspecialchars($pesanan['status_pesanan']); ?></span>
                                        <a href="./info.php?hapus=<?php echo (int) $pesanan['id_pesanan']; ?>" class="btn btn-outline-danger btn-sm" data-confirm-delete>Hapus</a>
                                    </div>
                                </div>
                                <hr>
                                <div class="order-status-grid mb-3">
                                    <div>
                                        <span>Pembayaran</span>
                                        <strong class="payment-status"><?php echo htmlspecialchars($pesanan['status_pembayaran']); ?></strong>
                                    </div>
                                    <div>
                                        <span>Total</span>
                                        <strong class="text-success"><?php echo rupiah($pesanan['total_harga']); ?></strong>
                                    </div>
                                </div>
                                <div class="admin-note-box mb-3">
                                    <span>Catatan admin</span>
                                    <p class="admin-note mb-0"><?php echo htmlspecialchars($pesanan['catatan_admin'] ?: '-'); ?></p>
                                </div>
                                <?php if ($pesanan['status_pembayaran'] === 'belum_bayar') : ?>
                                    <a href="./payment.php?id=<?php echo (int) $pesanan['id_pesanan']; ?>" class="btn btn-success">Bayar Sekarang</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
    <?php include '../include/modal/delete.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        const notification = document.getElementById('orderNotification');

        async function pollOrders() {
            const response = await fetch('./info.php?ajax=status', {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const orders = await response.json();

            orders.forEach(order => {
                const card = document.querySelector(`[data-order-id="${order.id_pesanan}"]`);
                if (!card) return;

                if (card.dataset.orderUpdated && card.dataset.orderUpdated !== order.updated_at) {
                    notification.textContent = `Pesanan ${order.kode_pesanan} diperbarui: ${order.status_pesanan}.`;
                    notification.classList.remove('d-none');
                }

                card.dataset.orderUpdated = order.updated_at;
                card.querySelector('.order-status').textContent = order.status_pesanan;
                card.querySelector('.payment-status').textContent = order.status_pembayaran;
                card.querySelector('.admin-note').textContent = order.catatan_admin || '-';
            });
        }

        setInterval(pollOrders, 4000);
    </script>
</body>
</html>
