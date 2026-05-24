<?php
require_once '../config/koneksi.php';
require_once '../include/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');

    if ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO users (nama, email, password, telepon, alamat, role) VALUES (?, ?, ?, ?, ?, "pelanggan")');
        mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $hash, $telepon, $alamat);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: ./login.php');
            exit;
        }

        $error = 'Registrasi gagal. Email mungkin sudah digunakan.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/assets/style.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>

    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card auth-card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <h1 class="h3 fw-bold page-title text-center mb-2">Buat Akun Baru</h1>
                            <p class="text-secondary text-center mb-4">Daftar untuk mulai menyimpan keranjang belanja.</p>

                            <?php if ($error) : ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nama" class="form-label">Nama lengkap</label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="telepon" class="form-label">Nomor telepon</label>
                                        <input type="text" class="form-control" id="telepon" name="telepon" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="konfirmasi_password" class="form-label">Konfirmasi password</label>
                                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success w-100 fw-semibold mt-4">Daftar</button>
                            </form>

                            <p class="text-center mt-4 mb-0">Sudah punya akun? <a class="text-success fw-semibold" href="./login.php">Login</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
