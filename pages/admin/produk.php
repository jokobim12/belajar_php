<?php
require_once '../../config/koneksi.php';
require_once '../../include/auth.php';
require_admin();

$edit = null;
$kategoriOptions = ['barang', 'makanan', 'minuman', 'sembako', 'elektronik', 'kesehatan'];

function upload_gambar_produk($fieldName)
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = mime_content_type($_FILES[$fieldName]['tmp_name']);

    if (!isset($allowedTypes[$mime])) {
        return '';
    }

    $uploadDir = '../../public/uploads/produk/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = 'produk_' . time() . '_' . random_int(1000, 9999) . '.' . $allowedTypes[$mime];
    $target = $uploadDir . $filename;

    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $target)) {
        return '../public/uploads/produk/' . $filename;
    }

    return '';
}

function admin_gambar_src($gambar)
{
    if ($gambar === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//', $gambar)) {
        return $gambar;
    }

    return '../../' . ltrim(str_replace('../', '', $gambar), '/');
}

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmtCart = mysqli_prepare($koneksi, 'DELETE FROM keranjang WHERE id_produk = ?');
    mysqli_stmt_bind_param($stmtCart, 'i', $id);
    mysqli_stmt_execute($stmtCart);

    $stmt = mysqli_prepare($koneksi, 'DELETE FROM produk WHERE id_produk = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Produk berhasil disimpan.']);
        exit;
    }

    header('Location: ./produk.php');
    exit;
}

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE id_produk = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id_produk'] ?? 0);
    $nama = trim($_POST['nama_produk'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $harga = (int) ($_POST['harga'] ?? 0);
    $stok = (int) ($_POST['stok'] ?? 0);
    $gambarUrl = trim($_POST['gambar_url'] ?? '');
    $gambarLama = trim($_POST['gambar_lama'] ?? '');
    $gambarUpload = upload_gambar_produk('gambar_file');
    $gambar = $gambarUpload ?: ($gambarUrl ?: $gambarLama);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!in_array($kategori, $kategoriOptions, true)) {
        $kategori = 'barang';
    }

    if ($id > 0) {
        $stmt = mysqli_prepare($koneksi, 'UPDATE produk SET nama_produk = ?, kategori = ?, harga = ?, stok = ?, gambar = ?, deskripsi = ? WHERE id_produk = ?');
        mysqli_stmt_bind_param($stmt, 'ssiissi', $nama, $kategori, $harga, $stok, $gambar, $deskripsi, $id);
    } else {
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO produk (nama_produk, kategori, harga, stok, gambar, deskripsi) VALUES (?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssiiss', $nama, $kategori, $harga, $stok, $gambar, $deskripsi);
    }

    mysqli_stmt_execute($stmt);
    header('Location: ./produk.php');
    exit;
}

$produk = mysqli_query($koneksi, 'SELECT * FROM produk ORDER BY id_produk DESC');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/assets/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include '../../include/sidebar.php'; ?>
        <main class="flex-grow-1 p-4">
            <h1 class="page-title fw-bold mb-4">Data Produk</h1>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3"><?php echo $edit ? 'Edit Produk' : 'Tambah Produk'; ?></h2>
                    <form method="post" enctype="multipart/form-data" data-confirm-save data-ajax-save>
                        <input type="hidden" name="id_produk" value="<?php echo (int) ($edit['id_produk'] ?? 0); ?>">
                        <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($edit['gambar'] ?? ''); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama produk</label>
                                <input type="text" name="nama_produk" class="form-control" value="<?php echo htmlspecialchars($edit['nama_produk'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <?php foreach ($kategoriOptions as $kategoriOption) : ?>
                                        <option value="<?php echo $kategoriOption; ?>" <?php echo ($edit['kategori'] ?? '') === $kategoriOption ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($kategoriOption); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" class="form-control" value="<?php echo htmlspecialchars($edit['harga'] ?? 0); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stok</label>
                                <input type="number" name="stok" class="form-control" value="<?php echo htmlspecialchars($edit['stok'] ?? 0); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">URL gambar</label>
                                <input type="text" name="gambar_url" class="form-control" value="<?php echo htmlspecialchars($edit['gambar'] ?? ''); ?>" placeholder="https://contoh.com/gambar.jpg">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Upload gambar dari device</label>
                                <input type="file" name="gambar_file" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($edit['gambar'])) : ?>
                                    <label class="form-label">Preview gambar saat ini</label>
                                    <img src="<?php echo htmlspecialchars(admin_gambar_src($edit['gambar'])); ?>" class="admin-product-preview" alt="Preview produk">
                                <?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3"><?php echo htmlspecialchars($edit['deskripsi'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3"><?php echo $edit ? 'Update Produk' : 'Simpan Produk'; ?></button>
                        <?php if ($edit) : ?><a href="./produk.php" class="btn btn-outline-secondary mt-3">Batal</a><?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="table-responsive bg-white rounded-3 shadow-sm">
                <table class="table align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($produk)) : ?>
                            <tr>
                                <td class="fw-semibold"><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                <td><?php echo rupiah($row['harga']); ?></td>
                                <td><?php echo (int) $row['stok']; ?></td>
                                <td>
                                    <a href="./produk.php?edit=<?php echo (int) $row['id_produk']; ?>" class="btn btn-outline-success btn-sm">Edit</a>
                                    <a href="./produk.php?hapus=<?php echo (int) $row['id_produk']; ?>" class="btn btn-outline-danger btn-sm" data-confirm-delete>Hapus</a>
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
