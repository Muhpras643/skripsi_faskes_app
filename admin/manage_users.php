<?php
session_start();
include '../includes/config.php';
include '../includes/auth_check.php';
include '../includes/functions.php';

auth_check('admin'); // Hanya admin yang bisa mengakses halaman ini

$message = '';
$error = '';

// Handle Add/Edit User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_user'])) {
    $user_id = $_POST['user_id'] ?? null;
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = $_POST['password']; // Password plaintext dari form

    if ($user_id) { // Edit existing user
        if (!empty($password)) { // If password is provided, hash it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $hashed_password, $role, $user_id);
        } else { // No new password provided, keep existing
            $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $role, $user_id);
        }
        
        if ($stmt->execute()) {
            $message = "Pengguna berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui pengguna: " . $stmt->error;
        }
        $stmt->close();
    } else { // Add new user
        if (empty($password)) {
            $error = "Password tidak boleh kosong untuk pengguna baru!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role);
            if ($stmt->execute()) {
                $message = "Pengguna baru berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan pengguna baru: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];
    // Prevent admin from deleting themselves or the only admin
    if ($id_to_delete == $_SESSION['user_id']) {
        $error = "Anda tidak bisa menghapus akun Anda sendiri!";
    } else {
        $stmt_check_admin = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt_check_admin->execute();
        $stmt_check_admin->bind_result($admin_count);
        $stmt_check_admin->fetch();
        $stmt_check_admin->close();

        $stmt_role_to_delete = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt_role_to_delete->bind_param("i", $id_to_delete);
        $stmt_role_to_delete->execute();
        $stmt_role_to_delete->bind_result($role_to_delete);
        $stmt_role_to_delete->fetch();
        $stmt_role_to_delete->close();

        if ($role_to_delete == 'admin' && $admin_count <= 1) {
             $error = "Tidak bisa menghapus admin terakhir. Harus ada minimal satu akun admin.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id_to_delete);
            if ($stmt->execute()) {
                $message = "Pengguna berhasil dihapus!";
            } else {
                $error = "Gagal menghapus pengguna: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch Users for display
$user_list = [];
$result = $conn->query("SELECT id, username, role FROM users ORDER BY username");
while ($row = $result->fetch_assoc()) {
    $user_list[] = $row;
}

// Get User data for edit form if 'edit' action is requested
$user_to_edit = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_to_edit = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_to_edit);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    $user_to_edit = $result_edit->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Klasifikasi Faskes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <div class="header">
        <h1>Sistem Klasifikasi Prioritas Fasilitas Kesehatan Kota Bekasi</h1>
        <p>Manajemen Pengguna (Admin)</p>

    </div>
    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
    <a href="manage_faskes.php">Manajemen Faskes</a>
    <a href="upload_and_classify.php">Klasifikasi Otomatis</a>
    <a href="view_results.php">Hasil Klasifikasi</a>
    <a href="form_event.php">Test Skill</a>
    <a href="../logout.php">Logout</a>
    </div>
    <div class="container">
        <h2><?php echo $user_to_edit ? 'Edit Pengguna' : 'Tambah Pengguna Baru'; ?></h2>
        <?php if ($message): ?>
            <p style="color: green;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_to_edit['id'] ?? ''); ?>">

            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user_to_edit['username'] ?? ''); ?>" required>

            <label for="password">Password <?php echo $user_to_edit ? '(Kosongkan jika tidak ingin mengubah)' : ''; ?>:</label>
            <input type="password" name="password" id="password" <?php echo $user_to_edit ? '' : 'required'; ?>>

            <label for="role">Peran:</label>
            <select name="role" id="role" required>
                <option value="admin" <?php echo (isset($user_to_edit['role']) && $user_to_edit['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="pengguna" <?php echo (isset($user_to_edit['role']) && $user_to_edit['role'] == 'pengguna') ? 'selected' : ''; ?>>Pengguna</option>
            </select>
            <br><br>
            <button type="submit" name="submit_user"><?php echo $user_to_edit ? 'Perbarui Pengguna' : 'Tambah Pengguna'; ?></button>
        </form>

        <hr>

        <h2>Daftar Pengguna</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Peran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($user_list)): ?>
                    <tr><td colspan="4">Tidak ada pengguna.</td></tr>
                <?php else: ?>
                    <?php foreach ($user_list as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="action-links">
                                <a href="manage_users.php?action=edit&id=<?php echo $user['id']; ?>">Edit</a> |
                                <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" onclick="return confirmDelete('Yakin ingin menghapus pengguna ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>