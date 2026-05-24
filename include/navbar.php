<?php
require_once __DIR__ . '/auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);

function active_nav($page, $currentPage)
{
    return $page === $currentPage ? 'active fw-semibold' : '';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../public/index.php">Toko<span class="text-warning">Paedi</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <a class="nav-link text-white <?php echo active_nav('index.php', $currentPage); ?>" href="../public/index.php">Beranda</a>
                <a class="nav-link text-white <?php echo active_nav('produk.php', $currentPage); ?>" href="../pages/produk.php">Produk</a>
                <a class="nav-link text-white <?php echo active_nav('keranjang.php', $currentPage); ?>" href="../pages/keranjang.php">Keranjang</a>
                <?php if (is_logged_in()) : ?>
                    <a class="nav-link text-white <?php echo active_nav('info.php', $currentPage); ?>" href="../pages/info.php">Info</a>
                    <a class="nav-link text-white <?php echo active_nav('profil.php', $currentPage); ?>" href="../pages/profil.php">Profil</a>
                    <?php if ((current_user()['role'] ?? '') === 'admin') : ?>
                        <a class="nav-link text-white" href="../pages/admin/dashboard.php">Admin</a>
                    <?php endif; ?>
                    <a class="btn btn-warning btn-sm fw-semibold px-3 ms-lg-2" href="../pages/logout.php" data-confirm-logout>Logout</a>
                <?php else : ?>
                    <a class="btn btn-warning btn-sm fw-semibold px-3 ms-lg-2" href="../pages/login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php include __DIR__ . '/modal/logout.php'; ?>
