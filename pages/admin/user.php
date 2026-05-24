<?php
require_once '../../config/koneksi.php';
require_once '../../include/auth.php';
require_admin();

$edit = null;

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmtCart = mysqli_prepare($koneksi, 'DELETE FROM keranjang WHERE id_user = ?');
    mysqli_stmt_bind_param($stmtCart, 'i', $id);
    mysqli_stmt_execute($stmtCart);

    $stmt = mysqli_prepare($koneksi, 'DELETE FROM users WHERE id_user = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'User berhasil disimpan.']);
        exit;
    }

    header('Location: ./user.php');
    exit;
}

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM users WHERE id_user = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id_user'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $role = $_POST['role'] ?? 'pelanggan';
    $password = $_POST['password'] ?? '';

    if ($id > 0) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, 'UPDATE users SET nama = ?, email = ?, telepon = ?, alamat = ?, role = ?, password = ? WHERE id_user = ?');
            mysqli_stmt_bind_param($stmt, 'ssssssi', $nama, $email, $telepon, $alamat, $role, $hash, $id);
        } else {
            $stmt = mysqli_prepare($koneksi, 'UPDATE users SET nama = ?, email = ?, telepon = ?, alamat = ?, role = ? WHERE id_user = ?');
            mysqli_stmt_bind_param($stmt, 'sssssi', $nama, $email, $telepon, $alamat, $role, $id);
        }
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO users (nama, email, password, telepon, alamat, role) VALUES (?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssssss', $nama, $email, $hash, $telepon, $alamat, $role);
    }

    mysqli_stmt_execute($stmt);
    header('Location: ./user.php');
    exit;
}

$users = mysqli_query($koneksi, 'SELECT * FROM users ORDER BY id_user DESC');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/assets/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../../include/sidebar.php'; ?>
        <main class="flex-grow-1 p-4">
            <h1 class="page-title fw-bold mb-4">Data User</h1>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3"><?php echo $edit ? 'Edit User' : 'Tambah User'; ?></h2>
                    <form method="post" data-confirm-save data-ajax-save>
                        <input type="hidden" name="id_user" value="<?php echo (int) ($edit['id_user'] ?? 0); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($edit['nama'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="telepon" class="form-control" value="<?php echo htmlspecialchars($edit['telepon'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="pelanggan" <?php echo ($edit['role'] ?? '') === 'pelanggan' ? 'selected' : ''; ?>>Pelanggan</option>
                                    <option value="admin" <?php echo ($edit['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password <?php echo $edit ? 'baru' : ''; ?></label>
                                <input type="password" name="password" class="form-control" <?php echo $edit ? '' : 'required'; ?>>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control" rows="2"><?php echo htmlspecialchars($edit['alamat'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3"><?php echo $edit ? 'Update User' : 'Simpan User'; ?></button>
                        <?php if ($edit) : ?><a href="./user.php" class="btn btn-outline-secondary mt-3">Batal</a><?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="table-responsive bg-white rounded-3 shadow-sm">
                <table class="table align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($users)) : ?>
                            <tr>
                                <td class="fw-semibold"><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="badge text-bg-success"><?php echo htmlspecialchars($row['role']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['telepon']); ?></td>
                                <td>
                                    <a href="./user.php?edit=<?php echo (int) $row['id_user']; ?>" class="btn btn-outline-success btn-sm">Edit</a>
                                    <a href="./user.php?hapus=<?php echo (int) $row['id_user']; ?>" class="btn btn-outline-danger btn-sm" data-confirm-delete>Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <?php include '../../include/modal/save.php'; ?>
    <?php include '../../include/modal/delete.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
