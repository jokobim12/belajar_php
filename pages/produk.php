<?php
require_once '../config/koneksi.php';
require_once '../include/auth.php';

$kategoriOptions = ['barang', 'makanan', 'minuman', 'sembako', 'elektronik', 'kesehatan'];

function is_ajax_request()
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

function render_cart_control($product, $cartQty)
{
    ob_start();
    ?>
    <div class="product-cart-action mt-auto" data-product-action="<?php echo (int) $product['id_produk']; ?>">
        <?php if ($cartQty > 0) : ?>
            <div class="product-qty-control">
                <form method="post" data-cart-form>
                    <input type="hidden" name="id_produk" value="<?php echo (int) $product['id_produk']; ?>">
                    <input type="hidden" name="aksi" value="minus">
                    <button type="submit" class="btn btn-outline-success" aria-label="Kurangi jumlah">-</button>
                </form>
                <span><?php echo (int) $cartQty; ?></span>
                <form method="post" data-cart-form>
                    <input type="hidden" name="id_produk" value="<?php echo (int) $product['id_produk']; ?>">
                    <input type="hidden" name="aksi" value="add">
                    <button type="submit" class="btn btn-success" aria-label="Tambah jumlah" <?php echo $cartQty >= (int) $product['stok'] ? 'disabled' : ''; ?>>+</button>
                </form>
            </div>
        <?php else : ?>
            <form method="post" data-cart-form>
                <input type="hidden" name="id_produk" value="<?php echo (int) $product['id_produk']; ?>">
                <input type="hidden" name="aksi" value="add">
                <button type="submit" class="btn btn-success w-100" <?php echo (int) $product['stok'] <= 0 ? 'disabled' : ''; ?>>Tambah Keranjang</button>
            </form>
        <?php endif; ?>
    </div>
    <?php
    return trim(ob_get_clean());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produk'])) {
    require_login();

    $idUser = current_user()['id_user'];
    $idProduk = (int) $_POST['id_produk'];
    $aksi = $_POST['aksi'] ?? 'add';

    $produkStmt = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE id_produk = ? LIMIT 1');
    mysqli_stmt_bind_param($produkStmt, 'i', $idProduk);
    mysqli_stmt_execute($produkStmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($produkStmt));

    if (!$product) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
        exit;
    }

    $cek = mysqli_prepare($koneksi, 'SELECT id_keranjang, jumlah FROM keranjang WHERE id_user = ? AND id_produk = ?');
    mysqli_stmt_bind_param($cek, 'ii', $idUser, $idProduk);
    mysqli_stmt_execute($cek);
    $cart = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
    $newQty = 0;

    if ($aksi === 'minus' && $cart) {
        $newQty = (int) $cart['jumlah'] - 1;
        if ($newQty <= 0) {
            $stmt = mysqli_prepare($koneksi, 'DELETE FROM keranjang WHERE id_keranjang = ?');
            mysqli_stmt_bind_param($stmt, 'i', $cart['id_keranjang']);
            $newQty = 0;
        } else {
            $stmt = mysqli_prepare($koneksi, 'UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?');
            mysqli_stmt_bind_param($stmt, 'ii', $newQty, $cart['id_keranjang']);
        }
    } elseif ($cart) {
        $newQty = min((int) $cart['jumlah'] + 1, (int) $product['stok']);
        $stmt = mysqli_prepare($koneksi, 'UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?');
        mysqli_stmt_bind_param($stmt, 'ii', $newQty, $cart['id_keranjang']);
    } else {
        $newQty = 1;
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, 1)');
        mysqli_stmt_bind_param($stmt, 'ii', $idUser, $idProduk);
    }

    mysqli_stmt_execute($stmt);
    $message = $product['nama_produk'] . ' sudah masuk di keranjang.';

    if (is_ajax_request()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'id_produk' => $idProduk,
            'quantity' => $newQty,
            'html' => render_cart_control($product, $newQty),
        ]);
        exit;
    }

    $_SESSION['cart_success'] = $message;
    header('Location: ./produk.php');
    exit;
}

$cartSuccess = $_SESSION['cart_success'] ?? '';
unset($_SESSION['cart_success']);

$keyword = trim($_GET['q'] ?? '');
$kategori = $_GET['kategori'] ?? '';
if (!in_array($kategori, $kategoriOptions, true)) {
    $kategori = '';
}

if ($keyword !== '' && $kategori !== '') {
    $like = '%' . $keyword . '%';
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE kategori = ? AND (nama_produk LIKE ? OR kategori LIKE ?) ORDER BY id_produk DESC');
    mysqli_stmt_bind_param($stmt, 'sss', $kategori, $like, $like);
    mysqli_stmt_execute($stmt);
    $products = mysqli_stmt_get_result($stmt);
} elseif ($keyword !== '') {
    $like = '%' . $keyword . '%';
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE nama_produk LIKE ? OR kategori LIKE ? ORDER BY id_produk DESC');
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $products = mysqli_stmt_get_result($stmt);
} elseif ($kategori !== '') {
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE kategori = ? ORDER BY id_produk DESC');
    mysqli_stmt_bind_param($stmt, 's', $kategori);
    mysqli_stmt_execute($stmt);
    $products = mysqli_stmt_get_result($stmt);
} else {
    $products = mysqli_query($koneksi, 'SELECT * FROM produk ORDER BY id_produk DESC');
}

$cartMap = [];
if (is_logged_in()) {
    $idUser = current_user()['id_user'];
    $cartStmt = mysqli_prepare($koneksi, 'SELECT id_produk, jumlah FROM keranjang WHERE id_user = ?');
    mysqli_stmt_bind_param($cartStmt, 'i', $idUser);
    mysqli_stmt_execute($cartStmt);
    $cartItems = mysqli_stmt_get_result($cartStmt);

    while ($cartItem = mysqli_fetch_assoc($cartItems)) {
        $cartMap[(int) $cartItem['id_produk']] = (int) $cartItem['jumlah'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/assets/style.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>

    <main class="py-5">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <p class="text-success fw-semibold mb-1">Katalog</p>
                    <h1 class="page-title fw-bold mb-0">Produk Pilihan</h1>
                </div>
                <form class="d-flex" method="get" id="productSearchForm">
                    <?php if ($kategori !== '') : ?>
                        <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kategori); ?>">
                    <?php endif; ?>
                    <input class="form-control me-2" type="search" name="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Cari produk">
                    <button class="btn btn-success" type="submit">Cari</button>
                </form>
            </div>

            <div class="category-filter mb-4" id="categoryFilter">
                <a href="./produk.php<?php echo $keyword !== '' ? '?q=' . urlencode($keyword) : ''; ?>" class="btn <?php echo $kategori === '' ? 'btn-success' : 'btn-outline-success'; ?>">Semua</a>
                <?php foreach ($kategoriOptions as $kategoriOption) : ?>
                    <?php
                    $query = http_build_query(array_filter([
                        'kategori' => $kategoriOption,
                        'q' => $keyword,
                    ]));
                    ?>
                    <a href="./produk.php?<?php echo $query; ?>" class="btn <?php echo $kategori === $kategoriOption ? 'btn-success' : 'btn-outline-success'; ?>"><?php echo ucfirst($kategoriOption); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="row g-4" id="productGrid">
                <?php if (mysqli_num_rows($products) === 0) : ?>
                    <div class="col-12">
                        <div class="alert alert-success">Belum ada produk.</div>
                    </div>
                <?php endif; ?>

                <?php while ($product = mysqli_fetch_assoc($products)) : ?>
                    <?php $cartQty = $cartMap[(int) $product['id_produk']] ?? 0; ?>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card product-card h-100 border-0 shadow-sm">
                            <img src="<?php echo htmlspecialchars($product['gambar'] ?: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80'); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>">
                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-green-soft text-success align-self-start mb-2"><?php echo htmlspecialchars($product['kategori']); ?></span>
                                <h2 class="h5 card-title"><?php echo htmlspecialchars($product['nama_produk']); ?></h2>
                                <p class="text-secondary small mb-2">Stok: <?php echo (int) $product['stok']; ?></p>
                                <p class="fw-bold text-success mb-3"><?php echo rupiah($product['harga']); ?></p>
                                <?php echo render_cart_control($product, $cartQty); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="cartInfoToast" class="toast align-items-center text-bg-success border-0 shadow" role="status" aria-live="polite" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"><?php echo htmlspecialchars($cartSuccess); ?></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        const cartToastElement = document.getElementById('cartInfoToast');
        const cartToastBody = cartToastElement.querySelector('.toast-body');
        const cartToast = new bootstrap.Toast(cartToastElement, {
            autohide: true,
            delay: 1800
        });

        function showCartInfo(message) {
            cartToastBody.textContent = message;
            cartToast.show();
        }

        async function submitCartForm(form) {
            const response = await fetch('./produk.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            });

            if (response.redirected) {
                window.location.href = response.url;
                return;
            }

            const data = await response.json();
            const actionWrapper = document.querySelector(`[data-product-action="${data.id_produk}"]`);

            if (actionWrapper) {
                actionWrapper.outerHTML = data.html;
            }

            showCartInfo(data.message);
        }

        async function loadProductContent(url, pushState = true) {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const html = await response.text();
            const page = new DOMParser().parseFromString(html, 'text/html');

            document.getElementById('categoryFilter').innerHTML = page.getElementById('categoryFilter').innerHTML;
            document.getElementById('productGrid').innerHTML = page.getElementById('productGrid').innerHTML;

            if (pushState) {
                history.pushState(null, '', url);
            }
        }

        document.addEventListener('submit', function (event) {
            if (event.target.matches('[data-cart-form]')) {
                event.preventDefault();
                submitCartForm(event.target);
                return;
            }

            if (event.target.matches('#productSearchForm')) {
                event.preventDefault();
                const params = new URLSearchParams(new FormData(event.target));
                loadProductContent(`./produk.php?${params.toString()}`, true);
            }
        });

        document.addEventListener('click', function (event) {
            const categoryLink = event.target.closest('#categoryFilter a');

            if (!categoryLink) {
                return;
            }

            event.preventDefault();
            loadProductContent(categoryLink.href, true);
        });

        window.addEventListener('popstate', function () {
            loadProductContent(window.location.href, false);
        });

        <?php if ($cartSuccess) : ?>
            cartToast.show();
        <?php endif; ?>
    </script>
</body>
</html>
