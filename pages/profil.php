<?php
require_once '../config/koneksi.php';
require_once '../include/auth.php';
require_login();

$idUser = current_user()['id_user'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    $stmt = mysqli_prepare($koneksi, 'UPDATE users SET nama = ?, email = ?, telepon = ?, alamat = ? WHERE id_user = ?');
    mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $email, $telepon, $alamat, $idUser);
    mysqli_stmt_execute($stmt);

    $_SESSION['user']['nama'] = $nama;
    $_SESSION['user']['email'] = $email;
    $success = 'Profil berhasil diperbarui.';
}

$stmt = mysqli_prepare($koneksi, 'SELECT * FROM users WHERE id_user = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $idUser);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$initials = strtoupper(substr($user['nama'], 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - TokoPaedi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/assets/style.css">
</head>
<body>
    <?php include '../include/navbar.php'; ?>

    <main class="py-5">
        <div class="container">
            <?php if ($success) : ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card profile-card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center fw-bold mb-3" style="width: 86px; height: 86px; font-size: 2rem;"><?php echo $initials; ?></div>
                            <h1 class="h4 fw-bold page-title mb-1"><?php echo htmlspecialchars($user['nama']); ?></h1>
                            <p class="text-secondary mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                            <span class="badge text-bg-success"><?php echo htmlspecialchars($user['role']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h4 fw-bold page-title mb-4">Informasi Profil</h2>
                            <form method="post" data-confirm-save>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nama" class="form-label">Nama lengkap</label>
                                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="telepon" class="form-label">Nomor telepon</label>
                                        <input type="text" class="form-control" id="telepon" name="telepon" value="<?php echo htmlspecialchars($user['telepon']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tanggal_daftar" class="form-label">Tanggal daftar</label>
                                        <input type="text" class="form-control" id="tanggal_daftar" value="<?php echo htmlspecialchars($user['created_at']); ?>" disabled>
                                    </div>
                                    <div class="col-12">
                                        <label for="alamat" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success fw-semibold mt-4">Simpan Perubahan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../include/modal/save.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
