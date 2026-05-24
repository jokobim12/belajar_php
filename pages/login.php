<?php
require_once '../config/koneksi.php';
require_once '../include/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($koneksi, 'SELECT id_user, nama, email, password, role FROM users WHERE email = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id_user' => $user['id_user'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        header($user['role'] === 'admin' ? 'Location: ./admin/dashboard.php' : 'Location: ./produk.php');
        exit;
    }

    $error = 'Email atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/assets/style.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>

    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">
                    <div class="card auth-card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h1 class="h3 fw-bold page-title text-center mb-2">Login Akun</h1>
                            <p class="text-secondary text-center mb-4">Masuk untuk melihat keranjang dan profil belanja.</p>

                            <?php if ($error) : ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100 fw-semibold">Login</button>
                            </form>

                            <p class="text-center mt-4 mb-0">Belum punya akun? <a class="text-success fw-semibold" href="./regist.php">Daftar</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
