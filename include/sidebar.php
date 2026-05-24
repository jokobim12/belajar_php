<?php
$adminPage = basename($_SERVER['PHP_SELF']);

function active_admin_nav($page, $adminPage)
{
    return $page === $adminPage ? 'active bg-success text-white' : 'text-success';
}
?>

<aside class="bg-white border-end min-vh-100 p-3" style="width: 260px;">
    <a href="./dashboard.php" class="d-block text-decoration-none mb-4">
        <h1 class="h4 fw-bold text-success mb-0">Admin TokoPaedi</h1>
        <small class="text-secondary">Panel pengelolaan toko</small>
    </a>

    <nav class="nav nav-pills flex-column gap-2">
        <a class="nav-link <?php echo active_admin_nav('dashboard.php', $adminPage); ?>" href="./dashboard.php">Dashboard</a>
        <a class="nav-link <?php echo active_admin_nav('produk.php', $adminPage); ?>" href="./produk.php">Data Produk</a>
        <a class="nav-link <?php echo active_admin_nav('user.php', $adminPage); ?>" href="./user.php">Data User</a>
        <a class="nav-link <?php echo active_admin_nav('checkout.php', $adminPage); ?>" href="./checkout.php">Data Checkout</a>
        <a class="nav-link <?php echo active_admin_nav('payment.php', $adminPage); ?>" href="./payment.php">Payment</a>
        <a class="nav-link text-danger" href="../logout.php" data-confirm-logout>Logout</a>
    </nav>
</aside>
<?php include __DIR__ . '/modal/logout.php'; ?>
