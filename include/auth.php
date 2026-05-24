<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in()
{
    return isset($_SESSION['user']);
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: ../login.php');
        exit;
    }
}

function require_admin()
{
    if (!is_logged_in() || ($_SESSION['user']['role'] ?? '') !== 'admin') {
        header('Location: ../login.php');
        exit;
    }
}

function rupiah($angka)
{
    return 'Rp' . number_format((int) $angka, 0, ',', '.');
}
?>
